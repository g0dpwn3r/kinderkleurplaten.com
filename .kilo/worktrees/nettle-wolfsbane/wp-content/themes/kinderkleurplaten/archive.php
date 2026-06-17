<?php
get_header();
?>

<main id="primary" class="site-main">
	<div class="container">
		<?php kinderkleurplaten_breadcrumbs(); ?>

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
					<?php $image = kinderkleurplaten_find_colouring_image(get_the_ID()); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?>>
						<?php if (has_post_thumbnail()) : ?>
							<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('kinderkleurplaten-card'); ?></a>
						<?php elseif ($image) : ?>
							<a href="<?php the_permalink(); ?>"><img src="<?php echo esc_url(kinderkleurplaten_get_colouring_image_url($image['slug'])); ?>" alt="<?php echo esc_attr($image['alt']); ?>" loading="lazy"></a>
						<?php endif; ?>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo kinderkleurplaten_excerpt(18); ?></p>
					</article>
				<?php endwhile; ?>
			</div>
			<?php the_posts_pagination(array('prev_text' => __('Vorige', 'kinderkleurplaten'), 'next_text' => __('Volgende', 'kinderkleurplaten'))); ?>
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

<?php
get_footer();
