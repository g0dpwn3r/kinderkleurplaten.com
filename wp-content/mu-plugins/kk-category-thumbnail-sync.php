<?php
/**
 * Plugin Name: KK Category Thumbnail Sync
 * Description: REST endpoint om termmeta thumbnail_id voor kleurplaat_categorie termen
 *              bij te werken op basis van de meest recente kleurplaat-attachment.
 *              Gebruikt door het Python-script update_category_thumbnails.py.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Author:      kinderkleurplaten
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * REST endpoint: update category thumbnails.
 *
 * POST /wp-json/kk/v1/update-category-thumbnails
 *
 * Body (JSON, optioneel):
 *   dry_run  bool  Als true: alleen rapporteren, geen writes. Default: false.
 *
 * Response (JSON):
 *   {
 *     "status":       "ok" | "error",
 *     "dry_run":      bool,
 *     "total_terms":  int,
 *     "results":      [
 *       {
 *         "term_id":         int,
 *         "name":            string,
 *         "attachment_id":   int|null,
 *         "previous_value":  string|null,
 *         "action":          "updated" | "unchanged" | "no_attachment" | "invalid_attachment" | "skipped"
 *       },
 *       ...
 *     ],
 *     "summary": {
 *       "total":                int,
 *       "updated":              int,
 *       "unchanged":            int,
 *       "no_attachment":        int,
 *       "invalid_attachment":   int
 *     }
 *   }
 */
add_action( 'rest_api_init', function() {

    register_rest_route( 'kk/v1', '/update-category-thumbnails', array(
        'methods'             => WP_REST_Server::CREATABLE,  // POST
        'callback'            => 'kk_update_category_thumbnails_handler',
        'permission_callback' => function() {
            return current_user_can( 'manage_options' );
        },
        'args'                => array(
            'dry_run' => array(
                'type'              => 'boolean',
                'default'           => false,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ),
        ),
    ) );
} );

/**
 * Handler voor de REST endpoint.
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response|WP_Error
 */
function kk_update_category_thumbnails_handler( WP_REST_Request $request ) {

    global $wpdb;

    $dry_run = (bool) $request->get_param( 'dry_run' );
    $prefix  = $wpdb->prefix;

    // ── Stap 1: Alle termen ophalen ──────────────────────────────────
    // We hebben term_id, name, slug, count, én term_taxonomy_id nodig.
    $terms_sql = $wpdb->prepare(
        "SELECT
            t.term_id   AS term_id,
            t.name      AS name,
            t.slug      AS slug,
            tt.count    AS post_count,
            tt.term_taxonomy_id AS term_taxonomy_id
        FROM {$prefix}terms t
        INNER JOIN {$prefix}term_taxonomy tt ON tt.term_id = t.term_id
        WHERE tt.taxonomy = %s
        ORDER BY tt.count DESC",
        'kleurplaat_categorie'
    );

    $terms = $wpdb->get_results( $terms_sql, ARRAY_A );

    if ( empty( $terms ) ) {
        return new WP_REST_Response( array(
            'status'      => 'ok',
            'dry_run'     => $dry_run,
            'total_terms' => 0,
            'results'     => array(),
            'summary'     => array(
                'total' => 0, 'updated' => 0, 'unchanged' => 0,
                'no_attachment' => 0, 'invalid_attachment' => 0,
            ),
            'message'     => 'Geen termen gevonden in kleurplaat_categorie.',
        ), 200 );
    }

    $results = array();
    $summary = array(
        'total'              => count( $terms ),
        'updated'            => 0,
        'unchanged'          => 0,
        'no_attachment'      => 0,
        'invalid_attachment' => 0,
    );

    // ── Stap 2: Per term de meest recente attachment zoeken ────────────
    // De query zoekt via term_relationships de kleurplaten in deze categorie,
    // haalt _thumbnail_id uit postmeta (dat is het attachment post_id),
    // en sorteert op post_date DESC (meest recente eerst).
    foreach ( $terms as $term ) {
        $term_id      = (int) $term['term_id'];
        $term_name    = $term['name'];
        $tt_id        = (int) $term['term_taxonomy_id'];

        $entry = array(
            'term_id'        => $term_id,
            'name'           => $term_name,
            'attachment_id'  => null,
            'previous_value' => null,
            'action'         => 'skipped',
        );

        // Zoek de meest recente attachment voor deze term.
        $attach_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    CAST(pm.meta_value AS UNSIGNED) AS attachment_id
                FROM {$prefix}term_relationships tr
                INNER JOIN {$prefix}posts p
                    ON p.ID = tr.object_id
                INNER JOIN {$prefix}postmeta pm
                    ON pm.post_id = p.ID
                    AND pm.meta_key = %s
                WHERE tr.term_taxonomy_id = %d
                  AND p.post_type = 'kleurplaten'
                  AND p.post_status = 'publish'
                  AND pm.meta_value != ''
                  AND pm.meta_value != '0'
                ORDER BY p.post_date DESC
                LIMIT 1",
                '_thumbnail_id',
                $tt_id
            ),
            ARRAY_A
        );

        if ( empty( $attach_row ) || empty( $attach_row['attachment_id'] ) ) {
            $entry['action'] = 'no_attachment';
            $summary['no_attachment']++;
            $results[] = $entry;
            continue;
        }

        $attachment_id = (int) $attach_row['attachment_id'];
        $entry['attachment_id'] = $attachment_id;

        // Verifieer dat het attachment daadwerkelijk bestaat.
        $attachment_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$prefix}posts
                 WHERE ID = %d AND post_type = 'attachment'
                 LIMIT 1",
                $attachment_id
            )
        );

        if ( ! $attachment_exists ) {
            $entry['action'] = 'invalid_attachment';
            $summary['invalid_attachment']++;
            $results[] = $entry;
            continue;
        }

        // Lees de huidige termmeta-waarde.
        $current_value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM {$prefix}termmeta
                 WHERE term_id = %d AND meta_key = %s
                 LIMIT 1",
                $term_id,
                'thumbnail_id'
            )
        );

        $entry['previous_value'] = $current_value;

        // Vergelijk en update indien nodig.
        if ( $current_value === (string) $attachment_id ) {
            $entry['action'] = 'unchanged';
            $summary['unchanged']++;
            $results[] = $entry;
            continue;
        }

        // Schrijf termmeta (tenzij dry_run).
        if ( ! $dry_run ) {
            $existing = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$prefix}termmeta
                     WHERE term_id = %d AND meta_key = %s",
                    $term_id,
                    'thumbnail_id'
                )
            );

            if ( $existing ) {
                $wpdb->update(
                    "{$prefix}termmeta",
                    array( 'meta_value' => (string) $attachment_id ),
                    array( 'term_id' => $term_id, 'meta_key' => 'thumbnail_id' ),
                    array( '%s' ),
                    array( '%d', '%s' )
                );
            } else {
                $wpdb->insert(
                    "{$prefix}termmeta",
                    array(
                        'term_id'    => $term_id,
                        'meta_key'   => 'thumbnail_id',
                        'meta_value' => (string) $attachment_id,
                    ),
                    array( '%d', '%s', '%s' )
                );
            }
        }

        $entry['action'] = $dry_run ? 'would_update' : 'updated';
        $summary['updated']++;
        $results[] = $entry;
    }

    // ── Stap 3: Cache wissen na update ─────────────────────────────────
    if ( ! $dry_run && $summary['updated'] > 0 && function_exists( 'kk_invalidate_category_cache' ) ) {
        kk_invalidate_category_cache();
    }

    return new WP_REST_Response( array(
        'status'      => 'ok',
        'dry_run'     => $dry_run,
        'total_terms' => count( $terms ),
        'results'     => $results,
        'summary'     => $summary,
    ), 200 );
}
