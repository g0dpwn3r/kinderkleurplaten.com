<?php
/**
 * Historical SDK compatibility entry point.
 *
 * @package WPGO_Content_Censor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpcf_fs' ) ) {
	// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Published compatibility API; new code uses content_censor_fs().
	/**
	 * Retain the helper used by the released 1.0.0 migration build.
	 *
	 * @return Freemius
	 */
	function wpcf_fs() {
		return content_censor_fs();
	}
	// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
}
