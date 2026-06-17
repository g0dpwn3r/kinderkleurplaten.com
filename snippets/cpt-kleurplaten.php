<?php
/**
 * WPCode Snippet: Custom Post Type for Coloring Pages
 * 
 * This snippet registers a custom post type 'kleurplaat' (coloring page) for managing
 * individual coloring pages with SVG downloads. Each coloring page includes title,
 * excerpt, featured image, and additional metadata.
 * 
 * Installation: Import via WPCode plugin or copy to your theme's functions.php
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register the 'kleurplaat' custom post type
 */
function kk_register_cpt_kleurplaten() {
	$labels = array(
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
		'parent_item_colon'  => __('Hoofdkleurplaat:', 'kinderkleurplaten'),
		'not_found'          => __('Geen kleurplaten gevonden.', 'kinderkleurplaten'),
		'not_found_in_trash' => __('Geen kleurplaten gevonden in prullenbak.', 'kinderkleurplaten'),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'         => true,
		'query_var'           => true,
		'rewrite'             => array('slug' => 'kleurplaat', 'with_front' => false),
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-art',
		'show_in_rest'         => true,
		'show_in_nav_menus'    => true,
		'supports'            => array('title', 'editor', 'excerpt', 'thumbnail', 'comments', 'revisions'),
		'taxonomies'          => array('category', 'post_tag'),
		'template'            => array(
			array('core/paragraph', array('placeholder' => __('Kies je stiften, potloden of wasco en kleur deze vrolijke kinderkleurplaat in.', 'kinderkleurplaten'))),
		),
	);

	register_post_type('kleurplaat', $args);
}
add_action('init', 'kk_register_cpt_kleurplaten');

/**
 * Add custom meta box for SVG file URL
 */
function kk_add_svg_meta_box() {
	add_meta_box(
		'kk_svg_url',
		__('SVG Bestand URL', 'kinderkleurplaten'),
		'kk_svg_url_meta_box_callback',
		'kleurplaat',
		'side',
		'high'
	);
}
add_action('add_meta_boxes', 'kk_add_svg_meta_box');

function kk_svg_url_meta_box_callback($post) {
	wp_nonce_field('kk_save_svg_url', 'kk_svg_url_nonce');
	$value = get_post_meta($post->ID, '_kk_svg_url', true);
	echo '<p><label for="kk_svg_url">' . esc_html__('Voer de URL van het SVG-bestand in:', 'kinderkleurplaten') . '</label></p>';
	echo '<input type="url" id="kk_svg_url" name="kk_svg_url" value="' . esc_url($value) . '" style="width:100%" placeholder="' . esc_attr__('https://example.com/image.svg', 'kinderkleurplaten') . '">';
}

function kk_save_svg_url_meta_box($post_id) {
	if (!isset($_POST['kk_svg_url_nonce']) || !wp_verify_nonce($_POST['kk_svg_url_nonce'], 'kk_save_svg_url')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (isset($_POST['kk_svg_url'])) {
		update_post_meta($post_id, '_kk_svg_url', esc_url_raw($_POST['kk_svg_url']));
	}
}
add_action('save_post', 'kk_save_svg_url_meta_box');

/**
 * Register additional taxonomy for coloring page themes
 */
function kk_register_kleurplaat_category_taxonomy() {
	$labels = array(
		'name'              => __('Thema\'s', 'kinderkleurplaten'),
		'singular_name'     => __('Thema', 'kinderkleurplaten'),
		'search_items'      => __('Thema\'s zoeken', 'kinderkleurplaten'),
		'all_items'         => __('Alle thema\'s', 'kinderkleurplaten'),
		'parent_item'       => __('Hoofdthema', 'kinderkleurplaten'),
		'parent_item_colon' => __('Hoofdthema:', 'kinderkleurplaten'),
		'edit_item'         => __('Thema bewerken', 'kinderkleurplaten'),
		'update_item'       => __('Thema bijwerken', 'kinderkleurplaten'),
		'add_new_item'      => __('Nieuw thema toevoegen', 'kinderkleurplaten'),
		'new_item_name'     => __('Nieuw themanaam', 'kinderkleurplaten'),
		'menu_name'         => __('Thema\'s', 'kinderkleurplaten'),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array('slug' => 'kleurplaat-thema'),
	);

	register_taxonomy('kleurplaat_thema', array('kleurplaat'), $args);
}
add_action('init', 'kk_register_kleurplaat_category_taxonomy');

/**
 * Set default columns for the admin list view
 */
function kk_manage_kleurplaat_columns($columns) {
	$columns['kk_thema'] = __('Thema', 'kinderkleurplaten');
	$columns['kk_svg'] = __('SVG Bestand', 'kinderkleurplaten');
	return $columns;
}
add_filter('manage_kleurplaat_posts_columns', 'kk_manage_kleurplaat_columns');

function kk_render_kleurplaat_columns($column, $post_id) {
	switch ($column) {
		case 'kk_thema':
			$terms = get_the_terms($post_id, 'kleurplaat_thema');
			if ($terms && !is_wp_error($terms)) {
				echo esc_html(implode(', ', wp_list_pluck($terms, 'name')));
			}
			break;
		case 'kk_svg':
			$svg_url = get_post_meta($post_id, '_kk_svg_url', true);
			if ($svg_url) {
				echo '<a href="' . esc_url($svg_url) . '" target="_blank">' . esc_html__('Bekijken', 'kinderkleurplaten') . '</a>';
			}
			break;
	}
}
add_action('manage_kleurplaat_posts_custom_column', 'kk_render_kleurplaat_columns', 10, 2);

/**
 * Make thema column sortable
 */
function kk_sortable_kleurplaat_columns($columns) {
	$columns['kk_thema'] = 'kk_thema';
	$columns['kk_svg'] = 'kk_svg';
	return $columns;
}
add_filter('manage_edit-kleurplaat_sortable_columns', 'kk_sortable_kleurplaat_columns');