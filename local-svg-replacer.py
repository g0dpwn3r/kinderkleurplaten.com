#!/usr/bin/env python3

import base64
import os
import sys
import time
from pathlib import Path
from typing import List, Optional

import requests
from deep_translator import GoogleTranslator
from dotenv import load_dotenv

SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
THEME_ASSETS_DIR = SCRIPT_DIR / "wp-content/themes/kinderkleurplaten/assets/images"

HF_API_URL = "https://router.huggingface.co/hf-inference/models/black-forest-labs/FLUX.1-schnell"
HF_TIMEOUT = 180
DEFAULT_503_RETRY_SECONDS = 20
MAX_503_RETRIES = 1
POST_SUCCESS_DELAY_SECONDS = 5

NOISY_TERMS = [
    "coloring page",
    "to print",
    "for free",
    "printable",
    "colouring page",
]

load_dotenv(dotenv_path=DOTENV_PATH)

HF_TOKEN = os.getenv("HF_TOKEN")

session = requests.Session()
session.headers.update({"Authorization": f"Bearer {HF_TOKEN}"})

DNS_RETRY_WAIT_SECONDS = 10
DNS_MAX_RETRIES = 3


def slug_to_title(slug: str) -> str:
    return slug.replace("-", " ").replace("_", " ").strip()


def clean_search_query(text: str) -> str:
    cleaned = text.lower()
    for term in NOISY_TERMS:
        cleaned = cleaned.replace(term, "")
    return " ".join(cleaned.split()).strip()


def translate_slug_to_english(slug: str) -> str:
    title = slug_to_title(slug)

    if not title:
        return "coloring page"

    try:
        translator = GoogleTranslator(source="nl", target="en")
        translated = translator.translate(title)
        print(f"  Translated slug: '{title}' -> '{translated}'")
    except Exception as e:
        print(f"  [ERROR] Translation failed: {e}")
        translated = title

    cleaned = clean_search_query(translated)
    return cleaned or title


def build_ai_prompt(translated_title: str) -> str:
    return f"A simple, child-friendly coloring page of {translated_title}. Strictly black and white line art, thick black outlines, pure white background, no shading, no colors, clean minimalist style."


def parse_estimated_time(response: requests.Response) -> float:
    try:
        payload = response.json()
    except ValueError:
        return DEFAULT_503_RETRY_SECONDS

    try:
        estimated_time = float(payload.get("estimated_time", DEFAULT_503_RETRY_SECONDS))
    except (TypeError, ValueError):
        return DEFAULT_503_RETRY_SECONDS

    return max(estimated_time, 0)


def generate_image_hf(prompt_text: str) -> Optional[bytes]:
    payload = {"inputs": prompt_text}

    print("  Generating image with Hugging Face Inference API")
    print(f"  Model: stabilityai/stable-diffusion-xl-base-1.0")
    print(f"  Prompt: {prompt_text}")

    dns_attempt = 0
    while dns_attempt < DNS_MAX_RETRIES:
        dns_attempt += 1
        print(f"  API request attempt: {dns_attempt}")

        try:
            response = session.post(
                HF_API_URL,
                json=payload,
                timeout=HF_TIMEOUT,
            )
        except (requests.exceptions.ConnectionError, requests.exceptions.Timeout) as e:
            print(f"  [ERROR] {type(e).__name__}: {e}")
            if dns_attempt < DNS_MAX_RETRIES:
                print(f"  Retrying in {DNS_RETRY_WAIT_SECONDS} seconds... (attempt {dns_attempt}/{DNS_MAX_RETRIES})")
                time.sleep(DNS_RETRY_WAIT_SECONDS)
                continue
            return None
        except requests.exceptions.RequestException as e:
            print(f"  [ERROR] {type(e).__name__}: {e}")
            if dns_attempt < DNS_MAX_RETRIES:
                print(f"  Retrying in {DNS_RETRY_WAIT_SECONDS} seconds... (attempt {dns_attempt}/{DNS_MAX_RETRIES})")
                time.sleep(DNS_RETRY_WAIT_SECONDS)
                continue
            return None

        if response.status_code == 503:
            retry_after = parse_estimated_time(response)
            print(f"  [503] Model is loading. Estimated wait: {retry_after:.1f} seconds.")
            time.sleep(retry_after)
            if dns_attempt < DNS_MAX_RETRIES:
                continue
            return None

        if response.status_code != 200:
            print(f"  [ERROR] HTTP {response.status_code}")
            print(f"  Response body: {response.text}")
            return None

        if not response.content:
            print("  [ERROR] Hugging Face returned an empty image response.")
            return None

        print(f"  Generated image bytes: {len(response.content):,}")
        return response.content

    return None


def write_svg_wrapper(image_bytes: bytes, dest_path: Path) -> bool:
    base64_data = base64.b64encode(image_bytes).decode("ascii")
    svg_content = (
        f'<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">'
        f'<image href="data:image/jpeg;base64,{base64_data}" '
        f'width="100%" height="100%" />'
        f'</svg>'
    )

    try:
        dest_path.write_text(svg_content, encoding="utf-8")
    except OSError as e:
        print(f"  [ERROR] Failed to overwrite SVG file: {e}")
        return False

    print(f"  SVG wrapper size: {dest_path.stat().st_size:,} bytes")
    print(f"  Overwrote: {dest_path}")
    return True


def find_svg_files(directory: Path) -> List[Path]:
    if not directory.exists():
        print(f"[ERROR] Directory not found: {directory}")
        return []
    return sorted(directory.glob("*.svg"))


def process_svg_file(svg_path: Path) -> bool:
    slug = svg_path.stem

    print(f"\n{'=' * 60}")
    print(f"Processing: {svg_path.name}")
    print(f"  File: {svg_path}")
    print(f"  Slug: {slug}")

    translated_title = translate_slug_to_english(slug)
    prompt_text = build_ai_prompt(translated_title)

    image_bytes = generate_image_hf(prompt_text)
    if image_bytes is None:
        print(f"  [FAILED] Could not generate image for: {svg_path.name}")
        return False

    success = write_svg_wrapper(image_bytes, svg_path)
    if success:
        print(f"  [SUCCESS] Replaced: {svg_path.name}")

    return success


def count_total_images(directory: Path) -> int:
    return len(list(directory.glob("*.svg")))


def validate_hf_config() -> bool:
    if HF_TOKEN:
        print(f"Loaded HF_TOKEN from: {DOTENV_PATH}")
        return True

    print("[ERROR] Missing HF_TOKEN in .env.")
    return False


def main() -> None:
    print("=" * 60)
    print("Local SVG Replacer for Kinderkleurplaten")
    print("=" * 60)
    print("Image provider: Hugging Face Free Inference API")
    print("Model: stabilityai/stable-diffusion-xl-base-1.0")

    if not validate_hf_config():
        sys.exit(1)

    if not THEME_ASSETS_DIR.exists():
        print(f"[FATAL] Assets directory not found: {THEME_ASSETS_DIR}")
        sys.exit(1)

    svg_files = find_svg_files(THEME_ASSETS_DIR)
    total = len(svg_files)

    print(f"Target directory: {THEME_ASSETS_DIR}")
    print(f"Found {total} SVG files to process.")
    print(f"Post-success rate limit delay: {POST_SUCCESS_DELAY_SECONDS} seconds")

    if total == 0:
        print("[FATAL] No SVG files found. Exiting.")
        sys.exit(1)

    force = os.getenv("FORCE_OVERWRITE", "true").lower() in ("true", "1", "yes")
    if not force or "--confirm" in sys.argv:
        confirm = input("Continue? Existing SVG files will be overwritten. Type yes/no: ").strip().lower()
        if confirm not in ("yes", "y"):
            print("Aborted by user.")
            sys.exit(0)
    else:
        print("[AUTO] FORCE_OVERWRITE=true — skipping confirmation prompt.")

    results = {"success": 0, "failed": 0}

    for i, svg_path in enumerate(svg_files, 1):
        print(f"\n>>> [{i}/{total}] Processing...")
        success = process_svg_file(svg_path)

        if success:
            results["success"] += 1
            print(f"  Rate limit pause: sleeping {POST_SUCCESS_DELAY_SECONDS} seconds before next file.")
            time.sleep(POST_SUCCESS_DELAY_SECONDS)
        else:
            results["failed"] += 1

    print("\n" + "=" * 60)
    print("PROCESSING COMPLETE")
    print("=" * 60)
    print(f"  Total files processed : {total}")
    print(f"  Successfully replaced : {results['success']}")
    print(f"  Failed / errors       : {results['failed']}")
    print(f"  Images in directory   : {count_total_images(THEME_ASSETS_DIR)}")
    print("=" * 60)


if __name__ == "__main__":
    main()
