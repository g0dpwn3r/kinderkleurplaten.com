<?php

/**
 * Plugin Name: Content Censor
 * Plugin URI: https://wpgoplugins.com/plugins/content-censor/
 * Description: Filter configured words from WordPress content, titles, comments, tags, and supported community plugins.
 * Version: 3.2.3
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: David Gwyer
 * Author URI: https://wpgoplugins.com/
 * License: GPL-2.0-or-later
 * Text Domain: wp-content-filter
 *
 * @package WPGO_Content_Censor
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'content_censor_fs' ) ) {
    content_censor_fs()->set_basename( false, __FILE__ );
} else {
    /**
     * Return the Content Censor Freemius SDK instance.
     *
     * @return Freemius
     */
    function content_censor_fs() {
        global $content_censor_fs;
        if ( !isset( $content_censor_fs ) ) {
            require_once __DIR__ . '/vendor/freemius/wordpress-sdk/start.php';
            $content_censor_fs = fs_dynamic_init( array(
                'id'               => '6755',
                'slug'             => 'wp-content-filter',
                'premium_slug'     => 'content-censor-pro',
                'type'             => 'plugin',
                'public_key'       => 'pk_f231afb642d228e0bbba760ca82fb',
                'is_premium'       => false,
                'premium_suffix'   => 'Pro',
                'has_addons'       => false,
                'has_paid_plans'   => true,
                'is_org_compliant' => true,
                'menu'             => array(
                    'slug'       => 'content-censor-wpgoplugins',
                    'first-path' => 'admin.php?page=content-censor-wpgoplugins',
                    'support'    => false,
                ),
                'is_live'          => true,
            ) );
        }
        return $content_censor_fs;
    }

    require_once __DIR__ . '/includes/legacy-sdk.php';
    content_censor_fs()->add_filter( 'pricing/show_annual_in_monthly', '__return_false' );
    require_once __DIR__ . '/includes/class-content-censor-introductory-pricing.php';
    \WPGO_Content_Censor_Introductory_Pricing::register( array(
        'page'        => 'content-censor-wpgoplugins-pricing',
        'handle'      => 'content-censor-introductory-pricing',
        'plugin_file' => __FILE__,
        'script'      => 'js/wpgo-introductory-pricing.js',
        'style'       => 'css/wpgo-introductory-pricing.css',
        'version'     => content_censor_fs()->get_plugin_version(),
        'tiers'       => array(
            '1'  => array(
                'firstYear' => '$29',
                'renewal'   => '$39',
                'coupon'    => 'CC1STYEAR10',
            ),
            '3'  => array(
                'firstYear' => '$59',
                'renewal'   => '$79',
                'coupon'    => 'CC3SITE20',
            ),
            '25' => array(
                'firstYear' => '$109',
                'renewal'   => '$129',
                'coupon'    => 'CC25SITE20',
            ),
        ),
    ) );
    do_action( 'content_censor_fs_loaded' );
    define( 'WPGO_CONTENT_CENSOR_VERSION', '3.2.3' );
    define( 'WPGO_CONTENT_CENSOR_FILE', __FILE__ );
    define( 'WPGO_CONTENT_CENSOR_DIR', __DIR__ . '/' );
    define( 'WPGO_CONTENT_CENSOR_OPTIONS', 'content_censor_plugin_options' );
    require_once WPGO_CONTENT_CENSOR_DIR . 'includes/class-filter-engine.php';
    require_once WPGO_CONTENT_CENSOR_DIR . 'includes/class-migrator.php';
    require_once WPGO_CONTENT_CENSOR_DIR . 'includes/class-settings.php';
    require_once WPGO_CONTENT_CENSOR_DIR . 'includes/class-admin.php';
    require_once WPGO_CONTENT_CENSOR_DIR . 'includes/class-plugin.php';
    $content_censor_premium_available = content_censor_fs()->can_use_premium_code__premium_only();
    if ( $content_censor_premium_available ) {
        require_once WPGO_CONTENT_CENSOR_DIR . 'premium/class-scanner.php';
        require_once WPGO_CONTENT_CENSOR_DIR . 'premium/class-premium.php';
    }
    register_activation_hook( __FILE__, array('WPGO\\Content_Censor\\Migrator', 'activate') );
    WPGO\Content_Censor\Plugin::boot( $content_censor_premium_available );
}