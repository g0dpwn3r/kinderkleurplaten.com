<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Report Abuse for single entry.
 *
 * @since 1.2.0
 */
function gwolle_gb_entry_metabox_lines_report_abuse_v2( $gb_metabox, $entry ) {

	if (get_option( 'gwolle_gb_addon-report', 'false') === 'true') {

		if ( function_exists( 'gwolle_gb_check_ip_on_blocklist' ) && gwolle_gb_check_ip_on_blocklist() ) {
			return $gb_metabox;
		}

		$reports = (int) gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'report-abuse' );
		if ( $reports == -1 ) {
			// Already moderated.
			return $gb_metabox;
		}

		if ( gwolle_gb_addon_report_already_flagged_v2( $entry->get_id() ) ) {
			// Already reported.
			return $gb_metabox;
		}

		$gb_metabox .= '
					<div class="gb-metabox-line gb-metabox-line-report-abuse">
						<a class="gwolle-gb-report-abuse" href="#" data-entry-id="' . (int) $entry->get_id() . '" title="' . esc_attr__('Report Abuse for this entry', 'gwolle-gb') . '">' . esc_html__('Report Abuse', 'gwolle-gb') . '</a>
					</div>';
	}

	return $gb_metabox;

}
add_filter( 'gwolle_gb_entry_metabox_lines', 'gwolle_gb_entry_metabox_lines_report_abuse_v2', 80, 2 );


/*
 * Callback function for handling the Ajax request that is generated from the JavaScript above in gwolle_gb_report_abuse_javascript.
 * Also for non-logged-in users.
 */
add_action( 'wp_ajax_gwolle_gb_report', 'gwolle_gb_report_abuse_callback_v2' );
add_action( 'wp_ajax_nopriv_gwolle_gb_report', 'gwolle_gb_report_abuse_callback_v2' );
function gwolle_gb_report_abuse_callback_v2() {

	if (get_option( 'gwolle_gb_addon-report', 'false') === 'false') {
		echo 'error, report function is disabled.';
		die();
	}

	if ( function_exists( 'gwolle_gb_check_ip_on_blocklist' ) && gwolle_gb_check_ip_on_blocklist() ) {
		echo 'error, report function is disabled.';
		die();
	}

	/* Check Nonce */
	$verified = false;
	if ( isset($_POST['nonce']) ) {
		$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'gwolle_gb_addon_frontend_list_nonce' );
	}
	if ( $verified == false ) {
		echo 'error, nonce check failed.';
		die();
	}

	if (isset($_POST['entry_id'])) {
		$entry_id = (int) $_POST['entry_id'];
	}

	if ( isset($entry_id) && $entry_id > 0 ) {
		$entry = new gwolle_gb_entry();
		$result = $entry->load( $entry_id );
		if ( ! $result ) {
			echo 'error, no such entry.';
			die();
		}

		$reports = (int) gwolle_gb_addon_get_meta_v2( $entry_id, 'report-abuse' );
		if ( $reports == -1 ) {
			echo 'error, this entry was already moderated.';
			die();
		}

		if ( gwolle_gb_addon_report_already_flagged_v2( $entry_id ) ) {
			echo 'error, you already reported this entry.';
			die();
		}

		$reports = ( $reports + 1 );
		gwolle_gb_addon_save_meta_v2( $entry_id, 'report-abuse', $reports );
		if ( $reports > 2 ) {
			if ( $entry->get_ischecked() == 1 ) {
				$entry->set_ischecked( false );
				$result = $entry->save();
				if ( $result ) {
					gwolle_gb_add_log_entry( $entry_id, 'entry-unchecked' );
				}
			}
		}
		gwolle_gb_addon_report_set_cookie_v2( $entry_id );
		gwolle_gb_addon_mail_moderators_report_abuse_v2( $entry, $reports );
		echo 'reported';
		die();
	}

	echo 'error, not the right data';
	die();

}
