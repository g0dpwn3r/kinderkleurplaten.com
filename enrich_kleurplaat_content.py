#!/usr/bin/env python3
"""
enrich_kleurplaat_content.py
============================

Rijke, SEO-vriendelijke content-generator voor kinderkleurplaten.com.

Probleem
--------
Google AdSense wees de site af wegens "Content van weinig waarde" (Thin Content).
De kleurplaat-pagina's bevatten voornamelijk een afbeelding en ~1-2 zinnen tekst,
onvoldoende voor de Google crawler om de pagina als waardevol te beoordelen.

Oplossing
---------
Deze module genereert voor ELKE kleurplaat een uniek content-blok van
~250-350 woorden, bestaande uit:

  1. Unieke introductie (2-3 alinea's, ~120-160 woorden)
     - Creatieve introductie over het specifieke onderwerp
     - Educatieve alinea voor ouders/leerkrachten (motoriek/creativiteit)
  2. "Details van deze kleurplaat" - HTML-tabel met metadata
  3. "Hoe gebruik je deze kleurplaat?" - gestructureerde handleiding
     met thema-specifieke kleurtips
  4. Natuurlijk verwerkte long-tail keywords

Anti-AI-spam maatregelen
------------------------
- 4+ variaties van elke sectie-template (random per post)
- Synoniemenrotatie voor "kleurplaat" (kleurplaat, kleurtekening,
  inkleuropdracht, kleurplaatje, tekenopdracht)
- Wisselende openingszinnen en zinsstructuren
- Thema-specifieke feitjes i.p.v. generieke praat
- Natuurlijke keyword-spreiding (geen keyword-stuffing)
- Idempotente markers: <!-- KK-ENRICH-START --> ... <!-- KK-ENRICH-END -->
  zodat herhaaldelijke runs geen duplicaten veroorzaken

Gebruik als library
-------------------
    from enrich_kleurplaat_content import build_rich_content, enrich_existing_posts
    html = build_rich_content(theme="Dinosaurussen", factoid="...")
    enrich_exrich = enrich_existing_posts(wp_url, auth, dry_run=False, limit=50)

Auteur: kinderkleurplaten.com
Versie: 1.0.0
"""
from __future__ import annotations

import argparse
import json
import os
import random
import re
import sys
import time
from pathlib import Path
from typing import Any, Iterable

try:
    import requests
    from dotenv import load_dotenv
except ImportError:  # pragma: no cover
    requests = None  # type: ignore
    load_dotenv = None  # type: ignore


# ---------------------------------------------------------------------------
# Configuratie & constanten
# ---------------------------------------------------------------------------

SCRIPT_DIR = Path(__file__).parent.resolve()

# Markers waarmee we eerdere enrichment kunnen herkennen en overschrijven.
ENRICH_START = "<!-- KK-ENRICH-START -->"
ENRICH_END = "<!-- KK-ENRICH-END -->"
ENRICH_BLOCK_RE = re.compile(
    rf"{re.escape(ENRICH_START)}.*?{re.escape(ENRICH_END)}\s*",
    re.DOTALL,
)

# Ook de oude zwakke seo-subtitle blokken strippen zodat we niet stapelen op
# wat bulk_categorize_existing.py / fix_duplicate_seo_content.py hebben gezet.
LEGACY_INTRO_RE = re.compile(
    r'<h2\s+class=["\']seo-subtitle["\'][^>]*>.*?</h2>\s*<p[^>]*>.*?</p>',
    re.DOTALL | re.IGNORECASE,
)
LEGACY_FOOTER_RE = re.compile(
    r'<p[^>]*(?:style=["\'][^"\']*["\'][^>]*)?>\s*'
    r'(?:Zie ook onze andere|Bekijk onze galerij).*?</p>',
    re.DOTALL | re.IGNORECASE,
)


# ---------------------------------------------------------------------------
# Thema-categorisatie
# ---------------------------------------------------------------------------

#: Categorie-labels die we aan elk thema koppelen om automatisch
#: context-passende content te genereren.
THEME_CATEGORY_RULES: list[tuple[str, set[str]]] = [
    ("dier", {
        "dier", "dieren", "boerderijdier", "boerderijdieren", "hond", "honden",
        "kat", "katten", "paard", "paarden", "vogel", "vogels", "eend",
        "eenden", "kip", "kippen", "konijn", "konijntjes", "hert", "hertjes",
        "vis", "vissen", "olifant", "leeuw", "tijger", "wolf", "vos",
    }),
    ("dinosaurus", {
        "dinosaurus", "dinosaurussen", "dino", "trex", "t-rex", "raptor",
        "brachiosaurus", "stegosaurus", "triceratops",
    }),
    ("zeedier", {
        "onderwaterwereld", "zeedier", "zeedieren", "vis", "vissen",
        "zeemeermin", "zeemeerminnen", "kwal", "kreeft", "krab", "zeester",
        "dolfijn", "walvis", "haai",
    }),
    ("insect", {
        "vlinder", "vlinders", "bij", "bijen", "lieveheersbeestje",
        "mieren", "mier", "kever", "krekel",
    }),
    ("plant", {
        "bloem", "bloemen", "boom", "bomen", "paddestoel", "paddestoelen",
        "plant", "planten", "roos", "zonnebloem", "tulp",
    }),
    ("fantasie", {
        "eenhoorn", "eenhoorns", "draak", "draken", "fee", "feeën",
        "feeënhuisje", "feeënhuisjes", "sprookje", "magisch", "magische",
        "tovenaar", "heks",
    }),
    ("voertuig", {
        "auto", "auto's", "vrachtwagen", "vrachtwagens", "trein", "treinen",
        "vliegtuig", "vliegtuigen", "schip", "schepen", "fiets", "fietsen",
        "bus", "boot", "helikopter", "rakket", "raket", "raketten",
    }),
    ("ruimte", {
        "ruimte", "planeet", "planeten", "ster", "sterren", "maan", "zon",
        "raket", "raketten", "astronaut", "ufo", "melkweg",
    }),
    ("feestdag", {
        "sinterklaas", "kerst", "kerstmis", "pasen", "verjaardag",
        "carnaval", "halloween", "valentijn", "moederdag", "vaderdag",
        "bruiloft", "feest",
    }),
    ("seizoen", {
        "lente", "zomer", "herfst", "winter", "seizoen", "seizoenen",
    }),
    ("sport", {
        "sport", "voetbal", "tennis", "hockey", "basketbal", "zwemmen",
        "skiën", "atletiek",
    }),
    ("natuur", {
        "weer", "regen", "sneeuw", "zon", "regenboog", "berg", "bergen",
        "bos", "strand", "zee", "rivier", "meer", "weide",
    }),
    ("vervoer", {"bouwplaats", "kraan", "machine", "trein", "tram", "metro"}),
    ("eten", {
        "groente", "groenten", "fruit", "ijsje", "ijsjes", "taart", "taarten",
        "snoep", "koek", "pizza",
    }),
    ("gevoel", {"hart", "harten", "liefde", "vriendschap", "knuffel"}),
    ("kasteel", {
        "kasteel", "kastelen", "ridder", "ridders", "prinses", "prinsessen",
        "prins", "kroon",
    }),
    ("speelgoed", {"speelgoed", "bal", "ballen", "pop", "beer", "blokken"}),
]


def detect_theme_categories(theme: str) -> list[str]:
    """Geeft een lijst categorie-labels terug die op ``theme`` van toepassing zijn.

    Voorbeeld:
        >>> detect_theme_categories("Dino")
        ['dinosaurus']
        >>> detect_theme_categories("Kerst")
        ['feestdag']
    """
    if not theme:
        return []
    norm = theme.lower().strip()
    matches: list[str] = []
    for label, keywords in THEME_CATEGORY_RULES:
        if any(kw in norm for kw in keywords):
            matches.append(label)
    return matches


# ---------------------------------------------------------------------------
# Thema-specifieke kennisbank
# ---------------------------------------------------------------------------

#: Rijke profielen voor populaire thema's. Elk profiel bevat:
#:  - factoid_extra: extra weetje (1-2 zinnen) dat naast het AI-gegenereerde
#:    factoid wordt geplaatst om variatie en thema-diepte te geven
#:  - kleur_tips: 2-3 specifieke kleurtips voor dit thema
#:  - doelgroep: voorkeur leeftijdsgroep
#:  - formaat: voorkeur bestandsformaat
#:  - onderwijskundige_waarde: korte zin over motoriek/creativiteit
THEME_PROFILES: dict[str, dict[str, Any]] = {
    # ---- Dinosaurussen ----
    "dinosaurussen": {
        "factoid_extra": (
            "Wetenschappers denken dat de meeste dinosauriërs veren of een "
            "donzige vacht hadden, vergelijkbaar met moderne vogels."
        ),
        "kleur_tips": [
            "Gebruik aardse tinten zoals mosgroen, oker en steenrood voor een realistisch ogend dinosaurusvel.",
            "Experimenteer met onverwachte kleuren zoals turquoise of paars — paleontologen weten niet precies welke kleuren dinosauriërs hadden.",
            "Laat de achtergrond contrasteren: een zachte pastelkleurige ondergrond laat de donkere contouren extra goed spreken.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-9 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en concentratie",
    },
    "dino": {
        "factoid_extra": (
            "Het woord 'dinosaurus' betekent letterlijk 'verschrikkelijke hagedis' "
            "en werd in 1842 bedacht door de Britse wetenschapper Richard Owen."
        ),
        "kleur_tips": [
            "Geef je T-Rex een groene of bruine huid en vergeet de scherpe tanden niet rood-roze te kleuren.",
            "Voeg textuur toe met kleine lijntjes of stipjes in een donkerdere tint van de basiskleur.",
            "Teken een vulkaan op de achtergrond in rood, oranje en geel voor een dramatisch effect.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-9 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en concentratie",
    },
    # ---- Dieren algemeen ----
    "dieren": {
        "factoid_extra": (
            "Er bestaan meer dan 8,7 miljoen diersoorten op aarde, maar de meeste "
            "hiervan zijn nog niet eens ontdekt door de wetenschap."
        ),
        "kleur_tips": [
            "Kijk naar echte foto's van het dier voor natuurgetrouwe kleuren.",
            "Gebruik minstens drie tinten van elke hoofdkleur voor schaduw en diepte.",
            "Vergeet de ogen niet: een klein zwart puntje met een witte glitter geeft het dier direct leven.",
        ],
        "doelgroep": "Peuters en kleuters (3-6 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en kleurherkenning",
    },
    "boerderijdieren": {
        "factoid_extra": (
            "Op een boerderij leven vaak kippen, koeien, varkens en schapen samen, "
            "ieder met hun eigen typische geluid en gewoontes."
        ),
        "kleur_tips": [
            "Een Holstein-koe is zwart-wit gevlekt, een Jersey-koe is lichtbruin.",
            "Een varken is roze, maar biggetjes hebben vaak lichte of donkere vlekjes.",
            "Kippen zijn er in allerlei kleuren: bruin, wit, zwart, gestreept — laat je fantasie de vrije loop.",
        ],
        "doelgroep": "Peuters en kleuters (3-6 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en woordenschat",
    },
    "honden": {
        "factoid_extra": (
            "Honden hebben een uitzonderlijk goed reukvermogen: ze kunnen tot wel "
            "100.000 keer beter ruiken dan mensen."
        ),
        "kleur_tips": [
            "Labradors zijn vaak geel, zwart of chocoladebruin.",
            "Voor een harige vacht kun je korte, lichte lijntjes in twee tinten over elkaar tekenen.",
            "Een roze tong en een natte, zwarte neus geven de hond direct karakter.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en emotionele expressie",
    },
    "katten": {
        "factoid_extra": (
            "Katten slapen gemiddeld 12 tot 16 uur per dag — echte "
            "professionele dutters!"
        ),
        "kleur_tips": [
            "Een lapjeskat (calico) heeft drie kleuren: zwart, oranje en wit.",
            "Siamese katten hebben een crèmekleurig lijf met donkerbruine 'points' op oren, poten en staart.",
            "Voor gestreepte katten (tabby) gebruik je dunne, gebogen lijntjes in een donkerdere tint.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en kleurherkenning",
    },
    "konijntjes": {
        "factoid_extra": (
            "Konijnen maken een zacht knorrend geluid als ze tevreden zijn, "
            "dat 'tandenknarsen' wordt genoemd."
        ),
        "kleur_tips": [
            "Een Hollands dwergkonijn is vaak wit met bruine of zwarte vlekken.",
            "Voor een wollige vacht kun je kleine krulletjes in een lichtere tint over de hoofdkleur tekenen.",
            "Roze oortjes van binnen en een roze neusje geven het konijntje direct een vriendelijke uitstraling.",
        ],
        "doelgroep": "Peuters en kleuters (3-6 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en zachtaardigheid",
    },
    # ---- Paarden ----
    "paarden": {
        "factoid_extra": (
            "Paarden kunnen staand slapen, maar dromen alleen als ze liggen — "
            "net als mensen!"
        ),
        "kleur_tips": [
            "Een vos is roodbruin, een schimmel wordt langzaam steeds witter naarmate hij ouder wordt.",
            "Voor een glanzende vacht kun je witte of lichte lijntjes in de richting van de haren tekenen.",
            "De manen en staart zijn meestal donkerder dan de vacht — perfect om mee te experimenteren.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Concentratie en fijne motoriek",
    },
    # ---- Vogels ----
    "vogels": {
        "factoid_extra": (
            "Vogels zijn de enige dieren op aarde met veren — "
            "elke vogelsoort heeft een uniek verenkleed."
        ),
        "kleur_tips": [
            "Een roodborstje heeft een opvallende oranje-rode borst; lijsters zijn meer bruin en gespikkeld.",
            "Gebruik felle, contrasterende kleuren voor tropische vogels zoals papegaaien.",
            "Voor veren textuur: kleine, overlappende halve maantjes in twee tinten geven een realistisch effect.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en observatie",
    },
    # ---- Zeedieren ----
    "onderwaterwereld": {
        "factoid_extra": (
            "Meer dan 80% van de oceanen op aarde is nog nooit door mensen "
            "verkend — er valt dus nog veel te ontdekken!"
        ),
        "kleur_tips": [
            "Een zeemeermin: groen of turkoois voor de staart, roze voor de huid.",
            "Kwallen zijn transparant met zachte roze of paarse gloed — gebruik kleurpotloden of wasco voor een zacht effect.",
            "Vissen in koraalriffen zijn er in alle kleuren van de regenboog — hoe feller, hoe beter!",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-9 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en kleurherkenning",
    },
    "zeemeerminnen": {
        "factoid_extra": (
            "Zeemeerminnen komen voor in zeemansverhalen uit vrijwel elke cultuur, "
            "van de Griekse mythologie tot de zeevaarders van de 17e eeuw."
        ),
        "kleur_tips": [
            "Een klassieke zeemeermin: groene of blauwe staart met zilveren schubben.",
            "Lang, golvend haar in rood, blond of zelfs paars geeft een magisch effect.",
            "Schelpjes in zachte pasteltinten vormen de typische zeemeermin-bovenkleding.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-9 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en fantasie",
    },
    # ---- Insecten ----
    "vlinders": {
        "factoid_extra": (
            "Vlinders proeven met hun voeten — zij hebben smaakreceptors op hun "
            "pootjes om te bepalen of een plant geschikt is om eitjes op te leggen."
        ),
        "kleur_tips": [
            "Een monarchvlinder is oranje met zwarte lijnen en witte stippen — heel herkenbaar.",
            "Gebruik symmetrische patronen: kleur beide vleugels hetzelfde voor een mooi effect.",
            "Een lichtere achtergrondkleur laat de vleugels extra goed uitkomen.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en symmetrie",
    },
    "bijen en bloemen": {
        "factoid_extra": (
            "Bijen zijn verantwoordelijk voor het bestuiven van ongeveer 75% van "
            "onze voedselgewassen — zonder bijen geen fruit, geen groenten."
        ),
        "kleur_tips": [
            "Een honingbij heeft een goudgele kop en borst, met zwarte strepen op het achterlijf.",
            "Bloemen in vrolijke kleuren: rood, geel, paars, roze — alles mag!",
            "Een blauwe lucht op de achtergrond en groene grassprieten maken het tafereel compleet.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en waardering voor de natuur",
    },
    # ---- Planten ----
    "bloemen": {
        "factoid_extra": (
            "Bloemen communiceren met insecten via kleur en geur — felle kleuren "
            "lokken bijen en vlinders, witte bloemen trekken nachtvlinders aan."
        ),
        "kleur_tips": [
            "Rozen: rood, roze, wit of geel — combineer donkere en lichte tinten voor diepte.",
            "Zonnebloemen zijn warmgeel met een bruin of zwart hart.",
            "Voor bloemblaadjes: gebruik kleine, gebogen lijntjes vanaf het hart naar de buitenkant.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en kleurherkenning",
    },
    # ---- Fantasie ----
    "eenhoorns": {
        "factoid_extra": (
            "Het woord 'eenhoorn' komt uit het Oudgrieks en betekent letterlijk "
            "'één hoorn' — monokerós."
        ),
        "kleur_tips": [
            "Een klassieke eenhoorn is wit met een gouden of zilveren hoorn en regenboog-manen.",
            "Experimenteer met pasteltinten: babyroze, mintgroen, lavendelpaars.",
            "Voeg glitter, sterretjes of kleine hartjes toe voor een magisch effect.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-9 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Creativiteit en fijne motoriek",
    },
    "draken": {
        "factoid_extra": (
            "In tegenstelling tot wat veel mensen denken, zijn draken in de "
            "Chinese mythologie juist vriendelijke, wijze wezens die geluk brengen."
        ),
        "kleur_tips": [
            "Een Chinese draak is vaak groen of goudkleurig met een lange, golvende staart.",
            "Een Europese draak is meestal rood, zwart of donkergroen met vurige accenten.",
            "Voor schubben: kleine, overlappende halve maantjes in twee tinten geven een schitterend effect.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    "feeën": {
        "factoid_extra": (
            "Feeën komen voor in de folklore van vrijwel elk land — van de Ierse "
            "sidhe tot de Scandinavische älvor."
        ),
        "kleur_tips": [
            "Klassieke feeën zijn klein en licht, vaak in pasteltinten: roze, mintgroen, lavendel.",
            "Gebruik metallic kleuren zoals goud en zilver voor de vleugels en sluiers.",
            "Een bloemenweide als achtergrond past perfect bij een betoverd sfeertje.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-9 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en fantasie",
    },
    # ---- Feestdagen ----
    "kerst": {
        "factoid_extra": (
            "De traditie van de kerstboom stamt uit de 16e eeuw in Duitsland, "
            "waar devotionele stukkenappelbomen werden versierd."
        ),
        "kleur_tips": [
            "Een kerstboom is donkergroen met rode, gouden of zilveren ballen en slingers.",
            "De Kerstman draagt een rood pak met witte bontrand, een zwarte riem en zwarte laarzen.",
            "Sneeuw op de achtergrond in lichtblauw en wit maakt het extra winters.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en feestvreugde",
    },
    "sinterklaas": {
        "factoid_extra": (
            "Sinterklaas wordt in Nederland al sinds de middeleeuwen gevierd, "
            "lang voordat de Kerstman in de VS populair werd."
        ),
        "kleur_tips": [
            "Sinterklaas draagt een rode mantel met witte kruis, een rode mijter en een gouden staf.",
            "Zijn paard is wit of bruin, met schimmel of roodbruin.",
            "De schoorsteen en het huis op de achtergrond in warme, gezellige tinten bruin en rood.",
        ],
        "doelgroep": "Peuters en kleuters (3-6 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en traditie",
    },
    "halloween": {
        "factoid_extra": (
            "Halloween komt oorspronkelijk van het Keltische Samhain, waar men "
            "geloofde dat de grens tussen de levenden en de doden dunner was."
        ),
        "kleur_tips": [
            "Pompoenen zijn oranje met een groene steel en donkergroene bladeren.",
            "Heksen dragen vaak zwart met een groene of paarse hoed — geheimzinnig!",
            "Een maanverlichte nachthemel in donkerpaars en geel maakt het griezelig en gezellig tegelijk.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    "verjaardag": {
        "factoid_extra": (
            "De verjaardagstaart met kaarsjes is een traditie die teruggaat tot "
            "het oude Griekenland, waar men ronde taarten offerde aan Artemis."
        ),
        "kleur_tips": [
            "Een verjaardagstaart is vaak roze, blauw of geel met gekleurde kaarsjes.",
            "Ballonnen in alle kleuren van de regenboog horen erbij!",
            "De jarige staat centraal — geef het figuurtje een feestelijke kleur en een vrolijke glimlach.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Vreugde en fijne motoriek",
    },
    "pasen": {
        "factoid_extra": (
            "Paaseieren werden oorspronkelijk versierd door ze in uienschillen "
            "te koken — dat gaf ze een mooie, natuurlijke bruine kleur."
        ),
        "kleur_tips": [
            "Paaseieren zijn er in alle kleuren — van pastel roze tot helder geel en groen.",
            "Een kuiken is lichtgeel met een oranje snaveltje en pootjes.",
            "Versier de eieren met stippen, streepjes of kleine bloemetjes voor een vrolijk effect.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en feestvreugde",
    },
    # ---- Seizoenen ----
    "lente": {
        "factoid_extra": (
            "In de lente komen veel bloemen en bomen in bloei, en is de lucht "
            "gevuld met de geur van vers gras en bloesem."
        ),
        "kleur_tips": [
            "Bloesembomen zijn roze of wit met lichtbruine takken.",
            "Voor een frisse lentetuin: lichtgroene grassprietjes en kleurrijke bloemen.",
            "Een helderblauwe lucht met een zonnetje in geel maakt het vrolijk.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en seizoensbeleving",
    },
    "zomer": {
        "factoid_extra": (
            "De zomer is het warmste seizoen — perfect voor ijsjes, zwemmen "
            "en lange avonden buitenspelen."
        ),
        "kleur_tips": [
            "Een zonnebloem is warmgeel met een bruin hart — heel zomers!",
            "De zee is azuurblauw, het strand zandkleurig met hier en daar een kleurige handdoek.",
            "Ijsjes in roze, geel of groen met een bruin hoorntje zijn altijd een goed idee.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en zomerplezier",
    },
    "herfst": {
        "factoid_extra": (
            "In de herfst verkleuren de bladeren naar rood, oranje, geel en "
            "bruin — een waar kleurenpalet in de natuur."
        ),
        "kleur_tips": [
            "Herfstbladeren: gebruik warme tinten zoals rood, oranje, geel en bruin.",
            "Een eekhoorn is roodbruin met een grote, pluizige staart.",
            "Een pompoen is oranje met een groene steel — heel herfstig.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en seizoensbeleving",
    },
    "winter": {
        "factoid_extra": (
            "Elke sneeuwvlok is uniek — geen twee sneeuwvlokken ter wereld "
            "hebben ooit precies dezelfde vorm."
        ),
        "kleur_tips": [
            "Sneeuw is wit met lichtblauwe schaduwen voor diepte.",
            "Een pinguïn is zwart met een witte buik en een oranje snavel — heel herkenbaar.",
            "Een besneeuwde dennenboom is donkergroen met een wit laagje sneeuw op de takken.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en seizoensbeleving",
    },
    # ---- Voertuigen ----
    "auto's": {
        "factoid_extra": (
            "De allereerste auto ooit werd in 1886 gebouwd door Karl Benz — "
            "hij noemde zijn uitvinding de 'Benz Patent-Motorwagen'."
        ),
        "kleur_tips": [
            "Raceauto's zijn vaak rood, blauw of zwart met opvallende striping.",
            "Voor metallic effect: gebruik een basiskleur en lichtere lijntjes voor glans.",
            "De wielen zijn grijs of zwart met zilveren velgen — heel stoer.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en concentratie",
    },
    "treinen": {
        "factoid_extra": (
            "De eerste trein op stoom reed in 1804 in Wales — een eenvoudige "
            "machine die zware ladingen over een mijnspoor kon trekken."
        ),
        "kleur_tips": [
            "Een stoomlocomotief is zwart met een rode of gouden bel en wijzerplaat.",
            "Personenrijtuigen zijn vaak blauw, groen of rood met crèmekleurige randen.",
            "De rails zijn zilver of grijs met houten dwarsliggers in bruin.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-9 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en techniek",
    },
    "vliegtuigen": {
        "factoid_extra": (
            "De Wright Brothers maakten in 1903 de eerste gemotoriseerde vlucht "
            "— die duurde slechts 12 seconden."
        ),
        "kleur_tips": [
            "Verkeersvliegtuigen zijn meestal wit met gekleurde strepen of logo's.",
            "Een propeller is zilver of grijs, de cockpit donkerblauw of zwart.",
            "De lucht op de achtergrond is lichtblauw met witte wolken — heel vrolijk.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    # ---- Ruimte ----
    "ruimte": {
        "factoid_extra": (
            "Onze Melkweg bevat naar schatting 100 tot 400 miljard sterren — "
            "en het heelal bevat weer honderden miljarden van die sterrenstelsels."
        ),
        "kleur_tips": [
            "De ruimte is diepzwart of donkerpaars met fonkelende witte, gele en blauwe sterren.",
            "Planeten hebben allerlei kleuren: Mars is rood, Jupiter oranje-bruin, de aarde blauw-groen.",
            "Een raket is vaak wit of zilver met een oranje vlam aan de onderkant.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    "planeten": {
        "factoid_extra": (
            "Jupiter is de grootste planeet van ons zonnestelsel — "
            "er zouden meer dan 1.300 aardes in passen!"
        ),
        "kleur_tips": [
            "Jupiter heeft oranje en bruine banden — heel herkenbaar.",
            "Saturnus is zachtgeel met prachtige ringen eromheen.",
            "De aarde is blauw met groene continenten en witte poolkappen.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    # ---- Regenboog & overig ----
    "regenboog": {
        "factoid_extra": (
            "Een regenboog ontstaat wanneer zonlicht door regendruppels wordt "
            "gebogen — de klassieke zeven kleuren zijn rood, oranje, geel, "
            "groen, blauw, indigo en violet."
        ),
        "kleur_tips": [
            "Gebruik de zeven kleuren in deze volgorde: rood, oranje, geel, groen, blauw, indigo, violet.",
            "De kleuren lopen vloeiend in elkaar over — gebruik kleurpotloden voor een zacht effect.",
            "Voeg een paar witte wolkjes toe aan beide uiteinden voor een extra mooi effect.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Kleurherkenning en fijne motoriek",
    },
    "harten": {
        "factoid_extra": (
            "Het hart-symbool ♥ wordt al sinds de middeleeuwen gebruikt om "
            "liefde en vriendschap uit te drukken."
        ),
        "kleur_tips": [
            "Een klassiek hart is rood, maar pasteltinten (roze, lichtgeel) zijn ook prachtig.",
            "Voeg kleine versieringen toe: stipjes, sterretjes of een strik.",
            "Combineer meerdere harten in verschillende kleuren voor een speels effect.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Emotionele expressie en fijne motoriek",
    },
    # ---- Sport ----
    "voetbal": {
        "factoid_extra": (
            "Voetbal is wereldwijd de populairste sport: meer dan 250 miljoen "
            "mensen spelen het in meer dan 200 landen."
        ),
        "kleur_tips": [
            "Een voetbal is traditioneel zwart-wit met vijf- en zeshoekige panelen.",
            "De trui van het Nederlands elftal is helder oranje — een echte blikvanger!",
            "Een groen voetbalveld met witte lijnen maakt het tafereel compleet.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Concentratie en fijne motoriek",
    },
    # ---- Natuur ----
    "weer": {
        "factoid_extra": (
            "Een onweersbui ontstaat wanneer warme, vochtige lucht snel opstijgt "
            "en afkoelt — de wrijving tussen waterdruppels veroorzaakt de bliksem."
        ),
        "kleur_tips": [
            "Een zon is geel met stralen — altijd vrolijk!",
            "Een regenwolk is donkergrijs met blauwe of zwarte regendruppels.",
            "Een regenboog verschijnt na de regen: alle kleuren van de regenboog.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Observatie en fijne motoriek",
    },
    "bergen": {
        "factoid_extra": (
            "De Mount Everest is met 8.849 meter de hoogste berg ter wereld — "
            "en hij groeit nog steeds een paar millimeter per jaar."
        ),
        "kleur_tips": [
            "Sneeuw op bergtoppen is wit met lichtblauwe schaduwen.",
            "Een groene alpenweide met paarse en gele bloemen is heel kleurrijk.",
            "Een heldere blauwe lucht maakt het geheel extra fris.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    "strand": {
        "factoid_extra": (
            "Het langste strand ter wereld is Praia do Cassino in Brazilië — "
            "het is maar liefst 254 kilometer lang."
        ),
        "kleur_tips": [
            "Zand is zandkleurig (warm geel of beige) met hier en daar een schelpje.",
            "De zee is azuurblauw of turkoois, met witte schuimkoppen op de golven.",
            "Een zonsondergang boven zee is een prachtige mix van oranje, roze en paars.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    "zee": {
        "factoid_extra": (
            "De oceanen produceren ongeveer 50% van de zuurstof die we inademen — "
            "dankzij minuscule algen, fytoplankton genaamd."
        ),
        "kleur_tips": [
            "De zee is azuurblauw, met witte golfjes en soms een groene glans.",
            "Een zeester is oranje, rood of paars.",
            "Een vis kan alle kleuren hebben: rood, geel, blauw, gestreept — laat je fantasie de vrije loop.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en natuurbeleving",
    },
    # ---- Eten ----
    "fruit": {
        "factoid_extra": (
            "Een appel die je eet, bevat stukjes sterrenlicht: het zonlicht dat "
            "de boom het hele jaar door heeft opgevangen."
        ),
        "kleur_tips": [
            "Een aardbei is helder rood met groene blaadjes bovenop.",
            "Een banaan is warmgeel, soms met kleine bruine vlekjes.",
            "Een sinaasappel is oranje met een dunne, oranje schil.",
        ],
        "doelgroep": "Peuters en kleuters (3-6 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en gezond eten",
    },
    "ijsjes": {
        "factoid_extra": (
            "De oudst bekende vorm van ijs werd meer dan 2.000 jaar geleden "
            "in China gemaakt — een mix van melk, rijst en sneeuw."
        ),
        "kleur_tips": [
            "Een bolletje ijs is roze (aardbei), geel (citroen), bruin (chocolade) of groen (pistache).",
            "Het hoorntje is goudbruin of beige.",
            "Voeg een kers bovenop toe — klassiek rood met een groen steeltje.",
        ],
        "doelgroep": "Peuters en kleuters (3-6 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en zomerplezier",
    },
    "taarten": {
        "factoid_extra": (
            "De duurste taart ooit gemaakt kostte meer dan 30 miljoen euro — "
            "hij was versierd met 4.000 diamanten."
        ),
        "kleur_tips": [
            "Een verjaardagstaart is roze, blauw of geel met gekleurde kaarsjes.",
            "Chocolade is donkerbruin, aardbeienglazuur is roze.",
            "Kaarsjes in allerlei kleuren met een klein geel vlammetje erboven.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en feestvreugde",
    },
    # ---- Overig ----
    "ballonnen": {
        "factoid_extra": (
            "De allereerste rubberen ballon werd uitgevonden door Michael Faraday "
            "in 1824 — hij gebruikte het om waterstofgas te bestuderen."
        ),
        "kleur_tips": [
            "Een feestelijke bos ballonnen: rood, blauw, geel, groen, roze — alle kleuren van de regenboog.",
            "Een glanzende ballon heeft een klein wit stipje bovenin voor de reflectie.",
            "De touwtjes zijn grijs of zwart.",
        ],
        "doelgroep": "Peuters en kleuters (3-6 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en feestvreugde",
    },
    "speelgoed": {
        "factoid_extra": (
            "De oudst bekende pop is meer dan 4.000 jaar oud en werd gevonden "
            "in een Egyptisch graf."
        ),
        "kleur_tips": [
            "Een teddybeer is bruin of beige met een roze of zwarte neus.",
            "Bouwblokken in alle kleuren van de regenboog: rood, blauw, geel, groen.",
            "Een pop kan een kleedje aan in roze, geel of rood — met vlechten of krullen in het haar.",
        ],
        "doelgroep": "Peuters en kleuters (3-6 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en spel",
    },
    "kastelen": {
        "factoid_extra": (
            "Het oudste nog bestaande kasteel ter wereld is de Burg Alhambra in "
            "Spanje, gebouwd in 889 na Christus."
        ),
        "kleur_tips": [
            "Een middeleeuws kasteel is grijs of zandkleurig met blauwe of rode vlaggen.",
            "De torens eindigen vaak in puntige, blauwe of rode daken.",
            "Een ophaalbrug in donkerbruin hout geeft het kasteel een echt riddergevoel.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    "ridders": {
        "factoid_extra": (
            "Een ridder droeg een harnas dat tot 25 kilo kon wegen — "
            "vandaar dat ridders vaak alleen in gevechten vochten, niet de hele dag door."
        ),
        "kleur_tips": [
            "Een harnas is zilver of grijs met gouden versieringen.",
            "De vlag of schild kan in rood, blauw, geel of groen — de familie-kleuren.",
            "Het paard van de ridder is meestal bruin, zwart of wit.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    "prinsen en prinsessen": {
        "factoid_extra": (
            "De bekendste prinses uit de sprookjes is misschien wel Doornroosje — "
            "haar verhaal is meer dan 400 jaar oud."
        ),
        "kleur_tips": [
            "Een prinses draagt een mooie jurk in roze, blauw, geel of wit met gouden accenten.",
            "De kroon is goudkleurig met gekleurde edelstenen.",
            "Lange, golvende haren in blond, bruin of zwart maken het plaatje compleet.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-9 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    "magische bossen": {
        "factoid_extra": (
            "In de Keltische mythologie geloven veel volkeren dat magische bossen "
            "het domein zijn van elfen, feeën en andere betoverde wezens."
        ),
        "kleur_tips": [
            "Een magisch bos is diepgroen met zachte, mysterieuze tinten.",
            "Paddenstoelen zijn rood met witte stippen — heel sprookjesachtig.",
            "Een blauwachtig of paars waas over het bos geeft het een betoverd effect.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    "sterren en maan": {
        "factoid_extra": (
            "De maan is ongeveer 384.400 kilometer van de aarde verwijderd — "
            "een astronaut doet er ongeveer drie dagen over om er te komen."
        ),
        "kleur_tips": [
            "De maan is zachtgeel of wit met grijze kraters.",
            "Een nachthemel is diep donkerblauw of paars met fonkelende sterren.",
            "Een stralende ster heeft een geel hart met witte punten — heel feeëriek.",
        ],
        "doelgroep": "Peuters en kleuters (3-8 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Verbeeldingskracht en fijne motoriek",
    },
    "bouwplaats": {
        "factoid_extra": (
            "De grootste kraan ter wereld is 250 meter hoog — "
            "hoger dan de Eiffeltoren!"
        ),
        "kleur_tips": [
            "Een hijskraan is geel met zwarte accenten — heel herkenbaar op een bouwplaats.",
            "Veiligheidshelmen zijn geel, oranje of wit.",
            "Bakstenen zijn roodbruin, cement is grijs, hout is lichtbruin.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en techniek",
    },
    "fietsen": {
        "factoid_extra": (
            "De allereerste fiets, de 'laufmaschine', werd in 1817 uitgevonden "
            "door Karl Drais — hij had nog geen pedalen!"
        ),
        "kleur_tips": [
            "Een fietsframe is rood, blauw of zwart met verchroomde (zilveren) onderdelen.",
            "De banden zijn zwart met een witte reflecterende streep aan de zijkant.",
            "Een fietshelm is vaak roze, blauw of groen voor kinderen.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en veiligheid",
    },
    "tuin": {
        "factoid_extra": (
            "Eén theelepel gezonde tuingrond bevat meer levende organismen dan "
            "er mensen op aarde zijn."
        ),
        "kleur_tips": [
            "Gras is heldergroen, aarde is donkerbruin.",
            "Bloemen in alle kleuren: rood, geel, paars, roze, oranje.",
            "Een tuinslang is vaak groen of blauw — een echte blikvanger.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en natuurbeleving",
    },
    "bomen": {
        "factoid_extra": (
            "De oudste boom ter wereld is een grove den in Californië die "
            "naar schatting 4.853 jaar oud is."
        ),
        "kleur_tips": [
            "Een eikenblad is diepgroen met een getande rand.",
            "Een berk heeft een witte stam met zwarte streepjes.",
            "In de herfst kleuren bladeren rood, oranje, geel en bruin.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en natuurbeleving",
    },
    "paddestoelen": {
        "factoid_extra": (
            "Paddenstoelen zijn geen planten, maar behoren tot een eigen rijk: "
            "de fungi. Ze hebben geen bladgroen en groeien vaak in vochtige, "
            "donkere plekken."
        ),
        "kleur_tips": [
            "Een vliegenzwam is rood met witte stippen — heel klassiek.",
            "Een cantharel is oranje-geel en lijkt een beetje op een trechter.",
            "Een oesterzwam is grijs-bruin met een waaiervormige hoed.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (5-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en natuur",
    },
    "groenten": {
        "factoid_extra": (
            "Wortelen waren oorspronkelijk paars — de oranje wortel zoals wij "
            "die kennen is pas in de 17e eeuw in Nederland ontstaan."
        ),
        "kleur_tips": [
            "Een wortel is oranje met een groene steel bovenop.",
            "Een tomaat is rood, soms geel of oranje — heel kleurrijk.",
            "Broccoli is donkergroen met kleine, dichte roosjes.",
        ],
        "doelgroep": "Peuters en kleuters (3-7 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en gezond eten",
    },
    "carnaval": {
        "factoid_extra": (
            "Carnaval wordt al sinds de middeleeuwen gevierd in Nederland, "
            "vooral in het zuiden — het is de feestelijke afsluiting van de "
            "vastentijd."
        ),
        "kleur_tips": [
            "Carnavalskleding is vaak rood, geel of groen — de drie kleuren van de Limburgse vlag.",
            "Confetti in alle kleuren van de regenboog: paars, goud, zilver, rood, blauw.",
            "Een prins of prinses van carnaval draagt een prachtig versierd kostuum.",
        ],
        "doelgroep": "Kleuters en oudere kinderen (4-10 jaar)",
        "formaat": "PDF en PNG (A4)",
        "onderwijskundige_waarde": "Fijne motoriek en feestvreugde",
    },
}


#: Standaard fallback-profiel voor thema's die niet in de database staan.
DEFAULT_PROFILE: dict[str, Any] = {
    "factoid_extra": (
        "Elke kleurplaat is een mooie gelegenheid om lekker te ontspannen "
        "en je fantasie de vrije loop te laten."
    ),
    "kleur_tips": [
        "Kies kleuren die je zelf mooi vindt — er is geen goed of fout.",
        "Gebruik minstens drie tinten van elke hoofdkleur voor schaduw en diepte.",
        "Vergeet de ogen niet: een klein zwart puntje geeft het figuurtje direct leven.",
    ],
    "doelgroep": "Kleuters (4-7 jaar)",
    "formaat": "PDF en PNG (A4)",
    "onderwijskundige_waarde": "Fijne motoriek en creativiteit",
}


def get_profile(theme: str) -> dict[str, Any]:
    """Haalt het profiel op voor een gegeven thema; valt terug op DEFAULT_PROFILE."""
    if not theme:
        return dict(DEFAULT_PROFILE)
    norm = theme.lower().strip()
    if norm in THEME_PROFILES:
        return THEME_PROFILES[norm]
    # Probeer gedeeltelijke match.
    for key, profile in THEME_PROFILES.items():
        if key in norm or norm in key:
            return profile
    return dict(DEFAULT_PROFILE)


# ---------------------------------------------------------------------------
# Synoniemen & zinsvarianten
# ---------------------------------------------------------------------------

#: Synoniemen voor 'kleurplaat' om variatie aan te brengen.
SYNONYMS_KLEURPLAAT = [
    "kleurplaat",
    "kleurplaatje",
    "kleurtekening",
    "inkleuropdracht",
    "tekenopdracht",
    "kleurbladzijde",
]

#: Varianten van openingszinnen voor de introductie.
INTRO_OPENERS = [
    "{Subject} is een {syn} waar kinderen urenlang plezier aan beleven.",
    "Deze {syn} met {subject} is perfect voor jonge kunstenaars in spe.",
    "Op zoek naar een leuke {syn} over {subject}? Dan ben je hier aan het juiste adres!",
    "Laat je kinderen kennismaken met de wondere wereld van {subject} via deze gratis {syn}.",
    "Deze {syn} staat garant voor uren kleurplezier en creatieve ontwikkeling.",
    "Een vrolijke {syn} voor kinderen die graag met {subject} bezig zijn.",
    "{Subject} is een tijdloos onderwerp dat door kinderen van alle leeftijden wordt geliefd.",
]

#: Educatieve alinea varianten (ouder-/leerkracht-focus).
EDUCATIONAL_LINES = [
    "Voor ouders en leerkrachten is deze {syn} meer dan alleen een leuke bezigheid. "
    "Tijdens het inkleuren oefenen kinderen hun fijne motoriek, leren ze kleuren "
    "herkennen en ontwikkelen ze hun concentratievermogen. De herhalende bewegingen "
    "binnen de lijntjes helpen bij de ontwikkeling van de pengreep, een essentiële "
    "vaardigheid voor het leren schrijven.",

    "Kleuren is niet alleen ontspannend, maar ook ontzettend leerzaam. Door deze "
    "{syn} in te kleuren, oefent je kind geduld, oog-handcoördinatie en creatief "
    "denken. Leerkrachten gebruiken dit soort opdrachten graag in de klas om "
    "kinderen op een speelse manier te laten werken aan hun {onderwijs}.",

    "Uit onderzoek blijkt dat kinderen die regelmatig kleuren, beter scoren op "
    "taken die concentratie en planning vereisen. Deze {syn} rond {subject} is "
    "daarom een waardevolle aanvulling op elke (voor)schoolse activiteit. "
    "Het stimuleert de {onderwijs} en geeft kinderen een gevoel van voldoening "
    "als ze de tekening helemaal hebben ingekleurd.",

    "Deze {syn} biedt een mooie gelegenheid om met je kind in gesprek te gaan "
    "over {subject}. Stel vragen als 'Wat vind jij het mooiste aan {subject}?' "
    "of 'Welke kleur past het best bij {subject}?'. Zo wordt kleuren niet alleen "
    "een creatieve, maar ook een leerzame activiteit die de taalontwikkeling en "
    "{onderwijs} van je kind stimuleert.",
]

#: Hoe-to stappen introducties.
HOWTO_INTROS = [
    "Volg deze eenvoudige stappen om het meeste uit je {syn} te halen:",
    "Aan de slag? Zo gebruik je deze {syn} in een paar simpele stappen:",
    "Hoe download, print en kleur je deze {syn}? We leggen het stap voor stap uit:",
    "Klaar om te beginnen? Met deze handleiding print en kleur je de {syn} in een handomdraai:",
]

HOWTO_STEP1 = [
    "Klik op de knop '<strong>Kleurplaat printen</strong>' of '<strong>Download</strong>' onder de afbeelding om de {syn} te downloaden. De download start direct en is volledig gratis.",
    "Download de {syn} via de afbeeldingslink onder de tekening. Het bestand is direct beschikbaar in PDF- of PNG-formaat.",
    "Sla de {syn} op door op de print- of downloadknop te klikken. Geen registratie nodig — printen kan meteen.",
]
HOWTO_STEP2 = [
    "Print de {syn} op A4-papier voor het beste resultaat. Instellingen: 100% schaal, liggend of staand (afhankelijk van de afbeelding).",
    "Kies voor stevig printpapier (minimaal 100 grams) zodat wasco en viltstiften niet doordrukken. Stel je printer in op de normale kwaliteit.",
    "Gebruik bij voorkeur wat dikker papier zodat de kleuren mooi uitkomen en de {syn} niet scheurt tijdens het kleuren.",
]
HOWTO_STEP3 = [
    "Kleur de {syn} in met potloden, viltstiften, wasco of een mix hiervan. Werk van licht naar donker voor het mooiste resultaat.",
    "Pak je favoriete kleurmaterialen erbij. Voor fijne details zijn dunne viltstiften of kleurpotloden ideaal; voor grote vlakken is wasco of houtskool heel geschikt.",
    "Begin met de lichte kleuren en werk langzaam naar de donkere tinten. Dat voorkomt dat per ongeluk donkere vegen op de lichtere delen komen.",
]
HOWTO_STEP4 = [
    "Kleur de {syn} stap voor stap in. Wacht eventueel tot een laag droog is voordat je een nieuwe kleur erover aanbrengt — vooral bij viltstiften.",
    "Neem de tijd en geniet van het kleuren. Voor de allerkleinsten is een uurtje rustig kleuren al een hele prestatie!",
    "Werk rustig en geniet van het proces. Een {syn} inkleuren is een meditatief klusje — neem er de tijd voor.",
]

#: Kleur-tips section varianten.
KLEURTIPS_INTROS = [
    "Nog wat handige {tips_label} voor het inkleuren van deze {syn}:",
    "Met deze {tips_label} maak je van deze {syn} een echt kunstwerkje:",
    "Voor de allermooiste resultaten kun je deze {tips_label} proberen:",
    "Onze favoriete {tips_label} voor deze {syn}:",
]


def _pick(variants: list[str], **kwargs: str) -> str:
    """Kiest een willekeurige variant en past er de placeholders op toe."""
    text = random.choice(variants)
    try:
        return text.format(**kwargs)
    except (KeyError, IndexError):
        # Veilig terugvallen bij ontbrekende placeholders.
        return text


# ---------------------------------------------------------------------------
# Content builders
# ---------------------------------------------------------------------------

def _slugify_subject(theme: str) -> str:
    """Maakt een URL-slug van het thema voor de categorie-link."""
    s = (theme or "").lower().strip()
    s = re.sub(r"[^\w\s-]", "", s)
    s = re.sub(r"[\s_]+", "-", s)
    s = re.sub(r"-{2,}", "-", s)
    return s.strip("-")


def _detect_audience(theme: str) -> str:
    """Bepaalt de doelgroep op basis van thema-categorieën."""
    cats = detect_theme_categories(theme)
    if not cats:
        return "Peuters en kleuters (3-7 jaar)"
    if any(c in cats for c in {"dinosaurus", "ruimte", "voertuig", "kasteel"}):
        return "Kleuters en oudere kinderen (5-10 jaar)"
    if "fantasie" in cats:
        return "Kleuters en oudere kinderen (4-9 jaar)"
    if "feestdag" in cats:
        return "Peuters en kleuters (3-7 jaar)"
    return "Peuters en kleuters (3-7 jaar)"


def _normalize_factoid(factoid: str) -> str:
    """Maakt het AI-gegenereerde factoid veilig voor in HTML."""
    if not factoid:
        return ""
    text = factoid.strip()
    # Verwijder eventuele Markdown-sterretjes en platte HTML-tags.
    text = text.replace("**", "").replace("*", "")
    text = re.sub(r"<[^>]+>", "", text)
    # Verbeter enkele veelvoorkomende AI-fouten.
    text = re.sub(r"\bHerfstse\s+(\w+)", r"Herfst\1", text)
    return text


def build_intro(theme: str, factoid: str = "") -> str:
    """Bouwt de introductie (2-3 alinea's, 120-160 woorden)."""
    profile = get_profile(theme)
    subject = theme or "dit onderwerp"
    subject_lower = subject.lower()
    syn = random.choice(SYNONYMS_KLEURPLAAT)
    onderwijs = profile.get("onderwijskundige_waarde", "fijne motoriek").lower()

    # Alinea 1: variabele opener met factoid of thema-weetje.
    factoid_text = _normalize_factoid(factoid)
    extra_text = profile.get("factoid_extra", "")
    opener = _pick(INTRO_OPENERS, Subject=subject, syn=syn, subject=subject_lower)

    p1_lines = [opener]
    if factoid_text:
        p1_lines.append(factoid_text)
    elif extra_text:
        p1_lines.append(extra_text)
    else:
        p1_lines.append(
            f"{subject.capitalize()} is een geliefd onderwerp voor kinderen "
            "en leent zich perfect voor een gezellig uurtje kleurplezier."
        )

    p1 = " ".join(p1_lines)

    # Alinea 2: educatieve inhoud.
    p2 = _pick(EDUCATIONAL_LINES, syn=syn, subject=subject_lower, onderwijs=onderwijs)

    # Alinea 3: SEO-zin met long-tail keyword + uitnodiging tot download.
    p3 = (
        f"Of je nu op zoek bent naar een {subject_lower} {syn} om te printen voor een "
        f"regenachtige middag, of een {subject_lower} tekentips zoekt voor in de klas — "
        f"deze gratis {subject_lower} {syn} printen of downloaden doe je hier in een "
        f"handomdraai. Veel kleurplezier!"
    )

    return f'<p>{p1}</p>\n<p>{p2}</p>\n<p>{p3}</p>'


def build_details_table(theme: str) -> str:
    """Bouwt de 'Details van deze kleurplaat' HTML-tabel."""
    profile = get_profile(theme)
    subject = theme or "Kleurplaat"
    syn = random.choice(SYNONYMS_KLEURPLAAT)
    doelgroep = _detect_audience(theme) or profile.get("doelgroep", "Peuters en kleuters (3-7 jaar)")
    formaat = profile.get("formaat", "PDF en PNG (A4)")
    categorie = _categorie_label(theme) or "Algemeen"

    rows = [
        ("Categorie", f"{subject} ({categorie})"),
        ("Bestandsformaat", formaat),
        ("Doelgroep", doelgroep),
        ("Type", syn.capitalize()),
        ("Licentie", "Gratis voor persoonlijk en educatief gebruik (niet voor commerciële doorverkoop)"),
    ]

    rows_html = "\n".join(
        f"      <tr>\n"
        f"        <th scope=\"row\">{_escape(label)}</th>\n"
        f"        <td>{_escape(value)}</td>\n"
        f"      </tr>"
        for label, value in rows
    )

    return (
        f'<h3>Details van deze {syn}</h3>\n'
        f'<table class="kk-details-table" style="width:100%;border-collapse:collapse;margin:1.5em 0;">\n'
        f'  <tbody>\n{rows_html}\n  </tbody>\n'
        f'</table>'
    )


def _categorie_label(theme: str) -> str:
    """Vertaalt de gedetecteerde categorie-labels naar een leesbare categorie."""
    cats = detect_theme_categories(theme)
    if not cats:
        return ""
    label_map = {
        "dier": "Dieren",
        "dinosaurus": "Dinosaurussen",
        "zeedier": "Zeedieren",
        "insect": "Insecten",
        "plant": "Planten & Bloemen",
        "fantasie": "Fantasie & Sprookjes",
        "voertuig": "Voertuigen",
        "ruimte": "Ruimte & Sterren",
        "feestdag": "Feestdagen",
        "seizoen": "Seizoenen",
        "sport": "Sport",
        "natuur": "Natuur & Weer",
        "vervoer": "Bouw & Vervoer",
        "eten": "Eten & Drinken",
        "gevoel": "Liefde & Gevoelens",
        "kasteel": "Kastelen & Ridders",
        "speelgoed": "Speelgoed",
    }
    return ", ".join(label_map.get(c, c.capitalize()) for c in cats[:2])


def _escape(text: str) -> str:
    """Eenvoudige HTML-escape voor in tabelcellen en attributen."""
    if text is None:
        return ""
    return (
        str(text)
        .replace("&", "&amp;")
        .replace("<", "&lt;")
        .replace(">", "&gt;")
        .replace('"', "&quot;")
    )


def build_howto(theme: str) -> str:
    """Bouwt de stap-voor-stap handleiding."""
    profile = get_profile(theme)
    subject = theme or "kleurplaat"
    subject_lower = subject.lower()
    syn = random.choice(SYNONYMS_KLEURPLAAT)

    intro_text = _pick(HOWTO_INTROS, syn=syn, subject=subject_lower)

    steps = [
        ("1. Download de " + syn, _pick(HOWTO_STEP1, syn=syn)),
        ("2. Print de " + syn, _pick(HOWTO_STEP2, syn=syn)),
        ("3. Kleur de " + syn, _pick(HOWTO_STEP3, syn=syn)),
        ("4. Werk rustig en geniet", _pick(HOWTO_STEP4, syn=syn)),
    ]

    steps_html = "\n".join(
        f"  <li><strong>{_escape(title)}.</strong> {text}</li>"
        for title, text in steps
    )

    # Specifieke kleurtips voor dit thema.
    color_tips = profile.get("kleur_tips") or DEFAULT_PROFILE["kleur_tips"]
    tips_intro = _pick(KLEURTIPS_INTROS, syn=syn, tips_label="kleurtips")
    tips_html = "\n".join(f"  <li>{_escape(tip)}</li>" for tip in color_tips)

    howto_html = (
        f'<h3>Hoe gebruik je deze {subject_lower} {syn}?</h3>\n'
        f'<p>{intro_text}</p>\n'
        f'<ol>\n{steps_html}\n</ol>\n'
        f'<h4>{tips_intro.split(":")[0]}:</h4>\n'
        f'<ul>\n{tips_html}\n</ul>'
    )
    return howto_html


def build_keywords_footer(theme: str) -> str:
    """Bouwt een natuurlijk stuk tekst met long-tail keywords (geen losse opsomming)."""
    subject = theme or "kleurplaat"
    subject_lower = subject.lower()
    syn = random.choice(SYNONYMS_KLEURPLAAT)
    slug = _slugify_subject(theme)
    categorie = _categorie_label(theme)

    keywords_text = (
        f"Wil je meer {subject_lower} {SYNONYMS_KLEURPLAAT[0]} printen of downloaden, "
        f"of ben je op zoek naar andere {subject_lower} tekentips en inspiratie? "
        f"Neem dan een kijkje bij onze andere "
    )

    if categorie:
        keywords_text += f"{categorie.lower()} kleurplaten"
    else:
        keywords_text += f"{subject_lower} kleurplaten"

    keywords_text += (
        f" voor nog meer gratis {subject_lower} {SYNONYMS_KLEURPLAAT[0]} en "
        f"kleurplaten om te printen. Of ontdek onze volledige collectie "
        f"kleurplaten voor kinderen van alle leeftijden."
    )

    # Voeg een footer-link toe (interne link voor SEO).
    if slug:
        link = (
            f'<p style="margin-top: 24px; font-style: italic;">'
            f'Zie ook onze andere <a href="/kleurplaat_categorie/{slug}/">{_escape(subject)}</a> '
            f'{syn} voor meer inspiratie.</p>'
        )
    else:
        link = (
            f'<p style="margin-top: 24px; font-style: italic;">'
            f'Ontdek meer kleurplaten in onze <a href="/kleurplaten/">volledige collectie</a>.</p>'
        )

    return f'<p>{keywords_text}</p>\n{link}'


# ---------------------------------------------------------------------------
# Hoofd-functie
# ---------------------------------------------------------------------------

def build_rich_content(theme: str, factoid: str = "") -> str:
    """Bouw het volledige rijke content-blok voor een kleurplaat.

    Args:
        theme: Het onderwerp van de kleurplaat (bijv. "Dinosaurussen").
        factoid: Een optioneel AI-gegenereerd weetje (1-2 zinnen).

    Returns:
        HTML-string met het volledige enrichment-blok, ingepakt in
        ``<!-- KK-ENRICH-START --> ... <!-- KK-ENRICH-END -->`` markers.
    """
    if not theme or not theme.strip():
        theme = "Kleurplaat"

    intro_html = build_intro(theme, factoid)
    table_html = build_details_table(theme)
    howto_html = build_howto(theme)
    keywords_html = build_keywords_footer(theme)

    # H2-titel boven het hele blok (kan SEO-schema van de pagina ondersteunen).
    subject = theme.strip()
    h2 = f'<h2 class="seo-subtitle">{_escape(subject)} kleurplaat printen, downloaden en inkleuren</h2>'

    body = "\n".join([h2, intro_html, table_html, howto_html, keywords_html])

    return f"{ENRICH_START}\n{body}\n{ENRICH_END}"


def strip_old_enrichment(content: str) -> str:
    """Verwijdert oude enrichment-blokken (en de zwakke legacy-blokken).

    Hiermee is de functie idempotent: meerdere runs produceren nooit
    gedupliceerde content.
    """
    if not content:
        return content
    # Verwijder de oude enrichment markers.
    content = ENRICH_BLOCK_RE.sub("", content)
    # Verwijder oude seo-subtitle intro's.
    content = LEGACY_INTRO_RE.sub("", content)
    # Verwijder oude "Zie ook onze andere" footers.
    content = LEGACY_FOOTER_RE.sub("", content)
    # Ruim lege <p>-tags op.
    content = re.sub(r"<p[^>]*>\s*</p>", "", content)
    # Meerdere lege regels terugbrengen naar max 2.
    content = re.sub(r"(\s*\n){3,}", "\n\n", content)
    return content.strip()


def build_post_content(
    theme: str,
    factoid: str = "",
    existing_content: str = "",
    image_html: str = "",
) -> str:
    """Bouw de volledige nieuwe ``post_content`` voor een kleurplaat-post.

    Args:
        theme: Onderwerp van de kleurplaat.
        factoid: Optioneel AI-factoid (1-2 zinnen).
        existing_content: Huidige content van de post (om de oude
            enrichment/SEO-blokken veilig te strippen).
        image_html: Optionele HTML voor de afbeelding (bijv. ``<img ...>``).
            Wordt bovenaan de content geplaatst, voor het enrichment-blok.

    Returns:
        Volledige ``post_content``-string, klaar voor de WP REST API.
    """
    enrichment = build_rich_content(theme, factoid)

    # Bestaande content strippen van oude blokken.
    cleaned = strip_old_enrichment(existing_content or "")

    parts: list[str] = []
    if image_html:
        parts.append(image_html)
    if cleaned:
        parts.append(cleaned)
    parts.append(enrichment)

    return "\n\n".join(parts)


# ---------------------------------------------------------------------------
# WP REST API: bulk enrichment van bestaande posts
# ---------------------------------------------------------------------------

def _get_env() -> dict[str, str]:
    if load_dotenv is None:
        raise ImportError("python-dotenv niet geïnstalleerd")
    dotenv_path = SCRIPT_DIR / ".env"
    if dotenv_path.exists():
        load_dotenv(dotenv_path=dotenv_path)
    return {
        "url": os.getenv("WORDPRESS_URL", "").rstrip("/"),
        "user": os.getenv("WORDPRESS_USERNAME", ""),
        "pass": os.getenv("WORDPRESS_APP_PASSWORD", ""),
    }


def _fetch_all_kleurplaten(auth: tuple[str, str], base_url: str) -> list[dict]:
    """Haalt alle kleurplaten-posts op, met paginatie."""
    if requests is None:
        raise ImportError("requests niet geïnstalleerd")
    posts: list[dict] = []
    page = 1
    while True:
        resp = requests.get(
            f"{base_url}/wp-json/wp/v2/kleurplaten",
            auth=auth,
            params={"per_page": 100, "page": page, "context": "edit", "status": "publish"},
            timeout=30,
        )
        if resp.status_code != 200:
            print(f"[FOUT] Ophalen posts mislukt: HTTP {resp.status_code} - {resp.text[:200]}")
            break
        batch = resp.json()
        if not batch:
            break
        posts.extend(batch)
        if len(batch) < 100:
            break
        page += 1
    return posts


def _update_post_content(
    auth: tuple[str, str], base_url: str, post_id: int, content: str
) -> bool:
    if requests is None:
        raise ImportError("requests niet geïnstalleerd")
    resp = requests.put(
        f"{base_url}/wp-json/wp/v2/kleurplaten/{post_id}",
        auth=auth,
        json={"content": content},
        timeout=30,
    )
    if resp.status_code in (200, 201):
        return True
    print(f"  [FOUT] Update post {post_id} mislukt: HTTP {resp.status_code} - {resp.text[:200]}")
    return False


def _extract_theme(post: dict) -> str:
    """Probeert het thema te achterhalen uit title of taxonomietermen."""
    title = post.get("title", {}).get("rendered", "").strip()
    # Strategie 1: neem alles in de titel behalve stopwoorden.
    stopwoorden = {
        "gratis", "kleurplaat", "kleurplaten", "kleurplaatje", "kleurplaatjes",
        "van", "een", "voor", "kinderen", "om", "te", "printen", "downloaden",
        "download", "print", "ingekleurd", "gekleurd", "voorbeeld",
    }
    if title:
        woorden = re.split(r"\s+", title)
        schoon = [w for w in woorden if w.lower().strip(",.!?") not in stopwoorden]
        if schoon:
            onderwerp = " ".join(schoon).strip(" ,.!?")
            if onderwerp:
                return onderwerp[:80]
    # Strategie 2: pak de eerste taxonomie-term.
    for tax_key in ("kleurplaat_categorie", "kleurplaat_thema"):
        terms = post.get(tax_key, [])
        if terms and isinstance(terms, list) and terms:
            # Term-slug bevat vaak het onderwerp.
            return str(terms[0]).replace("-", " ").capitalize()
    return title or "Kleurplaat"


def enrich_existing_posts(
    dry_run: bool = False,
    limit: int = 0,
    delay_seconds: float = 0.3,
    sleep_callback=None,
) -> dict[str, int]:
    """Verrijk alle bestaande kleurplaten-posts met rijke content.

    Args:
        dry_run: Toon wijzigingen zonder ze door te voeren.
        limit: Maximaal aantal te verwerken posts (0 = alles).
        delay_seconds: Vertraging tussen API-calls (rate-limit vriendelijk).
        sleep_callback: Optionele callable om de delay te overschrijven
            (handig voor tests).

    Returns:
        Dict met statistieken: ``updated``, ``skipped``, ``failed``.
    """
    if requests is None:
        raise ImportError("requests niet geïnstalleerd (pip install requests)")

    env = _get_env()
    if not all([env["url"], env["user"], env["pass"]]):
        raise RuntimeError(
            "Stel WORDPRESS_URL, WORDPRESS_USERNAME en WORDPRESS_APP_PASSWORD "
            "in (in .env of als environment variables)."
        )

    auth = (env["user"], env["pass"])
    base_url = env["url"]

    print(f"[START] Ophalen kleurplaten van {base_url} ...")
    posts = _fetch_all_kleurplaten(auth, base_url)
    print(f"[INFO] {len(posts)} posts gevonden")

    stats = {"updated": 0, "skipped": 0, "failed": 0, "total": len(posts)}

    for idx, post in enumerate(posts, start=1):
        if limit and stats["updated"] >= limit:
            print(f"[LIMIET] {limit} posts verwerkt. Stoppen.")
            break

        post_id = post.get("id")
        title = post.get("title", {}).get("rendered", "").strip()
        if not post_id or not title:
            stats["skipped"] += 1
            continue

        # Onderwerp uit titel of taxonomie afleiden.
        theme = _extract_theme(post)

        # Huidige content ophalen (bij context=edit is 'raw' beschikbaar).
        current_content = (
            post.get("content", {}).get("raw", "")
            or post.get("content", {}).get("rendered", "")
        )

        # Bestaat er al een (verse) enrichment met markers? Sla dan over.
        if ENRICH_START in current_content and ENRICH_END in current_content:
            print(f"  [SKIP] Post {post_id} ('{title[:60]}'): al verrijkt.")
            stats["skipped"] += 1
            continue

        new_content = build_post_content(
            theme=theme,
            factoid="",  # Geen factoid beschikbaar bij bestaande posts.
            existing_content=current_content,
        )

        # Toon een korte preview.
        plain = re.sub(r"<[^>]+>", "", new_content)
        word_count = len(plain.split())
        print(
            f"  [{idx}/{len(posts)}] Post {post_id} ('{title[:50]}') → "
            f"thema='{theme}', ~{word_count} woorden"
        )

        if dry_run:
            stats["updated"] += 1
            continue

        if _update_post_content(auth, base_url, post_id, new_content):
            stats["updated"] += 1
        else:
            stats["failed"] += 1

        if sleep_callback:
            sleep_callback()
        elif delay_seconds > 0:
            time.sleep(delay_seconds)

    print("\n" + "=" * 60)
    print(f"[KLAAR] {stats['updated']} verrijkt, "
          f"{stats['skipped']} overgeslagen, {stats['failed']} mislukt "
          f"(van {stats['total']} totaal)")
    print("=" * 60)
    return stats


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def _cli() -> int:
    parser = argparse.ArgumentParser(
        description=(
            "Verrijk alle kleurplaat-posts op kinderkleurplaten.com met unieke, "
            "SEO-vriendelijke content. Lost Google AdSense 'Thin Content' afwijzing op."
        ),
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Toon wat er zou veranderen, maar voer geen updates uit.",
    )
    parser.add_argument(
        "--limit",
        type=int,
        default=0,
        help="Verwerk maximaal N posts (0 = alles). Handig voor test-runs.",
    )
    parser.add_argument(
        "--delay",
        type=float,
        default=0.3,
        help="Vertraging in seconden tussen API-calls (default 0.3).",
    )
    parser.add_argument(
        "--preview",
        type=str,
        default=None,
        help="Toon een preview van de gegenereerde content voor het opgegeven thema, "
             "zonder de WP API aan te roepen. Voorbeeld: --preview 'Dinosaurussen'",
    )
    args = parser.parse_args()

    if args.preview:
        print(build_rich_content(args.preview, "Wist je dat tyrannosaurus rex tot 12 meter lang kon worden?"))
        return 0

    try:
        stats = enrich_existing_posts(
            dry_run=args.dry_run,
            limit=args.limit,
            delay_seconds=args.delay,
        )
        return 0 if stats["failed"] == 0 else 1
    except (ImportError, RuntimeError) as exc:
        print(f"[FOUT] {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(_cli())
