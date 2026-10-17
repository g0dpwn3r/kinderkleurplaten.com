<?php
/**
 * Settings and plugin-basename migrations.
 *
 * @package WPGO_Content_Censor
 */

namespace WPGO\Content_Censor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preserves settings across the WordPress.org, legacy commercial, and Freemius builds.
 */
final class Migrator {

	/** Migration state option. */
	const VERSION_OPTION = 'content_censor_db_version';

	/**
	 * Run activation migrations.
	 *
	 * @return void
	 */
	public static function activate() {
		self::migrate_settings();
		update_option( self::VERSION_OPTION, WPGO_CONTENT_CENSOR_VERSION, false );

		if ( ! function_exists( 'content_censor_fs' ) || ! content_censor_fs()->is_premium() ) {
			return;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$free_basename = 'wp-content-filter/wp-content-filter.php';
		if ( is_plugin_active( $free_basename ) ) {
			deactivate_plugins( $free_basename, true );
		}
	}

	/**
	 * Run migrations after an in-place update as well as on activation.
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		if ( WPGO_CONTENT_CENSOR_VERSION === get_option( self::VERSION_OPTION ) ) {
			return;
		}
		self::migrate_settings();
		update_option( self::VERSION_OPTION, WPGO_CONTENT_CENSOR_VERSION, false );
	}

	/**
	 * Copy WordPress.org settings only when no commercial settings exist.
	 *
	 * @return bool True when a settings record was migrated.
	 */
	public static function migrate_settings() {
		$current = get_option( WPGO_CONTENT_CENSOR_OPTIONS, null );
		if ( is_array( $current ) && array() !== $current ) {
			return false;
		}

		$legacy = get_option( 'pccf_options', null );
		if ( ! is_array( $legacy ) || array() === $legacy ) {
			return false;
		}

		$migrated = array(
			'chk_post_title'       => self::checkbox( $legacy, 'chk_post_title' ),
			'chk_post_content'     => self::checkbox( $legacy, 'chk_post_content' ),
			'chk_comments'         => self::checkbox( $legacy, 'chk_comments' ),
			'chk_tags'             => self::checkbox( $legacy, 'chk_tags' ),
			'chk_tag_cloud'        => self::checkbox( $legacy, isset( $legacy['chk_tag_cloud'] ) ? 'chk_tag_cloud' : 'chk_cloud' ),
			'chk_ignore_html_attr' => '0',
			'txtar_keywords'       => isset( $legacy['txtar_keywords'] ) ? sanitize_textarea_field( (string) $legacy['txtar_keywords'] ) : '',
			'txt_exclude'          => self::sanitize_id_list( (string) ( $legacy['txt_exclude'] ?? '' ) ),
			'drp_keyword_sep'      => 'comma',
			'rdo_strict_filtering' => self::allowed( $legacy, 'rdo_strict_filtering', array( 'strict_on', 'strict_off' ), 'strict_on' ),
			'rdo_word'             => self::allowed( $legacy, 'rdo_word', array( 'first', 'all', 'firstlast' ), 'all' ),
			'drp_filter_char'      => self::allowed( $legacy, 'drp_filter_char', array( 'star', 'dollar', 'question', 'exclamation', 'hyphen', 'hash', 'tilde', 'blank' ), 'star' ),
			'rdo_case'             => self::allowed( $legacy, 'rdo_case', array( 'sen', 'insen' ), 'insen' ),
		);

		update_option( WPGO_CONTENT_CENSOR_OPTIONS, $migrated, false );
		return true;
	}

	/**
	 * Normalize a comma-separated ID list.
	 *
	 * @param string $value Raw list.
	 * @return string
	 */
	public static function sanitize_id_list( $value ) {
		$ids = preg_split( '/[^0-9]+/', $value, -1, PREG_SPLIT_NO_EMPTY );
		if ( false === $ids ) {
			return '';
		}
		$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
		return implode( ',', $ids );
	}

	/**
	 * Normalize a checkbox from an options array.
	 *
	 * @param array<string, mixed> $options Options.
	 * @param string               $key     Key.
	 * @return string
	 */
	private static function checkbox( array $options, $key ) {
		return isset( $options[ $key ] ) && '1' === (string) $options[ $key ] ? '1' : '0';
	}

	/**
	 * Return a whitelisted setting value.
	 *
	 * @param array<string, mixed> $options Options.
	 * @param string               $key     Key.
	 * @param array<int, string>   $allowed Allowed values.
	 * @param string               $fallback Fallback.
	 * @return string
	 */
	private static function allowed( array $options, $key, array $allowed, $fallback ) {
		$value = isset( $options[ $key ] ) ? (string) $options[ $key ] : '';
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}
}
