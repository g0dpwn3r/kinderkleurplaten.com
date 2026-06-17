<?php
get_header();
?>

<main id="primary" class="site-main">
	<div class="container">
		<?php kinderkleurplaten_breadcrumbs(); ?>

		<?php if (have_posts()) : ?>
			<?php while (have_posts()) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
					<header class="entry-header">
						<div class="entry-meta"><?php echo kinderkleurplaten_category_links(); ?></div>
						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
					<div class="entry-actions">
						<?php kinderkleurplaten_print_button(); ?>
					</div>
				</article>
			<?php endwhile; ?>

			<?php the_posts_pagination(array(
				'prev_text' => __('Vorige', 'kinderkleurplaten'),
				'next_text' => __('Volgende', 'kinderkleurplaten'),
			)); ?>
		<?php else : ?>
			<article class="entry">
				<header class="entry-header">
					<h1 class="entry-title"><?php esc_html_e('Geen kleurplaten gevonden', 'kinderkleurplaten'); ?></h1>
				</header>
				<div class="entry-content">
					<p><?php esc_html_e('Er zijn nog geen kleurplaten beschikbaar. Kom later nog eens terug.', 'kinderkleurplaten'); ?></p>
				</div>
			</article>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
