<?php
/*
 * @package WPSEO\XML_Sitemaps
 * https://wordpress.org/plugins/wordpress-seo/
 * Included from extra function in /gwolle-gb.php
 * Available under /sitemap_index.xml and /guestbook-sitemap.xml
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Yoast SEO sitemap provider for Gwolle Guestbook.
 *
 * @since 1.1.0
 */
class WPSEO_Gwolle_GB_Sitemap_Provider_v2 implements WPSEO_Sitemap_Provider {

	/**
	 * Check if provider supports given item type.
	 *
	 * @param string $type Type string to check for.
	 *
	 * @return boolean
	 */
	public function handles_type( $type ) {
		return $type === 'guestbook';
	}

	/**
	 * @param int $max_entries Entries per sitemap.
	 *
	 * @return array
	 */
	public function get_index_links( $max_entries ) {

		$entries = gwolle_gb_get_entries(
			array(
				'offset'      => 0,
				'num_entries' => 1,
				'checked'     => 'checked',
				'trash'       => 'notrash',
				'spam'        => 'nospam',
			)
		);

		if ( function_exists('gwolle_gb_get_books') ) {
			$postids = gwolle_gb_get_books();
		} else {
			return array();
		}

		foreach ( $postids as $postid ) {
			if ( ! isset( $modified ) ) {
				$post = get_post( $postid );
				$modified = max( $post->post_modified_gmt, $post->post_date_gmt );
			}
		}
		if ( is_array($entries) && ! empty($entries) ) {
			foreach ( $entries as $entry ) {
				$modified = date( DATE_W3C, $entry->get_datetime() );
			}
		}

		$index   = array();
		$index[] = array(
			'loc'     => WPSEO_Sitemaps_Router::get_base_url( 'guestbook-sitemap.xml' ),
			'lastmod' => $modified,
		);

		return $index;
	}


	/**
	 * Get set of sitemap link data.
	 *
	 * @param string $type         Sitemap type.
	 * @param int    $max_entries  Entries per sitemap.
	 * @param int    $current_page Current page of the sitemap.
	 *
	 * @return array
	 */
	public function get_sitemap_links( $type, $max_entries, $current_page ) {

		$links = array();

		if ( function_exists('gwolle_gb_get_books') ) {
			$postids = gwolle_gb_get_books();
		} else {
			return array();
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

				$url = array(
					'loc' => $pagelink,
					'mod' => $modified,

					// Deprecated, kept for backwards data compat. R.
					'chf' => 'daily',
					'pri' => 1,
				);

				if ( ! empty( $url ) ) {
					$links[] = $url;
				}
			}
		}

		return $links;
	}

}
