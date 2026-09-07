#!/usr/bin/env python3
"""
Bulk Pinterest Publisher voor kinderkleurplaten.com
Haalt alle gepubliceerde kleurplaten op uit WordPress en pinnt ze automatisch.

Afbeelding fallback: featured_media → eerste <img> in post content → overslaan.
Anti-spam: max 25 succesvolle pins per run, random 60-180s vertraging.
Geschiedenis: pinned_history.json voorkomt duplicaten, directe write per pin.

Configuratie:
    Zorg dat PINTEREST_ACCESS_TOKEN, PINTEREST_BOARD_ID en WORDPRESS_URL
    in .env staan of als environment variable geëxporteerd zijn.

Gebruik:
    python bulk_pinterest_publisher.py
"""

import json
import os
import random
import re
import sys
import time
from pathlib import Path
from typing import Optional

import requests

from publish_to_pinterest import (
    PinterestPublisher,
    PinterestBoardRouter,
    PinterestAPIError,
    PinterestRateLimitError,
    extract_wp_category_name,
)

# ---------------------------------------------------------------------------
# Configuratie
# ---------------------------------------------------------------------------

SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
TEMP_IMAGE_DIR = SCRIPT_DIR / "temp_images"
HISTORY_FILE = SCRIPT_DIR / "pinned_history.json"

WP_BASE_URL = os.getenv("WORDPRESS_URL", "https://kinderkleurplaten.com").rstrip("/")
WP_KLEURPLATEN_ENDPOINT = f"{WP_BASE_URL}/wp-json/wp/v2/kleurplaten"
WP_PER_PAGE = 100

DAILY_PIN_LIMIT = 25
MIN_SLEEP_BETWEEN_PINS = 60
MAX_SLEEP_BETWEEN_PINS = 180
SLEEP_ON_ERROR = 30

_IMG_SRC_RE = re.compile(r'<img[^>]+src=["\']([^"\']+)["\']', re.IGNORECASE)
_OG_IMAGE_RE = re.compile(r'<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']', re.IGNORECASE)


# ---------------------------------------------------------------------------
# State Management
# ---------------------------------------------------------------------------

def load_pinned_history() -> set[int]:
    if not HISTORY_FILE.exists():
        return set()

    try:
        content = HISTORY_FILE.read_text(encoding="utf-8")
        data = json.loads(content)
        if isinstance(data, list):
            return {int(item) for item in data}
        print(f"[Bulk] Waarschuwing: {HISTORY_FILE} bevat geen lijst. Starten met lege set...")
    except (json.JSONDecodeError, ValueError) as exc:
        print(f"[Bulk] Waarschuwing: kon {HISTORY_FILE} niet lezen ({exc}). Starten met lege set...")

    return set()


def save_pinned_history(pinned_ids: set[int]) -> None:
    HISTORY_FILE.write_text(
        json.dumps(sorted(pinned_ids), indent=2),
        encoding="utf-8",
    )


def mark_as_pinned(pinned_ids: set[int], post_id: int) -> None:
    pinned_ids.add(post_id)
    save_pinned_history(pinned_ids)


# ---------------------------------------------------------------------------
# WordPress Integratie
# ---------------------------------------------------------------------------

def fetch_all_wordpress_posts() -> list[dict]:
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


def extract_first_image_from_content(content_html: str) -> str:
    match = _IMG_SRC_RE.search(content_html)
    if match:
        return match.group(1).strip()
    return ""


def _extract_yoast_og_image(post: dict) -> str:
    yoast_json = post.get("yoast_head_json", {})
    if isinstance(yoast_json, dict):
        og_images = yoast_json.get("og_image", [])
        if og_images and isinstance(og_images, list) and len(og_images) > 0:
            url = og_images[0].get("url", "")
            if url:
                return url
    
    yoast_head = post.get("yoast_head", "")
    if yoast_head:
        match = _OG_IMAGE_RE.search(yoast_head)
        if match:
            return match.group(1).strip()
    
    return ""


def _extract_featured_image_url(post: dict) -> str:
    embedded = post.get("_embedded", {})
    if not isinstance(embedded, dict):
        return ""
    
    media_list = embedded.get("wp:featuredmedia", [])
    if not media_list or not isinstance(media_list, list) or len(media_list) == 0:
        return ""
    
    media = media_list[0]
    if not isinstance(media, dict):
        return ""
    
    source_url = media.get("source_url", "")
    if source_url:
        return source_url
    
    media_details = media.get("media_details", {})
    if isinstance(media_details, dict):
        sizes = media_details.get("sizes", {})
        if isinstance(sizes, dict):
            for size_key in ["full", "large", "medium_large", "medium", "thumbnail"]:
                if size_key in sizes:
                    size_data = sizes[size_key]
                    if isinstance(size_data, dict):
                        url = size_data.get("source_url", "")
                        if url:
                            return url
    
    return ""


def _fetch_wp_media_attachment(post_id: int) -> str:
    try:
        attachment_url = f"{WP_BASE_URL}/wp-json/wp/v2/media?parent={post_id}&per_page=1"
        media_response = requests.get(attachment_url, timeout=10)
        
        if media_response.status_code == 200:
            attachments = media_response.json()
            if attachments and isinstance(attachments, list) and len(attachments) > 0:
                attachment = attachments[0]
                if isinstance(attachment, dict):
                    source_url = attachment.get("source_url", "")
                    if source_url:
                        return source_url
    except Exception as exc:
        print(f"[WP API] Waarschuwing: kon gekoppelde media voor post {post_id} niet ophalen: {exc}")
    
    return ""


def extract_post_data(post: dict) -> Optional[dict]:
    post_id = post.get("id")
    if post_id is None:
        return None

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

    image_url = _extract_featured_image_url(post)
    image_source = "featured_media"

    if not image_url:
        image_url = _extract_yoast_og_image(post)
        if image_url:
            image_source = "yoast_og_image"

    if not image_url:
        content_html = post.get("content", {}).get("rendered", "")
        if content_html:
            image_url = extract_first_image_from_content(content_html)
            if image_url:
                image_source = "content_html"

    if not image_url:
        image_url = _fetch_wp_media_attachment(post_id)
        if image_url:
            image_source = "wp_api_attachment"

    if not image_url:
        print(f"[WP] Waarschuwing: geen afbeelding gevonden (4 lagen) voor post {post_id} ('{title}')")
        return None

    return {
        "id": post_id,
        "title": title,
        "url": post_url,
        "featured_image_url": image_url,
        "image_source": image_source,
        "category_name": extract_wp_category_name(post, taxonomy="kleurplaat_categorie"),
    }


# ---------------------------------------------------------------------------
# Afbeelding Download
# ---------------------------------------------------------------------------

def download_image(url: str, dest_path: Path) -> bool:
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
    post_id = post["id"]
    title = post["title"]
    url = post["url"]
    image_url = post["featured_image_url"]
    image_source = post.get("image_source", "onbekend")

    if post_id in pinned_ids:
        print(f"\n[Skippen] Post {post_id} ('{title}') is al gepind.")
        return True

    print(f"\n{'='*60}")
    print(f"[Verwerken] Post {post_id}: '{title}'")
    print(f"[Bron] Afbeelding via: {image_source}")
    print(f"{'='*60}")

    # Bepaal het Pinterest Board op basis van de WordPress category
    board_id = None
    category_name = post.get("category_name")

    if category_name:
        board_id = publisher.board_router.get_or_create_board(
            board_name=category_name,
            board_map=publisher.board_map,
        )
    else:
        print(f"[Fout] Geen WordPress category gevonden voor post {post_id}. Board routing overgeslagen.")

    if not board_id:
        print(f"[Fout] Kon geen geldige Pinterest Board ID bepalen voor post {post_id}. Overslaan.")
        return False

    # Gebruik de WordPress afbeelding-URL rechtstreeks (geen download nodig)
    print(f"[Pinterest] Afbeelding-URL: {image_url[:80]}...")

    try:
        pin_result = publisher.publish_image_url(
            image_url=image_url,
            subject=title,
            wordpress_post_url=url,
            alt_text=title,
            board_id=board_id,
        )

        if pin_result:
            pin_url = pin_result.get("url", "n/a")
            print(f"[Succes] Pin aangemaakt voor '{title}': {pin_url}")
            mark_as_pinned(pinned_ids, post_id)
            return True
        else:
            print(f"[Fout] Pinterest publish_image_url() faalde voor post {post_id}.")
            return False

    except (PinterestAPIError, PinterestRateLimitError) as exc:
        print(f"[Fout] Pinterest API fout voor post {post_id}: {exc}", file=sys.stderr)
        return False
    except Exception as exc:
        print(f"[Fout] Onverwachte fout voor post {post_id}: {exc}", file=sys.stderr)
        return False


# ---------------------------------------------------------------------------
# Hoofdlus
# ---------------------------------------------------------------------------

def main():
    print("=" * 60)
    print("BULK PINTEREST PUBLISHER")
    print("Website: kinderkleurplaten.com")
    print(f"Limiet: {DAILY_PIN_LIMIT} pins per run | Delay: {MIN_SLEEP_BETWEEN_PINS}-{MAX_SLEEP_BETWEEN_PINS}s")
    print("=" * 60)

    TEMP_IMAGE_DIR.mkdir(parents=True, exist_ok=True)
    print(f"[Setup] Tijdelijke map: {TEMP_IMAGE_DIR}")

    pinned_ids = load_pinned_history()
    print(f"[Setup] Al gepinde posts in geschiedenis: {len(pinned_ids)}")

    publisher = create_publisher()
    if publisher is None:
        print("[Fout] Kan PinterestPublisher niet initialiseren. Controleer credentials.", file=sys.stderr)
        sys.exit(1)

    publisher.board_router = PinterestBoardRouter(
        access_token=publisher.access_token,
        api_base=publisher.api_base,
    )

    try:
        publisher.board_map = publisher.board_router.fetch_boards()
        print(f"[Setup] Pinterest Boards geladen: {len(publisher.board_map)}")
    except Exception as exc:
        publisher.board_map = {}
        print(f"[Setup] Waarschuwing: kon Pinterest Boards niet laden ({exc}). Ontbrekende Boards worden later automatisch aangemaakt.")

    print(f"[Setup] Pinterest Publisher geïnitialiseerd. Fallback board: {publisher.board_id or 'geen'}")

    print("\n[WP] Alle kleurplaten ophalen uit WordPress...")
    posts = fetch_all_wordpress_posts()
    print(f"[WP] Totaal {len(posts)} gepubliceerde kleurplaten gevonden.")

    if not posts:
        print("\nGeen posts om te verwerken. Script beëindigd.")
        sys.exit(0)

    success_count = 0
    skip_already_pinned = 0
    skip_no_image = 0
    fail_count = 0
    limit_reached = False

    for idx, raw_post in enumerate(posts, start=1):
        post_id = raw_post.get("id")

        if post_id in pinned_ids:
            skip_already_pinned += 1
            continue

        post_data = extract_post_data(raw_post)
        if post_data is None:
            skip_no_image += 1
            continue

        print(f"\n[Progress] {idx}/{len(posts)} | Pins deze run: {success_count}/{DAILY_PIN_LIMIT}")

        result = process_post(publisher, post_data, pinned_ids)

        if result and post_id in pinned_ids:
            success_count += 1

            if success_count >= DAILY_PIN_LIMIT:
                print(f"\n[Limiet] {DAILY_PIN_LIMIT} succesvolle pins bereikt in deze run. Stoppen.")
                limit_reached = True
                break

            sleep_seconds = random.randint(MIN_SLEEP_BETWEEN_PINS, MAX_SLEEP_BETWEEN_PINS)
            print(f"[Anti-spam] Wachten {sleep_seconds} seconden voor volgende pin...")
            time.sleep(sleep_seconds)
        elif not result:
            fail_count += 1

    print("\n" + "=" * 60)
    print("SAMENVATTING")
    print("=" * 60)
    print(f"  Totaal gevonden posts:        {len(posts)}")
    print(f"  Overgeslagen (al gepind):     {skip_already_pinned}")
    print(f"  Overgeslagen (geen afbeelding): {skip_no_image}")
    print(f"  Succesvol gepind deze run:    {success_count}")
    print(f"  Mislukt deze run:             {fail_count}")
    print(f"  Dagelijkse limiet bereikt:    {'Ja' if limit_reached else 'Nee'}")
    print(f"  Totaal in geschiedenis:       {len(pinned_ids)}")
    print(f"  Geschiedenis opgeslagen:      {HISTORY_FILE}")
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
