<?php
/**
 * Deterministic content filtering engine.
 *
 * @package WPGO_Content_Censor
 */

namespace WPGO\Content_Censor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters scalar strings and string arrays without depending on global state.
 */
final class Filter_Engine {

	/** Maximum number of configured keywords evaluated in one pass. */
	const MAX_KEYWORDS = 500;

	/** Maximum UTF-8 length of one keyword. */
	const MAX_KEYWORD_LENGTH = 200;

	/**
	 * Filter a WordPress value.
	 *
	 * @param mixed                $value   String or array of strings.
	 * @param array<string, mixed> $options Plugin options.
	 * @param int                  $post_id Current post ID, when available.
	 * @return mixed
	 */
	public static function filter( $value, array $options, $post_id = 0 ) {
		if ( self::is_excluded( (int) $post_id, $options ) ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$filtered = array();
			foreach ( $value as $key => $item ) {
				$filtered[ $key ] = self::filter( $item, $options, $post_id );
			}
			return $filtered;
		}

		if ( ! is_string( $value ) || '' === $value ) {
			return $value;
		}

		$keywords = self::keywords( $options );
		if ( array() === $keywords ) {
			return $value;
		}

		if ( '1' === (string) ( $options['chk_ignore_html_attr'] ?? '0' ) && function_exists( 'wp_html_split' ) ) {
			$parts = wp_html_split( $value );
			foreach ( $parts as $index => $part ) {
				if ( '' !== $part && '<' !== $part[0] ) {
					$parts[ $index ] = self::filter_text( $part, $keywords, $options );
				}
			}
			return implode( '', $parts );
		}

		return self::filter_text( $value, $keywords, $options );
	}

	/**
	 * Parse and normalize configured keywords.
	 *
	 * @param array<string, mixed> $options Plugin options.
	 * @return array<int, string>
	 */
	public static function keywords( array $options ) {
		$delimiter_map = array(
			'pipe'  => '|',
			'comma' => ',',
			'pound' => '#',
		);
		$delimiter     = $delimiter_map[ $options['drp_keyword_sep'] ?? 'pipe' ] ?? '|';
		$raw           = (string) ( $options['txtar_keywords'] ?? '' );
		$keywords      = array();

		foreach ( explode( $delimiter, $raw ) as $candidate ) {
			$candidate = trim( $candidate );
			$length    = self::length( $candidate );
			if ( $length < 3 || $length > self::MAX_KEYWORD_LENGTH ) {
				continue;
			}
			$keywords[ $candidate ] = $candidate;
			if ( count( $keywords ) >= self::MAX_KEYWORDS ) {
				break;
			}
		}

		$keywords = array_values( $keywords );
		usort(
			$keywords,
			static function ( $left, $right ) {
				return Filter_Engine::length( $right ) <=> Filter_Engine::length( $left );
			}
		);
		return $keywords;
	}

	/**
	 * Render one matched word using the selected mask.
	 *
	 * @param string $word     Matched word.
	 * @param string $mode     all, first, or firstlast.
	 * @param string $wildcard Replacement character.
	 * @return string
	 */
	public static function censor_word( $word, $mode, $wildcard ) {
		$characters = preg_split( '//u', $word, -1, PREG_SPLIT_NO_EMPTY );
		if ( false === $characters ) {
			$characters = str_split( $word );
		}
		$count = count( $characters );
		if ( 0 === $count ) {
			return $word;
		}

		if ( 'first' === $mode ) {
			return $characters[0] . str_repeat( $wildcard, max( 0, $count - 1 ) );
		}
		if ( 'firstlast' === $mode && $count > 1 ) {
			return $characters[0] . str_repeat( $wildcard, max( 0, $count - 2 ) ) . $characters[ $count - 1 ];
		}
		return str_repeat( $wildcard, $count );
	}

	/**
	 * Filter one plain-text fragment.
	 *
	 * @param string               $text     Text fragment.
	 * @param array<int, string>   $keywords Normalized keywords.
	 * @param array<string, mixed> $options  Plugin options.
	 * @return string
	 */
	private static function filter_text( $text, array $keywords, array $options ) {
		$alternatives = array_map(
			static function ( $keyword ) {
				return preg_quote( $keyword, '/' );
			},
			$keywords
		);
		$body         = '(?:' . implode( '|', $alternatives ) . ')';
		$strict       = 'strict_off' !== (string) ( $options['rdo_strict_filtering'] ?? 'strict_on' );
		if ( $strict ) {
			$body = '(?<![\p{L}\p{N}_])' . $body . '(?![\p{L}\p{N}_])';
		}

		$case_insensitive = 'sen' !== (string) ( $options['rdo_case'] ?? 'insen' );
		$utf8             = 1 === preg_match( '//u', $text );
		$modifiers        = ( $case_insensitive ? 'i' : '' ) . ( $utf8 ? 'u' : '' );
		$pattern          = '/' . $body . '/' . $modifiers;
		$mode             = (string) ( $options['rdo_word'] ?? 'all' );
		$wildcard         = self::wildcard( (string) ( $options['drp_filter_char'] ?? 'star' ) );

		$result = preg_replace_callback(
			$pattern,
			static function ( array $matched ) use ( $mode, $wildcard ) {
				return Filter_Engine::censor_word( $matched[0], $mode, $wildcard );
			},
			$text
		);

		return null === $result ? $text : $result;
	}

	/**
	 * Check the configured exclusion list.
	 *
	 * @param int                  $post_id Current post ID.
	 * @param array<string, mixed> $options Plugin options.
	 * @return bool
	 */
	private static function is_excluded( $post_id, array $options ) {
		if ( $post_id <= 0 ) {
			return false;
		}
		$ids = preg_split( '/\s*,\s*/', (string) ( $options['txt_exclude'] ?? '' ), -1, PREG_SPLIT_NO_EMPTY );
		if ( false === $ids ) {
			return false;
		}
		return in_array( $post_id, array_map( 'intval', $ids ), true );
	}

	/**
	 * Convert the setting key into a replacement character.
	 *
	 * @param string $name Setting key.
	 * @return string
	 */
	private static function wildcard( $name ) {
		$wildcards = array(
			'star'        => '*',
			'dollar'      => '$',
			'question'    => '?',
			'exclamation' => '!',
			'hyphen'      => '-',
			'hash'        => '#',
			'tilde'       => '~',
			'blank'       => '',
		);
		return $wildcards[ $name ] ?? '*';
	}

	/**
	 * Return the Unicode-aware string length when available.
	 *
	 * @param string $value String value.
	 * @return int
	 */
	private static function length( $value ) {
		return function_exists( 'mb_strlen' ) ? mb_strlen( $value, 'UTF-8' ) : strlen( $value );
	}
}
