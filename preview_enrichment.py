#!/usr/bin/env python3
"""
preview_enrichment.py
=====================

Lokale preview: laat zien welke content de enrichment-module genereert
voor een gegeven thema, zonder de WordPress API aan te roepen.

Handig om:
- Snel te controleren of de output aan je verwachtingen voldoet
- Variatie tussen runs te vergelijken (de module is random-gebaseerd)
- Themadekking te testen voor onderwerpen die niet in de profiel-DB staan

Gebruik
-------
    python3 preview_enrichment.py
    python3 preview_enrichment.py --theme "Dinosaurussen" --runs 3
    python3 preview_enrichment.py --theme "Kerstmis" --runs 5 --with-factoid
"""
from __future__ import annotations

import argparse
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).parent.resolve()
if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

from enrich_kleurplaat_content import build_rich_content  # noqa: E402


DEFAULT_FACTOIDS = {
    "dinosaurussen": "Wetenschappers denken dat tyrannosaurus rex in groepjes leefde om grote prooien te vangen.",
    "kerstmis": "De traditie van de kerstboom stamt uit de 16e eeuw in Duitsland.",
    "eenhoorns": "Het woord 'eenhoorn' komt uit het Oudgrieks en betekent 'één hoorn'.",
    "regenboog": "Een regenboog ontstaat wanneer zonlicht door regendruppels wordt gebogen.",
}


def main() -> int:
    parser = argparse.ArgumentParser(description="Preview enrichment-output zonder WordPress.")
    parser.add_argument("--theme", type=str, default="Dinosaurussen", help="Thema om te previewen.")
    parser.add_argument("--runs", type=int, default=2, help="Aantal variaties om te tonen.")
    parser.add_argument("--with-factoid", action="store_true", help="Voeg een voorbeeld-factoid toe.")
    args = parser.parse_args()

    factoid = ""
    if args.with_factoid:
        factoid = DEFAULT_FACTOIDS.get(args.theme.lower(), f"Een leuk weetje over {args.theme}!")

    for i in range(1, args.runs + 1):
        print("=" * 78)
        print(f"VARIANTIE {i} — thema: {args.theme}")
        print("=" * 78)
        print(build_rich_content(args.theme, factoid))
        print()
    return 0


if __name__ == "__main__":
    sys.exit(main())
