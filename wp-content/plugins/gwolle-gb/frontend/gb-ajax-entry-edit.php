<?php
/*
 * ajax.php
 * Processes AJAX requests.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Edit Entry for Authors and Moderators.
 *
 * @since 1.5.0
 */
function gwolle_gb_entry_metabox_lines_entry_edit_v2( $gb_metabox, $entry ) {

	if ( ! is_user_logged_in() ) {
		return $gb_metabox;
	}

	$moderator = false;
	if ( current_user_can('gwolle_gb_moderate_comments') ) {
		$moderator = true;
	}

	$author = $entry->get_author_id();
	$current_user = get_current_user_id();
	if ( ( (int) $author ) == 0 && $moderator == false ) {
		return $gb_metabox;
	}
	if ( ( (int) $author ) != ( (int) $current_user ) && $moderator == false ) {
		return $gb_metabox;
	}

	$form_setting = gwolle_gb_get_setting( 'form' );
	$content_field = '';
	if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled'] === 'true' ) {
		$content_field = '<div class="gwolle-gb-entry-edit-content-raw" style="display: none;">' . gwolle_gb_sanitize_output( $entry->get_content(), 'content' ) . '</div>';
	}
	$name_field = '';
	if ( isset($form_setting['form_name_enabled']) && $form_setting['form_name_enabled'] === 'true' ) {
		$name_field = '<div class="gwolle-gb-entry-edit-author-name-raw" style="display: none;">' . gwolle_gb_sanitize_output( $entry->get_author_name(), '' ) . '</div>';
	}
	$origin_field = '';
	if ( isset($form_setting['form_city_enabled']) && $form_setting['form_city_enabled'] === 'true' ) {
		$origin_field = '<div class="gwolle-gb-entry-edit-origin-raw" style="display: none;">' . gwolle_gb_sanitize_output( $entry->get_author_origin(), '' ) . '</div>';
	}

	if ( strlen($content_field) > 0 || strlen($name_field) > 0 || strlen($origin_field) > 0 ) {
		$gb_metabox .= '
					<div class="gb-metabox-line gb-metabox-line-entry-edit">
						<a class="gwolle-gb-entry-edit" href="#" data-entry-id="' . (int) $entry->get_id() . '" title="' . esc_attr__('Edit entry', 'gwolle-gb') . '">' . esc_html__('Edit entry', 'gwolle-gb') . '</a>
						' . $content_field . $name_field . $origin_field . '
						<span class="gwolle_gb_addon_entry_edit_icon gwolle-gb-addon-entry-edit-icon"></span>
					</div>';
	}

	return $gb_metabox;

}
add_filter( 'gwolle_gb_entry_metabox_lines', 'gwolle_gb_entry_metabox_lines_entry_edit_v2', 91, 2 );


/*
 * Add container for the textarea to each entry, so we can use this for AJAX handling of entry edit.
 *
 * @param  string $content html added to each entry.
 * @param  string $entry instance of gwolle_gb_entry class.
 * @return string $content updated html added to each entry.
 *
 * @since 2.10.2
 */
function gwolle_gb_entry_read_add_after_entry_edit_container_v2( $content, $entry ) {

	if ( ! is_user_logged_in() ) {
		return $content;
	}

	$entry_id = (int) $entry->get_id();
	$content .= '
				<div id="gwolle-gb-entry-edit-container-' . $entry_id . '">
				</div>
				';

	return $content;

}
add_filter( 'gwolle_gb_entry_read_add_after', 'gwolle_gb_entry_read_add_after_entry_edit_container_v2', 10, 2 );


/*
 * Add prefab textarea to the list of entries, so we can use entry edit for AJAX handling of entries.
 *
 * @param  string $output html added to the list of entries.
 * @return string $output updated html added to the list of entries.
 *
 * @since 2.10.2
 */
function gwolle_gb_addon_entry_edit_add_html_v2( $output ) {

	if ( ! is_user_logged_in() ) {
		return $output;
	}

	$form_setting = gwolle_gb_get_setting( 'form' );

	$content_field = '';
	if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled'] === 'true' ) {
		$field_id = gwolle_gb_get_field_id( 'gwolle-gb-entry-edit-content' );
		$content_label = apply_filters( 'gwolle_gb_author_content_label', esc_html__('Guestbook entry', 'gwolle-gb')  );
		$content_field = '<label for="' . esc_attr( $field_id ) . '" class="text-info">' . $content_label . '</label>' .
						'<textarea id="' . esc_attr( $field_id ) . '" name="gwolle-gb-entry-edit-content" class="gwolle-gb-entry-edit-content wp-exclude-emoji" style="width:400px;display:block;"></textarea>';
	}
	$name_field = '';
	if ( isset($form_setting['form_name_enabled']) && $form_setting['form_name_enabled'] === 'true' ) {
		$field_id = gwolle_gb_get_field_id( 'gwolle-gb-entry-edit-author-name' );
		$author_name_label = apply_filters( 'gwolle_gb_author_name_label', esc_html__('Name', 'gwolle-gb') );
		$name_field = '<label for="' . esc_attr( $field_id ) . '" class="text-info">' . $author_name_label . '</label>' .
						'<input type="text" id="' . esc_attr( $field_id ) . '" name="gwolle-gb-entry-edit-author-name" class="gwolle-gb-entry-edit-author-name wp-exclude-emoji" style="width:400px;display:block;" />';
	}
	$origin_field = '';
	if ( isset($form_setting['form_city_enabled']) && $form_setting['form_city_enabled'] === 'true' ) {
		$field_id = gwolle_gb_get_field_id( 'gwolle-gb-entry-edit-origin' );
		$origin_label = apply_filters( 'gwolle_gb_author_origin_label', esc_html__('City', 'gwolle-gb') );
		$origin_field = '<label for="' . esc_attr( $field_id ) . '" class="text-info">' . $origin_label . '</label>' .
						'<input type="text" id="' . esc_attr( $field_id ) . '" name="gwolle-gb-entry-edit-origin" class="gwolle-gb-entry-edit-origin wp-exclude-emoji" style="width:400px;display:block;" />';
	}

	if ( strlen($content_field) === 0 && strlen($name_field) === 0 && strlen($origin_field) === 0 ) {
		return;
	}

	$output .= '
				<div id="gwolle-gb-entry-edit-prefab" style="display: none;">
				<div id="gwolle-gb-entry-edit-ajax">
					<form action="#" method="POST">
					' . $content_field . $name_field . $origin_field . '
					<input type="button" name="gwolle-gb-entry-edit-submit" class="gwolle-gb-entry-edit-submit" class="button btn" value="' . esc_attr__('Submit', 'gwolle-gb') . '" style="margin:5px 0;"/>
						<input type="button" name="gwolle-gb-entry-edit-cancel" class="gwolle-gb-entry-edit-cancel" class="button btn" value="' . esc_attr__('Cancel', 'gwolle-gb') . '" style="margin:5px 5px;" />
						<span class="gwolle-gb-submit-ajax-icon"></span>
					</form>
				</div>
				</div>
				';

	return $output;

}
add_filter( 'gwolle_gb_entries_read', 'gwolle_gb_addon_entry_edit_add_html_v2' );


/*
 * Callback function for handling the Ajax request that is generated from the JavaScript above in gwolle_gb_edit_entry_javascript
 */
add_action( 'wp_ajax_gwolle_gb_entry_edit', 'gwolle_gb_entry_edit_callback_v2' );
function gwolle_gb_entry_edit_callback_v2() {

	if ( ! is_user_logged_in() ) {
		echo 'error, not logged in.';
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

	if ( isset($_POST['id']) && (int) $_POST['id'] > 0 ) {
		$id = (int) $_POST['id'];
	} else {
		echo 'error, no entry id given.';
		die();
	}

	$entry = new gwolle_gb_entry();
	$result = $entry->load( $id );
	if ( ! $result ) {
		echo 'error, no such entry.';
		die();
	}

	$moderator = false;
	if ( current_user_can('gwolle_gb_moderate_comments') ) {
		$moderator = true;
	}

	$author = $entry->get_author_id();
	$current_user = get_current_user_id();
	if ( ( (int) $author ) == 0 && $moderator === false ) {
		echo 'error, no permission.';
		die();
	}
	if ( ( (int) $author != (int) $current_user ) && $moderator === false ) {
		echo 'error, no permission.';
		die();
	}

	$form_setting = gwolle_gb_get_setting( 'form' );

	if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled'] === 'true' ) {
		if ( isset($_POST['content']) && strlen($_POST['content']) > 0 ) {
			$content = gwolle_gb_maybe_encode_emoji( $_POST['content'], 'content' );
			$entry->set_content( $content );
		}
	}

	if ( isset($form_setting['form_name_enabled']) && $form_setting['form_name_enabled'] === 'true' ) {
		if ( isset($_POST['author_name']) && strlen($_POST['author_name']) > 0 ) {
			$entry->set_author_name( $_POST['author_name'] );
		}
	}

	if ( isset($form_setting['form_city_enabled']) && $form_setting['form_city_enabled'] === 'true' ) {
		if ( isset($_POST['origin']) && strlen($_POST['origin']) > 0 ) {
			$entry->set_author_origin( $_POST['origin'] );
		}
	}

	$result = $entry->save();
	if ( $result ) {
		gwolle_gb_add_log_entry( $entry->get_id(), 'entry-edited' );

		$entry_content = gwolle_gb_sanitize_output( $entry->get_content(), 'content' );
		if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
			$entry_content = nl2br($entry_content);
		}
		if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled'] === 'true' ) {
			$entry_content = gwolle_gb_bbcode_parse($entry_content);
		} else {
			$entry_content = gwolle_gb_bbcode_strip($entry_content);
		}

		// No excerpt stuff, only the full content.

		if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
			// should be done after wp_trim_words to keep all the smileys intact.
			$entry_content = convert_smilies($entry_content);
		}
		$raw_content = gwolle_gb_sanitize_output( $entry->get_content(), 'content' );
		$author_name   = gwolle_gb_sanitize_output( $entry->get_author_name(), '' );
		$origin        = gwolle_gb_sanitize_output( $entry->get_author_origin(), '' );

		$data_content = array(
			'entry_content' => $entry_content,
			'raw_content'   => $raw_content,
			'author_name'   => $author_name,
			'origin'        => $origin,
			);

		echo json_encode( $data_content );
		die();
	} else {
		echo 'error, could not save.';
		die();
	}

}
