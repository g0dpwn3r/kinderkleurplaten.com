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