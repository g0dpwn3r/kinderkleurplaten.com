<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

$target_dir = __DIR__ . '/../assets/images';
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0775, true);
}

$templates = [
    ['type'=>'dino','title'=>'T-Rex met vriendelijke glimlach','category'=>'Dinosaurussen','fact'=>'T-Rex had kleine armpjes, maar zijn kop en staart waren juist heel krachtig.'],
    ['type'=>'dino','title'=>'Triceratops in de wei','category'=>'Dinosaurussen','fact'=>'De naam Triceratops betekent gezicht met drie hoorns.'],
    ['type'=>'dino','title'=>'Stegosaurus met rugplaten','category'=>'Dinosaurussen','fact'=>'De Stegosaurus droeg grote beenplaten op zijn rug.'],
    ['type'=>'dino','title'=>'Brachiosaurus bij hoge bomen','category'=>'Dinosaurussen','fact'=>'Brachiosaurus was een planteneter met een heel lange nek.'],
    ['type'=>'dino','title'=>'Baby dino uit het ei','category'=>'Dinosaurussen','fact'=>'Veel dinosauriërs kwamen uit eieren, net als vogels vandaag.'],
    ['type'=>'dino','title'=>'Dino eieren in het zand','category'=>'Dinosaurussen','fact'=>'Sommige fossiele nesten laten zien dat dino\'s voor hun eieren zorgden.'],
    ['type'=>'dino','title'=>'Vulkaan in de prehistorie','category'=>'Dinosaurussen','fact'=>'In de tijd van de dinosauriërs waren er veel vulkanen en warme bossen.'],
    ['type'=>'dino','title'=>'Dinosauriër fossiel','category'=>'Dinosaurussen','fact'=>'Fossielen zijn versteende sporen van dieren en planten uit lang geleden.'],
    ['type'=>'dino','title'=>'Pteranodon boven de rotsen','category'=>'Dinosaurussen','fact'=>'Pteranodon was geen dino, maar een vliegende reptiel uit dezelfde tijd.'],
    ['type'=>'dino','title'=>'Ankylosaurus met knotsstaart','category'=>'Dinosaurussen','fact'=>'Ankylosaurus had een zware staartknots die als bescherming diende.'],

    ['type'=>'ice','title'=>'Originele ijsprinses','category'=>'IJsprinses en sneeuwkasteel','fact'=>'Een ijsprinses is een sprookjesfiguur die past bij winter, sneeuw en vriendelijkheid.'],
    ['type'=>'ice','title'=>'Sneeuwkasteel met torens','category'=>'IJsprinses en sneeuwkasteel','fact'=>'Sneeuwkastelen kun je maken door sneeuw stevig samen te drukken.'],
    ['type'=>'ice','title'=>'Winterkroon met sneeuwparels','category'=>'IJsprinses en sneeuwkasteel','fact'=>'Kronen worden vaak gebruikt in sprookjes als symbool van feestelijkheid.'],
    ['type'=>'ice','title'=>'Grote sneeuwvlok','category'=>'IJsprinses en sneeuwkasteel','fact'=>'Elke sneeuwvlok heeft een eigen patroon van ijskristallen.'],
    ['type'=>'ice','title'=>'Pinguin op het ijs','category'=>'IJsprinses en sneeuwkasteel','fact'=>'Pinguins kunnen niet vliegen, maar ze zijn uitstekende zwemmers.'],
    ['type'=>'ice','title'=>'Paardenslee met sterren','category'=>'IJsprinses en sneeuwkasteel','fact'=>'Sleeën glijden makkelijk over sneeuw en ijs.'],
    ['type'=>'ice','title'=>'IJsberg met zeehond','category'=>'IJsprinses en sneeuwkasteel','fact'=>'Een ijsberg bestaat uit zoet water dat in de zee drijft.'],
    ['type'=>'ice','title'=>'Sneeuwpop met sjaal','category'=>'IJsprinses en sneeuwkasteel','fact'=>'Een sneeuwpop blijft het beste staan als de sneeuw een beetje nat is.'],
    ['type'=>'ice','title'=>'Warme muts en wanten','category'=>'IJsprinses en sneeuwkasteel','fact'=>'Wollen wanten houden lucht vast en helpen je handen warm te houden.'],
    ['type'=>'ice','title'=>'Maan boven het winterbos','category'=>'IJsprinses en sneeuwkasteel','fact'=>'In de winter staat de maan soms heel lang aan de hemel.'],

    ['type'=>'vehicle','title'=>'Raceauto op de baan','category'=>'Auto\'s en vliegtuigen','fact'=>'Raceauto\'s zijn laag en breed zodat ze stabiel blijven bij hoge snelheid.'],
    ['type'=>'vehicle','title'=>'Brandweerwagen met ladder','category'=>'Auto\'s en vliegtuigen','fact'=>'Brandweerwagens hebben slangen, ladders en speciale lampen.'],
    ['type'=>'vehicle','title'=>'Politieauto met zwaailicht','category'=>'Auto\'s en vliegtuigen','fact'=>'Zwaailichten helpen andere weggebruikers om hulpvoertuigen op te merken.'],
    ['type'=>'vehicle','title'=>'Stadsbus met ramen','category'=>'Auto\'s en vliegtuigen','fact'=>'Bussen kunnen veel mensen tegelijk vervoeren door de stad.'],
    ['type'=>'vehicle','title'=>'Trein door het dal','category'=>'Auto\'s en vliegtuigen','fact'=>'Treinen rijden over rails en kunnen heel veel wagons trekken.'],
    ['type'=>'vehicle','title'=>'Helikopter in de lucht','category'=>'Auto\'s en vliegtuigen','fact'=>'Helikopters kunnen opstijgen en landen zonder lange landingsbaan.'],
    ['type'=>'vehicle','title'=>'Vliegtuig tussen wolken','category'=>'Auto\'s en vliegtuigen','fact'=>'Vliegtuigvleugels helpen om lift te maken tijdens het vliegen.'],
    ['type'=>'vehicle','title'=>'Raket naar de sterren','category'=>'Auto\'s en vliegtuigen','fact'=>'Raketten gebruiken krachtige stuwkracht om de ruimte in te gaan.'],
    ['type'=>'vehicle','title'=>'Veerboot op het water','category'=>'Auto\'s en vliegtuigen','fact'=>'Veerboten brengen mensen, fietsen en auto\'s van de ene oever naar de andere.'],
    ['type'=>'vehicle','title'=>'Fiets met mandje','category'=>'Auto\'s en vliegtuigen','fact'=>'Een fiets met goed opgepompte banden rijdt lichter.'],
    ['type'=>'vehicle','title'=>'Tractor op het veld','category'=>'Auto\'s en vliegtuigen','fact'=>'Tractoren helpen boeren met zwaar werk op het land.'],
    ['type'=>'vehicle','title'=>'Kraanwagen op de bouwplaats','category'=>'Auto\'s en vliegtuigen','fact'=>'Kraanwagens tillen zware materialen omhoog met kabels en haken.'],

    ['type'=>'fantasy','title'=>'Eenhoorn in bloemenwei','category'=>'Populaire fantasy','fact'=>'Eenhoorns komen uit oude verhalen en staan vaak voor magie en vriendelijkheid.'],
    ['type'=>'fantasy','title'=>'Vriendelijke draak','category'=>'Populaire fantasy','fact'=>'Draken zijn in veel verhalen groot, sterk en soms verrassend zachtaardig.'],
    ['type'=>'fantasy','title'=>'Tovenaar met sterrenhoed','category'=>'Populaire fantasy','fact'=>'Tovenaars horen bij oude sprookjes vol sterren, boeken en toverstafjes.'],
    ['type'=>'fantasy','title'=>'Fee met vleugels','category'=>'Populaire fantasy','fact'=>'Feeën worden vaak afgebeeld als kleine wezens met vleugels.'],
    ['type'=>'fantasy','title'=>'Magische toverstaf','category'=>'Populaire fantasy','fact'=>'Een toverstaf is in sprookjes een stokje waarmee wonderen gebeuren.'],
    ['type'=>'fantasy','title'=>'Sprookjeskasteel','category'=>'Populaire fantasy','fact'=>'Kastelen hebben in verhalen vaak torens, poorten en hoge muren.'],
    ['type'=>'fantasy','title'=>'Magische paddenstoelen','category'=>'Populaire fantasy','fact'=>'Paddenstoelen horen niet bij planten, maar vormen een eigen groep in de natuur.'],
    ['type'=>'fantasy','title'=>'Sterrenpoort in het bos','category'=>'Populaire fantasy','fact'=>'Sterren zijn enorme bollen heet gas die van heel ver weg schijnen.'],
    ['type'=>'fantasy','title'=>'Elfje in het bos','category'=>'Populaire fantasy','fact'=>'Elfen en elfjes komen voor in veel bosverhalen uit verschillende landen.'],
    ['type'=>'fantasy','title'=>'Draakjei in een nest','category'=>'Populaire fantasy','fact'=>'In fantasieverhalen komen wonderlijke eieren voor met magische geheimen.'],

    ['type'=>'animal','title'=>'Kat met bol wol','category'=>'Dieren','fact'=>'Katten gebruiken hun snorharen om afstanden en openingen te voelen.'],
    ['type'=>'animal','title'=>'Hond met botje','category'=>'Dieren','fact'=>'Honden kunnen veel geuren ruiken die mensen niet merken.'],
    ['type'=>'animal','title'=>'Konijn met wortel','category'=>'Dieren','fact'=>'Konijnen hebben tanden die hun hele leven doorgroeien.'],
    ['type'=>'animal','title'=>'Uil op een tak','category'=>'Dieren','fact'=>'Uilen kunnen hun kop heel ver draaien om beter te kijken.'],
    ['type'=>'animal','title'=>'Vos in het bos','category'=>'Dieren','fact'=>'Vossen zijn slimme jagers die zich goed aanpassen aan verschillende omgevingen.'],
    ['type'=>'animal','title'=>'Leeuw met zachte manen','category'=>'Dieren','fact'=>'Leeuwen leven vaak in groepen die roedels worden genoemd.'],
    ['type'=>'animal','title'=>'Olifant met bloemen','category'=>'Dieren','fact'=>'Olifanten gebruiken hun slurf om te ruiken, te drinken en dingen op te pakken.'],
    ['type'=>'animal','title'=>'Giraffe met lange hals','category'=>'Dieren','fact'=>'Giraffen hebben net als mensen zeven halswervels.'],
    ['type'=>'animal','title'=>'Beer met honingpot','category'=>'Dieren','fact'=>'Beren hebben een uitstekend reukvermogen.'],
    ['type'=>'animal','title'=>'Panda met bamboe','category'=>'Dieren','fact'=>'Panda\'s eten vooral bamboe en kunnen daar veel uren per dag mee bezig zijn.'],
    ['type'=>'animal','title'=>'Kikker op lelieblad','category'=>'Dieren','fact'=>'Kikkers beginnen hun leven als kikkervisjes in het water.'],
    ['type'=>'animal','title'=>'Egel tussen appels','category'=>'Dieren','fact'=>'Egels hebben duizenden stekels die hen helpen beschermen.'],
    ['type'=>'animal','title'=>'Schildpad met bloemen','category'=>'Dieren','fact'=>'Schildpadden dragen hun beschermende schild hun hele leven bij zich.'],
    ['type'=>'animal','title'=>'Koala in boom','category'=>'Dieren','fact'=>'Koala\'s slapen vaak veel omdat bamboeblad weinig energie geeft.'],

    ['type'=>'sea','title'=>'Vrolijke vis','category'=>'Zee en strand','fact'=>'Vissen ademen zuurstof uit het water met hun kieuwen.'],
    ['type'=>'sea','title'=>'Dolfijn met bal','category'=>'Zee en strand','fact'=>'Dolfijnen zijn sociale dieren die graag samen zwemmen.'],
    ['type'=>'sea','title'=>'Walvis met fontein','category'=>'Zee en strand','fact'=>'Walvissen zijn zoogdieren en moeten ademhalen aan de oppervlakte.'],
    ['type'=>'sea','title'=>'Octopus tussen zeewier','category'=>'Zee en strand','fact'=>'Een octopus heeft acht armen en kan slim puzzels oplossen.'],
    ['type'=>'sea','title'=>'Zeester op het strand','category'=>'Zee en strand','fact'=>'Veel zeesterren kunnen een verloren arm weer laten aangroeien.'],
    ['type'=>'sea','title'=>'Koraalrif met bubbels','category'=>'Zee en strand','fact'=>'Koraalriffen bieden woonplek aan talloze zeedieren.'],
    ['type'=>'sea','title'=>'Schelpen op het zand','category'=>'Zee en strand','fact'=>'Schelpen zijn de harde huisjes van zeedieren zoals mosselen.'],
    ['type'=>'sea','title'=>'Zeemeermin met zeester','category'=>'Zee en strand','fact'=>'Zeemeerminnen zijn figuren uit oude zeeverhalen en sprookjes.'],
    ['type'=>'sea','title'=>'Zeilbootje op zee','category'=>'Zee en strand','fact'=>'Zeilboten gebruiken de wind om over het water te bewegen.'],
    ['type'=>'sea','title'=>'Krab met schelp','category'=>'Zee en strand','fact'=>'Krabben lopen vaak zijwaarts en dragen een hard pantser.'],

    ['type'=>'space','title'=>'Astronaut zweeft in ruimte','category'=>'Ruimte','fact'=>'Astronauten dragen speciale pakken met zuurstof en bescherming.'],
    ['type'=>'space','title'=>'Maanlander op de maan','category'=>'Ruimte','fact'=>'De maan heeft geen lucht zoals de aarde, dus ruimtevaartuigen moeten zelf zuurstof meenemen.'],
    ['type'=>'space','title'=>'Planeet met ringen','category'=>'Ruimte','fact'=>'Ringen rond planeten bestaan uit ijs, steen en stof.'],
    ['type'=>'space','title'=>'Komeet met staart','category'=>'Ruimte','fact'=>'De staart van een komeet wijst vaak weg van de zon door zonnewind.'],
    ['type'=>'space','title'=>'Satelliet om de aarde','category'=>'Ruimte','fact'=>'Satellieten helpen met weerbericht, navigatie en communicatie.'],
    ['type'=>'space','title'=>'Sterrenbeeld draakje','category'=>'Ruimte','fact'=>'Sterrenbeelden zijn patronen die mensen in groepen sterren herkennen.'],
    ['type'=>'space','title'=>'Ruimtestation in een baan','category'=>'Ruimte','fact'=>'Ruimtestations draaien om de aarde en dienen als woon- en werkplek voor astronauten.'],
    ['type'=>'space','title'=>'Telescoop op statief','category'=>'Ruimte','fact'=>'Telescopen verzamelen licht zodat we verre sterren en planeten beter kunnen zien.'],

    ['type'=>'robot','title'=>'Vriendelijke robot','category'=>'Robots','fact'=>'Robots kunnen taken uitvoeren die door mensen zijn geprogrammeerd.'],
    ['type'=>'robot','title'=>'Robot hond','category'=>'Robots','fact'=>'Sommige robots bewegen op wielen, poten of rupsbanden.'],
    ['type'=>'robot','title'=>'Robot met ballonnen','category'=>'Robots','fact'=>'Sensoren helpen robots om licht, afstand of geluid waar te nemen.'],
    ['type'=>'robot','title'=>'Robotauto','category'=>'Robots','fact'=>'Zelfrijdende voertuigen gebruiken camera\'s en sensoren om hun weg te vinden.'],
    ['type'=>'robot','title'=>'Robotarm op tafel','category'=>'Robots','fact'=>'Robotarmen worden gebruikt in fabrieken om precies te bouwen en te tillen.'],
    ['type'=>'robot','title'=>'Robot huis met antenne','category'=>'Robots','fact'=>'Thuisrobots kunnen helpen met schoonmaken, spelen of herinneringen.'],
    ['type'=>'robot','title'=>'Robot speelgoed op wielen','category'=>'Robots','fact'=>'Speelgoedrobots laten op kleine schaal zien hoe beweging en code samenwerken.'],
    ['type'=>'robot','title'=>'Robot fabriek met tandwielen','category'=>'Robots','fact'=>'Tandwielen zetten draaiing om in beweging en kracht.'],
    ['type'=>'robot','title'=>'Robot met bloemen','category'=>'Robots','fact'=>'Robots kunnen ook zachte taken leren, zoals planten water geven.'],

    ['type'=>'food','title'=>'Appel met blaadje','category'=>'Eten en drinken','fact'=>'Appels bestaan voor een deel uit lucht, daarom kunnen ze soms drijven.'],
    ['type'=>'food','title'=>'Taart op bord','category'=>'Eten en drinken','fact'=>'Taart wordt vaak in punten gesneden zodat iedereen een stukje krijgt.'],
    ['type'=>'food','title'=>'Pannenkoek met fruit','category'=>'Eten en drinken','fact'=>'Pannenkoeken worden in veel landen op verschillende manieren gemaakt.'],
    ['type'=>'food','title'=>'IJsje met twee bollen','category'=>'Eten en drinken','fact'=>'IJswinkels gebruiken kou om het ijs stevig en romig te houden.'],
    ['type'=>'food','title'=>'Pizza in punten','category'=>'Eten en drinken','fact'=>'Pizza is een rond brood met beleg dat vaak in punten wordt gesneden.'],
    ['type'=>'food','title'=>'Fruitmand met zomerfruit','category'=>'Eten en drinken','fact'=>'Fruit bevat vitamines, vezels en veel natuurlijk zoetigheid.'],
    ['type'=>'food','title'=>'Koekjes in kom','category'=>'Eten en drinken','fact'=>'Koekjes worden knapperig door te bakken in een warme oven.'],
    ['type'=>'food','title'=>'Soepkom met lepel','category'=>'Eten en drinken','fact'=>'Warme soep kan helpen om op koude dagen weer lekker op te warmen.'],
    ['type'=>'food','title'=>'Cupcake met sterretje','category'=>'Eten en drinken','fact'=>'Cupcakes zijn kleine cakes die vaak met een toefje worden versierd.'],

    ['type'=>'holiday','title'=>'Verjaardagstaart met kaarsen','category'=>'Feestdagen en vieringen','fact'=>'Kaarsjes op een taart maken verjaardagen feestelijk.'],
    ['type'=>'holiday','title'=>'Kerstboom met ster','category'=>'Feestdagen en vieringen','fact'=>'Kerstbomen worden vaak versierd met lichtjes en ballen.'],
    ['type'=>'holiday','title'=>'Pakketjes onder de boom','category'=>'Feestdagen en vieringen','fact'=>'Cadeaus worden vaak ingepakt om ze verrassend te houden.'],
    ['type'=>'holiday','title'=>'Paaseieren in het gras','category'=>'Feestdagen en vieringen','fact'=>'Paaseieren horen bij lente en bij verhalen over nieuw begin.'],
    ['type'=>'holiday','title'=>'Pompoen met vriendelijk gezicht','category'=>'Feestdagen en vieringen','fact'=>'Pompoenen zijn grote oranje vruchten die in de herfst rijpen.'],
    ['type'=>'holiday','title'=>'Herfstblad op tak','category'=>'Feestdagen en vieringen','fact'=>'Bladeren veranderen van kleur wanneer bomen zich voorbereiden op de winter.'],
    ['type'=>'holiday','title'=>'Nieuwjaars vuurwerk','category'=>'Feestdagen en vieringen','fact'=>'Vuurwerk kleurt de lucht door brandende mineralen en zouten.'],
    ['type'=>'holiday','title'=>'Winterfeest met sterren','category'=>'Feestdagen en vieringen','fact'=>'Veel winterfeesten gebruiken licht om donkere dagen gezelliger te maken.'],

    ['type'=>'sport','title'=>'Voetbal op het veld','category'=>'Sport en beweging','fact'=>'Een voetbal heeft vaak een patroon van vijf- en zeshoeken.'],
    ['type'=>'sport','title'=>'Basketbal bij de ring','category'=>'Sport en beweging','fact'=>'Bij basketbal probeer je de bal door een hoge ring te gooien.'],
    ['type'=>'sport','title'=>'Tennisracket en bal','category'=>'Sport en beweging','fact'=>'Tennisrackets hebben snaren die de bal terugkaatsen.'],
    ['type'=>'sport','title'=>'Zwemmer in het water','category'=>'Sport en beweging','fact'=>'Zwemmen traint veel spieren tegelijk.'],
    ['type'=>'sport','title'=>'Fiets met helm','category'=>'Sport en beweging','fact'=>'Een helm helpt je hoofd beschermen tijdens het fietsen.'],
    ['type'=>'sport','title'=>'Schaatsen op ijs','category'=>'Sport en beweging','fact'=>'Schaatsen glijdt over ijs omdat er weinig wrijving is.'],
    ['type'=>'sport','title'=>'Skateboard trick','category'=>'Sport en beweging','fact'=>'Skateboarden vraagt om balans, oefening en goede bescherming.'],
    ['type'=>'sport','title'=>'Yoga op matje','category'=>'Sport en beweging','fact'=>'Yoga combineert houdingen, ademhaling en rustige aandacht.'],
    ['type'=>'sport','title'=>'Slagbalknuppel en bal','category'=>'Sport en beweging','fact'=>'Bij slagbalsporten probeer je een bal zo ver mogelijk weg te slaan.'],
    ['type'=>'sport','title'=>'Turnlint met sterren','category'=>'Sport en beweging','fact'=>'Turnen helpt bij balans, kracht en soepel bewegen.'],

    ['type'=>'monster','title'=>'Vriendelijk hoornmonster','category'=>'Vriendelijke monsters','fact'=>'Monsters in kinderverhalen zijn vaak grappig en helemaal niet eng.'],
    ['type'=>'monster','title'=>'Bolmonster met stippen','category'=>'Vriendelijke monsters','fact'=>'Ronde vormen maken een tekening zacht en kindvriendelijk.'],
    ['type'=>'monster','title'=>'Pluizig monster','category'=>'Vriendelijke monsters','fact'=>'Pluizige lijntjes geven een tekening een speels gevoel.'],
    ['type'=>'monster','title'=>'Lachmonster met vlaggetjes','category'=>'Vriendelijke monsters','fact'=>'Een brede glimlach maakt een figuur meteen vriendelijker.'],
    ['type'=>'monster','title'=>'Slakmonster met huisje','category'=>'Vriendelijke monsters','fact'=>'Slakken dragen hun huisje overal mee naartoe.'],
    ['type'=>'monster','title'=>'Sterrenmonster in de nacht','category'=>'Vriendelijke monsters','fact'=>'Sterren aan de hemel vormen patronen die al heel lang mensen inspireren.'],
    ['type'=>'monster','title'=>'Monster met regenboogvinnen','category'=>'Vriendelijke monsters','fact'=>'Regenbogen ontstaan wanneer licht door waterdruppels wordt gebroken.'],
    ['type'=>'monster','title'=>'Knuffelmonster met hart','category'=>'Vriendelijke monsters','fact'=>'Knuffels kunnen helpen om je veilig en getroost te voelen.'],
    ['type'=>'robot','title'=>'Robot maanwagen','category'=>'Robots','fact'=>'Maanwagens helpen astronauten en robots om over ruw terrein te rijden.'],
    ['type'=>'food','title'=>'Broodmand met croissant','category'=>'Eten en drinken','fact'=>'Croissants krijgen hun laagjes door deeg met boter te vouwen.'],
];

function svg_attr($value) {
    return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function svg_tag($tag, array $attrs = [], $children = '') {
    $out = [];
    foreach ($attrs as $key => $value) {
        if ($value !== null && $value !== false) {
            $out[] = $key . '="' . svg_attr($value) . '"';
        }
    }
    $attr = $out ? ' ' . implode(' ', $out) : '';
    return $children === '' ? '<' . $tag . $attr . '/>' : '<' . $tag . $attr . '>' . $children . '</' . $tag . '>';
}

function path_svg($d, $w = 8, array $extra = []) { return svg_tag('path', array_merge(['d' => $d, 'stroke-width' => $w], $extra)); }
function line_svg($x1, $y1, $x2, $y2, $w = 8, array $extra = []) { return svg_tag('line', array_merge(['x1' => $x1, 'y1' => $y1, 'x2' => $x2, 'y2' => $y2, 'stroke-width' => $w], $extra)); }
function circle_svg($cx, $cy, $r, $w = 8, array $extra = []) { return svg_tag('circle', array_merge(['cx' => $cx, 'cy' => $cy, 'r' => $r, 'stroke-width' => $w], $extra)); }
function ellipse_svg($cx, $cy, $rx, $ry, $w = 8, array $extra = []) { return svg_tag('ellipse', array_merge(['cx' => $cx, 'cy' => $cy, 'rx' => $rx, 'ry' => $ry, 'stroke-width' => $w], $extra)); }
function rect_svg($x, $y, $w, $h, $s = 8, array $extra = []) { return svg_tag('rect', array_merge(['x' => $x, 'y' => $y, 'width' => $w, 'height' => $h, 'stroke-width' => $s], $extra)); }
function polygon_svg($points, $w = 8, array $extra = []) { return svg_tag('polygon', array_merge(['points' => $points, 'stroke-width' => $w], $extra)); }
function polyline_svg($points, $w = 8, array $extra = []) { return svg_tag('polyline', array_merge(['points' => $points, 'stroke-width' => $w], $extra)); }

function star_svg($cx, $cy, $outer, $inner, $points = 5, $w = 6) {
    $coords = [];
    for ($i = 0; $i < $points * 2; $i++) {
        $radius = $i % 2 === 0 ? $outer : $inner;
        $angle = deg2rad(-90 + $i * 180 / $points);
        $coords[] = round($cx + cos($angle) * $radius) . ',' . round($cy + sin($angle) * $radius);
    }
    return polygon_svg(implode(' ', $coords), $w);
}

function cloud_svg($x, $y, $s = 1, $w = 6) {
    return ellipse_svg($x, $y + 35 * $s, 95 * $s, 32 * $s, $w)
        . circle_svg($x - 65 * $s, $y + 28 * $s, 42 * $s, $w)
        . circle_svg($x, $y, 55 * $s, $w)
        . circle_svg($x + 68 * $s, $y + 24 * $s, 48 * $s, $w);
}

function sun_svg($x, $y, $r = 58, $w = 6) {
    $parts = [circle_svg($x, $y, $r, $w)];
    for ($i = 0; $i < 12; $i++) {
        $a = deg2rad($i * 30);
        $parts[] = line_svg($x + cos($a) * ($r + 18), $y + sin($a) * ($r + 18), $x + cos($a) * ($r + 42), $y + sin($a) * ($r + 42), $w);
    }
    return implode('', $parts);
}

function wave_svg($y, $w = 5, $x1 = 120, $x2 = 680) {
    return path_svg('M' . $x1 . ' ' . $y . ' C' . ($x1 + 40) . ' ' . ($y - 22) . ' ' . ($x1 + 80) . ' ' . ($y + 22) . ' ' . ($x1 + 120) . ' ' . $y . ' S' . ($x1 + 220) . ' ' . ($y - 22) . ' ' . ($x1 + 270) . ' ' . $y . ' S' . ($x1 + 370) . ' ' . ($y + 22) . ' ' . ($x1 + 420) . ' ' . $y . ' S' . ($x1 + 520) . ' ' . ($y - 22) . ' ' . $x2 . ' ' . $y, $w);
}

function ground_svg($y = 735) {
    return path_svg('M120 ' . $y . ' C220 ' . ($y - 30) . ' 580 ' . ($y - 30) . ' 680 ' . $y, 5)
        . path_svg('M160 ' . ($y + 45) . ' C270 ' . ($y + 15) . ' 530 ' . ($y + 15) . ' 640 ' . ($y + 45), 4);
}

function border_svg() {
    return rect_svg(48, 48, 704, 904, 6, ['rx' => 28])
        . path_svg('M90 145 C180 115 260 115 350 145 S520 175 710 145', 4)
        . path_svg('M90 855 C190 885 610 885 710 855', 4)
        . circle_svg(105, 105, 8, 4) . circle_svg(695, 105, 8, 4) . circle_svg(105, 895, 8, 4) . circle_svg(695, 895, 8, 4);
}

function snowflake_svg($cx, $cy, $r = 70, $w = 6) {
    $parts = [];
    for ($i = 0; $i < 6; $i++) {
        $a = deg2rad($i * 60);
        $parts[] = line_svg($cx, $cy, $cx + cos($a) * $r, $cy + sin($a) * $r, $w);
        $parts[] = line_svg($cx + cos($a) * $r * 0.55, $cy + sin($a) * $r * 0.55, $cx + cos($a + deg2rad(35)) * $r * 0.78, $cy + sin($a + deg2rad(35)) * $r * 0.78, max(3, $w - 1));
        $parts[] = line_svg($cx + cos($a) * $r * 0.55, $cy + sin($a) * $r * 0.55, $cx + cos($a - deg2rad(35)) * $r * 0.78, $cy + sin($a - deg2rad(35)) * $r * 0.78, max(3, $w - 1));
    }
    return implode('', $parts);
}

function leaf_svg($x, $y, $s = 1, $w = 5) {
    return path_svg('M' . $x . ' ' . ($y + 45 * $s) . ' C' . ($x - 55 * $s) . ' ' . ($y + 10 * $s) . ' ' . ($x - 35 * $s) . ' ' . ($y - 35 * $s) . ' ' . $x . ' ' . ($y - 55 * $s) . ' C' . ($x + 35 * $s) . ' ' . ($y - 20 * $s) . ' ' . ($x + 55 * $s) . ' ' . ($y + 20 * $s) . ' ' . $x . ' ' . ($y + 45 * $s), $w);
}

function flower_svg($cx, $cy, $s = 1, $w = 6) {
    $parts = [];
    for ($i = 0; $i < 6; $i++) {
        $a = deg2rad($i * 60);
        $parts[] = ellipse_svg($cx + cos($a) * 42 * $s, $cy + sin($a) * 42 * $s, 24 * $s, 42 * $s, $w);
    }
    $parts[] = circle_svg($cx, $cy, 24 * $s, $w);
    return implode('', $parts);
}

function render_svg($drawing) {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 1000">' . PHP_EOL
        . '  <rect x="0" y="0" width="800" height="1000" fill="#ffffff"/>' . PHP_EOL
        . '  <g stroke="#000000" fill="none" stroke-linecap="round" stroke-linejoin="round">' . PHP_EOL
        . '    ' . border_svg() . PHP_EOL
        . '    ' . $drawing . PHP_EOL
        . '  </g>' . PHP_EOL
        . '</svg>' . PHP_EOL;
}

function render_category($type, $title) {
    switch ($type) {
        case 'dino': return render_dino($title);
        case 'ice': return render_ice($title);
        case 'vehicle': return render_vehicle($title);
        case 'fantasy': return render_fantasy($title);
        case 'animal': return render_animal($title);
        case 'sea': return render_sea($title);
        case 'space': return render_space($title);
        case 'robot': return render_robot($title);
        case 'food': return render_food($title);
        case 'holiday': return render_holiday($title);
        case 'sport': return render_sport($title);
        case 'monster': return render_monster($title);
        default: return flower_svg(400, 420, 1.5, 8);
    }
}

function render_dino($title) {
    $t = strtolower($title);
    if (strpos($t, 't-rex') !== false) return render_trex();
    if (strpos($t, 'triceratops') !== false) return render_triceratops();
    if (strpos($t, 'stego') !== false) return render_stegosaurus();
    if (strpos($t, 'brachio') !== false) return render_brachiosaurus();
    if (strpos($t, 'baby') !== false) return render_baby_dino();
    if (strpos($t, 'eieren') !== false) return render_dino_eggs();
    if (strpos($t, 'vulkaan') !== false) return render_volcano();
    if (strpos($t, 'fossiel') !== false) return render_fossil();
    if (strpos($t, 'pteranodon') !== false) return render_pteranodon();
    return render_ankylosaurus();
}

function render_trex() {
    return ground_svg(735)
        . path_svg('M245 575 C290 485 440 465 555 520 C610 545 610 595 555 625 C465 650 320 650 245 575 Z', 10)
        . path_svg('M520 520 C585 465 645 480 675 535 C635 570 575 570 520 520 Z', 9)
        . circle_svg(610, 515, 10, 5)
        . path_svg('M650 535 L690 525 L655 550 L685 570 L650 560 Z', 7)
        . path_svg('M260 575 C185 535 145 545 115 590', 10)
        . path_svg('M415 590 C380 625 345 640 315 635', 8)
        . line_svg(335, 635, 315, 710, 10) . line_svg(475, 630, 495, 710, 10) . line_svg(540, 600, 590, 665, 10)
        . line_svg(575, 530, 595, 545, 4) . line_svg(600, 548, 625, 548, 4)
        . leaf_svg(170, 650, 0.8, 5) . leaf_svg(650, 650, 0.8, 5);
}

function render_triceratops() {
    return ground_svg(735)
        . path_svg('M220 600 C270 510 430 495 560 535 C605 550 610 600 560 630 C455 655 305 655 220 600 Z', 10)
        . polygon_svg('555 495 625 430 700 470 660 560 565 565', 9)
        . line_svg(625, 430, 610, 375, 8) . line_svg(650, 450, 690, 395, 8) . line_svg(625, 500, 680, 500, 8)
        . circle_svg(590, 505, 8, 5) . path_svg('M575 525 C590 540 610 535 620 520', 5)
        . path_svg('M545 585 C610 565 650 580 675 610', 8)
        . line_svg(300, 625, 280, 710, 10) . line_svg(420, 630, 405, 710, 10) . line_svg(520, 615, 540, 710, 10)
        . flower_svg(190, 640, 0.9, 6) . flower_svg(640, 640, 0.9, 6);
}

function render_stegosaurus() {
    $plates = '';
    for ($i = 0; $i < 7; $i++) {
        $x = 275 + $i * 38;
        $y = 405 + ($i % 2) * 25;
        $plates .= polygon_svg(($x - 24) . ' 490 ' . $x . ' ' . $y . ' ' . ($x + 24) . ' 490', 7);
    }
    $spikes = '';
    for ($i = 0; $i < 4; $i++) { $spikes .= line_svg(625 + $i * 12, 585 - $i * 4, 650 + $i * 10, 565 - $i * 5, 6); }
    return ground_svg(735)
        . path_svg('M205 600 C260 510 455 495 565 545 C595 565 585 615 550 635 C445 660 300 660 205 600 Z', 10)
        . $plates . $spikes
        . path_svg('M545 570 C625 545 670 560 695 605 C640 605 590 615 545 590 Z', 8)
        . line_svg(300, 630, 280, 710, 10) . line_svg(410, 635, 400, 710, 10) . line_svg(500, 625, 520, 710, 10)
        . leaf_svg(170, 650, 0.8, 5);
}

function render_brachiosaurus() {
    return ground_svg(735)
        . path_svg('M220 600 C265 510 410 490 525 535 C560 550 560 595 530 620 C445 650 310 650 220 600 Z', 10)
        . path_svg('M505 535 C530 430 585 360 630 335 C655 365 620 410 585 395 C560 420 545 475 535 535 Z', 8)
        . circle_svg(630, 325, 34, 8) . polygon_svg('655 315 700 305 650 350', 6)
        . path_svg('M250 590 C180 560 150 595 170 625', 10)
        . line_svg(305, 625, 285, 710, 10) . line_svg(430, 630, 415, 710, 10) . line_svg(505, 605, 530, 710, 10)
        . path_svg('M165 300 C215 250 275 245 330 285 C285 300 250 335 230 385', 7) . path_svg('M230 385 C260 360 300 350 330 365', 5)
        . path_svg('M620 250 C675 210 710 230 735 270', 6) . path_svg('M675 270 C690 250 710 245 735 260', 4);
}

function render_baby_dino() {
    return ground_svg(745)
        . path_svg('M270 620 C300 520 500 520 530 620 C500 675 300 675 270 620 Z', 9)
        . path_svg('M300 620 C315 575 485 575 500 620 Z', 6)
        . circle_svg(400, 465, 70, 8) . polygon_svg('350 410 330 350 385 405', 7) . polygon_svg('450 405 470 350 415 405', 7)
        . circle_svg(380, 455, 8, 5) . circle_svg(420, 455, 8, 5) . path_svg('M390 490 C400 500 410 500 420 490', 5)
        . path_svg('M225 620 C260 560 320 555 340 620', 8) . path_svg('M460 620 C480 555 540 560 575 620', 8)
        . circle_svg(260, 690, 12, 5) . circle_svg(540, 690, 12, 5) . line_svg(300, 725, 340, 725, 5) . line_svg(460, 725, 500, 725, 5);
}

function render_dino_eggs() {
    return ground_svg(745)
        . ellipse_svg(250, 575, 70, 100, 9) . path_svg('M225 520 C250 485 290 490 305 520', 6)
        . ellipse_svg(400, 545, 78, 110, 9) . path_svg('M370 485 C405 445 455 455 470 490', 6)
        . ellipse_svg(555, 585, 65, 92, 9) . path_svg('M535 535 C555 505 590 510 600 535', 6)
        . path_svg('M210 675 C250 635 305 635 340 675', 5) . path_svg('M460 675 C505 635 560 635 600 675', 5)
        . flower_svg(180, 650, 0.8, 6) . flower_svg(620, 650, 0.8, 6) . leaf_svg(680, 650, 0.8, 5);
}

function render_volcano() {
    return ground_svg(740)
        . path_svg('M120 705 L315 330 L510 705 Z', 10) . path_svg('M315 330 L355 420 L285 420 Z', 8)
        . path_svg('M355 420 C330 470 315 520 300 575 C330 550 360 525 390 500 C405 545 430 585 470 620 C420 635 370 650 325 675', 6)
        . path_svg('M210 690 C260 660 300 660 340 690', 5) . path_svg('M465 690 C505 660 545 660 590 690', 5)
        . path_svg('M535 640 C585 575 625 585 665 635 C635 650 590 655 535 640 Z', 8)
        . path_svg('M545 610 C575 585 600 590 625 620', 5) . path_svg('M575 635 C595 615 615 615 635 635', 5)
        . leaf_svg(150, 650, 0.9, 5) . leaf_svg(700, 650, 0.9, 5) . star_svg(620, 300, 35, 15, 5, 6);
}

function render_fossil() {
    return ground_svg(745) . path_svg('M180 625 C230 545 310 530 370 575 C430 625 520 610 620 540', 9)
        . circle_svg(245, 590, 42, 8) . circle_svg(315, 555, 35, 8) . line_svg(315, 555, 360, 610, 8)
        . line_svg(355, 580, 420, 550, 8) . line_svg(400, 560, 455, 610, 8) . line_svg(445, 600, 515, 570, 8)
        . line_svg(510, 565, 575, 605, 8) . line_svg(575, 605, 620, 540, 8)
        . path_svg('M210 705 L590 705 L540 760 L260 760 Z', 8) . line_svg(250, 705, 230, 760, 7) . line_svg(560, 705, 590, 760, 7)
        . circle_svg(610, 330, 22, 6) . line_svg(610, 352, 610, 410, 6);
}

function render_pteranodon() {
    return ground_svg(735)
        . path_svg('M400 455 C310 365 190 330 85 390 C190 390 310 430 375 505 Z', 8)
        . path_svg('M400 455 C490 365 610 330 715 390 C610 390 490 430 425 505 Z', 8)
        . ellipse_svg(400, 520, 55, 115, 8) . circle_svg(400, 390, 55, 8)
        . polygon_svg('375 350 365 295 410 365', 7) . polygon_svg('425 365 435 295 390 350', 7)
        . polygon_svg('405 390 455 380 405 410', 6) . circle_svg(385, 385, 7, 4) . circle_svg(415, 385, 7, 4)
        . line_svg(370, 610, 345, 705, 8) . line_svg(430, 610, 455, 705, 8)
        . cloud_svg(210, 260, 0.8, 5) . cloud_svg(590, 260, 0.8, 5);
}

function render_ankylosaurus() {
    $armor = '';
    for ($i = 0; $i < 8; $i++) { $armor .= circle_svg(255 + $i * 42, 525 + ($i % 2) * 12, 22, 6); }
    return ground_svg(735)
        . path_svg('M205 610 C255 525 445 505 575 555 C610 570 610 615 570 635 C465 660 300 660 205 610 Z', 10)
        . $armor . path_svg('M560 580 C625 555 675 570 700 610 C650 615 610 620 560 605 Z', 8)
        . line_svg(590, 600, 640, 620, 6) . line_svg(605, 585, 650, 600, 6)
        . path_svg('M230 585 C285 545 340 545 385 585', 8) . circle_svg(300, 565, 8, 5)
        . line_svg(300, 635, 280, 710, 10) . line_svg(410, 640, 395, 710, 10) . line_svg(500, 630, 520, 710, 10)
        . flower_svg(180, 650, 0.8, 6);
}

function render_ice($title) {
    $t = strtolower($title);
    if (strpos($t, 'ijsprinses') !== false) return render_ice_princess();
    if (strpos($t, 'sneeuwkasteel') !== false) return render_snow_castle();
    if (strpos($t, 'winterkroon') !== false) return render_winter_crown();
    if (strpos($t, 'sneeuwvlok') !== false) return snowflake_svg(400, 430, 180, 8) . snowflake_svg(210, 640, 70, 6) . snowflake_svg(610, 620, 70, 6);
    if (strpos($t, 'pinguin') !== false) return render_penguin();
    if (strpos($t, 'paardenslee') !== false) return render_sleigh();
    if (strpos($t, 'ijsberg') !== false) return render_iceberg();
    if (strpos($t, 'sneeuwpop') !== false) return render_snowman();
    if (strpos($t, 'mut') !== false || strpos($t, 'want') !== false) return render_winter_accessories();
    return render_winter_forest();
}

function render_ice_princess() {
    return snowflake_svg(170, 230, 55, 5) . snowflake_svg(630, 250, 60, 5) . snowflake_svg(650, 620, 45, 5)
        . path_svg('M400 245 L425 330 L375 330 Z', 8) . circle_svg(400, 325, 12, 6) . circle_svg(370, 305, 8, 5) . circle_svg(430, 305, 8, 5)
        . circle_svg(400, 390, 70, 8) . path_svg('M345 380 C330 445 350 475 375 455', 7) . path_svg('M455 380 C470 445 450 475 425 455', 7)
        . circle_svg(375, 385, 8, 5) . circle_svg(425, 385, 8, 5) . path_svg('M385 425 C395 440 405 440 415 425', 5)
        . path_svg('M330 500 C350 620 450 620 470 500 Z', 9) . path_svg('M345 505 C275 555 245 620 270 675', 9) . path_svg('M455 505 C525 555 555 620 530 675', 9)
        . path_svg('M360 540 C390 570 410 570 440 540', 6) . line_svg(310, 470, 250, 520, 6) . circle_svg(245, 525, 10, 5)
        . path_svg('M260 735 C330 705 470 705 540 735', 5);
}

function render_snow_castle() {
    $parts = [rect_svg(230, 415, 340, 210, 8), rect_svg(180, 335, 110, 290, 8), rect_svg(510, 335, 110, 290, 8)];
    $parts[] = polygon_svg('180 335 235 275 290 335', 8); $parts[] = polygon_svg('510 335 565 275 620 335', 8);
    $parts[] = path_svg('M180 335 C210 315 260 315 290 335', 6); $parts[] = path_svg('M510 335 C540 315 590 315 620 335', 6);
    $parts[] = rect_svg(365, 500, 70, 120, 8, ['rx' => 25]); $parts[] = rect_svg(270, 445, 55, 65, 7); $parts[] = rect_svg(475, 445, 55, 65, 7);
    $parts[] = path_svg('M130 655 C230 615 570 615 670 655', 7); $parts[] = path_svg('M180 690 C300 660 500 660 620 690', 5);
    $parts[] = snowflake_svg(170, 250, 45, 5) . snowflake_svg(630, 250, 45, 5) . star_svg(400, 245, 30, 12, 5, 6);
    return implode('', $parts);
}

function render_winter_crown() {
    return snowflake_svg(400, 260, 70, 6)
        . path_svg('M250 470 L285 350 L350 445 L400 330 L450 445 L515 350 L550 470 Z', 9)
        . circle_svg(300, 350, 12, 6) . circle_svg(400, 330, 12, 6) . circle_svg(500, 350, 12, 6)
        . rect_svg(250, 470, 300, 35, 8, ['rx' => 12])
        . snowflake_svg(200, 610, 55, 5) . snowflake_svg(600, 610, 55, 5)
        . path_svg('M250 700 C330 670 470 670 550 700', 5);
}

function render_penguin() {
    return ground_svg(745) . path_svg('M150 705 C260 665 540 665 650 705', 5)
        . path_svg('M290 635 C250 540 275 420 340 350 C390 315 455 330 490 385 C555 455 555 560 510 635 C460 675 340 675 290 635 Z', 10)
        . ellipse_svg(400, 505, 95, 145, 8) . circle_svg(400, 390, 85, 8)
        . circle_svg(375, 380, 9, 5) . circle_svg(425, 380, 9, 5) . polygon_svg('400 395 370 420 430 420', 6)
        . path_svg('M330 505 C270 545 245 610 260 655', 8) . path_svg('M470 505 C530 545 555 610 540 655', 8)
        . line_svg(355, 650, 340, 690, 8) . line_svg(445, 650, 460, 690, 8)
        . snowflake_svg(170, 250, 45, 5) . snowflake_svg(630, 250, 45, 5);
}

function render_sleigh() {
    return ground_svg(745) . path_svg('M140 705 C260 665 540 665 660 705', 5)
        . path_svg('M230 570 L570 570 L620 640 L180 640 Z', 9) . path_svg('M250 570 C270 520 320 500 370 500 C430 500 480 520 550 570', 8)
        . path_svg('M180 640 C145 675 130 690 105 705', 8) . path_svg('M620 640 C655 675 675 690 700 705', 8)
        . circle_svg(245, 640, 10, 5) . circle_svg(555, 640, 10, 5)
        . path_svg('M160 500 C205 430 285 410 340 455 C390 430 455 445 480 500 C430 520 380 520 335 500 C280 520 220 520 160 500 Z', 9)
        . circle_svg(350, 465, 8, 5) . polygon_svg('390 455 435 440 405 475', 5)
        . line_svg(300, 520, 235, 570, 8) . line_svg(480, 520, 565, 570, 8)
        . star_svg(620, 260, 35, 15, 5, 6) . snowflake_svg(180, 260, 45, 5);
}

function render_iceberg() {
    return wave_svg(650, 6, 120, 680) . wave_svg(705, 5, 150, 650)
        . path_svg('M300 650 L420 320 L580 650 Z', 10) . path_svg('M330 650 L420 420 L520 650', 6)
        . path_svg('M420 320 C360 365 335 420 330 650', 5) . path_svg('M420 320 C480 365 505 420 520 650', 5)
        . path_svg('M560 620 C610 560 675 570 705 620 C650 640 610 640 560 620 Z', 8)
        . circle_svg(610, 585, 8, 5) . circle_svg(630, 585, 8, 5) . path_svg('M600 610 C615 625 635 625 650 610', 5)
        . line_svg(545, 620, 520, 665, 8) . line_svg(625, 620, 650, 665, 8)
        . snowflake_svg(180, 260, 45, 5) . snowflake_svg(650, 280, 45, 5);
}

function render_snowman() {
    return ground_svg(745) . path_svg('M150 705 C260 665 540 665 650 705', 5)
        . circle_svg(400, 410, 95, 9) . circle_svg(400, 560, 70, 9) . circle_svg(400, 680, 48, 8)
        . polygon_svg('355 340 340 270 390 330', 8) . polygon_svg('445 330 460 270 410 340', 8)
        . circle_svg(375, 400, 8, 5) . circle_svg(425, 400, 8, 5) . polygon_svg('400 415 370 435 430 435', 6)
        . path_svg('M365 450 C385 465 415 465 435 450', 5)
        . path_svg('M330 545 L250 500', 8) . path_svg('M470 545 L550 500', 8)
        . path_svg('M370 680 C390 695 410 695 430 680', 5)
        . snowflake_svg(170, 250, 45, 5) . snowflake_svg(630, 250, 45, 5);
}

function render_winter_accessories() {
    return snowflake_svg(400, 250, 65, 6) . snowflake_svg(180, 620, 45, 5) . snowflake_svg(620, 620, 45, 5)
        . path_svg('M250 430 C260 330 540 330 550 430 C520 480 280 480 250 430 Z', 10)
        . path_svg('M300 430 L300 610', 8) . path_svg('M400 430 L400 650', 8) . path_svg('M500 430 L500 610', 8)
        . path_svg('M260 650 C310 610 490 610 540 650', 8)
        . path_svg('M180 560 C130 520 130 465 180 430 C230 465 230 520 180 560 Z', 8)
        . path_svg('M620 560 C570 520 570 465 620 430 C670 465 670 520 620 560 Z', 8)
        . path_svg('M215 705 C300 675 500 675 585 705', 5);
}

function render_winter_forest() {
    $trees = '';
    foreach ([[200, 650, 0.85], [300, 610, 1], [500, 610, 1], [600, 650, 0.85]] as $tree) {
        $x = $tree[0]; $y = $tree[1]; $s = $tree[2];
        $trees .= line_svg($x, $y - 160 * $s, $x, $y + 70 * $s, 8 * $s);
        $trees .= polygon_svg(($x - 70 * $s) . ' ' . ($y - 95 * $s) . ' ' . $x . ' ' . ($y - 230 * $s) . ' ' . ($x + 70 * $s) . ' ' . ($y - 95 * $s), 8 * $s);
        $trees .= polygon_svg(($x - 95 * $s) . ' ' . ($y - 20 * $s) . ' ' . $x . ' ' . ($y - 170 * $s) . ' ' . ($x + 95 * $s) . ' ' . ($y - 20 * $s), 8 * $s);
    }
    return $trees . path_svg('M150 705 C260 670 540 670 650 705', 5) . circle_svg(400, 240, 55, 7) . path_svg('M430 220 C470 210 490 230 470 260 C450 245 430 240 430 220', 5) . snowflake_svg(620, 260, 45, 5);
}

function render_vehicle($title) {
    $t = strtolower($title);
    if (strpos($t, 'race') !== false) return render_racecar();
    if (strpos($t, 'brandweer') !== false) return render_firetruck();
    if (strpos($t, 'politie') !== false) return render_policecar();
    if (strpos($t, 'bus') !== false) return render_bus();
    if (strpos($t, 'trein') !== false) return render_train();
    if (strpos($t, 'helikopter') !== false) return render_helicopter();
    if (strpos($t, 'vliegtuig') !== false) return render_airplane();
    if (strpos($t, 'raket') !== false) return render_rocket();
    if (strpos($t, 'veerboot') !== false) return render_boat();
    if (strpos($t, 'fiets') !== false) return render_bicycle();
    if (strpos($t, 'tractor') !== false) return render_tractor();
    return render_crane_truck();
}

function render_racecar() {
    return ground_svg(745) . path_svg('M115 610 L235 505 L420 475 L575 540 L700 540 L725 615 L90 615 Z', 10)
        . polygon_svg('405 475 475 430 535 475', 7) . circle_svg(250, 615, 48) . circle_svg(560, 615, 48)
        . circle_svg(250, 615, 18, 7) . circle_svg(560, 615, 18, 7)
        . path_svg('M130 560 C250 535 370 535 490 560', 6) . path_svg('M500 560 C570 545 640 545 690 560', 6)
        . path_svg('M170 675 C270 645 530 645 630 675', 5) . path_svg('M190 705 C300 680 500 680 610 705', 4);
}

function render_firetruck() {
    return ground_svg(745) . path_svg('M150 590 L210 500 L430 485 L590 535 L665 535 L680 615 L125 615 Z', 10)
        . line_svg(235, 485, 565, 485, 7) . line_svg(260, 470, 540, 470, 5)
        . path_svg('M590 535 C625 520 650 530 665 555', 7) . line_svg(620, 535, 620, 610, 7)
        . path_svg('M205 515 L560 515 L560 545 L205 545 Z', 6) . line_svg(260, 515, 260, 545, 5) . line_svg(330, 515, 330, 545, 5) . line_svg(400, 515, 400, 545, 5) . line_svg(470, 515, 470, 545, 5) . line_svg(540, 515, 540, 545, 5)
        . circle_svg(265, 615, 48) . circle_svg(545, 615, 48) . circle_svg(265, 615, 18, 7) . circle_svg(545, 615, 18, 7)
        . star_svg(390, 470, 25, 10, 5, 5);
}

function render_policecar() {
    return ground_svg(745) . path_svg('M160 590 L225 500 L430 475 L575 535 L650 535 L675 615 L130 615 Z', 10)
        . rect_svg(360, 455, 85, 25, 7) . star_svg(402, 468, 18, 8, 5, 5)
        . rect_svg(300, 505, 105, 55, 7) . rect_svg(430, 505, 95, 50, 7)
        . path_svg('M250 555 L330 555', 6) . path_svg('M470 555 L550 555', 6)
        . circle_svg(265, 615, 48) . circle_svg(545, 615, 48) . circle_svg(265, 615, 18, 7) . circle_svg(545, 615, 18, 7)
        . path_svg('M170 675 C270 645 530 645 630 675', 5);
}

function render_bus() {
    return ground_svg(745) . rect_svg(130, 475, 540, 145, 8, ['rx' => 25])
        . rect_svg(190, 515, 80, 55, 7) . rect_svg(305, 515, 80, 55, 7) . rect_svg(420, 515, 80, 55, 7) . rect_svg(535, 515, 70, 55, 7)
        . line_svg(170, 575, 630, 575, 5) . path_svg('M170 595 C260 575 540 575 630 595', 5)
        . circle_svg(250, 620, 48) . circle_svg(550, 620, 48) . circle_svg(250, 620, 18, 7) . circle_svg(550, 620, 18, 7)
        . path_svg('M120 675 C250 650 550 650 680 675', 5);
}

function render_train() {
    return ground_svg(745) . path_svg('M170 610 L250 500 L560 500 L650 610 Z', 10)
        . rect_svg(250, 500, 310, 110, 8) . rect_svg(300, 535, 70, 55, 7) . rect_svg(400, 535, 70, 55, 7) . rect_svg(500, 535, 70, 55, 7)
        . path_svg('M560 500 L620 455 L650 500', 8) . circle_svg(590, 455, 12, 5)
        . path_svg('M170 610 L140 650 L660 650 L630 610', 7) . line_svg(250, 650, 250, 690, 8) . line_svg(550, 650, 550, 690, 8)
        . circle_svg(285, 610, 45) . circle_svg(515, 610, 45) . circle_svg(285, 610, 16, 7) . circle_svg(515, 610, 16, 7)
        . cloud_svg(210, 300, 0.8, 5) . cloud_svg(560, 300, 0.8, 5);
}

function render_helicopter() {
    return cloud_svg(210, 260, 0.8, 5) . cloud_svg(570, 260, 0.8, 5)
        . line_svg(210, 330, 590, 330, 8) . line_svg(400, 330, 400, 285, 8)
        . ellipse_svg(400, 455, 150, 80, 8) . rect_svg(310, 420, 180, 70, 8, ['rx' => 18])
        . rect_svg(405, 430, 75, 45, 7) . line_svg(535, 455, 650, 490, 8) . line_svg(650, 490, 620, 535, 8)
        . path_svg('M250 535 C210 575 190 610 205 635', 7) . path_svg('M150 675 C260 640 540 640 650 675', 5);
}

function render_airplane() {
    return cloud_svg(210, 260, 0.8, 5) . cloud_svg(570, 260, 0.8, 5)
        . path_svg('M155 500 L650 430 L690 475 L430 510 L385 610 L330 610 L375 520 Z', 9)
        . path_svg('M390 505 L315 430 L360 420 L425 500 Z', 8)
        . circle_svg(475, 465, 18, 6) . line_svg(230, 515, 180, 555, 7)
        . path_svg('M150 675 C260 640 540 640 650 675', 5);
}

function render_rocket() {
    return star_svg(210, 250, 35, 15, 5, 6) . star_svg(610, 285, 30, 13, 5, 6) . star_svg(610, 610, 28, 12, 5, 5)
        . path_svg('M400 245 C340 310 340 470 400 560 C460 470 460 310 400 245 Z', 10)
        . circle_svg(400, 365, 35) . polygon_svg('340 470 285 505 345 520', 8) . polygon_svg('460 470 515 505 455 520', 8)
        . polygon_svg('360 560 400 635 440 560', 8)
        . path_svg('M375 635 C350 675 330 705 300 730', 7) . path_svg('M425 635 C450 675 470 705 500 730', 7)
        . path_svg('M150 705 C260 675 540 675 650 705', 5);
}

function render_boat() {
    return wave_svg(650, 6, 120, 680) . wave_svg(705, 5, 150, 650)
        . path_svg('M180 555 L620 555 L555 650 L245 650 Z', 10)
        . line_svg(400, 300, 400, 555, 8) . path_svg('M410 330 L560 540 L410 520 Z', 8)
        . path_svg('M220 585 L580 585', 6) . circle_svg(300, 590, 25, 5) . circle_svg(500, 590, 25, 5)
        . cloud_svg(210, 280, 0.8, 5) . cloud_svg(590, 300, 0.8, 5);
}

function render_bicycle() {
    return ground_svg(745) . circle_svg(270, 610, 95) . circle_svg(270, 610, 25, 7) . circle_svg(530, 610, 95) . circle_svg(530, 610, 25, 7)
        . line_svg(270, 610, 400, 470, 8) . line_svg(400, 470, 530, 610, 8) . line_svg(400, 470, 400, 555, 8)
        . line_svg(360, 555, 505, 555, 8) . line_svg(400, 555, 360, 555, 8) . line_svg(400, 555, 505, 555, 8)
        . line_svg(400, 470, 350, 430, 7) . line_svg(400, 470, 455, 430, 7) . circle_svg(400, 405, 28, 7)
        . path_svg('M150 675 C260 645 540 645 650 675', 5);
}

function render_tractor() {
    return ground_svg(745) . rect_svg(300, 500, 150, 95, 8) . rect_svg(430, 455, 120, 140, 8, ['rx' => 20])
        . rect_svg(455, 480, 55, 45, 7) . circle_svg(330, 610, 58) . circle_svg(330, 610, 22, 8) . circle_svg(520, 610, 90) . circle_svg(520, 610, 35, 8)
        . path_svg('M270 500 C240 455 205 430 165 425', 8) . path_svg('M270 595 C220 575 190 610 175 650', 7)
        . path_svg('M150 705 C260 675 540 675 650 705', 5) . leaf_svg(650, 650, 0.8, 5);
}

function render_crane_truck() {
    return ground_svg(745) . rect_svg(150, 560, 160, 70, 8, ['rx' => 18]) . rect_svg(310, 510, 110, 120, 8, ['rx' => 18])
        . rect_svg(335, 535, 50, 45, 7) . circle_svg(225, 630, 45) . circle_svg(455, 630, 45) . circle_svg(225, 630, 16, 7) . circle_svg(455, 630, 16, 7)
        . line_svg(420, 510, 560, 330, 9) . line_svg(560, 330, 610, 330, 8) . line_svg(610, 330, 610, 560, 6) . circle_svg(610, 575, 22, 6)
        . path_svg('M585 575 L635 575 L635 620 L585 620 Z', 6)
        . path_svg('M150 675 C260 645 540 645 650 675', 5);
}

function render_fantasy($title) {
    $t = strtolower($title);
    if (strpos($t, 'eenhoorn') !== false) return render_unicorn();
    if (strpos($t, 'draak') !== false && strpos($t, 'ei') === false) return render_dragon();
    if (strpos($t, 'tovenaar') !== false) return render_wizard();
    if (strpos($t, 'fee') !== false) return render_fairy();
    if (strpos($t, 'toverstaf') !== false) return render_wand();
    if (strpos($t, 'kasteel') !== false) return render_castle();
    if (strpos($t, 'paddenstoel') !== false) return render_mushrooms();
    if (strpos($t, 'sterrenpoort') !== false) return render_star_gate();
    if (strpos($t, 'elf') !== false) return render_elf();
    return render_dragon_egg();
}

function render_unicorn() {
    return ground_svg(735) . flower_svg(180, 650, 0.9, 6) . flower_svg(620, 650, 0.9, 6)
        . path_svg('M220 590 C265 510 420 490 535 535 C575 550 580 595 545 620 C450 650 300 650 220 590 Z', 10)
        . circle_svg(535, 445, 70, 8) . polygon_svg('575 395 615 330 620 430', 8) . circle_svg(555, 435, 8, 5)
        . path_svg('M500 440 C545 425 575 440 590 465', 7) . path_svg('M250 545 C180 520 155 565 170 600', 10)
        . line_svg(305, 620, 285, 710, 9) . line_svg(420, 625, 405, 710, 9) . line_svg(505, 605, 530, 710, 9)
        . star_svg(620, 260, 35, 15, 5, 6) . path_svg('M150 705 C260 675 540 675 650 705', 5);
}

function render_dragon() {
    return ground_svg(735) . path_svg('M210 560 C265 480 425 470 545 520 C590 540 600 590 555 620 C455 640 305 635 210 560 Z', 10)
        . path_svg('M545 520 C610 455 675 455 705 500 C655 500 615 520 575 555 Z', 8)
        . polygon_svg('690 485 735 455 705 525', 7) . path_svg('M240 545 C180 500 145 515 120 555', 8)
        . path_svg('M240 575 C180 610 145 650 120 690', 8) . line_svg(330, 615, 315, 705, 8) . line_svg(470, 615, 485, 705, 8)
        . circle_svg(585, 485, 8, 5) . path_svg('M590 510 C610 525 625 520 635 505', 5)
        . polygon_svg('300 500 285 465 330 485', 6) . polygon_svg('385 485 370 450 415 475', 6)
        . path_svg('M150 705 C260 675 540 675 650 705', 5);
}

function render_wizard() {
    return star_svg(610, 300, 35, 15, 5, 6) . star_svg(190, 300, 30, 13, 5, 6)
        . polygon_svg('400 230 310 360 490 360', 9) . circle_svg(400, 410, 75, 8) . circle_svg(375, 405, 8, 5) . circle_svg(425, 405, 8, 5)
        . path_svg('M385 445 C395 460 405 460 415 445', 5) . path_svg('M325 500 C350 620 450 620 475 500 Z', 9)
        . line_svg(485, 455, 600, 345, 9) . path_svg('M330 535 C280 580 260 640 290 690', 9) . path_svg('M470 535 C520 580 540 640 510 690', 9)
        . path_svg('M150 705 C260 675 540 675 650 705', 5);
}

function render_fairy() {
    return star_svg(610, 280, 35, 15, 5, 6) . star_svg(200, 280, 30, 13, 5, 6)
        . circle_svg(400, 390, 60, 8) . path_svg('M330 495 C350 620 450 620 470 495 Z', 9)
        . path_svg('M325 390 C235 315 185 360 255 455', 8) . path_svg('M475 390 C565 315 615 360 545 455', 8)
        . line_svg(460, 430, 585, 345, 8) . circle_svg(380, 385, 8, 5) . circle_svg(420, 385, 8, 5) . path_svg('M390 420 C400 435 410 435 420 420', 5)
        . line_svg(340, 600, 315, 690, 8) . line_svg(460, 600, 485, 690, 8) . path_svg('M150 705 C260 675 540 675 650 705', 5);
}

function render_wand() {
    return star_svg(400, 250, 70, 30, 6, 7) . star_svg(230, 360, 30, 13, 5, 5) . star_svg(570, 360, 30, 13, 5, 5)
        . line_svg(400, 310, 400, 700, 12) . circle_svg(400, 360, 25, 7) . circle_svg(400, 455, 18, 6) . circle_svg(400, 560, 18, 6)
        . path_svg('M300 660 C360 625 440 625 500 660', 6) . path_svg('M150 705 C260 675 540 675 650 705', 5);
}

function render_castle() {
    return ground_svg(735) . rect_svg(230, 410, 340, 210, 8) . rect_svg(180, 330, 110, 290, 8) . rect_svg(510, 330, 110, 290, 8)
        . polygon_svg('180 330 235 275 290 330', 8) . polygon_svg('510 330 565 275 620 330', 8)
        . rect_svg(365, 500, 70, 120, 8, ['rx' => 25]) . rect_svg(270, 445, 55, 65, 7) . rect_svg(475, 445, 55, 65, 7)
        . path_svg('M130 650 C230 610 570 610 670 650', 7) . path_svg('M150 705 C260 675 540 675 650 705', 5)
        . star_svg(620, 260, 35, 15, 5, 6);
}

function render_mushrooms() {
    return ground_svg(745) . path_svg('M285 500 C300 410 500 410 515 500 Z', 9) . line_svg(360, 445, 360, 500, 5) . line_svg(430, 430, 430, 500, 5)
        . rect_svg(350, 500, 100, 130, 8, ['rx' => 18]) . circle_svg(385, 545, 12, 6) . circle_svg(430, 585, 10, 6)
        . path_svg('M180 610 C190 545 255 520 300 560 C260 575 235 600 230 640', 8) . path_svg('M230 640 L180 640', 8)
        . path_svg('M520 600 C530 545 585 525 630 560 C590 575 575 600 570 640', 8) . path_svg('M570 640 L630 640', 8)
        . circle_svg(330, 470, 8, 5) . circle_svg(460, 470, 8, 5) . star_svg(400, 320, 35, 15, 5, 6);
}

function render_star_gate() {
    return star_svg(400, 330, 95, 45, 8, 7) . circle_svg(400, 455, 95, 8) . circle_svg(400, 455, 45, 6)
        . path_svg('M300 620 C350 575 450 575 500 620', 7) . path_svg('M260 665 C330 630 470 630 540 665', 6)
        . star_svg(180, 250, 35, 15, 5, 6) . star_svg(620, 250, 35, 15, 5, 6) . star_svg(620, 620, 30, 13, 5, 6)
        . path_svg('M150 705 C260 675 540 675 650 705', 5);
}

function render_elf() {
    return ground_svg(735) . circle_svg(400, 390, 65, 8) . polygon_svg('340 340 270 300 325 390', 8) . polygon_svg('460 340 530 300 475 390', 8)
        . circle_svg(375, 385, 8, 5) . circle_svg(425, 385, 8, 5) . path_svg('M390 420 C400 435 410 435 420 420', 5)
        . path_svg('M335 495 C355 620 445 620 465 495 Z', 9) . path_svg('M330 535 C275 575 250 630 280 680', 9) . path_svg('M470 535 C525 575 550 630 520 680', 9)
        . line_svg(465, 440, 585, 360, 8) . star_svg(595, 345, 24, 10, 5, 6)
        . path_svg('M150 705 C260 675 540 675 650 705', 5);
}

function render_dragon_egg() {
    return ground_svg(745) . ellipse_svg(400, 560, 95, 130, 10) . path_svg('M345 500 C380 455 430 455 455 500', 7)
        . path_svg('M370 535 C395 505 430 510 445 540', 5) . path_svg('M355 605 C390 575 430 580 450 615', 5)
        . polygon_svg('350 455 330 420 380 445', 7) . polygon_svg('450 445 470 420 420 455', 7)
        . path_svg('M250 650 C310 610 490 610 550 650', 7) . flower_svg(210, 640, 0.8, 6) . flower_svg(590, 640, 0.8, 6)
        . star_svg(400, 280, 35, 15, 5, 6);
}

function render_animal($title) {
    $t = strtolower($title);
    if (strpos($t, 'kat') !== false) return render_cat();
    if (strpos($t, 'hond') !== false) return render_dog();
    if (strpos($t, 'konijn') !== false) return render_rabbit();
    if (strpos($t, 'uil') !== false) return render_owl();
    if (strpos($t, 'vos') !== false) return render_fox();
    if (strpos($t, 'leeuw') !== false) return render_lion();
    if (strpos($t, 'olifant') !== false) return render_elephant();
    if (strpos($t, 'giraffe') !== false) return render_giraffe();
    if (strpos($t, 'beer') !== false) return render_bear();
    if (strpos($t, 'panda') !== false) return render_panda();
    if (strpos($t, 'kikker') !== false) return render_frog();
    if (strpos($t, 'egel') !== false) return render_hedgehog();
    if (strpos($t, 'schildpad') !== false) return render_turtle();
    return render_koala();
}

function render_cat() {
    return ground_svg(735) . path_svg('M235 665 C210 600 270 540 400 540 C530 540 590 600 565 665 C545 720 255 720 235 665 Z', 10)
        . circle_svg(400, 360, 125) . polygon_svg('300 285 335 170 380 275', 8) . polygon_svg('420 275 465 170 500 285', 8)
        . circle_svg(355, 350, 13, 6) . circle_svg(445, 350, 13, 6) . path_svg('M400 382 L378 405 L422 405 Z', 7)
        . path_svg('M398 408 C382 424 358 420 342 408', 5) . path_svg('M402 408 C418 424 442 420 458 408', 5)
        . line_svg(335, 390, 250, 365, 5) . line_svg(335, 405, 245, 405, 5) . line_svg(335, 420, 250, 445, 5)
        . line_svg(465, 390, 550, 365, 5) . line_svg(465, 405, 555, 405, 5) . line_svg(465, 420, 550, 445, 5)
        . line_svg(260, 640, 245, 730, 9) . line_svg(330, 650, 320, 730, 9) . line_svg(470, 650, 480, 730, 9) . line_svg(540, 640, 555, 730, 9)
        . path_svg('M535 610 C620 555 640 650 560 640', 10) . circle_svg(270, 595, 38, 7) . path_svg('M285 585 L255 605', 5);
}

function render_dog() {
    return ground_svg(735) . path_svg('M230 665 C205 605 270 545 400 545 C530 545 595 605 570 665 C545 720 255 720 230 665 Z', 10)
        . circle_svg(400, 365, 120) . ellipse_svg(315, 355, 42, 82, 8) . ellipse_svg(485, 355, 42, 82, 8)
        . ellipse_svg(400, 395, 42, 28, 7) . circle_svg(400, 388, 10, 6) . circle_svg(355, 350, 12, 6) . circle_svg(445, 350, 12, 6)
        . path_svg('M395 410 C380 430 360 425 348 410', 5) . path_svg('M405 410 C420 430 440 425 452 410', 5)
        . line_svg(260, 640, 245, 730, 9) . line_svg(330, 650, 320, 730, 9) . line_svg(470, 650, 480, 730, 9) . line_svg(540, 640, 555, 730, 9)
        . path_svg('M530 610 C600 560 625 650 555 635', 10) . path_svg('M270 590 L320 620', 6);
}

function render_rabbit() {
    return ground_svg(735) . ellipse_svg(400, 610, 165, 105) . circle_svg(400, 380, 110)
        . ellipse_svg(350, 255, 35, 105, 8) . ellipse_svg(450, 255, 35, 105, 8)
        . circle_svg(355, 365, 12, 6) . circle_svg(445, 365, 12, 6) . path_svg('M400 390 L378 410 L422 410 Z', 7)
        . path_svg('M395 414 C380 430 360 426 348 412', 5) . path_svg('M405 414 C420 430 440 426 452 412', 5)
        . circle_svg(250, 585, 42) . line_svg(320, 680, 310, 745, 9) . line_svg(480, 680, 490, 745, 9)
        . path_svg('M220 690 C270 665 330 665 365 690', 5) . path_svg('M435 690 C470 665 530 665 580 690', 5);
}

function render_owl() {
    return line_svg(400, 300, 400, 720, 8) . path_svg('M330 300 L280 360 L365 340 Z', 7) . path_svg('M470 300 L520 360 L435 340 Z', 7)
        . ellipse_svg(400, 515, 150, 190) . circle_svg(400, 370, 135)
        . polygon_svg('315 300 280 235 350 315', 8) . polygon_svg('485 300 520 235 450 315', 8)
        . circle_svg(350, 365, 42, 8) . circle_svg(450, 365, 42, 8) . circle_svg(350, 365, 12, 5) . circle_svg(450, 365, 12, 5)
        . polygon_svg('392 405 408 405 400 430', 6) . line_svg(260, 670, 540, 650, 9)
        . line_svg(300, 670, 270, 735, 8) . line_svg(500, 650, 535, 720, 8) . leaf_svg(520, 650, 0.8, 5);
}

function render_fox() {
    return ground_svg(735) . path_svg('M230 620 C250 535 350 505 455 530 C520 545 560 590 545 645 C470 665 315 665 230 620 Z', 10)
        . polygon_svg('335 430 275 315 405 380', 8) . polygon_svg('465 430 525 315 395 380', 8)
        . polygon_svg('545 610 650 545 650 675', 8) . polygon_svg('570 620 630 590 625 650', 5)
        . circle_svg(375, 405, 10, 5) . circle_svg(425, 405, 10, 5) . polygon_svg('395 425 380 455 420 455', 6)
        . line_svg(320, 620, 305, 705, 8) . line_svg(480, 620, 495, 705, 8)
        . path_svg('M160 690 C250 660 330 660 390 690', 5) . leaf_svg(610, 650, 0.8, 5);
}

function render_lion() {
    $mane = '';
    for ($i = 0; $i < 16; $i++) { $a = deg2rad($i * 22.5); $mane .= line_svg(400 + cos($a) * 82, 365 + sin($a) * 82, 400 + cos($a) * 125, 365 + sin($a) * 125, 7); }
    return ground_svg(735) . $mane . circle_svg(400, 365, 85, 8) . circle_svg(365, 350, 10, 6) . circle_svg(435, 350, 10, 6)
        . ellipse_svg(400, 395, 42, 30, 7) . path_svg('M395 415 C380 435 420 435 405 415', 6)
        . ellipse_svg(400, 565, 150, 105) . path_svg('M520 610 C600 555 630 650 555 640', 10)
        . line_svg(330, 640, 315, 720, 9) . line_svg(470, 640, 485, 720, 9)
        . path_svg('M160 690 C250 660 330 660 390 690', 5);
}

function render_elephant() {
    return ground_svg(735) . path_svg('M210 590 C260 500 455 480 575 535 C610 555 610 610 565 635 C470 665 300 665 210 590 Z', 10)
        . path_svg('M560 535 C640 485 690 520 675 590 C635 610 600 590 560 560 Z', 8) . circle_svg(635, 535, 8, 5)
        . path_svg('M245 560 C180 535 145 575 160 625', 9) . path_svg('M245 590 C180 610 150 585 145 545', 7)
        . circle_svg(335, 505, 10, 5) . circle_svg(405, 505, 10, 5) . path_svg('M355 545 C380 560 405 560 430 545', 6)
        . line_svg(300, 635, 280, 710, 10) . line_svg(410, 640, 395, 710, 10) . line_svg(500, 625, 520, 710, 10)
        . flower_svg(180, 650, 0.8, 6) . flower_svg(620, 650, 0.8, 6);
}

function render_giraffe() {
    return ground_svg(735) . path_svg('M220 605 C265 525 405 505 520 545 C555 560 555 605 525 625 C435 655 305 655 220 605 Z', 10)
        . path_svg('M505 540 C520 430 540 340 585 285 C610 315 590 365 575 420 C565 470 560 510 555 540 Z', 8)
        . circle_svg(585, 275, 42, 8) . polygon_svg('545 245 525 210 570 250', 7) . polygon_svg('625 250 650 210 635 260', 7)
        . circle_svg(575, 270, 8, 5) . circle_svg(600, 270, 8, 5) . path_svg('M580 305 C590 320 605 320 615 305', 5)
        . polygon_svg('300 530 330 565 285 570', 6) . polygon_svg('390 525 420 560 375 565', 6) . polygon_svg('470 545 500 580 455 585', 6)
        . line_svg(300, 630, 280, 710, 10) . line_svg(430, 635, 415, 710, 10) . line_svg(505, 610, 530, 710, 10)
        . leaf_svg(640, 260, 0.8, 5);
}

function render_bear() {
    return ground_svg(735) . ellipse_svg(400, 570, 170, 150) . circle_svg(400, 365, 130)
        . circle_svg(310, 280, 50) . circle_svg(490, 280, 50) . circle_svg(355, 350, 12, 6) . circle_svg(445, 350, 12, 6)
        . ellipse_svg(400, 395, 55, 38, 7) . circle_svg(400, 390, 12, 6) . path_svg('M395 420 C380 438 420 438 405 420', 6)
        . path_svg('M250 535 C190 595 185 670 245 690', 10) . path_svg('M550 535 C610 595 615 670 555 690', 10)
        . path_svg('M285 500 L350 535 L320 560 L250 525', 6) . path_svg('M305 515 C325 525 345 525 360 515', 4);
}

function render_panda() {
    return ground_svg(735) . ellipse_svg(400, 575, 175, 140) . circle_svg(400, 375, 125)
        . circle_svg(330, 320, 42) . circle_svg(470, 320, 42) . ellipse_svg(345, 370, 35, 45, 8) . ellipse_svg(455, 370, 35, 45, 8)
        . circle_svg(365, 365, 10, 6) . circle_svg(435, 365, 10, 6) . ellipse_svg(400, 400, 42, 28, 7)
        . path_svg('M390 430 C400 445 410 445 420 430', 5)
        . line_svg(280, 640, 260, 710, 9) . line_svg(360, 650, 345, 710, 9) . line_svg(440, 650, 455, 710, 9) . line_svg(520, 640, 540, 710, 9)
        . path_svg('M250 535 C190 585 180 650 230 690', 8) . path_svg('M550 535 C610 585 620 650 570 690', 8)
        . path_svg('M270 455 L315 505 L285 535 L235 485', 6) . path_svg('M530 485 L485 535 L515 505 L530 455', 6);
}

function render_frog() {
    return wave_svg(670, 6, 130, 670) . ellipse_svg(400, 560, 165, 115)
        . circle_svg(340, 430, 65) . circle_svg(460, 430, 65) . circle_svg(340, 405, 18, 7) . circle_svg(460, 405, 18, 7)
        . circle_svg(340, 405, 7, 4) . circle_svg(460, 405, 7, 4) . path_svg('M365 475 C390 500 410 500 435 475', 7)
        . path_svg('M260 600 C190 640 170 705 230 720', 9) . path_svg('M540 600 C610 640 630 705 570 720', 9)
        . line_svg(330, 650, 300, 735, 8) . line_svg(470, 650, 500, 735, 8)
        . path_svg('M250 675 C320 645 480 645 550 675', 5);
}

function render_hedgehog() {
    $spines = '';
    for ($i = 0; $i < 12; $i++) { $x = 250 + $i * 27; $spines .= line_svg($x, 410, $x - 18, 360 + ($i % 3) * 18, 6); }
    return ground_svg(735) . $spines . ellipse_svg(400, 545, 180, 105) . circle_svg(545, 475, 70)
        . polygon_svg('575 445 635 420 590 490', 6) . circle_svg(560, 465, 7, 5) . path_svg('M585 495 C570 510 550 505 540 490', 5)
        . line_svg(330, 630, 315, 705, 8) . line_svg(470, 635, 485, 705, 8)
        . circle_svg(250, 610, 28, 7) . circle_svg(290, 585, 24, 7) . circle_svg(325, 625, 22, 7)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_turtle() {
    return ground_svg(735) . ellipse_svg(400, 545, 175, 105) . circle_svg(400, 545, 95, 8)
        . polygon_svg('330 505 365 535 330 565', 5) . polygon_svg('400 470 400 520 400 570', 5) . polygon_svg('470 505 435 535 470 565', 5)
        . circle_svg(560, 505, 55) . circle_svg(580, 495, 7, 5) . path_svg('M575 530 C585 545 605 545 615 530', 5)
        . line_svg(280, 610, 250, 675, 8) . line_svg(360, 625, 340, 685, 8) . line_svg(440, 625, 460, 685, 8) . line_svg(520, 610, 550, 675, 8)
        . flower_svg(180, 650, 0.8, 6) . flower_svg(620, 650, 0.8, 6);
}

function render_koala() {
    return line_svg(400, 280, 400, 730, 10) . path_svg('M360 300 L310 365 L395 340 Z', 7) . path_svg('M440 300 L490 365 L405 340 Z', 7)
        . ellipse_svg(400, 500, 145, 130) . circle_svg(400, 365, 105)
        . circle_svg(330, 310, 42) . circle_svg(470, 310, 42) . ellipse_svg(350, 365, 30, 38, 8) . ellipse_svg(450, 365, 30, 38, 8)
        . circle_svg(365, 360, 10, 6) . circle_svg(435, 360, 10, 6) . ellipse_svg(400, 395, 35, 25, 7)
        . path_svg('M390 420 C400 435 410 435 420 420', 5)
        . line_svg(300, 520, 260, 620, 9) . line_svg(500, 520, 540, 620, 9) . line_svg(335, 610, 315, 700, 9) . line_svg(465, 610, 485, 700, 9)
        . leaf_svg(520, 360, 0.9, 5);
}

function render_sea($title) {
    $t = strtolower($title);
    if (strpos($t, 'dolfijn') !== false) return render_dolphin();
    if (strpos($t, 'walvis') !== false) return render_whale();
    if (strpos($t, 'octopus') !== false) return render_octopus();
    if (strpos($t, 'zeester') !== false) return render_starfish();
    if (strpos($t, 'koraal') !== false) return render_coral();
    if (strpos($t, 'schelp') !== false) return render_shells();
    if (strpos($t, 'zeemeermin') !== false) return render_mermaid();
    if (strpos($t, 'zeilboot') !== false) return render_sailboat();
    if (strpos($t, 'krab') !== false) return render_crab();
    return render_fish();
}

function render_fish() {
    return wave_svg(650, 6, 120, 680) . wave_svg(705, 5, 150, 650)
        . ellipse_svg(390, 480, 160, 85) . polygon_svg('545 480 650 420 650 540', 8)
        . circle_svg(330, 455, 12, 6) . path_svg('M380 400 C420 360 460 390 475 430', 7) . path_svg('M380 560 C420 600 460 570 475 530', 7)
        . circle_svg(210, 330, 18, 5) . circle_svg(610, 330, 16, 5) . circle_svg(610, 610, 14, 5);
}

function render_dolphin() {
    return wave_svg(650, 6, 120, 680) . wave_svg(705, 5, 150, 650)
        . path_svg('M205 500 C270 395 455 395 560 470 C500 505 430 525 330 520 C280 520 245 515 205 500 Z', 9)
        . polygon_svg('420 405 455 340 475 435', 7) . path_svg('M205 500 C150 510 125 545 155 570 C175 540 190 520 205 500 Z', 8)
        . circle_svg(520, 455, 8, 5) . circle_svg(430, 575, 28, 6) . path_svg('M405 575 C430 590 455 590 475 575', 5);
}

function render_whale() {
    return wave_svg(650, 6, 120, 680) . wave_svg(705, 5, 150, 650)
        . path_svg('M165 535 C210 430 430 405 575 475 C520 545 300 580 165 535 Z', 10)
        . polygon_svg('390 420 425 350 445 455', 7) . path_svg('M170 535 C120 545 95 585 130 605 C145 575 155 550 170 535 Z', 8)
        . path_svg('M555 470 C590 430 625 425 635 460 C610 470 585 485 555 470 Z', 7)
        . path_svg('M520 430 C510 390 500 365 485 340', 6) . circle_svg(220, 330, 18, 5) . circle_svg(610, 330, 16, 5);
}

function render_octopus() {
    return wave_svg(690, 6, 120, 680)
        . ellipse_svg(400, 455, 140, 115) . circle_svg(350, 420, 10, 6) . circle_svg(450, 420, 10, 6) . path_svg('M385 485 C400 500 415 500 430 485', 5)
        . path_svg('M300 545 C250 610 235 665 270 705', 8) . path_svg('M345 555 C330 620 350 670 385 705', 8)
        . path_svg('M390 555 C385 620 415 620 410 705', 8) . path_svg('M455 555 C470 620 450 670 415 705', 8)
        . path_svg('M500 545 C550 610 565 665 530 705', 8)
        . path_svg('M250 350 C220 295 185 285 155 330', 7) . path_svg('M550 350 C580 295 615 285 645 330', 7)
        . circle_svg(210, 620, 16, 5) . circle_svg(590, 620, 16, 5);
}

function render_starfish() {
    $parts = [];
    for ($i = 0; $i < 5; $i++) {
        $a1 = deg2rad(-90 + $i * 72);
        $a2 = deg2rad(-90 + $i * 72 + 18);
        $parts[] = polygon_svg(round(400 + cos($a1) * 150) . ',' . round(390 + sin($a1) * 150) . ' ' . round(400 + cos($a2) * 55) . ',' . round(390 + sin($a2) * 55) . ' ' . round(400 + cos($a1 + deg2rad(18)) * 55) . ',' . round(390 + sin($a1 + deg2rad(18)) * 55), 8);
    }
    return wave_svg(650, 6, 120, 680) . wave_svg(705, 5, 150, 650) . implode('', $parts) . circle_svg(220, 330, 18, 5) . circle_svg(610, 330, 16, 5);
}

function render_coral() {
    return wave_svg(690, 6, 120, 680)
        . path_svg('M300 675 C285 600 310 540 350 500 C385 535 370 595 355 675', 8) . path_svg('M350 560 L405 520 L390 590', 6)
        . path_svg('M430 675 C420 610 455 560 500 520 C535 560 520 620 505 675', 8) . path_svg('M470 585 L525 545 L510 620', 6)
        . path_svg('M250 675 C250 610 285 565 325 535', 7) . path_svg('M285 590 L330 555 L315 620', 5)
        . circle_svg(210, 330, 18, 5) . circle_svg(610, 330, 16, 5) . circle_svg(230, 610, 14, 5) . circle_svg(590, 610, 14, 5);
}

function render_shells() {
    return wave_svg(690, 6, 120, 680) . wave_svg(735, 5, 150, 650)
        . path_svg('M250 620 C250 530 325 470 400 520 C475 470 550 530 550 620 C500 670 300 670 250 620 Z', 9)
        . path_svg('M270 620 C320 585 360 585 400 620 C440 585 480 585 530 620', 6)
        . line_svg(400, 520, 400, 620, 5) . line_svg(330, 545, 360, 620, 5) . line_svg(470, 545, 440, 620, 5)
        . path_svg('M180 610 C220 565 280 565 315 610', 7) . path_svg('M485 610 C520 565 580 565 620 610', 7)
        . circle_svg(220, 330, 18, 5) . circle_svg(610, 330, 16, 5);
}

function render_mermaid() {
    return wave_svg(690, 6, 120, 680) . wave_svg(735, 5, 150, 650)
        . circle_svg(390, 365, 55, 8) . path_svg('M345 350 C315 305 350 280 390 315 C430 280 465 305 435 350', 8)
        . circle_svg(375, 360, 8, 5) . circle_svg(405, 360, 8, 5) . path_svg('M385 390 C395 405 405 405 415 390', 5)
        . path_svg('M330 445 C350 520 430 520 450 445 C430 560 350 560 330 445 Z', 9)
        . path_svg('M345 545 C285 610 250 665 300 705 C345 660 380 610 400 545 Z', 8) . path_svg('M455 545 C515 610 550 665 500 705 C455 660 420 610 400 545 Z', 8)
        . path_svg('M320 430 C260 390 230 430 250 475', 8) . path_svg('M460 430 C520 390 550 430 530 475', 8)
        . star_svg(610, 280, 35, 15, 5, 6) . circle_svg(220, 330, 18, 5);
}

function render_sailboat() {
    return wave_svg(650, 6, 120, 680) . wave_svg(705, 5, 150, 650)
        . path_svg('M180 555 L620 555 L555 650 L245 650 Z', 10)
        . line_svg(400, 300, 400, 555, 8) . path_svg('M410 330 L560 540 L410 520 Z', 8)
        . path_svg('M390 340 L250 535 L390 520 Z', 8) . circle_svg(300, 590, 25, 5) . circle_svg(500, 590, 25, 5)
        . cloud_svg(210, 280, 0.8, 5) . cloud_svg(590, 300, 0.8, 5);
}

function render_crab() {
    return wave_svg(690, 6, 120, 680) . wave_svg(735, 5, 150, 650)
        . ellipse_svg(400, 530, 150, 85) . circle_svg(335, 455, 55) . circle_svg(465, 455, 55)
        . circle_svg(315, 455, 10, 5) . circle_svg(485, 455, 10, 5) . path_svg('M395 495 C380 515 420 515 405 495', 6)
        . line_svg(250, 505, 160, 455) . line_svg(160, 455, 150, 520) . line_svg(260, 555, 165, 605) . line_svg(165, 605, 190, 650)
        . line_svg(550, 505, 640, 455) . line_svg(640, 455, 650, 520) . line_svg(540, 555, 635, 605) . line_svg(635, 605, 610, 650)
        . path_svg('M300 650 C350 625 450 625 500 650', 5);
}

function render_space($title) {
    $t = strtolower($title);
    if (strpos($t, 'astronaut') !== false) return render_astronaut();
    if (strpos($t, 'maanlander') !== false) return render_lunar_lander();
    if (strpos($t, 'planeet') !== false && strpos($t, 'planeten') === false) return render_planet();
    if (strpos($t, 'komeet') !== false) return render_comet();
    if (strpos($t, 'satelliet') !== false) return render_satellite();
    if (strpos($t, 'sterrenbeeld') !== false) return render_constellation();
    if (strpos($t, 'ruimtestation') !== false) return render_space_station();
    if (strpos($t, 'telescoop') !== false) return render_telescope();
    if (strpos($t, 'asteroide') !== false) return render_asteroids();
    return render_planet_row();
}

function render_astronaut() {
    return star_svg(220, 280, 35, 15, 5, 5) . star_svg(610, 300, 35, 15, 5, 5) . star_svg(610, 610, 28, 12, 5, 5)
        . circle_svg(400, 390, 95) . circle_svg(400, 390, 45) . circle_svg(370, 385, 8, 5) . circle_svg(430, 385, 8, 5)
        . rect_svg(320, 485, 160, 140, 8, ['rx' => 35]) . line_svg(330, 530, 250, 585, 9) . line_svg(470, 530, 550, 585, 9)
        . line_svg(350, 625, 320, 710, 9) . line_svg(450, 625, 480, 710, 9)
        . path_svg('M360 520 L440 520', 5) . path_svg('M360 565 L440 565', 5);
}

function render_lunar_lander() {
    return star_svg(220, 280, 35, 15, 5, 5) . star_svg(610, 300, 35, 15, 5, 5)
        . polygon_svg('400 300 340 390 365 470 435 470 460 390', 8) . rect_svg(360, 390, 80, 55, 7)
        . line_svg(365, 470, 315, 560, 8) . line_svg(435, 470, 485, 560, 8) . line_svg(315, 560, 285, 610, 8) . line_svg(485, 560, 515, 610, 8)
        . line_svg(400, 300, 400, 250, 7) . circle_svg(400, 235, 28, 7)
        . path_svg('M250 675 C330 635 470 635 550 675', 6) . circle_svg(280, 650, 12, 5) . circle_svg(520, 650, 12, 5);
}

function render_planet() {
    return star_svg(220, 280, 35, 15, 5, 5) . star_svg(610, 610, 30, 13, 5, 5)
        . ellipse_svg(400, 455, 150, 100) . ellipse_svg(400, 455, 230, 38, 7)
        . circle_svg(345, 425, 22, 6) . circle_svg(455, 500, 16, 6)
        . path_svg('M330 455 C365 430 435 430 470 455', 5) . path_svg('M340 500 C375 525 425 525 460 500', 5);
}

function render_comet() {
    return star_svg(610, 280, 35, 15, 5, 5) . star_svg(210, 610, 30, 13, 5, 5)
        . circle_svg(475, 430, 55) . path_svg('M145 360 C250 315 350 335 430 385', 9)
        . path_svg('M145 430 C260 405 350 420 430 455', 8) . path_svg('M145 500 C250 490 350 505 430 535', 7)
        . circle_svg(220, 330, 18, 5) . circle_svg(610, 610, 16, 5);
}

function render_satellite() {
    return star_svg(220, 280, 35, 15, 5, 5) . star_svg(610, 300, 35, 15, 5, 5)
        . rect_svg(355, 430, 90, 70, 8, ['rx' => 14]) . circle_svg(400, 465, 18, 6)
        . line_svg(355, 465, 210, 420, 8) . line_svg(445, 465, 590, 420, 8)
        . rect_svg(185, 385, 70, 70, 7) . rect_svg(545, 385, 70, 70, 7)
        . line_svg(220, 420, 185, 385, 5) . line_svg(220, 420, 255, 385, 5) . line_svg(220, 420, 185, 455, 5) . line_svg(220, 420, 255, 455, 5)
        . line_svg(580, 420, 545, 385, 5) . line_svg(580, 420, 615, 385, 5) . line_svg(580, 420, 545, 455, 5) . line_svg(580, 420, 615, 455, 5);
}

function render_constellation() {
    $points = [[250,300],[330,260],[410,310],[500,280],[580,340],[520,430],[420,400],[330,450]];
    $poly = '';
    for ($i = 0; $i < count($points) - 1; $i++) { $poly .= line_svg($points[$i][0], $points[$i][1], $points[$i + 1][0], $points[$i + 1][1], 5); }
    $stars = '';
    foreach ($points as $p) { $stars .= star_svg($p[0], $p[1], 22, 9, 5, 5); }
    return $poly . $stars . circle_svg(210, 610, 16, 5) . circle_svg(610, 610, 16, 5);
}

function render_space_station() {
    return star_svg(220, 280, 35, 15, 5, 5) . star_svg(610, 300, 35, 15, 5, 5)
        . rect_svg(300, 430, 200, 80, 8, ['rx' => 20]) . circle_svg(400, 470, 35, 7) . rect_svg(340, 455, 120, 30, 6)
        . line_svg(300, 470, 170, 430, 8) . line_svg(500, 470, 630, 430, 8)
        . rect_svg(135, 390, 70, 80, 7) . rect_svg(595, 390, 70, 80, 7)
        . line_svg(170, 430, 135, 390, 5) . line_svg(170, 430, 205, 390, 5) . line_svg(170, 470, 135, 470, 5) . line_svg(170, 470, 205, 470, 5)
        . line_svg(630, 430, 595, 390, 5) . line_svg(630, 430, 665, 390, 5) . line_svg(630, 470, 595, 470, 5) . line_svg(630, 470, 665, 470, 5);
}

function render_telescope() {
    return star_svg(610, 280, 35, 15, 5, 5) . star_svg(210, 300, 30, 13, 5, 5)
        . path_svg('M250 500 L520 360 L575 405 L305 545 Z', 9) . rect_svg(210, 520, 80, 45, 8, ['rx' => 12])
        . line_svg(360, 545, 360, 675, 8) . line_svg(300, 675, 430, 675, 8)
        . path_svg('M150 705 C260 675 540 675 650 705', 5);
}

function render_asteroids() {
    $parts = [star_svg(220, 280, 35, 15, 5, 5), star_svg(610, 300, 35, 15, 5, 5)];
    foreach ([[300,380,80],[430,320,55],[560,430,70],[360,560,65],[500,575,45]] as $a) {
        $parts[] = path_svg('M' . ($a[0] - $a[2]) . ' ' . ($a[1] - 25) . ' C' . ($a[0] - 20) . ' ' . ($a[1] - 60) . ' ' . ($a[0] + 40) . ' ' . ($a[1] - 45) . ' ' . ($a[0] + $a[2]) . ' ' . ($a[1] - 10) . ' C' . ($a[0] + 35) . ' ' . ($a[1] + 50) . ' ' . ($a[0] - 40) . ' ' . ($a[1] + 45) . ' ' . ($a[0] - $a[2]) . ' ' . ($a[1] - 25), 8);
        $parts[] = circle_svg($a[0] - 20, $a[1] + 5, 8, 5);
    }
    return implode('', $parts);
}

function render_planet_row() {
    return star_svg(220, 280, 35, 15, 5, 5) . star_svg(610, 300, 35, 15, 5, 5)
        . circle_svg(230, 455, 45) . circle_svg(330, 430, 60) . ellipse_svg(430, 455, 70, 45) . ellipse_svg(430, 455, 120, 22, 6) . circle_svg(535, 440, 50) . circle_svg(610, 470, 35)
        . path_svg('M160 675 C250 640 550 640 640 675', 5) . circle_svg(190, 620, 16, 5) . circle_svg(650, 620, 16, 5);
}

function render_robot($title) {
    $t = strtolower($title);
    if (strpos($t, 'hond') !== false) return render_robot_dog();
    if (strpos($t, 'ballon') !== false) return render_robot_balloons();
    if (strpos($t, 'auto') !== false) return render_robot_car();
    if (strpos($t, 'arm') !== false) return render_robot_arm();
    if (strpos($t, 'huis') !== false) return render_robot_house();
    if (strpos($t, 'speel') !== false) return render_robot_toy();
    if (strpos($t, 'fabriek') !== false) return render_robot_factory();
    if (strpos($t, 'bloem') !== false) return render_robot_flowers();
    if (strpos($t, 'maan') !== false) return render_robot_moonrover();
    return render_robot_friendly();
}

function render_robot_friendly() {
    return rect_svg(300, 330, 200, 180, 9, ['rx' => 24]) . circle_svg(360, 405, 18, 7) . circle_svg(440, 405, 18, 7)
        . rect_svg(350, 455, 100, 22, 7, ['rx' => 10]) . line_svg(400, 330, 400, 285, 8) . circle_svg(400, 270, 24, 7)
        . rect_svg(250, 510, 55, 120, 8, ['rx' => 20]) . rect_svg(495, 510, 55, 120, 8, ['rx' => 20])
        . line_svg(275, 630, 250, 705, 8) . line_svg(525, 630, 550, 705, 8)
        . rect_svg(340, 610, 120, 35, 8, ['rx' => 12]) . circle_svg(365, 645, 14, 6) . circle_svg(435, 645, 14, 6)
        . star_svg(210, 280, 30, 13, 5, 5) . star_svg(610, 280, 30, 13, 5, 5);
}

function render_robot_dog() {
    return rect_svg(300, 430, 200, 110, 9, ['rx' => 24]) . rect_svg(455, 390, 95, 90, 8, ['rx' => 18]) . circle_svg(485, 425, 12, 6)
        . polygon_svg('345 395 330 330 390 385', 8) . polygon_svg('430 385 470 330 455 395', 8)
        . rect_svg(250, 540, 60, 100, 8, ['rx' => 18]) . rect_svg(490, 540, 60, 100, 8, ['rx' => 18])
        . circle_svg(280, 640, 25) . circle_svg(520, 640, 25) . path_svg('M500 480 C540 455 570 465 590 500', 7)
        . star_svg(210, 280, 30, 13, 5, 5) . star_svg(610, 280, 30, 13, 5, 5);
}

function render_robot_balloons() {
    return rect_svg(315, 390, 170, 160, 9, ['rx' => 22]) . circle_svg(370, 455, 16, 7) . circle_svg(430, 455, 16, 7)
        . path_svg('M380 505 C400 520 420 520 440 505', 6) . line_svg(400, 390, 400, 340, 8) . circle_svg(400, 325, 20, 7)
        . rect_svg(250, 550, 50, 120, 8, ['rx' => 18]) . rect_svg(500, 550, 50, 120, 8, ['rx' => 18])
        . line_svg(275, 670, 250, 725, 8) . line_svg(525, 670, 550, 725, 8)
        . path_svg('M230 300 C200 245 235 210 280 240 C315 210 350 245 320 300 C290 330 260 330 230 300 Z', 7) . line_svg(280, 300, 315, 360, 6)
        . path_svg('M480 285 C450 230 485 195 530 225 C565 195 600 230 570 285 C540 315 510 315 480 285 Z', 7) . line_svg(530, 285, 485, 360, 6);
}

function render_robot_car() {
    return path_svg('M160 590 L225 500 L430 475 L575 535 L650 535 L675 610 L130 610 Z', 9)
        . rect_svg(300, 505, 105, 55, 7) . rect_svg(430, 505, 95, 50, 7)
        . circle_svg(265, 610, 48) . circle_svg(545, 610, 48) . circle_svg(265, 610, 18, 7) . circle_svg(545, 610, 18, 7)
        . rect_svg(350, 360, 100, 90, 9, ['rx' => 18]) . circle_svg(380, 405, 12, 6) . circle_svg(420, 405, 12, 6) . path_svg('M385 430 C395 440 405 440 415 430', 5)
        . line_svg(400, 360, 400, 315, 8) . circle_svg(400, 300, 20, 7)
        . path_svg('M170 675 C270 645 530 645 630 675', 5);
}

function render_robot_arm() {
    return path_svg('M180 675 L620 675 L580 735 L220 735 Z', 9) . rect_svg(350, 590, 100, 85, 9, ['rx' => 18])
        . line_svg(400, 590, 470, 480, 10) . line_svg(470, 480, 560, 455, 9) . line_svg(560, 455, 590, 515, 8)
        . circle_svg(400, 590, 28, 7) . circle_svg(470, 480, 24, 7) . circle_svg(560, 455, 20, 7)
        . path_svg('M585 515 L630 515 L615 555 L590 555 Z', 6)
        . rect_svg(230, 520, 120, 45, 7, ['rx' => 12]) . circle_svg(260, 542, 12, 5) . circle_svg(320, 542, 12, 5);
}

function render_robot_house() {
    return rect_svg(210, 430, 380, 220, 9, ['rx' => 22]) . polygon_svg('190 430 400 300 610 430', 9)
        . rect_svg(365, 525, 70, 125, 8, ['rx' => 25]) . rect_svg(260, 475, 80, 70, 7) . rect_svg(460, 475, 80, 70, 7)
        . circle_svg(300, 510, 10, 5) . circle_svg(500, 510, 10, 5) . line_svg(400, 300, 400, 250, 8) . circle_svg(400, 235, 24, 7)
        . rect_svg(250, 650, 80, 60, 7, ['rx' => 12]) . rect_svg(470, 650, 80, 60, 7, ['rx' => 12])
        . path_svg('M150 705 C260 675 540 675 650 705', 5);
}

function render_robot_toy() {
    return rect_svg(300, 380, 200, 160, 9, ['rx' => 22]) . circle_svg(360, 445, 16, 7) . circle_svg(440, 445, 16, 7)
        . path_svg('M380 495 C400 510 420 510 440 495', 6) . rect_svg(350, 540, 100, 35, 7, ['rx' => 12])
        . circle_svg(260, 610, 60) . circle_svg(540, 610, 60) . circle_svg(260, 610, 20, 7) . circle_svg(540, 610, 20, 7)
        . line_svg(300, 540, 260, 610, 8) . line_svg(500, 540, 540, 610, 8)
        . path_svg('M350 380 L350 330 L450 330 L450 380', 7) . circle_svg(400, 315, 18, 6)
        . star_svg(210, 280, 30, 13, 5, 5) . star_svg(610, 280, 30, 13, 5, 5);
}

function render_robot_factory() {
    return rect_svg(170, 470, 460, 180, 9) . polygon_svg('170 470 260 380 350 470', 8) . polygon_svg('350 470 440 380 530 470', 8) . polygon_svg('530 470 620 395 630 470', 8)
        . rect_svg(230, 520, 70, 60, 7) . rect_svg(365, 520, 70, 60, 7) . rect_svg(500, 520, 70, 60, 7)
        . circle_svg(400, 360, 24, 7) . line_svg(400, 335, 400, 290, 8) . circle_svg(400, 275, 16, 6)
        . path_svg('M230 650 C300 620 500 620 570 650', 6) . circle_svg(250, 620, 30, 7) . circle_svg(550, 620, 30, 7)
        . path_svg('M270 585 L330 585 L330 620 L270 620 Z', 6) . path_svg('M470 585 L530 585 L530 620 L470 620 Z', 6);
}

function render_robot_flowers() {
    return rect_svg(315, 420, 170, 150, 9, ['rx' => 22]) . circle_svg(370, 480, 16, 7) . circle_svg(430, 480, 16, 7)
        . path_svg('M380 530 C400 545 420 545 440 530', 6) . line_svg(400, 420, 400, 365, 8) . circle_svg(400, 350, 22, 7)
        . rect_svg(260, 570, 50, 120, 8, ['rx' => 18]) . rect_svg(490, 570, 50, 120, 8, ['rx' => 18])
        . line_svg(285, 690, 260, 735, 8) . line_svg(515, 690, 540, 735, 8)
        . flower_svg(210, 610, 0.9, 6) . flower_svg(590, 610, 0.9, 6) . path_svg('M250 675 C320 645 480 645 550 675', 5);
}

function render_robot_moonrover() {
    return star_svg(220, 280, 35, 15, 5, 5) . star_svg(610, 300, 35, 15, 5, 5)
        . rect_svg(210, 500, 380, 100, 9, ['rx' => 20]) . rect_svg(430, 430, 105, 85, 8, ['rx' => 18]) . circle_svg(465, 465, 12, 6)
        . line_svg(300, 600, 260, 675, 8) . line_svg(500, 600, 540, 675, 8)
        . circle_svg(250, 675, 45) . circle_svg(550, 675, 45) . circle_svg(250, 675, 16, 7) . circle_svg(550, 675, 16, 7)
        . line_svg(535, 455, 630, 410, 8) . rect_svg(620, 375, 60, 70, 7)
        . path_svg('M180 735 C280 700 520 700 620 735', 5) . circle_svg(240, 705, 12, 5) . circle_svg(560, 705, 12, 5);
}

function render_food($title) {
    $t = strtolower($title);
    if (strpos($t, 'appel') !== false) return render_apple();
    if (strpos($t, 'taart') !== false) return render_cake();
    if (strpos($t, 'pannenkoek') !== false) return render_pancake();
    if (strpos($t, 'ijsje') !== false) return render_icecream();
    if (strpos($t, 'pizza') !== false) return render_pizza();
    if (strpos($t, 'fruit') !== false) return render_fruitbasket();
    if (strpos($t, 'koek') !== false) return render_cookies();
    if (strpos($t, 'soep') !== false) return render_soup();
    if (strpos($t, 'brood') !== false || strpos($t, 'croissant') !== false) return render_bread_basket();
    return render_cupcake();
}

function render_apple() {
    return path_svg('M315 430 C285 350 360 315 400 360 C440 315 515 350 485 430 C465 520 335 520 315 430 Z', 10)
        . line_svg(400, 360, 400, 315, 8) . path_svg('M400 315 C430 285 465 290 485 315 C455 320 430 320 400 315 Z', 7)
        . path_svg('M365 405 C395 430 430 430 455 405', 6) . path_svg('M150 690 C260 660 540 660 650 690', 5)
        . leaf_svg(480, 315, 0.7, 5);
}

function render_cake() {
    return path_svg('M230 520 C245 430 555 430 570 520 Z', 9) . path_svg('M230 520 C300 570 500 570 570 520 Z', 8)
        . path_svg('M270 575 C330 610 470 610 530 575 L510 650 L290 650 Z', 8)
        . path_svg('M285 475 C330 455 390 455 430 475', 6) . path_svg('M370 475 C415 455 470 455 515 475', 6)
        . line_svg(315, 520, 335, 650, 5) . line_svg(400, 520, 400, 650, 5) . line_svg(485, 520, 465, 650, 5)
        . path_svg('M400 365 L400 315', 7) . path_svg('M400 315 C385 330 390 345 400 355 C410 345 415 330 400 315', 5);
}

function render_pancake() {
    return ellipse_svg(400, 520, 170, 70) . ellipse_svg(400, 485, 135, 55)
        . path_svg('M300 430 C330 390 380 410 395 445', 6) . path_svg('M405 430 C430 395 480 400 500 445', 6)
        . path_svg('M260 575 C340 610 460 610 540 575', 6) . circle_svg(350, 465, 12, 5) . circle_svg(450, 465, 12, 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_icecream() {
    return path_svg('M340 470 C310 390 490 390 460 470 Z', 9) . circle_svg(350, 455, 55) . circle_svg(400, 420, 60) . circle_svg(450, 455, 55)
        . path_svg('M345 475 L455 475 L420 660 L380 660 Z', 8) . path_svg('M380 535 L420 535', 5) . path_svg('M370 590 L430 590', 5)
        . star_svg(400, 330, 30, 13, 5, 6) . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_pizza() {
    return polygon_svg('400 330 610 610 190 610', 9)
        . circle_svg(350, 505, 18, 5) . circle_svg(455, 470, 18, 5) . circle_svg(410, 560, 18, 5)
        . path_svg('M260 585 C330 540 470 540 540 585', 6) . line_svg(400, 330, 400, 610, 5) . line_svg(400, 330, 190, 610, 5) . line_svg(400, 330, 610, 610, 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_fruitbasket() {
    return path_svg('M250 500 L550 500 L510 650 L290 650 Z', 9)
        . circle_svg(330, 455, 45) . circle_svg(400, 430, 50) . circle_svg(470, 455, 45) . circle_svg(365, 500, 48) . circle_svg(435, 500, 48)
        . path_svg('M395 385 C415 365 440 370 450 390', 5) . line_svg(285, 500, 300, 650, 5) . line_svg(360, 500, 365, 650, 5) . line_svg(435, 500, 430, 650, 5) . line_svg(515, 500, 500, 650, 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_cookies() {
    return path_svg('M250 500 L550 500 L510 650 L290 650 Z', 9)
        . circle_svg(330, 455, 55) . circle_svg(455, 455, 55) . circle_svg(390, 430, 50)
        . circle_svg(310, 445, 10, 5) . circle_svg(350, 425, 10, 5) . circle_svg(350, 485, 10, 5) . circle_svg(430, 420, 10, 5) . circle_svg(480, 485, 10, 5) . circle_svg(390, 455, 10, 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_soup() {
    return path_svg('M260 455 C275 390 525 390 540 455 Z', 9) . path_svg('M235 470 C260 575 540 575 565 470 L530 650 L270 650 Z', 8)
        . line_svg(470, 420, 560, 330, 8) . circle_svg(570, 315, 22, 7)
        . path_svg('M310 520 C350 495 450 495 490 520', 6) . path_svg('M330 575 C370 600 430 600 470 575', 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_bread_basket() {
    return path_svg('M250 500 L550 500 L510 650 L290 650 Z', 9)
        . path_svg('M300 500 C310 430 390 410 430 500 Z', 8) . path_svg('M420 500 C430 440 500 430 530 500 Z', 8)
        . path_svg('M330 470 C350 450 385 450 405 470', 5) . path_svg('M445 470 C465 450 500 450 520 470', 5)
        . path_svg('M285 500 L300 650', 5) . path_svg('M360 500 L365 650', 5) . path_svg('M435 500 L430 650', 5) . path_svg('M515 500 L500 650', 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_cupcake() {
    return path_svg('M300 500 L500 500 L465 650 L335 650 Z', 9) . path_svg('M300 500 C320 430 480 430 500 500 Z', 9)
        . path_svg('M335 540 L465 540', 5) . path_svg('M325 590 L475 590', 5)
        . star_svg(400, 350, 35, 15, 5, 6) . circle_svg(365, 430, 10, 5) . circle_svg(435, 430, 10, 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_holiday($title) {
    $t = strtolower($title);
    if (strpos($t, 'verjaardag') !== false) return render_birthday_cake();
    if (strpos($t, 'kerst') !== false && strpos($t, 'pakket') === false) return render_christmas_tree();
    if (strpos($t, 'pakket') !== false) return render_gifts();
    if (strpos($t, 'paas') !== false) return render_easter_eggs();
    if (strpos($t, 'hart') !== false) return render_hearts();
    if (strpos($t, 'pompoen') !== false) return render_pumpkin();
    if (strpos($t, 'zomer') !== false) return render_summer_party();
    if (strpos($t, 'herfst') !== false) return render_autumn_leaf();
    if (strpos($t, 'vuurwerk') !== false) return render_fireworks();
    return render_winter_lights();
}

function render_birthday_cake() {
    return path_svg('M230 520 C245 430 555 430 570 520 Z', 9) . path_svg('M230 520 C300 570 500 570 570 520 Z', 8)
        . path_svg('M270 575 C330 610 470 610 530 575 L510 650 L290 650 Z', 8)
        . line_svg(315, 520, 315, 650, 5) . line_svg(400, 520, 400, 650, 5) . line_svg(485, 520, 485, 650, 5)
        . line_svg(315, 430, 315, 375, 6) . line_svg(400, 430, 400, 365, 6) . line_svg(485, 430, 485, 375, 6)
        . path_svg('M315 375 C300 390 305 405 315 415 C325 405 330 390 315 375', 5)
        . path_svg('M400 365 C385 380 390 395 400 405 C410 395 415 380 400 365', 5)
        . path_svg('M485 375 C470 390 475 405 485 415 C495 405 500 390 485 375', 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_christmas_tree() {
    $parts = [line_svg(400, 300, 400, 650, 10)];
    for ($i = 0; $i < 7; $i++) {
        $y = 330 + $i * 45;
        $w = 70 + $i * 24;
        $parts[] = line_svg(400 - $w / 2, $y, 400 + $w / 2, $y, 8);
    }
    $parts[] = circle_svg(400, 250, 16, 7) . star_svg(400, 215, 45, 20, 5, 6) . circle_svg(350, 355, 10, 5) . circle_svg(455, 430, 10, 5) . circle_svg(380, 520, 10, 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
    return implode('', $parts);
}

function render_gifts() {
    return rect_svg(260, 430, 120, 150, 8) . rect_svg(420, 390, 150, 190, 8)
        . line_svg(320, 430, 320, 580, 7) . line_svg(260, 500, 380, 500, 7) . line_svg(495, 390, 495, 580, 7) . line_svg(420, 485, 570, 485, 7)
        . path_svg('M300 430 C310 395 330 395 340 430', 7) . path_svg('M475 390 C490 350 515 350 530 390', 7)
        . path_svg('M250 650 C330 620 470 620 550 650', 6) . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_easter_eggs() {
    return ground_svg(745) . ellipse_svg(300, 530, 65, 95) . ellipse_svg(400, 505, 70, 105) . ellipse_svg(500, 535, 65, 95)
        . path_svg('M260 500 C285 480 315 480 340 500', 5) . path_svg('M360 475 C385 455 415 455 440 475', 5) . path_svg('M460 505 C485 485 515 485 540 505', 5)
        . path_svg('M270 555 C300 535 330 535 355 555', 5) . path_svg('M375 535 C400 515 430 515 455 535', 5) . path_svg('M475 555 C500 535 530 535 555 555', 5)
        . path_svg('M230 650 C320 610 480 610 570 650', 6) . flower_svg(200, 640, 0.8, 6) . flower_svg(600, 640, 0.8, 6);
}

function render_hearts() {
    $hearts = '';
    foreach ([[300, 430, 0.9], [400, 360, 1.1], [500, 430, 0.9], [400, 560, 1.2]] as $h) { $hearts .= heart_svg($h[0], $h[1], $h[2], 8); }
    return $hearts . path_svg('M200 650 C280 610 520 610 600 650', 6) . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_pumpkin() {
    return ground_svg(745) . ellipse_svg(400, 500, 145, 125)
        . path_svg('M400 385 C360 405 345 450 340 500 C390 470 440 470 460 500 C455 450 440 405 400 385 Z', 7)
        . line_svg(400, 385, 410, 345, 8) . path_svg('M315 455 C355 430 390 430 425 455', 6)
        . path_svg('M330 515 C375 545 425 545 470 515', 6) . path_svg('M365 470 C390 490 410 490 435 470', 6)
        . path_svg('M150 690 C260 660 540 660 650 690', 5) . leaf_svg(520, 350, 0.7, 5);
}

function render_summer_party() {
    return path_svg('M250 560 C300 510 350 560 400 510 C450 560 500 510 550 560', 8)
        . line_svg(300, 560, 260, 650, 8) . line_svg(500, 560, 540, 650, 8) . path_svg('M220 690 C310 650 490 650 580 690', 6)
        . path_svg('M230 470 C200 415 235 380 280 410 C315 380 350 415 320 470 C290 500 260 500 230 470 Z', 7) . line_svg(280, 470, 315, 530, 6)
        . path_svg('M480 455 C450 400 485 365 530 395 C565 365 600 400 570 455 C540 485 510 485 480 455 Z', 7) . line_svg(530, 455, 485, 530, 6)
        . sun_svg(400, 260, 55, 6) . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_autumn_leaf() {
    return path_svg('M300 360 C345 335 385 370 370 415 C335 405 315 405 300 360 Z', 8)
        . line_svg(300, 360, 370, 415, 5) . line_svg(330, 390, 300, 430, 4) . line_svg(345, 375, 350, 430, 4)
        . path_svg('M370 415 C390 480 360 545 300 585', 8) . path_svg('M330 505 C300 485 275 490 255 515', 5) . path_svg('M350 540 C320 530 300 540 285 565', 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5) . leaf_svg(520, 360, 0.8, 5);
}

function render_fireworks() {
    $parts = [];
    foreach ([[300, 360], [500, 330], [400, 520]] as $p) {
        for ($i = 0; $i < 10; $i++) { $a = deg2rad($i * 36); $parts[] = line_svg($p[0], $p[1], $p[0] + cos($a) * 70, $p[1] + sin($a) * 70, 5); }
        $parts[] = circle_svg($p[0], $p[1], 12, 5);
    }
    return implode('', $parts) . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_winter_lights() {
    return path_svg('M200 650 C280 610 520 610 600 650', 6) . path_svg('M150 690 C260 660 540 660 650 690', 5)
        . star_svg(400, 260, 55, 24, 6, 7) . snowflake_svg(200, 330, 55, 5) . snowflake_svg(600, 330, 55, 5)
        . path_svg('M250 420 C270 360 310 360 330 420 C310 480 270 480 250 420 Z', 7) . line_svg(290, 420, 290, 560, 5)
        . path_svg('M470 420 C490 360 530 360 550 420 C530 480 490 480 470 420 Z', 7) . line_svg(510, 420, 510, 560, 5)
        . circle_svg(290, 560, 18, 6) . circle_svg(510, 560, 18, 6) . path_svg('M330 560 C380 530 420 530 470 560', 6);
}

function render_sport($title) {
    $t = strtolower($title);
    if (strpos($t, 'tennis') !== false) return render_tennis();
    if (strpos($t, 'zwem') !== false) return render_swimmer();
    if (strpos($t, 'fiets') !== false) return render_bicycle_sport();
    if (strpos($t, 'basket') !== false) return render_basketball();
    if (strpos($t, 'schaats') !== false) return render_skater();
    if (strpos($t, 'skate') !== false) return render_skateboard();
    if (strpos($t, 'yoga') !== false) return render_yoga();
    if (strpos($t, 'slag') !== false) return render_baseball();
    if (strpos($t, 'turn') !== false) return render_gymnastics();
    return render_soccer();
}

function render_soccer() {
    return circle_svg(400, 470, 130, 9)
        . path_svg('M400 390 L430 435 L405 470 L370 470 L345 435 Z', 6)
        . path_svg('M345 435 L285 415 L315 470 L300 535 L370 470', 6)
        . path_svg('M430 435 L490 415 L485 470 L500 535 L430 470', 6)
        . path_svg('M370 470 L340 545 L400 590 L460 545 L430 470', 6)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_tennis() {
    return circle_svg(330, 470, 70, 8) . path_svg('M300 440 C330 420 375 425 400 455 C375 485 330 490 300 470 Z', 5)
        . line_svg(405, 450, 590, 350, 9) . path_svg('M585 340 C630 330 660 350 665 380 C625 380 600 370 585 340 Z', 7)
        . path_svg('M220 610 C300 570 500 570 580 610', 6) . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_swimmer() {
    return wave_svg(650, 6, 120, 680) . wave_svg(705, 5, 150, 650)
        . circle_svg(390, 390, 45) . path_svg('M330 455 C360 520 455 520 485 455 Z', 8)
        . path_svg('M335 460 C270 430 225 455 205 500', 9) . path_svg('M455 455 C520 420 565 455 590 505', 9)
        . line_svg(355, 520, 315, 610, 8) . line_svg(455, 520, 500, 610, 8)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_bicycle_sport() {
    return circle_svg(270, 610, 95) . circle_svg(270, 610, 25, 7) . circle_svg(530, 610, 95) . circle_svg(530, 610, 25, 7)
        . line_svg(270, 610, 400, 470, 8) . line_svg(400, 470, 530, 610, 8) . line_svg(400, 470, 400, 555, 8)
        . line_svg(360, 555, 505, 555, 8) . line_svg(400, 555, 360, 555, 8) . line_svg(400, 555, 505, 555, 8)
        . line_svg(400, 470, 350, 430, 7) . line_svg(400, 470, 455, 430, 7) . path_svg('M330 430 C360 390 410 390 440 430', 8)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_basketball() {
    return circle_svg(400, 470, 130, 9) . line_svg(270, 470, 530, 470, 6) . line_svg(400, 340, 400, 600, 6)
        . path_svg('M310 365 C365 430 435 430 490 365', 6) . path_svg('M310 575 C365 510 435 510 490 575', 6)
        . line_svg(330, 330, 330, 260, 8) . path_svg('M285 260 C315 230 345 230 375 260', 7)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_skater() {
    return wave_svg(650, 6, 120, 680) . wave_svg(705, 5, 150, 650)
        . circle_svg(400, 385, 42) . path_svg('M350 450 C370 545 430 545 450 450 Z', 8)
        . path_svg('M350 470 C290 505 260 555 285 600', 9) . path_svg('M450 470 C510 505 540 555 515 600', 9)
        . line_svg(285, 610, 390, 610, 8) . line_svg(410, 610, 515, 610, 8)
        . line_svg(300, 620, 335, 620, 6) . line_svg(465, 620, 500, 620, 6)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_skateboard() {
    return circle_svg(400, 385, 42) . path_svg('M350 450 C370 545 430 545 450 450 Z', 8)
        . path_svg('M355 470 C300 505 275 555 300 600', 9) . path_svg('M445 470 C500 505 525 555 500 600', 9)
        . rect_svg(250, 620, 300, 28, 8, ['rx' => 14]) . circle_svg(310, 655, 18, 6) . circle_svg(490, 655, 18, 6)
        . star_svg(610, 300, 35, 15, 5, 6) . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_yoga() {
    return circle_svg(400, 330, 38) . path_svg('M400 370 C380 445 420 445 400 520 Z', 8)
        . path_svg('M400 430 C330 405 285 430 260 480', 8) . path_svg('M400 430 C470 405 515 430 540 480', 8)
        . path_svg('M400 520 C350 585 300 620 250 635', 9) . path_svg('M400 520 C450 585 500 620 550 635', 9)
        . path_svg('M220 665 C300 635 500 635 580 665', 6) . star_svg(610, 300, 35, 15, 5, 6);
}

function render_baseball() {
    return line_svg(300, 560, 560, 360, 10) . circle_svg(585, 335, 35, 7) . line_svg(560, 350, 610, 320, 4) . line_svg(560, 370, 610, 350, 4)
        . circle_svg(330, 470, 95, 8) . path_svg('M330 470 C370 430 430 430 470 470', 5) . path_svg('M330 470 C370 510 430 510 470 470', 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_gymnastics() {
    return path_svg('M230 620 L570 620', 10) . path_svg('M300 520 C350 455 450 455 500 520', 8)
        . circle_svg(300, 520, 38, 8) . line_svg(335, 520, 430, 455, 8) . line_svg(430, 455, 500, 520, 8)
        . line_svg(335, 520, 300, 620, 8) . line_svg(500, 520, 570, 620, 8)
        . star_svg(230, 350, 35, 15, 5, 6) . star_svg(570, 350, 35, 15, 5, 6)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_monster($title) {
    $t = strtolower($title);
    if (strpos($t, 'hoorn') !== false) return render_horn_monster();
    if (strpos($t, 'bol') !== false) return render_blob_monster();
    if (strpos($t, 'pluiz') !== false) return render_fuzzy_monster();
    if (strpos($t, 'lach') !== false) return render_flag_monster();
    if (strpos($t, 'robot') !== false) return render_robot_monster();
    if (strpos($t, 'draak') !== false) return render_dragon_monster();
    if (strpos($t, 'slak') !== false) return render_snail_monster();
    if (strpos($t, 'sterren') !== false) return render_night_monster();
    if (strpos($t, 'regenboog') !== false) return render_fin_monster();
    return render_hug_monster();
}

function render_horn_monster() {
    return ground_svg(735) . circle_svg(400, 455, 145)
        . polygon_svg('335 365 300 280 380 365', 8) . polygon_svg('400 345 400 255 440 345', 8) . polygon_svg('465 365 500 280 420 365', 8)
        . circle_svg(355, 440, 14, 7) . circle_svg(445, 440, 14, 7) . path_svg('M335 520 C380 560 420 560 465 520', 8)
        . line_svg(300, 590, 260, 675, 8) . line_svg(500, 590, 540, 675, 8)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_blob_monster() {
    return ground_svg(735) . path_svg('M260 585 C210 520 235 410 310 365 C360 310 455 330 500 380 C570 430 585 530 540 585 C480 625 320 625 260 585 Z', 9)
        . circle_svg(350, 445, 14, 7) . circle_svg(450, 445, 14, 7) . path_svg('M345 515 C385 545 415 545 455 515', 8)
        . line_svg(285, 585, 245, 670, 8) . line_svg(515, 585, 555, 670, 8)
        . circle_svg(290, 405, 10, 5) . circle_svg(510, 405, 10, 5) . circle_svg(400, 365, 10, 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_fuzzy_monster() {
    $fuzz = '';
    for ($i = 0; $i < 16; $i++) { $a = deg2rad($i * 22.5); $fuzz .= line_svg(400 + cos($a) * 135, 470 + sin($a) * 105, 400 + cos($a) * 165, 470 + sin($a) * 130, 5); }
    return ground_svg(735) . $fuzz . circle_svg(400, 470, 130)
        . circle_svg(355, 455, 14, 7) . circle_svg(445, 455, 14, 7) . path_svg('M345 525 C385 565 415 565 455 525', 8)
        . line_svg(300, 575, 260, 665, 8) . line_svg(500, 575, 540, 665, 8)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_flag_monster() {
    return ground_svg(735) . circle_svg(400, 455, 140)
        . circle_svg(355, 430, 14, 7) . circle_svg(445, 430, 14, 7) . path_svg('M335 515 C375 560 425 560 465 515', 8)
        . path_svg('M285 590 L260 675 L330 650 Z', 7) . path_svg('M515 590 L540 675 L470 650 Z', 7)
        . line_svg(260, 360, 260, 285, 7) . path_svg('M260 285 L350 315 L260 345 Z', 7)
        . line_svg(540, 360, 540, 285, 7) . path_svg('M540 285 L630 315 L540 345 Z', 7)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_robot_monster() {
    return ground_svg(735) . rect_svg(300, 390, 200, 160, 9, ['rx' => 22])
        . circle_svg(360, 455, 14, 7) . circle_svg(440, 455, 14, 7) . path_svg('M380 505 C400 520 420 520 440 505', 6)
        . polygon_svg('335 340 300 285 370 345', 7) . polygon_svg('465 345 500 285 430 340', 7)
        . rect_svg(250, 550, 55, 120, 8, ['rx' => 18]) . rect_svg(495, 550, 55, 120, 8, ['rx' => 18])
        . line_svg(275, 670, 250, 725, 8) . line_svg(525, 670, 550, 725, 8)
        . circle_svg(330, 410, 8, 5) . circle_svg(470, 410, 8, 5) . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_dragon_monster() {
    return ground_svg(735) . path_svg('M230 575 C270 500 400 485 510 530 C555 550 555 600 515 625 C430 650 300 650 230 575 Z', 9)
        . path_svg('M510 530 C570 470 625 475 650 520 C605 515 570 535 540 570 Z', 8)
        . polygon_svg('640 505 690 475 655 545', 7) . path_svg('M250 555 C190 510 155 525 130 570', 8)
        . line_svg(315, 625, 295, 705, 8) . line_svg(455, 625, 475, 705, 8)
        . circle_svg(560, 510, 8, 5) . path_svg('M565 535 C580 545 595 545 605 535', 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_snail_monster() {
    return ground_svg(735) . path_svg('M250 585 C210 520 260 455 345 455 C430 455 480 520 440 585 C390 625 300 625 250 585 Z', 9)
        . path_svg('M300 560 C320 500 385 480 430 520 C400 545 350 545 300 560 Z', 7)
        . circle_svg(370, 515, 12, 6) . path_svg('M390 535 C410 545 430 545 445 535', 5)
        . path_svg('M250 585 C210 640 230 680 280 690', 8) . path_svg('M440 585 C480 640 460 680 410 690', 8)
        . line_svg(345, 455, 315, 395, 7) . line_svg(375, 455, 395, 395, 7) . circle_svg(315, 385, 8, 5) . circle_svg(395, 385, 8, 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_night_monster() {
    return ground_svg(735) . circle_svg(400, 455, 135)
        . circle_svg(355, 430, 14, 7) . circle_svg(445, 430, 14, 7) . path_svg('M340 515 C380 555 420 555 460 515', 8)
        . star_svg(300, 330, 35, 15, 5, 6) . star_svg(500, 330, 35, 15, 5, 6) . star_svg(400, 300, 25, 10, 5, 6)
        . path_svg('M300 590 L270 675 L340 650 Z', 7) . path_svg('M500 590 L530 675 L460 650 Z', 7)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_fin_monster() {
    return wave_svg(650, 6, 120, 680) . wave_svg(705, 5, 150, 650)
        . path_svg('M230 560 C270 480 420 470 535 520 C580 540 585 590 540 620 C440 650 300 635 230 560 Z', 9)
        . polygon_svg('540 520 620 470 610 560', 7) . path_svg('M250 545 C190 500 150 515 125 555', 8)
        . line_svg(315, 620, 295, 705, 8) . line_svg(455, 620, 475, 705, 8)
        . circle_svg(500, 505, 8, 5) . path_svg('M510 535 C525 545 540 545 550 535', 5)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function render_hug_monster() {
    return ground_svg(735) . heart_svg(400, 455, 0.8, 8)
        . circle_svg(340, 420, 14, 7) . circle_svg(460, 420, 14, 7) . path_svg('M345 520 C385 560 415 560 455 520', 8)
        . path_svg('M270 545 C210 500 170 530 185 585', 8) . path_svg('M530 545 C590 500 630 530 615 585', 8)
        . line_svg(230, 610, 210, 680, 8) . line_svg(570, 610, 590, 680, 8)
        . path_svg('M150 690 C260 660 540 660 650 690', 5);
}

function heart_svg($x, $y, $s, $w = 8) {
    $d = 'M ' . $x . ' ' . ($y + 110 * $s) . ' C ' . ($x - 80 * $s) . ' ' . ($y + 20 * $s) . ' ' . ($x - 55 * $s) . ' ' . ($y - 45 * $s) . ' ' . $x . ' ' . $y . ' C ' . ($x + 55 * $s) . ' ' . ($y - 45 * $s) . ' ' . ($x + 80 * $s) . ' ' . ($y + 20 * $s) . ' ' . $x . ' ' . ($y + 110 * $s) . ' Z';
    return path_svg($d, $w);
}

function slugify($value) {
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
    $slug = preg_replace('/^-+|-+$/i', '', $slug);
    return $slug === '' ? 'kleurplaat' : $slug;
}

$manifest = [];
$seen_slugs = [];
foreach ($templates as $item) {
    $slug = slugify($item['title']);
    $base_slug = $slug;
    $counter = 2;
    while (isset($seen_slugs[$slug])) {
        $slug = $base_slug . '-' . $counter++;
    }
    $seen_slugs[$slug] = true;

    $manifest[] = [
        'slug' => $slug,
        'type' => $item['type'],
        'title' => $item['title'],
        'category' => $item['category'],
        'alt' => 'Zwarte lijntekening van ' . $item['title'] . ' als kinderkleurplaat',
        'fact' => $item['fact'],
    ];

    $drawing = render_category($item['type'], $item['title']);
    $svg = render_svg($drawing);
    file_put_contents($target_dir . '/' . $slug . '.svg', $svg, LOCK_EX);
}

foreach ((array) glob($target_dir . '/*') as $file) {
    if (!is_file($file)) {
        continue;
    }
    $name = basename($file);
    if ($name === 'manifest.json' || pathinfo($file, PATHINFO_EXTENSION) === 'svg') {
        unlink($file);
    }
}

foreach ($manifest as $item) {
    $svg = render_svg(render_category($item['type'] ?? '', $item['title']));
    file_put_contents($target_dir . '/' . $item['slug'] . '.svg', $svg, LOCK_EX);
}

file_put_contents($target_dir . '/manifest.json', json_encode(['images' => $manifest], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);

echo 'Generated ' . count($manifest) . ' SVGs and manifest.json' . PHP_EOL;
