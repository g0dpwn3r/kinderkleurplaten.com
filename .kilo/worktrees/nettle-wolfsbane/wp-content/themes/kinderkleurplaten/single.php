<?php get_header(); ?>

<main style="max-width: 800px; margin: 40px auto; padding: 20px; text-align: center;">
    <?php while (have_posts()) : the_post(); ?>
        <h1><?php the_title(); ?></h1>
        <?php the_post_thumbnail('large', array('style' => 'max-width:100%; height:auto; border-radius:15px; box-shadow:0 4px 15px rgba(0,0,0,0.1);')); ?>
        <div style="margin-top: 24px; text-align: left; line-height: 1.7;">
            <?php the_content(); ?>
        </div>
        <?php $print_url = get_post_meta(get_the_ID(), 'kk_print_url', true); ?>
        <a href="<?php echo esc_url($print_url); ?>" target="_blank" style="display: inline-block; background: #61f4a3; color: #000; padding: 15px 30px; border-radius: 30px; font-weight: bold; text-decoration: none; margin-top: 20px; font-size: 18px;">Nu Printen!</a>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>