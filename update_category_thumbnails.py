#!/usr/bin/env python3
"""
Update termmeta thumbnail_id voor alle 'kleurplaat_categorie' termen.

Dit script roept de WordPress REST API endpoint aan die door de mu-plugin
'kk-category-thumbnail-sync.php' wordt geregistreerd. Die plugin doet het
eigenlijke database-werk via $wpdb (dus binnen de WordPress container).

Voor elke categorie wordt de meest recente 'attachment' gezocht die via de
post-meta tabel (_thumbnail_id) aan een kleurplaat in die categorie gekoppeld is.
De gevonden attachment-ID wordt opgeslagen als 'thumbnail_id' in wp_termmeta,
zodat de PHP-functie kk_get_all_categories_with_thumbnails() snel de thumbnail
kan ophalen zonder dure WP_Query per term.

Authenticatie:
  Via WordPress Application Passwords uit .env (WORDPRESS_USERNAME + WORDPRESS_APP_PASSWORD).

Gebruik:
  python3 update_category_thumbnails.py                 # dry-run (geen writes)
  python3 update_category_thumbnails.py --apply         # termmeta daadwerkelijk updaten
  python3 update_category_thumbnails.py --apply -v      # verbose output
"""

import argparse
import json
import os
import sys
from pathlib import Path

import requests
from dotenv import load_dotenv

# ---------------------------------------------------------------------------
# Configuratie
# ---------------------------------------------------------------------------
SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
load_dotenv(dotenv_path=DOTENV_PATH)

WP_URL = os.getenv("WORDPRESS_URL", "https://kinderkleurplaten.com")
WP_USER = os.getenv("WORDPRESS_USERNAME")
WP_APP_PASS = os.getenv("WORDPRESS_APP_PASSWORD")

REQUEST_TIMEOUT = 30
REST_ENDPOINT = "/wp-json/kk/v1/update-category-thumbnails"


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def load_config() -> dict:
    """Laad en valideer configuratie."""
    if not WP_USER or not WP_APP_PASS:
        sys.exit(
            "[FOUT] WORDPRESS_USERNAME en WORDPRESS_APP_PASSWORD "
            "moeten ingesteld zijn in .env"
        )
    return {
        "wp_url": WP_URL.rstrip("/"),
        "auth": (WP_USER, WP_APP_PASS),
    }


def call_endpoint(wp_url: str, auth: tuple, dry_run: bool) -> dict:
    """
    Roep de REST endpoint aan en geef de JSON response terug.
    """
    url = f"{wp_url}{REST_ENDPOINT}"
    payload = {"dry_run": dry_run}

    print(f"[INFO] POST {url}")
    print(f"[INFO] dry_run = {dry_run}")
    print()

    try:
        response = requests.post(
            url,
            json=payload,
            auth=auth,
            timeout=REQUEST_TIMEOUT,
        )
    except requests.exceptions.ConnectionError as exc:
        sys.exit(f"[FOUT] Kan geen verbinding maken met {wp_url}: {exc}")
    except requests.exceptions.Timeout:
        sys.exit(f"[FOUT] Timeout bij het verbinden met {wp_url}")
    except requests.exceptions.RequestException as exc:
        sys.exit(f"[FOUT] Request mislukt: {exc}")

    if response.status_code == 401:
        sys.exit(
            "[FOUT] Authenticatie mislukt (401). "
            "Controleer WORDPRESS_USERNAME en WORDPRESS_APP_PASSWORD in .env"
        )
    if response.status_code == 403:
        sys.exit(
            "[FOUT] Geen rechten (403). De gebruiker heeft 'manage_options' nodig."
        )

    if response.status_code != 200:
        sys.exit(
            f"[FOUT] Endpoint gaf status {response.status_code}\n"
            f"  Body: {response.text[:500]}"
        )

    try:
        return response.json()
    except (ValueError, json.JSONDecodeError) as exc:
        sys.exit(f"[FOUT] Ongeldige JSON response: {exc}\n  Body: {response.text[:500]}")


def format_results(data: dict, dry_run: bool, verbose: bool) -> None:
    """
    Format en print de resultaten van de endpoint aanroep.
    """
    total_terms = data.get("total_terms", 0)
    results = data.get("results", [])
    summary = data.get("summary", {})

    print(f"[INFO] {total_terms} termen gevonden in 'kleurplaat_categorie'.")
    print()

    # --- Per-term resultaten tonen ---
    for entry in results:
        term_id = entry.get("term_id", "?")
        name = entry.get("name", "?")
        attachment_id = entry.get("attachment_id")
        prev = entry.get("previous_value")
        action = entry.get("action", "unknown")

        match action:
            case "would_update":
                print(
                    f"  [ZOU_UPDATEN] term_id={term_id} '{name}' — "
                    f"thumbnail_id: {prev or '(leeg)'} → {attachment_id}"
                )
            case "updated":
                print(
                    f"  [UPDATE]      term_id={term_id} '{name}' — "
                    f"thumbnail_id: {prev or '(leeg)'} → {attachment_id}"
                )
            case "unchanged":
                if verbose:
                    print(f"  [=]           term_id={term_id} '{name}' — onveranderd ({attachment_id})")
            case "no_attachment":
                if verbose:
                    print(f"  [—]           term_id={term_id} '{name}' — geen attachment gevonden")
            case "invalid_attachment":
                print(
                    f"  [SKIP]        term_id={term_id} '{name}' — "
                    f"attachment_id={attachment_id} bestaat niet (ongeldig)"
                )
            case _:
                if verbose:
                    print(f"  [?]           term_id={term_id} '{name}' — actie: {action}")

    # --- Samenvatting ---
    print()
    print("=" * 60)
    print("  SAMENVATTING")
    print("=" * 60)
    print(f"  Termen totaal:          {summary.get('total', total_terms)}")
    print(f"  Bijgewerkt:             {summary.get('updated', 0)}")
    print(f"  Ongewijzigd:            {summary.get('unchanged', 0)}")
    print(f"  Geen attachment:        {summary.get('no_attachment', 0)}")
    print(f"  Ongeldig attachment:    {summary.get('invalid_attachment', 0)}")
    print("=" * 60)

    if dry_run and summary.get("updated", 0) > 0:
        print()
        print("  Tip: voer uit met  --apply  om de wijzigingen door te voeren.")


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def main():
    parser = argparse.ArgumentParser(
        description="Update termmeta thumbnail_id voor kleurplaat_categorie termen "
                    "via WordPress REST API."
    )
    parser.add_argument(
        "--apply",
        action="store_true",
        help="Termmeta daadwerkelijk updaten (zonder deze flag = dry-run).",
    )
    parser.add_argument(
        "-v", "--verbose",
        action="store_true",
        help="Toon ook termen zonder wijzigingen.",
    )
    parser.add_argument(
        "--json",
        action="store_true",
        help="Toon ruwe JSON response (voor debugging).",
    )
    args = parser.parse_args()

    dry_run = not args.apply
    verbose = args.verbose

    print("=" * 60)
    print("  Category Thumbnail Sync")
    print(f"  WordPress: {WP_URL}")
    print(f"  Modus:     {'DRY-RUN (geen writes)' if dry_run else 'APPLY (schrijft termmeta)'}")
    print("=" * 60)
    print()

    config = load_config()

    # --- Roep de REST endpoint aan ---
    data = call_endpoint(config["wp_url"], config["auth"], dry_run)

    if args.json:
        print(json.dumps(data, indent=2, ensure_ascii=False))
        return

    format_results(data, dry_run, verbose)


if __name__ == "__main__":
    main()
