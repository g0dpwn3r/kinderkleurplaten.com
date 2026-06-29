<?php
/**
 * Template: taxonomy-kleurplaat_categorie.php
 *
 * WordPress template hierarchy: dit bestand wordt automatisch gebruikt
 * voor archive-pagina's van de custom taxonomy 'kleurplaat_categorie',
 * vóór archive.php.
 *
 * Toont:
 *  1. Banner met dynamische thumbnail (featured image van de eerste
 *     kleurplaat in deze categorie).
 *  2. Grid van alle kleurplaten binnen de categorie.
 *  3. WordPress paginatie.
 */
get_header(); ?>

<main id="primary" class="site-main">
	<div class="container">
		<?php
		if (function_exists('kinderkleurplaten_breadcrumbs')) {
			kinderkleurplaten_breadcrumbs();
		}

		$term = get_queried_object();
		?>

		<header class="section-header">
			<div>
				<p class="eyebrow"><?php esc_html_e('Categorie', 'kinderkleurplaten'); ?></p>
				<h1 class="archive-title"><?php echo esc_html($term ? $term->name : single_term_title('', false)); ?></h1>
				<?php
				if ($term && !empty($term->description)) {
					echo '<div class="archive-description"><p>' . esc_html($term->description) . '</p></div>';
				}

				/* Aantal kleurplaten in deze categorie */
				if ($term && isset($term->count)) {
					printf(
						'<p class="archive-count"><strong>%s</strong></p>',
						esc_html(sprintf(
							_n('%s kleurplaat', '%s kleurplaten', $term->count, 'kinderkleurplaten'),
							number_format_i18n($term->count)
						))
					);
				}
				?>
			</div>

			<?php
			/**
			 * Banner: featured image van de eerste kleurplaat in deze
			 * categorie. function_exists()-guard + lege-check voorkomen
			 * fatal errors.
			 */
			$banner = false;
			if (function_exists('kinderkleurplaten_find_colouring_image')) {
				$banner = kinderkleurplaten_find_colouring_image(); // geen arg → archive context
			}
			if ($banner && !empty($banner['url'])) :
			?>
				<div class="archive-banner">
					<img src="<?php echo esc_url($banner['url']); ?>"
						 alt="<?php echo !empty($banner['alt']) ? esc_attr($banner['alt']) : esc_attr($term ? $term->name : ''); ?>"
						 loading="lazy">
				</div>
			<?php endif; ?>
		</header>

		<?php if (have_posts()) : ?>
			<div class="posts-grid">
				<?php while (have_posts()) : the_post(); ?>

					<?php
					$post_image = false;
					if (function_exists('kinderkleurplaten_find_colouring_image')) {
						$post_image = kinderkleurplaten_find_colouring_image(get_the_ID());
					}
					?>

					<article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
						<?php if (has_post_thumbnail()) : ?>
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail('medium', array('loading' => 'lazy', 'alt' => the_title_attribute(array('echo' => false)))); ?>
							</a>
						<?php elseif ($post_image && !empty($post_image['url'])) : ?>
							<a href="<?php the_permalink(); ?>">
								<img src="<?php echo esc_url($post_image['url']); ?>"
									 alt="<?php echo !empty($post_image['alt']) ? esc_attr($post_image['alt']) : esc_attr(get_the_title()); ?>"
									 loading="lazy">
							</a>
						<?php endif; ?>

						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

						<?php if (function_exists('kinderkleurplaten_excerpt')) : ?>
							<p><?php echo esc_html(kinderkleurplaten_excerpt(18)); ?></p>
						<?php else : ?>
							<p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '…')); ?></p>
						<?php endif; ?>
					</article>

				<?php endwhile; ?>
			</div>

			<?php the_posts_pagination(array(
				'prev_text' => __('&laquo; Vorige', 'kinderkleurplaten'),
				'next_text' => __('Volgende &raquo;', 'kinderkleurplaten'),
			)); ?>

		<?php else : ?>

			<article class="entry">
				<header class="entry-header">
					<h1 class="entry-title"><?php esc_html_e('Geen kleurplaten gevonden', 'kinderkleurplaten'); ?></h1>
				</header>
				<div class="entry-content">
					<p><?php esc_html_e('Er zijn nog geen kleurplaten in deze categorie.', 'kinderkleurplaten'); ?></p>
				</div>
			</article>

		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
