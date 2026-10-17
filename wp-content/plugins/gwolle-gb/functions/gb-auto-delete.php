<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Auto Deletes entries that are older than a certain date.
 *
 * @since 1.1.1
 */
function gwolle_gb_addon_auto_delete_v2() {

	if ( get_option( 'gwolle_gb_addon-auto_delete', 'false') === 'true' ) {
		$time = (int) get_option( 'gwolle_gb_addon-auto_delete_time', 5 );
		if ( in_array( $time, array( 1, 2, 3, 4, 5 ) ) ) {
			$current_time = current_time( 'timestamp' );
			switch ($time) {
				case 1:
					// 1 Day
					$timestamp = ( $current_time - ( 1 * 24 * 60 * 60 ) );
					break;

				case 2:
					// 2 Days
					$timestamp = ( $current_time - ( 2 * 24 * 60 * 60 ) );
					break;

				case 3:
					// 1 Week
					$timestamp = ( $current_time - ( 7 * 24 * 60 * 60 ) );
					// 7 days; 24 hours; 60 mins; 60 secs
					break;

				case 4:
					// 2 Weeks
					$timestamp = ( $current_time - ( 14 * 24 * 60 * 60 ) );
					break;

				case 5:
					// 1 Month
					$timestamp = strtotime( '-1 month', $current_time );
					break;

				case 6:
					// 6 Months
					$timestamp = strtotime( '-6 month', $current_time );
					break;

				case 7:
					// 12 Months
					$timestamp = strtotime( '-12 month', $current_time );
					break;

			}

			$entries = gwolle_gb_get_entries(array(
				'num_entries' => 20,
				'trash'       => 'notrash',
				'spam'        => 'nospam',
				'date_query'  => array(
					'datetime'  => $timestamp,
					'before'    => true,
				),
			));
			if ( is_array($entries) && ! empty($entries) ) {
				foreach ( $entries as $entry ) {
					// Set to trash
					$entry->set_istrash( true );

					// Save entry, returns the id of the entry.
					$save = $entry->save();
					if ( $save ) {
						gwolle_gb_add_log_entry( $entry->get_id(), 'entry-trashed' );
					}
				}
				// Clear cache and maybe more.
				do_action( 'gwolle_gb_save_entry_admin', $entry );
			}
		}
	}
}
add_action( 'shutdown', 'gwolle_gb_addon_auto_delete_v2' );
