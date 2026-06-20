#!/usr/bin/env python3
"""
Pinterest Auto-Publisher voor kinderkleurplaten.com
Uploadt gegenereerde kleurplaten automatisch naar Pinterest via de v5 REST API.

Gebruik:
    Vanaf CLI:   python publish_to_pinterest.py --image pad/naar/afbeelding.png --subject "Dino" --url https://...
    Als module:  from publish_to_pinterest import PinterestPublisher
"""

import argparse
import base64
import io
import json
import os
import sys
import time
from pathlib import Path

import requests

# ---------------------------------------------------------------------------
# Configuratie
# ---------------------------------------------------------------------------

SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"

# Probeer .env te laden (zonder op python-dotenv te vereisen als die
# niet geïnstalleerd is – de gebruiker kan ook environment variables
# direct exporteren).
try:
    from dotenv import load_dotenv
    load_dotenv(dotenv_path=DOTENV_PATH)
except ImportError:
    pass

PINTEREST_ACCESS_TOKEN = os.getenv("PINTEREST_ACCESS_TOKEN", "")
PINTEREST_BOARD_ID = os.getenv("PINTEREST_BOARD_ID", "")
PINTEREST_API_BASE = "https://api.pinterest.com/v5"
PIN_BASE_URL = "https://www.kinderkleurplaten.com"  # geen trailing slash


# ---------------------------------------------------------------------------
# Exceptions
# ---------------------------------------------------------------------------

class PinterestAPIError(Exception):
    """Generieke Pinterest API fout."""
    def __init__(self, message: str, status_code: int | None = None, response_body: str | None = None):
        super().__init__(message)
        self.status_code = status_code
        self.response_body = response_body


class PinterestRateLimitError(PinterestAPIError):
    """Pinterest heeft de rate-limit bereikt."""
    pass


class PinterestBoardError(PinterestAPIError):
    """Pinterest Board lookup/create fout."""
    pass


# ---------------------------------------------------------------------------
# Pinterest Board Routing
# ---------------------------------------------------------------------------

PINTEREST_BOARD_PRIVACY = os.getenv("PINTEREST_BOARD_PRIVACY", "PUBLIC")


class PinterestBoardRouter:
    """
    Zoekt bestaande Pinterest Boards op en maakt ontbrekende Boards aan.

    Board namen worden exact gematcht na het verwijderen van leading/trailing
    whitespace. Een mislukte lookup of create wordt netjes afgehandeld door
    get_or_create_board() en retourneert None, zodat de pinning queue kan
    doorgaan met de volgende post.
    """

    MAX_RETRIES = 3
    RETRY_BASE_DELAY = 5

    def __init__(
        self,
        access_token: str,
        api_base: str = PINTEREST_API_BASE,
        timeout: int = 30,
        default_privacy: str = PINTEREST_BOARD_PRIVACY,
    ):
        if not access_token:
            raise PinterestBoardError("PINTEREST_ACCESS_TOKEN ontbreekt.")

        self.access_token = access_token
        self.api_base = api_base.rstrip("/")
        self.timeout = timeout
        self.default_privacy = default_privacy.upper()

    def _headers(self) -> dict:
        return {
            "Authorization": f"Bearer {self.access_token}",
            "Accept": "application/json",
        }

    def _api_url(self, endpoint: str) -> str:
        return f"{self.api_base}/{endpoint.lstrip('/')}"

    def _extract_error_message(self, response: requests.Response) -> str:
        try:
            body = response.json()
            if isinstance(body, dict):
                return body.get("message") or body.get("code") or response.text
        except (json.JSONDecodeError, ValueError):
            pass
        return response.text[:500]

    def _request_json(
        self,
        method: str,
        endpoint: str,
        json_body: dict | None = None,
        params: dict | None = None,
    ) -> dict:
        url = self._api_url(endpoint)

        for attempt in range(1, self.MAX_RETRIES + 1):
            try:
                response = requests.request(
                    method=method,
                    url=url,
                    headers=self._headers(),
                    json=json_body,
                    params=params,
                    timeout=self.timeout,
                )

                if response.status_code in (200, 201):
                    try:
                        return response.json()
                    except (json.JSONDecodeError, ValueError) as exc:
                        raise PinterestBoardError(
                            f"Pinterest API gaf geen geldige JSON terug voor {endpoint}: {exc}"
                        ) from exc

                if response.status_code == 429:
                    retry_after = response.headers.get("Retry-After")
                    wait = int(retry_after) if retry_after and retry_after.isdigit() else self.RETRY_BASE_DELAY * (2 ** (attempt - 1))
                    print(f"[Pinterest Boards] Rate-limit (429). Wachten {wait}s (poging {attempt}/{self.MAX_RETRIES})...")
                    time.sleep(wait)
                    continue

                if response.status_code >= 500:
                    wait = self.RETRY_BASE_DELAY * (2 ** (attempt - 1))
                    print(f"[Pinterest Boards] Serverfout {response.status_code}. Wachten {wait}s (poging {attempt}/{self.MAX_RETRIES})...")
                    time.sleep(wait)
                    continue

                error_msg = self._extract_error_message(response)
                raise PinterestBoardError(
                    f"Pinterest Board API fout: {error_msg}",
                    status_code=response.status_code,
                    response_body=response.text,
                )

            except requests.exceptions.Timeout:
                print(f"[Pinterest Boards] Timeout bij poging {attempt}/{self.MAX_RETRIES}...")
                time.sleep(self.RETRY_BASE_DELAY * (2 ** (attempt - 1)))
            except requests.exceptions.ConnectionError as exc:
                print(f"[Pinterest Boards] Verbindingsfout: {exc}")
                time.sleep(self.RETRY_BASE_DELAY * (2 ** (attempt - 1)))

        raise PinterestBoardError(f"Pinterest Board API gaf geen antwoord na {self.MAX_RETRIES} pogingen.")

    def fetch_boards(self) -> dict[str, str]:
        """
        Haalt alle beschikbare Boards op en retourneert {"Board Name": "Board ID"}.
        Ondersteunt Pinterest bookmark-paginering.
        """
        board_map: dict[str, str] = {}
        bookmark: str | None = None

        while True:
            params = {"page_size": 100}
            if bookmark:
                params["bookmark"] = bookmark

            try:
                data = self._request_json("GET", "/boards", params=params)
            except PinterestBoardError:
                raise
            except Exception as exc:
                raise PinterestBoardError(f"Onverwachte fout bij ophalen Boards: {exc}") from exc

            items = data.get("items", [])
            if not isinstance(items, list):
                raise PinterestBoardError(
                    "Pinterest Boards response bevat geen 'items' lijst.",
                    response_body=json.dumps(data),
                )

            for board in items:
                if not isinstance(board, dict):
                    continue

                name = board.get("name")
                board_id = board.get("id")
                if name and board_id:
                    board_map[str(name).strip()] = str(board_id)

            bookmark = data.get("bookmark")
            if not bookmark:
                break

        return board_map

    def create_board(self, board_name: str) -> str:
        """
        Maakt een nieuw Pinterest Board aan en retourneert de Board ID.
        """
        clean_name = str(board_name).strip()
        if not clean_name:
            raise PinterestBoardError("Board naam mag niet leeg zijn.")

        payload = {
            "name": clean_name,
        }

        if self.default_privacy:
            payload["privacy"] = self.default_privacy

        try:
            data = self._request_json("POST", "/boards", json_body=payload)
        except PinterestBoardError:
            raise
        except Exception as exc:
            raise PinterestBoardError(f"Onverwachte fout bij aanmaken Board '{clean_name}': {exc}") from exc

        board_id = data.get("id")
        if not board_id:
            raise PinterestBoardError(
                f"Pinterest retourneerde geen Board ID bij het aanmaken van '{clean_name}'.",
                response_body=json.dumps(data),
            )

        return str(board_id)

    def get_or_create_board(
        self,
        board_name: str | None,
        board_map: dict[str, str],
    ) -> str | None:
        """
        Geeft de Board ID terug voor een exacte WordPress category naam.
        Als het Board ontbreekt, wordt het automatisch aangemaakt.

        Retourneert None bij fouten zodat de caller de pin kan overslaan zonder
        dat de volledige pinning queue crasht.
        """
        if not board_name:
            print("[Pinterest Boards] Geen WordPress category naam gevonden; board routing overgeslagen.")
            return None

        clean_name = str(board_name).strip()
        if not clean_name:
            print("[Pinterest Boards] Lege category naam; board routing overgeslagen.")
            return None

        if clean_name in board_map:
            return board_map[clean_name]

        print(f"[Pinterest Boards] Board '{clean_name}' niet gevonden. Aanmaken...")

        try:
            new_board_id = self.create_board(clean_name)
        except PinterestBoardError as exc:
            print(f"[Pinterest Boards] Fout bij aanmaken Board '{clean_name}': {exc}", file=sys.stderr)
            return None
        except Exception as exc:
            print(f"[Pinterest Boards] Onverwachte fout bij Board '{clean_name}': {exc}", file=sys.stderr)
            return None

        board_map[clean_name] = new_board_id
        print(f"[Pinterest Boards] Board '{clean_name}' aangemaakt met ID: {new_board_id}")
        return new_board_id


def extract_wp_category_name(post: dict, taxonomy: str = "category") -> str | None:
    """
    Haalt de eerste WordPress category naam uit een WP REST post object.

    Verwacht dat de WordPress API met _embed wordt aangeroepen, zodat
    post["_embedded"]["wp:term"] gevuld is.
    """
    embedded = post.get("_embedded", {})
    if not isinstance(embedded, dict):
        return None

    term_groups = embedded.get("wp:term", [])
    if not isinstance(term_groups, list):
        return None

    for term_group in term_groups:
        if not isinstance(term_group, list):
            continue

        for term in term_group:
            if not isinstance(term, dict):
                continue

            if term.get("taxonomy") == taxonomy:
                name = term.get("name")
                if name:
                    return str(name).strip()

    return None


# ---------------------------------------------------------------------------
# Hoofdklasse
# ---------------------------------------------------------------------------

class PinterestPublisher:
    """
    Modular class voor het uploaden van media en aanmaken van Pins
    op Pinterest via de officiële v5 REST API.
    """

    MAX_RETRIES = 3          # Aantal herhaalpogingen bij rate-limit / tijdelijk falen
    RETRY_BASE_DELAY = 5    # Basiswachttijd in seconden (exponentiële back-off)

    def __init__(
        self,
        access_token: str = "",
        board_id: str = "",
        api_base: str = PINTEREST_API_BASE,
        site_base: str = PIN_BASE_URL,
    ):
        if not access_token:
            raise PinterestAPIError(
                "PINTEREST_ACCESS_TOKEN ontbreekt. "
                "Zie instructies onderaan dit bestand."
            )
        if not board_id:
            print(
                "[Config] Waarschuwing: PINTEREST_BOARD_ID ontbreekt. "
                "Geef bij dynamische board routing board_id per pin mee."
            )

        self.access_token = access_token
        self.board_id = board_id
        self.board_router: PinterestBoardRouter | None = None
        self.board_map: dict[str, str] = {}
        self.api_base = api_base.rstrip("/")
        self.site_base = site_base.rstrip("/")

    # ------------------------------------------------------------------
    # Interne helper: HTTP-request met retry-logica
    # ------------------------------------------------------------------

    def _request(
        self,
        method: str,
        endpoint: str,
        files: dict | None = None,
        json_body: dict | None = None,
        params: dict | None = None,
    ) -> dict:
        """
        Voert een Pinterest API-request uit met:
        - Bearer-token authenticatie
        - Exponentiële back-off bij rate-limit (429) en serverfouten (5xx)
        - Duidelijke error-raise bij 4xx clientfouten
        """
        url = f"{self.api_base}/{endpoint.lstrip('/')}"
        headers = {
            "Authorization": f"Bearer {self.access_token}",
        }

        last_exception: Exception | None = None

        for attempt in range(1, self.MAX_RETRIES + 1):
            try:
                resp = requests.request(
                    method=method,
                    url=url,
                    headers=headers,
                    files=files,
                    json=json_body,
                    params=params,
                    timeout=60,
                )

                # Succes
                if resp.status_code in (200, 201):
                    return resp.json()

                # Rate-limit → wacht en probeer opnieuw
                if resp.status_code == 429:
                    retry_after = self._get_retry_after(resp)
                    wait = retry_after if retry_after else self.RETRY_BASE_DELAY * (2 ** (attempt - 1))
                    print(
                        f"[Pinterest] Rate-limit (429). Wachten {wait}s "
                        f"(poging {attempt}/{self.MAX_RETRIES})..."
                    )
                    time.sleep(wait)
                    continue

                # Tijdelijke serverfout → wacht en probeer opnieuw
                if resp.status_code >= 500:
                    wait = self.RETRY_BASE_DELAY * (2 ** (attempt - 1))
                    print(
                        f"[Pinterest] Serverfout {resp.status_code}. "
                        f"Wachten {wait}s (poging {attempt}/{self.MAX_RETRIES})..."
                    )
                    time.sleep(wait)
                    continue

                # Clientfout (400, 401, 403, 404...) → geen herhaling nuttig
                error_msg = self._extract_error_message(resp)
                raise PinterestAPIError(
                    f"Pinterest API fout: {error_msg}",
                    status_code=resp.status_code,
                    response_body=resp.text,
                )

            except requests.exceptions.Timeout:
                print(f"[Pinterest] Timeout bij poging {attempt}/{self.MAX_RETRIES}...")
                time.sleep(self.RETRY_BASE_DELAY * (2 ** (attempt - 1)))
            except requests.exceptions.ConnectionError as exc:
                print(f"[Pinterest] Verbindingsfout: {exc}")
                time.sleep(self.RETRY_BASE_DELAY * (2 ** (attempt - 1)))

        raise PinterestRateLimitError(
            f"Pinterest API gaf geen antwoord na {self.MAX_RETRIES} pogingen."
        )

    @staticmethod
    def _get_retry_after(response: requests.Response) -> int | None:
        """Haalt de 'Retry-After' header uit de response (indien aanwezig)."""
        retry_after = response.headers.get("Retry-After")
        if retry_after:
            try:
                return int(retry_after)
            except ValueError:
                pass
        return None

    @staticmethod
    def _extract_error_message(response: requests.Response) -> str:
        """Extraheert een Leesbare foutmelding uit de Pinterest JSON-response."""
        try:
            body = response.json()
            # Pinterest v5 foutformaat: {"code": "...", "message": "...", ...}
            if isinstance(body, dict):
                return body.get("message", body.get("code", response.text))
        except (json.JSONDecodeError, ValueError):
            pass
        return response.text[:500]

    # ------------------------------------------------------------------
    # Publieke API
    # ------------------------------------------------------------------

    def upload_image(self, image_path: str) -> str:
        """
        Upload een lokale afbeelding (.png of .jpg) naar Pinterest's
        media-server en retourneert de gegenereerde media_id.

        Arg:
            image_path: Bestandspad naar de afbeelding.

        Retourneert:
            media_id (str) die gebruikt kan worden bij pin_creation.

        Werpt:
            PinterestAPIError, PinterestRateLimitError bij mislukking.
        """
        path = Path(image_path)
        if not path.exists():
            raise FileNotFoundError(f"Afbeeldingsbestand niet gevonden: {image_path}")

        suffix = path.suffix.lower()
        mime_types = {
            ".png": "image/png",
            ".jpg": "image/jpeg",
            ".jpeg": "image/jpeg",
        }
        media_type = mime_types.get(suffix)
        if media_type is None:
            raise ValueError(
                f"Niet-ondersteunde bestandsformaat '{suffix}'. "
                f"Ondersteund: {', '.join(mime_types.keys())}"
            )

        print(f"[Pinterest] Uploaden van afbeelding: {path.name}")

        with open(path, "rb") as fh:
            file_bytes = fh.read()

        files = {
            "image": (path.name, io.BytesIO(file_bytes), media_type),
        }

        result = self._request(
            method="POST",
            endpoint="/media",
            files=files,
        )

        media_id = result.get("media_id")
        if not media_id:
            raise PinterestAPIError(
                "Pinterest retourneerde geen media_id bij upload.",
                response_body=json.dumps(result),
            )

        print(f"[Pinterest] Upload succesvol. Media ID: {media_id}")
        return str(media_id)

    def create_pin(
        self,
        media_id: str,
        title: str,
        description: str,
        destination_url: str,
        alt_text: str = "",
        board_id: str | None = None,
    ) -> dict:
        """
        Maakt een nieuwe Pin aan op de geconfigureerde Board of op de
        dynamisch gekozen Board.

        Args:
            media_id:        ID returned door upload_image().
            title:           Pin-titel (max. 100 tekens).
            description:     Pin-beschrijving (max. 500 tekens).
            destination_url: Volledige URL waar de pin naartoe leidt
                             (bijv. de WordPress-post).
            alt_text:        Optionele alt-tekst voor toegankelijkheid.
            board_id:        Optionele dynamische Pinterest Board ID. Als deze
                             leeg is, wordt self.board_id gebruikt.

        Retourneert:
            dict met pin-gegevens (id, url, etc.) van Pinterest.

        Werpt:
            PinterestAPIError, PinterestRateLimitError bij mislukking.
        """
        print(f"[Pinterest] Pin aanmaken: '{title}'")

        target_board_id = board_id or self.board_id
        if not target_board_id:
            raise PinterestAPIError(
                "Pinterest board_id ontbreekt. Geef PINTEREST_BOARD_ID mee of "
                "geef board_id per pin mee bij create_pin()."
            )

        pin_data: dict = {
            "board_id": target_board_id,
            "media_source": {
                "media_id": media_id,
            },
            "title": title[:100],
            "description": description[:500],
            "link": destination_url,
        }

        if alt_text:
            pin_data["alt_text"] = alt_text

        result = self._request(
            method="POST",
            endpoint="/pins",
            json_body=pin_data,
        )

        pin_id = result.get("id", "onbekend")
        pin_url = result.get("url", "onbekend")
        print(f"[Pinterest] Pin aangemaakt! ID: {pin_id} | URL: {pin_url}")

        return result

    def publish_image(
        self,
        image_path: str,
        subject: str,
        wordpress_post_url: str,
        alt_text: str = "",
        title_template: str = "Gratis {subject} Kleurplaat",
        description_template: str | None = None,
        board_id: str | None = None,
    ) -> dict | None:
        """
        Hoog-niveau methode: uploadt een afbeelding en maakt direct een Pin.

        Args:
            image_path:          Pad naar de lokale afbeelding.
            subject:             Het thema (bijv. "Dino", "Eenhoorn").
            wordpress_post_url:  Volledige URL van de WordPress-post.
            alt_text:            Alt-tekst voor de afbeelding.
            title_template:      Jinlaag-vrije titel; {subject} wordt vervangen.
            description_template:Optionele beschrijving; wordt gegenereerd als None.
            board_id:            Optionele dynamische Pinterest Board ID.

        Retourneert:
            dict met pin-gegevens bij succes, None bij fout.
        """
        title = title_template.format(subject=subject)

        if description_template is None:
            description = self._build_seo_description(subject, wordpress_post_url)
        else:
            description = description_template.format(subject=subject)

        try:
            media_id = self.upload_image(image_path)
            pin_info = self.create_pin(
                media_id=media_id,
                title=title,
                description=description,
                destination_url=wordpress_post_url,
                alt_text=alt_text or title,
                board_id=board_id,
            )
            return pin_info

        except PinterestAPIError as exc:
            print(f"[Pinterest] API-fout: {exc}", file=sys.stderr)
        except PinterestRateLimitError as exc:
            print(f"[Pinterest] Rate-limit: {exc}", file=sys.stderr)
        except FileNotFoundError as exc:
            print(f"[Pinterest] Bestand niet gevonden: {exc}", file=sys.stderr)
        except ValueError as exc:
            print(f"[Pinterest] Onjuiste invoer: {exc}", file=sys.stderr)

        return None

    # ------------------------------------------------------------------
    # SEO-helpers
    # ------------------------------------------------------------------

    @staticmethod
    def _build_seo_description(subject: str, destination_url: str) -> str:
        """
        Genereert een Pinterest-geoptimaliseerde beschrijving in het Nederlands.
        Richt zich op SEO-keywords en een duidelijke call-to-action.
        """
        hashtags = (
            "#kleurplaat #kleurplaten #printen #kleuren "
            "#kinderen #activiteit #thuis #school #creatief"
        )
        description = (
            f"📄 Gratis {subject} kleurplaat zum Downloaden! "
            f"Bekijk en print direct op {destination_url}. "
            f"Veel plezier met kleuren! 🖍️"
        )
        if len(description) + len(hashtags) + 1 <= 500:
            description += f"\n\n{hashtags}"
        return description[:500]


# ---------------------------------------------------------------------------
# CLI-introductie
# ---------------------------------------------------------------------------

def main():
    parser = argparse.ArgumentParser(
        description="Upload een kleurplaat naar Pinterest via de v5 API."
    )
    parser.add_argument(
        "--image", required=True,
        help="Pad naar de afbeelding (.png of .jpg).",
    )
    parser.add_argument(
        "--subject", required=True,
        help="Onderwerp / thema van de kleurplaat (bijv. 'Dino', 'Eenhoorn').",
    )
    parser.add_argument(
        "--url", required=True,
        help="Destination URL (bijv. https://kinderkleurplaten.com/dino-kleurplaat/).",
    )
    parser.add_argument(
        "--alt", default="",
        help="Optionele alt-tekst voor de afbeelding.",
    )
    parser.add_argument(
        "--board-id",
        default=os.getenv("PINTEREST_BOARD_ID", ""),
        help="Pinterest Board ID (overrideert .env).",
    )
    parser.add_argument(
        "--access-token",
        default=os.getenv("PINTEREST_ACCESS_TOKEN", ""),
        help="Pinterest Access Token (overrideert .env).",
    )
    args = parser.parse_args()

    try:
        publisher = PinterestPublisher(
            access_token=args.access_token,
            board_id=args.board_id,
        )
    except PinterestAPIError as exc:
        print(f"Configuratiefout: {exc}", file=sys.stderr)
        sys.exit(1)

    result = publisher.publish_image(
        image_path=args.image,
        subject=args.subject,
        wordpress_post_url=args.url,
        alt_text=args.alt,
    )

    if result:
        print("\n✅ Pin succesvol gepubliceerd naar Pinterest!")
        print(f"   Pin-URL: {result.get('url', 'n/a')}")
    else:
        print("\n❌ Publiceren naar Pinterest mislukt.", file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
