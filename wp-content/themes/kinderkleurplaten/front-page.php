<?php
/**
 * Front-page template.
 *
 * Modern, mobile-first homepage for Kinderkleurplaten.com
 * Optimized for AdSense approval with rich, original Dutch content.
 *
 * @package kinderkleurplaten
 */

if (!function_exists('kk_home_category_url')) {
	/**
	 * Resolve a human-friendly category display name to its taxonomy archive URL.
	 */
	function kk_home_category_url($name) {
		if (empty($name)) {
			return home_url('/');
		}

		$mapping = array(
			'dieren'                            => array('dieren', 'Dieren'),
			'feestdagen & seizoenen'            => array('feestdagen-seizoenen', 'Feestdagen & Seizoenen', 'Feestdagen en Seizoenen', 'Seizoenen'),
			'voertuigen & bouw'                 => array('voertuigen-bouw', 'Voertuigen & Bouw', 'Voertuigen en Bouw', 'Voertuigen', 'Bouw'),
			'fantasie & sprookjes'              => array('fantasie-sprookjes', 'Fantasie & Sprookjes', 'Fantasie en Sprookjes', 'Fantasie', 'Sprookjes'),
			'educatief & cijfers/letters'       => array('educatief-cijfers-letters', 'Educatief & Cijfers/Letters', 'Educatief en Cijfers/Letters', 'Educatief', 'Cijfers/Letters', 'Cijfers en Letters', 'Letters en Cijfers'),
			'bekende helden'                    => array('bekende-helden', 'Bekende Helden'),
			'prinsessen'                        => array('prinsessen', 'Prinsessen'),
			'dinosaurussen'                     => array('dinosaurussen', 'Dinosaurussen'),
		);

		$taxonomy = 'kleurplaat_categorie';
		$name_lower = strtolower(trim($name));

		if (isset($mapping[$name_lower])) {
			foreach ($mapping[$name_lower] as $candidate) {
				$term = get_term_by('slug', sanitize_title($candidate), $taxonomy);
				if (!$term) {
					$term = get_term_by('name', $candidate, $taxonomy);
				}
				if ($term && !is_wp_error($term)) {
					return get_term_link($term);
				}
			}
		}

		$slug_candidate = sanitize_title($name);
		$term = get_term_by('slug', $slug_candidate, $taxonomy);
		if ($term && !is_wp_error($term)) {
			return get_term_link($term);
		}

		$term = get_term_by('name', $name, $taxonomy);
		if ($term && !is_wp_error($term)) {
			return get_term_link($term);
		}

		$terms_all = get_terms(array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'all',
		));

		if (!is_wp_error($terms_all) && !empty($terms_all)) {
			foreach ($terms_all as $t) {
				if (strtolower($t->name) === $name_lower || strtolower($t->slug) === $name_lower) {
					return get_term_link($t);
				}
				$t_name_lower = strtolower($t->name);
				$t_slug_lower = strtolower($t->slug);
				if (strpos($t_name_lower, $name_lower) !== false || strpos($name_lower, $t_name_lower) !== false ||
					strpos($t_slug_lower, $name_lower) !== false || strpos($name_lower, $t_slug_lower) !== false) {
					return get_term_link($t);
				}
			}
		}

		return home_url('/?s=' . rawurlencode($name));
	}
}

if (!function_exists('kk_get_all_categories_with_links')) {
	function kk_get_all_categories_with_links() {
		$taxonomy = 'kleurplaat_categorie';
		$terms = get_terms(array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		));

		if (is_wp_error($terms) || empty($terms)) {
			return array();
		}

		$result = array();
		foreach ($terms as $term) {
			$result[] = array(
				'term' => $term,
				'name' => $term->name,
				'slug' => $term->slug,
				'url'  => get_term_link($term),
				'id'   => $term->term_id,
				'count'=> $term->count,
			);
		}
		return $result;
	}
}

get_header();
?>

<main id="primary" class="site-main">
	<div class="container">

		<?php if (is_front_page() && !is_home()) : ?>
			<?php if (have_posts()) : ?>
				<?php while (have_posts()) : the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
					</article>
				<?php endwhile; ?>
			<?php endif; ?>
		<?php else : ?>

			<?php
			/* ============================================================
			   SECTION 1: HERO
			   ============================================================ */
			?>
			<section class="kk-hero" aria-label="Kleurplaten hero">
				<div class="kk-hero__bg"></div>
				<div class="kk-hero__content container">
					<p class="kk-hero__kicker">Welkom bij Kinderkleurplaten.com</p>
					<h1 class="kk-hero__title">
						Gratis kleurplaten voor kinderen
						<span class="kk-hero__accent">direct te printen in A4-formaat</span>
					</h1>
					<p class="kk-hero__subtitle">Duizenden gratis kleurplaten om thuis, op school of tijdens een verjaardag te printen. Geen account nodig, geen verborgen kosten, 100% kindvriendelijk.</p>
					<div class="kk-hero__actions">
						<a href="<?php echo esc_url(home_url('/kleurplaten')); ?>" class="kk-btn kk-btn--primary">Bekijk alle kleurplaten</a>
						<a href="<?php echo esc_url(home_url('/kleurplaat-categorie')); ?>" class="kk-btn kk-btn--secondary">Populaire categorieën</a>
					</div>
				</div>
			</section>

			<?php
			/* ============================================================
			   SECTION 2: TRUST / VALUE
			   ============================================================ */
			?>
			<section class="kk-trust" aria-label="Waarom kiezen voor Kinderkleurplaten">
				<div class="container">
					<div class="kk-trust__header">
						<h2 class="kk-trust__title">Waarom kiezen voor Kinderkleurplaten.com?</h2>
						<p class="kk-trust__subtitle">Alles wat ouders, leerkrachten en kinderen nodig hebben voor urenlang kleurplezier.</p>
					</div>
					<div class="kk-trust__grid">
						<div class="kk-trust-card">
							<div class="kk-trust-card__icon" aria-hidden="true">&#127919;</div>
							<h3 class="kk-trust-card__title">100% Gratis</h3>
							<p class="kk-trust-card__text">Alle kleurplaten op onze site zijn volledig gratis te downloaden en te printen. Geen abonnement, geen verrassingen.</p>
						</div>
						<div class="kk-trust-card">
							<div class="kk-trust-card__icon" aria-hidden="true">&#128471;</div>
							<h3 class="kk-trust-card__title">Makkelijk te printen</h3>
							<p class="kk-trust-card__text">Elke kleurplaat past perfect op A4-formaat en blijft scherp, ook bij vergroting. Druk af en begin met kleuren.</p>
						</div>
						<div class="kk-trust-card">
							<div class="kk-trust-card__icon" aria-hidden="true">&#128512;</div>
							<h3 class="kk-trust-card__title">Geschikt voor alle leeftijden</h3>
							<p class="kk-trust-card__text">Van peuters tot basisschoolkinderen: we hebben kleurplaten voor elk niveau en elk interessesgebied.</p>
						</div>
						<div class="kk-trust-card">
							<div class="kk-trust-card__icon" aria-hidden="true">&#128197;</div>
							<h3 class="kk-trust-card__title">Nieuwe kleurplaten iedere week</h3>
							<p class="kk-trust-card__text">Onze collectie groeit continu met nieuwe thema's en seizoensgebonden tekeningen. Kom regelmatig kijken.</p>
						</div>
					</div>
				</div>
			</section>

			<?php
			/* ============================================================
			   SECTION 3: POPULAR CATEGORIES GRID
			   ============================================================ */
			?>
			<section class="kk-categories" aria-label="Populaire kleurplaat-categorieën">
				<div class="container">
					<div class="kk-categories__header">
						<h2 class="kk-categories__title">Populaire categorieën</h2>
						<p class="kk-categories__subtitle">Kies een thema en ontdek alle kleurplaten binnen dat thema.</p>
						<a href="<?php echo esc_url(home_url('/kleurplaat-categorie')); ?>" class="kk-categories__view-all">Alle categorieën bekijken <span aria-hidden="true">&#8594;</span></a>
					</div>
					<div class="kk-categories__grid">
						<?php
						$categories = get_terms(array(
							'taxonomy'   => 'kleurplaat_categorie',
							'hide_empty' => true,
							'orderby'    => 'count',
							'order'      => 'DESC',
							'number'     => 8,
						));
						$category_icons = array(
							'Dieren'                => '&#128054;',
							'Prinsessen'            => '&#128096;',
							'Dinosaurussen'         => '&#128003;',
							'Voertuigen'            => '&#128689;',
							'Feestdagen'            => '&#127873;',
							'Seizoenen'             => '&#127784;',
							'Educatief'             => '&#128215;',
							'Fantasie'              => '&#128009;',
							'Sprookjes'             => '&#128008;',
							'Bekende Helden'        => '&#128127;',
							'Gevoelens & Emoties'   => '&#128522;',
							'Muziek & Geluid'       => '&#127911;',
							'Natuur & Planten'      => '&#127794;',
							'Sport & Bewegen'       => '&#2697;',
							'Voedsel'               => '&#127828;',
							'Ruimte'                => '&#128640;',
						);

						if (empty($categories) || is_wp_error($categories)) {
							echo '<p class="kk-categories__empty">We zijn bezig met het toevoegen van nieuwe kleurplaten in deze categorieën. Kom binnenkort terug!</p>';
						} else {
							foreach ($categories as $cat) :
								$icon = isset($category_icons[$cat->name]) ? $category_icons[$cat->name] : '&#127753;';
								$count = isset($cat->count) && $cat->count > 0 ? (int) $cat->count : 0;
								$count_text = $count > 0 ? sprintf(_n('%d kleurplaat', '%d kleurplaten', $count, 'kinderkleurplaten'), $count) : 'Binnenkort beschikbaar';
							?>
							<a href="<?php echo esc_url(get_term_link($cat)); ?>" class="kk-category-card">
								<div class="kk-category-card__icon"><?php echo $icon; // HTML entities from trusted array ?></div>
								<h3 class="kk-category-card__title"><?php echo esc_html($cat->name); ?></h3>
								<p class="kk-category-card__count"><?php echo esc_html($count_text); ?></p>
							</a>
							<?php endforeach;
						}
						?>
					</div>
				</div>
			</section>

			<?php
			/* ============================================================
			   SECTION 4: LATEST / FEATURED COLORING PAGES
			   ============================================================ */
			?>
			<section class="kk-latest" aria-label="Nieuwste kleurplaten">
				<div class="container">
					<div class="kk-latest__header">
						<h2 class="kk-latest__title">Nieuwste kleurplaten</h2>
						<p class="kk-latest__subtitle">Helemaal vers om te downloaden en te printen.</p>
					</div>
					<div class="kk-latest__grid" id="kk-latest-grid">
						<?php
						$latest_posts = new WP_Query(array(
							'post_type'      => 'kleurplaten',
							'post_status'    => 'publish',
							'posts_per_page' => 12,
							'orderby'        => 'date',
							'order'          => 'DESC',
						));

						if ($latest_posts->have_posts()) :
							while ($latest_posts->have_posts()) : $latest_posts->the_post();
								$thumbnail = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium') : get_theme_file_uri('/assets/images/placeholder.svg');
								$terms = get_the_terms(get_the_ID(), 'kleurplaat_categorie');
								$category_name = !empty($terms) && !is_wp_error($terms) ? esc_html($terms[0]->name) : 'Algemeen';
							?>
							<article class="kk-latest-card">
								<div class="kk-latest-card__thumb">
									<img src="<?php echo esc_url($thumbnail); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" width="300" height="300">
									<span class="kk-latest-card__badge">Gratis</span>
								</div>
								<div class="kk-latest-card__body">
									<span class="kk-latest-card__category"><?php echo esc_html($category_name); ?></span>
									<h3 class="kk-latest-card__title"><?php the_title(); ?></h3>
									<a href="<?php the_permalink(); ?>" class="kk-latest-card__cta">
										Gratis download
										<span class="kk-latest-card__arrow" aria-hidden="true">&#8594;</span>
									</a>
								</div>
							</article>
							<?php
							endwhile;
							wp_reset_postdata();
						else :
							?>
							<p class="kk-latest-card__empty">Momenteel zijn er nog geen kleurplaten beschikbaar. Kom binnenkort terug!</p>
							<?php
						endif;
						?>
					</div>
				</div>
			</section>

			<?php
			/* ============================================================
			   SECTION 5: FOR PARENTS & TEACHERS
			   ============================================================ */
			?>
			<section class="kk-parents-teachers" aria-label="Voor ouders en leerkrachten">
				<div class="container">
					<div class="kk-parents-teachers__grid">
						<div class="kk-parents-teachers__text">
							<h2 class="kk-parents-teachers__title">Handig voor ouders en leerkrachten</h2>
							<p>Op Kinderkleurplaten.com vind je duizenden gratis kleurplaten die speciaal zijn geselecteerd voor kinderen. Of je nu een rustige activiteit zoekt voor thuis, een lesaanvulling voor de klas of een creatieve invulling voor een kinderfeestje: onze kleurplaten zijn direct te downloaden en te printen in A4-formaat.</p>
							<ul class="kk-parents-teachers__list">
								<li><strong>Geen account nodig:</strong> direct downloaden en printen, zonder registratie.</li>
								<li><strong>Educatieve waarde:</strong> kleuren stimuleert de fijne motoriek, creativiteit en concentratie.</li>
								<li><strong>Kindvriendelijk:</strong> alle tekeningen zijn veilig en geschikt voor kinderen van alle leeftijden.</li>
								<li><strong>Printklaar:</strong> elke kleurplaat past perfect op een A4-vet en blijft scherp bij uitprinten.</li>
							</ul>
						</div>
						<div class="kk-parents-teachers__visual" aria-hidden="true">
							<div class="kk-parents-teachers__illustration">
								<svg width="400" height="300" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
									<rect width="400" height="300" fill="#fff8fb" rx="24"/>
									<rect x="40" y="40" width="120" height="160" fill="#ffd166" rx="12"/>
									<rect x="200" y="60" width="120" height="140" fill="#ff6b9d" rx="12"/>
									<rect x="240" y="160" width="100" height="80" fill="#4cc9f0" rx="12"/>
									<circle cx="80" cy="120" r="20" fill="#72efb2"/>
									<circle cx="260" cy="200" r="15" fill="#b8a7ff"/>
								</svg>
							</div>
							<p class="kk-parents-teachers__caption">Ouders en leerkrachten kiezen Kinderkleurplaten.com</p>
						</div>
					</div>
				</div>
			</section>

			<?php
			/* ============================================================
			   SECTION 6: SEO CONTENT (important for AdSense)
			   ============================================================ */
			?>
			<section class="kk-seo-content" aria-label="Over kleurplaten en de voordelen voor kinderen">
				<div class="container">
					<div class="kk-seo-content__inner">
						<h2 class="kk-seo-content__title">Waarom kleurplaten goed zijn voor kinderen</h2>
						<p class="kk-seo-content__lead">Kleurplaten zijn meer dan alleen een leuke bezigheid. Ze dragen bij aan de ontwikkeling van kinderen op vele manieren. Bij Kinderkleurplaten.com helpen we ouders, leerkrachten en opvoeders om gratis kleurplaten te vinden, te downloaden en te printen.</p>

						<h3>De voordelen van kleuren voor kinderen</h3>
						<p>Kleuren is een van de simpelste én meest effectieve manieren om kinderen te laten leren en groeien. Wanneer een kind een kleurplaat invult, werkt het niet alleen aan creativiteit, maar ook aan belangrijke motorische vaardigheden. Het vasthouden van een kleurpotlood of stift oefent de fijne motoriek in de handen en vingers, wat later essentieel is voor het leren schrijven.</p>
						<p>Daarnaast helpt kleuren bij het ontwikkelen van concentratie en geduld. Een kind dat zich verdiept in een kleurplaat leert om zijn aandacht langer op één taak te richten. Dit draagt bij aan een betere focus, ook buiten het kleuren om. Creativiteit krijgt een impuls doordat kinderen zelf bepalen welke kleuren ze gebruiken en hoe ze de tekening invullen.</p>

						<h3>Gratis kleurplaten voor thuis en op school</h3>
						<p>Bij Kinderkleurplaten.com bieden we een uitgebreide collectie gratis kleurplaten voor kinderen van alle leeftijden. Of je nu op zoek bent naar dieren, prinsessen, dinosaurussen, voertuigen of seizoensgebonden thema's: we hebben voor elk kind iets passends. Alle kleurplaten zijn ontworpen om eenvoudig te printen in A4-formaat, zodat je direct aan de slag kunt zonder gedoe.</p>
						<p>Ouders waarderen dat onze kleurplaten volledig gratis zijn en geen account vereisen. Je kunt direct naar de gewenste categorie gaan, een kleurplaat kiezen en printen. Dit maakt onze site ideaal voor drukke gezinnen die een snelle, kindvriendelijke activiteit zoeken.</p>
						<p>Leerkrachten kunnen onze kleurplaten gebruiken als lesaanvulling, bijvoorbeeld tijdens themaweken over dieren, seizoenen of feestdagen. De educatieve kleurplaten helpen kinderen om cijfers, letters en vormen te herkennen op een speelse manier. Omdat er geen registratie nodig is, kunnen leerkrachten snel en eenvoudig materiaal voor de hele klas printen.</p>

						<h3>Tips voor ouders en leerkrachten</h3>
						<ul class="kk-seo-content__tips">
							<li>Kies kleurplaten die aansluiten bij de leeftijd en interesses van het kind.</li>
							<li>Bied verschillende kleurmaterialen aan, zoals kleurpotloden, viltstiften en wastekrijtjes.</li>
							<li>Laat het kind zelf bepalen welke kleuren het gebruikt, ook al wijken ze af van de werkelijkheid.</li>
							<li>Print meerdere exemplaren van dezelfde kleurplaat voor broertjes, zusjes of klasgenoten.</li>
							<li>Gebruik kleurplaten als rustige activiteit na een drukke schooldag of voor het slapen gaan.</li>
						</ul>

						<h3>Over Kinderkleurplaten.com</h3>
						<p>Kinderkleurplaten.com is dé plek voor gratis kleurplaten in Nederland en België. Onze missie is om elke dag weer nieuwe, hoogwaardige kleurplaten aan te bieden die kinderen inspireren en ouders en leerkrachten helpen. Alle tekeningen op onze site zijn zorgvuldig geselecteerd en geschikt voor kinderen van alle leeftijden.</p>
						<p>Bezoek onze categorieën om alle gratis kleurplaten te ontdekken, of gebruik de zoekfunctie om een specifiek thema te vinden. Veel kleurplezier!</p>
					</div>
				</div>
			</section>

			<?php
			/* ============================================================
			   SECTION 7: NEWSLETTER / COMMUNITY
			   ============================================================ */
			?>
			<section class="kk-newsletter" aria-label="Nieuwsbrief">
				<div class="container kk-newsletter__wrap">
					<div class="kk-newsletter__card">
						<div class="kk-newsletter__content">
							<h2 class="kk-newsletter__title">Ontvang nieuwe kleurplaten in je inbox</h2>
							<p class="kk-newsletter__subtitle">Schrijf je in en ontvang iedere week de nieuwste kleurplaten, knutselideeën en creatieve tips voor kinderen.</p>
							<form class="kk-newsletter__form" action="#" method="post" novalidate>
								<label for="kk-newsletter-email" class="screen-reader-text">E-mailadres</label>
								<input type="email" id="kk-newsletter-email" name="email" placeholder="Jouw e-mailadres" required aria-label="E-mailadres">
								<button type="submit" class="kk-btn kk-btn--primary kk-newsletter__button">Inschrijven</button>
								<p class="kk-newsletter__note">Je kunt je te jeder tijd uitschrijven. Je e-mailadres wordt niet gedeeld met derden.</p>
							</form>
						</div>
						<div class="kk-newsletter__visual" aria-hidden="true">
							<div class="kk-newsletter__badge">&#128247;</div>
							<div class="kk-newsletter__badge" style="animation-delay: 1s;">&#127937;</div>
							<div class="kk-newsletter__badge" style="animation-delay: 2s;">&#128127;</div>
						</div>
					</div>
				</div>
			</section>

		<?php endif; ?>
	</div>
</main>

<?php get_footer();
