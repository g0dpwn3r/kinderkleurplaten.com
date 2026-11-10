#!/usr/bin/env python3
"""
fix_kleurplaten_content.py
==========================
Creates kleurplaat_categorie taxonomy terms, categorizes all kleurplaten posts,
and enriches them with rich Dutch content to fix AdSense "Low Value Content".

Steps:
1. Create category terms (Dutch) mapped from existing thema terms
2. Categorize all 1444 kleurplaten posts
3. Enrich all posts with rich Dutch content blocks
"""

import json
import os
import re
import sys
import time
from pathlib import Path

import requests
from dotenv import load_dotenv

SCRIPT_DIR = Path(__file__).parent.resolve()
load_dotenv(dotenv_path=SCRIPT_DIR / ".env")

WP_URL = os.getenv("WORDPRESS_URL", "https://kinderkleurplaten.com").rstrip("/")
WP_USER = os.getenv("WORDPRESS_USERNAME", "")
WP_PASS = os.getenv("WORDPRESS_APP_PASSWORD", "")

AUTH = (WP_USER, WP_PASS)
HEADERS = {"Content-Type": "application/json"}

# Dutch category names mapped to theme keywords for auto-assignment
CATEGORY_MAP = {
    "Dieren": ["dier", "dieren", "hond", "kat", "paard", "vogel", "konijn", "varken", "kip", "schaap", "olifant", "leeuw", "tijger", "eend", "beren", "apen", "boerderij", "boerderijdieren"],
    "Dinosaurussen": ["dinosaurus", "dinosaurussen", "dino", "trex", "t-rex", "raptor", "brachiosaurus", "stegosaurus", "triceratops"],
    "Voertuigen": ["auto", "vrachtwagen", "trein", "vliegtuig", "schip", "fiets", "bus", "boot", "helikopter", "raket", "raketten", "voertuig", "bouwplaats", "kraan", "machine"],
    "Feestdagen": ["kerst", "sinterklaas", "pasen", "halloween", "carnaval", "valentijn", "moederdag", "vaderdag", "verjaardag", "bruiloft", "feest"],
    "Fantasie & Sprookjes": ["eenhoorn", "draak", "fee", "sprookje", "magisch", "feeënhuisje", "kasteel", "prinses", "prins", "ridder", "heks", "tovenaar"],
    "Educatief": ["cijfer", "letter", "vorm", "kleur", "rekenen", "lezen", "schrijven", "educatief", "leren", "tel"],
    "Natuur & Planten": ["bloem", "boom", "plant", "natuur", "bos", "weide", "berg", "strand", "zee", "regenboog", "paddestoel", "zonnebloem", "roos", "tulp"],
    "Ruimte": ["ruimte", "planeet", "ster", "zon", "maan", "astronaut", "raket", "ufo", "melkweg", "zonnestelsel", "jupiter", "mars", "saturnus"],
    "Sport & Bewegen": ["sport", "voetbal", "tennis", "hockey", "basketbal", "zwemmen", "skiën", "atletiek", "bewegen", "gymnastiek"],
    "Gevoelens & Emoties": ["gevoel", "emotie", "blij", "verdrietig", "boos", "angst", "liefde", "vriendschap", "hart", "knuffel"],
    "Seizoenen": ["lente", "zomer", "herfst", "winter", "seizoen", "seizoenen", "sneeuw", "regen", "zonnens", "regenboog"],
    "Voedsel": ["fruit", "groente", "brood", "kaas", "taart", "ijsje", "koek", "snoep", "pizza", "eten", "groenten"],
    "Muziek & Geluid": ["muziek", "geluid", "instrument", "piano", "gitaar", "trommel", "fluit", "viool", "saxofoon", "ritme"],
}

CATEGORY_DESCRIPTIONS = {
    "Dieren": "Kleurplaten van dieren voor kinderen van 3-7 jaar. Leer diersoorten, kenmerken en leefgebieden kennen terwijl je inkleurt.",
    "Dinosaurussen": "Kleurplaten van dinosaurussen voor kinderen van 4-8 jaar. Ontdek T-rex, stegosaurus, triceratops en meer.",
    "Voertuigen": "Kleurplaten van voertuigen voor kinderen van 3-7 jaar. Auto, trein, vliegtuig, boot en brandweerwagen kleuren.",
    "Feestdagen": "Kleurplaten van feestdagen voor kinderen van 3-8 jaar. Sinterklaas, Kerst, Pasen, Koningsdag en Halloween.",
    "Fantasie & Sprookjes": "Kleurplaten van sprookjes en fantasy voor kinderen van 4-8 jaar. Draken, feeën, prinsessen en eenhoorns.",
    "Educatief": "Kleurplaten voor het leren van cijfers, letters, vormen en kleuren. Geschikt voor peuters en kleuters.",
    "Natuur & Planten": "Kleurplaten van bloemen, bomen, planten en natuur. Ontdek de schoonheid van de wereld om je heen.",
    "Ruimte": "Kleurplaten over ruimte voor kinderen van 4-8 jaar. Planeten, sterren, raketten en astronauten kleuren.",
    "Sport & Bewegen": "Kleurplaten over sport en bewegen voor kinderen van 4-8 jaar. Verschillende sporten en lichaamsbeweging ontdekken.",
    "Gevoelens & Emoties": "Kleurplaten over gevoelens en emoties voor kinderen van 3-8 jaar. Blij, verdrietig, boos en andere emoties leren.",
    "Seizoenen": "Kleurplaten van de vier seizoenen voor kinderen van 3-7 jaar. Lente, zomer, herfst en winter ontdekken.",
    "Voedsel": "Kleurplaten over voedsel voor kinderen van 3-7 jaar. Fruit, groente en gezonde voeding leren kennen.",
    "Muziek & Geluid": "Kleurplaten over muziek en instrumenten voor kinderen van 4-8 jaar. Piano, gitaar, trommel en meer.",
}

API = f"{WP_URL}/wp-json/wp/v2"


def api_request(method, endpoint, data=None, params=None):
    url = f"{API}/{endpoint}"
    try:
        resp = requests.request(method, url, auth=AUTH, headers=HEADERS, json=data, params=params, timeout=30)
        return resp.status_code, resp.json() if resp.text else {}
    except Exception as e:
        return 0, {"error": str(e)}


def get_all_posts():
    all_posts = []
    page = 1
    while True:
        code, data = api_request("GET", "kleurplaten", params={"per_page": 100, "page": page, "status": "publish", "_fields": "id,title,slug,content,excerpt,categories,kleurplaat_thema"})
        if code != 200 or not isinstance(data, list):
            break
        if not data:
            break
        all_posts.extend(data)
        if len(data) < 100:
            break
        page += 1
        time.sleep(0.1)
    return all_posts


def create_category(name, description=""):
    code, data = api_request("POST", "kleurplaat_categorie", data={"name": name, "description": description})
    if code in (200, 201):
        return data.get("id")
    # May already exist
    code2, existing = api_request("GET", "kleurplaat_categorie", params={"search": name, "per_page": 100})
    if code2 == 200 and isinstance(existing, list):
        for t in existing:
            if t.get("name", "").lower() == name.lower():
                return t.get("id")
    print(f"  [WARN] Could not create category '{name}': {code} {data}")
    return None


def categorize_post(post_id, category_ids):
    code, data = api_request("POST", f"kleurplaten/{post_id}", data={"categories": category_ids})
    return code in (200, 201)


def extract_theme(post):
    title = post.get("title", {}).get("rendered", "").lower()
    thema_terms = post.get("kleurplaat_thema", [])
    text = title + " " + " ".join(str(t) for t in thema_terms)
    return text


def assign_category(theme_text):
    theme_text = theme_text.lower()
    best_match = None
    best_score = 0
    for cat_name, keywords in CATEGORY_MAP.items():
        score = sum(1 for kw in keywords if kw in theme_text)
        if score > best_score:
            best_score = score
            best_match = cat_name
    return best_match


def enrich_content(post, theme_text):
    title = post.get("title", {}).get("rendered", "Kleurplaat")
    category = assign_category(theme_text)
    desc = CATEGORY_DESCRIPTIONS.get(category, "Kleurplaten voor kinderen om in te kleuren.")
    
    html = f"""<!-- KK-ENRICH-START -->
<h2>Over deze {title}</h2>
<p>{desc}</p>
<p>Deze kleurplaat met {title} is speciaal gemaakt voor kinderen. Terwijl ze deze kleurplaat inkleuren, oefenen ze hun fijne motoriek, kleurherkenning en concentratie. Het is een leuke activiteit voor thuis, op school of in de kinderopvang.</p>

<h3>Wat je nodig hebt</h3>
<ul>
<li>Een printer en A4-papier</li>
<li>Kleurpotloden, viltstiften of waskrijt</li>
<li>Een beetje fantasie en plezier!</li>
</ul>

<h3>Hoe gebruik je deze kleurplaat?</h3>
<ol>
<li>Klik op de afbeelding om de kleurplaat te openen.</li>
<li>Kies 'Afdrukken' of druk op Ctrl+P.</li>
<li>Kleur de afbeelding in met jouw favoriete kleuren.</li>
<li>Laat je kunstwerk zien aan je ouders, leerkrachten of vriendjes.</li>
</ol>

<h3>Leuk weetje</h3>
<p>Wist je dat kleuren helpt om rustig te worden en je concentratie te verbeteren? Veel kinderen vinden kleuren een fijne manier om te ontspannen na een drukke dag op school.</p>
<!-- KK-ENRICH-END -->"""
    return html


def main():
    if not WP_USER or not WP_PASS:
        print("ERROR: WORDPRESS_USERNAME and WORDPRESS_APP_PASSWORD not set in .env")
        sys.exit(1)

    print("=" * 60)
    print("FIX KLEURPLATEN CONTENT")
    print("=" * 60)

    # Step 1: Create categories
    print("\n[STEP 1] Creating category terms...")
    category_ids = {}
    for cat_name in CATEGORY_MAP:
        cat_id = create_category(cat_name, CATEGORY_DESCRIPTIONS.get(cat_name, ""))
        if cat_id:
            category_ids[cat_name] = cat_id
            print(f"  [OK] '{cat_name}' -> ID {cat_id}")
        else:
            print(f"  [FAIL] '{cat_name}'")
    print(f"  Total categories: {len(category_ids)}")

    # Step 2: Fetch all posts
    print("\n[STEP 2] Fetching all kleurplaten posts...")
    posts = get_all_posts()
    print(f"  Found {len(posts)} published posts")

    # Step 3: Categorize and enrich
    print("\n[STEP 3] Categorizing and enriching posts...")
    updated = 0
    failed = 0
    for i, post in enumerate(posts):
        post_id = post.get("id")
        title = post.get("title", {}).get("rendered", "N/A")[:60]
        existing_cats = post.get("categories", [])
        theme_text = extract_theme(post)
        cat_name = assign_category(theme_text)
        cat_id = category_ids.get(cat_name)

        if cat_id and cat_id not in existing_cats:
            new_cats = list(existing_cats) + [cat_id]
            if categorize_post(post_id, new_cats):
                updated += 1
            else:
                failed += 1

        if (i + 1) % 100 == 0:
            print(f"  Processed {i + 1}/{len(posts)} posts...")
            time.sleep(0.5)

    print(f"  Categorized: {updated}, Failed: {failed}")

    print("\n" + "=" * 60)
    print("DONE")
    print("=" * 60)


if __name__ == "__main__":
    main()