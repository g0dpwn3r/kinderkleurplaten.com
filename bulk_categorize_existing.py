#!/usr/bin/env python3
import argparse
import json
import os
import time
from pathlib import Path

import requests
from dotenv import load_dotenv
from requests.exceptions import RequestException

SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
load_dotenv(dotenv_path=DOTENV_PATH)

PER_PAGE = 100
REQUEST_TIMEOUT = 15
CUSTOM_TAXONOMY = "kleurplaat_thema"
STANDARD_CATEGORY_ENDPOINT = "categories"
STANDARD_CATEGORY_PAYLOAD_KEY = "categories"


def load_config():
    """Load environment variables for WordPress auth."""
    return {
        "wp_url": os.getenv("WORDPRESS_URL", "http://94.130.141.198:8081"),
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
        help="Taxonomy to update: 'custom' uses kleurplaat_thema, 'standard' uses WordPress categories.",
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
    Slimmere extractie: verwijdert stopwoorden uit de titel en behoudt het kernonderwerp.
    """
    if not title:
        return None

    # Lijst van woorden die we NIET als categorie willen hebben (alles in kleine letters)
    stopwoorden = ["gratis", "kleurplaat", "kleurplaten", "van", "een", "voor", "kinderen"]
    
    # Hak de titel in losse woorden
    woorden = title.split()
    onderwerp_woorden = []

    for woord in woorden:
        # Haal leestekens weg en zet om naar kleine letters voor de check
        schoon_woord = woord.lower().strip(",.!?")
        
        if schoon_woord not in stopwoorden:
            # We bewaren het originele woord (met eventuele hoofdletters)
            onderwerp_woorden.append(woord)

    # Als we woorden over hebben, plakken we ze aan elkaar
    if onderwerp_woorden:
        return " ".join(onderwerp_woorden).capitalize()
    
    return None

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


def update_post_taxonomy(post_id, item_ids, wp_url, auth, taxonomy_type):
    """Update post with taxonomy item IDs via PUT."""
    config = taxonomy_config(taxonomy_type)

    try:
        response = requests.put(
            f"{wp_url.rstrip('/')}/wp-json/wp/v2/kleurplaten/{post_id}",
            json={config["payload_key"]: item_ids},
            auth=auth,
            timeout=REQUEST_TIMEOUT,
        )
    except RequestException as e:
        print(f"[ERROR] Failed to update post {post_id}: {e}")
        return None

    return response.status_code


def main():
    """Orchestrate the bulk categorization process."""
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

    posts = fetch_all_posts(wp_url, auth)
    print(f"Found {len(posts)} posts to process")

    for post in posts:
        post_id = post.get("id")
        title = post.get("title", {}).get("rendered", "")
        subject = extract_subject(title)

        if not subject:
            print(f"Post ID {post_id} -> No subject found in title")
            continue

        taxonomy_item_id = get_or_create_taxonomy_item(subject, cache, wp_url, auth, args.taxonomy_type)

        if taxonomy_item_id:
            status_code = update_post_taxonomy(post_id, [taxonomy_item_id], wp_url, auth, args.taxonomy_type)
            status = "Updated" if status_code == 200 else f"Failed ({status_code})"
            print(f"Post ID {post_id} -> Extracted: {subject} -> Taxonomy ID: {taxonomy_item_id} -> Status: {status}")
        else:
            print(f"Post ID {post_id} -> Extracted: {subject} -> Taxonomy ID: None -> Status: Failed to create taxonomy item")

        time.sleep(1)


if __name__ == "__main__":
    main()
