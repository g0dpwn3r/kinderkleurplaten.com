<?php
/**
 * WPCode Snippet: Guestbook Custom Post Type
 * 
 * This snippet registers a custom post type 'gastenboek' (guestbook) for managing
 * guestbook entries. Parents can leave messages, drawings, or feedback about the
 * coloring pages. Includes author name, email (private), and message content.
 * 
 * Installation: Import via WPCode plugin or copy to your theme's functions.php
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Register the 'gastenboek' custom post type
 */
function kk_register_cpt_gastenboek() {
	$labels = array(
		'name'               => __('Gastenboek', 'kinderkleurplaten'),
		'singular_name'      => __('Bericht', 'kinderkleurplaten'),
		'menu_name'          => __('Gastenboek', 'kinderkleurplaten'),
		'name_admin_bar'     => __('Bericht', 'kinderkleurplaten'),
		'add_new'            => __('Nieuw bericht', 'kinderkleurplaten'),
		'add_new_item'       => __('Nieuw gastenboekbericht toevoegen', 'kinderkleurplaten'),
		'edit_item'          => __('Bericht bewerken', 'kinderkleurplaten'),
		'new_item'           => __('Nieuw bericht', 'kinderkleurplaten'),
		'view_item'          => __('Bericht bekijken', 'kinderkleurplaten'),
		'all_items'          => __('Alle berichten', 'kinderkleurplaten'),
		'search_items'       => __('Zoeken in gastenboek', 'kinderkleurplaten'),
		'parent_item_colon'  => __('Hoofdbericht:', 'kinderkleurplaten'),
		'not_found'          => __('Geen gastenboekberichten gevonden.', 'kinderkleurplaten'),
		'not_found_in_trash' => __('Geen gastenboekberichten gevonden in prullenbak.', 'kinderkleurplaten'),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'         => true,
		'query_var'           => true,
		'rewrite'             => array('slug' => 'gastenboek', 'with_front' => false),
		'capability_type'     => 'post',
		'has_archive'         => true,
		'hierarchical'        => false,
		'menu_position'       => 6,
		'menu_icon'           => 'dashicons-format-chat',
		'show_in_rest'         => true,
		'show_in_nav_menus'    => false,
		'supports'            => array('title', 'editor', 'author', 'custom-fields'),
		'capabilities'        => array(
			'create_posts'       => false, // Remove "Add New"
			'edit_posts'         => 'edit_others_posts',
			'edit_others_posts'  => 'edit_others_posts',
		),
		'map_meta_cap'        => true,
	);

	register_post_type('gastenboek', $args);
}
add_action('init', 'kk_register_cpt_gastenboek');

/**
 * Change guestbook post status to pending for moderation
 */
function kk_gastenboek_auto_pending($status, $post_data) {
	if ($post_data['post_type'] === 'gastenboek' && $status === 'publish') {
		return 'pending';
	}
	return $status;
}
add_filter('wp_insert_post_data', 'kk_gastenboek_auto_pending', 10, 2);

/**
 * Add meta fields for guestbook entry
 */
function kk_add_gastenboek_meta_boxes() {
	add_meta_box(
		'kk_gastenboek_details',
		__('Berichtgegevens', 'kinderkleurplaten'),
		'kk_gastenboek_meta_box_callback',
		'gastenboek',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'kk_add_gastenboek_meta_boxes');

function kk_gastenboek_meta_box_callback($post) {
	wp_nonce_field('kk_save_gastenboek_meta', 'kk_gastenboek_meta_nonce');
	
	$author_name = get_post_meta($post->ID, '_kk_author_name', true);
	$child_age = get_post_meta($post->ID, '_kk_child_age', true);
	$rating = get_post_meta($post->ID, '_kk_rating', true);
	?>
	<p>
		<label for="kk_author_name"><?php esc_html_e('Naam ouder/', 'kinderkleurplaten'); ?>
		<input type="text" id="kk_author_name" name="kk_author_name" value="<?php echo esc_attr($author_name); ?>" style="width:100%">
	</label>
	</p>
	<p>
		<label for="kk_child_age"><?php esc_html_e('Leeftijd kind(eren):', 'kinderkleurplaten'); ?><br>
		<input type="number" id="kk_child_age" name="kk_child_age" value="<?php echo esc_attr($child_age); ?>" min="1" max="18">
	</label>
	</p>
	<p>
		<label for="kk_rating"><?php esc_html_e('Beoordeling:', 'kinderkleurplaten'); ?><br>
		<select id="kk_rating" name="kk_rating">
			<option value=""><?php esc_html_e('Selecteer...', 'kinderkleurplaten'); ?></option>
			<?php for ($i = 5; $i >= 1; $i--) : ?>
				<option value="<?php echo $i; ?>" <?php selected($rating, $i); ?>>
					<?php echo str_repeat('⭐', $i) . ' (' . $i . '/5)'; ?>
				</option>
			<?php endfor; ?>
		</select>
	</label>
	</p>
	<?php
}

function kk_save_gastenboek_meta_boxes($post_id) {
	if (!isset($_POST['kk_gastenboek_meta_nonce']) || !wp_verify_nonce($_POST['kk_gastenboek_meta_nonce'], 'kk_save_gastenboek_meta')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (isset($_POST['kk_author_name'])) {
		update_post_meta($post_id, '_kk_author_name', sanitize_text_field($_POST['kk_author_name']));
	}
	if (isset($_POST['kk_child_age'])) {
		update_post_meta($post_id, '_kk_child_age', absint($_POST['kk_child_age']));
	}
	if (isset($_POST['kk_rating'])) {
		update_post_meta($post_id, '_kk_rating', absint($_POST['kk_rating']));
	}
}
add_action('save_post', 'kk_save_gastenboek_meta_boxes');

/**
 * Add admin columns for guestbook entries
 */
function kk_gastenboek_admin_columns($columns) {
	$columns['kk_author'] = __('Auteur', 'kinderkleurplaten');
	$columns['kk_child_age'] = __('Leeftijd', 'kinderkleurplaten');
	$columns['kk_rating'] = __('Beoordeling', 'kinderkleurplaten');
	$columns['kk_date'] = __('Datum', 'kinderkleurplaten');
	return $columns;
}
add_filter('manage_gastenboek_posts_columns', 'kk_gastenboek_admin_columns');

function kk_gastenboek_render_admin_columns($column, $post_id) {
	switch ($column) {
		case 'kk_author':
			$name = get_post_meta($post_id, '_kk_author_name', true);
			echo esc_html($name ?: get_the_author_meta('display_name', $post_id));
			break;
		case 'kk_child_age':
			$age = get_post_meta($post_id, '_kk_child_age', true);
			echo esc_html($age ? $age . ' ' . __('jaar', 'kinderkleurplaten') : '—');
			break;
		case 'kk_rating':
			$rating = get_post_meta($post_id, '_kk_rating', true);
			echo esc_html($rating ? str_repeat('⭐', $rating) : '—');
			break;
		case 'kk_date':
			echo esc_html(get_the_date('d-m-Y', $post_id));
			break;
	}
}
add_action('manage_gastenboek_posts_custom_column', 'kk_gastenboek_render_admin_columns', 10, 2);

/**
 * Allow guestbook entries on front-end for submission
 */
function kk_frontend_gastenboek_submission() {
	if (!is_admin() && isset($_POST['kk_submit_gastenboek'])) {
		// Verify nonce
		if (!isset($_POST['kk_gastenboek_nonce']) || !wp_verify_nonce($_POST['kk_gastenboek_nonce'], 'kk_gastenboek_submit')) {
			wp_die(__('Nonce verificatie mislukt.', 'kinderkleurplaten'));
		}

		// Sanitize input
		$author_name = sanitize_text_field($_POST['kk_author_name'] ?? '');
		$child_age = absint($_POST['kk_child_age'] ?? '');
		$rating = absint($_POST['kk_rating'] ?? '');
		$message = wp_kses_post($_POST['kk_message'] ?? '');

		// Validate required fields
		if (empty($author_name) || empty($message)) {
			wp_die(__('Naam en bericht zijn verplicht.', 'kinderkleurplaten'));
		}

		// Create the guestbook entry
		$post_id = wp_insert_post(array(
			'post_title'   => substr($author_name, 0, 50) . ' - ' . current_time('d-m-Y'),
			'post_content' => $message,
			'post_status'  => 'pending',
			'post_type'    => 'gastenboek',
		));

		if ($post_id) {
			update_post_meta($post_id, '_kk_author_name', $author_name);
			update_post_meta($post_id, '_kk_child_age', $child_age);
			update_post_meta($post_id, '_kk_rating', $rating);
		}

		// Redirect to prevent resubmission
		wp_redirect(add_query_arg('gastenboek_submit', 'success', $_SERVER['REQUEST_URI']));
		exit;
	}
}
add_action('template_redirect', 'kk_frontend_gastenboek_submission');