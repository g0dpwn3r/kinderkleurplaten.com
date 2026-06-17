#!/usr/bin/env python3

import argparse
import json
import re
import time
import requests
from pathlib import Path
from dotenv import load_dotenv

SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
load_dotenv(dotenv_path=DOTENV_PATH)

import os

# =====================================================================
# INSTELLINGEN
# =====================================================================
HUGGINGFACE_TOKEN = os.getenv("HF_TOKEN")
WP_URL = os.getenv("WORDPRESS_URL")
WP_USERNAME = os.getenv("WORDPRESS_USERNAME")
WP_APP_PASSWORD = os.getenv("WORDPRESS_APP_PASSWORD")

TEXT_API_URL = "https://router.huggingface.co/hf-inference/v1/chat/completions"
IMAGE_API_URL = "https://router.huggingface.co/hf-inference/v1/models/black-forest-labs/FLUX.1-schnell"
HEADERS = {"Authorization": f"Bearer {HUGGINGFACE_TOKEN}", "Content-Type": "application/json"}

def generate_metadata(theme: str) -> dict:
    system_prompt = (
        "Je bent een JSON-only API. "
        "Geef enkel een JSON-object met 'title', 'image_prompt' en 'factoid'. "
        "Regels: "
        "1) 'title' is een korte, beschrijvende Nederlandse titel voor de kleurplaat (bv. 'Lachende T-Rex'). "
        "2) 'image_prompt' is een ENGELSE prompt specifiek voor het genereren van een kleurplaat: zwart-witte lijntekening, dikke zwarte contouren, pure witte achtergrond, geen schaduwen, geen kleuren, schone minimalistische stijl, geschikt voor kinderen. "
        "3) 'factoid' is een kort Nederlands weetje over het thema (bv. 'Wist je dat de T-Rex de voorouwer is van de kip?')."
    )
    payload = {
        "model": "microsoft/Phi-3-mini-4k-instruct",
        "messages": [
            {"role": "system", "content": system_prompt},
            {"role": "user", "content": f"Thema: {theme}. Genereer een passende titel, een engelse kleurplaat-prompt en een Nederlands weetje."}
        ],
        "temperature": 0.7
    }
    
    try:
        response = requests.post(TEXT_API_URL, headers=HEADERS, json=payload, timeout=60)
        response.raise_for_status()
        content = response.json()['choices'][0]['message']['content']
        match = re.search(r"\{.*\}", content, re.DOTALL)
        if match:
            return json.loads(match.group(0))
    except Exception as e:
        print(f"Fout bij metadata: {e}")
        
    return {
        "title": f"Kleurplaat {theme}",
        "image_prompt": f"A simple child-friendly coloring page of {theme}. Black and white line art, thick outlines, white background.",
        "factoid": f"Wist je dat er veel bijzondere dingen zijn over {theme}?"
    }

def upload_and_post(metadata: dict, img_data: bytes, theme: str, i: int):
    files = {"file": (f"{theme}-{i}.png", img_data, "image/png")}
    media_resp = requests.post(f"{WP_URL}/wp-json/wp/v2/media", auth=(WP_USERNAME, WP_APP_PASSWORD), files=files)
    
    if media_resp.status_code != 201:
        print(f"WP Media fout: {media_resp.text}")
        return

    media_id = media_resp.json()['id']
    source_url = media_resp.json()['source_url']

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
        print("Error: Set HF_TOKEN environment variable with a valid Hugging Face token")
        return
    parser = argparse.ArgumentParser()
    parser.add_argument("--theme", required=True)
    parser.add_argument("--count", type=int, default=1)
    args = parser.parse_args()

    for i in range(1, args.count + 1):
        print(f"Processing {i}/{args.count}...")
        meta = generate_metadata(args.theme)
        
        img_resp = requests.post(IMAGE_API_URL, headers={"Authorization": f"Bearer {HUGGINGFACE_TOKEN}"}, json={"inputs": meta['image_prompt']})
        if img_resp.status_code == 200:
            upload_and_post(meta, img_resp.content, args.theme.replace(" ", "-"), i)
        
        time.sleep(2)

if __name__ == "__main__":
    main()

