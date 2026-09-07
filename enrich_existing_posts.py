#!/usr/bin/env python3
"""
enrich_existing_posts.py
========================

Dunne wrapper rond ``enrich_kleurplaat_content.enrich_existing_posts``
met dezelfde defaults als de oude ``fix_duplicate_seo_content.py``-runs.

Gebruik
-------
    # Droogloop voor 5 posts (geen WP API writes):
    python3 enrich_existing_posts.py --dry-run --limit 5

    # Echte run voor alle posts (met 0.5s pauze tussen calls):
    python3 enrich_existing_posts.py --delay 0.5

    # Verwerk de eerste 50 posts:
    python3 enrich_existing_posts.py --limit 50

Na deze run bevatten alle kleurplaat-posts 250-350 unieke woorden aan
content (intro + tabel + how-to + long-tail keywords), omhuld door
``<!-- KK-ENRICH-START --> ... <!-- KK-ENRICH-END -->`` markers zodat
de functie idempotent is en veilig meerdere keren kan worden gedraaid.

Vereisten
---------
- .env met WORDPRESS_URL, WORDPRESS_USERNAME, WORDPRESS_APP_PASSWORD
- Python packages: requests, python-dotenv
"""
from __future__ import annotations

import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).parent.resolve()
if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

from enrich_kleurplaat_content import enrich_existing_posts  # noqa: E402


def main() -> int:
    import argparse

    parser = argparse.ArgumentParser(
        description="Verrijk alle bestaande kleurplaat-posts met rijke SEO-content."
    )
    parser.add_argument("--dry-run", action="store_true", help="Alleen tonen, niet bijwerken.")
    parser.add_argument("--limit", type=int, default=0, help="Maximaal aantal te verwerken posts (0 = alles).")
    parser.add_argument("--delay", type=float, default=0.3, help="Seconden pauze tussen API-calls (default 0.3).")
    args = parser.parse_args()

    stats = enrich_existing_posts(
        dry_run=args.dry_run,
        limit=args.limit,
        delay_seconds=args.delay,
    )
    return 0 if stats.get("failed", 0) == 0 else 1


if __name__ == "__main__":
    sys.exit(main())
