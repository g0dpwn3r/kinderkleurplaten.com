#!/usr/bin/env python3
"""
Bulk Pinterest Publisher voor kinderkleurplaten.com
Haalt alle gepubliceerde kleurplaten op uit WordPress en pinnt ze automatisch.

Configuratie:
    Zorg dat PINTEREST_ACCESS_TOKEN, PINTEREST_BOARD_ID en WORDPRESS_URL
    in .env staan of als environment variable geëxporteerd zijn.

Gebruik:
    python bulk_pinterest_publisher.py
"""

import json
import os
import sys
import time
from pathlib import Path
from typing import Optional

import requests

from publish_to_pinterest import PinterestPublisher, PinterestAPIError, PinterestRateLimitError

# ---------------------------------------------------------------------------
# Configuratie
# ---------------------------------------------------------------------------

SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
TEMP_IMAGE_DIR = SCRIPT_DIR / "temp_images"
HISTORY_FILE = SCRIPT_DIR / "pinned_history.json"

WP_BASE_URL = os.getenv("WORDPRESS_URL", "https://kinderkleurplaten.com").rstrip("/")
WP_KLEURPLATEN_ENDPOINT = f"{WP_BASE_URL}/wp-json/wp/v2/kleurplaten"
WP_PER_PAGE = 100  # Maximale posts per pagina (WordPress ondersteunt meestal 100)
SLEEP_BETWEEN_PINS = 15  # Seconden tussen succesvolle pins om spam te vermijden
SLEEP_ON_ERROR = 30     # Seconden wachten bij API-fout voor herhaling


# ---------------------------------------------------------------------------
# State Management
# ---------------------------------------------------------------------------

def load_pinned_history() -> set[int]:
    """
    Laadt de set van WordPress post-ID's die al succesvol zijn gepind.
    Retourneert een lege set als het bestand niet bestaat of corrupt is.
    """
    if not HISTORY_FILE.exists():
        return set()

    try:
        content = HISTORY_FILE.read_text(encoding="utf-8")
        data = json.loads(content)
        if isinstance(data, list):
            return {int(item) for item in data}
        print(f"[Bulk] Waarschuwing: {HISTORY_FILE} bevat geen lijst. Hernieuwend...")
    except (json.JSONDecodeError, ValueError) as exc:
        print(f"[Bulk] Waarschuwing: kon {HISTORY_FILE} niet lezen ({exc}). Hernieuwend...")

    return set()


def save_pinned_history(pinned_ids: set[int]) -> None:
    """Sla de set van gepinde post-ID's op naar JSON."""
    HISTORY_FILE.write_text(
        json.dumps(sorted(pinned_ids), indent=2),
        encoding="utf-8",
    )


def mark_as_pinned(pinned_ids: set[int], post_id: int) -> None:
    """Voeg een post-ID toe aan de history en sla direct op."""
    pinned_ids.add(post_id)
    save_pinned_history(pinned_ids)


# ---------------------------------------------------------------------------
# WordPress Integratie
# ---------------------------------------------------------------------------

def fetch_all_wordpress_posts() -> list[dict]:
    """
    Haalt alle gepubliceerde kleurplaten op uit WordPress met paginering.
    Stopt wanneer er geen posts meer zijn of de API een fout geeft.

    Retourneert:
        Lijst van post-objecten (dicts) met '_embedded' data.
    """
    all_posts: list[dict] = []
    page = 1

    while True:
        print(f"[WP] Ophalen pagina {page}...")

        try:
            resp = requests.get(
                WP_KLEURPLATEN_ENDPOINT,
                params={
                    "_embed": "true",
                    "per_page": WP_PER_PAGE,
                    "page": page,
                    "status": "publish",
                },
                timeout=30,
            )

            if resp.status_code != 200:
                print(
                    f"[WP] Fout bij ophalen pagina {page}: "
                    f"HTTP {resp.status_code} - {resp.text[:300]}"
                )
                break

            posts = resp.json()

            if not posts:
                print(f"[WP] Geen posts meer op pagina {page}. Klaar.")
                break

            all_posts.extend(posts)
            print(f"[WP] {len(posts)} posts gevonden op pagina {page} (totaal nu: {len(all_posts)})")

            # Als er minder posts zijn dan per_page, is dit de laatste pagina
            if len(posts) < WP_PER_PAGE:
                print("[WP] Laatste pagina bereikt.")
                break

            page += 1

        except requests.exceptions.Timeout:
            print(f"[WP] Timeout bij ophalen pagina {page}. Wachten {SLEEP_ON_ERROR}s...")
            time.sleep(SLEEP_ON_ERROR)
        except requests.exceptions.ConnectionError as exc:
            print(f"[WP] Verbindingsfout: {exc}. Wachten {SLEEP_ON_ERROR}s...")
            time.sleep(SLEEP_ON_ERROR)
        except Exception as exc:
            print(f"[WP] Onverwachte fout: {exc}")
            break

    return all_posts


def extract_post_data(post: dict) -> Optional[dict]:
    """
    Extraheert relevante data uit een WordPress post.

    Retourneert:
        Dict met 'id', 'title', 'url', 'featured_image_url'
        of None als essentiële data ontbreekt.
    """
    post_id = post.get("id")
    if post_id is None:
        return None

    # Titel kan een dict of string zijn in de WP API
    title_raw = post.get("title", {})
    if isinstance(title_raw, dict):
        title = title_raw.get("rendered", "").strip()
    else:
        title = str(title_raw).strip()

    if not title:
        return None

    post_url = post.get("link", "").strip()
    if not post_url:
        return None

    # Featured image URL uit _embedded
    featured_image_url = ""
    embedded = post.get("_embedded", {})
    if "_wp:featuredmedia" in embedded:
        media_list = embedded["_wp:featuredmedia"]
        if media_list and isinstance(media_list, list):
            media = media_list[0]
            media_embedded = media.get("_embedded", {})
            if "wp:featuredmedia" in media_embedded:
                sizes = media_embedded["wp:featuredmedia"].get("media_details", {}).get("sizes", {})
                # Volgorde van voorkeur voor grootte
                for size_key in ["full", "large", "medium_large", "medium", "thumbnail"]:
                    if size_key in sizes:
                        featured_image_url = sizes[size_key].get("source_url", "")
                        if featured_image_url:
                            break
            if not featured_image_url:
                featured_image_url = media.get("source_url", "")

    if not featured_image_url:
        print(f"[WP] Waarschuwing: geen featured image voor post {post_id} ('{title}')")
        return None

    return {
        "id": post_id,
        "title": title,
        "url": post_url,
        "featured_image_url": featured_image_url,
    }


# ---------------------------------------------------------------------------
# Afbeelding Download
# ---------------------------------------------------------------------------

def download_image(url: str, dest_path: Path) -> bool:
    """
    Downloadt een afbeelding naar een lokaal pad.

    Retourneert:
        True bij succes, False bij falen.
    """
    try:
        resp = requests.get(url, timeout=60, stream=True)
        if resp.status_code != 200:
            print(f"[IMG] Download fout: HTTP {resp.status_code} voor {url}")
            return False

        dest_path.parent.mkdir(parents=True, exist_ok=True)
        with open(dest_path, "wb") as fh:
            for chunk in resp.iter_content(chunk_size=8192):
                if chunk:
                    fh.write(chunk)

        return True

    except requests.exceptions.Timeout:
        print(f"[IMG] Timeout bij downloaden {url}")
    except requests.exceptions.ConnectionError as exc:
        print(f"[IMG] Verbindingsfout bij downloaden {url}: {exc}")
    except Exception as exc:
        print(f"[IMG] Onverwachte fout bij downloaden {url}: {exc}")

    return False


# ---------------------------------------------------------------------------
# Pinterest Publishing
# ---------------------------------------------------------------------------

def create_publisher() -> Optional[PinterestPublisher]:
    """
    Initialiseert de PinterestPublisher uit environment variables.

    Retourneert:
        PinterestPublisher instantie of None bij configuratiefout.
    """
    try:
        return PinterestPublisher()
    except PinterestAPIError as exc:
        print(f"[Config] Fout: {exc}", file=sys.stderr)
        return None


def process_post(
    publisher: PinterestPublisher,
    post: dict,
    pinned_ids: set[int],
) -> bool:
    """
    Verwerkt één WordPress post: download afbeelding, pin op Pinterest,
    update history, en ruim temp file op.

    Retourneert:
        True als de post succesvol is verwerkt (of al gepind was),
        False bij een fout.
    """
    post_id = post["id"]
    title = post["title"]
    url = post["url"]
    image_url = post["featured_image_url"]

    # Duplicaatcontrole
    if post_id in pinned_ids:
        print(f"\n[Skippen] Post {post_id} ('{title}') is al gepind.")
        return True

    print(f"\n{'='*60}")
    print(f"[Verwerken] Post {post_id}: '{title}'")
    print(f"{'='*60}")

    # Download afbeelding naar tijdelijke map
    temp_filename = f"{post_id}_{Path(url).stem}.png"
    # Neem extensie van originele URL als die .png of .jpg is
    ext = Path(image_url).suffix.lower()
    if ext in (".png", ".jpg", ".jpeg"):
        temp_filename = f"{post_id}_{Path(url).stem}{ext}"
    else:
        temp_filename = f"{post_id}_{Path(url).stem}.png"

    temp_path = TEMP_IMAGE_DIR / temp_filename

    print(f"[IMG] Downloaden van {image_url[:80]}...")
    if not download_image(image_url, temp_path):
        print(f"[Fout] Kon afbeelding niet downloaden voor post {post_id}. Overslaan.")
        return False

    file_size_kb = temp_path.stat().st_size / 1024
    print(f"[IMG] Gedownload: {temp_path.name} ({file_size_kb:.1f} KB)")

    # Pin op Pinterest
    try:
        pin_result = publisher.publish_image(
            image_path=str(temp_path),
            subject=title,
            wordpress_post_url=url,
            alt_text=title,
        )

        if pin_result:
            pin_url = pin_result.get("url", "n/a")
            print(f"[Succes] Pin aangemaakt voor '{title}': {pin_url}")
            mark_as_pinned(pinned_ids, post_id)
            return True
        else:
            print(f"[Fout] Pinterest publish_image() faalde voor post {post_id}.")
            return False

    except (PinterestAPIError, PinterestRateLimitError) as exc:
        print(f"[Fout] Pinterest API fout voor post {post_id}: {exc}", file=sys.stderr)
        return False
    except Exception as exc:
        print(f"[Fout] Onverwachte fout voor post {post_id}: {exc}", file=sys.stderr)
        return False
    finally:
        # Ruim altijd de temp file op
        if temp_path.exists():
            try:
                temp_path.unlink()
                print(f"[Cleanup] Verwijderd: {temp_path.name}")
            except OSError as exc:
                print(f"[Cleanup] Kon {temp_path.name} niet verwijderen: {exc}", file=sys.stderr)


# ---------------------------------------------------------------------------
# Hoofdlus
# ---------------------------------------------------------------------------

def main():
    print("=" * 60)
    print("BULK PINTEREST PUBLISHER")
    print("Website: kinderkleurplaten.com")
    print("=" * 60)

    # Initialiseer temp map
    TEMP_IMAGE_DIR.mkdir(parents=True, exist_ok=True)
    print(f"[Setup] Tijdelijke map: {TEMP_IMAGE_DIR}")

    # Laad gepinde geschiedenis
    pinned_ids = load_pinned_history()
    print(f"[Setup] Al gepinde posts in geschiedenis: {len(pinned_ids)}")

    # Initialiseer Pinterest publisher
    publisher = create_publisher()
    if publisher is None:
        print("[Fout] Kan PinterestPublisher niet initialiseren. Controleer credentials.", file=sys.stderr)
        sys.exit(1)

    print(f"[Setup] Pinterest Publisher geïnitialiseerd voor board: {publisher.board_id}")

    # Haal alle WordPress posts op
    print("\n[WP] Alle kleurplaten ophalen uit WordPress...")
    posts = fetch_all_wordpress_posts()
    print(f"[WP] Totaal {len(posts)} gepubliceerde kleurplaten gevonden.")

    if not posts:
        print("\nGeen posts om te verwerken. Script beëindigd.")
        sys.exit(0)

    # Filter op posts met bruikbare data
    valid_posts = []
    skipped_no_image = 0
    for raw_post in posts:
        post_data = extract_post_data(raw_post)
        if post_data is None:
            skipped_no_image += 1
            continue
        valid_posts.append(post_data)

    print(f"[Filter] {len(valid_posts)} posts met featured image klaar om te pinnen.")
    if skipped_no_image:
        print(f"[Filter] {skipped_no_image} posts overgeslagen (geen titel/beeld).")

    # Verwerk posts
    success_count = 0
    skip_count = 0
    fail_count = 0

    for idx, post in enumerate(valid_posts, start=1):
        print(f"\n[Progress] {idx}/{len(valid_posts)}")

        if post["id"] in pinned_ids:
            print(f"[Skippen] Post {post['id']} al in geschiedenis.")
            skip_count += 1
            continue

        result = process_post(publisher, post, pinned_ids)

        if result:
            if post["id"] in pinned_ids:
                success_count += 1
            else:
                skip_count += 1  # Alreeds gepind in deze sessie
        else:
            fail_count += 1

        # Wacht tussen pins (alleen na succesvolle pins)
        if result and idx < len(valid_posts):
            print(f"[Wacht] {SLEEP_BETWEEN_PINS} seconden voor volgende pin...")
            time.sleep(SLEEP_BETWEEN_PINS)

    # Samenvatting
    print("\n" + "=" * 60)
    print("SAMENVATTING")
    print("=" * 60)
    print(f"  Totaal gevonden posts:     {len(posts)}")
    print(f"  Met bruikbaar beeld:       {len(valid_posts)}")
    print(f"  Succesvol gepind:          {success_count}")
    print(f"  Overgeslagen (duplicaat):  {skip_count}")
    print(f"  Mislukt:                   {fail_count}")
    print(f"  Totaal in geschiedenis:    {len(pinned_ids)}")
    print(f"  Geschiedenis opgeslagen:   {HISTORY_FILE}")
    print("=" * 60)


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("\n\n[Stop] Onderbroken door gebruiker (Ctrl+C).")
        sys.exit(0)
    except Exception as exc:
        print(f"\n[Kritieke fout] {exc}", file=sys.stderr)
        sys.exit(1)
