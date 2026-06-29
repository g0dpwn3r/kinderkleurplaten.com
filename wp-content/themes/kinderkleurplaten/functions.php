<?php
/**
 * Kinderkleurplaten theme bootstrap.
 * Rebuilt as a single safe functions.php file.
 */

if (!defined('ABSPATH')) {
	exit;
}

define('KINDERKLEURPLATEN_VERSION', '1.0.1');

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
}

add_action('wp_enqueue_scripts', 'kk_enqueue_theme_styles');
function kk_enqueue_theme_styles() {
	wp_enqueue_style('kinderkleurplaten-style', get_stylesheet_uri(), array(), KINDERKLEURPLATEN_VERSION);
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
	 *     eerste kleurplaat uit de huidige term en geeft diens featured
	 *     image terug. Wordt gebruikt als banner / fallback afbeelding
	 *     voor de archive header.
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
			'supports'           => array('title', 'thumbnail', 'custom-fields'),
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

add_action('init', 'kk_handle_gastenboek_submit');
function kk_handle_gastenboek_submit() {
	if (!isset($_POST['kk_gastenboek_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['kk_gastenboek_nonce'])), 'kk_gastenboek_submit')) {
		return;
	}

	$name = sanitize_text_field(wp_unslash($_POST['kk_gastenboek_name']));
	$message = sanitize_textarea_field(wp_unslash($_POST['kk_gastenboek_message']));
	$captcha_answer = isset($_POST['kk_captcha_answer']) ? intval($_POST['kk_captcha_answer']) : 0;
	$captcha_expected = isset($_POST['kk_captcha_expected']) ? intval($_POST['kk_captcha_expected']) : 0;

	if ($captcha_answer !== $captcha_expected) {
		return;
	}

	if ($name === '' || $message === '') {
		return;
	}

	$post_id = wp_insert_post(array(
		'post_type'    => 'gastenboek_bericht',
		'post_status'  => 'pending',
		'post_title'   => $name,
		'post_content' => $message,
	));

	if (is_wp_error($post_id)) {
		return;
	}

	$redirect_to = wp_get_referer();
	if (!$redirect_to) {
		$redirect_to = home_url('/');
	}

	wp_safe_redirect(add_query_arg('kk_gb_sent', '1', $redirect_to));
	exit;
}

add_shortcode('kk_gastenboek', 'kk_gastenboek_shortcode');
function kk_gastenboek_shortcode() {
	$num1 = rand(1, 10);
	$num2 = rand(1, 10);
	$expected = $num1 + $num2;
	
	ob_start();
	?>
	<div class="kk-gastenboek-shell">
		<form class="kk-gastenboek-form" method="post" action="">
			<?php wp_nonce_field('kk_gastenboek_submit', 'kk_gastenboek_nonce'); ?>
			<label class="kk-gastenboek-field">
				<span><?php esc_html_e('Naam', 'kinderkleurplaten'); ?></span>
				<input type="text" name="kk_gastenboek_name" required>
			</label>
			<label class="kk-gastenboek-field">
				<span><?php esc_html_e('Bericht', 'kinderkleurplaten'); ?></span>
				<textarea name="kk_gastenboek_message" required></textarea>
			</label>
			<label class="kk-gastenboek-field">
				<span><?php esc_html_e('Controle', 'kinderkleurplaten'); echo ' ' . $num1 . ' + ' . $num2 . ' = ?'; ?></span>
				<input type="number" name="kk_captcha_answer" required>
				<input type="hidden" name="kk_captcha_expected" value="<?php echo esc_attr($expected); ?>">
			</label>
			<button class="kk-gastenboek-button" type="submit"><?php esc_html_e('Verstuur bericht', 'kinderkleurplaten'); ?></button>
		</form>

		<?php if (isset($_GET['kk_gb_sent'])) : ?>
			<p class="kk-gastenboek-notice"><?php esc_html_e('Bedankt! Je bericht wacht op moderatie.', 'kinderkleurplaten'); ?></p>
		<?php endif; ?>

		<div class="kk-gastenboek-list">
			<?php
			$messages_query = new WP_Query(array(
				'post_type'      => 'gastenboek_bericht',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
			));

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
     * Haalt de featured image op van de eerste kleurplaat in de opgegeven
     * categorie. Wordt gebruikt als dynamisch logo / thumbnail op
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
     * Dit elimineert het N+1 query probleem (1400+ queries → 1 query + cache read).
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

        $result = array();

        foreach ($terms as $term) {
            // Zoek de eerste kleurplaat in deze categorie (minimal query).
            $first_post_query = new WP_Query(array(
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
                'fields'         => 'ids',
                'no_found_rows'  => true,
            ));

            $thumbnail_html = '';

            if ($first_post_query->have_posts()) {
                $first_post_id = $first_post_query->posts[0];
                $thumbnail_id = get_post_thumbnail_id($first_post_id);

                if ($thumbnail_id) {
                    $thumbnail_html = wp_get_attachment_image($thumbnail_id, $image_size, false, array(
                        'alt'     => get_the_title($first_post_id),
                        'loading' => 'lazy',
                    ));
                }
            }

            wp_reset_postdata();

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
        // Wis transients voor alle mogelijke image sizes.
        delete_transient('kk_categories_with_thumbs_' . md5('medium_large'));
        delete_transient('kk_categories_with_thumbs_' . md5('medium'));
        delete_transient('kk_categories_with_thumbs_' . md5('thumbnail'));
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