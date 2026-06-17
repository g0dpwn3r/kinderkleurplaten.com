<?php
/**
 * WPCode Snippet: Guestbook Shortcode with Form Processing
 * 
 * This snippet creates a shortcode [kk_gastenboek] that displays approved guestbook
 * entries and includes a front-end submission form. It shows author, child age,
 * rating, and message content in a clean layout.
 * 
 * Usage: [kk_gastenboek limit="10" show_form="true"]
 * 
 * Installation: Import via WPCode plugin or copy to your theme's functions.php
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Shortcode: Display guestbook entries and submission form
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function kk_gastenboek_shortcode($atts) {
	$atts = shortcode_atts(
		array(
			'limit'     => 10,
			'show_form' => 'true',
			'orderby'   => 'date',
			'order'     => 'DESC',
		),
		$atts,
		'kk_gastenboek'
	);

	$limit = max(1, absint($atts['limit']));
	$show_form = $atts['show_form'] === 'true';

	ob_start();
	?>
	<div class="kk-gastenboek">
		<?php if ($show_form) : ?>
			<section class="kk-gastenboek-form" aria-labelledby="gastenboek-form-title">
				<h2 id="gastenboek-form-title"><?php esc_html_e('Laat een bericht achter', 'kinderkleurplaten'); ?></h2>
				
				<?php if (get_query_var('gastenboek_submit') === 'success') : ?>
					<div class="kk-gastenboek-notice kk-gastenboek-notice--success" role="alert">
						<?php esc_html_e('Bedankt! Je bericht is ontvangen en wacht op goedkeuring.', 'kinderkleurplaten'); ?>
					</div>
				<?php endif; ?>

				<form method="post" action="" class="kk-gastenboek-form__form">
					<?php wp_nonce_field('kk_gastenboek_submit', 'kk_gastenboek_nonce'); ?>
					
					<div class="kk-gastenboek-form__row">
						<label for="kk_author_name"><?php esc_html_e('Naam', 'kinderkleurplaten'); ?> <span aria-label="<?php esc_attr_e('verplicht', 'kinderkleurplaten'); ?>">*</span></label>
						<input type="text" id="kk_author_name" name="kk_author_name" required placeholder="<?php esc_attr_e('Je naam', 'kinderkleurplaten'); ?>">
					</div>

					<div class="kk-gastenboek-form__row">
						<label for="kk_child_age"><?php esc_html_e('Leeftijd kind(eren)', 'kinderkleurplaten'); ?></label>
						<input type="number" id="kk_child_age" name="kk_child_age" min="1" max="18" placeholder="<?php esc_attr_e('Bijv. 6', 'kinderkleurplaten'); ?>">
					</div>

					<div class="kk-gastenboek-form__row">
						<label for="kk_rating"><?php esc_html_e('Beoordeling', 'kinderkleurplaten'); ?></label>
						<select id="kk_rating" name="kk_rating">
							<option value=""><?php esc_html_e('Selecteer een beoordeling...', 'kinderkleurplaten'); ?></option>
							<option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
							<option value="4">⭐⭐⭐⭐ (4/5)</option>
							<option value="3">⭐⭐⭐ (3/5)</option>
							<option value="2">⭐⭐ (2/5)</option>
							<option value="1">⭐ (1/5)</option>
						</select>
					</div>

					<div class="kk-gastenboek-form__row">
						<label for="kk_message"><?php esc_html_e('Bericht', 'kinderkleurplaten'); ?> <span aria-label="<?php esc_attr_e('verplicht', 'kinderkleurplaten'); ?>">*</span></label>
						<textarea id="kk_message" name="kk_message" required rows="5" placeholder="<?php esc_attr_e('Vertel ons wat je van de kleurplaten vindt!', 'kinderkleurplaten'); ?>"></textarea>
					</div>

					<button type="submit" name="kk_submit_gastenboek" class="button button--secondary">
						<?php esc_html_e('Verstuur bericht', 'kinderkleurplaten'); ?>
					</button>
				</form>
			</section>
		<?php endif; ?>

		<section class="kk-gastenboek-entries" aria-labelledby="gastenboek-entries-title">
			<h2 id="gastenboek-entries-title"><?php esc_html_e('Wat ouders zeggen', 'kinderkleurplaten'); ?></h2>

			<?php
			$query = new WP_Query(array(
				'post_type'      => 'gastenboek',
				'posts_per_page' => $limit,
				'post_status'    => 'publish',
				'orderby'        => $atts['orderby'],
				'order'          => $atts['order'],
			));

			if ($query->have_posts()) :
				?>
				<div class="kk-gastenboek-list">
					<?php while ($query->have_posts()) : $query->the_post(); ?>
						<article class="kk-gastenboek-entry" id="gastenboek-<?php the_ID(); ?>">
							<div class="kk-gastenboek-entry__header">
								<div class="kk-gastenboek-entry__author">
									<strong><?php echo esc_html(get_post_meta(get_the_ID(), '_kk_author_name', true)); ?></strong>
									<?php
									$age = get_post_meta(get_the_ID(), '_kk_child_age', true);
									if ($age) :
									?>
										<span class="kk-gastenboek-entry__age">(<?php echo esc_html($age); ?> <?php esc_html_e('jaar', 'kinderkleurplaten'); ?>)</span>
									<?php endif; ?>
								</div>
								<time class="kk-gastenboek-entry__date" datetime="<?php echo get_the_date('c', get_the_ID()); ?>">
									<?php echo get_the_date(get_option('date_format'), get_the_ID()); ?>
								</time>
							</div>

							<?php
							$rating = get_post_meta(get_the_ID(), '_kk_rating', true);
							if ($rating) :
							?>
								<div class="kk-gastenboek-entry__rating" aria-label="<?php esc_attr_e('Beoordeling', 'kinderkleurplaten'); ?>: <?php echo esc_attr($rating); ?> <?php esc_attr_e('van 5 sterren', 'kinderkleurplaten'); ?>">
									<?php echo str_repeat('⭐', $rating); ?>
								</div>
							<?php endif; ?>

							<div class="kk-gastenboek-entry__content">
								<?php the_content(); ?>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p class="kk-gastenboek-no-entries">
					<?php esc_html_e('Nog geen berichten in het gastenboek. Wees de eerste!', 'kinderkleurplaten'); ?>
				</p>
			<?php endif; ?>
		</section>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode('kk_gastenboek', 'kk_gastenboek_shortcode');

/**
 * Register guestbook query var for form submission notice
 */
function kk_gastenboek_query_vars($vars) {
	$vars[] = 'gastenboek_submit';
	return $vars;
}
add_filter('query_vars', 'kk_gastenboek_query_vars');

/**
 * Enqueue styles for guestbook
 */
function kk_gastenboek_styles() {
	if (is_singular() || is_archive()) {
		wp_enqueue_style(
			'kk-gastenboek',
			get_template_directory_uri() . '/snippets/css-gastenboek.css',
			array(),
			'1.0.0'
		);
	}
}
add_action('wp_enqueue_scripts', 'kk_gastenboek_styles');

/**
 * AJAX handler for guestbook form submission (alternative to form post)
 */
function kk_ajax_gastenboek_submit() {
	// Check nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'kk_gastenboek_submit')) {
		wp_die(wp_json_encode(array('success' => false, 'message' => __('Nonce verificatie mislukt.', 'kinderkleurplaten'))));
	}

	// Sanitize and validate
	$author_name = sanitize_text_field($_POST['author_name'] ?? '');
	$child_age = absint($_POST['child_age'] ?? '');
	$rating = absint($_POST['rating'] ?? '');
	$message = wp_kses_post($_POST['message'] ?? '');

	if (empty($author_name) || empty($message)) {
		wp_die(wp_json_encode(array('success' => false, 'message' => __('Naam en bericht zijn verplicht.', 'kinderkleurplaten'))));
	}

	// Create entry
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
		$response = array('success' => true, 'message' => __('Bedankt! Je bericht is ontvangen en wacht op goedkeuring.', 'kinderkleurplaten'));
	} else {
		$response = array('success' => false, 'message' => __('Er ging iets mis. Probeer opnieuw.', 'kinderkleurplaten'));
	}

	wp_die(wp_json_encode($response));
}
add_action('wp_ajax_kk_gastenboek_submit', 'kk_ajax_gastenboek_submit');
add_action('wp_ajax_nopriv_kk_gastenboek_submit', 'kk_ajax_gastenboek_submit');