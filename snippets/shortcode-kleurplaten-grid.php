<?php
/**
 * WPCode Snippet: Homepage Grid Shortcode for Coloring Pages
 * 
 * This snippet creates a shortcode [kk_kleurplaten_grid] to display a responsive
 * grid of coloring pages on the homepage. It shows featured images with download
 * links and can be customized via shortcode attributes.
 * 
 * Usage: [kk_kleurplaten_grid limit="12" columns="4" category="dieren"]
 * 
 * Installation: Import via WPCode plugin or copy to your theme's functions.php
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Shortcode: Display coloring pages grid on homepage
 * 
 * @param array $atts Shortcode attributes
 * @return string HTML output
 */
function kk_kleurplaten_grid_shortcode($atts) {
	$atts = shortcode_atts(
		array(
			'limit'    => 12,
			'columns'  => 4,
			'category' => '',
			'orderby'  => 'date',
			'order'    => 'DESC',
		),
		$atts,
		'kk_kleurplaten_grid'
	);

	$columns = max(1, min(6, absint($atts['columns'])));
	$limit = max(1, absint($atts['limit']));

	$args = array(
		'post_type'      => 'kleurplaat',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order'],
	);

	// Filter by category if specified
	if (!empty($atts['category'])) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'kleurplaat_thema',
				'field'    => 'slug',
				'terms'    => sanitize_title($atts['category']),
			),
		);
	}

	$query = new WP_Query($args);

	if (!$query->have_posts()) {
		return '<!-- No coloring pages found -->';
	}

	ob_start();
	?>
	<div class="kk-homepage-grid kk-homepage-grid--columns-<?php echo esc_attr($columns); ?>">
		<?php while ($query->have_posts()) : $query->the_post(); ?>
			<article class="kk-homepage-card">
				<?php if (has_post_thumbnail()) : ?>
					<a href="<?php the_permalink(); ?>" class="kk-homepage-card__image">
						<?php the_post_thumbnail('kinderkleurplaten-card', array('alt' => get_the_title())); ?>
					</a>
				<?php endif; ?>
				<div class="kk-homepage-card__content">
					<h3 class="kk-homepage-card__title">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h3>
					<?php if (has_excerpt()) : ?>
						<p class="kk-homepage-card__excerpt"><?php echo wp_kses_post(get_the_excerpt()); ?></p>
					<?php endif; ?>
					<?php
					$svg_url = get_post_meta(get_the_ID(), '_kk_svg_url', true);
					if ($svg_url) :
					?>
						<a href="<?php echo esc_url($svg_url); ?>" class="kk-homepage-card__download" download>
							<span aria-hidden="true">📥</span>
							<?php esc_html_e('Download SVG', 'kinderkleurplaten'); ?>
						</a>
					<?php endif; ?>
				</div>
			</article>
		<?php endwhile; ?>
		<?php wp_reset_postdata(); ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode('kk_kleurplaten_grid', 'kk_kleurplaten_grid_shortcode');

/**
 * Enqueue styles for the homepage grid
 */
function kk_homepage_grid_styles() {
	if (is_front_page() || is_home()) {
		wp_enqueue_style(
			'kk-homepage-grid',
			get_template_directory_uri() . '/snippets/css-kleurplaten-grid.css',
			array(),
			'1.0.0'
		);
	}
}
add_action('wp_enqueue_scripts', 'kk_homepage_grid_styles');