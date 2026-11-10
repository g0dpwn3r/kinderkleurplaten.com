<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#primary"><?php esc_html_e('Ga naar de inhoud', 'kinderkleurplaten'); ?></a>

<header class="site-header">
	<div class="container header-inner">
		<div class="site-branding">
			<?php if (has_custom_logo()) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<div class="custom-logo" aria-hidden="true"></div>
			<?php endif; ?>
			<div>
				<p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></p>
				<p class="site-description"><?php bloginfo('description'); ?></p>
			</div>
		</div>

		<nav class="primary-navigation" aria-label="<?php esc_attr_e('Hoofdmenu', 'kinderkleurplaten'); ?>">
			<?php
			wp_nav_menu(array(
				'theme_location' => 'primary',
				'menu_class' => '',
				'container' => false,
				'fallback_cb' => 'kinderkleurplaten_fallback_menu',
			));
			?>
		</nav>
	</div>
</header>
