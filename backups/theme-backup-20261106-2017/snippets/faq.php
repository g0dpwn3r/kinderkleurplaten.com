<?php
/**
 * Veelgestelde vragen pagina-inhoud voor kinderkleurplaten.com
 *
 * Getoond op de FAQ-pagina. Bevat een gestructureerde lijst van vragen
 * en antwoorden met bijbehorende FAQPage JSON-LD schema markup.
 *
 * @package Kinderkleurplaten
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kk_faq_items = array(
	array(
		'question' => __( 'Hoe gebruik ik een kleurplaat?', 'kinderkleurplaten' ),
		'answer'   => __( 'Open de gewenste kleurplaat door op de afbeelding te klikken. Rechtsbovenin vind je een "Print kleurplaat"-knop, die de klaar-voor-de-printer versie opent. Wil je de pagina later op papier kleuren? Gebruik de "Download kleurplaat"-link om een PDF op te slaan. Er is geen account nodig: kies, print en begin direct.', 'kinderkleurplaten' ),
	),
	array(
		'question' => __( 'Voor welke leeftijden zijn deze kleurplaten geschikt?', 'kinderkleurplaten' ),
		'answer'   => __( 'Het leeftijdbereik is per thema afgestemd. Kleuren- en vormenthema beginnen bij twee tot zes jaar; dieren, boerderijdieren, voertuigen, seizoenen en voedsel beginnen bij drie tot zeven; feestdagen en emoties beginnen bij drie tot acht; dinosaurussen, fantasy, bloemen en natuur, ruimte, sport, muziek en geluid beginnen bij vier tot acht. Bij elke categorie staat het aanbevolen leeftijdbereik vermeld, zodat je makkelijk de juiste pagina vindt.', 'kinderkleurplaten' ),
	),
	array(
		'question' => __( 'Zijn de kleurplaten veilig en privacyvriendelijk?', 'kinderkleurplaten' ),
		'answer'   => __( 'Ja. Kinderkleurplaten.com werkt zonder registratie, zonder marketingpop-ups en zonder traceercookies. Er worden geen locatiegevens of andere persoonsgegevens van kinderen vastgelegd, en er wordt geen gedraaide reclame op basis van surfgedrag getoond. De site blijft lek en kindveilig.', 'kinderkleurplaten' ),
	),
	array(
		'question' => __( 'Mag ik de kleurplaten ook gebruiken in de klas?', 'kinderkleurplaten' ),
		'answer'   => __( 'De kleurplaten zijn gratis te gebruiken voor onderwijsdoeleinden, waaronder groepsactiviteiten en kleuren-marathons op school. Een attribuatie aan kinderkleurplaten.com wordt gewaardeerd, maar is niet verplicht bij niet-commercieel gebruik.', 'kinderkleurplaten' ),
	),
	array(
		'question' => __( 'Welke thema en categorie zijn er allemaal?', 'kinderkleurplaten' ),
		'answer'   => __( 'Wij bieden thema aan als dieren, dinosaurussen, boerderijdieren, voertuigen, feestdagen, sprookjes en fantasy, bloemen en natuur, ruimte, sport en bewegen, gevoelens en emoties, seizoenen, voedsel, muziek en geluid en kleuren en vormen. Klik op "Bekijk alle kleurplaten" om de volledige lijst te verkennen.', 'kinderkleurplaten' ),
	),
	array(
		'question' => __( 'Kan ik een kleurplaat aanpassen of een eigen ontwerp laten maken?', 'kinderkleurplaten' ),
		'answer'   => __( 'De beschikbare kleurplaten zijn vast en worden niet op maat gemaakt. Mocht je een thema zoeken die wij nog niet aanbieden, kun je contact opnemen. Wij evalueren aanvragen en kunnen nieuwe ontwerpen in onze planning opnemen.', 'kinderkleurplaten' ),
	),
);

$kk_faq_schema = array(
	'@context'  => 'https://schema.org',
	'@type'     => 'FAQPage',
	'mainEntity' => array(),
);
foreach ( $kk_faq_items as $item ) {
	$kk_faq_schema['mainEntity'][] = array(
		'@type'            => 'Question',
		'name'             => $item['question'],
		'acceptedAnswer'   => array(
			'@type' => 'Answer',
			'text'  => $item['answer'],
		),
	);
}
?>
<main class="kk-faq-page" aria-labelledby="kk-faq-page-title">
	<div class="kk-faq-page__inner">
		<h1 id="kk-faq-page-title"><?php esc_html_e( 'Veelgestelde vragen', 'kinderkleurplaten' ); ?></h1>
		<p class="kk-faq-page__intro">
			<?php esc_html_e( 'Praktische vragen over gebruik, leeftijden, veiligheid, onderwijs en thema.', 'kinderkleurplaten' ); ?>
		</p>

		<dl class="kk-faq-list">
			<?php foreach ( $kk_faq_items as $item ) : ?>
				<dt class="kk-faq-item kk-faq-question">
					<?php echo esc_html( $item['question'] ); ?>
				</dt>
				<dd class="kk-faq-answer">
					<?php echo esc_html( $item['answer'] ); ?>
				</dd>
			<?php endforeach; ?>
		</dl>

		<div class="kk-faq-page__cta">
			<h2 class="kk-faq-page__cta-title"><?php esc_html_e( 'Nog een vraag?', 'kinderkleurplaten' ); ?></h2>
			<p class="kk-faq-page__cta-text">
				<?php esc_html_e( 'Neem gerust contact op. Wij reageren snel.', 'kinderkleurplaten' ); ?>
			</p>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="kk-button kk-button--print">
				<?php esc_html_e( 'Contact opnemen', 'kinderkleurplaten' ); ?>
			</a>
		</div>
	</div>
</main>

<script type="application/ld+json">
	<?php
	$kk_faq_json_ld = wp_json_encode(
		$kk_faq_schema,
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
	);
	if ( false !== $kk_faq_json_ld ) {
		echo $kk_faq_json_ld;
	}
	?>
</script>
