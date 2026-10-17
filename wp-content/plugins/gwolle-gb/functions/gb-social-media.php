<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Return a list of Social Media services with all the data that is needed.
 *
 * @return array List of Social Media services.
 *
 * @since 1.0.0
 */
function gwolle_gb_addon_get_social_media_v2() {

	$services = array(
		'facebook'  => array(
			'name'  => 'Facebook',
			'icon'  => GWOLLE_GB_URL . 'assets/social-icons/facebook.png',
			'url'   => 'https://www.facebook.com/sharer.php?u=',
			'check' => 'false',
			'order' => 0,
		),
		'twitter'   => array(
			'name'  => 'Twitter',
			'icon'  => GWOLLE_GB_URL . 'assets/social-icons/twitter.png',
			'url'   => 'https://twitter.com/intent/tweet?url=',
			'check' => 'false',
			'order' => 0,
		),
		'reddit'    => array(
			'name'  => 'Reddit',
			'icon'  => GWOLLE_GB_URL . 'assets/social-icons/reddit.png',
			'url'   => 'https://www.reddit.com/submit?url=',
			'check' => 'false',
			'order' => 0,
		),
		'whatsapp'  => array(
			'name'  => 'WhatsApp',
			'icon'  => GWOLLE_GB_URL . 'assets/social-icons/whatsapp.png',
			'url'   => 'https://api.whatsapp.com/send?text=',
			'check' => 'false',
			'order' => 0,
		),
		'telegram'  => array(
			'name'  => 'Telegram',
			'icon'  => GWOLLE_GB_URL . 'assets/social-icons/telegram.png',
			'url'   => 'https://telegram.me/share/url?url=',
			'check' => 'false',
			'order' => 0,
		),
		'tumblr'    => array(
			'name'  => 'Tumblr',
			'icon'  => GWOLLE_GB_URL . 'assets/social-icons/tumblr.png',
			'url'   => 'http://tumblr.com/widgets/share/tool?canonicalUrl=',
			'check' => 'false',
			'order' => 0,
		),
		'linkedin'  => array(
			'name'  => 'LinkedIn',
			'icon'  => GWOLLE_GB_URL . 'assets/social-icons/linkedin.png',
			'url'   => 'https://www.linkedin.com/shareArticle?mini=true&url=',
			'check' => 'false',
			'order' => 0,
		),
		'email'     => array(
			'name'  => 'Email',
			'icon'  => GWOLLE_GB_URL . 'assets/social-icons/email.png',
			'url'   => 'mailto:?Subject=' . __('Guestbook entry', 'gwolle-gb') . '&Body=',
			'check' => 'false',
			'order' => 0,
		),
	);

	$setting = get_option( 'gwolle_gb_addon-social_services', array() );
	if ( is_string( $setting ) ) {
		$setting = maybe_unserialize( $setting );
	}
	// Merge arrays...
	if ( is_array($setting) && ! empty($setting) ) {
		foreach ( $services as $key => $service ) {
			if ( isset($setting["$key"]) && $setting["$key"]['check'] == 'true' ) {
				$services["$key"]['check'] = 'true';
			}
		}
	}

	/*
	 * Filters the returned list of social media services.
	 *
	 * @since 1.0.3
	 *
	 * @param array  $services The list of services.
	 */
	$services = apply_filters( 'gwolle_gb_addon_social_media', $services );

	return $services;

}
