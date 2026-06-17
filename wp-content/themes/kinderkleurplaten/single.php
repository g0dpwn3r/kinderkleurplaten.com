<?php get_header(); ?>

<main style="max-width: 800px; margin: 40px auto; padding: 20px; text-align: center;">
    <?php while (have_posts()) : the_post(); ?>
        <h1><?php the_title(); ?></h1>
        <?php the_post_thumbnail('large', array('style' => 'max-width:100%; height:auto; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,0.1);')); ?>
        <div style="margin-top: 24px; text-align: left; line-height: 1.7;">
            <?php the_content(); ?>
        </div>
        <div style="margin-top: 24px;">
            <?php echo do_shortcode('[print_kleurplaat]'); ?>
        </div>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>