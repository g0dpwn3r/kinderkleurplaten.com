#!/usr/bin/env python3
"""
Fix duplicate paragraphs in kleurplaten posts.
Addresses the issue where content like 'Gratis Herfstse geheimen... Zie ook...'
appears twice in the post content.

Usage: python3 fix_duplicate_content.py [--dry-run]
"""

import argparse
import json
import os
import re
import sys
from pathlib import Path
from dotenv import load_dotenv
import requests

# Setup
SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
load_dotenv(dotenv_path=DOTENV_PATH)

WP_URL = os.getenv("WORDPRESS_URL")
WP_USERNAME = os.getenv("WORDPRESS_USERNAME")
WP_APP_PASSWORD = os.getenv("WORDPRESS_APP_PASSWORD")


def deduplicate_paragraphs(html_content: str) -> str:
    """
    Removes consecutive duplicate paragraphs from HTML content.
    Also removes exact duplicate sentences within the content.
    """
    if not html_content:
        return html_content

    # Strategy 1: Remove consecutive duplicate <p> tags (exact match)
    html_content = re.sub(
        r'(<p[^>]*>\s*(.*?)\s*</p>)\s*<p[^>]*>\s*\2\s*</p>',
        r'\1',
        html_content,
        flags=re.DOTALL
    )

    # Strategy 2: Split into sentences and remove duplicate sentences
    # This handles cases where the same sentence appears multiple times
    sentences = re.split(r'(?<=[.!?])\s+', html_content)
    seen = []
    result = []
    for sentence in sentences:
        # Normalize for comparison
        normalized = re.sub(r'\s+', ' ', sentence.strip())
        if normalized not in seen and normalized:
            seen.append(normalized)
            result.append(sentence)

    fixed_content = ' '.join(result)

    return fixed_content


def fix_translation_errors(text: str) -> str:
    """
    Fixes common Dutch translation errors in AI-generated content.
    """
    if not text:
        return text

    # "Herfstse" should be "Herfst" or combined into compound words
    # e.g., "Herfstse geheimen" -> "Herfstgeheimen"
    replacements = [
        (r'\bHerfstse\s+geheimen\b', 'Herfstgeheimen'),
        (r'\bherfstse\s+geheimen\b', 'herfstgeheimen'),
        (r'\bHerfstse\s+bloemen\b', 'Herfstbloemen'),
        (r'\bherfstse\s+bloemen\b', 'herfstbloemen'),
        (r'\bHerfstse\s+bladeren\b', 'Herfstbladeren'),
        (r'\bherfstse\s+bladeren\b', 'herfstbladeren'),
        (r'\bHerfstse\s+velden\b', 'Herfstvelden'),
        (r'\bherfstse\s+velden\b', 'herfstvelden'),
        (r'\bHerfstse\s+natuur\b', 'Herfstnatuur'),
        (r'\bherfstse\s+natuur\b', 'herfstnatuur'),
        # General pattern: "Xse Y" where X is a noun -> "XY"
        # Be careful with this one as it might not always apply
        (r'\b([A-Za-z]+)se\s+([a-z]+)\b', lambda m: _check_compound(m.group(1).lower(), m.group(2).lower(), m.group(0))),
    ]

    for pattern, replacement in replacements:
        if callable(replacement):
            text = re.sub(pattern, replacement, text, flags=re.IGNORECASE)
        else:
            text = re.sub(pattern, replacement, text, flags=re.IGNORECASE)

    return text


def _check_compound(noun: str, modifier: str, original: str) -> str:
    """
    Helper: checks if the noun-modifier combination should be a compound word.
    Returns the corrected text or original if not applicable.
    """
    # Common nouns that form compound words in Dutch
    compound_nouns = {
        'herfst', 'winter', 'zomer', 'lente',
        'bos', 'zee', 'strand', 'berg', 'tuin',
        'dier', 'hond', 'kat', 'vogel', 'vis',
        'water', 'vuur', 'lucht', 'aarde',
        'zon', 'maan', 'ster', 'wolk',
    }

    if noun in compound_nouns:
        return noun + modifier
    return original


def get_all_kleurplaten_posts() -> list:
    """Fetches all kleurplaten posts from WordPress."""
    posts = []
    page = 1

    while True:
        try:
            resp = requests.get(
                f"{WP_URL}/wp-json/wp/v2/kleurplaten",
                auth=(WP_USERNAME, WP_APP_PASSWORD),
                params={
                    "per_page": 50,
                    "page": page,
                    "status": "publish"
                },
                timeout=30,
            )
            resp.raise_for_status()
            batch = resp.json()

            if not batch:
                break

            posts.extend(batch)
            page += 1

        except requests.exceptions.RequestException as e:
            print(f"[FOUT] Ophalen posts mislukt op pagina {page}: {e}")
            break
        except (ValueError, json.JSONDecodeError) as e:
            print(f"[FOUT] Ongeldig antwoord bij pagina {page}: {e}")
            break

    return posts


def update_post_content(post_id: int, new_content: str) -> bool:
    """Updates a post's content via WordPress REST API."""
    try:
        resp = requests.put(
            f"{WP_URL}/wp-json/wp/v2/kleurplaten/{post_id}",
            auth=(WP_USERNAME, WP_APP_PASSWORD),
            json={"content": new_content},
            timeout=30,
        )
        resp.raise_for_status()
        return True
    except requests.exceptions.RequestException as e:
        print(f"[FOUT] Bijwerken post {post_id} mislukt: {e}")
        return False
    except (ValueError, json.JSONDecodeError) as e:
        print(f"[FOUT] Ongeldig antwoord bij bijwerken post {post_id}: {e}")
        return False


def main():
    parser = argparse.ArgumentParser(description="Fix duplicate content in kleurplaten posts")
    parser.add_argument("--dry-run", action="store_true", help="Show what would be changed without making changes")
    args = parser.parse_args()

    if not all([WP_URL, WP_USERNAME, WP_APP_PASSWORD]):
        print("FOUT: Stel WORDPRESS_URL, WORDPRESS_USERNAME en WORDPRESS_APP_PASSWORD in in .env")
        sys.exit(1)

    print(f"Ophalen kleurplaten posts van {WP_URL}...")
    posts = get_all_kleurplaten_posts()
    print(f"({len(posts)} posts gevonden)")

    fixed_count = 0

    for post in posts:
        post_id = post.get("id")
        title = post.get("title", {}).get("rendered", "Untitled")
        content = post.get("content", {}).get("rendered", "")

        if not content:
            continue

        # Fix the content
        fixed_content = fix_translation_errors(content)
        fixed_content = deduplicate_paragraphs(fixed_content)

        # Check if anything changed
        if fixed_content == content:
            continue

        # Strip tags for comparison (to avoid HTML differences)
        original_text = re.sub(r'<[^>]+>', '', content).strip()
        fixed_text = re.sub(r'<[^>]+>', '', fixed_content).strip()

        if original_text == fixed_text:
            continue

        fixed_count += 1

        print(f"\n[{post_id}] {title}")
        print(f"  Origineel ({len(content)} chars): {content[:200]}...")
        print(f"  Aangepast ({len(fixed_content)} chars): {fixed_content[:200]}...")

        if not args.dry_run:
            if update_post_content(post_id, fixed_content):
                print(f"  ✓ Post {post_id} bijgewerkt")
            else:
                print(f"  ✗ Bijwerken mislukt")
        else:
            print(f"  [DRY-RUN] Zou post {post_id} bijwerken")

    print(f"\n{'[DRY-RUN] ' if args.dry_run else ''}{fixed_count} posts {'zouden' if args.dry_run else 'zijn'} aangepast.")


if __name__ == "__main__":
    main()
