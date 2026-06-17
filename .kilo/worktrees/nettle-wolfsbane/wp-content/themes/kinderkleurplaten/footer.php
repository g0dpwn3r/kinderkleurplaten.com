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

<?php wp_footer(); ?>
</body>
</html>
