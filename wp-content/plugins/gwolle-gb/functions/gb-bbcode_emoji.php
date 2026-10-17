<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



/*
 * Parse the BBcode into HTML for output.
 *
 * @param string $entry_content content that needs to be parsed
 * @return string parsed content
 */
function gwolle_gb_bbcode_parse( $entry_content ) {

	$bb = array();
	$html = array();

	$bb[] = '#\[b\](.*?)\[/b\]#si';
	$html[] = '<strong>\\1</strong>';
	$bb[] = '#\[i\](.*?)\[/i\]#si';
	$html[] = '<i>\\1</i>';
	$bb[] = '#\[u\](.*?)\[/u\]#si';
	$html[] = '<u>\\1</u>';
	// We run the regex on lists twice to support sublists.
	$bb[] = '#\[ul\](.*?)\[/ul\]#si';
	$html[] = '<ul>\\1</ul>';
	$bb[] = '#\[ul\](.*?)\[/ul\]#si';
	$html[] = '<ul>\\1</ul>';
	$bb[] = '#\[ol\](.*?)\[/ol\]#si';
	$html[] = '<ol>\\1</ol>';
	$bb[] = '#\[ol\](.*?)\[/ol\]#si';
	$html[] = '<ol>\\1</ol>';
	$bb[] = '#\[li\](.*?)\[/li\]#si';
	$html[] = '<li>\\1</li>';
	$bb[] = '#\[li\](.*?)\[/li\]#si';
	$html[] = '<li>\\1</li>';
	$entry_content = preg_replace( $bb, $html, $entry_content );


	// First images, then links, so we support images inside links.
	$bbcode_img_enabled = apply_filters( 'gwolle_gb_bbcode_img_enabled', true );
	if ( $bbcode_img_enabled ) {
		//$bbcode_img_referrer = apply_filters( 'gwolle_gb_bbcode_img_referrer', 'no-referrer' );
		$pattern = '#\[img\]([^\[]*)\[/img\]#i';
		//$replace = '<img src="\\1" alt="" referrerpolicy="' . $bbcode_img_referrer . '" loading="lazy" />'; // do not use, we need wp_kses()
		//$entry_content = preg_replace( $pattern, $replace, $entry_content );
		$entry_content = preg_replace_callback( $pattern, 'gwolle_gb_bbcode_parse_img_callback', $entry_content );
	} else {
		$pattern = '#\[img\]([^\[]*)\[/img\]#i';
		$replace = '';
		$entry_content = preg_replace( $pattern, $replace, $entry_content );
	}


	// Youtube embeds.
	$bbcode_youtube_enabled = apply_filters( 'gwolle_gb_bbcode_youtube_enabled', true );
	if ( $bbcode_youtube_enabled ) {
		$entry_content = gwolle_gb_bbcode_youtube_embed( $entry_content );
	}


	// Links with quotes.
	//$bbcode_link_rel = apply_filters( 'gwolle_gb_bbcode_link_rel', 'nofollow noopener noreferrer' );
	$pattern = '#\[url href=\&\#034\;([^\]]*)\&\#034\;\]([^\[]*)\[/url\]#i';
	//$replace = '<a href="\\1" target="_blank" rel="' . $bbcode_link_rel . '">\\2</a>';
	//$entry_content = preg_replace( $pattern, $replace, $entry_content ); // do not use, we need wp_kses()
	$entry_content = preg_replace_callback( $pattern, 'gwolle_gb_bbcode_parse_link_callback', $entry_content );

	// Links without quotes.
	$pattern = '#\[url href=([^\]]*)\]([^\[]*)\[/url\]#i';
	//$replace = '<a href="\\1" target="_blank" rel="' . $bbcode_link_rel . '">\\2</a>';
	//$entry_content = preg_replace( $pattern, $replace, $entry_content ); // do not use, we need wp_kses()
	$entry_content = preg_replace_callback( $pattern, 'gwolle_gb_bbcode_parse_link_callback', $entry_content );


	if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
		// fix nl2br adding <br />'s
		$entry_content = str_replace( '<br /><ol>', '<ol>', $entry_content );
		$entry_content = str_replace( '<ol><br />', '<ol>', $entry_content );
		$entry_content = str_replace( '</ol><br />', '</ol>', $entry_content );
		$entry_content = str_replace( '<br /><ul>', '<ul>', $entry_content );
		$entry_content = str_replace( '<ul><br />', '<ul>', $entry_content );
		$entry_content = str_replace( '</ul><br />', '</ul>', $entry_content );
		$entry_content = str_replace( '</li><br />', '</li>', $entry_content );
	}

	return $entry_content;

}


/*
 * Sanitize the BBcode images with a callback for preg_replace_callback().
 *
 * @param array $matches content that needs to be sanitized
 * @return sanitized content
 *
 * @since 4.9.3
 */
function gwolle_gb_bbcode_parse_img_callback( $matches ) {

	$bbcode_img_referrer = apply_filters( 'gwolle_gb_bbcode_img_referrer', 'no-referrer' );

	/*
	 * as usual:
	 * $matches[0] is the complete match
	 * $matches[1] the match for the first subpattern enclosed in '(...)'
	 * $matches[2] the match for the second subpattern enclosed in '(...)'
	 */

	// overwrite data with our own.
	$matches[1] = '<img src="' . esc_url( $matches[1] ) . '" alt="" referrerpolicy="' . $bbcode_img_referrer . '" loading="lazy" />';

	return $matches[1];

}


/*
 * Sanitize the BBcode links with a callback for preg_replace_callback().
 *
 * @param array $matches content that needs to be sanitized
 * @return sanitized content
 *
 * @since 4.9.3
 */
function gwolle_gb_bbcode_parse_link_callback( $matches ) {

	$bbcode_link_rel = apply_filters( 'gwolle_gb_bbcode_link_rel', 'nofollow noopener noreferrer' );

	/*
	 * as usual:
	 * $matches[0] is the complete match
	 * $matches[1] the match for the first subpattern enclosed in '(...)'
	 * $matches[2] the match for the second subpattern enclosed in '(...)'
	 */

	// overwrite data with our own.
	// use wp_kses_post because an image might be inside a link.
	$matches[1] = '<a href="' . esc_url( $matches[1] ) . '" target="_blank" rel="' . $bbcode_link_rel . '">' . wp_kses_post( $matches[2] ) . '</a>';
	$matches[2] = '';

	return $matches[1] . ($matches[2]);

}


/*
 * Strip the BBcode from the output.
 *
 * @param string $str content that needs to be stripped
 * @return string stripped content
 */
function gwolle_gb_bbcode_strip( $entry_content ) {

	$bb = array();
	$html = array();

	$bb[] = '#\[b\](.*?)\[/b\]#si';
	$html[] = '\\1';
	$bb[] = '#\[i\](.*?)\[/i\]#si';
	$html[] = '\\1';
	$bb[] = '#\[u\](.*?)\[/u\]#si';
	$html[] = '\\1';
	$bb[] = '#\[ul\](.*?)\[/ul\]#si';
	$html[] = '\\1';
	$bb[] = '#\[ol\](.*?)\[/ol\]#si';
	$html[] = '\\1';
	$bb[] = '#\[li\](.*?)\[/li\]#si';
	$html[] = '\\1';
	$entry_content = preg_replace( $bb, $html, $entry_content );

	$pattern = '#\[url href=([^\]]*)\]([^\[]*)\[/url\]#i';
	$replace = '\\1';
	$entry_content = preg_replace( $pattern, $replace, $entry_content );

	$pattern = '#\[img\]([^\[]*)\[/img\]#i';
	$replace = '';
	$entry_content = preg_replace( $pattern, $replace, $entry_content );

	$pattern = '#\[youtube\]([^\[]*)\[/youtube\]#i';
	$replace = '';
	$entry_content = preg_replace( $pattern, $replace, $entry_content );

	return $entry_content;

}


/*
 * Get the list of Emoji for the form.
 *
 * @return string html with a elements with emoji
 */
function gwolle_gb_get_emoji() {

	$emoji = '
		<a title="😄" class="gwolle_gb_emoji_1 noslimstat">😄</a>
		<a title="😃" class="gwolle_gb_emoji_2 noslimstat">😃</a>
		<a title="😉" class="gwolle_gb_emoji_3 noslimstat">😉</a>
		<a title="😊" class="gwolle_gb_emoji_4 noslimstat">😊</a>
		<a title="😚" class="gwolle_gb_emoji_5 noslimstat">😚</a>
		<a title="😗" class="gwolle_gb_emoji_6 noslimstat">😗</a>
		<a title="😜" class="gwolle_gb_emoji_7 noslimstat">😜</a>
		<a title="😛" class="gwolle_gb_emoji_8 noslimstat">😛</a>
		<a title="😳" class="gwolle_gb_emoji_9 noslimstat">😳</a>
		<a title="😁" class="gwolle_gb_emoji_10 noslimstat">😁</a>
		<a title="😬" class="gwolle_gb_emoji_11 noslimstat">😬</a>
		<a title="😌" class="gwolle_gb_emoji_12 noslimstat">😌</a>
		<a title="😞" class="gwolle_gb_emoji_13 noslimstat">😞</a>
		<a title="😘" class="gwolle_gb_emoji_14 noslimstat">😘</a>
		<a title="😍" class="gwolle_gb_emoji_15 noslimstat">😍</a>
		<a title="😢" class="gwolle_gb_emoji_16 noslimstat">😢</a>
		<a title="😂" class="gwolle_gb_emoji_17 noslimstat">😂</a>
		<a title="😭" class="gwolle_gb_emoji_18 noslimstat">😭</a>
		<a title="😅" class="gwolle_gb_emoji_19 noslimstat">😅</a>
		<a title="😓" class="gwolle_gb_emoji_20 noslimstat">😓</a>
		<a title="😩" class="gwolle_gb_emoji_21 noslimstat">😩</a>
		<a title="😮" class="gwolle_gb_emoji_22 noslimstat">😮</a>
		<a title="😱" class="gwolle_gb_emoji_23 noslimstat">😱</a>
		<a title="😠" class="gwolle_gb_emoji_24 noslimstat">😠</a>
		<a title="😡" class="gwolle_gb_emoji_25 noslimstat">😡</a>
		<a title="😤" class="gwolle_gb_emoji_26 noslimstat">😤</a>
		<a title="😋" class="gwolle_gb_emoji_27 noslimstat">😋</a>
		<a title="😎" class="gwolle_gb_emoji_28 noslimstat">😎</a>
		<a title="😴" class="gwolle_gb_emoji_29 noslimstat">😴</a>
		<a title="😈" class="gwolle_gb_emoji_30 noslimstat">😈</a>
		<a title="😇" class="gwolle_gb_emoji_31 noslimstat">😇</a>
		<a title="😕" class="gwolle_gb_emoji_32 noslimstat">😕</a>
		<a title="😏" class="gwolle_gb_emoji_33 noslimstat">😏</a>
		<a title="😑" class="gwolle_gb_emoji_34 noslimstat">😑</a>
		<a title="👲" class="gwolle_gb_emoji_35 noslimstat">👲</a>
		<a title="👮" class="gwolle_gb_emoji_36 noslimstat">👮</a>
		<a title="💂" class="gwolle_gb_emoji_37 noslimstat">💂</a>
		<a title="👶" class="gwolle_gb_emoji_38 noslimstat">👶</a>
		<a title="❤" class="gwolle_gb_emoji_39 noslimstat">❤</a>
		<a title="💔" class="gwolle_gb_emoji_40 noslimstat">💔</a>
		<a title="💕" class="gwolle_gb_emoji_41 noslimstat">💕</a>
		<a title="💘" class="gwolle_gb_emoji_42 noslimstat">💘</a>
		<a title="💌" class="gwolle_gb_emoji_43 noslimstat">💌</a>
		<a title="💋" class="gwolle_gb_emoji_44 noslimstat">💋</a>
		<a title="🎁" class="gwolle_gb_emoji_45 noslimstat">🎁</a>
		<a title="💰" class="gwolle_gb_emoji_46 noslimstat">💰</a>
		<a title="💍" class="gwolle_gb_emoji_47 noslimstat">💍</a>
		<a title="👍" class="gwolle_gb_emoji_48 noslimstat">👍</a>
		<a title="👎" class="gwolle_gb_emoji_49 noslimstat">👎</a>
		<a title="👌" class="gwolle_gb_emoji_50 noslimstat">👌</a>
		<a title="✌️" class="gwolle_gb_emoji_51 noslimstat">✌️</a>
		<a title="🤘️" class="gwolle_gb_emoji_52 noslimstat">🤘</a>
		<a title="👏" class="gwolle_gb_emoji_53 noslimstat">👏</a>
		<a title="🎵" class="gwolle_gb_emoji_54 noslimstat">🎵</a>
		<a title="☕️" class="gwolle_gb_emoji_55 noslimstat">☕️</a>
		<a title="🍵" class="gwolle_gb_emoji_56 noslimstat">🍵</a>
		<a title="🍺" class="gwolle_gb_emoji_57 noslimstat">🍺</a>
		<a title="🍷" class="gwolle_gb_emoji_58 noslimstat">🍷</a>
		<a title="🍼" class="gwolle_gb_emoji_59 noslimstat">🍼</a>
		<a title="☀️" class="gwolle_gb_emoji_60 noslimstat">☀️</a>
		<a title="🌤" class="gwolle_gb_emoji_61 noslimstat">🌤</a>
		<a title="🌦" class="gwolle_gb_emoji_62 noslimstat">🌦</a>
		<a title="🌧" class="gwolle_gb_emoji_63 noslimstat">🌧</a>
		<a title="🌜" class="gwolle_gb_emoji_64 noslimstat">🌜</a>
		<a title="🌈" class="gwolle_gb_emoji_65 noslimstat">🌈</a>
		<a title="🏝" class="gwolle_gb_emoji_66 noslimstat">🏝</a>
		<a title="🎅" class="gwolle_gb_emoji_67 noslimstat">🎅</a>
		';
	/*
	 * Filters the list of emoji shown on textarea/bbcode/emoji at the frontend form.
	 *
	 * Returning the altered string is the recommended way use this filter.
	 * You can add emoji characters or replace them with str_replace.
	 *
	 * @since 2.3.0
	 *
	 * @param string $emoji The list of Emoji.
	 */
	$emoji = apply_filters( 'gwolle_gb_get_emoji', $emoji );

	return $emoji;

}


/*
 * Convert to 3byte Emoji for storing in db, if db-charset is not utf8mb4.
 *
 * @param string $entry_content text string to encode
 * @param string $field the database field that is used for that string, will be checked on charset.
 * @return string original input entry_content encoded or not.
 *
 * @since 1.3.5
 * @since WordPress 4.2.0 for wp_encode_emoji function.
 */
function gwolle_gb_maybe_encode_emoji( $entry_content, $field ) {

	global $wpdb;

	$db_charset = $wpdb->charset;
	if ( 'utf8mb4' !== $db_charset ) {
		if ( function_exists( 'wp_encode_emoji' ) && function_exists( 'mb_convert_encoding' ) ) {
			// No support for the proper charset, so encode to html entities.
			$entry_content = wp_encode_emoji( $entry_content );
			// Enable this for debugging.
			// gwolle_gb_add_message( '<p class="debug_emoji"><strong>Ran wp_encode_emoji function.</strong></p>', false, false );
		}
		// Enable this for debugging.
		// gwolle_gb_add_message( '<p class="debug_emoji"><strong>MySQL Charset: ' . $charset . '</strong></p>', false, false );
	}

	return $entry_content;

}


/*
 * Add CSS to the Footer to make it possible to hide BBcode buttons.
 */
function gwolle_gb_bbcode_disabled() {

	$bbcode_img_enabled = apply_filters( 'gwolle_gb_bbcode_img_enabled', true );
	if ( ! $bbcode_img_enabled ) {
		echo '
		<style id="gwolle-gb-bbcode-img-disabled" type="text/css">
		html body .markItUp li.markItUpButton5 {
			display: none;
		}
		</style>
		';
	}

	$bbcode_youtube_enabled = apply_filters( 'gwolle_gb_bbcode_youtube_enabled', true );
	if ( ! $bbcode_youtube_enabled ) {
		echo '
		<style id="gwolle-gb-bbcode-youtube-disabled" type="text/css">
		html body .markItUp li.markItUpButton6 {
			display: none;
		}
		</style>
		';
	}

}
add_action( 'wp_footer', 'gwolle_gb_bbcode_disabled' );


function gwolle_gb_bbcode_img_disabled() {

	_deprecated_function( __FUNCTION__, ' 4.8.0', 'gwolle_gb_bbcode_disabled()' );
	gwolle_gb_bbcode_disabled();

}


/*
 * Convert youtube embed from bbcode to full iframe embeds in entry content.
 *
 * @param string $entry_content text with entry_content including possible youtube embed.
 * @return array with youtube bbcode changed for youtube iframe embeds (or unchanged if nothing found).
 *
 * @since 4.8.0
 */
function gwolle_gb_bbcode_youtube_embed( $entry_content ) {

	$embeds = array();

	/*
	 * Check if the custom string matches the YouTube pattern
	 * Use a regular expression to match the custom [youtube] shortcode.
	 * The regex captures the YouTube URL inside the shortcode, which is then extracted into the matches array.
	 * matches[0] is an array with the original string [youtube]youtube.com...[/youtube]
	 * matches[1] is an array with the matching string youtube.com...
	 *
	 */
	if ( preg_match_all( '#\[youtube\]([^\[]*)\[/youtube\]#i', $entry_content, $matches ) ) {


/*
array(2) {
	[0]=>
	array(2) {
		[0]=>
			string(62) "[youtube]https://www.youtube.com/watch?v=FqkW3jpfLC8[/youtube]"
		[1]=>
			string(62) "[youtube]https://www.youtube.com/watch?v=8vmv6xsdjO4[/youtube]"
		}
	[1]=>
	array(2) {
		[0]=>
			string(43) "https://www.youtube.com/watch?v=FqkW3jpfLC8"
		[1]=>
			string(43) "https://www.youtube.com/watch?v=8vmv6xsdjO4"
		}
}
*/


		if ( is_array( $matches[0] ) && ! empty( $matches[0] ) ) {

			$count = count( $matches[0] );
			for ( $i = 0; $i < $count; $i++ ) {

				$match_0 = $matches[0][$i];
				$match_1 = $matches[1][$i];

				if ( strlen( $match_0 ) < 1 || strlen( $match_1 ) < 1 ) {
					continue; // something broke somewhere
				}

				// Use WP_Embed to handle the YouTube embed
				// Create a new WP_Embed object and use its run_shortcode() method to embed the YouTube URL.
				// This method wraps the YouTube URL in an embed shortcode and processes it, using the default WordPress embed system.
				// The run_shortcode() method is used with [embed] shortcode for embedding, as WordPress allows embeds from platforms like YouTube through this shortcode.
				$wp_embed = new WP_Embed();
				$embed_code = $wp_embed->run_shortcode('[embed]' . esc_url( $match_1 ) . '[/embed]'); // iframe code

				// Optionally, you can also use WP_oEmbed directly to get the HTML
				// Use WP_oEmbed to fetch the oEmbed HTML.
				// The get_html() method from WP_oEmbed returns the HTML embed code for the URL, which can be directly output.
				//$oembed = new WP_oEmbed();
				//$oembed_html = $oembed->get_html( $match_1 );

				// You can choose to use either embed_code or oembed_html depending on your needs.
				$entry_content = str_replace( $match_0, $embed_code, $entry_content );

			}
		}
	}

	return $entry_content;

}
