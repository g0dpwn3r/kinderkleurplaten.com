<?php
/*
 * The WordPress Core Sitemap for Gwolle Guestbook.
 * Available at /wp-sitemap.xml
 *
 * @since WordPress 5.5.0
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Get set of sitemap link data.
 *
 * @since 2.0.2
 */
if ( class_exists( 'WP_Sitemaps_Provider' ) ) {

class WP_Sitemaps_Gwolle_GB_Addon_v2 extends WP_Sitemaps_Provider {

	/**
	 * WP_Sitemaps_Gwolle_GB constructor.
	 *
	 * @since 2.0.2
	 */
	public function __construct() {
		$this->name        = 'guestbook';
		$this->object_type = 'gwolle_gb';
	}


	/**
	 * Gets a URL list for a guestbook sitemap.
	 *
	 * @since 2.0.2
	 *
	 * @param int    $page_num       Page of results. Every book_id is related to a sitemap page.
	 * @param string $object_subtype Optional. Default empty.
	 * @return array $url_list Array of URLs for a sitemap. Every url is related to guestbook paging with pageNum parameter.
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {

		$page_num = ( $page_num - 1 ); // array starts at 0.

		if ( function_exists('gwolle_gb_get_permalinks') ) {
			$books = gwolle_gb_get_permalinks();
		} else {
			return array();
		}

		if ( isset( $books["$page_num"] ) ) {
			$book      = $books["$page_num"];
			$postid    = $book['post_id'];
			$book_id   = $book['book_id'];
			$permalink = $book['permalink'];
		} else {
			return array();
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

		$url_list = array();
		for ( $i = 1; $i < ( $pages_total + 1 ); $i++ ) {
			$pagelink = add_query_arg( 'pageNum', $i, $permalink );

			// No need to add 'lastmod', it is only a hint to search engines.
			$sitemap_entry = array(
				'loc' => $pagelink,
			);

			$url_list[] = $sitemap_entry;
		}

		return $url_list;

	}


	/**
	 * Gets the max number of pages available for the object type.
	 * The number of book_id's is the number of sitemap pages.
	 *
	 * @since 2.0.2
	 *
	 * @see WP_Sitemaps_Provider::max_num_pages
	 *
	 * @param string $object_subtype Optional. Default empty.
	 * @return int Total page count.
	 */
	public function get_max_num_pages( $object_subtype = '' ) {

		if ( function_exists('gwolle_gb_get_permalinks') ) {
			$books = gwolle_gb_get_permalinks();
		} else {
			return 1;
		}

		return count( $books );

	}

}


function gwolle_gb_addon_wp_sitemaps_register_providers_v2() {

	if ( function_exists('wp_register_sitemap_provider') && class_exists( 'WP_Sitemaps_Provider' ) ) {
		$provider = new WP_Sitemaps_Gwolle_GB_Addon_v2();
		wp_register_sitemap_provider( 'guestbook', $provider );
	}

}
if (get_option( 'gwolle_gb_addon-sitemap', 'true') === 'true') {
	add_action( 'init', 'gwolle_gb_addon_wp_sitemaps_register_providers_v2' );
}


}
