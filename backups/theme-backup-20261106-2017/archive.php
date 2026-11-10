<?php
/**
 * Generic archive template — val terug op dit bestand voor
 * CPT-archives, categorie- en tag-archives.
 *
 * Voor de custom taxonomy 'kleurplaat_categorie' heeft WordPress een
 * specifieker template: taxonomy-kleurplaat_categorie.php
 */
get_header(); ?>

<main id="primary" class="site-main">
	<div class="container">
		<?php
		if (function_exists('kinderkleurplaten_breadcrumbs')) {
			kinderkleurplaten_breadcrumbs();
		}
		?>

		<header class="section-header">
			<div>
				<p class="eyebrow"><?php esc_html_e('Overzicht', 'kinderkleurplaten'); ?></p>
				<h1 class="archive-title"><?php the_archive_title(); ?></h1>
				<?php the_archive_description('<div class="archive-description">', '</div>'); ?>
			</div>
		</header>

		<?php if (have_posts()) : ?>
			<div class="posts-grid">
				<?php while (have_posts()) : the_post(); ?>

					<?php
					/**
					 * Zoek de featured image van deze post.
					 * function_exists()-guard voorkomt fatal error als de
					 * functie om wat voor reden dan niet geladen is.
					 */
					$image = false;
					if (function_exists('kinderkleurplaten_find_colouring_image')) {
						$image = kinderkleurplaten_find_colouring_image(get_the_ID());
					}
					?>

					<article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
						<?php if (has_post_thumbnail()) : ?>
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail('medium', array('loading' => 'lazy', 'alt' => the_title_attribute(array('echo' => false)))); ?>
							</a>
						<?php elseif ($image && !empty($image['url'])) : ?>
							<a href="<?php the_permalink(); ?>">
								<img src="<?php echo esc_url($image['url']); ?>"
									 alt="<?php echo !empty($image['alt']) ? esc_attr($image['alt']) : esc_attr(get_the_title()); ?>"
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
					<p><?php esc_html_e('Probeer een andere categorie of zoekterm.', 'kinderkleurplaten'); ?></p>
				</div>
			</article>

		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
