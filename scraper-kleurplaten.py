#!/usr/bin/env python3

import argparse
import json
import re
import time
import requests
import io
import os
from pathlib import Path
from dotenv import load_dotenv
from huggingface_hub import InferenceClient

# Setup
SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
load_dotenv(dotenv_path=DOTENV_PATH)

HUGGINGFACE_TOKEN = os.getenv("HF_TOKEN")
WP_URL = os.getenv("WORDPRESS_URL")
WP_USERNAME = os.getenv("WORDPRESS_USERNAME")
WP_APP_PASSWORD = os.getenv("WORDPRESS_APP_PASSWORD")

# Initialiseer de client voor tekstgeneratie (Llama 3.1)
client = InferenceClient(api_key=HUGGINGFACE_TOKEN)

# URL voor FLUX afbeeldingen
IMAGE_API_URL = "https://router.huggingface.co/hf-inference/v1/models/black-forest-labs/FLUX.1-schnell"

def generate_metadata(theme: str) -> dict:
    """Genereert metadata via InferenceClient (voorkomt 400 errors)."""
    system_prompt = (
        "Je bent een creatieve assistent voor een kleurplaten-website. "
        "Geef enkel een JSON-object terug met: 'title' (aantrekkelijke Nederlandse titel), "
        "'image_prompt' (een gedetailleerde prompt in het Engels voor een kleurplaat: "
        "zwart-witte lijntekening, dikke zwarte contouren, witte achtergrond, minimalistisch), "
        "en 'factoid' (een interessant weetje in het Nederlands over het thema)."
    )
    
    try:
        completion = client.chat.completions.create(
            model="meta-llama/Llama-3.1-8B-Instruct",
            messages=[
                {"role": "system", "content": system_prompt},
                {"role": "user", "content": f"Thema: {theme}"}
            ],
            temperature=0.7
        )
        
        content = completion.choices[0].message.content
        match = re.search(r"\{.*\}", content, re.DOTALL)
        if match:
            return json.loads(match.group(0))
    except Exception as e:
        print(f"Fout bij metadata: {e}")
        
    return {
        "title": f"Kleurplaat {theme}", 
        "image_prompt": f"Simple coloring page of {theme}, black and white line art, white background.", 
        "factoid": "Veel plezier met kleuren!"
    }

POPULAR_THEMES = [
    "Dieren", "Boerderijdieren", "Dinosaurussen", "Dieren in het bos", "Onderwaterwereld",
    "Vlinders", "Bijen en bloemen", "Katten", "Honden", "Paarden",
    "Vogels", "Eenden", "Kippen", "Konijntjes", "Hertjes",
    "Eenhoorns", "Draken", "Feeën", "Zeemeerminnen", "Feeënhuisjes",
    "Kastelen", "Prinsen en prinsessen", "Ridders", "Magische bossen", "Sterren en maan",
    "Ruimte", "Planeten", "Raketten", "Auto's", "Vrachtwagens",
    "Treinen", "Vliegtuigen", "Schepen", "Fietsen", "Bouwplaats",
    "Boerderij", "Tuin", "Bloemen", "Bomen", "Paddestoelen",
    "Groenten", "Fruit", "IJsjes", "Taarten", "Speelgoed",
    "Ballonnen", "Regenboog", "Harten", "Sport", "Voetbal",
    "Zee", "Strand", "Bergen", "Weer", "Seizoenen",
    "Lente", "Zomer", "Herfst", "Winter", "Sinterklaas",
    "Kerst", "Pasen", "Verjaardag", "Carnaval", "Halloween"
]

def set_media_alt(media_id: int, alt: str):
    updates = {"alt_text": alt}
    url = f"{WP_URL}/wp-json/wp/v2/media/{media_id}"
    resp = requests.post(url, auth=(WP_USERNAME, WP_APP_PASSWORD), json=updates)
    if resp.status_code not in (200, 201):
        print(f"WP Alt update fout voor media {media_id}: {resp.text}")

# ---------------------------------------------------------------------------
# Term Management (WordPress Custom Taxonomy: kleurplaat_thema)
# ---------------------------------------------------------------------------

CUSTOM_TAXONOMY = "kleurplaat_thema"

def get_or_create_term(term_name: str) -> int | None:
    """
    Zoekt een term op in de custom taxonomy kleurplaat_thema; maakt hem aan als hij niet bestaat.
    Retourneert de term ID of None bij een fout.
    """
    if not term_name or not term_name.strip():
        return None

    term_name = term_name.strip()

    try:
        search_resp = requests.get(
            f"{WP_URL}/wp-json/wp/v2/{CUSTOM_TAXONOMY}",
            auth=(WP_USERNAME, WP_APP_PASSWORD),
            params={"search": term_name, "per_page": 10},
            timeout=10,
        )
        search_resp.raise_for_status()
        terms = search_resp.json()

        for term in terms:
            if term.get("name", "").lower() == term_name.lower():
                term_id = term.get("id")
                print(f"[WP] Term gevonden: '{term_name}' (ID {term_id})")
                return term_id

    except requests.exceptions.RequestException as e:
        print(f"[WP] Term zoeken mislukt voor '{term_name}': {e}")
        return None
    except (ValueError, json.JSONDecodeError) as e:
        print(f"[WP] Ongeldig antwoord bij zoeken term '{term_name}': {e}")
        return None

    try:
        create_resp = requests.post(
            f"{WP_URL}/wp-json/wp/v2/{CUSTOM_TAXONOMY}",
            auth=(WP_USERNAME, WP_APP_PASSWORD),
            json={"name": term_name},
            timeout=10,
        )
        create_resp.raise_for_status()
        new_term = create_resp.json()
        term_id = new_term.get("id")
        print(f"[WP] Nieuwe term aangemaakt: '{term_name}' (ID {term_id})")
        return term_id

    except requests.exceptions.RequestException as e:
        print(f"[WP] Term aanmaken mislukt voor '{term_name}': {e}")
        return None
    except (ValueError, json.JSONDecodeError) as e:
        print(f"[WP] Ongeldig antwoord bij aanmaken term '{term_name}': {e}")
        return None


def get_or_create_terms(term_names: list[str]) -> list[int]:
    """
    Verwerkt een lijst van termennamen en retourneert een lijst van IDs.
    Sla termen met fouten over, retourneert lege lijst bij geen invoer.
    """
    if not term_names:
        return []

    term_ids: list[int] = []
    for name in term_names:
        if not name:
            continue
        term_id = get_or_create_term(name)
        if term_id is None:
            print(f"[WP] Waarschuwing: term '{name}' kon niet verwerkt worden, overslaan.")
            continue
        term_ids.append(term_id)

    return term_ids


def update_post_categories(post_id: int, term_ids: list[int]) -> bool:
    """
    Werk een bestaande kleurplaat post bij met term IDs.
    Retourneert True bij succes, False bij fout.
    """
    if not term_ids:
        print(f"[WP] Geen term IDs om toe te wijzen aan post {post_id}")
        return False

    try:
        update_resp = requests.put(
            f"{WP_URL}/wp-json/wp/v2/kleurplaten/{post_id}",
            auth=(WP_USERNAME, WP_APP_PASSWORD),
            json={CUSTOM_TAXONOMY: term_ids},
            timeout=10,
        )
        update_resp.raise_for_status()
        print(f"[WP] Post {post_id} bijgewerkt met term IDs: {term_ids}")
        return True

    except requests.exceptions.RequestException as e:
        print(f"[WP] Post {post_id} updaten mislukt: {e}")
        return False
    except (ValueError, json.JSONDecodeError) as e:
        print(f"[WP] Ongeldig antwoord bij updaten post {post_id}: {e}")
        return False


def categorize_existing_images():
    """
    Haalt alle bestaande kleurplaten posts op en categoriseert ze op basis van
    thema keywords in hun titels.
    """
    try:
        posts_resp = requests.get(
            f"{WP_URL}/wp-json/wp/v2/kleurplaten",
            auth=(WP_USERNAME, WP_APP_PASSWORD),
            params={"per_page": 100, "page": 1},
            timeout=30,
        )
        posts_resp.raise_for_status()
        posts = posts_resp.json()

    except requests.exceptions.RequestException as e:
        print(f"[WP] Ophalen posts mislukt: {e}")
        return
    except (ValueError, json.JSONDecodeError) as e:
        print(f"[WP] Ongeldig antwoord bij ophalen posts: {e}")
        return

    print(f"[WP] {len(posts)} posts gevonden, verwerzen...")

    for post in posts:
        post_id = post.get("id")
        title = post.get("title", {})
        title_text = title.get("rendered", "") if isinstance(title, dict) else str(title)

        matched_terms: list[str] = []
        title_lower = title_text.lower()

        for theme in POPULAR_THEMES:
            if theme.lower() in title_lower:
                matched_terms.append(theme)

        if not matched_terms:
            if "dier" in title_lower:
                matched_terms.append("Dieren")
            elif "dinosaurussen" in title_lower or "dinosaurus" in title_lower:
                matched_terms.append("Dinosaurussen")
            elif "konijn" in title_lower:
                matched_terms.append("Konijntjes")

        if matched_terms:
            term_ids = get_or_create_terms(matched_terms)
            if term_ids:
                update_post_categories(post_id, term_ids)
        else:
            print(f"[WP] Geen matching termen voor post {post_id}: '{title_text}'")

    print("[WP] Categorisering voltooid.")


# ---------------------------------------------------------------------------
# Category Management (WordPress REST API)
# ---------------------------------------------------------------------------

def get_or_create_category(category_name: str) -> int | None:
    """
    Zoekt een categorie op naam; maakt hem aan als hij niet bestaat.
    Retourneert de category_id of None bij een fout.
    """
    if not category_name or not category_name.strip():
        return None

    category_name = category_name.strip()

    # 1. Zoek bestaande categorie
    try:
        search_resp = requests.get(
            f"{WP_URL}/wp-json/wp/v2/categories",
            auth=(WP_USERNAME, WP_APP_PASSWORD),
            params={"search": category_name, "per_page": 10},
            timeout=10,
        )
        search_resp.raise_for_status()
        categories = search_resp.json()

        # Exacte match op naam (hoofdletterongevoelig)
        for cat in categories:
            if cat.get("name", "").lower() == category_name.lower():
                cat_id = cat.get("id")
                print(f"[WP] Categorie gevonden: '{category_name}' (ID {cat_id})")
                return cat_id

    except requests.exceptions.RequestException as e:
        print(f"[WP] Categorie zoeken mislukt voor '{category_name}': {e}")
        return None
    except (ValueError, json.JSONDecodeError) as e:
        print(f"[WP] Ongeldig antwoord bij zoeken categorie '{category_name}': {e}")
        return None

    # 2. Niet gevonden → aanmaken
    try:
        create_resp = requests.post(
            f"{WP_URL}/wp-json/wp/v2/categories",
            auth=(WP_USERNAME, WP_APP_PASSWORD),
            json={"name": category_name},
            timeout=10,
        )
        create_resp.raise_for_status()
        new_cat = create_resp.json()
        cat_id = new_cat.get("id")
        print(f"[WP] Nieuwe categorie aangemaakt: '{category_name}' (ID {cat_id})")
        return cat_id

    except requests.exceptions.RequestException as e:
        print(f"[WP] Categorie aanmaken mislukt voor '{category_name}': {e}")
        return None
    except (ValueError, json.JSONDecodeError) as e:
        print(f"[WP] Ongeldig antwoord bij aanmaken categorie '{category_name}': {e}")
        return None


def get_or_create_categories(category_names: list[str]) -> list[int] | None:
    """
    Verwerkt een lijst van categorienamen en retourneert een lijst van IDs.
    Retourneert None als een van de categorien niet verwerkt kan worden.
    """
    if not category_names:
        return []

    category_ids: list[int] = []
    for name in category_names:
        if not name:
            continue
        cat_id = get_or_create_category(name)
        if cat_id is None:
            print(f"[WP]Waarschuwing: categorie '{name}' kon niet verwerkt worden, overslaan.")
            # Geen None retourneren; sluit deze categorie gewoon uit
            continue
        category_ids.append(cat_id)

    return category_ids if category_ids else None


# ---------------------------------------------------------------------------
# Upload & Post (met categorien)
# ---------------------------------------------------------------------------

def upload_and_post(metadata: dict, img_data: bytes, theme: str, i: int, categories: list[str] | None = None):
    files = {"file": (f"{theme}-{i}.png", img_data, "image/png")}
    media_resp = requests.post(f"{WP_URL}/wp-json/wp/v2/media", auth=(WP_USERNAME, WP_APP_PASSWORD), files=files)

    if media_resp.status_code != 201:
        print(f"WP Media fout: {media_resp.text}")
        return

    try:
        media_json = media_resp.json()
    except (ValueError, json.JSONDecodeError) as e:
        print(f"WP Media JSON fout: {e}")
        return

    media_id = media_json['id']
    source_url = media_json['source_url']

    alt = metadata.get('title', f"Kleurplaat {theme}")
    set_media_alt(media_id, alt)

    term_ids: list[int] = []
    if categories:
        all_cats = list(categories)
        if theme not in all_cats:
            all_cats.insert(0, theme)
    else:
        all_cats = [theme]

    result_ids = get_or_create_terms(all_cats)
    if result_ids:
        term_ids.extend(result_ids)

    post_payload = {
        "title": metadata['title'],
        "content": metadata['factoid'],
        "status": "publish",
        "featured_media": media_id,
        "meta": {"kk_print_url": source_url},
    }

    if term_ids:
        post_payload[CUSTOM_TAXONOMY] = term_ids

    post_resp = requests.post(f"{WP_URL}/wp-json/wp/v2/kleurplaten", auth=(WP_USERNAME, WP_APP_PASSWORD), json=post_payload)
    if post_resp.status_code == 201:
        term_str = ", ".join(str(t) for t in term_ids) if term_ids else "geen"
        print(f"Succes: {metadata['title']} staat live (terms: {term_str}).")
    else:
        print(f"WP Post fout: {post_resp.text}")

def main():
    if not HUGGINGFACE_TOKEN:
        print("Error: Set HF_TOKEN environment variable")
        return

    if not all([WP_URL, WP_USERNAME, WP_APP_PASSWORD]):
        print("Error: Set WORDPRESS_URL, WORDPRESS_USERNAME, and WORDPRESS_APP_PASSWORD environment variables")
        return

    parser = argparse.ArgumentParser()
    parser.add_argument("--theme", required=False)
    parser.add_argument("--count", type=int, default=1)
    parser.add_argument("--themes-file")
    parser.add_argument("--popular-themes", action="store_true")
    parser.add_argument("--count-per-theme", type=int, default=1)
    parser.add_argument("--categorize-existing", action="store_true", help="Categorize existing kleurplaten posts based on theme keywords in titles")
    args = parser.parse_args()

    if args.categorize_existing:
        categorize_existing_images()
        return

    themes = []
    count_per_theme = args.count_per_theme

    if args.theme and not args.popular_themes and not args.themes_file:
        themes = [args.theme]
        count_per_theme = args.count
    elif args.popular_themes:
        themes = list(POPULAR_THEMES)
    elif args.themes_file:
        path = Path(args.themes_file)
        if not path.exists():
            print(f"Themes-bestand niet gevonden: {args.themes_file}")
            return
        text = path.read_text(encoding="utf-8")
        try:
            data = json.loads(text)
            if isinstance(data, list):
                themes = [str(item) for item in data]
            else:
                print("Themes-bestand moet een JSON-array of een lijst met thema's zijn.")
                return
        except json.JSONDecodeError:
            lines = [line.strip() for line in text.splitlines() if line.strip()]
            themes = lines
    elif args.theme:
        themes = [args.theme]

    if not themes:
        print("Geen thema's opgegeven. Gebruik --theme, --popular-themes of --themes-file.")
        return

    for theme in themes:
        for i in range(1, count_per_theme + 1):
            print(f"Verwerken {theme} {i}/{count_per_theme}...")

            meta = generate_metadata(theme)

            try:
                image = client.text_to_image(
                    prompt=meta['image_prompt'],
                    model="black-forest-labs/FLUX.1-schnell"
                )

                img_byte_arr = io.BytesIO()
                image.save(img_byte_arr, format='PNG')
                img_content = img_byte_arr.getvalue()

                upload_and_post(meta, img_content, theme.replace(" ", "-"), i, categories=[theme])

            except Exception as e:
                print(f"Afbeelding generatie fout: {e}")

            time.sleep(2)
if __name__ == "__main__":
    main()