<?php
/*
 * ajax.php
 * Processes AJAX requests.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Delete Link for Moderators and Authors
 */
function gwolle_gb_entry_metabox_lines_delete_link_v2( $gb_metabox, $entry ) {
	$metabox = '';
	if ( current_user_can('gwolle_gb_moderate_comments') ) {
		if (get_option( 'gwolle_gb_addon-delete_link', 'false') === 'true') {

			$metabox = '
					<div class="gb-metabox-line gb-metabox-line-delete">
						<a class="gwolle_gb_delete_link" href="#" data-entry-id="' . (int) $entry->get_id() . '" title="' . esc_attr__('Delete entry', 'gwolle-gb') . '">' . esc_html__('Delete', 'gwolle-gb') . '</a>
					</div>';
		}
	}
	if ( gwolle_gb_is_author( $entry ) ) {
		if (get_option( 'gwolle_gb_addon-delete_link_author', 'false') === 'true') {

			$metabox = '
					<div class="gb-metabox-line gb-metabox-line-delete">
						<a class="gwolle_gb_delete_link" href="#" data-entry-id="' . (int) $entry->get_id() . '" title="' . esc_attr__('Delete entry', 'gwolle-gb') . '">' . esc_html__('Delete', 'gwolle-gb') . '</a>
					</div>';
		}
	}
	$gb_metabox .= $metabox;
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_metabox_lines', 'gwolle_gb_entry_metabox_lines_delete_link_v2', 94, 2 );


/*
 * Callback function for handling the Ajax request that is generated from the JavaScript above in gwolle_gb_delete_javascript
 */
add_action( 'wp_ajax_gwolle_gb_delete', 'gwolle_gb_delete_callback_v2' );
function gwolle_gb_delete_callback_v2() {
	if ( ! is_user_logged_in() ) {
		echo 'not logged in, no permission.';
		die();
	}
	if ( ( get_option( 'gwolle_gb_addon-delete_link', 'false') === 'false' ) || get_option( 'gwolle_gb_addon-delete_link_author', 'false' ) === 'false' ) {
		echo 'error, delete function is disabled.';
		die();
	}


	/* Check Nonce */
	$verified = false;
	if ( isset($_POST['nonce']) ) {
		$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'gwolle_gb_addon_frontend_list_nonce' );
	}
	if ( $verified == false ) {
		// Nonce is invalid.
		echo 'error, nonce check failed.';
		die();
	}

	if (isset($_POST['id'])) {
		$id = (int) $_POST['id'];
	}
	if (isset($_POST['setter'])) {
		$setter = (string) $_POST['setter'];
	}


	if ( isset($id) && $id > 0 && isset($setter) && strlen($setter) > 0) {
		$entry = new gwolle_gb_entry();
		$result = $entry->load( $id );
		if ( ! $result ) {
			echo 'error, no such entry.';
			die();
		}

		if ( ( ! current_user_can('gwolle_gb_moderate_comments') ) && ( ! gwolle_gb_is_author( $entry ) ) ) {
			echo 'error, no permission.';
			die();
		}

		if ( $setter === 'trash' ) {
			if ( $entry->get_istrash() == 0 ) {
				$entry->set_istrash( true );
				$result = $entry->save();
				if ($result ) {
					$response = 'trash';
					gwolle_gb_add_log_entry( $entry->get_id(), 'entry-trashed' );
				} else {
					echo 'error, allready in trash';
					die();
				}
			} else {
				echo 'nochange';
				die();
			}
		}

		gwolle_gb_clear_cache( $entry );

	} else {
		$response = 'error, not the right data';
	}

	echo $response;
	die(); // this is required to return a proper result
}
