<?php
get_header();
?>

<main id="primary" class="site-main">
	<div class="container">
		<header class="section-header">
			<div>
				<p class="eyebrow"><?php esc_html_e('Zoeken', 'kinderkleurplaten'); ?></p>
				<h1 class="archive-title"><?php printf(esc_html__('Zoekresultaten voor: %s', 'kinderkleurplaten'), '<span>' . get_search_query() . '</span>'); ?></h1>
			</div>
		</header>

		<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
			<label>
				<span class="screen-reader-text"><?php esc_html_e('Zoekterm', 'kinderkleurplaten'); ?></span>
				<input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Zoek kleurplaat...', 'kinderkleurplaten'); ?>">
			</label>
			<button class="button" type="submit"><?php esc_html_e('Zoeken', 'kinderkleurplaten'); ?></button>
		</form>

		<?php if (have_posts()) : ?>
			<?php while (have_posts()) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
					<header class="entry-header">
						<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					</header>
					<div class="entry-content">
						<?php the_excerpt(); ?>
					</div>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<article class="entry">
				<header class="entry-header">
					<h2 class="entry-title"><?php esc_html_e('Geen resultaten', 'kinderkleurplaten'); ?></h2>
				</header>
				<div class="entry-content">
					<p><?php esc_html_e('Probeer te zoeken op kat, hond, auto, trein, bloem, regenboog of robot.', 'kinderkleurplaten'); ?></p>
				</div>
			</article>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
