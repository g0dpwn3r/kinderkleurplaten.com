#!/usr/bin/env python3
"""
Bulk categorisatie script voor kleurplaten.

OPTIMIZED VERSION:
- Batch processing: verwerkt 10 posts tegelijk
- Rate limiting: 2 seconden pauze na elke batch (geeft MySQL tijd om term counts bij te werken)
- Cache flush: stuurt na elke batch een request naar WordPress om de transient cache te wissen
- Progress tracking: toont voortgang en statistieken

Dit voorkomt 504 Gateway Timeouts door de database niet te overbelasten.
"""
import argparse
import json
import os
import re
import time
from pathlib import Path

import requests
from dotenv import load_dotenv
from requests.exceptions import RequestException
from math import ceil

SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
load_dotenv(dotenv_path=DOTENV_PATH)

PER_PAGE = 100
REQUEST_TIMEOUT = 15
CUSTOM_TAXONOMY = "kleurplaat_categorie"
STANDARD_CATEGORY_ENDPOINT = "categories"
STANDARD_CATEGORY_PAYLOAD_KEY = "categories"

BATCH_SIZE = 10  # Aantal posts per batch
BATCH_DELAY = 2  # Seconden pauze na elke batch

COLORED_KEYWORDS = ["ingekleurd", "gekleurd", "voorbeeld"]
COLORED_CATEGORY_NAME = "Ingekleurde Voorbeelden"
COLORED_CATEGORY_SLUG = "ingekleurde-voorbeelden"


def load_config():
    """Load environment variables for WordPress auth."""
    return {
        "wp_url": os.getenv("WORDPRESS_URL", "https://kinderkleurplaten.com"),
        "wp_user": os.getenv("WORDPRESS_USERNAME"),
        "wp_app_pass": os.getenv("WORDPRESS_APP_PASSWORD"),
    }


def parse_args():
    """Parse command-line arguments."""
    parser = argparse.ArgumentParser(description="Bulk categorize existing kleurplaten posts.")
    parser.add_argument(
        "--taxonomy-type",
        choices=("standard", "custom"),
        default="custom",
        help="Taxonomy to update: 'custom' uses kleurplaat_categorie, 'standard' uses WordPress categories.",
    )
    return parser.parse_args()


def taxonomy_config(taxonomy_type):
    """Return endpoint and update payload key for the selected taxonomy type."""
    if taxonomy_type == "custom":
        return {
            "endpoint": CUSTOM_TAXONOMY,
            "payload_key": CUSTOM_TAXONOMY,
            "label": "custom taxonomy",
        }

    return {
        "endpoint": STANDARD_CATEGORY_ENDPOINT,
        "payload_key": STANDARD_CATEGORY_PAYLOAD_KEY,
        "label": "standard category",
    }


def fetch_all_posts(wp_url, auth):
    """Fetch all posts from /wp-json/wp/v2/kleurplaten with pagination."""
    all_posts = []
    page = 1

    while True:
        try:
            response = requests.get(
                f"{wp_url.rstrip('/')}/wp-json/wp/v2/kleurplaten",
                params={"per_page": PER_PAGE, "page": page},
                auth=auth,
                timeout=REQUEST_TIMEOUT,
            )
        except RequestException as e:
            print(f"[ERROR] Failed to fetch posts (page {page}): {e}")
            break

        if response.status_code != 200:
            print(f"[ERROR] Failed to fetch posts: status {response.status_code}")
            break

        try:
            posts = response.json()
        except (ValueError, json.JSONDecodeError) as e:
            print(f"[ERROR] Failed to parse posts response (page {page}): {e}")
            break

        if not posts:
            break

        all_posts.extend(posts)

        if len(posts) < PER_PAGE:
            break

        page += 1

    return all_posts


def extract_subject(title: str) -> str | None:
    """
    Extract subject from title by removing stop words.
    """
    if not title:
        return None

    stopwoorden = ["gratis", "kleurplaat", "kleurplaten", "van", "een", "voor", "kinderen"]
    woorden = title.split()
    onderwerp_woorden = []

    for woord in woorden:
        schoon_woord = woord.lower().strip(",.!?")
        if schoon_woord not in stopwoorden:
            onderwerp_woorden.append(woord)

    if onderwerp_woorden:
        return " ".join(onderwerp_woorden).capitalize()

    return None


def is_colored(title: str) -> bool:
    """
    Check if the post title indicates the image is already colored.
    Returns True if any colored keyword is found in the title.
    """
    if not title:
        return False

    title_lower = title.lower()
    return any(keyword in title_lower for keyword in COLORED_KEYWORDS)


def slugify(text: str) -> str:
    """Convert text to a lowercase, hyphen-separated URL slug."""
    text = text.lower()
    text = re.sub(r"[^a-z0-9\s-]", "", text)
    text = re.sub(r"[\s_]+", "-", text)
    text = re.sub(r"-{2,}", "-", text)
    return text.strip("-")


def get_or_create_taxonomy_item(item_name, cache, wp_url, auth, taxonomy_type):
    """Get or create a taxonomy item by name, using cache for lookups."""
    if item_name in cache:
        return cache[item_name]

    config = taxonomy_config(taxonomy_type)
    taxonomy_url = f"{wp_url.rstrip('/')}/wp-json/wp/v2/{config['endpoint']}"

    try:
        search_response = requests.get(
            taxonomy_url,
            params={"search": item_name},
            auth=auth,
            timeout=REQUEST_TIMEOUT,
        )
    except RequestException as e:
        print(f"[ERROR] Failed to search {config['label']} '{item_name}': {e}")
        return None

    if search_response.status_code == 200:
        try:
            items = search_response.json()
        except (ValueError, json.JSONDecodeError) as e:
            print(f"[ERROR] Failed to parse search response for {config['label']} '{item_name}': {e}")
            return None

        for item in items:
            if item.get("name", "").lower() == item_name.lower():
                cache[item_name] = item["id"]
                return item["id"]

    try:
        create_response = requests.post(
            taxonomy_url,
            json={"name": item_name},
            auth=auth,
            timeout=REQUEST_TIMEOUT,
        )
    except RequestException as e:
        print(f"[ERROR] Failed to create {config['label']} '{item_name}': {e}")
        return None

    if create_response.status_code == 201:
        try:
            item_id = create_response.json().get("id")
        except (ValueError, json.JSONDecodeError) as e:
            print(f"[ERROR] Failed to parse create response for {config['label']} '{item_name}': {e}")
            return None

        cache[item_name] = item_id
        return item_id

    print(f"[ERROR] Failed to create {config['label']} '{item_name}': status {create_response.status_code}")
    return None


def build_seo_content(subject: str, subject_slug: str, colored: bool) -> str:
    """
    Builds the SEO HTML content to prepend and append based on the colored flag.
    """
    if colored:
        prepend = (
            f'<h2 class="seo-subtitle">Prachtig Ingekleurd Voorbeeld: {subject}</h2>'
            f'<p>Bekijk dit mooi <strong>ingekleurde voorbeeld</strong> van een {subject}. '
            f'Gebruik deze afbeelding ter inspiratie voor je eigen tekeningen!</p>'
        )
        append = (
            f'<p style="margin-top: 30px; font-style: italic;">'
            f'Bekijk onze galerij voor meer '
            f'<a href="/kleurplaat_categorie/{COLORED_CATEGORY_SLUG}/">{COLORED_CATEGORY_NAME}</a>.'
            f'</p>'
        )
    else:
        prepend = (
            f'<h2 class="seo-subtitle">Gratis {subject} Kleurplaat Printen of Downloaden</h2>'
            f'<p>Op zoek naar een leuke <strong>{subject}</strong> kleurplaat? '
            f'Hier kun je deze tekening direct gratis printen of downloaden om in te kleuren.</p>'
        )
        append = (
            f'<p style="margin-top: 30px; font-style: italic;">'
            f'Zie ook onze andere '
            f'<a href="/kleurplaat_categorie/{subject_slug}/">{subject}</a>'
            f' kleurplaten.</p>'
        )

    return prepend, append


def update_post_taxonomy(post_id, item_ids, wp_url, auth, taxonomy_type, subject, subject_slug, colored):
    """Update post with taxonomy item IDs AND inject SEO content."""
    config = taxonomy_config(taxonomy_type)
    prepend, append = build_seo_content(subject, subject_slug, colored)

    api_base = f"{wp_url.rstrip('/')}/wp-json/wp/v2/kleurplaten/{post_id}"

    try:
        get_response = requests.get(
            api_base,
            auth=auth,
            timeout=REQUEST_TIMEOUT,
        )
    except RequestException as e:
        print(f"[ERROR] Failed to fetch post {post_id} content: {e}")
        return None

    if get_response.status_code != 200:
        print(f"[ERROR] Failed to fetch post {post_id}: status {get_response.status_code}")
        return None

    try:
        post_data = get_response.json()
    except (ValueError, json.JSONDecodeError) as e:
        print(f"[ERROR] Failed to parse post {post_id} response: {e}")
        return None

    current_content = post_data.get("content", {}).get("rendered", "")
    updated_content = f"{prepend}{current_content}{append}"

    try:
        response = requests.put(
            api_base,
            json={
                config["payload_key"]: item_ids,
                "content": updated_content,
            },
            auth=auth,
            timeout=REQUEST_TIMEOUT,
        )
    except RequestException as e:
        print(f"[ERROR] Failed to update post {post_id}: {e}")
        return None

    return response.status_code


def flush_wordpress_cache(wp_url, auth):
    """
    Stuur een request naar WordPress om de transient cache te wissen.
    Dit zorgt dat de frontend direct de nieuwe categorieën ziet.
    """
    try:
        response = requests.post(
            f"{wp_url.rstrip('/')}/wp-json/kk/v1/flush-cache",
            auth=auth,
            timeout=REQUEST_TIMEOUT,
        )
        if response.status_code == 200:
            print("[CACHE] WordPress cache flushed successfully")
        else:
            print(f"[CACHE] Warning: flush returned status {response.status_code}")
    except RequestException as e:
        print(f"[CACHE] Warning: failed to flush cache: {e}")


def process_batch(batch, batch_num, total_batches, cache, wp_url, auth, taxonomy_type, stats):
    """
    Verwerk één batch van posts.
    
    Args:
        batch: Lijst van posts in deze batch
        batch_num: Batch nummer (voor logging)
        total_batches: Totaal aantal batches
        cache: Term ID cache dict
        wp_url: WordPress URL
        auth: (username, app_password) tuple
        taxonomy_type: 'custom' of 'standard'
        stats: Dict met statistieken (wordt in-place bijgewerkt)
    
    Returns:
        Aantal succesvol verwerkte posts in deze batch
    """
    batch_success = 0
    
    for post in batch:
        post_id = post.get("id")
        title = post.get("title", {}).get("rendered", "")

        colored = is_colored(title)

        if colored:
            subject = COLORED_CATEGORY_NAME
            subject_slug = COLORED_CATEGORY_SLUG
        else:
            subject = extract_subject(title)

            if not subject:
                print(f"  [SKIP] Post ID {post_id} -> No subject found in title")
                stats["skipped"] += 1
                continue

            subject_slug = slugify(subject)

        taxonomy_item_id = get_or_create_taxonomy_item(subject, cache, wp_url, auth, taxonomy_type)

        if taxonomy_item_id:
            status_code = update_post_taxonomy(
                post_id, [taxonomy_item_id], wp_url, auth, taxonomy_type, subject, subject_slug, colored
            )
            
            if status_code == 200:
                print(f"  [OK]   Post ID {post_id} -> {subject} -> Tax ID: {taxonomy_item_id}")
                batch_success += 1
                stats["updated"] += 1
            else:
                print(f"  [FAIL] Post ID {post_id} -> Status: {status_code}")
                stats["failed"] += 1
        else:
            print(f"  [FAIL] Post ID {post_id} -> Failed to create taxonomy item for '{subject}'")
            stats["failed"] += 1

    return batch_success


def main():
    """Orchestrate the bulk categorization process with batch processing and rate limiting."""
    args = parse_args()
    config = load_config()
    wp_url = config["wp_url"]
    wp_user = config["wp_user"]
    wp_app_pass = config["wp_app_pass"]

    if not wp_user or not wp_app_pass:
        print("[ERROR] WORDPRESS_USERNAME and WORDPRESS_APP_PASSWORD must be set in .env")
        return

    auth = (wp_user, wp_app_pass)
    cache = {}

    print(f"[START] Fetching all posts from {wp_url}...")
    posts = fetch_all_posts(wp_url, auth)
    
    if not posts:
        print("[ERROR] No posts found. Exiting.")
        return
    
    print(f"[INFO] Found {len(posts)} posts to process")

    # Bereken aantal batches.
    total_batches = ceil(len(posts) / BATCH_SIZE)
    print(f"[INFO] Processing in {total_batches} batches of {BATCH_SIZE} posts each")
    print(f"[INFO] Rate limiting: {BATCH_DELAY}s delay after each batch")
    print()

    stats = {
        "total": len(posts),
        "updated": 0,
        "skipped": 0,
        "failed": 0,
    }

    start_time = time.time()

    # Verwerk posts in batches.
    for batch_num in range(total_batches):
        batch_start = batch_num * BATCH_SIZE
        batch_end = batch_start + BATCH_SIZE
        batch = posts[batch_start:batch_end]

        print(f"[BATCH {batch_num + 1}/{total_batches}] Processing posts {batch_start + 1}-{batch_end}...")

        batch_success = process_batch(
            batch, batch_num + 1, total_batches, cache, wp_url, auth, args.taxonomy_type, stats
        )

        # Na elke batch: pauze en cache flush.
        if batch_num < total_batches - 1:  # Niet na de laatste batch
            print(f"  [RATE LIMIT] Waiting {BATCH_DELAY}s...")
            time.sleep(BATCH_DELAY)

            # Flush WordPress cache zodat frontend de wijzigingen direct ziet.
            flush_wordpress_cache(wp_url, auth)

            elapsed = time.time() - start_time
            print(f"  [PROGRESS] {stats['updated']}/{stats['total']} updated, "
                  f"{batch_success} in this batch, elapsed: {elapsed:.1f}s")
            print()

    # Final flush.
    print("[FINAL] Flushing WordPress cache...")
    flush_wordpress_cache(wp_url, auth)

    elapsed = time.time() - start_time

    print()
    print("=" * 60)
    print("[DONE] Bulk categorization complete!")
    print(f"  Total posts:   {stats['total']}")
    print(f"  Updated:       {stats['updated']}")
    print(f"  Skipped:       {stats['skipped']}")
    print(f"  Failed:        {stats['failed']}")
    print(f"  Time elapsed:  {elapsed:.1f}s")
    print("=" * 60)


if __name__ == "__main__":
    main()
