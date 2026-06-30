<?php
/**
 * Plugin Name: KK Self-Healing Category Thumbnails
 * Description: Keeps wp_termmeta.thumbnail_id for the 'kleurplaat_categorie'
 *              taxonomy automatically in sync with the latest published
 *              kleurplaat in each term. Hooks into set_object_terms +
 *              save_post_kleurplaten, uses direct $wpdb (no WP_Query) and
 *              guards against orphan-meta cleanup plugins. Includes a
 *              twicedaily cron re-sync and a WP-CLI command.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:      kinderkleurplaten
 * License:     GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class KK_Self_Healing_Cat_Thumbs {

	const VERSION      = '1.0.0';
	const CPT          = 'kleurplaten';
	const TAX          = 'kleurplaat_categorie';
	const META_KEY     = 'thumbnail_id';
	const PROTECT_KEY  = '_kk_thumb_protected'; // underscore-prefixed = private meta
	const CRON_HOOK    = 'kk_self_heal_cat_thumbs_sync';
	const BATCH_SIZE   = 200;
	const SCHEDULE     = 'twicedaily';

	/** In-request dedupe so a bulk import doesn't reseed the same term 100x. */
	private static $ensured = array();

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// 1. Programmatic term changes (REST, WP-CLI, custom scripts).
		add_action( 'set_object_terms', array( $this, 'on_set_object_terms' ), 10, 6 );

		// 2. Kleurplaat saved in the admin (incl. quick/bulk edit, REST).
		add_action( 'save_post_' . self::CPT, array( $this, 'on_save_post' ), 20, 3 );

		// 3. Attachment deleted → re-validate (the orphan that just disappeared).
		add_action( 'delete_attachment', array( $this, 'on_delete_attachment' ) );

		// 4. Make sure the cron is scheduled.
		add_action( 'init', array( $this, 'schedule_cron' ) );
		add_action( self::CRON_HOOK, array( $this, 'full_resync' ) );

		// 5. Defensive: if a sweeper deleted meta whose term_id looks orphan,
		//    re-protect on a late hook so our data survives.
		add_action( 'deleted_term_meta', array( $this, 'on_meta_deleted' ), 10, 4 );

		// 6. WP-CLI command for manual triggers and ops.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'kk cat-thumbs', array( $this, 'cli_command' ) );
		}
	}

	/* =====================================================================
	 * Hook callbacks
	 * ===================================================================== */

	/**
	 * Fires whenever a post↔term relationship changes for any taxonomy.
	 * Only act when it concerns kleurplaat_categorie.
	 *
	 * @param int    $object_id  Post ID.
	 * @param array  $terms      Term slugs/IDs/objects (unused, we work from $tt_ids).
	 * @param array  $tt_ids     New term_taxonomy_ids.
	 * @param string $taxonomy   Taxonomy slug.
	 * @param bool   $append     Whether terms were appended.
	 * @param array  $old_tt_ids Previous term_taxonomy_ids.
	 */
	public function on_set_object_terms( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		if ( $taxonomy !== self::TAX ) {
			return;
		}

		// Combine new + old tt_ids so we reseed terms that lost their last kleurplaat.
		$all_tt_ids = array_unique(
			array_merge( array_map( 'intval', (array) $tt_ids ), array_map( 'intval', (array) $old_tt_ids ) )
		);
		if ( empty( $all_tt_ids ) ) {
			return;
		}

		$term_ids = $this->tt_ids_to_term_ids( $all_tt_ids );
		foreach ( $term_ids as $term_id ) {
			$this->ensure_term_thumbnail( (int) $term_id );
		}
	}

	/**
	 * Fires when a kleurplaat is saved.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated.
	 */
	public function on_save_post( $post_id, $post, $update ) {
		// Skip autosaves, revisions, and non-published posts.
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( empty( $post ) || $post->post_status !== 'publish' ) {
			return;
		}

		foreach ( $this->get_post_term_ids( (int) $post_id ) as $term_id ) {
			$this->ensure_term_thumbnail( (int) $term_id );
		}
	}

	/**
	 * When the featured image itself is deleted, every term that pointed
	 * to it now has an orphan. Re-seed them in batch.
	 */
	public function on_delete_attachment( $attachment_id ) {
		global $wpdb;

		$term_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT term_id FROM {$wpdb->termmeta}
				 WHERE meta_key = %s AND CAST(meta_value AS UNSIGNED) = %d",
				self::META_KEY,
				(int) $attachment_id
			)
		);

		if ( empty( $term_ids ) ) {
			return;
		}

		foreach ( $term_ids as $term_id ) {
			$this->ensure_term_thumbnail( (int) $term_id );
		}
	}

	/**
	 * If a sweeper just removed a protected meta row, immediately re-seed
	 * (only when the term is still around and the post still has a thumb).
	 */
	public function on_meta_deleted( $meta_ids, $object_id, $meta_key, $meta_value ) {
		if ( $meta_key !== self::META_KEY ) {
			return;
		}
		if ( ! $this->term_exists( (int) $object_id ) ) {
			return; // Don't resurrect meta for a deleted term.
		}
		$this->ensure_term_thumbnail( (int) $object_id );
	}

	/* =====================================================================
	 * Scheduling
	 * ===================================================================== */

	public function schedule_cron() {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 60, self::SCHEDULE, self::CRON_HOOK );
		}
	}

	/* =====================================================================
	 * Core: ensure_term_thumbnail
	 * ===================================================================== */

	/**
	 * Idempotent. Verifies the term's `thumbnail_id` and reseeds it from
	 * the latest published kleurplaat in the term if missing or orphaned.
	 *
	 * @param int $term_id
	 * @return bool True when the term now has a valid thumbnail_id.
	 */
	public function ensure_term_thumbnail( $term_id ) {
		$term_id = (int) $term_id;
		if ( $term_id <= 0 ) {
			return false;
		}
		if ( isset( self::$ensured[ $term_id ] ) ) {
			return self::$ensured[ $term_id ];
		}

		global $wpdb;

		// 1. Term must still exist.
		if ( ! $this->term_exists( $term_id ) ) {
			return self::$ensured[ $term_id ] = false;
		}

		// 2. Read current thumbnail_id directly from termmeta.
		$current = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->termmeta}
				 WHERE term_id = %d AND meta_key = %s
				 LIMIT 1",
				$term_id,
				self::META_KEY
			)
		);

		// 3. Validate: must be a non-empty, non-zero integer pointing to a real attachment.
		$needs_reseed = empty( $current )
			|| ! ctype_digit( (string) $current )
			|| ! $this->attachment_is_valid( (int) $current );

		if ( ! $needs_reseed ) {
			// Healthy: just re-arm the protection marker and bail.
			$this->mark_protected( $term_id );
			return self::$ensured[ $term_id ] = true;
		}

		// 4. Reseed: find the latest published kleurplaat in this term
		//    that has a valid _thumbnail_id, and write the termmeta row.
		$thumb_id = $this->find_latest_thumbnail_for_term( $term_id );
		if ( ! $thumb_id ) {
			return self::$ensured[ $term_id ] = false;
		}

		$written = $this->upsert_term_meta( $term_id, self::META_KEY, (string) $thumb_id );
		if ( $written ) {
			$this->mark_protected( $term_id );
			// Bust the theme's homepage transient so the new thumb shows immediately.
			if ( function_exists( 'kk_invalidate_category_cache' ) ) {
				kk_invalidate_category_cache();
			}
		}

		return self::$ensured[ $term_id ] = $written;
	}

	/* =====================================================================
	 * Helpers (all $wpdb, no WP_Query)
	 * ===================================================================== */

	/**
	 * Latest published kleurplaat in the term that has a valid _thumbnail_id.
	 *
	 * @return int Attachment ID, or 0 if none.
	 */
	private function find_latest_thumbnail_for_term( $term_id ) {
		global $wpdb;

		// Single query: latest post in the term whose _thumbnail_id resolves to a real attachment.
		$thumb_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT CAST(pm.meta_value AS UNSIGNED)
				 FROM {$wpdb->term_relationships} tr
				 INNER JOIN {$wpdb->term_taxonomy} tt
				     ON tr.term_taxonomy_id = tt.term_taxonomy_id
				 INNER JOIN {$wpdb->posts} p
				     ON p.ID = tr.object_id
				 INNER JOIN {$wpdb->postmeta} pm
				     ON pm.post_id = p.ID AND pm.meta_key = '_thumbnail_id'
				 INNER JOIN {$wpdb->posts} a
				     ON a.ID = CAST(pm.meta_value AS UNSIGNED)
				     AND a.post_type = 'attachment'
				     AND a.post_status != 'trash'
				 WHERE tt.term_id = %d
				   AND p.post_type = %s
				   AND p.post_status = 'publish'
				   AND pm.meta_value != ''
				   AND pm.meta_value != '0'
				 ORDER BY p.post_date DESC
				 LIMIT 1",
				$term_id,
				self::CPT
			)
		);

		return $thumb_id > 0 ? $thumb_id : 0;
	}

	/**
	 * Is the attachment a real, non-trashed post?
	 */
	private function attachment_is_valid( $attachment_id ) {
		$attachment_id = (int) $attachment_id;
		if ( $attachment_id <= 0 ) {
			return false;
		}
		global $wpdb;
		$status = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_status FROM {$wpdb->posts}
				 WHERE ID = %d AND post_type = 'attachment'
				 LIMIT 1",
				$attachment_id
			)
		);
		return ! empty( $status ) && $status !== 'trash';
	}

	/**
	 * Term exists in wp_terms?
	 */
	private function term_exists( $term_id ) {
		$term_id = (int) $term_id;
		if ( $term_id <= 0 ) {
			return false;
		}
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT term_id FROM {$wpdb->terms} WHERE term_id = %d LIMIT 1",
				$term_id
			)
		) === $term_id;
	}

	/**
	 * Get term_ids for a single kleurplaat (direct SQL).
	 *
	 * @return int[]
	 */
	private function get_post_term_ids( $post_id ) {
		global $wpdb;
		$tt_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT tr.term_taxonomy_id FROM {$wpdb->term_relationships} tr
				 INNER JOIN {$wpdb->term_taxonomy} tt
				     ON tr.term_taxonomy_id = tt.term_taxonomy_id
				 WHERE tr.object_id = %d AND tt.taxonomy = %s",
				(int) $post_id,
				self::TAX
			)
		);
		if ( empty( $tt_ids ) ) {
			return array();
		}
		return $this->tt_ids_to_term_ids( $tt_ids );
	}

	/**
	 * Map term_taxonomy_ids → term_ids.
	 *
	 * @param int[] $tt_ids
	 * @return int[]
	 */
	private function tt_ids_to_term_ids( $tt_ids ) {
		$tt_ids = array_values( array_unique( array_map( 'intval', (array) $tt_ids ) ) );
		if ( empty( $tt_ids ) ) {
			return array();
		}
		global $wpdb;
		$placeholders = implode( ',', array_fill( 0, count( $tt_ids ), '%d' ) );
		$term_ids     = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT term_id FROM {$wpdb->term_taxonomy}
				 WHERE term_taxonomy_id IN ($placeholders)",
				$tt_ids
			)
		);
		return array_map( 'intval', (array) $term_ids );
	}

	/**
	 * Upsert a single (term_id, meta_key) row in wp_termmeta.
	 */
	private function upsert_term_meta( $term_id, $meta_key, $meta_value ) {
		global $wpdb;
		$existing = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->termmeta}
				 WHERE term_id = %d AND meta_key = %s LIMIT 1",
				$term_id,
				$meta_key
			)
		);
		if ( $existing ) {
			$ok = $wpdb->update(
				$wpdb->termmeta,
				array( 'meta_value' => (string) $meta_value ),
				array( 'meta_id' => $existing ),
				array( '%s' ),
				array( '%d' )
			);
		} else {
			$ok = $wpdb->insert(
				$wpdb->termmeta,
				array(
					'term_id'    => $term_id,
					'meta_key'   => $meta_key,
					'meta_value' => (string) $meta_value,
				),
				array( '%d', '%s', '%s' )
			);
		}
		return false !== $ok && 0 !== $ok;
	}

	/* =====================================================================
	 * Anti-cleanup protection
	 * ===================================================================== */

	/**
	 * Drop a hidden "self-heal marker" in termmeta every time we touch the
	 * thumbnail_id row. This meta row:
	 *  - references a real term_id and a numeric meta_value (timestamp),
	 *  - uses an underscore-prefixed key (private; hidden in custom-field UIs),
	 *  - looks legitimate to most sweepers, so the adjacent thumbnail_id row
	 *    is unlikely to be deleted in isolation.
	 *
	 * If a sweeper does delete the thumbnail_id, the deleted_term_meta hook
	 * above reseeds it.
	 */
	private function mark_protected( $term_id ) {
		global $wpdb;
		$existing = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->termmeta}
				 WHERE term_id = %d AND meta_key = %s LIMIT 1",
				$term_id,
				self::PROTECT_KEY
			)
		);
		if ( $existing ) {
			$wpdb->update(
				$wpdb->termmeta,
				array( 'meta_value' => (string) time() ),
				array( 'meta_id' => $existing ),
				array( '%s' ),
				array( '%d' )
			);
		} else {
			$wpdb->insert(
				$wpdb->termmeta,
				array(
					'term_id'    => $term_id,
					'meta_key'   => self::PROTECT_KEY,
					'meta_value' => (string) time(),
				),
				array( '%d', '%s', '%s' )
			);
		}
	}

	/* =====================================================================
	 * Full re-sync (cron + CLI)
	 * ===================================================================== */

	/**
	 * Walks every kleurplaat_categorie term in paginated batches and
	 * self-heals each one. Safe to run from cron or WP-CLI.
	 *
	 * @return array{scanned:int,reseeded:int,unchanged:int}
	 */
	public function full_resync() {
		global $wpdb;
		$stats = array(
			'scanned'   => 0,
			'reseeded'  => 0,
			'unchanged' => 0,
		);

		$offset = 0;
		do {
			$term_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT term_id FROM {$wpdb->term_taxonomy}
					 WHERE taxonomy = %s
					 ORDER BY term_id ASC
					 LIMIT %d OFFSET %d",
					self::TAX,
					self::BATCH_SIZE,
					$offset
				)
			);
			if ( empty( $term_ids ) ) {
				break;
			}

			foreach ( $term_ids as $term_id ) {
				$stats['scanned']++;
				$had         = $this->term_has_valid_thumb( (int) $term_id );
				$ok          = $this->ensure_term_thumbnail( (int) $term_id );
				if ( $ok ) {
					if ( $had ) {
						$stats['unchanged']++;
					} else {
						$stats['reseeded']++;
					}
				}
			}

			$offset += self::BATCH_SIZE;
		} while ( count( $term_ids ) === self::BATCH_SIZE );

		// Bust the homepage transient once after the sweep.
		if ( $stats['reseeded'] > 0 && function_exists( 'kk_invalidate_category_cache' ) ) {
			kk_invalidate_category_cache();
		}

		return $stats;
	}

	/**
	 * @return bool True when the term already has a valid thumbnail_id.
	 */
	private function term_has_valid_thumb( $term_id ) {
		global $wpdb;
		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->termmeta}
				 WHERE term_id = %d AND meta_key = %s LIMIT 1",
				$term_id,
				self::META_KEY
			)
		);
		return ! empty( $value ) && ctype_digit( (string) $value ) && $this->attachment_is_valid( (int) $value );
	}

	/* =====================================================================
	 * WP-CLI
	 * ===================================================================== */

	/**
	 * ## EXAMPLES
	 *
	 *     wp kk cat-thumbs resync
	 *     wp kk cat-thumbs status
	 *     wp kk cat-thumbs fix --term=42
	 *
	 * @when after_wp_load
	 */
	public function cli_command( $args, $assoc_args ) {
		$action = isset( $args[0] ) ? $args[0] : 'status';

		if ( 'resync' === $action ) {
			$stats = $this->full_resync();
			WP_CLI::success(
				sprintf(
					'Re-synced kleurplaat_categorie: scanned=%d, reseeded=%d, unchanged=%d',
					$stats['scanned'],
					$stats['reseeded'],
					$stats['unchanged']
				)
			);
			return;
		}

		if ( 'fix' === $action ) {
			$term_id = isset( $assoc_args['term'] ) ? (int) $assoc_args['term'] : 0;
			if ( $term_id <= 0 ) {
				WP_CLI::error( 'Please pass --term=<term_id>.' );
			}
			$ok = $this->ensure_term_thumbnail( $term_id );
			if ( $ok ) {
				WP_CLI::success( "Term {$term_id}: thumbnail_id is healthy." );
			} else {
				WP_CLI::warning( "Term {$term_id}: no kleurplaat with a featured image found." );
			}
			return;
		}

		if ( 'status' === $action ) {
			global $wpdb;
			$total    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s", self::TAX ) );
			$with     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->termmeta} tm INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = tm.term_id WHERE tm.meta_key = %s AND tt.taxonomy = %s", self::META_KEY, self::TAX ) );
			$next_run = wp_next_scheduled( self::CRON_HOOK );
			WP_CLI::log( sprintf( 'Taxonomy:         %s', self::TAX ) );
			WP_CLI::log( sprintf( 'Total terms:      %d', $total ) );
			WP_CLI::log( sprintf( 'With thumbnail_id:%d', $with ) );
			WP_CLI::log( sprintf( 'Missing:          %d', max( 0, $total - $with ) ) );
			WP_CLI::log( sprintf( 'Cron next run:    %s', $next_run ? gmdate( 'c', $next_run ) : 'not scheduled' ) );
			WP_CLI::log( sprintf( 'Plugin version:   %s', self::VERSION ) );
			return;
		}

		WP_CLI::error( 'Unknown subcommand. Use: resync | fix --term=<id> | status' );
	}
}

KK_Self_Healing_Cat_Thumbs::instance();
