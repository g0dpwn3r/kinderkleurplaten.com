#!/usr/bin/env python3
"""
Fix duplicated SEO intro/footer blocks in kleurplaten posts.

Root cause: bulk_categorize_existing.py prepended SEO content to the
'rendered' version of each post. Running the script multiple times (or
on already-categorized posts) caused the SEO block to be injected
twice, three times, etc.

This script:
  1. Fetches posts via the WP REST API.
  2. Strips ALL existing "seo-subtitle" <h2> + <p> intro blocks.
  3. Strips ALL existing <p> footer blocks ("Zie ook..." / "Bekijk...").
  4. Rebuilds the content with ONE intro block and ONE footer.
  5. Updates the post.

Usage:
  python3 fix_duplicate_seo_content.py              # apply fixes
  python3 fix_duplicate_seo_content.py --dry-run    # preview only
  python3 fix_duplicate_seo_content.py --limit=5    # fix only first 5

Requires .env with WORDPRESS_URL, WORDPRESS_USERNAME, WORDPRESS_APP_PASSWORD.
"""

import argparse
import json
import os
import re
import sys
from pathlib import Path
from dotenv import load_dotenv
import requests

SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
load_dotenv(dotenv_path=DOTENV_PATH)

WP_URL = os.getenv("WORDPRESS_URL")
WP_USERNAME = os.getenv("WORDPRESS_USERNAME")
WP_APP_PASSWORD = os.getenv("WORDPRESS_APP_PASSWORD")

COLORED_CATEGORY_SLUG = "ingekleurd"
COLORED_CATEGORY_NAME = "Ingekleurde kleurplaten"


def slugify(text: str) -> str:
    text = text.lower().strip()
    text = re.sub(r"[^\w\s-]", "", text)
    text = re.sub(r"[\s_-]+", "-", text)
    text = re.sub(r"^-+|-+$", "", text)
    return text


def extract_subject(title: str) -> str:
    if not title:
        return ""

    title = re.sub(r"\s*(Kleurplaat|Gratis|Printen|Downloaden|Print|Download)\s*", " ", title, flags=re.IGNORECASE)
    title = title.strip()
    return title


def is_colored(title: str) -> bool:
    if not title:
        return False
    lower = title.lower()
    return any(
        kw in lower
        for kw in ("ingekleurd", "gekleurd", "kleurrijk voorbeeld", "gekleurde versie")
    )


def build_intro(subject: str, colored: bool) -> str:
    if colored:
        return (
            f'<h2 class="seo-subtitle">Prachtig Ingekleurd Voorbeeld: {subject}</h2>'
            f'<p>Bekijk dit mooi <strong>ingekleurde voorbeeld</strong> van een {subject}. '
            f'Gebruik deze afbeelding ter inspiratie voor je eigen tekeningen!</p>'
        )
    return (
        f'<h2 class="seo-subtitle">Gratis {subject} Kleurplaat Printen of Downloaden</h2>'
        f'<p>Op zoek naar een leuke <strong>{subject}</strong> kleurplaat? '
        f'Hier kun je deze tekening direct gratis printen of downloaden om in te kleuren.</p>'
    )


def build_footer(subject: str, subject_slug: str, colored: bool) -> str:
    if colored:
        return (
            f'<p style="margin-top: 30px; font-style: italic;">'
            f'Bekijk onze galerij voor meer '
            f'<a href="/kleurplaat_categorie/{COLORED_CATEGORY_SLUG}/">{COLORED_CATEGORY_NAME}</a>.'
            f'</p>'
        )
    return (
        f'<p style="margin-top: 30px; font-style: italic;">'
        f'Zie ook onze andere '
        f'<a href="/kleurplaat_categorie/{subject_slug}/">{subject}</a> kleurplaten.</p>'
    )


def strip_seo_blocks(html: str) -> str:
    """Verwijdert ALLE seo-subtitle <h2>+<p> intro- en <p> footer-blokken."""
    # Intro: <h2 class="seo-subtitle">...</h2><p>...</p>
    # Non-greedy match per blok; DOTALL zodat we over lijnen heen matchen.
    intro_pattern = (
        r'\s*<h2\s+class=["\']seo-subtitle["\'][^>]*>.*?</h2>\s*'
        r'<p[^>]*>.*?</p>\s*'
    )
    html = re.sub(intro_pattern, '\n', html, flags=re.DOTALL | re.IGNORECASE)

    # Footer: <p style="...">Zie ook / Bekijk ...</p>
    footer_pattern = (
        r'\s*<p[^>]*style=["\'][^"\']*["\'][^>]*>\s*'
        r'(?:Zie ook|Bekijk).*?</p>\s*'
    )
    html = re.sub(footer_pattern, '\n', html, flags=re.DOTALL | re.IGNORECASE)

    # Plain "Zie ook" of "Bekijk" <p> (zonder style)
    footer_plain = (
        r'\s*<p[^>]*>\s*(?:Zie ook onze andere|Bekijk onze galerij).*?</p>\s*'
    )
    html = re.sub(footer_plain, '\n', html, flags=re.DOTALL | re.IGNORECASE)

    # Ruim lege <p>-tags op.
    html = re.sub(r'\s*<p[^>]*>\s*</p>\s*', '', html)

    # Meerdere lege regels terugbrengen naar max 2.
    html = re.sub(r'(\s*\n\s*){3,}', '\n\n', html)

    return html.strip()


def has_duplicate_content(html: str) -> bool:
    """Detecteert of de SEO-blocks al gedupliceerd zijn in de content."""
    h2_count = len(re.findall(r'class=["\']seo-subtitle["\']', html, flags=re.IGNORECASE))
    if h2_count > 1:
        return True
    # Footer: meerdere "Zie ook" / "Bekijk" lijnen
    footer_count = len(re.findall(r'z(?:ie ook|ie ook onze andere|ien ook)', html, flags=re.IGNORECASE))
    if footer_count > 1:
        return True
    return False


def fix_post_content(html: str, subject: str, subject_slug: str, colored: bool) -> str:
    """
    Stript alle bestaande SEO-blokken en voegt precies één intro + één footer
    toe rond de body-content.
    """
    body = strip_seo_blocks(html)
    intro = build_intro(subject, colored)
    footer = build_footer(subject, subject_slug, colored)
    return f"{intro}\n{body}\n{footer}"


def get_all_posts(wp_url: str, auth, limit: int = 0):
    """Haalt alle kleurplaten op met context=edit (voor 'raw' content)."""
    posts = []
    page = 1
    while True:
        params = {
            "per_page": 100,
            "page": page,
            "status": "publish",
            "context": "edit",
        }
        try:
            resp = requests.get(
                f"{wp_url}/wp-json/wp/v2/kleurplaten",
                auth=auth,
                params=params,
                timeout=30,
            )
            resp.raise_for_status()
            batch = resp.json()
        except Exception as e:
            print(f"[ERROR] pagina {page}: {e}")
            break

        if not batch:
            break
        posts.extend(batch)
        page += 1
        if limit and len(posts) >= limit:
            posts = posts[:limit]
            break

    return posts


def update_post(wp_url, auth, post_id, content):
    try:
        resp = requests.put(
            f"{wp_url}/wp-json/wp/v2/kleurplaten/{post_id}",
            auth=auth,
            json={"content": content},
            timeout=30,
        )
        resp.raise_for_status()
        return True
    except Exception as e:
        print(f"    [ERROR] Update mislukt voor post {post_id}: {e}")
        return False


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument("--limit", type=int, default=0)
    args = parser.parse_args()

    if not all([WP_URL, WP_USERNAME, WP_APP_PASSWORD]):
        print("FOUT: Stel WORDPRESS_URL, WORDPRESS_USERNAME en WORDPRESS_APP_PASSWORD in in .env")
        sys.exit(1)

    auth = (WP_USERNAME, WP_APP_PASSWORD)
    base_url = WP_URL.rstrip("/")

    posts = get_all_posts(base_url, auth, args.limit)
    print(f"Gevonden: {len(posts)} kleurplaten")

    fixed = 0

    for post in posts:
        post_id = post.get("id")
        title = post.get("title", {}).get("rendered", "")

        raw = post.get("content", {}).get("raw", "")
        rendered = post.get("content", {}).get("rendered", "")
        content = raw if raw else rendered

        if not has_duplicate_content(content) and not has_duplicate_content(rendered):
            continue

        colored = is_colored(title)
        if colored:
            subject = COLORED_CATEGORY_NAME
            slug = COLORED_CATEGORY_SLUG
        else:
            subject = extract_subject(title)
            if not subject:
                continue
            slug = slugify(subject)

        new_content = fix_post_content(content, subject, slug, colored)

        if new_content == content:
            continue

        fixed += 1
        print(f"\n[{post_id}] {title}")
        snippet = re.sub(r'<[^>]+>', '', new_content)
        print(f"  NIEUW preview: {snippet[:250]}...")

        if not args.dry_run:
            if update_post(base_url, auth, post_id, new_content):
                print(f"  ✓ Bijgewerkt")
            else:
                print(f"  ✗ Mislukt")
        else:
            print(f"  [DRY-RUN] Zou bijwerken")

    print(f"\n[{'DRY-RUN ' if args.dry_run else ''}]{fixed} post(s) {'zouden' if args.dry_run else 'zijn'} aangepast.")


if __name__ == "__main__":
    main()
