<?php
/**
 * Plugin Name: Kinderkleurplaten category thumbnail ensure [DEPRECATED]
 * Description: This plugin is superseded by the mu-plugin at
 *              wp-content/mu-plugins/kk-self-healing-category-thumbs.php
 *              (which hooks the correct CPT slug 'kleurplaten' and uses
 *              direct $wpdb queries). It is kept here only as a no-op
 *              stub so existing installations do not error if the file
 *              is still scanned. Please deactivate and remove this folder.
 * Version:     1.0.1-deprecated
 * Author:      Kinderkleurplaten
 * License:     GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Intentionally empty. The self-healing logic now lives in:
 *   wp-content/mu-plugins/kk-self-healing-category-thumbs.php
 *
 * Notes from the previous (buggy) version for historical context:
 *  - It hooked 'save_post_kleurplaat' (singular) but the real CPT is
 *    'kleurplaten' (plural), so the hook never fired.
 *  - It used get_posts() / WP_Query for a one-row lookup, which was
 *    unnecessarily heavy.
 *  - It had no 'set_object_terms' listener, so programmatic category
 *    changes bypassed the logic.
 *  - It had no defence against orphan-meta cleanup plugins.
 */
function kk_ensure_category_thumbnail_exists_deprecated_noop() {
	// No-op.
}
