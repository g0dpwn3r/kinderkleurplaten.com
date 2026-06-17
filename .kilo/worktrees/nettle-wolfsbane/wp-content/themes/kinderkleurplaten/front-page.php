<?php get_header(); ?>

<main id="primary" class="site-main" style="max-width: 1200px; margin: 0 auto; padding: 32px 20px;">
    <section class="kk-home-intro" style="text-align: center; margin-bottom: 32px;">
        <h1 style="font-size: clamp(2rem, 5vw, 3.5rem); margin-bottom: 16px; line-height: 1.1;">Kleurplaten voor kinderen in vrolijke thema's</h1>
        <p style="font-size: 1.2rem; color: #475569; max-width: 720px; margin: 0 auto;">Ontdek gratis kleurplaten voor kinderen, print ze direct uit en laat de creativiteit beginnen.</p>
    </section>

    <section class="kk-home-search" style="margin-bottom: 32px;">
        <form method="get" action="<?php echo esc_url(home_url('/')); ?>" style="display: flex; gap: 12px; max-width: 640px; margin: 0 auto;">
            <input type="search" name="kk_zoek" placeholder="Zoek kleurplaten..." style="flex: 1; min-height: 56px; border: 2px solid #e5e7eb; border-radius: 999px; padding: 0 18px; font-size: 16px;">
            <button type="submit" style="min-height: 56px; border: 0; border-radius: 999px; padding: 0 24px; background: #ffb6c1; color: #5a0f1e; font-weight: 800; cursor: pointer;">Zoeken</button>
        </form>
    </section>

    <?php echo do_shortcode('[kk_ultieme_galerij]'); ?>
</main>

<?php get_footer(); ?>