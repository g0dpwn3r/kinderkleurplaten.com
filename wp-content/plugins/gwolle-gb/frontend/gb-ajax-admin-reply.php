<?php
/*
 * ajax.php
 * Processes AJAX requests.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Add Admin Reply for Moderators.
 */
function gwolle_gb_entry_metabox_lines_admin_reply_v2( $gb_metabox, $entry ) {

	$admin_reply = $entry->get_admin_reply();
	if ( strlen($admin_reply) > 0 ) {
		return $gb_metabox;
	}

	if ( current_user_can('gwolle_gb_moderate_comments') ) {
		$gb_metabox .= '
					<div class="gb-metabox-line gb-metabox-line-admin-reply">
						<a class="gwolle_gb_admin_reply" href="#" data-entry-id="' . (int) $entry->get_id() . '" title="' . esc_attr__('Add admin reply', 'gwolle-gb') . '">' . esc_html__('Admin Reply', 'gwolle-gb') . '</a>
					</div>';
	}

	return $gb_metabox;

}
add_filter( 'gwolle_gb_entry_metabox_lines', 'gwolle_gb_entry_metabox_lines_admin_reply_v2', 92, 2 );


/*
 * Add container for the textarea to each entry, so we can use this for AJAX handling of admin reply.
 *
 * @param  string $content html added to each entry.
 * @param  string $entry instance of gwolle_gb_entry class.
 * @return string $content updated html added to each entry.
 *
 * @since 2.10.2
 */
function gwolle_gb_entry_read_add_after_admin_reply_container_v2( $content, $entry ) {

	if ( current_user_can('gwolle_gb_moderate_comments') ) {
		$entry_id = (int) $entry->get_id();
		$content .= '
				<div id="admin-reply-container-' . $entry_id . '">
				</div>
				';
	}

	return $content;

}
add_filter( 'gwolle_gb_entry_read_add_after', 'gwolle_gb_entry_read_add_after_admin_reply_container_v2', 10, 2 );


/*
 * Add prefab textarea to the list of entries, so we can use admin reply for AJAX handling of entries.
 *
 * @param  string $output html added to the list of entries.
 * @return string $output updated html added to the list of entries.
 *
 * @since 2.10.2
 */
function gwolle_gb_addon_admin_reply_add_html_v2( $output ) {

	if ( current_user_can('gwolle_gb_moderate_comments') ) {
		$output .= '
				<div id="admin_reply_ajax_prefab_textarea" style="display: none;">
				<div id="admin_reply_ajax">
					<span>' . esc_attr__('Admin Reply:', 'gwolle-gb') . '</span>
					<textarea name="gwolle_gb_admin_reply_text" id="gwolle_gb_admin_reply_text" style="width:400px;display:block;"></textarea>
					<input type="button" name="gwolle_gb_admin_reply_submit" id="gwolle_gb_admin_reply_submit" class="button btn" value="' . esc_attr__('Submit', 'gwolle-gb') . '" style="margin:5px 0;"/>
					<input type="button" name="gwolle_gb_admin_reply_cancel" id="gwolle_gb_admin_reply_cancel" class="button btn" value="' . esc_attr__('Cancel', 'gwolle-gb') . '" style="margin:5px 5px;" />
					<span class="gwolle-gb-submit-ajax-icon"></span>
				</div>
				</div>
				';
	}

	return $output;

}
add_filter( 'gwolle_gb_entries_read', 'gwolle_gb_addon_admin_reply_add_html_v2' );


/*
 * Callback function for handling the Ajax request that is generated from the JavaScript above in gwolle_gb_admin_reply_javascript
 */
add_action( 'wp_ajax_gwolle_gb_admin_reply', 'gwolle_gb_admin_reply_callback_v2' );
function gwolle_gb_admin_reply_callback_v2() {

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

	if ( ! current_user_can( 'gwolle_gb_moderate_comments' ) ) {
		echo 'error, no permission.';
		die();
	}

	if ( isset($_POST['id']) && (int) $_POST['id'] > 0 ) {
		$id = (int) $_POST['id'];
	} else {
		echo 'error, no entry id given.';
		die();
	}
	if ( isset($_POST['admin_reply']) && strlen($_POST['admin_reply']) > 0 ) {
		$admin_reply = gwolle_gb_maybe_encode_emoji( $_POST['admin_reply'], 'admin_reply' );
	} else {
		echo 'error, no data given.';
		die();
	}

	$entry = new gwolle_gb_entry();
	$result = $entry->load( $id );
	if ( ! $result ) {
		echo 'error, no such entry.';
		die();
	}

	if ( strlen( $entry->get_admin_reply() ) > 0 ) {
		echo 'error, already set.';
		die();
	}

	$entry->set_admin_reply_uid( get_current_user_id() );
	$entry->set_admin_reply( $admin_reply );

	$result = $entry->save();
	if ( $result ) {
		if (get_option( 'gwolle_gb-mail_author', 'false') === 'true') {
			gwolle_gb_mail_author_on_admin_reply( $entry );
		}
		gwolle_gb_add_log_entry( $entry->get_id(), 'admin-reply-added' );
		$read_setting = gwolle_gb_get_setting( 'read' );

		$class = '';
		if ( get_option( 'gwolle_gb-admin_style', 'false' ) === 'true' ) {
			$class = ' admin-entry';
		}

		$admin_reply = '
			<div class="gb-entry-admin_reply' . $class . '">';

		/* Admin Reply Author */
		$admin_reply .= '
				<div class="gb-admin_reply_uid">';
		$admin_reply_name = gwolle_gb_is_moderator( $entry->get_admin_reply_uid() );
		/* Admin Avatar */
		if ( isset($read_setting['read_aavatar']) && $read_setting['read_aavatar'] === 'true' ) {
			$user_info = get_userdata( $entry->get_admin_reply_uid() );
			$admin_reply_email = $user_info->user_email;
			$avatar = get_avatar( $admin_reply_email, 32, '', $admin_reply_name );
			if ($avatar) {
				$admin_reply .= '
					<span class="gb-admin-avatar">' . $avatar . '</span>';
			}
		}
		/* Admin Header */
		if ( isset($read_setting['read_name']) && $read_setting['read_name'] === 'true' && $admin_reply_name ) {
			$admin_reply_header = '
					<em>' . esc_html__('Admin Reply by:', 'gwolle-gb') . ' ' . $admin_reply_name . '</em>';
		} else {
			$admin_reply_header = '
					<em>' . esc_html__('Admin Reply:', 'gwolle-gb') . '</em>';
		}
		$admin_reply .= apply_filters( 'gwolle_gb_admin_reply_header', $admin_reply_header, $entry );
		$admin_reply .= '
				</div> ';

		/* Admin Reply Content */
		$admin_reply_content = gwolle_gb_sanitize_output( $entry->get_admin_reply(), 'admin_reply' );
		if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
			$admin_reply_content = convert_smilies($admin_reply_content);
		}
		if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
			$admin_reply_content = nl2br($admin_reply_content);
		}
		if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled'] === 'true' ) {
			$admin_reply_content = gwolle_gb_bbcode_parse($admin_reply_content);
		} else {
			$admin_reply_content = gwolle_gb_bbcode_strip($admin_reply_content);
		}
		$admin_reply .= '
				<div class="gb-admin_reply_content">
				' . $admin_reply_content . '
				</div>
			</div>';

		echo $admin_reply;
		die();
	} else {
		echo 'error, could not save.';
		die();
	}

}
