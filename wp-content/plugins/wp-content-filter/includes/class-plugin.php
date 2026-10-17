<?php
/**
 * Shared plugin runtime.
 *
 * @package WPGO_Content_Censor
 */

namespace WPGO\Content_Censor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers shared WordPress hooks and delegates text replacement to the engine.
 */
final class Plugin {

	/**
	 * Whether licensed premium code is available.
	 *
	 * @var bool
	 */
	private static $premium_available = false;

	/**
	 * Register lifecycle hooks.
	 *
	 * @param bool $premium_available Whether premium source is loaded and licensed.
	 * @return void
	 */
	public static function boot( $premium_available ) {
		self::$premium_available = (bool) $premium_available;
		add_action( 'plugins_loaded', array( 'WPGO\\Content_Censor\\Migrator', 'maybe_upgrade' ), 1 );
		add_action( 'plugins_loaded', array( __CLASS__, 'initialize' ), 5 );
		add_filter( 'plugin_action_links_' . plugin_basename( WPGO_CONTENT_CENSOR_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Initialize settings and public filters.
	 *
	 * @return void
	 */
	public static function initialize() {
		if ( content_censor_fs()->is_premium() ) {
			add_action( 'init', array( __CLASS__, 'load_pro_translations' ) );
		}
		Settings::boot();
		Admin::boot();

		if ( ! class_exists( 'WPGO_Content_Censor_Current', false ) ) {
			class_alias( __CLASS__, 'WPGO_Content_Censor_Current' );
		}

		if ( self::$premium_available && class_exists( 'WPGO\\Content_Censor\\Premium' ) ) {
			Premium::boot();
		}

		if ( is_admin() ) {
			return;
		}

		self::register_public_filters( Settings::get() );
	}

	/**
	 * Register bundled translations for the independently distributed Pro build.
	 *
	 * Free translations are loaded automatically by WordPress.org. Pro uses a
	 * different directory but deliberately retains the established text domain.
	 *
	 * @return void
	 */
	public static function load_pro_translations() {
		// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- Required only for the independently distributed Pro directory.
		load_plugin_textdomain( 'wp-content-filter', false, dirname( plugin_basename( WPGO_CONTENT_CENSOR_FILE ) ) . '/languages' );
	}

	/**
	 * Register shared frontend filter hooks from current settings.
	 *
	 * @param array<string, string> $options Plugin settings.
	 * @return void
	 */
	public static function register_public_filters( array $options ) {
		$callback = array( __CLASS__, 'filter' );
		if ( '1' === $options['chk_post_content'] ) {
			add_filter( 'the_content', $callback );
			add_filter( 'get_the_excerpt', $callback );
			if ( class_exists( 'bbPress' ) ) {
				add_filter( 'bbp_get_topic_content', $callback );
				add_filter( 'bbp_get_reply_content', $callback );
			}
		}
		if ( '1' === $options['chk_post_title'] ) {
			add_filter( 'the_title', $callback );
		}
		if ( '1' === $options['chk_comments'] ) {
			add_filter( 'comment_text', $callback );
			add_filter( 'get_comment_author', $callback );
		}
		if ( '1' === $options['chk_tags'] ) {
			add_filter( 'term_links-post_tag', $callback );
		}
		if ( '1' === $options['chk_tag_cloud'] ) {
			add_filter( 'wp_tag_cloud', $callback );
		}
	}

	/**
	 * Filter a string or string array using the current settings.
	 *
	 * @param mixed $value Filter value.
	 * @return mixed
	 */
	public static function filter( $value ) {
		global $post;
		$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
		return Filter_Engine::filter( $value, Settings::get(), $post_id );
	}

	/**
	 * Add a direct settings link on the Plugins screen.
	 *
	 * @param array<int, string> $links Existing links.
	 * @return array<int, string>
	 */
	public static function action_links( array $links ) {
		$url = add_query_arg( 'page', Settings::PAGE_SLUG, admin_url( 'admin.php' ) );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'wp-content-filter' ) . '</a>' );
		return $links;
	}
}
