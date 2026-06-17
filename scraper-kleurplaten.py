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

def upload_and_post(metadata: dict, img_data: bytes, theme: str, i: int):
    files = {"file": (f"{theme}-{i}.png", img_data, "image/png")}
    media_resp = requests.post(f"{WP_URL}/wp-json/wp/v2/media", auth=(WP_USERNAME, WP_APP_PASSWORD), files=files)

    if media_resp.status_code != 201:
        print(f"WP Media fout: {media_resp.text}")
        return

    media_id = media_resp.json()['id']
    source_url = media_resp.json()['source_url']

    alt = metadata.get('title', f"Kleurplaat {theme}")
    set_media_alt(media_id, alt)

    post_payload = {
        "title": metadata['title'],
        "content": metadata['factoid'],
        "status": "publish",
        "featured_media": media_id,
        "meta": {"kk_print_url": source_url}
    }

    post_resp = requests.post(f"{WP_URL}/wp-json/wp/v2/kleurplaten", auth=(WP_USERNAME, WP_APP_PASSWORD), json=post_payload)
    if post_resp.status_code == 201:
        print(f"Succes: {metadata['title']} staat live.")
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
    args = parser.parse_args()

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

                upload_and_post(meta, img_content, theme.replace(" ", "-"), i)

            except Exception as e:
                print(f"Afbeelding generatie fout: {e}")

            time.sleep(2)
if __name__ == "__main__":
    main()