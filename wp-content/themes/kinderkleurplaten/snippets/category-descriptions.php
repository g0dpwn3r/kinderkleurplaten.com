<?php
/**
 * Category descriptions and SEO metadata for kleurplaat_categorie taxonomy terms
 * 
 * @package Kinderkleurplaten
 */

/**
 * Get unique, SEO-optimized description for a kleurplaat_categorie term
 * 
 * @param string $term_name Name of the category term
 * @return string Category description (100-150 words)
 */
function kk_get_category_description($term_name) {
    $term_name_lower = strtolower($term_name);
    
    $descriptions = array(
        'dieren' => 'Kleurplaten van dieren helpen kinderen vertrouwd te raken met verschillende diersoorten uit huis en natuur. Ze leren de uiterlijke kenmerken herkennen, zoals vacht, veren of schubben, en ontdekken hoe dieren leven en bewegen. Door ze zelf in te kleuren, oefenen kinderen fijne motoriek en concentratie zonder druk. Ze kunnen bespreken welke dieren groot of klein zijn, waar ze wonen en wat ze eten. Deze kleurplaten zijn geschikt voor kinderen van 3-7 jaar en laten ruimte voor eigen ideeën over kleurkeuze. Begin vandaag met kleuren en ontdek de fascinerende wereld van dieren samen met je kind.',
        'dinosaurussen' => 'Kleurplaten van dinosaurussen brengen een stuk prehistorische geschiedenis tot leven voor jonge kinderen. Ze leren namen als T-rex, stegosaurus en triceratops kennen en ontdekken verschillen tussen vleeseters en planteneters. Terwijl ze de platen inkleuren, ontwikkelen ze nauwkeurigheid in fijne bewegingen en oefenen ze hun geheugen. Ouders kunnen vragen stellen over hoe groot ze waren, welke kenmerken ze hadden en hoe ze zich verplaatsten. Kinderen kunnen zelf kiezen of hun dinosaurus groen, bruin of felgekleurd wordt. Deze platen zijn geschikt voor kinderen van 4-8 jaar. Ga aan de slag en laat je kind een eigen dinosauruswereld creëren.',
        'boerderijdieren' => 'Met kleurplaten van boerderijdieren leren kinderen de dieren kennen die een rol spelen op de boerderij. Ze maken kennis met de koe, kip, varken, paard, geit en schapen en ontdekken waarvoor deze dieren worden gebruikt, zoals melk, eieren of wol. Het kleuren versterkt oog-handcoördinatie en stimuleert gesprek over dagelijkse rituelen op een boerderij. Kinderen kunnen zich voorstellen hoe een kalfje drinkt of hoe een haan kraait. Ook leren ze onderscheid maken tussen volwassen dieren en hun jongen. Deze kleurplaten zijn ideaal voor kinderen van 3-7 jaar. Pak je kleurpotloden en breng het leven op de boerderij tot leven.',
        'voertuigen' => 'Kleurplaten van voertuigen geven kinderen inzicht in de verschillende manieren om zich te verplaatsen. Van auto en vrachtwagen tot boot, vliegtuig, trein en brandweerwagen: elk voertuig heeft een eigen functie in onze omgeving. Terwijl ze kleuren, oefenen kinderen fijne motoriek en leren ze termen als wiel, stuur, raam en rem. Samen kun je praten over waar elk voertuig voor dient en wie ermee werkt. Kinderen ontwikkelen ook ruimtelijk inzicht door onderdelen te herkennen en te benoemen. Deze platen zijn geschikt voor kinderen van 3-7 jaar. Haal de kleurpotloden erbij en laat je kind kiezen welk voertuig het eerst mag inkleuren.',
        'feestdagen' => 'Kleurplaten van feestdagen helpen kinderen de bijzondere momenten van het jaar beter te begrijpen en te vieren. Of het nu gaat om Sinterklaas, Kerst, Pasen, Koningsdag of Halloween, elk feest heeft eigen tradities, kleuren en symbolen. Door deze plaatjes in te kleuren, kunnen kinderen actief meedoen aan de voorbereiding van een feest. Ze leren namen van attributen herkennen, zoals kaarsjes, pakjes of paaseieren. Het kleuren zorgt voor rust en draagt bij aan de feestelijke sfeer. Deze platen zijn geschikt voor kinderen van 3-8 jaar. Begin alvast met kleuren en bouw zo mee aan de spanning voor het feest.',
        'sprookjes en fantasy' => 'Kleurplaten van sprookjes en fantasy openen een magische wereld vol prinsessen, draken, feeën, kabouters en eenhoorns voor jonge kinderen. Ze leren bekende sprookjesfiguren kennen en stimuleren hun verbeelding door zelf een verhaal bij het plaatje te bedenken. Terwijl ze zorgvuldig kleuren, trainen ze hun concentratie en fijne motoriek. Ouders en kinderen kunnen samen praten over goede en minder goede keuzes, moed en vriendelijkheid. Ook biedt het kleuren ruimte om gevoelens en ideeën uit te drukken. Deze platen zijn geschikt voor kinderen van 4-8 jaar. Haal je kleurpotloden en laat de magie beginnen.',
        'bloemen en natuur' => 'Kleurplaten van bloemen en natuur laten kinderen de schoonheid van de wereld om hen heen ontdekken. Ze leren verschillende bloemen, bladeren, planten, bijen en vlinders kennen en zien hoe ze met elkaar samenhangen. Het kleuren helpt hen gedetailleerder te kijken naar vormen, patronen en kleuren in de natuur. Tijdens het kleuren kun je praten over seizoenen, groei en het belang van zorg voor planten en insecten. Kinderen ontwikkelen tevens geduld en nauwkeurigheid. Deze platen zijn geschikt voor kinderen van 4-8 jaar. Ga naar buiten voor inspiratie en kleur daarna je eigen stukje natuur in.',
        'ruimte' => 'Kleurplaten over ruimte geven kinderen een eerste kennismaking met het heelal. Ze leren planeten, sterren, manen, raketten, astronauten en zelfs ufo\'s kennen. Terwijl ze deze mysterieuze objecten inkleuren, ontwikkelen ze fijne motoriek en nieuwsgierigheid naar wetenschap. Samen kun je bespreken waarom het donker is in de ruimte, wat zwaartekracht is of hoe een raket opstijgt. Kinderen kunnen hun fantasie gebruiken om eigen sterrenstelsels te bedenken. Deze platen zijn geschikt voor kinderen van 4-8 jaar. Trek je ruimtepak aan en kleur mee naar onbekende werelden.',
        'sport en bewegen' => 'Kleurplaten over sport en bewegen stimuleren kinderen om actief te worden en verschillende sporten te ontdekken. Ze maken kennis met voetbal, ballet, zwemmen, tennis, gymnastiek, fietsen en meer. Het kleuren van sporters in actie leert kinderen lichaamsdelen en bewegingen benoemen. Naast motorische vaardigheden moedigt het ook gesprekken aan over teamwork, fair play, doorzetten en plezier in bewegen. Kinderen zien dat sporten voor iedereen is, ongeacht niveau. Deze platen zijn geschikt voor kinderen van 4-8 jaar. Kleur je favoriete sport en ga daarna meteen zelf actief bewegen voor extra plezier.',
        'gevoelens en emoties' => 'Kleurplaten over gevoelens en emoties helpen kinderen hun innerlijke wereld beter te begrijpen en te benoemen. Ze leren onderscheid maken tussen blijdschap, verdriet, boosheid, angst, verrassing en trots door gezichtuitdrukkingen te herkennen. Het kleuren biedt een veilige manier om over moeilijke gevoelens te praten zonder druk. Kinderen ontwikkelen empathie door na te denken over hoe anderen zich voelen. Ook versterkt het zelfbewustzijn en helpt het bij het vinden van woorden voor emoties. Deze platen zijn geschikt voor kinderen van 3-8 jaar. Pak een kleurpotlood en ontdek samen welke gevoelens er op dit moment leven.',
        'seizoenen' => 'Kleurplaten van de seizoenen leren kinderen het ritme van het jaar kennen: lente, zomer, herfst en winter. Elk seizoen heeft eigen kenmerken, zoals bloeiende bomen, zomerse stranden, vallende bladeren of besneeuwde landschappen. Terwijl ze kleuren, ontwikkelen kinderen observatievermogen en leren ze verbanden leggen tussen weer, kleding en activiteiten. Samen kun je praten over seizoensgebonden feestdagen, natuurverschijnselen en favoriete herinneringen. Het kleuren bevordert ook geduld en creativiteit. Deze platen zijn geschikt voor kinderen van 3-7 jaar. Volg de seizoenen mee en kleur voor elk jaargetijde een eigen tafereel.',
        'voedsel' => 'Kleurplaten over voedsel helpen kinderen gezonde en gevarieerde voeding op een leuke manier te leren kennen. Ze maken kennis met fruit, groente, brood, zuivel, vis en andere producten. Terwijl ze kleuren, oefenen ze fijne motoriek en leren ze namen en kleuren van voedingsmiddelen. Ouders kunnen bespreken waarom groenten belangrijk zijn en hoe maaltijden worden samengesteld. Kinderen ontdekken ook waar voedsel vandaan komt, van boerderij tot bord. Deze platen zijn geschikt voor kinderen van 3-7 jaar. Haal je kleurpotloden en maak een smakelijk kleurrijk bord vol gezonde lekkernijen.',
        'muziek en geluid' => 'Kleurplaten over muziek en geluid introduceren kinderen in de wereld van klanken en instrumenten. Ze leren bekende instrumenten kennen, zoals piano, gitaar, trommel, fluit, viool en saxofoon. Het kleuren stimuleert zowel creativiteit als concentratie en kan worden gecombineerd met luisteren naar muziek. Kinderen kunnen ritme klappen, meezingen of zelf geluiden imiteren bij elk instrument. Zo wordt muziek een actieve ervaring in plaats van alleen iets om naar te luisteren. Deze platen zijn geschikt voor kinderen van 4-8 jaar. Kleur je favoriete instrument en speel er daarna zelf op los.',
        'kleuren en vormen' => 'Kleurplaten over kleuren en vormen vormen een solide basis voor vroeg leren. Kinderen maken kennis met basisvormen als cirkel, vierkant, driehoek en rechthoek, en met primaire en secundaire kleuren. Terwijl ze kleuren, trainen ze oog-handcoördinatie, patroonherkenning en ruimtelijk inzicht. Je kunt samen spelen met kleurherkenning, sorteren en tellen om het leerproces te versterken. Deze eenvoudige plaatjes bieden veel oefening zonder overweldigend te zijn. Ideaal om nieuwe begrippen spelenderwijs aan te leren. Deze platen zijn geschikt voor kinderen van 2-6 jaar. Pak je kleurpotloden en ontdek samen de wereld van kleuren en vormen.',
    );
    
    $description = isset($descriptions[$term_name_lower]) ? $descriptions[$term_name_lower] : 'Kleurplaten van ' . $term_name . ' bieden kinderen een leuke en leerzame manier om hun creativiteit te ontwikkelen. Terwijl ze deze plaatjes inkleuren, oefenen ze fijne motoriek, concentratie en oog-handcoördinatie. Ze leren nieuwe begrippen, vormen en kleuren kennen die passen bij dit thema. Samen met ouders of leerkrachten kunnen ze praten over wat ze zien en zelf verhalen bedenken bij de afbeeldingen. Deze kleurplaten zijn geschikt voor kinderen van 3-7 jaar. Ze zijn perfect voor thuis, op school of tijdens rustige momenten. Haal je kleurpotloden erbij en laat je creativiteit de vrije loop.';
    
    return $description;
}

/**
 * Get structured SEO metadata for a kleurplaat_categorie term
 * 
 * @param string $term_name Name of the category term
 * @return array Structured data with SEO metadata
 */
function kk_get_term_seo_meta($term_name) {
    $term_name_lower = strtolower($term_name);
    
    $meta = array(
        'dieren' => array(
            'focus_keyword' => 'kinderkleurplaten dieren',
            'meta_description' => 'Kleurplaten van dieren voor kinderen van 3-7 jaar. Leer diersoorten, kenmerken en leefgebieden kennen terwijl je inkleurt.',
            'schema_type' => 'CollectionPage',
            'age_range' => '3-7 jaar',
            'learning_goals' => array('diersoorten herkennen', 'kenmerken benoemen', 'fijne motoriek oefenen'),
        ),
        'dinosaurussen' => array(
            'focus_keyword' => 'kinderkleurplaten dinosaurussen',
            'meta_description' => 'Kleurplaten van dinosaurussen voor kinderen van 4-8 jaar. Ontdek T-rex, stegosaurus, triceratops en meer.',
            'schema_type' => 'CollectionPage',
            'age_range' => '4-8 jaar',
            'learning_goals' => array('dinosaurussen leren kennen', 'vleeseter/planteneter onderscheid', 'geheugen trainen'),
        ),
        'boerderijdieren' => array(
            'focus_keyword' => 'kinderkleurplaten boerderijdieren',
            'meta_description' => 'Kleurplaten van boerderijdieren voor kinderen van 3-7 jaar. Koe, kip, varken, paard en meer leren kennen.',
            'schema_type' => 'CollectionPage',
            'age_range' => '3-7 jaar',
            'learning_goals' => array('boerderijdieren herkennen', 'jong en volwassen', 'dagelijks leven leren'),
        ),
        'voertuigen' => array(
            'focus_keyword' => 'kinderkleurplaten voertuigen',
            'meta_description' => 'Kleurplaten van voertuigen voor kinderen van 3-7 jaar. Auto, trein, vliegtuig, boot en brandweerwagen kleuren.',
            'schema_type' => 'CollectionPage',
            'age_range' => '3-7 jaar',
            'learning_goals' => array('voertuigen benoemen', 'functies leren', 'ruimtelijk inzicht'),
        ),
        'feestdagen' => array(
            'focus_keyword' => 'kinderkleurplaten feestdagen',
            'meta_description' => 'Kleurplaten van feestdagen voor kinderen van 3-8 jaar. Sinterklaas, Kerst, Pasen, Koningsdag en Halloween.',
            'schema_type' => 'CollectionPage',
            'age_range' => '3-8 jaar',
            'learning_goals' => array('tradities leren', 'symbolen herkennen', 'feestcultuur ontdekken'),
        ),
        'sprookjes en fantasy' => array(
            'focus_keyword' => 'kinderkleurplaten sprookjes fantasy',
            'meta_description' => 'Kleurplaten van sprookjes en fantasy voor kinderen van 4-8 jaar. Draken, feeën, prinsessen en eenhoorns.',
            'schema_type' => 'CollectionPage',
            'age_range' => '4-8 jaar',
            'learning_goals' => array('verbeelding stimuleren', 'verhalen bedenken', 'empathie ontwikkelen'),
        ),
        'bloemen en natuur' => array(
            'focus_keyword' => 'kinderkleurplaten bloemen natuur',
            'meta_description' => 'Kleurplaten van bloemen en natuur voor kinderen van 4-8 jaar. Bloemen, vlinders, bijen en planten leren kennen.',
            'schema_type' => 'CollectionPage',
            'age_range' => '4-8 jaar',
            'learning_goals' => array('natuur ontdekken', 'seizoenen leren', 'zorgzaamheid stimuleren'),
        ),
        'ruimte' => array(
            'focus_keyword' => 'kinderkleurplaten ruimte',
            'meta_description' => 'Kleurplaten over ruimte voor kinderen van 4-8 jaar. Planeten, sterren, raketten en astronauten kleuren.',
            'schema_type' => 'CollectionPage',
            'age_range' => '4-8 jaar',
            'learning_goals' => array('heelal kennismaken', 'nieuwsgierigheid stimuleren', 'begrippen leren'),
        ),
        'sport en bewegen' => array(
            'focus_keyword' => 'kinderkleurplaten sport bewegen',
            'meta_description' => 'Kleurplaten over sport en bewegen voor kinderen van 4-8 jaar. Verschillende sporten en lichaamsbeweging ontdekken.',
            'schema_type' => 'CollectionPage',
            'age_range' => '4-8 jaar',
            'learning_goals' => array('sporten herkennen', 'lichaamsdelen benoemen', 'fair play leren'),
        ),
        'gevoelens en emoties' => array(
            'focus_keyword' => 'kinderkleurplaten gevoelens emoties',
            'meta_description' => 'Kleurplaten over gevoelens en emoties voor kinderen van 3-8 jaar. Blij, verdrietig, boos en andere emoties leren.',
            'schema_type' => 'CollectionPage',
            'age_range' => '3-8 jaar',
            'learning_goals' => array('emoties benoemen', 'empathie ontwikkelen', 'zelfbewustzijn'),
        ),
        'seizoenen' => array(
            'focus_keyword' => 'kinderkleurplaten seizoenen',
            'meta_description' => 'Kleurplaten van de vier seizoenen voor kinderen van 3-7 jaar. Lente, zomer, herfst en winter ontdekken.',
            'schema_type' => 'CollectionPage',
            'age_range' => '3-7 jaar',
            'learning_goals' => array('seizoenen herkennen', 'weer leren', 'jaarritme begrijpen'),
        ),
        'voedsel' => array(
            'focus_keyword' => 'kinderkleurplaten voedsel',
            'meta_description' => 'Kleurplaten over voedsel voor kinderen van 3-7 jaar. Fruit, groente en gezonde voeding leren kennen.',
            'schema_type' => 'CollectionPage',
            'age_range' => '3-7 jaar',
            'learning_goals' => array('voedingsmiddelen herkennen', 'gezonde voeding', 'herkomst leren'),
        ),
        'muziek en geluid' => array(
            'focus_keyword' => 'kinderkleurplaten muziek instrumenten',
            'meta_description' => 'Kleurplaten over muziek en instrumenten voor kinderen van 4-8 jaar. Piano, gitaar, trommel en meer.',
            'schema_type' => 'CollectionPage',
            'age_range' => '4-8 jaar',
            'learning_goals' => array('instrumenten leren', 'ritme ontwikkelen', 'creativiteit stimuleren'),
        ),
        'kleuren en vormen' => array(
            'focus_keyword' => 'kinderkleurplaten kleuren vormen',
            'meta_description' => 'Kleurplaten over kleuren en vormen voor kinderen van 2-6 jaar. Basisvormen en kleuren spelenderwijs leren.',
            'schema_type' => 'CollectionPage',
            'age_range' => '2-6 jaar',
            'learning_goals' => array('vormen herkennen', 'kleuren benoemen', 'patroonherkenning'),
        ),
    );
    
    $default_meta = array(
        'focus_keyword' => 'kinderkleurplaten ' . $term_name_lower,
        'meta_description' => 'Kleurplaten van ' . $term_name . ' voor kinderen. Leerzaam en leuk om in te kleuren voor jonge kinderen.',
        'schema_type' => 'CollectionPage',
        'age_range' => '3-7 jaar',
        'learning_goals' => array('creativiteit ontwikkelen', 'fijne motoriek oefenen', 'nieuwe begrippen leren'),
    );
    
    return isset($meta[$term_name_lower]) ? $meta[$term_name_lower] : $default_meta;
}

/**
 * Safely display category description for taxonomy templates
 * 
 * @param WP_Term $term Term object
 * @return void
 */
function kk_display_category_description($term) {
    if (!is_object($term) || !isset($term->name)) {
        return;
    }
    
    $description = kk_get_category_description($term->name);
    
    if (!empty($description)) {
        echo wp_kses_post(wpautop($description));
    }
}
