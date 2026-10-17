<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Add JavaScript to the admin Footer so we can do Ajax.
 *
 * @since 1.2.0
 */
add_action( 'admin_footer', 'gwolle_gb_report_javascript_v2' );
function gwolle_gb_report_javascript_v2() {
	if ( ! current_user_can('gwolle_gb_moderate_comments') ) {
		return;
	}

	$report_nonce = wp_create_nonce( 'gwolle_gb_admin_report' ); ?>

	<script>
	jQuery( document ).ready( function( $ ) {

		jQuery( 'span.gwolle-gb-report-abuse-positive a' ).on( 'click', function(event) {

			var entry_id = jQuery(this).attr('data-entry-id');
			var data = {
				action: 'gwolle_gb_admin_report',
				security: '<?php echo esc_attr( $report_nonce ); ?>',
				id: entry_id,
				setter: 'check_moderate'
			};

			// Set Ajax icon on visible
			jQuery( '.gwolle_gb_ajax' ).css('display', 'inline-block');

			// Do the actual request
			jQuery.post( ajaxurl, data, function( response ) {
				response = response.trim();

				// Set classes accordingly
				if ( response == 'check_moderate' ) { // We got what we wanted

					// Countdown counter in admin menu, toolbar
					if ( jQuery( '.gwolle_gb_actions' ).hasClass('unchecked') && jQuery( '.gwolle_gb_actions' ).hasClass('nospam') && jQuery( '.gwolle_gb_actions' ).hasClass('notrash') ) {
						var gwolle_gb_menu_counter = jQuery('li#toplevel_page_gwolle-gb-gwolle-gb a.menu-top span.awaiting-mod span').text();
						var old_gwolle_gb_menu_counter = new Number( gwolle_gb_menu_counter );
						var new_gwolle_gb_menu_counter = old_gwolle_gb_menu_counter - 1;

						jQuery('li#toplevel_page_gwolle-gb-gwolle-gb span.awaiting-mod span').text( new_gwolle_gb_menu_counter );
						jQuery('li#wp-admin-bar-gwolle-gb span.awaiting-mod.pending-count').text( new_gwolle_gb_menu_counter );
					}

					jQuery( '.entry-icons' ).addClass('checked').removeClass('unchecked');
					jQuery( '.gwolle_gb_actions' ).addClass('checked').removeClass('unchecked');
					jQuery( 'input#ischecked' ).prop('checked', true);

					// Set to visible if needed.
					if ( jQuery( '.gwolle_gb_actions' ).hasClass('checked') && jQuery( '.gwolle_gb_actions' ).hasClass('nospam') && jQuery( '.gwolle_gb_actions' ).hasClass('notrash') ) {
						jQuery( '.entry-icons' ).addClass('visible').removeClass('invisible');
						jQuery( '.gwolle_gb_actions' ).addClass('visible').removeClass('invisible');
						jQuery( '.h3_invisible' ).css('display', 'none');
						jQuery( '.h3_visible' ).css('display', 'block');
					}

					// Change reports display.
					jQuery( 'span.gwolle-gb-report-abuse-positive' ).addClass('gwolle_gb_hide');
					jQuery( 'span.gwolle-gb-report-abuse-negative' ).removeClass('gwolle_gb_hide');

				} else {
					// Error or unexpected answer...
					jQuery( '.gwolle-gb-report-abuse-positive' ).append(' (error)');
					event.preventDefault();
					return;
				}

				// Hide Ajax icon again
				jQuery( '.gwolle_gb_ajax' ).css('display', 'none');
			});

			event.preventDefault();
		});

	});
	</script>
	<?php
}


/*
 * Callback function for handling the Ajax requests that are generated from the JavaScript above in gwolle_gb_ajax_javascript
 */
add_action( 'wp_ajax_gwolle_gb_admin_report', 'gwolle_gb_admin_report_callback_v2' );
function gwolle_gb_admin_report_callback_v2() {

	if ( ! current_user_can('gwolle_gb_moderate_comments') ) {
		echo 'error';
		die();
	}

	/* Check Nonce */
	$verified = false;
	if ( isset($_POST['security']) ) {
		$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'gwolle_gb_admin_report' );
	}
	if ( $verified === false ) {
		// Nonce is invalid.
		echo 'The Nonce did not validate. Please reload the page and try again.';
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

		if ( $entry->get_ischecked() == 0 ) {
			$entry->set_ischecked( true );
			$user_id = get_current_user_id(); // returns 0 if no current user
			$entry->set_checkedby( $user_id );
			$result = $entry->save();
			if ( $result ) {
				gwolle_gb_add_log_entry( $entry->get_id(), 'entry-checked' );
			} else {
				$response = 'error when saving';
			}
		}
		$result2 = gwolle_gb_addon_save_meta_v2( $id, 'report-abuse', '-1' );
		if ( $result && $result2 ) {
			$response = 'check_moderate';
			do_action( 'gwolle_gb_save_entry_admin', $entry );
		} else {
			if ( function_exists( 'gwolle_gb_array_flatten' ) ) {
				$result2 = gwolle_gb_array_flatten( $result2 );
				$result2 = implode( ', ', $result2 );
				$response = 'error happened during saving with result: ' . $result2;
			}
		}

	} else {
		$response = 'error, not the right data';
	}

	echo $response;
	die(); // this is required to return a proper result
}
