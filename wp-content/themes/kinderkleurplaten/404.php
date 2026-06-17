<?php
get_header();
?>

<main id="primary" class="site-main">
	<div class="container">
		<section class="error-card">
			<p class="eyebrow"><?php esc_html_e('Oeps', 'kinderkleurplaten'); ?></p>
			<h1><?php esc_html_e('404 - Pagina niet gevonden', 'kinderkleurplaten'); ?></h1>
			<p><?php esc_html_e('Deze kleurplaat of pagina kunnen we niet vinden. Ga terug naar de galerij en kies een andere kleurplaat.', 'kinderkleurplaten'); ?></p>
			<div class="entry-actions">
				<a class="button" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Naar home', 'kinderkleurplaten'); ?></a>
				<a class="button button--secondary" href="<?php echo esc_url(home_url('/kleurplaten/')); ?>"><?php esc_html_e('Naar de galerij', 'kinderkleurplaten'); ?></a>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
