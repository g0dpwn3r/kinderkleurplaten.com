<?php
/**
 * Kinderkleurplaten theme bootstrap.
 * Rebuilt as a single safe functions.php file.
 */

if (!defined('ABSPATH')) {
	exit;
}

define('KINDERKLEURPLATEN_VERSION', '1.0.4');

add_action('after_setup_theme', 'kk_theme_setup');
function kk_theme_setup() {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', array(
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	));
	add_theme_support('responsive-embeds');
	register_nav_menus(array(
		'primary' => __('Hoofdmenu', 'kinderkleurplaten'),
		'footer'  => __('Footermenu', 'kinderkleurplaten'),
	));
}

if (!function_exists('kinderkleurplaten_fallback_menu')) {
	function kinderkleurplaten_fallback_menu() {
		echo '<ul>';
		echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'kinderkleurplaten') . '</a></li>';
		echo '<li><a href="' . esc_url(home_url('/kleurplaten/')) . '">' . esc_html__('Kleurplaten', 'kinderkleurplaten') . '</a></li>';
		echo '</ul>';
	}
}

// Eénmalig de categorie-thumbnail cache flushen na een thema-update.
add_action('init', 'kk_flush_category_cache_on_version_change', 5);
function kk_flush_category_cache_on_version_change() {
	$stored_version = get_option('kinderkleurplaten_version', '');
	if ($stored_version !== KINDERKLEURPLATEN_VERSION) {
		// Eerst markeren dat we deze versie hebben verwerkt, zodat een
		// eventuele fout tijdens het rebuilden niet tot een flush-loop leidt.
		update_option('kinderkleurplaten_version', KINDERKLEURPLATEN_VERSION, false);
		if (function_exists('kk_invalidate_category_cache')) {
			kk_invalidate_category_cache();
		}
	}
}

add_action('wp_enqueue_scripts', 'kk_enqueue_theme_styles');
function kk_enqueue_theme_styles() {
	wp_enqueue_style('kinderkleurplaten-style', get_stylesheet_uri(), array(), KINDERKLEURPLATEN_VERSION);
	wp_enqueue_script(
		'kinderkleurplaten-main',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		KINDERKLEURPLATEN_VERSION,
		true
	);
}

if (!function_exists('kinderkleurplaten_get_colouring_image_url')) {
	function kinderkleurplaten_get_colouring_image_url($slug = '') {
		global $post;

		if (!is_a($post, 'WP_Post')) {
			return '';
		}

		$thumbnail_id = get_post_thumbnail_id($post->ID);
		if (!$thumbnail_id) {
			return '';
		}

		$image = wp_get_attachment_image_src($thumbnail_id, 'full');
		if (empty($image[0])) {
			return '';
		}

		return esc_url($image[0]);
	}
}

	if (!function_exists('kinderkleurplaten_find_colouring_image')) {
	/**
	 * zoekt de uitgelichte afbeelding (post thumbnail) van een kleurplaat.
	 *
	 * Gebruik 1 (per-post):   kinderkleurplaten_find_colouring_image( $post_id )
	 *   → geeft een array terug met 'id', 'url' en 'alt', of false.
	 *
	 * Gebruik 2 (archive):    kinderkleurplaten_find_colouring_image()
	 *   → Op een taxonomy-archive van 'kleurplaat_categorie': haalt de
	 *     meest recente kleurplaat met een geldige featured image uit de
	 *     huidige term. Gebruikt daarnaast het opgeslagen termmeta
	 *     thumbnail_id indien beschikbaar.
	 *
	 * @param int|null $post_id  Post-ID, of null voor archive-context.
	 * @return array|false  Array met keys 'id' (attachment ID), 'url', 'alt'.
	 *                      False als geen afbeelding gevonden.
	 */
	function kinderkleurplaten_find_colouring_image($post_id = null) {

		/* ── Archive-context: geen post-ID opgegeven ────────────── */
		if ($post_id === null) {
			if (!is_tax('kleurplaat_categorie') && !is_category() && !is_tag()) {
				return false;
			}

			$term = get_queried_object();
			if (!$term || is_wp_error($term) || !isset($term->term_id)) {
				return false;
			}

			// 1. Probeer het opgeslagen termmeta thumbnail_id.
			$term_thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
			if ($term_thumb_id) {
				$image_data = wp_get_attachment_image_src((int) $term_thumb_id, 'full');
				if (!empty($image_data[0])) {
					return array(
						'id'  => (int) $term_thumb_id,
						'url' => esc_url($image_data[0]),
						'alt' => esc_attr(get_post_meta((int) $term_thumb_id, '_wp_attachment_image_alt', true)),
					);
				}
			}

			// 2. Fallback: meest recente kleurplaat die WERKELIJK een thumbnail heeft.
			$first = new WP_Query(array(
				'post_type'      => 'kleurplaten',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'tax_query'      => array(
					array(
						'taxonomy'         => 'kleurplaat_categorie',
						'field'            => 'term_id',
						'terms'            => $term->term_id,
						'include_children' => true,
					),
				),
				'meta_query'     => array(
					array(
						'key'     => '_thumbnail_id',
						'value'   => '',
						'compare' => '!=',
					),
				),
				'fields'         => 'ids',
				'no_found_rows'  => true,
			));

			if (!$first->have_posts()) {
				wp_reset_postdata();
				return false;
			}

			$post_id = (int) $first->posts[0];
			wp_reset_postdata();
		}

		/* ── Per-post lookup (incl. fallback vanuit archive) ────── */
		$post_id = (int) $post_id;
		if ($post_id <= 0) {
			return false;
		}

		$thumbnail_id = get_post_thumbnail_id($post_id);
		if (!$thumbnail_id) {
			return false;
		}

		$image_data = wp_get_attachment_image_src($thumbnail_id, 'full');
		if (empty($image_data[0])) {
			return false;
		}

		return array(
			'id'  => (int) $thumbnail_id,
			'url' => esc_url($image_data[0]),
			'alt' => esc_attr(get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true)),
		);
	}
}

if (!function_exists('kinderkleurplaten_excerpt')) {
	/**
	 * Veilige excerpt-helper voor in templates.
	 * Werkt binnen de WordPress Loop: geen argument nodig.
	 *
	 * @param int $words Aantal woorden (default 18).
	 * @return string HTML-veilige excerpt.
	 */
	function kinderkleurplaten_excerpt($words = 18) {
		$excerpt = get_the_excerpt();
		if (empty($excerpt)) {
			$excerpt = get_the_content();
			$excerpt = wp_strip_all_tags($excerpt);
		}
		return wp_trim_words($excerpt, (int) $words, '…');
	}
}

add_action('init', 'kk_register_post_types_and_taxonomies');
function kk_register_post_types_and_taxonomies() {
	$kleurplaten_labels = array(
		'name'               => __('Kleurplaten', 'kinderkleurplaten'),
		'singular_name'      => __('Kleurplaat', 'kinderkleurplaten'),
		'menu_name'          => __('Kleurplaten', 'kinderkleurplaten'),
		'name_admin_bar'     => __('Kleurplaat', 'kinderkleurplaten'),
		'add_new'            => __('Nieuwe kleurplaat', 'kinderkleurplaten'),
		'add_new_item'       => __('Nieuwe kleurplaat toevoegen', 'kinderkleurplaten'),
		'edit_item'          => __('Kleurplaat bewerken', 'kinderkleurplaten'),
		'new_item'           => __('Nieuwe kleurplaat', 'kinderkleurplaten'),
		'view_item'          => __('Kleurplaat bekijken', 'kinderkleurplaten'),
		'all_items'          => __('Alle kleurplaten', 'kinderkleurplaten'),
		'search_items'       => __('Zoeken in kleurplaten', 'kinderkleurplaten'),
		'not_found'          => __('Geen kleurplaten gevonden', 'kinderkleurplaten'),
		'not_found_in_trash' => __('Geen kleurplaten gevonden in prullenbak', 'kinderkleurplaten'),
	);

	if (!post_type_exists('kleurplaten')) {
		register_post_type('kleurplaten', array(
			'labels'             => $kleurplaten_labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'kleurplaten', 'with_front' => false),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-images-alt2',
			'show_in_rest'       => true,
			'rest_base'          => 'kleurplaten',
			'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
		));
	}

	$gastenboek_labels = array(
		'name'               => __('Gastenboekberichten', 'kinderkleurplaten'),
		'singular_name'      => __('Gastenboekbericht', 'kinderkleurplaten'),
		'menu_name'          => __('Gastenboek', 'kinderkleurplaten'),
		'all_items'          => __('Alle gastenboekberichten', 'kinderkleurplaten'),
		'edit_item'          => __('Gastenboekbericht bewerken', 'kinderkleurplaten'),
		'view_item'          => __('Gastenboekbericht bekijken', 'kinderkleurplaten'),
		'not_found'          => __('Geen gastenboekberichten gevonden', 'kinderkleurplaten'),
		'not_found_in_trash' => __('Geen gastenboekberichten gevonden in prullenbak', 'kinderkleurplaten'),
	);

	if (!post_type_exists('gastenboek_bericht')) {
		register_post_type('gastenboek_bericht', array(
			'labels'             => $gastenboek_labels,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_admin_bar'  => false,
			'query_var'          => false,
			'capability_type'    => 'post',
			'hierarchical'       => false,
			'menu_icon'          => 'dashicons-admin-comments',
			'show_in_rest'       => false,
			'supports'           => array('title', 'editor', 'author', 'custom-fields'),
		));
	}

	$thema_labels = array(
		'name'              => __('Kleurplaat thema\'s', 'kinderkleurplaten'),
		'singular_name'     => __('Kleurplaat thema', 'kinderkleurplaten'),
		'search_items'      => __('Zoek kleurplaat thema\'s', 'kinderkleurplaten'),
		'all_items'         => __('Alle kleurplaat thema\'s', 'kinderkleurplaten'),
		'parent_item'       => __('Bovenliggend thema', 'kinderkleurplaten'),
		'parent_item_colon' => __('Bovenliggend thema:', 'kinderkleurplaten'),
		'edit_item'         => __('Thema bewerken', 'kinderkleurplaten'),
		'update_item'       => __('Thema bijwerken', 'kinderkleurplaten'),
		'add_new_item'      => __('Nieuw thema toevoegen', 'kinderkleurplaten'),
		'new_item_name'     => __('Nieuwe themanaam', 'kinderkleurplaten'),
		'menu_name'         => __('Thema\'s', 'kinderkleurplaten'),
	);

	if (!taxonomy_exists('kleurplaat_thema')) {
		register_taxonomy('kleurplaat_thema', array('kleurplaten'), array(
			'labels'            => $thema_labels,
			'public'            => true,
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'rest_base'         => 'kleurplaat_thema',
			'rewrite'           => array('slug' => 'kleurplaat-thema', 'with_front' => false),
		));
	} else {
		register_taxonomy_for_object_type('kleurplaat_thema', 'kleurplaten');
	}
}

add_action('wp_head', 'kk_print_inline_theme_css');
function kk_print_inline_theme_css() {
	$css = '
.kk-print-button { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 18px; border-radius: 16px; font-weight: 800; background: #61f4a3; color: #000; cursor: pointer; border: none; transition: all 0.2s ease; }
.kk-print-button:hover { background: #52e992; transform: scale(1.05); box-shadow: 0 4px 12px rgba(97, 244, 163, 0.4); }
.kk-print-button:active { transform: scale(0.98); }
.kk-gallery-shell,
.kk-gastenboek-shell { max-width: 1200px; margin: 0 auto; padding: 32px 20px; box-sizing: border-box; }
.kk-gallery-shell *, .kk-gastenboek-shell * { box-sizing: border-box; }
.kk-gallery-search { display: flex; gap: 12px; margin: 0 0 24px; }
.kk-gallery-search input[type="search"] { flex: 1; min-height: 56px; border: 2px solid #e5e7eb; border-radius: 999px; padding: 0 18px; font-size: 16px; outline: none; }
.kk-gallery-search input[type="search"]:focus { border-color: #ffb6c1; box-shadow: 0 0 0 4px rgba(255,182,193,.25); }
.kk-gallery-search button, .kk-gastenboek-button { border: 0; border-radius: 999px; background: #ffb6c1; color: #5a0f1e; padding: 0 22px; min-height: 56px; font-weight: 800; cursor: pointer; }
.kk-gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 22px; }
.kk-gallery-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 24px; overflow: hidden; box-shadow: 0 12px 30px rgba(15,23,42,.08); display: flex; flex-direction: column; }
.kk-gallery-card__image-wrap { background: #f8fafc; padding: 18px; display: grid; place-items: center; }
.kk-gallery-card__image { display: block; width: 100%; max-height: 360px; object-fit: contain; }
.kk-gallery-card__body { padding: 18px; display: flex; flex-direction: column; gap: 14px; flex: 1; }
.kk-gallery-card__title { margin: 0; font-size: 18px; line-height: 1.3; }
.kk-gallery-card__actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: auto; }
.kk-button { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 14px; border-radius: 999px; font-weight: 800; text-decoration: none; }
.kk-button--print { background: #61f4a3; color: #000; }
.kk-button--svg { background: transparent; border: 2px solid #ffb6c1; color: #ffb6c1; }
.kk-empty-state { grid-column: 1 / -1; padding: 28px; border: 2px dashed #cbd5e1; border-radius: 24px; background: #fff; text-align: center; color: #64748b; font-weight: 800; }
.kk-gastenboek-form, .kk-gastenboek-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 24px; box-shadow: 0 12px 30px rgba(15,23,42,.08); padding: 24px; margin-bottom: 22px; }
.kk-gastenboek-field { display: block; margin-bottom: 16px; }
.kk-gastenboek-field span { display: block; font-weight: 800; margin-bottom: 6px; }
.kk-gastenboek-field input, .kk-gastenboek-field textarea { width: 100%; border: 2px solid #e5e7eb; border-radius: 16px; padding: 12px 14px; font: inherit; }
.kk-gastenboek-field textarea { min-height: 130px; resize: vertical; }
.kk-gastenboek-notice { margin: 0 0 18px; padding: 12px 16px; border-radius: 16px; background: #ecfdf5; color: #065f46; font-weight: 800; }
.kk-gastenboek-list { display: grid; gap: 18px; }
.kk-gastenboek-card h3 { margin: 0 0 8px; }
.kk-gastenboek-card p { margin: 0; color: #475569; line-height: 1.6; }
@media (max-width: 720px) { .kk-gallery-search { flex-direction: column; } .kk-gallery-search button { width: 100%; } .kk-gallery-grid { grid-template-columns: 1fr; } }
.kk-homepage-grid { display: grid; gap: 1.5rem; grid-template-columns: repeat(4, minmax(0, 1fr)); margin: 2rem 0; }
.kk-homepage-card { display: flex; flex-direction: column; height: 100%; padding: 1.25rem; border: 1px solid rgba(36, 48, 66, 0.12); border-radius: 28px; background: rgba(255, 255, 255, 0.86); box-shadow: 0 10px 28px rgba(36, 48, 66, 0.08); text-decoration: none; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.kk-homepage-card:hover { transform: translateY(-4px); box-shadow: 0 15px 35px rgba(36, 48, 66, 0.12); }
.kk-homepage-card__image { display: block; margin-bottom: 1rem; border-radius: 18px; overflow: hidden; }
.kk-homepage-card__image img { display: block; width: 100%; height: auto; border: 1px solid rgba(36, 48, 66, 0.12); background: #ffffff; }
.kk-homepage-card__icon { display: grid; place-items: center; min-height: 100px; font-size: 2.5rem; background: rgba(36, 48, 66, 0.04); border-radius: 18px; color: #667085; }
.kk-homepage-card__content { flex: 1; display: flex; flex-direction: column; }
.kk-homepage-card__title { margin: 0 0 0.5rem; font-size: 1.25rem; font-weight: 800; letter-spacing: -0.03em; color: #243042; }
.kk-homepage-card__excerpt { flex: 1; color: #667085; font-size: 0.95rem; margin-bottom: 1rem; line-height: 1.5; }
.kk-homepage-card__download { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; border-radius: 999px; background: #72efb2; color: #053c2a; font-weight: 800; text-decoration: none; font-size: 0.9rem; }
@media (max-width: 1024px) { .kk-homepage-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .kk-homepage-grid { grid-template-columns: 1fr; } }
.kk-hero-cats { margin: 3rem 0; }
.kk-hero-cats__title { font-size: clamp(1.5rem, 4vw, 2.5rem); font-weight: 800; text-align: center; margin: 0 0 0.5rem; }
.kk-hero-cats__subtitle { text-align: center; color: #667085; margin: 0 0 2rem; font-size: 1.1rem; }
.kk-hero-cats__grid { display: grid; gap: 1.5rem; grid-template-columns: repeat(4, minmax(0, 1fr)); }
.kk-hero-cat-card { display: flex; flex-direction: column; border-radius: 24px; padding: 1.5rem; text-decoration: none; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.kk-hero-cat-card:hover { transform: translateY(-4px); }
.kk-hero-cat-card--pink { background: #fff0f3; border: 2px solid #ffb6c1; }
.kk-hero-cat-card--blue { background: #f0f9ff; border: 2px solid #a5d8ff; }
.kk-hero-cat-card--green { background: #f0fff4; border: 2px solid #b2f2bb; }
.kk-hero-cat-card--purple { background: #f8f0ff; border: 2px solid #d0bfff; }
.kk-hero-cat-card__visual { display: grid; place-items: center; min-height: 120px; margin-bottom: 1rem; border-radius: 16px; overflow: hidden; }
.kk-hero-cat-card__thumb img { display: block; width: 100%; height: 120px; object-fit: cover; border-radius: 14px; }
.kk-hero-cat-card__emoji { font-size: 2.5rem; }
.kk-hero-cat-card__body { display: flex; flex-direction: column; gap: 0.5rem; }
.kk-hero-cat-card__title { margin: 0; font-size: 1.2rem; font-weight: 800; color: #243042; }
.kk-hero-cat-card__count { color: #667085; font-size: 0.95rem; }
.kk-hero-cat-card__cta { display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 800; color: var(--kk-pink); font-size: 0.95rem; }
@media (max-width: 1024px) { .kk-hero-cats__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 640px) { .kk-hero-cats__grid { grid-template-columns: 1fr; } }
.kk-gallery-section-cats { margin-bottom: 3rem; }
.kk-gallery-section__title { font-size: 1.5rem; font-weight: 800; color: #243042; margin: 0 0 1.25rem; }
.kk-gallery-pagination { margin: 2rem 0 0; display: flex; justify-content: center; }
.kk-gallery-pagination .page-numbers { display: flex; gap: 6px; list-style: none; padding: 0; margin: 0; flex-wrap: wrap; }
.kk-gallery-pagination .page-numbers li { display: inline-block; }
.kk-gallery-pagination .page-numbers a, .kk-gallery-pagination .page-numbers span { display: inline-flex; align-items: center; justify-content: center; min-width: 42px; min-height: 42px; padding: 6px 14px; border-radius: 999px; font-weight: 700; text-decoration: none; transition: all 0.2s ease; }
.kk-gallery-pagination .page-numbers a { background: #fff; border: 2px solid #e5e7eb; color: #243042; }
.kk-gallery-pagination .page-numbers a:hover { border-color: #ffb6c1; background: #fff5f7; }
.kk-gallery-pagination .page-numbers .current { background: #ffb6c1; border: 2px solid #ffb6c1; color: #5a0f1e; }
.kk-gallery-pagination .page-numbers .dots { border: none; background: transparent; color: #94a3b8; }
';
 
	echo '<style id="kinderkleurplaten-inline-css">' . $css . '</style>' . "\n";
}

add_shortcode('kk_ultieme_galerij', 'kk_ultieme_galerij_shortcode');
function kk_ultieme_galerij_shortcode() {
	$search_input = '';
	if (isset($_GET['kk_zoek'])) {
		$search_input = sanitize_text_field(wp_unslash($_GET['kk_zoek']));
	}

	$query_args = array(
		'post_type'      => 'kleurplaten',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ($search_input !== '') {
		$query_args['s'] = $search_input;
	}

	$gallery_query = new WP_Query($query_args);

	ob_start();
	?>
	<div class="kk-gallery-shell">
		<form class="kk-gallery-search" method="get" action="<?php echo esc_url(get_permalink()); ?>" role="search">
			<input type="search" name="kk_zoek" value="<?php echo esc_attr($search_input); ?>" placeholder="<?php esc_attr_e('Zoek kleurplaten...', 'kinderkleurplaten'); ?>" aria-label="<?php esc_attr_e('Zoek kleurplaten', 'kinderkleurplaten'); ?>">
			<button type="submit"><?php esc_html_e('Zoeken', 'kinderkleurplaten'); ?></button>
		</form>

		<?php if ($gallery_query->have_posts()) : ?>
			<div class="kk-gallery-grid">
				<?php while ($gallery_query->have_posts()) : $gallery_query->the_post(); ?>
					<article class="kk-gallery-card" id="kk-kleurplaat-<?php echo esc_attr(get_the_ID()); ?>">
						<div class="kk-gallery-card__image-wrap">
							<?php the_post_thumbnail('full', array('class' => 'kk-gallery-card__image', 'loading' => 'lazy', 'decoding' => 'async')); ?>
						</div>
						<div class="kk-gallery-card__body">
							<h3 class="kk-gallery-card__title"><?php the_title(); ?></h3>
							<div class="kk-gallery-card__actions">
								<?php
								$print_url = trim(get_post_meta(get_the_ID(), 'kk_print_url', true));
								if ($print_url === '') {
									$print_url = get_permalink();
								}
								$svg_url = trim(get_post_meta(get_the_ID(), 'kk_svg_url', true));
								?>
								<a class="kk-button kk-button--print" href="<?php echo esc_url($print_url); ?>" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e('Print kleurplaat', 'kinderkleurplaten'); ?>
								</a>
								<?php if ($svg_url !== '') : ?>
									<a class="kk-button kk-button--svg" href="<?php echo esc_url($svg_url); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e('Download SVG', 'kinderkleurplaten'); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<p class="kk-empty-state"><?php esc_html_e('Geen kleurplaten gevonden', 'kinderkleurplaten'); ?></p>
		<?php endif; ?>
	</div>
	<?php
wp_reset_postdata();
    return ob_get_clean();
}

/**
 * Add meta box to mark kleurplaten as "uitgelicht" (featured).
 */
add_action('add_meta_boxes', 'kk_add_featured_metabox');
function kk_add_featured_metabox() {
    add_meta_box(
        'kk_featured_metabox',
        __('Uitgelichte kleurplaat', 'kinderkleurplaten'),
        'kk_featured_metabox_callback',
        'kleurplaten',
        'side',
        'high'
    );
}

function kk_featured_metabox_callback($post) {
    wp_nonce_field('kk_featured_metabox', 'kk_featured_metabox_nonce');
    $is_featured = get_post_meta($post->ID, '_kk_featured', true);
    ?>
    <p>
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input type="checkbox" name="kk_featured" value="1" <?php checked($is_featured, '1'); ?> style="width: 20px; height: 20px;">
            <span style="font-weight: 600;"><?php esc_html_e('Markeer als uitgelichte kleurplaat', 'kinderkleurplaten'); ?></span>
        </label>
    </p>
    <p class="description" style="margin-top: 8px; font-size: 12px; color: #666;">
        <?php esc_html_e('Uitgelichte kleurplaten worden getoond op de homepage in de "Uitgelichte kleurplaten" sectie.', 'kinderkleurplaten'); ?>
    </p>
    <?php
}

add_action('save_post_kleurplaten', 'kk_save_featured_metabox');
function kk_save_featured_metabox($post_id) {
    if (!isset($_POST['kk_featured_metabox_nonce']) || !wp_verify_nonce($_POST['kk_featured_metabox_nonce'], 'kk_featured_metabox')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $is_featured = isset($_POST['kk_featured']) ? '1' : '0';
    update_post_meta($post_id, '_kk_featured', $is_featured);
}

/**
 * Shortcode: [kleurplaten_uitgelicht] — toont uitgelichte kleurplaten.
 * Attributen:
 *  - limit (int) Aantal kleurplaten (default: 4)
 *  - columns (int) Aantal kolommen (default: 4)
 */
add_shortcode('kleurplaten_uitgelicht', 'kk_kleurplaten_uitgelicht_shortcode');
function kk_kleurplaten_uitgelicht_shortcode($atts = array()) {
    $atts = shortcode_atts(array(
        'limit'   => 4,
        'columns' => 4,
    ), $atts, 'kleurplaten_uitgelicht');

    $query = new WP_Query(array(
        'post_type'      => 'kleurplaten',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['limit']),
        'meta_key'       => '_kk_featured',
        'meta_value'     => '1',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    if (!$query->have_posts()) {
        return '';
    }

    $columns = max(1, min(6, intval($atts['columns'])));
    $grid_style = 'display:grid; gap:1.5rem; grid-template-columns:repeat(' . $columns . ', minmax(0, 1fr)); margin:2rem 0;';

    ob_start();
    ?>
    <section class="kk-uitgelicht" aria-label="<?php esc_attr_e('Uitgelichte kleurplaten', 'kinderkleurplaten'); ?>">
        <header class="kk-uitgelicht__header" style="margin-bottom: 1.5rem;">
            <h2 class="kk-uitgelicht__title" style="font-size: clamp(1.5rem, 4vw, 2.5rem); font-weight: 800; text-align: center; margin: 0 0 0.5rem;"><?php esc_html_e('Uitgelichte kleurplaten', 'kinderkleurplaten'); ?></h2>
            <p class="kk-uitgelicht__subtitle" style="text-align: center; color: #667085; margin: 0; font-size: 1.1rem;"><?php esc_html_e('Onze favoriete kleurplaten van het moment', 'kinderkleurplaten'); ?></p>
        </header>
        <div class="kk-homepage-grid" style="<?php echo esc_attr($grid_style); ?>">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <article class="kk-homepage-card" id="kk-kleurplaat-<?php echo esc_attr(get_the_ID()); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="kk-homepage-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium_large', array('loading' => 'lazy', 'decoding' => 'async')); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <div class="kk-homepage-card__content">
                        <h3 class="kk-homepage-card__title"><a href="<?php the_permalink(); ?>" style="color: inherit; text-decoration: none;"><?php the_title(); ?></a></h3>
                        <a class="kk-homepage-card__download" href="<?php the_permalink(); ?>">
                            <?php esc_html_e('Print kleurplaat', 'kinderkleurplaten'); ?>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php if ($query->found_posts > intval($atts['limit'])) : ?>
            <p style="text-align: center; margin-top: 1.5rem;">
                <a href="<?php echo esc_url(home_url('/kleurplaten/')); ?>" class="kk-button kk-button--print" style="display: inline-flex;">
                    <?php esc_html_e('Bekijk alle kleurplaten', 'kinderkleurplaten'); ?>
                </a>
            </p>
        <?php endif; ?>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

/**
 * Gastenboek Security Configuration
 */
define('KK_GASTENBOEK_RATE_LIMIT_WINDOW', 3600); // 1 hour in seconds
define('KK_GASTENBOEK_MAX_SUBMISSIONS_PER_IP', 3); // Max 3 submissions per hour per IP
define('KK_GASTENBOEK_MIN_SUBMIT_TIME', 5); // Minimum 5 seconds between form load and submit
define('KK_GASTENBOEK_BLOCKED_WORDS', 'viagra,cialis,casino,poker,betting,loan,credit,debt,mortgage,insurance,investment,bitcoin,crypto,forex,trading,pharmacy,drugs,porn,sex,xxx,adult,escort,dating,hookup,seo,backlink,backlinks,google,ranking,traffic,visitors,cheap,discount,free money,make money,work from home,earn cash,click here,buy now,order now,limited time,act now,guaranteed,no risk,risk free,miracle,cure,weight loss,lose weight,diet pill,male enhancement,penis enlargement,breast enlargement,anti-aging,wrinkle cream,hair loss,regrow hair');

/**
 * Get client IP address
 */
function kk_get_client_ip() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ips = explode(',', $_SERVER[$key]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return 'unknown';
}

/**
 * Check rate limit for IP
 */
function kk_check_rate_limit($ip) {
    $transient_key = 'kk_gb_ratelimit_' . sanitize_key($ip);
    $submissions = get_transient($transient_key);
    
    if ($submissions === false) {
        $submissions = 0;
    }
    
    if ($submissions >= KK_GASTENBOEK_MAX_SUBMISSIONS_PER_IP) {
        return false;
    }
    
    return true;
}

/**
 * Increment rate limit counter
 */
function kk_increment_rate_limit($ip) {
    $transient_key = 'kk_gb_ratelimit_' . sanitize_key($ip);
    $submissions = get_transient($transient_key);
    
    if ($submissions === false) {
        $submissions = 0;
    }
    
    $submissions++;
    set_transient($transient_key, $submissions, KK_GASTENBOEK_RATE_LIMIT_WINDOW);
}

/**
 * Check for spam content
 */
function kk_check_spam_content($text) {
    $blocked_words = explode(',', KK_GASTENBOEK_BLOCKED_WORDS);
    $text_lower = strtolower($text);
    
    foreach ($blocked_words as $word) {
        $word = trim($word);
        if ($word && strpos($text_lower, $word) !== false) {
            return true;
        }
    }
    
    // Check for excessive URLs
    $url_count = preg_match_all('/https?:\/\/\S+/', $text);
    if ($url_count > 1) {
        return true;
    }
    
    // Check for excessive repetition
    $words = str_word_count(strtolower($text), 1);
    if (count($words) > 10) {
        $word_freq = array_count_values($words);
        foreach ($word_freq as $word => $count) {
            if ($count > 5 && strlen($word) > 3) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Sanitize guestbook input strictly
 */
function kk_sanitize_gastenboek_input($input, $type = 'text') {
    // Remove null bytes
    $input = str_replace("\0", '', $input);
    
    // Remove control characters except newlines/tabs
    $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);
    
    if ($type === 'name') {
        // Name: only letters, spaces, hyphens, apostrophes, dots
        $input = preg_replace('/[^a-zA-ZÀ-ÿ\u0100-\u017F\s\-\'\.]/u', '', $input);
        $input = trim($input);
        // Limit length
        $input = mb_substr($input, 0, 50);
    } elseif ($type === 'message') {
        // Message: allow more characters but strip HTML
        $input = wp_strip_all_tags($input);
        $input = trim($input);
        $input = mb_substr($input, 0, 2000);
    }
    
    return $input;
}

/**
 * Generate server-side CAPTCHA challenge
 */
function kk_generate_captcha() {
    $num1 = random_int(1, 20);
    $num2 = random_int(1, 20);
    $operation = random_int(1, 3); // 1=add, 2=subtract, 3=multiply
    
    switch ($operation) {
        case 1:
            $answer = $num1 + $num2;
            $question = "$num1 + $num2";
            break;
        case 2:
            // Ensure positive result
            if ($num1 < $num2) { $temp = $num1; $num1 = $num2; $num2 = $temp; }
            $answer = $num1 - $num2;
            $question = "$num1 - $num2";
            break;
        case 3:
            // Keep numbers small for multiplication
            $num1 = random_int(1, 10);
            $num2 = random_int(1, 10);
            $answer = $num1 * $num2;
            $question = "$num1 × $num2";
            break;
    }
    
    // Store in session (transient with short expiry)
    $captcha_key = 'kk_gb_captcha_' . session_id();
    set_transient($captcha_key, $answer, 300); // 5 minutes
    
    return [
        'question' => $question,
        'answer' => $answer,
        'token' => wp_generate_password(32, false),
    ];
}

/**
 * Verify CAPTCHA answer
 */
function kk_verify_captcha($answer, $token) {
    $captcha_key = 'kk_gb_captcha_' . $token;
    $stored_answer = get_transient($captcha_key);
    
    // Delete after use (one-time)
    delete_transient($captcha_key);
    
    if ($stored_answer === false) {
        return false; // Expired or invalid token
    }
    
    return intval($answer) === intval($stored_answer);
}

/**
 * Handle guestbook submission with enhanced security
 */
add_action('init', 'kk_handle_gastenboek_submit');
function kk_handle_gastenboek_submit() {
    // Only process POST requests to the guestbook page
    if (!isset($_POST['kk_gastenboek_submit']) || $_POST['kk_gastenboek_submit'] !== '1') {
        return;
    }
    
    // Verify nonce
    if (!isset($_POST['kk_gastenboek_nonce']) || !wp_verify_nonce($_POST['kk_gastenboek_nonce'], 'kk_gastenboek_submit')) {
        wp_die(__('Ongeldig verzoek. Probeer het opnieuw.', 'kinderkleurplaten'), 403);
    }
    
    // Rate limiting
    $ip = kk_get_client_ip();
    if (!kk_check_rate_limit($ip)) {
        wp_die(__('Te veel pogingen. Probeer het over een uur opnieuw.', 'kinderkleurplaten'), 429);
    }
    
    // Honeypot field (should be empty)
    if (!empty($_POST['kk_gb_website'])) {
        // Bot detected - silently fail but don't increment rate limit
        wp_safe_redirect(add_query_arg('kk_gb_sent', '1', wp_get_referer() ?: home_url('/')));
        exit;
    }
    
    // Time-based validation (prevent too-fast submissions)
    $form_load_time = isset($_POST['kk_gb_form_time']) ? intval($_POST['kk_gb_form_time']) : 0;
    $submit_time = time();
    if ($form_load_time > 0 && ($submit_time - $form_load_time) < KK_GASTENBOEK_MIN_SUBMIT_TIME) {
        wp_die(__('Te snel verzonden. Wacht even en probeer opnieuw.', 'kinderkleurplaten'), 400);
    }
    
    // Get and sanitize inputs
    $name = kk_sanitize_gastenboek_input(wp_unslash($_POST['kk_gastenboek_name']), 'name');
    $message = kk_sanitize_gastenboek_input(wp_unslash($_POST['kk_gastenboek_message']), 'message');
    
    // Validate required fields
    if (empty($name) || empty($message)) {
        wp_die(__('Naam en bericht zijn verplicht.', 'kinderkleurplaten'), 400);
    }
    
    if (mb_strlen($name) < 2) {
        wp_die(__('Naam is te kort.', 'kinderkleurplaten'), 400);
    }
    
    if (mb_strlen($message) < 10) {
        wp_die(__('Bericht is te kort (minimaal 10 tekens).', 'kinderkleurplaten'), 400);
    }
    
    // Spam check
    if (kk_check_spam_content($name) || kk_check_spam_content($message)) {
        // Log attempt but don't reveal it's spam detection
        error_log('[GASTENBOEK] Spam blocked from IP: ' . $ip);
        wp_safe_redirect(add_query_arg('kk_gb_sent', '1', wp_get_referer() ?: home_url('/')));
        exit;
    }
    
    // Verify CAPTCHA
    $captcha_answer = isset($_POST['kk_captcha_answer']) ? intval($_POST['kk_captcha_answer']) : 0;
    $captcha_token = isset($_POST['kk_captcha_token']) ? sanitize_text_field($_POST['kk_captcha_token']) : '';
    
    if (!kk_verify_captcha($captcha_answer, $captcha_token)) {
        wp_die(__('Ongeldige controle. Probeer het opnieuw.', 'kinderkleurplaten'), 400);
    }
    
    // All checks passed - create post
    $post_id = wp_insert_post([
        'post_type'    => 'gastenboek_bericht',
        'post_status'  => 'pending',
        'post_title'   => $name,
        'post_content' => $message,
        'post_author'  => 0, // Guest author
    ], true);
    
    if (is_wp_error($post_id)) {
        error_log('[GASTENBOEK] Failed to create post: ' . $post_id->get_error_message());
        wp_die(__('Er is een fout opgetreden. Probeer het later opnieuw.', 'kinderkleurplaten'), 500);
    }
    
    // Store IP for moderation (optional, for admin review)
    update_post_meta($post_id, '_kk_gb_ip', $ip);
    update_post_meta($post_id, '_kk_gb_user_agent', substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255));
    
    // Increment rate limit
    kk_increment_rate_limit($ip);
    
    // Redirect with success
    $redirect_to = wp_get_referer() ?: home_url('/');
    wp_safe_redirect(add_query_arg('kk_gb_sent', '1', $redirect_to));
    exit;
}

/**
 * Guestbook shortcode with enhanced security
 */
add_shortcode('kk_gastenboek', 'kk_gastenboek_shortcode');
function kk_gastenboek_shortcode() {
    // Generate CAPTCHA
    $captcha = kk_generate_captcha();
    $form_time = time();
    
    ob_start();
    ?>
    <div class="kk-gastenboek-shell">
        <form class="kk-gastenboek-form" method="post" action="">
            <input type="hidden" name="kk_gastenboek_submit" value="1">
            <?php wp_nonce_field('kk_gastenboek_submit', 'kk_gastenboek_nonce'); ?>
            
            <!-- Honeypot field (hidden via CSS) -->
            <div class="kk-gb-honeypot" style="display:none !important; position:absolute; left:-9999px;" tabindex="-1" autocomplete="off">
                <label for="kk_gb_website"><?php esc_html_e('Website', 'kinderkleurplaten'); ?></label>
                <input type="text" name="kk_gb_website" id="kk_gb_website" value="" autocomplete="off">
            </div>
            
            <!-- Form load time for rate limiting -->
            <input type="hidden" name="kk_gb_form_time" value="<?php echo esc_attr($form_time); ?>">
            
            <label class="kk-gastenboek-field">
                <span><?php esc_html_e('Naam', 'kinderkleurplaten'); ?></span>
                <input type="text" name="kk_gastenboek_name" required maxlength="50" autocomplete="name">
            </label>
            <label class="kk-gastenboek-field">
                <span><?php esc_html_e('Bericht', 'kinderkleurplaten'); ?></span>
                <textarea name="kk_gastenboek_message" required maxlength="2000" rows="5" autocomplete="off"></textarea>
            </label>
            <label class="kk-gastenboek-field">
                <span><?php esc_html_e('Controle', 'kinderkleurplaten'); echo ' ' . esc_html($captcha['question']) . ' = ?'; ?></span>
                <input type="number" name="kk_captcha_answer" required autocomplete="off">
                <input type="hidden" name="kk_captcha_token" value="<?php echo esc_attr($captcha['token']); ?>">
            </label>
            <button class="kk-gastenboek-button" type="submit"><?php esc_html_e('Verstuur bericht', 'kinderkleurplaten'); ?></button>
        </form>

        <?php if (isset($_GET['kk_gb_sent'])) : ?>
            <p class="kk-gastenboek-notice"><?php esc_html_e('Bedankt! Je bericht wacht op moderatie.', 'kinderkleurplaten'); ?></p>
        <?php endif; ?>

        <div class="kk-gastenboek-list">
            <?php
            $messages_query = new WP_Query([
                'post_type'      => 'gastenboek_bericht',
                'post_status'    => 'publish',
                'posts_per_page' => 10,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]);

            if ($messages_query->have_posts()) :
                while ($messages_query->have_posts()) : $messages_query->the_post();
                    ?>
                    <article class="kk-gastenboek-card">
                        <h3><?php echo esc_html(get_the_title()); ?></h3>
                        <p><?php echo wp_kses_post(get_the_content()); ?></p>
                    </article>
                    <?php
                endwhile;
            else :
                ?>
                <article class="kk-gastenboek-card">
                    <p><?php esc_html_e('Er zijn nog geen goedgekeurde berichten.', 'kinderkleurplaten'); ?></p>
                </article>
                <?php
            endif;

            wp_reset_postdata();
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Admin: Send email notification on new guestbook entry
 */
add_action('transition_post_status', 'kk_gastenboek_notify_admin', 10, 3);
function kk_gastenboek_notify_admin($new_status, $old_status, $post) {
    if ($post->post_type !== 'gastenboek_bericht') {
        return;
    }
    
    // Only notify on new pending posts (not transitions from pending to publish)
    if ($new_status === 'pending' && $old_status !== 'pending') {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        $post_title = get_the_title($post->ID);
        $post_content = get_the_content(null, false, $post->ID);
        $ip = get_post_meta($post->ID, '_kk_gb_ip', true);
        $user_agent = get_post_meta($post->ID, '_kk_gb_user_agent', true);
        $edit_url = get_edit_post_link($post->ID);
        
        $subject = sprintf('[%s] Nieuw gastenboekbericht: %s', $site_name, $post_title);
        
        $message = "Er is een nieuw gastenboekbericht ontvangen dat wacht op moderatie.\n\n";
        $message .= "Naam: {$post_title}\n";
        $message .= "IP: {$ip}\n";
        $message .= "User Agent: {$user_agent}\n\n";
        $message .= "Bericht:\n{$post_content}\n\n";
        $message .= "Moderatie: {$edit_url}\n";
        
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        
        @wp_mail($admin_email, $subject, $message, $headers);
    }
}

/**
 * Admin: Add IP column to guestbook posts list
 */
add_filter('manage_edit-gastenboek_bericht_columns', 'kk_gastenboek_admin_columns');
function kk_gastenboek_admin_columns($columns) {
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['kk_gb_ip'] = 'IP Adres';
            $new_columns['kk_gb_date'] = 'Datum';
        }
    }
    return $new_columns;
}

add_action('manage_gastenboek_bericht_posts_custom_column', 'kk_gastenboek_admin_column_content', 10, 2);
function kk_gastenboek_admin_column_content($column, $post_id) {
    switch ($column) {
        case 'kk_gb_ip':
            echo esc_html(get_post_meta($post_id, '_kk_gb_ip', true) ?: 'Onbekend');
            break;
        case 'kk_gb_date':
            echo get_the_date('d-m-Y H:i', $post_id);
            break;
    }
}

/**
 * Admin: Add quick actions for guestbook moderation
 */
add_filter('post_row_actions', 'kk_gastenboek_row_actions', 10, 2);
function kk_gastenboek_row_actions($actions, $post) {
    if ($post->post_type !== 'gastenboek_bericht') {
        return $actions;
    }
    
    if ($post->post_status === 'pending') {
        $actions['approve'] = '<a href="' . wp_nonce_url(admin_url("post.php?action=approve&post={$post->ID}"), 'approve-post_' . $post->ID) . '">Goedkeuren</a>';
        $actions['spam'] = '<a href="' . wp_nonce_url(admin_url("post.php?action=spam&post={$post->ID}"), 'spam-post_' . $post->ID) . '" style="color:#d63636;">Spam markeren</a>';
    } elseif ($post->post_status === 'publish') {
        $actions['unapprove'] = '<a href="' . wp_nonce_url(admin_url("post.php?action=unapprove&post={$post->ID}"), 'unapprove-post_' . $post->ID) . '">Ongedaan maken</a>';
    }
    
    return $actions;
}

if (!function_exists('kinderkleurplaten_gallery_shortcode')) {
	function kinderkleurplaten_gallery_shortcode($atts = array()) {
		if (function_exists('kk_ultieme_galerij_shortcode')) {
			return kk_ultieme_galerij_shortcode(array());
		}

		return '';
	}
}

function kinderkleurplaten_breadcrumbs() {
    // Veilige fallback om de crash te voorkomen. 
    echo '<div class="kk-breadcrumbs" style="font-size: 14px; margin-bottom: 20px; color: #666;"><a href="' . home_url() . '" style="color: #ffb6c1; text-decoration: none;">Home</a> &raquo; ' . esc_html(get_the_title()) . '</div>';
}

/**
 * Verwijdert herhaalde SEO-intro- en footer-blokken uit de content van
 * kleurplaat-posts.
 *
 * Root-cause: bulk_categorize_existing.py prependde de SEO-blok meermaals
 * aan dezelfde post (omdat het de 'rendered' content gebruikte). Resultaat:
 * twee (of meer) identieke <h2>+<p> intro's bovenaan en identieke "Zie ook"
 * / "Bekijk"-footers.
 *
 * Aanpak:
 *  1. Verwijder alle <h2 class="seo-subtitle">...</h2><p>...</p> intro-
 *     blocen NA de eerste.
 *  2. Verwijder alle <p style="...">...</p> met "Zie ook" / "Bekijk" NA de
 *     eerste.
 *  3. Verwijder het eerste <img> (de featured image wordt al apart getoond).
 *
 * Werkt op HTML-niveau (na wpautop, priority 12).
 */
if (!function_exists('kk_deduplicate_kleurplaat_content')) {

    /**
     * Verwijdert ALLE <h2 class="seo-subtitle">...</h2><p>...</p> blokken
     * behalve de eerste. Matcht over meerdere regels heen (DOTALL).
     */
    function kk_strip_duplicate_intro_blocks($content) {
        if (empty($content)) {
            return $content;
        }

        // Match één of meer intro-blokken achter elkaar.
        // Elk blok = <h2 class="seo-subtitle">...</h2><spatie><p>...</p>
        // We verwijderen alles behalve het allereerste blok.
        $first_match = 0;
        $pattern     = '/\s*<h2\s+class=["\']seo-subtitle["\'][^>]*>.*?<\/h2>\s*<p[^>]*>.*?<\/p>/si';

        return preg_replace_callback($pattern, function ($m) use (&$first_match) {
            $first_match++;
            // Bewaar de eerste, verwijder de rest.
            return ($first_match === 1) ? $m[0] : '';
        }, $content);
    }

    /**
     * Verwijdert ALLE <p style="...">...Zie ook / Bekijk...</p> blokken
     * behalve de eerste.
     */
    function kk_strip_duplicate_footer_blocks($content) {
        if (empty($content)) {
            return $content;
        }

        $first_match = 0;
        // Match <p ...style...> met tekst die begint met "Zie ook" of "Bekijk".
        $pattern = '/\s*<p[^>]*style=["\'][^"\']*["\'][^>]*>\s*(?:Zie ook|Bekijk).*?<\/p>/si';

        return preg_replace_callback($pattern, function ($m) use (&$first_match) {
            $first_match++;
            return ($first_match === 1) ? $m[0] : '';
        }, $content);
    }

    /**
     * Extra veiligheidsnet: verwijdert <p>...</p> zonder style die met
     * "Zie ook onze andere" of "Bekijk onze" beginnen (voor gevallen waar
     * het style-attribuut ontbreekt).
     */
    function kk_strip_duplicate_plain_footers($content) {
        if (empty($content)) {
            return $content;
        }

        $first_match = 0;
        $pattern     = '/\s*<p[^>]*>\s*(?:Zie ook onze andere|Bekijk onze galerij).*?<\/p>/si';

        return preg_replace_callback($pattern, function ($m) use (&$first_match) {
            $first_match++;
            return ($first_match === 1) ? $m[0] : '';
        }, $content);
    }
}

// Priority 8: vóór wpautop, werkt op de rauwe post_content.
// Verwijdert het eerste <img> dat al via the_post_thumbnail() wordt getoond.
add_filter('the_content', 'kk_kleurplaat_content_pre', 8);
function kk_kleurplaat_content_pre($content) {
    if (!is_singular('kleurplaten') || empty($content)) {
        return $content;
    }
    return preg_replace('/<img[^>]*>/i', '', $content, 1);
}

/**
 * Verwijdert featured-image <img>-tags die door WPCode of andere plugins
 * aan het begin van de content worden geïnjecteerd (via "Insert Before
 * Content"). Deze injectie bevat:
 *   1. Een <div> met een <img> die dezelfde URL heeft als de featured
 *      image van de post.
 *   2. Een <script> met de functie printOfDownload.
 *   3. Een <button> met onclick="printOfDownload(...)".
 *   4. Een <br> tag.
 *
 * De featured image wordt al getoond via the_post_thumbnail() in single.php,
 * dus deze dubbele moeten eruit.
 *
 * Werkt op HTML-niveau (na wpautop, priority 10+). Prioriteit 20 zodat
 * we na mogelijke WPCode auto-inserts draaien.
 */
if (!function_exists('kk_strip_featured_image_injection')) {
    function kk_strip_featured_image_injection($content) {
        if (!is_singular('kleurplaten') || empty($content)) {
            return $content;
        }

        global $post;

        // 1) Verwijder het inline <script> blok dat de printOfDownload-functie
        //    definieert (plugin-injectie).
        $content = preg_replace(
            '#<script>\s*function\s+printOfDownload\b.*?</script>\s*#si',
            '',
            $content
        );

        // 2) Verwijder de <br> tag die voor de button werd gezet.
        $content = preg_replace('#\s*<br\s*/?>\s*#i', "\n", $content);

        // 3) Verwijder de <button onclick="printOfDownload(...)">...</button>.
        $content = preg_replace(
            '#\s*<button[^>]*onclick=["\']printOfDownload\([^)]*\)["\'][^>]*>.*?</button>\s*#si',
            '',
            $content
        );

        // Haal de featured image URL op om gerichte img-stripping te doen.
        $thumb_id  = get_post_thumbnail_id($post);
        if ($thumb_id) {
            $thumb_url = wp_get_attachment_url($thumb_id);
            if ($thumb_url) {
                $thumb_url_escaped = preg_quote($thumb_url, '/');

                // 4) Verwijder ALLE <img>-tags met dezelfde src als de featured image.
                $content = preg_replace(
                    '/<img\b[^>]*\bsrc=["\']' . $thumb_url_escaped . '["\'][^>]*>\s*/i',
                    '',
                    $content
                );
            }
        }

        // 5) Verwijder lege <div>s die achterbleven (bijv. wrappers van de injectie).
        $content = preg_replace('/<div[^>]*>\s*<\/div>\s*/i', '', $content);

        return $content;
    }
}
add_filter('the_content', 'kk_strip_featured_image_injection', 20);

// Priority 12: ná wpautop, werkt op de verwerkte HTML.
add_filter('the_content', 'kk_kleurplaat_content_dedup', 12);
function kk_kleurplaat_content_dedup($content) {
    if (!is_singular('kleurplaten') || empty($content)) {
        return $content;
    }

    $content = kk_strip_duplicate_intro_blocks($content);
    $content = kk_strip_duplicate_footer_blocks($content);
    $content = kk_strip_duplicate_plain_footers($content);

    // Ruim lege <p>-tags op die eventueel achterbleven.
    $content = preg_replace('/<p[^>]*>\s*<\/p>\s*/', '', $content);

    // Meerdere lege regels terugbrengen naar max. 2.
    $content = preg_replace('/(\s*\n\s*){3,}/', "\n\n", $content);

    return trim($content);
}


add_shortcode('print_kleurplaat', 'kk_print_kleurplaat_shortcode');
function kk_print_kleurplaat_shortcode() {
    if (!is_singular('kleurplaten')) {
        return '';
    }

    $print_url = trim(get_post_meta(get_the_ID(), 'kk_print_url', true));
    if (empty($print_url)) {
        return '';
    }

    // Store print URL in a global for the footer script
    global $kk_print_urls;
    if (!is_array($kk_print_urls)) {
        $kk_print_urls = array();
    }
    $kk_print_urls[] = $print_url;
    
    return '<button type="button" class="kk-print-button" data-print-url="' . esc_url($print_url) . '">' . esc_html__('🖨️ Print deze Kleurplaat', 'kinderkleurplaten') . '</button>';
}

// Output print script in footer
add_action('wp_footer', 'kk_print_script_footer');
function kk_print_script_footer() {
    global $kk_print_urls;
    if (empty($kk_print_urls)) {
        return;
    }
    ?>
<script>
(function(){
function initPrintButtons(){
document.querySelectorAll('.kk-print-button').forEach(function(button){
button.addEventListener('click',function(e){
e.preventDefault();
var imageUrl=this.getAttribute('data-print-url');
if(imageUrl){printColoringPage(imageUrl);}
});
});
}
function printColoringPage(imageUrl){
var existingFrame=document.getElementById('kk-print-frame');
if(existingFrame){existingFrame.remove();}
var printFrame=document.createElement('iframe');
printFrame.id='kk-print-frame';
printFrame.style.cssText='position:fixed;left:-9999px;top:-9999px;width:0;height:0;border:0;visibility:hidden;';
document.body.appendChild(printFrame);
var printContent='<!DOCTYPE html><html lang="nl"><head><meta charset="UTF-8"><title>Kleurplaat Afdrukken</title><style>@page { margin: 0; size: auto; } body { margin: 0; padding: 20mm; background: white; display: flex; justify-content: center; align-items: center; min-height: 100vh; } img { max-width: 100%; max-height: 100vh; width: auto; height: auto; }</style></head><body><img src="'+imageUrl+'" onload="window.print()"></body></html>';
printFrame.contentDocument.open();
printFrame.contentDocument.write(printContent);
printFrame.contentDocument.close();
setTimeout(function(){
if(printFrame.contentWindow){printFrame.contentWindow.focus();printFrame.contentWindow.print();}
},100);
setTimeout(function(){
var f=document.getElementById('kk-print-frame');
if(f){f.remove();}
},5000);
}
document.addEventListener('DOMContentLoaded',initPrintButtons);
})();
</script>
<?php
}

if (!function_exists('kk_get_category_dynamic_thumbnail')) {
    /**
     * Haalt de featured image op van de meest recente kleurplaat in de
     * opgegeven categorie die daadwerkelijk een thumbnail heeft.
     * Gebruikt eerst het opgeslagen termmeta thumbnail_id; anders een
     * WP_Query fallback. Wordt gebruikt als dynamisch logo / thumbnail op
     * categoriekaarten in plaats van een statisch icoon.
     *
     * Werkt op de officiële custom taxonomy 'kleurplaat_categorie' die aan
     * het CPT 'kleurplaten' is gekoppeld.
     *
     * @param int    $term_id  ID van de kleurplaat_categorie term.
     * @param string $size     Gewenste WP image size (default: 'medium').
     * @return string HTML <img> of lege string.
     */
    function kk_get_category_dynamic_thumbnail($term_id, $size = 'medium') {
        $term_id = (int) $term_id;
        if ($term_id <= 0) {
            return '';
        }

        // 1. Gebruik het opgeslagen termmeta thumbnail_id indien geldig.
        $term_thumb_id = get_term_meta($term_id, 'thumbnail_id', true);
        if ($term_thumb_id) {
            $image = wp_get_attachment_image((int) $term_thumb_id, $size, false, array(
                'alt'     => get_term_meta($term_id, 'name', true) ?: '',
                'loading' => 'lazy',
            ));
            if ($image) {
                return $image;
            }
        }

        // 2. Fallback: meest recente kleurplaat MET een thumbnail.
        $query = new WP_Query(array(
            'post_type'      => 'kleurplaten',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'kleurplaat_categorie',
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                    'include_children' => true,
                ),
            ),
            'meta_query'     => array(
                array(
                    'key'     => '_thumbnail_id',
                    'value'   => '',
                    'compare' => '!=',
                ),
            ),
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ));

        if ($query->have_posts()) {
            $first_post_id = $query->posts[0];
            $thumbnail_id = get_post_thumbnail_id($first_post_id);

            if ($thumbnail_id) {
                $image = wp_get_attachment_image($thumbnail_id, $size, false, array(
                    'alt'     => get_the_title($first_post_id),
                    'loading' => 'lazy',
                ));
                wp_reset_postdata();
                return $image;
            }
        }

        wp_reset_postdata();
        return '';
    }
}

if (!function_exists('kk_get_deduplicated_terms')) {
    /**
     * Haalt alle termen op uit de custom taxonomy 'kleurplaat_categorie'
     * (hoofd- en subcategorieën, geen limiet). Wordt gebruikt voor de
     * categorie-overzichten op de homepage en /kleurplaten/.
     *
     * @param array $args Optionele extra WP_Term_Query args.
     * @return array Array van WP_Term objecten.
     */
    function kk_get_deduplicated_terms($args = array()) {
        $defaults = array(
            'taxonomy'   => 'kleurplaat_categorie',
            'hide_empty' => true,
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => 0, // 0 = geen limiet, haal ALLE termen op.
        );
        $args = wp_parse_args($args, $defaults);

        // Geen parent-filter -> zowel hoofd- als subcategorieën worden opgehaald.

        $terms = get_terms($args);
        if (empty($terms) || is_wp_error($terms)) {
            return array();
        }

        return $terms;
    }
}

if (!function_exists('kk_get_all_categories_with_thumbnails')) {
    /**
     * PERFORMANCE-OPTIMIZED: Haalt alle categorieën + hun eerste kleurplaat-thumbnail
     * op in één batch en cached het resultaat in een WordPress transient.
     *
     * Gebruikt eerst het opgeslagen termmeta thumbnail_id (meegegeven door de
     * self-healing mu-plugins). Als die ontbreekt of ongeldig is, wordt de
     * meest recente kleurplaat met een geldige thumbnail gevonden via één
     * enkele SQL-query (geen N+1 WP_Query per term).
     *
     * Cache TTL: 12 uur (43200 seconden).
     *
     * @param string $image_size WP image size voor thumbnails (default: 'medium_large').
     * @return array Array van ['term' => WP_Term, 'thumbnail_html' => string].
     */
    function kk_get_all_categories_with_thumbnails($image_size = 'medium_large') {
        $transient_key = 'kk_categories_with_thumbs_' . md5($image_size);
        $transient_ttl = 12 * HOUR_IN_SECONDS; // 12 uur

        // Probeer uit cache te laden.
        $cached = get_transient($transient_key);
        if ($cached !== false) {
            return $cached;
        }

        // Cache miss: bouw de data op.
        $terms = kk_get_deduplicated_terms();
        if (empty($terms)) {
            return array();
        }

        // Indexeer termen op term_id voor snelle lookup.
        $terms_by_id = array();
        foreach ($terms as $term) {
            $terms_by_id[(int) $term->term_id] = $term;
        }

        // 1. Verzamel opgeslagen termmeta thumbnail_id in één query.
        $term_thumbs = array();
        if (!empty($terms_by_id)) {
            global $wpdb;
            $term_ids = array_keys($terms_by_id);
            $placeholders = implode(',', array_fill(0, count($term_ids), '%d'));
            $meta_rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT term_id, meta_value FROM {$wpdb->termmeta}
                     WHERE meta_key = 'thumbnail_id'
                       AND term_id IN ($placeholders)
                       AND meta_value != ''
                       AND meta_value != '0'",
                    $term_ids
                )
            );
            foreach ($meta_rows as $row) {
                $term_thumbs[(int) $row->term_id] = (int) $row->meta_value;
            }
        }

        // 2. Voor termen zonder termmeta: vind de meest recente kleurplaat MET
        //    een thumbnail in één SQL query. We laden alle relevante rijen en
        //    houden in PHP alleen de eerste per term_id (dankzij ORDER BY).
        $fallback_thumbs = array();
        $missing_term_ids = array_diff(array_keys($terms_by_id), array_keys($term_thumbs));

        if (!empty($missing_term_ids)) {
            global $wpdb;
            $placeholders = implode(',', array_fill(0, count($missing_term_ids), '%d'));
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT tt.term_id, p.ID AS post_id, CAST(pm.meta_value AS UNSIGNED) AS thumbnail_id
                     FROM {$wpdb->term_taxonomy} tt
                     INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
                     INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
                         AND p.post_type = 'kleurplaten'
                         AND p.post_status = 'publish'
                     INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
                         AND pm.meta_key = '_thumbnail_id'
                         AND pm.meta_value != ''
                         AND pm.meta_value != '0'
                     WHERE tt.taxonomy = 'kleurplaat_categorie'
                       AND tt.term_id IN ($placeholders)
                     ORDER BY tt.term_id ASC, p.post_date DESC",
                    $missing_term_ids
                )
            );

            foreach ($rows as $row) {
                $tid = (int) $row->term_id;
                if (!isset($fallback_thumbs[$tid])) {
                    $fallback_thumbs[$tid] = array(
                        'post_id'      => (int) $row->post_id,
                        'thumbnail_id' => (int) $row->thumbnail_id,
                    );
                }
            }
        }

        // 3. Bouw het resultaat: HTML per term.
        $result = array();
        foreach ($terms as $term) {
            $term_id = (int) $term->term_id;
            $thumbnail_html = '';

            if (isset($term_thumbs[$term_id])) {
                $thumbnail_html = wp_get_attachment_image($term_thumbs[$term_id], $image_size, false, array(
                    'alt'     => $term->name,
                    'loading' => 'lazy',
                ));
            } elseif (isset($fallback_thumbs[$term_id])) {
                $thumbnail_html = wp_get_attachment_image($fallback_thumbs[$term_id]['thumbnail_id'], $image_size, false, array(
                    'alt'     => get_the_title($fallback_thumbs[$term_id]['post_id']),
                    'loading' => 'lazy',
                ));
            }

            $result[] = array(
                'term'           => $term,
                'thumbnail_html' => $thumbnail_html,
            );
        }

        // Sla op in transient voor de volgende 12 uur.
        set_transient($transient_key, $result, $transient_ttl);

        return $result;
    }
}

if (!function_exists('kk_invalidate_category_cache')) {
    /**
     * Wis alle categorie-thumbnail transients.
     * Wordt aangeroepen via cache invalidation hooks bij content-wijzigingen.
     */
    function kk_invalidate_category_cache() {
        global $wpdb;

        // Wis transients voor de bekende image sizes.
        delete_transient('kk_categories_with_thumbs_' . md5('medium_large'));
        delete_transient('kk_categories_with_thumbs_' . md5('medium'));
        delete_transient('kk_categories_with_thumbs_' . md5('thumbnail'));

        // Wis ook eventuele transients voor andere image sizes via directe DB-query.
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_kk_categories_with_thumbs_%'
                OR option_name LIKE '_transient_timeout_kk_categories_with_thumbs_%'"
        );
    }
}

// Cache invalidation hooks.
add_action('save_post_kleurplaten', 'kk_invalidate_category_cache', 10, 3);
add_action('delete_post', 'kk_invalidate_category_cache');
add_action('created_kleurplaat_categorie', 'kk_invalidate_category_cache');
add_action('edited_kleurplaat_categorie', 'kk_invalidate_category_cache');
add_action('delete_kleurplaat_categorie', 'kk_invalidate_category_cache');
add_action('set_object_terms', 'kk_invalidate_category_cache', 10, 6);

// REST endpoint om cache te wissen vanuit het bulk categorisatie script.
add_action('rest_api_init', function() {
    register_rest_route('kk/v1', '/flush-cache', array(
        'methods'             => 'POST',
        'callback'            => function(\WP_REST_Request $request) {
            // Verifieer dat de requester geautoriseerd is.
            if (!current_user_can('manage_options')) {
                return new \WP_Error('rest_forbidden', 'Unauthorized', array('status' => 403));
            }

            kk_invalidate_category_cache();

            return array('success' => true, 'message' => 'Category cache flushed');
        },
        'permission_callback' => function() {
            return current_user_can('manage_options');
        },
    ));
});

/**
 * Create essential pages and menu items on theme setup.
 * Runs once when the theme is activated or on init if pages don't exist.
 */
add_action('after_switch_theme', 'kk_create_essential_pages');
add_action('init', 'kk_create_essential_pages');

function kk_create_essential_pages() {
    // Only run once - check if pages already exist
    if (get_option('kinderkleurplaten_pages_created', false)) {
        return;
    }

    $pages = array(
        'gastenboek' => array(
            'title'   => 'Gastenboek',
            'slug'    => 'gastenboek',
            'content' => '[kk_gastenboek]',
        ),
    );

    foreach ($pages as $slug => $page_data) {
        // Check if page already exists
        $existing_page = get_page_by_path($slug);
        if (!$existing_page) {
            $page_id = wp_insert_post(array(
                'post_title'   => $page_data['title'],
                'post_name'    => $page_data['slug'],
                'post_content' => $page_data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ));

            if (!is_wp_error($page_id)) {
                // Store page ID for menu creation
                update_option("kinderkleurplaten_page_{$slug}_id", $page_id);
            }
        } else {
            // Update existing page with shortcode if needed
            if (strpos($existing_page->post_content, '[kk_gastenboek]') === false) {
                wp_update_post(array(
                    'ID'           => $existing_page->ID,
                    'post_content' => $page_data['content'],
                ));
            }
            update_option("kinderkleurplaten_page_{$slug}_id", $existing_page->ID);
        }
    }

    // Create or update primary menu
    kk_setup_primary_menu();

    // Mark as done
    update_option('kinderkleurplaten_pages_created', true);
}

/**
 * Set up the primary navigation menu with required items.
 */
function kk_setup_primary_menu() {
    $menu_name = 'Hoofdmenu';

    // Get or create the menu
    $menu = wp_get_nav_menu_object($menu_name);
    if (!$menu) {
        $menu_id = wp_create_nav_menu($menu_name);
        if (is_wp_error($menu_id)) {
            return;
        }
        $menu = wp_get_nav_menu_object($menu_name);
    }

    $menu_id = $menu->term_id;

    // Define menu items in order
    $menu_items = array(
        array(
            'title' => 'Home',
            'url'   => home_url('/'),
            'order' => 1,
        ),
        array(
            'title' => 'Kleurplaten',
            'url'   => home_url('/kleurplaten/'),
            'order' => 2,
        ),
        array(
            'title' => 'Gastenboek',
            'url'   => home_url('/gastenboek/'),
            'order' => 3,
        ),
    );

    // Clear existing menu items for this menu
    $existing_items = wp_get_nav_menu_items($menu_id);
    if ($existing_items) {
        foreach ($existing_items as $item) {
            wp_delete_post($item->ID, true);
        }
    }

    // Add new menu items
    foreach ($menu_items as $item_data) {
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title'     => $item_data['title'],
            'menu-item-url'       => $item_data['url'],
            'menu-item-status'    => 'publish',
            'menu-item-type'      => 'custom',
            'menu-item-position'  => $item_data['order'],
        ));
    }

    // Assign menu to primary location
    $locations = get_theme_mod('nav_menu_locations');
    $locations['primary'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}

/**
 * Shortcode: [kleurplaat_categories] — toont categoriekaarten uit
 * 'kleurplaat_categorie' (hoofd- én subcategorieën, geen limiet).
 *
 * PERFORMANCE: Gebruikt kk_get_all_categories_with_thumbnails() met
 * transient caching (12u TTL) om N+1 queries te elimineren.
 *
 * Attributen:
 *  - number   (int) Aantal weer te geven kaarten. 0 of <0 = alles (default).
 *  - columns  (int) Aantal kolommen (default: 4).
 *  - dynamic_thumb (bool) Of dynamische thumbnail getoond moet worden (default ja).
 */
add_shortcode('kleurplaat_categories', 'kk_kleurplaat_categories_shortcode');
function kk_kleurplaat_categories_shortcode($atts = array()) {
    $atts = shortcode_atts(array(
        'number'        => 0,
        'columns'       => 4,
        'dynamic_thumb' => 1,
    ), $atts, 'kleurplaat_categories');

    $use_thumb = (int) $atts['dynamic_thumb'] !== 0;

    // Gebruik de gecachte versie als thumbnails aan staan.
    if ($use_thumb) {
        $cached_terms = kk_get_all_categories_with_thumbnails('medium_large');
    } else {
        $terms = kk_get_deduplicated_terms();
        $cached_terms = array();
        foreach ($terms as $term) {
            $cached_terms[] = array('term' => $term, 'thumbnail_html' => '');
        }
    }

    if (empty($cached_terms)) {
        return '';
    }

    $number = (int) $atts['number'];
    if ($number > 0 && count($cached_terms) > $number) {
        $cached_terms = array_slice($cached_terms, 0, $number);
    }

    $columns    = max(1, (int) $atts['columns']);
    $grid_style = 'display:grid; gap:1.5rem; grid-template-columns:repeat(' . $columns . ', minmax(0, 1fr)); margin:2rem 0;';

    ob_start();
    ?>
    <div class="kk-homepage-grid" style="<?php echo esc_attr($grid_style); ?>">
        <?php foreach ($cached_terms as $item) :
            $term           = $item['term'];
            $thumbnail_html = $item['thumbnail_html'];
        ?>
            <a href="<?php echo esc_url(get_term_link($term)); ?>" class="kk-homepage-card">
                <div class="kk-homepage-card__image">
                    <?php if ($thumbnail_html) : ?>
                        <?php echo $thumbnail_html; ?>
                    <?php elseif (trim($term->description)) : ?>
                        <div class="kk-homepage-card__icon"><?php echo esc_html($term->description); ?></div>
                    <?php endif; ?>
                </div>
                <div class="kk-homepage-card__content">
                    <h3 class="kk-homepage-card__title"><?php echo esc_html($term->name); ?></h3>
                    <?php if ($term->count > 0) : ?>
                        <p class="kk-homepage-card__excerpt">
                            <?php printf(_n('%s kleurplaat', '%s kleurplaten', $term->count, 'kinderkleurplaten'), $term->count); ?>
                        </p>
                    <?php endif; ?>
                    <span class="kk-homepage-card__download"><?php esc_html_e('Bekijk categorie', 'kinderkleurplaten'); ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('kk_hero_categorieën', 'kk_hero_categorieen_shortcode');
function kk_hero_categorieen_shortcode($atts = array()) {
    $atts = shortcode_atts(array(
        'count' => 4,
    ), $atts, 'kk_hero_categorieën');

    // Gebruik de gecachte functie om N+1 queries te elimineren.
    $cached_terms = kk_get_all_categories_with_thumbnails('medium_large');
    $cached_terms = array_slice($cached_terms, 0, intval($atts['count']));

    if (empty($cached_terms)) {
        return '';
    }

    $accents = array('pink', 'blue', 'green', 'purple');

    ob_start();
    ?>
    <section class="kk-hero-cats" aria-label="<?php esc_attr_e('Uitgelichte categorieën', 'kinderkleurplaten'); ?>">
        <h2 class="kk-hero-cats__title"><?php esc_html_e('Ontdek onze kleurplaten', 'kinderkleurplaten'); ?></h2>
        <p class="kk-hero-cats__subtitle"><?php esc_html_e('Kies een categorie en begin met kleuren!', 'kinderkleurplaten'); ?></p>
        <div class="kk-hero-cats__grid">
            <?php foreach ($cached_terms as $index => $item) :
                $term           = $item['term'];
                $thumbnail_html = $item['thumbnail_html'];
                $accent         = $accents[$index % count($accents)];
            ?>
                <a href="<?php echo esc_url(get_term_link($term)); ?>" class="kk-hero-cat-card kk-hero-cat-card--<?php echo esc_attr($accent); ?>">
                    <div class="kk-hero-cat-card__visual">
                        <?php if ($thumbnail_html) : ?>
                            <div class="kk-hero-cat-card__thumb"><?php echo $thumbnail_html; ?></div>
                        <?php elseif (trim($term->description)) : ?>
                            <span class="kk-hero-cat-card__emoji"><?php echo esc_html($term->description); ?></span>
                        <?php else : ?>
                            <span class="kk-hero-cat-card__emoji">🎨</span>
                        <?php endif; ?>
                    </div>
                    <div class="kk-hero-cat-card__body">
                        <h3 class="kk-hero-cat-card__title"><?php echo esc_html($term->name); ?></h3>
                        <?php if ($term->count > 0) : ?>
                            <span class="kk-hero-cat-card__count">
                                <?php printf(_n('%s kleurplaat', '%s kleurplaten', $term->count, 'kinderkleurplaten'), $term->count); ?>
                            </span>
                        <?php endif; ?>
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
    <?php
    return ob_get_clean();
}

add_shortcode('kleurplaat_examples', 'kk_kleurplaat_examples_shortcode');
function kk_kleurplaat_examples_shortcode($atts = array()) {
    $atts = shortcode_atts(array(
        'limit' => 6,
    ), $atts, 'kleurplaat_examples');

    $query = new WP_Query(array(
        'post_type'      => 'kleurplaten',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['limit']),
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    if (!$query->have_posts()) {
        return '';
    }

    ob_start();
    ?>
    <div class="kk-homepage-grid">
        <?php while ($query->have_posts()) : $query->the_post(); ?>
            <article class="kk-homepage-card" id="kk-kleurplaat-<?php echo esc_attr(get_the_ID()); ?>">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="kk-homepage-card__image">
                        <?php the_post_thumbnail('medium'); ?>
                    </div>
                <?php endif; ?>
                <div class="kk-homepage-card__content">
                    <h3 class="kk-homepage-card__title"><?php the_title(); ?></h3>
                    <a class="kk-homepage-card__download" href="<?php the_permalink(); ?>">
                        <?php esc_html_e('Print kleurplaat', 'kinderkleurplaten'); ?>
                    </a>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

add_shortcode('kk_galerij_volledig', 'kk_galerij_volledig_shortcode');
function kk_galerij_volledig_shortcode($atts = array()) {
    $atts = shortcode_atts(array(
        'per_page' => 12,
    ), $atts, 'kk_galerij_volledig');

    $paged = max(1, get_query_var('paged', 1));
    if ($paged < 2 && isset($_GET['paged'])) {
        $paged = max(1, intval($_GET['paged']));
    }

    // PERFORMANCE: Gebruik gecachte categorieën met thumbnails.
    $cached_categories = kk_get_all_categories_with_thumbnails('medium_large');

    $gallery_query = new WP_Query(array(
        'post_type'      => 'kleurplaten',
        'post_status'    => 'publish',
        'posts_per_page' => intval($atts['per_page']),
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    ob_start();
    ?>
    <div class="kk-gallery-shell">

        <section class="kk-gallery-section-cats">
            <h2 class="kk-gallery-section__title"><?php esc_html_e('Categorieën', 'kinderkleurplaten'); ?></h2>
            <?php if (!empty($cached_categories)) : ?>
                <div class="kk-homepage-grid">
                    <?php foreach ($cached_categories as $item) :
                        $term           = $item['term'];
                        $thumbnail_html = $item['thumbnail_html'];
                    ?>
                        <a href="<?php echo esc_url(get_term_link($term)); ?>" class="kk-homepage-card">
                            <div class="kk-homepage-card__image">
                                <?php if ($thumbnail_html) : ?>
                                    <?php echo $thumbnail_html; ?>
                                <?php elseif (trim($term->description)) : ?>
                                    <div class="kk-homepage-card__icon"><?php echo esc_html($term->description); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="kk-homepage-card__content">
                                <h3 class="kk-homepage-card__title"><?php echo esc_html($term->name); ?></h3>
                                <?php if ($term->count > 0) : ?>
                                    <p class="kk-homepage-card__excerpt">
                                        <?php printf(_n('%s kleurplaat', '%s kleurplaten', $term->count, 'kinderkleurplaten'), $term->count); ?>
                                    </p>
                                <?php endif; ?>
                                <span class="kk-homepage-card__download"><?php esc_html_e('Bekijk thema', 'kinderkleurplaten'); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="kk-gallery-section-posts">
            <h2 class="kk-gallery-section__title"><?php esc_html_e('Alle kleurplaten', 'kinderkleurplaten'); ?></h2>
            <?php if ($gallery_query->have_posts()) : ?>
                <div class="kk-gallery-grid">
                    <?php while ($gallery_query->have_posts()) : $gallery_query->the_post(); ?>
                        <article class="kk-gallery-card" id="kk-kleurplaat-<?php echo esc_attr(get_the_ID()); ?>">
                            <div class="kk-gallery-card__image-wrap">
                                <?php the_post_thumbnail('full', array('class' => 'kk-gallery-card__image', 'loading' => 'lazy', 'decoding' => 'async')); ?>
                            </div>
                            <div class="kk-gallery-card__body">
                                <h3 class="kk-gallery-card__title"><?php the_title(); ?></h3>
                                <div class="kk-gallery-card__actions">
                                    <?php
                                    $print_url = trim(get_post_meta(get_the_ID(), 'kk_print_url', true));
                                    if ($print_url === '') {
                                        $print_url = get_permalink();
                                    }
                                    $svg_url = trim(get_post_meta(get_the_ID(), 'kk_svg_url', true));
                                    ?>
                                    <a class="kk-button kk-button--print" href="<?php echo esc_url($print_url); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php esc_html_e('Print kleurplaat', 'kinderkleurplaten'); ?>
                                    </a>
                                    <?php if ($svg_url !== '') : ?>
                                        <a class="kk-button kk-button--svg" href="<?php echo esc_url($svg_url); ?>" target="_blank" rel="noopener noreferrer">
                                            <?php esc_html_e('Download SVG', 'kinderkleurplaten'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <nav class="kk-gallery-pagination">
                    <?php
                    $total_pages = $gallery_query->max_num_pages;
                    if ($total_pages > 1) {
                        $paginate_links = paginate_links(array(
                            'base'      => esc_url_raw(add_query_arg('paged', '%#%')),
                            'format'    => '',
                            'current'   => $paged,
                            'total'     => $total_pages,
                            'prev_text' => __('&laquo; Vorige', 'kinderkleurplaten'),
                            'next_text' => __('Volgende &raquo;', 'kinderkleurplaten'),
                            'type'      => 'list',
                        ));
                        if ($paginate_links) {
                            echo $paginate_links;
                        }
                    }
                    ?>
                </nav>
            <?php else : ?>
                <p class="kk-empty-state"><?php esc_html_e('Geen kleurplaten gevonden', 'kinderkleurplaten'); ?></p>
            <?php endif; ?>
        </section>

    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}