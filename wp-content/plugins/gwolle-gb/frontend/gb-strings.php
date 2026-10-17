<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Modifies the strings for the frontend.
 *
 * @since 1.0.0
 *
 * @param string $html The text or html that the admin wants a string to be replaced in.
 * @return string The text or html that was replaced.
 */
function gwolle_gb_addon_string_replace_v2( $html ) {

	$strings = get_option( 'gwolle_gb_addon-strings', array() );
	if ( is_string( $strings ) ) {
		$strings = maybe_unserialize( $strings );
	}
	if ( is_array($strings) && ! empty($strings) ) {
		foreach ( $strings as $oldstring => $newstring ) {
			$html = str_replace( $oldstring, $newstring, $html );
		}
	}
	return $html;

}
add_filter( 'gwolle_gb_admin_reply_header', 'gwolle_gb_addon_string_replace_v2' );
add_filter( 'gwolle_gb_antispam_label', 'gwolle_gb_addon_string_replace_v2' );
add_filter( 'gwolle_gb_author_email_label', 'gwolle_gb_addon_string_replace_v2' );
add_filter( 'gwolle_gb_author_name_label', 'gwolle_gb_addon_string_replace_v2' );
add_filter( 'gwolle_gb_author_origin_label', 'gwolle_gb_addon_string_replace_v2' );
add_filter( 'gwolle_gb_author_content_label', 'gwolle_gb_addon_string_replace_v2' );
add_filter( 'gwolle_gb_button', 'gwolle_gb_addon_string_replace_v2' );
add_filter( 'gwolle_gb_read_no_entries', 'gwolle_gb_addon_string_replace_v2' );
add_filter( 'gwolle_gb_write', 'gwolle_gb_addon_string_replace_v2' );


/*
 * Modifies the messages for the frontend.
 *
 * @since 1.0.0
 *
 * @param array $messages Array with messages with html that the admin wants a string to be replaced in.
 * @return array The messages with replaced strings.
 */
function gwolle_gb_addon_string_replace_messages_v2( $messages ) {

	$new_messages = array();

	$strings = get_option( 'gwolle_gb_addon-strings', array() );
	if ( is_string( $strings ) ) {
		$strings = maybe_unserialize( $strings );
	}
	if ( is_array($strings) && ! empty($strings) ) {
		foreach ( $messages as $message ) {
			foreach ( $strings as $oldstring => $newstring ) {
				$message = str_replace( $oldstring, $newstring, $message );
			}
			$new_messages[] = $message;
		}
		return $new_messages;
	}

	return $messages;

}
add_filter( 'gwolle_gb_messages', 'gwolle_gb_addon_string_replace_messages_v2' );
