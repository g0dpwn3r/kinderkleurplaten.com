#!/usr/bin/env python3
"""
create_legal_pages.py
=====================

Maakt (of actualiseert) de wettelijk verplichte pagina's voor
kinderkleurplaten.com via de WordPress REST API:

  1. Privacybeleid        — AVG/GDPR + Google AdSense cookie-richtlijnen
  2. Algemene voorwaarden — gratis-gebruiksvoorwaarden, intellectueel
                            eigendom en aansprakelijkheidsdisclaimer
  3. Over ons             — persoonsgebonden introductie over de oprichter

De script detecteert de bestaande privacybeleid-pagina (slug
"privacybeleid-kinderkleurplaten-com") en repareert de lelijke slug
naar "/privacybeleid/" om bestaande SEO-waarde te behouden.

Gebruik
-------
    # Preview van alle drie pagina's zonder WP API-aanroepen:
    python3 create_legal_pages.py --dry-run

    # Alsl preview, maar alleen één pagina tonen:
    python3 create_legal_pages.py --dry-run --only-over-ons

    # Als concept ("draft") aanmaken in WordPress:
    python3 create_legal_pages.py

    # Direct publiceren ("publish"):
    python3 create_legal_pages.py --publish

    # Forceer-overschrijven van bestaande "algemene-voorwaarden" / "over-ons"
    # pagina's (voorkomt dubbele pagina's bij herhaalde runs):
    python3 create_legal_pages.py --force

Afhankelijkheden
----------------
    pip install requests python-dotenv

Credentials (in `.env`):
    WORDPRESS_URL
    WORDPRESS_USERNAME
    WORDPRESS_APP_PASSWORD
"""
from __future__ import annotations

import argparse
import json
import os
import re
import sys
import textwrap
from dataclasses import dataclass
from pathlib import Path
from typing import Optional

try:
    import requests
    from dotenv import load_dotenv
except ImportError as exc:  # pragma: no cover
    raise ImportError(
        "Installeer eerst 'requests' en 'python-dotenv': "
        "pip install requests python-dotenv"
    ) from exc


# ---------------------------------------------------------------------------
# Configuratie
# ---------------------------------------------------------------------------

SCRIPT_DIR = Path(__file__).parent.resolve()
load_dotenv(dotenv_path=SCRIPT_DIR / ".env")

SITE_URL = "kinderkleurplaten.com"
OWNER_NAME = "Carlon van Spijker"
CONTACT_EMAIL = "cvsshred7@gmail.com"
AP_DATE = "8 juli 2026"  # Datum van laatste update


@dataclass
class PageSpec:
    """Beschrijving van één WordPress-pagina die moet worden aangemaakt."""

    slug: str
    title: str
    content_fn_name: str  # naam van de content-renderfunctie
    repair_legacy_slug: Optional[str] = None  # oude slug die moet worden hervalid


# Pagina-specificatie
PAGES = {
    "privacy": PageSpec(
        slug="privacybeleid",
        title="Privacybeleid",
        content_fn_name="privacy_content",
        repair_legacy_slug="privacybeleid-kinderkleurplaten-com",
    ),
    "algemeen": PageSpec(
        slug="algemene-voorwaarden",
        title="Algemene voorwaarden",
        content_fn_name="algemene_voorwaarden_content",
    ),
    "over-ons": PageSpec(
        slug="over-ons",
        title="Over ons",
        content_fn_name="over_ons_content",
    ),
}


# ---------------------------------------------------------------------------
# Pagina-content
#
# Elke functie retourneert volledige, semantische HTML5. Er is opzettelijk
# géén inline-styling behalve waar strikt noodzakelijk (tabellayout). De
# thema-stijl van het kinderthema regelt de rest.
# ---------------------------------------------------------------------------

def privacy_content() -> str:
    """Genereert het Privacybeleid."""
    return textwrap.dedent(f"""\
<p>Bedankt voor je bezoek aan <strong>{SITE_URL}</strong>. Het beschermen
van jouw privé-leven vinden wij erg belangrijk. In dit privacybeleid
lees je welke gegevens wij verzamelen, waarom we dat doen, en welke
keuzes je kunt maken over jouw gegevens. Dit beleid is opgesteld in
overeenstemming met de <strong>Algemene Verordening Gegevensbescherming
(AVG)</strong>, ook bekend als de General Data Protection Regulation
(GDPR).</p>

<h2>1. Verantwoordelijke voor de gegevensverwerking</h2>
<p>De gegevensverantwoordelijke van deze website is:</p>
<ul>
    <li><strong>Naam</strong>: {OWNER_NAME}</li>
    <li><strong>E-mail</strong>: <a href="mailto:{CONTACT_EMAIL}">{CONTACT_EMAIL}</a></li>
    <li><strong>Website</strong>: https://{SITE_URL}</li>
</ul>
<p>Je kunt voor vragen over dit privacybeleid, een verzoek om inzage in je
gegevens, of een bezwaarschrift altijd contact opnemen via het
bovenstaande e-mailadres. Wij reageren binnen maximaal dertig dagen.</p>

<h2>2. Welke gegevens verwerken wij?</h2>
<p>Wij vragen bezoekers <strong>geen account</strong> aan te maken en
verzamelen daarom geen namen, e-mailadressen of telefoonnummers via de
site zelf. Toch kunnen er enkele technische gegevens worden vastgelegd
om de site goed te laten werken:</p>
<ul>
    <li><strong>IP-adres</strong> (anoniem, in verkorte vorm)</li>
    <li><strong>Browser</strong> en besturingssysteem (de zogenaamde <em>user-agent</em>)</li>
    <li><strong>Tijdstip van het websitebezoek</strong></li>
    <li><strong>Bekeken pagina's</strong> op de website</li>
    <li><strong>Cookies</strong> van technische, analytische en advertentiedoeleinden (zie verder hoofdstuk 5)</li>
</ul>
<p>Deze gegevens zijn anoniem: zij zijn niet direct te koppelen aan jouw
naam of identiteit. Wij gebruiken ze uitsluitend om de website
technisch correct te laten draaien, de laadtijden te verbeteren en de
algemene bezoekersstatistieken bij te houden.</p>

<h2>3. Doelen van de gegevensverwerking</h2>
<p>Wij verwerken jouw gegevens voor de volgende doelen:</p>
<ol>
    <li><strong>Technische werking</strong> van de website (laadtijden, foutafhandeling, beveiliging).</li>
    <li><strong>Anonieme statistieken</strong> over het gebruik van de site (welke pagina's worden veel bezocht).</li>
    <li><strong>Advertenties</strong>: het tonen van relevante advertenties via onze advertentiepartner (zie hoofdstuk 4).</li>
    <li><strong>Veiligheid</strong>: het detecteren en voorkomen van misbruik, aanvallen of spam.</li>
</ol>

<h2>4. Google AdSense — advertenties en cookies</h2>
<p>Deze website maakt gebruik van <strong>Google AdSense</strong>, een
advertentiedienst van <em>Google Ireland Limited, Gordon House, Barrow
Street, Dublin 4, Ierland</em> ("Google"). Google AdSense plaatst
advertenties op onze website op basis van voorafgaande bezoeken aan
onze site of aan andere websites op het internet.</p>

<h3>4.1 DoubleClick-cookie (DART-cookie)</h3>
<p>Om advertenties weer te geven, maakt Google gebruik van de zogenaamde
<strong>DoubleClick-cookie</strong> (ook wel <strong>DART-cookie</strong>
genoemd). Via deze cookie kan Google:</p>
<ul>
    <li>Advertenties tonen die passen bij eerdere bezoeken aan deze website of aan andere websites.</li>
    <li>Advertenties van Google en diens partners personaliseren op basis van jouw surfgedrag.</li>
    <li>Technische gegevens verzamelen (zoals IP-adres) voor advertentiedoeleinden.</li>
</ul>

<h3>4.2 Afmelden voor gepersonaliseerde advertenties</h3>
<p>Je kunt je op elk moment <strong>afmelden</strong> voor gepersonaliseerde
advertenties. Ga hiervoor naar de officiële pagina van Google:</p>
<ul>
    <li><a href="https://adssettings.google.com/authenticated" rel="nofollow noopener" target="_blank">
    Google Advertentie-instellingen (Google Ads Settings)</a></li>
</ul>
<p>Daarnaast kun je je afmelden voor advertenties van derden via
<a href="https://www.aboutads.info/choices/" rel="nofollow noopener">aboutads.info/choices</a>
of <a href="https://www.youronlinechoices.eu/nl/" rel="nofollow noopener">youronlinechoices.eu</a>.</p>

<h3>4.3 Google Privacybeleid</h3>
<p>Het volledige privacybeleid van Google lees je op
<a href="https://policies.google.com/privacy" rel="nofollow noopener" target="_blank">
policies.google.com/privacy</a>. Google verwerkt gegevens volgens de
eigen privacyregels, die voldoen aan de Europese AVG.</p>

<h2>5. Cookies</h2>
<p>Cookies zijn kleine tekstbestandjes die door een website op jouw
computer, tablet of telefoon worden gezet. Zij helpen de website goed
te werken en geven ons inzicht in het gebruik van de site.</p>

<h3>5.1 Soorten cookies op deze website</h3>
<p>Wij gebruiken drie soorten cookies:</p>
<ul>
    <li><strong>Functionele cookies</strong> — noodzakelijk voor het functioneren van de website, bijvoorbeeld om jouw voorkeuren te onthouden.</li>
    <li><strong>Anonieme analytische cookies</strong> — om te meten hoe vaak de website wordt bezocht en welke pagina's populair zijn.</li>
    <li><strong>Advertentiecookies</strong> — van derden (zie hoofdstuk 4) om relevante advertenties te tonen.</li>
</ul>

<h3>5.2 Rechtsgrond voor het gebruik van cookies</h3>
<p>Volgens de AVG is voor het plaatsen van cookies een rechtsgrond
noodzakelijk (artikel 6). Wij hanteren het volgende:</p>
<ul>
    <li><strong>Functionele cookies</strong>: noodzakelijk voor de werking van de website. Rechtsgrond: <em>gerechtvaardigd belang</em> (artikel 6(1)(f) AVG).</li>
    <li><strong>Anonieme analytische cookies</strong>: rechtsgrond: <em>toestemming</em> van de gebruiker (artikel 6(1)(a) AVG) via een cookie-banner.</li>
    <li><strong>Advertentiecookies van derden</strong>: rechtsgrond: <em>toestemming</em> van de gebruiker (artikel 6(1)(a) AVG) via een cookie-banner.</li>
</ul>

<h3>5.3 Bewaartermijn van cookies</h3>
<p>Functionele cookies worden maximaal één jaar bewaard. Technische
analysetegevens (zoals IP-adressen in verkorte vorm) worden maximaal
dertig dagen bewaard, tenzij een wettelijke bewaarplicht een langere
periode vereist. Advertentiecookies van Google hebben een bewaartermijn
zoals aangegeven in het <a href="https://policies.google.com/technologies/cookies" rel="nofollow noopener" target="_blank">
Google Cookies-beleid</a>.</p>

<h3>5.4 Cookies beheren of uitschakelen</h3>
<p>Je kunt cookies in jouw webbrowser volledig weigeren of bestaande
cookies verwijderen. De procedure verschilt per browser:</p>
<ul>
    <li><a href="https://support.google.com/chrome/answer/95647" rel="nofollow noopener" target="_blank">Google Chrome</a></li>
    <li><a href="https://support.mozilla.org/nl/kb/cookies-toestaan-en-verwijderen" rel="nofollow noopener" target="_blank">Mozilla Firefox</a></li>
    <li><a href="https://support.apple.com/nl-nl/guide/safari/sfri11471/mac" rel="nofollow noopener" target="_blank">Apple Safari</a></li>
    <li><a href="https://support.microsoft.com/nl-nl/microsoft-edge/cookies-verwijderen-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" rel="nofollow noopener" target="_blank">Microsoft Edge</a></li>
</ul>
<p><strong>Let op</strong>: het uitschakelen van cookies kan ertoe
leiden dat sommige functies van de website niet meer correct werken.</p>

<h2>6. Derde partijen en externe diensten</h2>
<p>Naast Google AdSense kan deze website verbindingen onderhouden met
andere diensten van derden, bijvoorbeeld om inhoud te delen via
sociale-media-platforms (zoals Pinterest of Facebook) of om externe
afbeeldingen te laden. Wanneer je op een link naar een externe dienst
klikt, kan die dienst cookies plaatsen of andere gegevens verwerken
volgens het eigen privacybeleid.</p>
<p>Wij hebben geen invloed op de omgang van derde partijen met jouw
gegevens. Raadpleeg altijd het privacybeleid van de betreffende dienst
voordat je gegevens deelt.</p>

<h2>7. Internationale doorgifte van gegevens</h2>
<p>Google kan gegevens verwerken in landen buiten de Europese Economische
Ruimte (EER). Wij vertrouwen daarbij op wettelijke waarborgen zoals de
<em>EU–VS Data Privacy Framework</em> of de <em>Standard Contractual
Clauses</em> die door de Europese Commissie zijn goedgekeurd. Meer
informatie hierover vind je in het privacybeleid van Google.</p>

<h2>8. Jouw rechten volgens de AVG</h2>
<p>Je hebt een aantal belangrijke rechten onder de AVG. Deze rechten zijn:</p>
<ul>
    <li><strong>Recht op inzage</strong> (artikel 15): je mag opvragen welke persoonsgegevens wij over jou verwerken.</li>
    <li><strong>Recht op rectificatie</strong> (artikel 16): indien gegevens onjuist zijn, kun je ons vragen ze te corrigeren.</li>
    <li><strong>Recht op verwijdering</strong> (artikel 17): het zogenaamde "recht om vergeten te worden". Gegevens die niet meer noodzakelijk zijn, worden op jouw verzoek verwijderd.</li>
    <li><strong>Recht op beperking van verwerking</strong> (artikel 18): je kunt vragen om de verwerking tijdelijk te beperken.</li>
    <li><strong>Recht op dataportabiliteit</strong> (artikel 20): je mag jouw gegevens in een gangbaar formaat ontvangen of doorsturen naar een andere partij.</li>
    <li><strong>Recht van bezwaar</strong> (artikel 21): je mag bezwaar maken tegen verwerking op basis van gerechtvaardigd belang of voor direct marketing.</li>
    <li><strong>Recht om toestemming in te trekken</strong>: wanneer wij je gegevens verwerken op basis van toestemming, kun je die toestemming op elk moment weer intrekken.</li>
    <li><strong>Recht om een klacht in te dienen</strong> bij de <a href="https://www.autoriteitpersoonsgegevens.nl" rel="nofollow noopener" target="_blank">
    Autoriteit Persoonsgegevens</a> (AP), de Nederlandse toezichthouder.</li>
</ul>

<h2>9. Hoe kun je jouw rechten uitoefenen?</h2>
<p>Om gebruik te maken van jouw rechten of om een vraag te stellen over
dit privacybeleid, kun je op elk moment contact opnemen:</p>
<ul>
    <li><strong>E-mail</strong>: <a href="mailto:{CONTACT_EMAIL}">{CONTACT_EMAIL}</a></li>
</ul>
<p>Wij reageren binnen <strong>maximaal dertig dagen</strong> op jouw
verzoek en zullen jouw identiteit verifiëren voordat we informatie
delen — dit om jouw privacy te beschermen.</p>

<h2>10. Bewaartermijnen van jouw gegevens</h2>
<p>Wij bewaren persoonsgegevens niet langer dan strikt noodzakelijk is
om de doelen uit dit privacybeleid te bereiken. Concreet:</p>
<ul>
    <li><strong>Technische logbestanden</strong> (met IP-adres): maximaal 30 dagen.</li>
    <li><strong>Anonieme statistieken</strong>: onbeperkt, maar zonder herleidbare persoonsgegevens.</li>
    <li><strong>Cookies</strong>: volgens de in hoofdstuk 5 genoemde termijnen.</li>
    <li><strong>Correspondentie per e-mail</strong>: zo lang als nodig om jouw verzoek te behandelen, en vervolgens maximaal een jaar ter archivering.</li>
</ul>

<h2>11. Kinderen jonger dan 16 jaar</h2>
<p>Onze website is gericht op kinderen en hun ouders of verzorgers, maar
is niet bestemd voor kinderen onder de 16 jaar zonder toestemming van
een ouder of voogd. Wij verzamelen niet bewust persoonsgegevens van
kinderen onder de 16 jaar. Indien je vermoedt dat een kind onder de
16 jaar zonder toestemming persoonsgegevens heeft achtergelaten, neem
dan contact op via <a href="mailto:{CONTACT_EMAIL}">{CONTACT_EMAIL}</a>.
Wij verwijderen die gegevens dan zo snel mogelijk.</p>

<h2>12. Wijzigingen in dit privacybeleid</h2>
<p>Wij behouden ons het recht voor om dit privacybeleid te wijzigen. De
meest recente versie is altijd beschikbaar op deze pagina. Bij
belangrijke wijzigingen plaatsen wij een duidelijke melding op de
website. De laatste update van dit beleid is van toepassing per
<strong>{AP_DATE}</strong>.</p>

<h2>13. Vragen of klachten?</h2>
<p>Heb je een vraag over ons privacybeleid, wil je gebruikmaken van jouw
rechten, of heb je een klacht? Wij staan altijd open voor een gesprek
en streven naar een snelle en vriendelijke oplossing. Je bereikt ons
via:</p>
<ul>
    <li><strong>E-mail</strong>: <a href="mailto:{CONTACT_EMAIL}">{CONTACT_EMAIL}</a></li>
</ul>
<p>Komt het niet tot een oplossing dan heb je het recht om een klacht in
te dienen bij de toezichthouder, de <a href="https://www.autoriteitpersoonsgegevens.nl" rel="nofollow noopener" target="_blank">Autoriteit Persoonsgegevens</a>.</p>
""")


def algemene_voorwaarden_content() -> str:
    """Genereert de Algemene voorwaarden / Disclaimer."""
    return textwrap.dedent(f"""\
<p>Welkom op <strong>{SITE_URL}</strong>. Door het bezoeken van onze
website, het downloaden of afdrukken van kleurplaten ga je akkoord met
de onderstaande voorwaarden. Lees deze aandachtig door. Als je
hiermee niet akkoord gaat, verzoeken wij je deze site niet te
gebruiken.</p>

<h2>1. Algemeen</h2>
<p>Deze website — <strong>{SITE_URL}</strong> — wordt beheerd en
onderhouden door <strong>{OWNER_NAME}</strong>. De site heeft als doel
het gratis aanbieden van kleurplaten aan kinderen, ouders,
verzorgers en leerkrachten. Door het gebruik van deze website
aanvaard je zonder voorbehoud deze algemene voorwaarden en het
privacybeleid dat op deze site van toepassing is.</p>

<h2>2. Gratis gebruik voor persoonlijk en educatief gebruik</h2>
<p>Alle kleurplaten, bestanden (waaronder PDF's en PNG's) en andere
inhoud op deze website worden <strong>volledig gratis</strong>
aangeboden. Je mag de kleurplaten kosteloos downloaden, printen en
gebruiken voor de volgende doeleinden:</p>
<ul>
    <li><strong>Persoonlijk gebruik thuis</strong>: voor je eigen kinderen, in het gezin of binnen de familiekring.</li>
    <li><strong>Educatief gebruik</strong>: in basisscholen, peuterspeelzalen, kinderdagverblijven, naschoolse opvang en andere onderwijsinstellingen.</li>
    <li><strong>Therapeutisch en verzorgend gebruik</strong>: bijvoorbeeld in de logopedie, ergotherapie of ouderenzorg.</li>
</ul>
<p>Het is <strong>uitdrukkelijk niet toegestaan</strong> om de
kleurplaten, tekeningen of de bestanden op enige andere manier te
gebruiken dan hierboven beschreven — met name niet voor commerciële
doeleinden.</p>

<h2>3. Verbod op commercieel hergebruik</h2>
<p>Het is <strong>streng verboden</strong> om de kleurplaten, tekeningen
of bestanden van deze website op welke manier dan ook te verkopen, te
verhuren, op te nemen in betaalde producten, door te verkopen via
marktplaatsen, print-on-diensten of andere commerciële platforms.</p>
<p>Dit verbod is van toepassing op:</p>
<ul>
    <li>Het verkopen van fysieke of digitale kleurplaten die afkomstig zijn van deze website.</li>
    <li>Het opnemen van onze kleurplaten in boeken, tijdschriften, educatieve software of (leer)pakketten die tegen betaling worden aangeboden.</li>
    <li>Het (laten) produceren en verhandelen van gemaakte producten — zoals shirts, mokken of ansichtkaarten — die gebruikmaken van de originele kleurplaten.</li>
</ul>
<p>Schending van deze regel kan leiden tot juridische stappen en
vergoeding van eventuele schade.</p>

<h2>4. Intellectueel eigendom</h2>
<p>Alle rechten van intellectuele eigendom op de inhoud van deze website
— waaronder de kleurplaten, de teksten, de lay-out, het ontwerp en de
gebruikte afbeeldingen — berusten bij <strong>{OWNER_NAME}</strong>,
tenzij anders vermeld.</p>
<p>Het is niet toegestaan om onderdelen van deze website — of hun
afgeleiden — te kopiëren, te bewerken of openbaar te maken zonder
voorafgaande schriftelijke toestemming. Dit geldt zowel voor
commercieel als voor niet-commercieel gebruik.</p>
<p>Kleurplaten die je zelf hebt ingekleurd op basis van onze tekeningen
mogen <strong>wel vrij worden gedeeld</strong> — bijvoorbeeld via
sociale media of binnen het gezin — zolang dit gebeurt op niet-</
>commerciële basis en met bronvermelding naar <em>{SITE_URL}</em>.</p>

<h2>5. Aansprakelijkheidsdisclaimer</h2>
<p>Hoewel wij de grootst mogelijke zorgvuldigheid nastreven bij het
samenstellen van de inhoud van deze website, kunnen wij helaas niet
instaan voor volledige juistheid, volledigheid of actualiteit van de
informatie.</p>
<p>{OWNER_NAME} is <strong>niet aansprakelijk</strong> voor:</p>
<ul>
    <li>Directe of indirecte schade die ontstaat door het gebruik van of de onmogelijkheid om deze website te gebruiken.</li>
    <li>Technische storingen, onderbrekingen, fouten of vertragingen in de website of bij het downloaden van bestanden.</li>
    <li>Schade veroorzaakt door virussen, malware of andere schadelijke software die mogelijk via derden of externe bronnen op jouw apparaat terechtkomt.</li>
    <li>Inhoudelijke fouten of onvolledigheden in de informatie op de website.</li>
</ul>
<p>Het gebruik van de website en het downloaden van kleurplaten is
geheel voor eigen risico van de bezoeker.</p>

<h2>6. Externe links</h2>
<p>Op onze website staan mogelijk links naar externe websites. Wij hebben
geen invloed op de inhoud, het ontwerp of het privacybeleid van deze
sites. De aanwezigheid van een link betekent niet automatisch dat de
eigenaar van {OWNER_NAME} de inhoud van die site onderschrijft.</p>
<p>Voor vragen over externe websites verwijzen wij je naar hun eigen
eigenaar.</p>

<h2>7. Wijzigingen</h2>
<p>Wij behouden ons het recht voor om deze algemene voorwaarden
ten allen tijde te wijzigen, zonder voorafgaande kennisgeving.
De meest recente versie is altijd in te zien op deze pagina. Je wordt
geadviseerd deze voorwaarden regelmatig te raadplegen.</p>

<h2>8. Toepasselijk recht en geschillen</h2>
<p>Op deze algemene voorwaarden is uitsluitend het <strong>Nederlandse
recht</strong> van toepassing. Geschillen die voortvloeien uit het
gebruik van deze website of deze voorwaarden worden uitsluitend
behandeld door de bevoegde rechter in Nederland.</p>

<h2>9. Contact</h2>
<p>Heb je vragen over deze algemene voorwaarden, wil je toestemming
vragen voor gebruik buiten het reguliere kader, of heb je een klacht?
Neem dan gerust contact op:</p>
<ul>
    <li><strong>E-mail</strong>: <a href="mailto:{CONTACT_EMAIL}">{CONTACT_EMAIL}</a></li>
</ul>
<p>Laatste update: <strong>{AP_DATE}</strong>.</p>
""")


def over_ons_content() -> str:
    """Genereert de 'Over ons'-pagina."""
    return textwrap.dedent(f"""\
<p>Welkom op <strong>{SITE_URL}</strong> — dé plek waar kinderen, ouders
en leerkrachten terecht kunnen voor kleurrijke inspiratie. Leuk dat je
een kijkje komt nemen achter de schermen!</p>

<h2>Wie zit er achter deze website?</h2>
<p>Mijn naam is <strong>{OWNER_NAME}</strong> en ik ben oprichter en
beheerder van <em>{SITE_URL}</em>. Wat ooit begon als een kleine
verzameling kleurplaten om mijn eigen kinderen bezig te houden op een
regendag, is inmiddels uitgegroeid tot een groeiende verzameling
kleurplaten van hoge kwaliteit. Deze website is mijn passieproject —
een plek waar plezier, creativiteit en leren samenkomen.</p>

<h2>Waarom {SITE_URL}?</h2>
<p>In een wereld vol schermen en apps geloof ik sterk in de waarde van
ouderwets knutselen, kleuren en tekenen. Kinderen leren beter door te
spelen, en kleuren is daarvoor een van de beste middelen. Het is niet
alleen leuk — kleuren helpt kinderen op verschillende manieren:</p>
<ul>
    <li><strong>Fijne motoriek</strong>: de herhalende bewegingen van potlood en papier helpen bij het ontwikkelen van de hand-oogcoördinatie en de pengreep die kinderen later nodig hebben bij het leren schrijven.</li>
    <li><strong>Creativiteit</strong>: kleuren prikkelt de fantasie. Kinderen leren zelf keuzes te maken in kleur, compositie en stijl — een krachtige oefening in creatief denken.</li>
    <li><strong>Concentratie en rust</strong>: kinderen worden in onze drukke wereld vaak overprikkeld. Een kleurplaat biedt een moment van rust, focus en eigen inbreng.</li>
    <li><strong>Zelfvertrouwen</strong>: als een kind de hele kleurplaat heeft ingekleurd, is de trots van het resultaat vaak direct voelbaar — een kleine overwinning die opstapelt.</li>
</ul>
<p>Met <em>{SITE_URL}</em> wil ik een <strong>centrale, veilige en
inspirerende plek</strong> bieden waar ouders, verzorgers en leerkrachten
gratis kleurplaten van hoge kwaliteit kunnen vinden en downloaden —
zonder gedoe, zonder accounts en zonder reclamelawaaier.</p>

<h2>Voor wie is deze website?</h2>
<p>Onze kleurplaten zijn bedoeld voor iedereen die kinderen een leuk,
leerzaam kleurmoment wil bezorgen:</p>
<ul>
    <li><strong>Ouders en verzorgers</strong> die een regenachtige middag gezellig willen maken.</li>
    <li><strong>Leerkrachten</strong> die kleurrijke ondersteuning zoeken voor hun lessen.</li>
    <li><strong>Kinderopvang, trimsessies en logopedie</strong>: therapeuten die kleurplaten gebruiken als oefenmateriaal.</li>
    <li><strong>Grootouders, ooms, tantes en buren</strong> die een handige activiteit zoeken voor oppas- of logéemomenten.</li>
</ul>
<p>Alle kleurplaten zijn <strong>100% gratis</strong> om te downloaden en
te printen voor persoonlijk en educatief gebruik. Meer weten? Op onze
pagina <a href="/algemene-voorwaarden/">Algemene voorwaarden</a> lees je
alles over wat wel en niet is toegestaan.</p>

<h2>Veiligheid op de voorgrond</h2>
<p>Als je een website bezoekt samen met je kinderen, wil je niet
geconfronteerd worden met onverwachte advertenties of vervelende
pop-ups. Daarom vind ik het belangrijk dat deze website een
<strong>veilige en kindvriendelijke omgeving</strong> blijft. Wij
plaatsen alleen advertenties die relevant zijn en wij laten jouw
gegevens — en die van jouw kinderen — nooit onnodig verzamelen. Meer
hierover lees je in ons <a href="/privacybeleid/">privacybeleid</a>.</p>

<h2>Heb je suggesties?</h2>
<p>Deze website groeit door de feedback en wensen van bezoekers. Mis je
een thema in onze kleurplaten? Heb je een idee voor een nieuwe
categorie? Of wil je gewone een kleurplaat zien die wij nog niet
hebben? Dan hoor ik dat ontzettend graag! Schrijf me gerust een kort
berichtje via:</p>
<ul>
    <li><strong>E-mail</strong>: <a href="mailto:{CONTACT_EMAIL}">{CONTACT_EMAIL}</a></li>
</ul>
<p>Bedankt voor je bezoek — en heel veel kleur- en knutselplezier
gewenst!</p>
<p style="margin-top: 24px;"><strong>{OWNER_NAME}</strong><br />
Initiatiefnemer van <em>{SITE_URL}</em></p>
""")


# ---------------------------------------------------------------------------
# WordPress REST API helpers
# ---------------------------------------------------------------------------

def _wp_auth() -> tuple[str, str]:
    user = os.getenv("WORDPRESS_USERNAME", "")
    pw = os.getenv("WORDPRESS_APP_PASSWORD", "")
    if not user or not pw:
        raise RuntimeError(
            "Stel WORDPRESS_USERNAME en WORDPRESS_APP_PASSWORD in in `.env`."
        )
    return user, pw


def _wp_url() -> str:
    url = os.getenv("WORDPRESS_URL", "").rstrip("/")
    if not url:
        raise RuntimeError("Stel WORDPRESS_URL in in `.env`.")
    return url


def _find_page_by_slug(slug: str, auth: tuple[str, str], base: str) -> Optional[int]:
    """Retourneert de id van een pagina op slug, of None als deze niet bestaat."""
    resp = requests.get(
        f"{base}/wp-json/wp/v2/pages",
        auth=auth,
        params={"slug": slug, "status": "publish,draft", "per_page": 10},
        timeout=30,
    )
    if resp.status_code != 200:
        print(f"  [WAARSCHUWING] Ophalen pagina '{slug}' mislukt: HTTP {resp.status_code}")
        return None
    pages = resp.json()
    for page in pages:
        if page.get("slug") == slug:
            return int(page["id"])
    return None


def _create_or_update_page(
    *,
    spec: PageSpec,
    html: str,
    status: str,
    force: bool,
    auth: tuple[str, str],
    base: str,
) -> dict:
    """
    Maak een pagina aan of update een bestaande. Retouneert een korte
    status-dict voor de samenvatting.
    """
    payload = {
        "title": spec.title,
        "slug": spec.slug,
        "content": html,
        "status": status,
    }

    existing_id = _find_page_by_slug(spec.slug, auth, base)

    # Speciaal geval: repareer legacy-slug van bestaande privacybeleid-pagina.
    if existing_id is None and spec.repair_legacy_slug:
        repair_id = _find_page_by_slug(spec.repair_legacy_slug, auth, base)
        if repair_id is not None:
            print(
                f"  [REPAIR] Bestaande pagina gevonden onder legacy-slug "
                f"'{spec.repair_legacy_slug}' (id={repair_id}) → slug naar '{spec.slug}'"
            )
            payload["slug"] = spec.slug
            resp = requests.put(
                f"{base}/wp-json/wp/v2/pages/{repair_id}",
                auth=auth,
                json=payload,
                timeout=30,
            )
            if resp.status_code in (200, 201):
                return {"action": "repaired", "id": repair_id, "link": resp.json().get("link", "")}
            print(f"    FOUT: slug-repair mislukt voor id={repair_id}: {resp.text[:200]}")
            return {"action": "repair-failed", "id": repair_id}
        existing_id = None

    # Al bekende pagina? Bijwerken of negeren.
    if existing_id is not None:
        if force:
            resp = requests.put(
                f"{base}/wp-json/wp/v2/pages/{existing_id}",
                auth=auth,
                json=payload,
                timeout=30,
            )
            if resp.status_code in (200, 201):
                return {"action": "updated", "id": existing_id, "link": resp.json().get("link", "")}
            print(f"    FOUT: update van id={existing_id} mislukt: {resp.text[:200]}")
            return {"action": "update-failed", "id": existing_id}
        return {"action": "exists", "id": existing_id, "link": f"{base}/{spec.slug}/"}

    # Niet gevonden: aanmaken.
    resp = requests.post(
        f"{base}/wp-json/wp/v2/pages",
        auth=auth,
        json=payload,
        timeout=30,
    )
    if resp.status_code in (200, 201):
        return {"action": "created", "id": resp.json().get("id"), "link": resp.json().get("link", "")}
    print(f"    FOUT: aanmaken '{spec.slug}' mislukt: {resp.text[:300]}")
    return {"action": "create-failed", "id": None}


# ---------------------------------------------------------------------------
# Content lookup
# ---------------------------------------------------------------------------

_CONTENT_FNS = {
    "privacy_content": privacy_content,
    "algemene_voorwaarden_content": algemene_voorwaarden_content,
    "over_ons_content": over_ons_content,
}


def _word_count(html: str) -> int:
    plain = re.sub(r"<[^>]+>", " ", html)
    return len(plain.split())


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def _parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description=(
            "Aanmaken/update de wettelijke pagina's voor kinderkleurplaten.com "
            "(AVG/GDPR-privacybeleid, algemene voorwaarden, over ons)."
        ),
    )
    parser.add_argument(
        "--dry-run",
        action="store_true",
        help="Toon de HTML van alle te genereren pagina's zonder de WP API aan te roepen.",
    )
    parser.add_argument(
        "--only",
        choices=("privacy", "algemeen", "over-ons"),
        default=None,
        help="Genereer alleen één specifieke pagina (gebruik samen met --dry-run om te previewen).",
    )
    parser.add_argument(
        "--publish",
        action="store_true",
        help="Publiceer de pagina direct ('publish' status). Zonder deze vlag worden ze als 'draft' aangemaakt.",
    )
    parser.add_argument(
        "--force",
        action="store_true",
        help="Overschrijf bestaande pagina's met dezelfde slug (voorkomt duplicatie).",
    )
    parser.add_argument(
        "--stats",
        action="store_true",
        help="Toon woordenaantallen per pagina (werkt zonder WP API, nuttig voor kwaliteitscheck).",
    )
    return parser.parse_args()


def main() -> int:
    args = _parse_args()
    keys = (args.only,) if args.only else tuple(PAGES.keys())
    status = "publish" if args.publish else "draft"

    # Altijd --stats meedraaien als --dry-run om de inhoud te kwantificeren.
    if args.stats or args.dry_run:
        for key in keys:
            spec = PAGES[key]
            fn = _CONTENT_FNS[spec.content_fn_name]
            html = fn()
            words = _word_count(html)
            print(f"[{key:<11}] {spec.title:<28} — {words} woorden")
        print()

    if args.dry_run:
        for key in keys:
            spec = PAGES[key]
            fn = _CONTENT_FNS[spec.content_fn_name]
            print("=" * 78)
            print(f"[{key}] {spec.title} — slug '/{spec.slug}/'")
            print("=" * 78)
            print(fn())
            print()
        return 0

    auth = _wp_auth()
    base = _wp_url()

    summary: list[tuple[str, dict]] = []
    print(f"[START] {len(keys)} pagina('{len(keys) > 1 and 's' or ''}') aanmaken op {base} als status='{status}'")

    for key in keys:
        spec = PAGES[key]
        fn = _CONTENT_FNS[spec.content_fn_name]
        html = fn()
        print(f"\n[{key}] {spec.title} → slug '{spec.slug}'")
        result = _create_or_update_page(
            spec=spec,
            html=html,
            status=status,
            force=args.force,
            auth=auth,
            base=base,
        )
        summary.append((key, result))

    print("\n" + "=" * 78)
    print("SAMENVATTING")
    print("=" * 78)
    for key, result in summary:
        action = result["action"]
        page_id = result.get("id")
        link = result.get("link", "")
        marker = {
            "created": "+",
            "updated": "~",
            "repaired": "✓",
            "exists": "•",
            "create-failed": "✗",
            "update-failed": "✗",
            "repair-failed": "✗",
        }.get(action, "?")
        print(f"  {marker} [{key}] action={action:13} id={str(page_id):>5}  {link}")

    failed = sum(1 for _, r in summary if r["action"].endswith("-failed"))
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
