<?php
/**
 * Front-page template.
 *
 * Displays the homepage with featured, popular, and category sections.
 *
 * @package kinderkleurplaten
 */

if (!function_exists('kk_home_category_url')) {
	/**
	 * Resolve a human-friendly category display name to its taxonomy archive URL.
	 *
	 * Works on the official custom taxonomy 'kleurplaat_categorie' (rewrite slug
	 * 'kleurplaat-categorie'), so links point to /kleurplaat-categorie/<slug>/.
	 *
	 * @param string $name Display name of the category (e.g. "Feestdagen & Seizoenen").
	 * @return string Term archive URL or home URL fallback.
	 */
	function kk_home_category_url($name) {
		if (empty($name)) {
			return home_url('/');
		}

		// Mapping array for display names to actual taxonomy term names/slugs.
		$mapping = array(
			'dieren'                            => array('dieren', 'Dieren'),
			'feestdagen & seizoenen'            => array('feestdagen-seizoenen', 'Feestdagen & Seizoenen', 'Feestdagen en Seizoenen', 'Seizoenen'),
			'voertuigen & bouw'                 => array('voertuigen-bouw', 'Voertuigen & Bouw', 'Voertuigen en Bouw', 'Voertuigen', 'Bouw'),
			'fantasie & sprookjes'              => array('fantasie-sprookjes', 'Fantasie & Sprookjes', 'Fantasie en Sprookjes', 'Fantasie', 'Sprookjes'),
			'educatief & cijfers/letters'       => array('educatief-cijfers-letters', 'Educatief & Cijfers/Letters', 'Educatief en Cijfers/Letters', 'Educatief', 'Cijfers/Letters', 'Cijfers en Letters', 'Letters en Cijfers'),
			'bekende helden'                    => array('bekende-helden', 'Bekende Helden'),
		);

		$taxonomy = 'kleurplaat_categorie';
		$name_lower = strtolower(trim($name));

		// Try mapping first.
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

		// Try exact slug match.
		$slug_candidate = sanitize_title($name);
		$term = get_term_by('slug', $slug_candidate, $taxonomy);
		if ($term && !is_wp_error($term)) {
			return get_term_link($term);
		}

		// Try exact name match.
		$term = get_term_by('name', $name, $taxonomy);
		if ($term && !is_wp_error($term)) {
			return get_term_link($term);
		}

		// Try case-insensitive name match.
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
			}

			// Try partial matches.
			foreach ($terms_all as $t) {
				$t_name_lower = strtolower($t->name);
				$t_slug_lower = strtolower($t->slug);
				if (strpos($t_name_lower, $name_lower) !== false || strpos($name_lower, $t_name_lower) !== false ||
					strpos($t_slug_lower, $name_lower) !== false || strpos($name_lower, $t_slug_lower) !== false) {
					return get_term_link($t);
				}
			}

			// Try word-based partial matching.
			$name_words = preg_split('/[\s&\/]+/', $name_lower, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($terms_all as $t) {
				$t_name_lower = strtolower($t->name);
				$all_match = true;
				foreach ($name_words as $word) {
					if (strlen($word) > 2 && strpos($t_name_lower, $word) === false) {
						$all_match = false;
						break;
					}
				}
				if ($all_match && !empty($name_words)) {
					return get_term_link($t);
				}
			}
		}

		// Fallback to search.
		return home_url('/?s=' . rawurlencode($name));
	}
}

if (!function_exists('kk_get_all_categories_with_links')) {
	/**
	 * Return all kleurplaat_categorie terms with their archive links.
	 *
	 * @return array Array of arrays with term, name, slug, url, id, count.
	 */
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
			<?php get_template_part('snippets/homepage-intro'); ?>

			<section class="kk-home-search" aria-label="Zoeken naar kleurplaten">
				<div class="kk-home-search__inner">
					<form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="kk-search-form" role="search">
						<label for="kk-home-search-input" class="screen-reader-text"><?php esc_html_e('Zoek kleurplaten', 'kinderkleurplaten'); ?></label>
						<input id="kk-home-search-input" type="search" name="s" class="kk-search-input" placeholder="<?php esc_attr_e('Zoek kleurplaten...', 'kinderkleurplaten'); ?>" autocomplete="off" aria-label="<?php esc_attr_e('Zoek kleurplaten', 'kinderkleurplaten'); ?>">
						<button type="submit" class="kk-search-button"><?php esc_html_e('Zoeken', 'kinderkleurplaten'); ?></button>
					</form>
				</div>
			</section>

			<section class="kk-home-section" aria-labelledby="kk-featured-title">
				<div class="kk-home-section__header">
					<h2 id="kk-featured-title" class="kk-home-section__title">Uitgelichte kleurplaten</h2>
					<p class="kk-home-section__subtitle">Onze favoriete kleurplaten om direct te printen. Elke pagina is gratis te printen in scherp A4-formaat, zonder registratie. Klik op een miniatuur en je begint binnen seconden met kleuren.</p>
				</div>
				<?php echo do_shortcode('[kleurplaten_uitgelicht limit="4" columns="4"]'); ?>

				<?php
				$kk_popular_pages = array(
					array(
						'title'       => 'Dierenvriendjes',
						'description' => 'Puzzel met pinguïns, olifanten en andere dieren die elkaar als vriendjes helpen. Download de collectie en laat hun avonturen op papier springen.',
						'cta_label'   => 'Alle dieren bekijken',
						'category'    => 'Dieren',
					),
					array(
						'title'       => 'Voertuigen',
						'description' => 'Van snelle raceautos tot hoge rekkers en vliegtuigen &mdash; alle voertuigen staan klaar om gekleurd te worden. Bouw direct je eigen verkeersplein op A4.',
						'cta_label'   => 'Alle voertuigen bekijken',
						'category'    => 'Voertuigen & Bouw',
					),
					array(
						'title'       => 'Feestdagen',
						'description' => 'Vier kerst, sinterklaas en andere feestdagen met kleurplaten die feestelijk en mooi eruit zien. Klik en print direct, zonder gedoe.',
						'cta_label'   => 'Alle feestdagen bekijken',
						'category'    => 'Feestdagen & Seizoenen',
					),
					array(
						'title'       => 'Sprookjes',
						'description' => 'Klassieke sprookjes komen weer tot leven als jij ze zelf in kleur zet. Van prinsessen tot draken &mdash; het verhaal is van jou.',
						'cta_label'   => 'Alle sprookjes bekijken',
						'category'    => 'Fantasie & Sprookjes',
					),
				);

				?>
				<ul class="kk-popular-pages" role="list">
					<?php foreach ($kk_popular_pages as $page) :
						$category_url = kk_home_category_url($page['category']);
						?>
						<li class="kk-popular-page-card">
							<h3 class="kk-popular-page-card__title"><?php echo esc_html($page['title']); ?></h3>
							<p class="kk-popular-page-card__desc"><?php echo wp_kses_post($page['description']); ?></p>
							<a href="<?php echo esc_url($category_url); ?>" class="kk-popular-page-card__cta kk-search-button"><?php echo esc_html($page['cta_label']); ?></a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<?php
			$kk_popular_categories = array(
				array(
					'title'       => 'Dieren',
					'description' => 'Kleurplaten met olifanten, girpen, honden, katten en alle andere dieren die kinderen super leuk vinden.',
					'kid_love'    => 'Ze kunnen hun lievelingsdier kiezen en zich voorstellen dat het echt meeleeft.',
					'accent'      => 'green',
					'emoji'       => '🐘',
				),
				array(
					'title'       => 'Feestdagen & Seizoenen',
					'description' => 'Vier kerst, halloween, sinterklaas en lente met prachtige gekleurde kleurplaten die een feestelijk huis maken.',
					'kid_love'    => 'Ze mogen feesten en kleuren, en voelen zich zelf ook feestelijk.',
					'accent'      => 'pink',
					'emoji'       => '🎉',
				),
				array(
					'title'       => 'Voertuigen & Bouw',
					'description' => 'Kleur raceautos, vliegtuigen en schepen, of bouw een wereld vol voertuigen en kranen zelf mee.',
					'kid_love'    => 'Ze bouwen en besturen hun eigen wereld, precies als echte bouwers en pilots.',
					'accent'      => 'blue',
					'emoji'       => '🚗',
				),
				array(
					'title'       => 'Fantasie & Sprookjes',
					'description' => 'Prinsessen, draken, magiële dieren en sprookjeshelden die kinderen meenemen in nieuwe magische werelden op papier.',
					'kid_love'    => 'Ze vangen verhalen op papier vast en kunnen daarna zelf verder dromen.',
					'accent'      => 'purple',
					'emoji'       => '🐉',
				),
				array(
					'title'       => 'Educatief & Cijfers/Letters',
					'description' => 'Leer cijfers, letters, vormen en kleuren met kleurplaten die speciaal zijn gemaakt voor het leren.',
					'kid_love'    => 'Ze leren zonder het te merken, want kleuren blijft spelenderwijs leuk.',
					'accent'      => 'blue',
					'emoji'       => '📚',
				),
				array(
					'title'       => 'Bekende Helden',
					'description' => 'Superhelden, powned en bekende figuren die kinderen helpen en motiveren elke dag om te kleuren.',
					'kid_love'    => 'Ze imiteren hun helden en voelen zich zelf ook een beetje heldhaftig aan.',
					'accent'      => 'pink',
					'emoji'       => '🦸',
				),
			);
			?>
			<section class="kk-home-section kk-home-section--accent" aria-labelledby="kk-hero-cats-title">
				<div class="kk-home-section__header">
					<h2 id="kk-hero-cats-title" class="kk-home-section__title">Populaire thema's</h2>
					<p class="kk-home-section__subtitle">Kies een thema, print gratis en begin met kleuren!</p>
				</div>
				<div class="kk-hero-cats__grid">
					<?php foreach ($kk_popular_categories as $cat) :
						$cat_url = kk_home_category_url($cat['title']);
						$accent  = isset($cat['accent']) ? esc_attr($cat['accent']) : 'pink';
						?>
						<a href="<?php echo esc_url($cat_url); ?>" class="kk-hero-cat-card kk-hero-cat-card--<?php echo $accent; ?>" aria-label="<?php echo esc_attr($cat['title']); ?>">
							<div class="kk-hero-cat-card__visual">
								<span class="kk-hero-cat-card__emoji" aria-hidden="true"><?php echo esc_html($cat['emoji']); ?></span>
							</div>
							<div class="kk-hero-cat-card__body">
								<h3 class="kk-hero-cat-card__title"><?php echo esc_html($cat['title']); ?></h3>
								<p class="kk-hero-cat-card__desc"><?php echo esc_html($cat['description']); ?></p>
								<p class="kk-hero-cat-card__kids-love">
									<strong>Waarom kinderen dit leuk vinden:</strong> <?php echo esc_html($cat['kid_love']); ?>
								</p>
								<span class="kk-hero-cat-card__cta">
									<?php esc_html_e('Bekijk kleurplaten', 'kinderkleurplaten'); ?>
									<svg class="kk-hero-cat-card__arrow" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
										<path d="M4 10h12m0 0l-4-4m4 4l-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="kk-home-section" aria-labelledby="kk-examples-title">
				<div class="kk-home-section__header">
					<h2 id="kk-examples-title" class="kk-home-section__title">Nieuwe kleurplaten</h2>
					<p class="kk-home-section__subtitle">Helemaal vers om te printen</p>
				</div>
				<?php echo do_shortcode('[kleurplaat_examples limit="6"]'); ?>
			</section>

			<section class="kk-home-section kk-home-benefits" aria-labelledby="kk-benefits-title">
				<div class="kk-home-section__header">
					<h2 id="kk-benefits-title" class="kk-home-section__title">Waarom kiezen voor kinderkleurplaten</h2>
					<p class="kk-home-section__subtitle">Alles wat je nodig hebt om gratis te kleuren</p>
				</div>
				<div class="kk-benefits-grid">
					<article class="kk-benefit-card" aria-label="Educatief">
						<div class="kk-benefit-card__icon" aria-hidden="true">🧠</div>
						<h3 class="kk-benefit-card__title">Educatief</h3>
						<p class="kk-benefit-card__text">Kleurplaten stimuleren fijne motoriek, concentratie en creativiteit bij kinderen.</p>
					</article>
					<article class="kk-benefit-card" aria-label="Gratis">
						<div class="kk-benefit-card__icon" aria-hidden="true">🎁</div>
						<h3 class="kk-benefit-card__title">Gratis</h3>
						<p class="kk-benefit-card__text">Alle kleurplaten zijn helemaal gratis te printen en te downloaden.</p>
					</article>
					<article class="kk-benefit-card" aria-label="Direct klaar">
						<div class="kk-benefit-card__icon" aria-hidden="true">⚡</div>
						<h3 class="kk-benefit-card__title">Direct klaar</h3>
						<p class="kk-benefit-card__text">Geen account nodig. Kies, print en begin direct met kleuren.</p>
					</article>
					<article class="kk-benefit-card" aria-label="Betrouwbaar">
						<div class="kk-benefit-card__icon" aria-hidden="true">🌟</div>
						<h3 class="kk-benefit-card__title">Betrouwbaar</h3>
						<p class="kk-benefit-card__text">Kindvriendelijke selectie met hoge kwaliteit afbeeldingen voor het beste resultaat.</p>
					</article>
				</div>

				<div class="kk-eeat">
					<h3 class="kk-eeat__title">Onze kennis &amp; kwaliteit</h3>
					<div class="kk-eeat__grid">
						<div class="kk-eeat__item">
							<h4 class="kk-eeat__subtitle">Printkwaliteit &amp; Formaat</h4>
							<p class="kk-eeat__text">Alle kleurplaten op onze site worden in vector-formaat gemaakt en opgeslagen, zodat ze bij elke vergroting scherp blijven. Ze passen perfect op een vel A4-papier (210 bij 297 mm), zonder dat randen worden afgesneden. Zo krijg je na het afdrukken altijd een nette, bruikbare pagina die direct ingevouwen kan worden.</p>
						</div>
						<div class="kk-eeat__item">
							<h4 class="kk-eeat__subtitle">Gebruik voor Ouders, Scholen en Kinderopvang</h4>
							<p class="kk-eeat__text">Of je thuis een middag activiteit zoekt, in de klas een stille werkplek wilt bieden of op de peuterdag een betrouwbare aanvulling zoekt: hier kun je direct printen. Alle materialen zijn 100% gratis, zonder reclame, zonder registratie en zonder verborgen kosten. De afbeeldingen zijn kindvriendelijk en vrij van ongepast materiaal, zodat je als organisatie of opvoeder gerust kunt gebruiken.</p>
						</div>
						<div class="kk-eeat__item">
							<h4 class="kk-eeat__subtitle">Creatieve Ontwikkeling</h4>
							<p class="kk-eeat__text">Regelmatig kleuren versterkt de fijne motoriek, verbetert de concentratie en leert kinderen geduld en planning. Door met verschillende kleuren en technieken te experimenteren, groeit hun creativiteit en zelfvertrouwen. Ouders en pedagogisch personeel zien dat een simpele kleurplaat vaak de beste start is voor een rustige, gerichtere dag.</p>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
