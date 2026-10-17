<?php
/*
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Returns array of meta fields for starrating.
 *
 * @param array $entry_ids list of ints with ids
 * @return array
 *
 * @since 1.0.0
 */
function gwolle_gb_addon_get_meta_ratings_v2( $entry_ids ) {
	global $wpdb;

	$where = " 1 = 1";

	if ( ! is_array($entry_ids) ) {
		return false;
	}
	$entry_ids = array_map( 'absint', $entry_ids );
	$entry_ids = implode(',', $entry_ids);
	$where .= "
		AND
		entry_id IN (" . $entry_ids . ")";

	$where .= "
		AND
		meta_key = '" . 'starrating' . "'";

	$tablename = $wpdb->prefix . "gwolle_gb_meta";
	$limit = ' LIMIT 999999999999999 ';
	$offset = ' OFFSET 0 ';

	$sql = "
			SELECT
				`meta_value`
			FROM
				" . $tablename . "
			WHERE
				" . $where . "
			" . $limit . " " . $offset . "
			;";

	$starratings = $wpdb->get_results( $sql, ARRAY_N );

	if ( is_array($starratings) && ! empty($starratings) ) {
		$_starratings = array();
		foreach ( $starratings as $starrating ) {
			$_starratings[] = (int) $starrating[0];
		}
		return $_starratings;
	}

	return $starratings;
}


/*
 * Returns total count, total ratings and average
 *
 * @param array $args args from the shortcode.
 * @return array with 3 key/value pairs.
 *
 * @since 1.0.0
 */
function gwolle_gb_addon_starrating_average_v2( $args ) {

	if ( isset( $args['book_id'] ) ) {
		$key = 'gwolle_gb_addon_ratings_entries_book_' . $args['book_id'];
	} else {
		$key = 'gwolle_gb_addon_ratings_entries';
	}
	$entry_ids = get_transient( $key );
	if ( false === $entry_ids ) {
		$entry_ids = gwolle_gb_get_entry_ids( $args );
		set_transient( $key, $entry_ids, DAY_IN_SECONDS );
	}

	if ( isset( $args['book_id'] ) ) {
		$key = 'gwolle_gb_addon_ratings_book_' . $args['book_id'];
	} else {
		$key = 'gwolle_gb_addon_ratings';
	}
	$ratings = get_transient( $key );
	if ( false === $ratings ) {
		$ratings = gwolle_gb_addon_get_meta_ratings_v2( $entry_ids );
		set_transient( $key, $ratings, DAY_IN_SECONDS );
	}

	$totals	= array(
		'total'   => (int) 0,
		'count'   => (int) 0,
		'average' => (float) 0,
	);
	foreach ( $ratings as $rating ) {
		if ( ! empty( $rating ) ) {
			$totals['total'] = ( (int) $totals['total'] + (int) $rating );
			$totals['count']++;
		}
	}
	if ( isset($totals['count']) && $totals['count'] > 0 && isset($totals['total']) && $totals['total'] > 0 ) {
		$totals['average'] = ( $totals['total'] / $totals['count'] );
	}

	return $totals;
}


/*
 * Returns html for frontend, cumulative ratings for this book_id.
 *
 * @param string html to be output.
 *        array args the args from the shortcode.
 * @return string html to be output.
 *
 * @since 1.0.5
 */
function gwolle_gb_addon_starrating_average_hook_v2( $html, $args ) {

	if ( get_option( 'gwolle_gb_addon-starrating', 'false' ) === 'false' ) {
		return $html;
	}
	if ( get_option( 'gwolle_gb_addon-starrating_avg', 'false' ) === 'false' ) {
		return $html;
	}
	$html = gwolle_gb_addon_starrating_average_html_v2( $html, $args );

	return $html;

}
add_filter( 'gwolle_gb_entries_list_before', 'gwolle_gb_addon_starrating_average_hook_v2', 18, 2 );


/*
 * Returns html for frontend, cumulative ratings for this book_id.
 * Used as a hook and as a widget.
 *
 * @param string html to be output.
 * @param array  args the args from the shortcode.
 * @param title  string title of the widget or page, optional. since 2.0.1.
 * @return string html to be output.
 *
 * @since 1.0.0
 */
function gwolle_gb_addon_starrating_average_html_v2( $html, $args, $title = '' ) {

	$totals = gwolle_gb_addon_starrating_average_v2( $args );
	$average_localized = number_format_i18n( (float) $totals['average'], 2 );
	if ( strlen( $title ) === 0 ) {
		$title = get_the_title();
	}
	$type = get_option( 'gwolle_gb_addon-starrating_type', 'LocalBusiness' );

	$html .= '
				<div class="rating-totals" style="padding:8px 0;">
					<div class="rating-summary">
						<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
							<div itemprop="itemReviewed" itemscope itemtype="http://schema.org/' . esc_attr( $type ) . '">
								<meta itemprop="name" content="' . esc_attr( $title ) . '">
							</div>';
							if ( isset($totals['count']) && $totals['count'] > 0 && isset($totals['total']) && $totals['total'] > 0 ) {
								$html .= '
								<div class="rateit" data-rateit-value="' . (float) $totals['average'] . '" data-rateit-ispreset="true" data-rateit-readonly="true"></div>&nbsp;' .
								sprintf( __( 'Average Rating: <strong>%s out of %s</strong> (%s votes)', 'gwolle-gb' ), '<span itemprop="ratingValue">' . $average_localized . '</span>', '<span itemprop="bestRating">5</span>', '<span itemprop="ratingCount">' . (int) $totals['count'] . '</span>' );
							}
	$html .= '
						</div>
					</div>
				</div>';

	return $html;

}


/*
 * Clear the transients.
 */
function gwolle_gb_addon_clear_starrating_v2( $entry = false ) {

	/* Gwolle: Transient for frontend pagination counter */
	if ( is_object( $entry ) && is_a( $entry, 'gwolle_gb_entry' ) ) {
		$book_id = $entry->get_book_id();

		$key = 'gwolle_gb_addon_ratings_entries_book_' . $book_id;
		delete_transient( $key );

		$key = 'gwolle_gb_addon_ratings_book_' . $book_id;
		delete_transient( $key );
	}

}
add_action( 'gwolle_gb_save_entry_admin', 'gwolle_gb_addon_clear_starrating_v2' );
add_action( 'gwolle_gb_save_entry_frontend', 'gwolle_gb_addon_clear_starrating_v2' );
