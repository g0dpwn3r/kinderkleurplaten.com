<footer class="site-footer">
	<div class="container footer-inner">
		<p>&copy; <?php echo esc_html(gmdate('Y')); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>. <?php esc_html_e('Gratis kleurplaten voor kinderen.', 'kinderkleurplaten'); ?></p>
		<nav aria-label="<?php esc_attr_e('Footermenu', 'kinderkleurplaten'); ?>">
			<?php
			wp_nav_menu(array(
				'theme_location' => 'footer',
				'menu_class' => '',
				'container' => false,
				'fallback_cb' => false,
			));
			?>
		</nav>
	</div>
</footer>

<div id="kk-cookie-banner" class="kk-cookie-banner" role="dialog" aria-label="<?php esc_attr_e('Cookie toestemming', 'kinderkleurplaten'); ?>" aria-live="polite">
	<div class="kk-cookie-banner__inner">
		<div class="kk-cookie-banner__text">
			<h2><?php esc_html_e('Cookiebeleid', 'kinderkleurplaten'); ?></h2>
			<p><?php esc_html_e('Wij gebruiken alleen functionele en analytische cookies om je ervaring op onze website te verbeteren. Lees onze', 'kinderkleurplaten'); ?> <a href="<?php echo esc_url(get_privacy_policy_url()); ?>"><?php esc_html_e('privacyverklaring', 'kinderkleurplaten'); ?></a>.</p>
		</div>
		<div class="kk-cookie-banner__actions">
			<button type="button" id="kk-cookie-accept" class="kk-cookie-btn kk-cookie-btn--accept"><?php esc_html_e('Alles accepteren', 'kinderkleurplaten'); ?></button>
			<button type="button" id="kk-cookie-decline" class="kk-cookie-btn kk-cookie-btn--decline"><?php esc_html_e('Alleen noodzakelijk', 'kinderkleurplaten'); ?></button>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
