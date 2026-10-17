<?php
/*
 * XML Sitemap & Google News for Gwolle Guestbook.
 * https://wordpress.org/plugins/xml-sitemap-feed/
 * Visible under /sitemap.xml and /sitemap-custom.xml
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Get set of sitemap link data.
 *
 * @param array $pages  "posts" type.
 *
 * @return array
 *
 * @since 1.1.0
 */
function gwolle_gb_addon_xml_sitemap_feed_get_sitemap_v2( $pages ) {

	if ( ! is_array( $pages ) ) {
		$pages = array();
	}

	if ( ! is_sitemap() ) {
		return $pages;
	}

	if ( function_exists('gwolle_gb_get_books') ) {
		$postids = gwolle_gb_get_books();
	} else {
		return $pages;
	}

	foreach ( $postids as $postid ) {

		$permalink = gwolle_gb_get_permalink( $postid );

		if ( empty( $permalink ) ) {
			continue;
		}

		$book_id = get_post_meta( $postid, 'gwolle_gb_book_id', true );
		if ( empty( $book_id ) ) {
			continue;
		}

		$entries = gwolle_gb_get_entries(
			array(
				'offset'      => 0,
				'num_entries' => 1,
				'checked'     => 'checked',
				'trash'       => 'notrash',
				'spam'        => 'nospam',
				'book_id'     => $book_id,
			)
		);

		$post = get_post( $postid );
		$modified = max( $post->post_modified_gmt, $post->post_date_gmt );

		if ( is_array($entries) && ! empty($entries) ) {
			foreach ( $entries as $entry ) {
				$modified = date( DATE_W3C, $entry->get_datetime() );
			}
		}

		$num_entries = (int) get_option('gwolle_gb-entriesPerPage', 20);
		$num_entries = (int) apply_filters( 'gwolle_gb_read_num_entries', $num_entries, array() );

		$key = 'gwolle_gb_frontend_pagination_book_' . $book_id;
		$entries_total = get_transient( $key );
		if ( false === $entries_total ) {
			$entries_total = gwolle_gb_get_entry_count(
				array(
					'checked' => 'checked',
					'trash'   => 'notrash',
					'spam'    => 'nospam',
					'book_id' => $book_id,
				)
			);
			set_transient( $key, $entries_total, DAY_IN_SECONDS );
		}
		$pages_total = (int) ceil( $entries_total / $num_entries );

		for ( $i = 1; $i < ( $pages_total + 1 ); $i++ ) {
			$pagelink = add_query_arg( 'pageNum', $i, $permalink );
			$url = array();
			$url[0] = $pagelink;
			$url[1] = '0.6';
			$url[2] = 'daily'; // not yet supported
			$url[3] = $modified; // not yet supported

			if ( ! empty( $url ) ) {
				$pages[] = $url;
			}
		}
	}

	return $pages;

}
if (get_option( 'gwolle_gb_addon-sitemap', 'true') === 'true') {
	add_filter( 'xmlsf_custom_urls', 'gwolle_gb_addon_xml_sitemap_feed_get_sitemap_v2' );
}
