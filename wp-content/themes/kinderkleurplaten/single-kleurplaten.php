<?php
/**
 * The template for displaying single kleurplaten posts
 */

function kk_get_category_factoids($category_name) {
	$category_name = strtolower(trim($category_name));
	
	$factoids = array(
		'dieren' => array(
			'Zoogdieren hebben een warm lichaam en geven melk aan hun jongen.',
			'Veel dieren hebben camouflagekleuren om zich te verbergen voor roofdieren.',
			'Insecten zijn de grootste dierengroep op aarde - er zijn miljoenen soorten!',
			'Sommige dieren slapen de hele winter - dat heet winterslaap of wintersluimeren.'
		),
		'dinosaurussen' => array(
			'Dinosaurussen leefden miljoenen jaren geleden, lang voordat mensen op aarde kwamen.',
			'Niet alle dinosaurussen waren grote reuzen - sommige waren zo klein als een kip!',
			'Stegosaurus had scherpe botplaten op zijn rug om zich te beschermen.',
			'Veel dinosaurussen hadden veren en waren verwant aan moderne vogels.'
		),
		'boerderijdieren' => array(
			'Koeien kunnen elkaar herkennen aan hun eigen unieke beltoon.',
			'Kuikens komen uit eieren en kunnen al binnen een uur lopen.',
			'Schapen hebben een dikke vacht van wol die we kunnen gebruiken voor kleding.',
			'Varkens zijn heel slim en houden van knuffelen en spelen.'
		),
		'voertuigen' => array(
			'Een vrachtwagen kan wel 40 ton lading meenemen!',
			'Helikopters kunnen zowel vooruit als achteruit vliegen.',
			'Een ruimtetuig moet een enorme snelheid bereiken om van de aarde los te komen.',
			'Trams rijden op rails en zijn een milieuvriendelijke manier van vervoer.'
		),
		'feestdagen' => array(
			'Sinterklaas komt elk jaar in november naar Nederland met zijn Pieten.',
			'Paaseieren werden oorspronkelijk versierd met natuurlijke kleurstoffen.',
			'Kerstmis wordt gevierd ter herinnering aan de geboorte van Jezus Christus.',
			'Koningsdag is de nationale feestdag van Nederland op 27 april.'
		),
		'sprookjes en fantasy' => array(
			'Eenhoorns worden vaak afgebeeld als witte paarden met een gouden hoorn.',
			'Draakjes in sprookjes kunnen vuur spugen, maar echte draken bestaan natuurlijk niet!',
			'Feeën worden vaak geassocieerd met bloemen, bosjes en magische plekjes.',
			'Ridders droegen harnassen om zichzelf te beschermen tijdens gevechten.'
		),
		'bloemen en natuur' => array(
			'Bijen helpen bloemen te bestuiven - zo kunnen er nieuwe bloemen groeien.',
			'Zonnebloemen draaien altijd langzaam mee met de zon.',
			'Rozen hebben doorns om zichzelf te beschermen tegen dieren.',
			'Paddenstoelen zijn geen planten, maar een eigen soort organismen!'
		),
		'ruimte' => array(
			'Astronauten zweven in het ISS omdat ze gewichtloos zijn.',
			'De maan heeft geen atmosfeer, dus er is geen lucht om te ademen.',
			'Onze zon is eigenlijk een gigantische ster.',
			'Jupiter is de grootste planeet in ons zonnestelsel.'
		),
		'sport en bewegen' => array(
			'Bewegen maakt je lichaam sterk en je humeur beter!',
			'Zwemmers gebruiken bijna alle spieren in hun lichaam.',
			'Balans is een belangrijke vaardigheid die je kunt trainen.',
			'Elke vorm van beweging telt - zelfs springen en dansen!'
		),
		'gevoelens en emoties' => array(
			'Het is helemaal normaal om verschillende gevoelens te hebben.',
			'Een lach is besmettelijk - als iemand lacht, lach je vaak mee!',
			'Diepe ademhaling kan je helpen als je boos of zenuwachtig bent.',
			'Emoties helpen ons om te begrijpen wat we nodig hebben.'
		),
		'seizoenen' => array(
			'In de lente bloeien veel bloemen en komen dieren uit hun winterslaap.',
			'In de zomer is het het warmst en kunnen we veel buiten spelen.',
			'In de herfst vallen bladeren van de bomen en veranderen ze van kleur.',
			'In de winter kan het sneeuwen en slapen sommige dieren in hun hol.'
		),
		'voedsel' => array(
			'Groenten en fruit geven ons vitamines om gezond te blijven.',
			'Water is het allerbelangrijkste drankje voor ons lichaam.',
			'Bomen geven ons fruit zoals appels, peren en kersen.',
			'Veel voedingsmiddelen groeien in de grond of aan planten.'
		),
		'muziek en geluid' => array(
			'Geluid reist in golven door de lucht naar onze oren.',
			'Muziek kan ons blij, rustig of energiek maken.',
			'Instrumenten maken geluid op verschillende manieren - door trillen.',
			'We kunnen ritme voelen in onze voeten en lichaam!'
		),
		'kleuren en vormen' => array(
			'Rood, geel en blauw zijn de drie primaire kleuren.',
			'Door kleuren te mengen kun je alle andere kleuren maken.',
			'Vormen zijn overal om ons heen - cirkels, vierkanten en driehoeken!',
			'Kleuren kunnen ons verschillende gevoelens geven.'
		)
	);
	
	// Try to match category name (case-insensitive, partial match)
	foreach ($factoids as $key => $facts) {
		if (strpos($category_name, $key) !== false || strpos($key, $category_name) !== false) {
			return $facts;
		}
	}
	
	// Default fallback facts
	return array(
		'Kleuren helpt kinderen om hun creativiteit te ontwikkelen.',
		'Het inkleuren van afbeeldingen verbetert de fijne motoriek.',
		'Kleurplaten kunnen helpen bij het leren van concentratie.',
		'Samen kleuren is een leuke manier om tijd door te brengen!'
	);
}

get_header(); ?>

<div class="kk-single-wrapper">
	<?php while (have_posts()) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class('kk-single-article'); ?>>

			<header class="kk-single-header">
				<h1 class="kk-single-title"><?php the_title(); ?></h1>
			</header>

			<div class="kk-single-image-wrapper">
				<?php
				$featured_image_url = '';
				if (has_post_thumbnail()) {
					$thumbnail_id = get_post_thumbnail_id();
					$image = wp_get_attachment_image_src($thumbnail_id, 'full');
					if (!empty($image[0])) {
						$featured_image_url = $image[0];
						echo wp_get_attachment_image(
							$thumbnail_id,
							'full',
							false,
							array(
								'class' => 'kk-single-image',
								'alt'   => esc_attr(get_the_title()),
								'style' => 'width: 100%; height: auto; max-width: 100%; aspect-ratio: 210/297; object-fit: contain; display: block; margin: 0 auto;'
							)
						);
					}
				}

				if (empty($featured_image_url)) {
					$featured_image_url = get_post_meta(get_the_ID(), '_kk_image_url', true);
					if (!empty($featured_image_url)) {
						echo '<img src="' . esc_url($featured_image_url) . '" alt="' . esc_attr(get_the_title()) . '" class="kk-single-image" style="width: 100%; height: auto; max-width: 100%; aspect-ratio: 210/297; object-fit: contain; display: block; margin: 0 auto;" />';
					}
				}
				?>
			</div>

			<div class="kk-single-print">
				<button type="button" class="kk-print-button" onclick="window.print()">
					<?php echo esc_html__('Printer deze kleurplaat (A4)', 'kinderkleurplaten'); ?>
				</button>
			</div>

			<div class="kk-single-content">
				<?php the_content(); ?>
			</div>

			<?php
			$categories = get_the_terms(get_the_ID(), 'kleurplaat_categorie');
			$themas     = get_the_terms(get_the_ID(), 'kleurplaat_thema');
			?>

			<?php if (!empty($categories) && !is_wp_error($categories)) : ?>
				<div class="kk-single-badges kk-single-categories">
					<span class="kk-badges-label"><?php echo esc_html__('Categorieën:', 'kinderkleurplaten'); ?></span>
					<?php foreach ($categories as $category) : ?>
						<a href="<?php echo esc_url(get_term_link($category)); ?>" class="kk-badge kk-category-badge"><?php echo esc_html($category->name); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if (!empty($themas) && !is_wp_error($themas)) : ?>
				<div class="kk-single-badges kk-single-themas">
					<span class="kk-badges-label"><?php echo esc_html__('Thema\'s:', 'kinderkleurplaten'); ?></span>
					<?php foreach ($themas as $thema) : ?>
						<a href="<?php echo esc_url(get_term_link($thema)); ?>" class="kk-badge kk-thema-badge"><?php echo esc_html($thema->name); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<section class="kk-single-section kk-single-section-why">
				<h2 class="kk-single-section-title"><?php echo esc_html__('Waarom deze kleurplaat leuk is voor kinderen', 'kinderkleurplaten'); ?></h2>
				<p class="kk-single-section-content"><?php echo esc_html__('Deze kleurplaat helpt kinderen bij het ontwikkelen van fijne motoriek, kleurherkenning, concentratie en creativiteit. Ideaal om thuis of in de klas te gebruiken!', 'kinderkleurplaten'); ?></p>
			</section>

			<?php
			$kk_weetjes = get_post_meta(get_the_ID(), 'kk_weetjes', true);
			$kk_custom_weetjes = array();
			if (!empty($kk_weetjes) && is_string($kk_weetjes)) {
				$kk_custom_weetjes = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $kk_weetjes)), 'strlen');
				$kk_custom_weetjes = array_values($kk_custom_weetjes);
			}
			?>
			<section class="kk-single-section kk-single-section-facts">
				<h2 class="kk-single-section-title"><?php echo esc_html__('Leuke weetjes & Feitjes', 'kinderkleurplaten'); ?></h2>
				<?php if (!empty($kk_custom_weetjes)) : ?>
					<p class="kk-single-section-content"><?php echo esc_html__('Over het onderwerp van deze kleurplaat kunt u met kinderen leuke gesprekjes voeren terwijl ze aan deze kleurplaat werken:', 'kinderkleurplaten'); ?></p>
					<ul class="kk-single-section-content kk-factoids-list">
						<?php foreach ($kk_custom_weetjes as $kk_weetje) : ?>
							<li><?php echo esc_html($kk_weetje); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php elseif (!empty($categories) && !is_wp_error($categories)) : ?>
					<?php
					$first_category = reset($categories);
					$factoids = kk_get_category_factoids($first_category->name);
					?>
					<p class="kk-single-section-content"><?php printf(esc_html__('Over het onderwerp "%s" kunt u met kinderen leuke gesprekken voeren terwijl ze aan deze kleurplaat werken:', 'kinderkleurplaten'), esc_html($first_category->name)); ?></p>
					<?php if (!empty($factoids)) : ?>
						<ul class="kk-single-section-content kk-factoids-list">
							<?php foreach ($factoids as $factoid) : ?>
								<li><?php echo esc_html($factoid); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="kk-single-section-content"><?php echo esc_html__('Kleurplaten zijn een geweldige manier om met kinderen te praten over kleuren, vormen en creativiteit.', 'kinderkleurplaten'); ?></p>
					<?php endif; ?>
				<?php else : ?>
					<p class="kk-single-section-content"><?php echo esc_html__('Terwijl kinderen kleuren, kunt u leuke gesprekjes voeren over het thema van deze kleurplaat.', 'kinderkleurplaten'); ?></p>
					<p class="kk-single-section-content"><?php echo esc_html__('Kleurplaten zijn een geweldige manier om met kinderen te praten over kleuren, vormen en creativiteit.', 'kinderkleurplaten'); ?></p>
				<?php endif; ?>
			</section>

			<section class="kk-single-section kk-single-section-tips">
				<h2 class="kk-single-section-title"><?php echo esc_html__('Print- en Kleurtips voor Ouders & Leraren', 'kinderkleurplaten'); ?></h2>
				<ul class="kk-single-section-content">
					<li><?php echo esc_html__('Print op stevig A4-papier voor het beste resultaat.', 'kinderkleurplaten'); ?></li>
					<li><?php echo esc_html__('Gebruik kleurpotloden, viltstiften of waskrijt - alles mag!', 'kinderkleurplaten'); ?></li>
					<li><?php echo esc_html__('Laat kinderen zelf kleuren, zonder druk - het gaat om plezier.', 'kinderkleurplaten'); ?></li>
					<li><?php echo esc_html__('Kleurplaten zijn ook geweldig voor ontspanning en mindfulness.', 'kinderkleurplaten'); ?></li>
				</ul>
			</section>

			<?php
			$schema_image = $featured_image_url;
			if (empty($schema_image)) {
				$schema_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
			}
			?>
			<script type="application/ld+json">
			{
				"@context": "https://schema.org",
				"@graph": [
					{
						"@type": "ImageObject",
						"url": "<?php echo esc_url($schema_image); ?>",
						"contentUrl": "<?php echo esc_url($schema_image); ?>",
						"caption": "<?php echo esc_attr(get_the_title()); ?>",
						"width": "210mm",
						"height": "297mm"
					},
					{
						"@type": "CreativeWork",
						"name": "<?php echo esc_attr(get_the_title()); ?>",
						"description": "<?php echo esc_attr(wp_strip_all_tags(get_the_excerpt())); ?>",
						"image": "<?php echo esc_url($schema_image); ?>",
						"author": {
							"@type": "Organization",
							"name": "<?php echo esc_attr(get_bloginfo('name')); ?>"
						}
					},
					{
						"@type": "HowTo",
						"name": "<?php echo esc_attr(__('Kleurplaat printen en kleuren', 'kinderkleurplaten')); ?>",
						"description": "<?php echo esc_attr(__('Print deze kleurplaat op A4-papier en kleur hem in met kleurpotloden.', 'kinderkleurplaten')); ?>",
						"step": [
							{
								"@type": "HowToStep",
								"name": "<?php echo esc_attr(__('Printen', 'kinderkleurplaten')); ?>",
								"text": "<?php echo esc_attr(__('Klik op de printknop of druk op Ctrl+P om deze kleurplaat af te drukken.', 'kinderkleurplaten')); ?>"
							},
							{
								"@type": "HowToStep",
								"name": "<?php echo esc_attr(__('Kleuren', 'kinderkleurplaten')); ?>",
								"text": "<?php echo esc_attr(__('Kleur de afbeelding in met kleurpotloden, viltstiften of waskrijt.', 'kinderkleurplaten')); ?>"
							}
						]
					}
				]
			}
			</script>

		</article>
	<?php endwhile; ?>
</div>

<?php get_footer(); ?>
