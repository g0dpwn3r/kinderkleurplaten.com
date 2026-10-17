<?php
/*
 * XML Sitemap Generator for Google for Gwolle Guestbook.
 * https://wordpress.org/plugins/google-sitemap-generator/
 * Visible under /sitemap.xml and /sitemap-misc.xml
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Get set of sitemap link data.
 * Will get published under /sitemap-misc.xml
 *
 * @uses class GoogleSitemapGenerator
 *
 * @since 1.1.0
 */
function gwolle_gb_addon_google_sitemap_generator_get_sitemap_v2() {

	if ( function_exists('gwolle_gb_get_books') ) {
		$postids = gwolle_gb_get_books();
	} else {
		return;
	}

	if ( method_exists( 'GoogleSitemapGenerator', 'get_instance' ) ) {
		$generatorObject = &GoogleSitemapGenerator::get_instance();
	} else if (method_exists( 'GoogleSitemapGenerator', 'GetInstance' ) ) {
		$generatorObject = &GoogleSitemapGenerator::GetInstance();
	}

	if ( $generatorObject != null ) {

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
			$modified = strtotime( $modified ); // needs timestamp
			if ( is_array($entries) && ! empty($entries) ) {
				foreach ( $entries as $entry ) {
					$modified = $entry->get_datetime(); // needs timestamp
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

				if ( $generatorObject != null ) {
					if ( method_exists( 'GoogleSitemapGenerator', 'add_url' ) ) {
						$generatorObject->add_url( $pagelink, $modified, 'daily', 0.6 );
					} else if ( method_exists( 'GoogleSitemapGenerator', 'AddUrl' ) ) {
						$generatorObject->AddUrl( $pagelink, $modified, 'daily', 0.6 );
					}
				}
			}
		}
	}

}
if (get_option( 'gwolle_gb_addon-sitemap', 'true') === 'true') {
	add_action( 'sm_buildmap', 'gwolle_gb_addon_google_sitemap_generator_get_sitemap_v2' );
}
