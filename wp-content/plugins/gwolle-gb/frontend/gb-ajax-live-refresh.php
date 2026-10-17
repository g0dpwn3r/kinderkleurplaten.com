<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Handles AJAX request for Automatic Refresh.
 *
 * Prints html with a list of entries.
 */
function gwolle_gb_addon_refresh_callback_v2() {

	$output = '';

	$page_num = 1;
	if ( isset($_POST['pageNum']) && is_numeric($_POST['pageNum']) ) {
		$page_num = (int) $_POST['pageNum'];
	}
	if ( $page_num !== 1 ) {
		die( 'error' );
	}

	$book_id = 1;
	if ( isset($_POST['book_id']) && is_numeric($_POST['book_id']) ) {
		$book_id = (int) $_POST['book_id'];
	}

	if ( isset($_POST['latest_entry_id']) && is_numeric($_POST['latest_entry_id']) ) {
		$latest_entry_id = (int) $_POST['latest_entry_id'];
		$entry = new gwolle_gb_entry();
		$result = $entry->load( $latest_entry_id ); // any status is fine.
		if ( $result ) {
			$timestamp = $entry->get_datetime();
		}
	}
	if ( ! isset( $timestamp ) ) {
		die( 'error' );
	}

	/* Get the entries for the frontend */
	$entries = gwolle_gb_get_entries(
		array(
			'offset'      => 0,
			'num_entries' => -1,
			'checked'     => 'checked',
			'trash'       => 'notrash',
			'spam'        => 'nospam',
			'book_id'     => $book_id,
			'date_query'  => array(
				'datetime'  => $timestamp,
				'after'     => true,
			),
		)
	);


	/* Entries from the template */
	if ( ! is_array( $entries ) || empty( $entries ) ) {
		die( 'false' ); // no new entries.
	} else {

		// Try to load and require_once the template from the themes folders.
		if ( locate_template( array( 'gwolle_gb-entry.php' ), true, true ) === '') {

			$output .= '<!-- Gwolle-GB Entry: Default Template Loaded -->
				';

			// No template found and loaded in the theme folders.
			// Load the template from the plugin folder.
			require_once GWOLLE_GB_DIR . '/frontend/gwolle_gb-entry.php';

		} else {

			$output .= '<!-- Gwolle-GB Entry: Custom Template Loaded -->
				';

		}

		$counter = 0;
		$first = true;
		foreach ($entries as $entry) {

			$counter++;

			// Run the function from the template to get the entry.
			$entry_output = gwolle_gb_entry_template( $entry, $first, $counter );

			// Add a filter for each entry, so devs can add or remove parts.
			$output .= apply_filters( 'gwolle_gb_entry_read', $entry_output, $entry );

			$first = false;

		}

	}

	echo $output;
	die(); // this is required to return a proper result

}
add_action( 'wp_ajax_gwolle_gb_addon_refresh', 'gwolle_gb_addon_refresh_callback_v2' );
add_action( 'wp_ajax_nopriv_gwolle_gb_addon_refresh', 'gwolle_gb_addon_refresh_callback_v2' );
