<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Author email for single entry.
 *
 * @since 1.0.5
 */
function gwolle_gb_entry_metabox_lines_email_v2( $gb_metabox, $entry ) {
	if (get_option( 'gwolle_gb_addon-email', 'false') === 'true') {
		$author_name = gwolle_gb_sanitize_output( trim( $entry->get_author_name() ) );
		$author_email = gwolle_gb_addon_encode_email_v2( trim( $entry->get_author_email() ) );
		if ( $author_email ) {
			$title = sprintf( esc_attr__('Email %s', 'gwolle-gb'), esc_attr( $author_name ) );
			$gb_metabox .= '
					<div class="gb-metabox-line gb-metabox-line-email">
						<a href="mailto:' . esc_attr( $author_email ) . '" title="' . esc_attr( $title ) . '" class="gwolle-gb-email">' . esc_html__('Email author', 'gwolle-gb') . '</a>
					</div>';
		}
	}
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_metabox_lines', 'gwolle_gb_entry_metabox_lines_email_v2', 60, 2 );


/*
 * Searches for plain email addresses in given $string and
 * encodes them (by default) with the help of gwolle_gb_addon_encode_str().
 *
 * Regular expression is based on based on John Gruber's Markdown.
 * http://daringfireball.net/projects/markdown/
 *
 * Both functions are taken from:
 * https://wordpress.org/plugins/email-address-encoder/
 * version 1.0.5
 *
 * @param string $string Text with email addresses to encode
 * @return string $string Given text with encoded email addresses
 *
 * @since 1.0.6
 */
function gwolle_gb_addon_encode_email_v2( $string ) {

	// abort if `$string` isn't a string
	if ( ! is_string( $string ) ) {
		return $string;
	}

	// abort if `gwolle_gb_addon_at_sign_check` is true and `$string` doesn't contain a @-sign
	if ( apply_filters( 'gwolle_gb_addon_at_sign_check', true ) && strpos( $string, '@' ) === false ) {
		return $string;
	}

	// override regex pattern with the 'gwolle_gb_addon_regexp' filter
	$regexp = apply_filters(
		'gwolle_gb_addon_regexp',
		'{
			(?:mailto:)?
			(?:
				[-!#$%&*+/=?^_`.{|}~\w\x80-\xFF]+
			|
				".*?"
			)
			\@
			(?:
				[-a-z0-9\x80-\xFF]+(\.[-a-z0-9\x80-\xFF]+)*\.[a-z]+
			|
				\[[\d.a-fA-F:]+\]
			)
		}xi'
	);

	// Anonymous functions break on PHP 5.2.
	if ( version_compare( PHP_VERSION, '5.3', '>' ) ) { // PHP >= 5.3
		return preg_replace_callback(
			$regexp,
			function( $matches ) {
				return gwolle_gb_addon_encode_str_v2( $matches[0] );
			},
			$string
		);
	}
	return $string; // when all else fails.
}


/*
 * Encodes each character of the given string as either a decimal
 * or hexadecimal entity, in the hopes of foiling most email address
 * harvesting bots.
 *
 * Based on Michel Fortin's PHP Markdown:
 *   http://michelf.com/projects/php-markdown/
 * Which is based on John Gruber's original Markdown:
 *   http://daringfireball.net/projects/markdown/
 * Whose code is based on a filter by Matthew Wickline, posted to
 * the BBEdit-Talk with some optimizations by Milian Wolff.
 *
 * @param string $string Text with email addresses to encode
 * @return string $string Given text with encoded email addresses
 *
 * @since 1.0.6
 */
function gwolle_gb_addon_encode_str_v2( $string ) {

	$chars = str_split( $string );
	$seed = mt_rand( 0, (int) abs( crc32( $string ) / strlen( $string ) ) );

	foreach ( $chars as $key => $char ) {

		$ord = ord( $char );

		if ( $ord < 128 ) { // ignore non-ascii chars

			$r = ( ( $seed * ( 1 + $key ) ) % 100 ); // pseudo "random function"

			if ( $r > 60 && $char != '@' ); // plain character (not encoded), if not @-sign
			else if ( $r < 45 ) $chars["$key"] = '&#x' . dechex( $ord ) . ';'; // hexadecimal
			else $chars["$key"] = '&#' . $ord . ';'; // decimal (ascii)

		}

	}

	return implode( '', $chars );

}
