<?php
/*
 * ajax.php
 * Processes AJAX requests.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Delete Link for Moderators and Authors
 */
function gwolle_gb_addon_write_add_after_submit_preview( $html ) {

	if (get_option( 'gwolle_gb_addon-preview', 'true') === 'true') {
		$html .= '
					<input type="button" name="gwolle_gb_preview" class="gwolle_gb_preview button btn" value="' . esc_attr__('Preview', 'gwolle-gb') . '" />
					<span class="gwolle_gb_addon_preview_ajax_icon gwolle-gb-addon-preview-ajax-icon"></span>
			';
	}

	return $html;

}
add_filter( 'gwolle_gb_write_add_after_submit', 'gwolle_gb_addon_write_add_after_submit_preview', 10, 1 );


/*
 * Callback function for handling the Ajax request that is generated from the JavaScript above in gwolle_gb_delete_javascript.
 * Also for non-logged-in users.
 */
add_action( 'wp_ajax_gwolle_gb_preview', 'gwolle_gb_preview_callback_v2' );
add_action( 'wp_ajax_nopriv_gwolle_gb_preview', 'gwolle_gb_preview_callback_v2' );
function gwolle_gb_preview_callback_v2() {

	if (get_option( 'gwolle_gb_addon-preview', 'true') === 'false') {
		echo 'error, preview function is disabled.';
		die();
	}

	/* Check Nonce */
	$verified = false;
	if ( isset($_POST['nonce']) ) {
		$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'gwolle_gb_add_entry' );
	}
	if ( $verified == false ) {
		// Nonce is invalid.
		echo 'error, nonce check failed.';
		die();
	}

	$gwolle_gb_formdata = array(); // used to set the data in the entry

	$field_name = gwolle_gb_get_field_name( 'name' );
	if (isset($_POST["$field_name"])) {
		$author_name = trim($_POST["$field_name"]);
		$author_name = gwolle_gb_maybe_encode_emoji( $author_name, 'author_name' );
		$gwolle_gb_formdata['author_name'] = $author_name;
		gwolle_gb_add_formdata( 'author_name', $author_name );
	}
	$field_name = gwolle_gb_get_field_name( 'city' );
	if (isset($_POST["$field_name"])) {
		$author_origin = trim($_POST["$field_name"]);
		$author_origin = gwolle_gb_maybe_encode_emoji( $author_origin, 'author_origin' );
		$gwolle_gb_formdata['author_origin'] = $author_origin;
		gwolle_gb_add_formdata( 'author_origin', $author_origin );
	}
	$field_name = gwolle_gb_get_field_name( 'email' );
	if (isset($_POST["$field_name"])) {
		$author_email = trim($_POST["$field_name"]);
		$gwolle_gb_formdata['author_email'] = $author_email;
		gwolle_gb_add_formdata( 'author_email', $author_email );
	}
	$field_name = gwolle_gb_get_field_name( 'website' );
	if (isset($_POST["$field_name"])) {
		$author_website = trim($_POST["$field_name"]);
		$gwolle_gb_formdata['author_website'] = $author_website;
		gwolle_gb_add_formdata( 'author_website', $author_website );
	}
	$field_name = gwolle_gb_get_field_name( 'content' );
	if (isset($_POST["$field_name"])) {
		$content = trim($_POST["$field_name"]);
		$content = gwolle_gb_maybe_encode_emoji( $content, 'content' );
		$gwolle_gb_formdata['content'] = $content;
		gwolle_gb_add_formdata( 'content', $content );
	}

	/* New Instance of gwolle_gb_entry. */
	$entry = new gwolle_gb_entry();

	/* Set the data in the instance */
	$set_data = $entry->set_data( $gwolle_gb_formdata );
	if ( ! $set_data ) {
		echo 'error, cannot handle the data';
		die();
	}
	$user_id = get_current_user_id(); // Returns 0 if no current user.
	$entry->set_author_id( $user_id );

	gwolle_gb_addon_save_meta_for_preview_v2( $entry );

	$response = gwolle_gb_single_view( $entry );

	echo $response;
	die(); // this is required to return a proper result
}


/*
 * Save list of meta fields after preview.
 *
 * @since 1.2.5
 *
 * @param string $entry the guestbook entry the metadata belongs to.
 *
 * @return none.
 */
function gwolle_gb_addon_save_meta_for_preview_v2( $entry ) {
	$fields = gwolle_gb_addon_get_meta_fields_all_v2();
	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$slug = 'gwolle_gb_addon_' . $field['slug'];
			$name = $field['name'];
			$type = 'text';

			if ( isset($_POST[$slug]) && strlen($_POST[$slug]) > 0 ) {

				if ( isset( $field['type'] ) && isset( $field['options'] ) ) {
					$types = array( 'text', 'checkbox', 'radio', 'select', 'textarea' );
					if ( in_array( $field['type'], $types ) ) {
						$type = $field['type'];
						$options_ = explode( "\n", $field['options'] );
						$options = array();
						foreach ( (array) $options_ as $option ) {
							$option = trim( $option );
							if ( empty( $option ) ) {
								continue;
							}
							$options[] = $option;
						}
					}
				}

				if ( $type === 'checkbox' ) {
					if ( isset($_POST["$slug"]) && $_POST["$slug"] == 'on' ) {
						$gwolle_gb_addon_meta = esc_html__('Yes', 'gwolle-gb');
						gwolle_gb_add_formdata( $slug, $gwolle_gb_addon_meta );
					}
				} else if ( $type === 'radio' ) {
					if ( isset($_POST["$slug"]) && is_numeric($_POST["$slug"]) ) {
						$key = (int) $_POST["$slug"];
						$gwolle_gb_addon_meta = $options[$key];
						gwolle_gb_add_formdata( $slug, $gwolle_gb_addon_meta );
					}
				} else if ( $type === 'select' ) {
					if ( isset($_POST["$slug"]) && is_numeric($_POST["$slug"]) ) {
						$key = (int) $_POST["$slug"];
						if ( $key > 0 ) {
							$key = ( $key - 1 ); // Frontend starts counting at 1, 0 means nothing was selected.
						}
						$gwolle_gb_addon_meta = $options[$key];
						gwolle_gb_add_formdata( $slug, $gwolle_gb_addon_meta );
					}
				} else {
					$gwolle_gb_addon_meta = (string) $_POST["$slug"];
					gwolle_gb_add_formdata( $slug, $gwolle_gb_addon_meta );
				}
			}
		}
		$slug = 'gwolle_gb_addon_starrating';
		if ( isset($_POST["$slug"]) && strlen($_POST["$slug"]) > 0 ) {
			$gwolle_gb_addon_meta = (string) $_POST["$slug"];
			gwolle_gb_add_formdata( $slug, $gwolle_gb_addon_meta );
		}

	}
}


/*
 * Get meta field for preview.
 *
 * @since 1.2.5
 *
 * @param int $entry_id the ID for the preview entry, should be 0.
 * @param string $meta_key the meta_key for the preview entry.
 *
 * @return mixed/bool/string Single metadata value. false if not found.
 */
function gwolle_gb_addon_get_meta_for_preview_v2( $entry_id, $meta_key ) {

	if ( $entry_id != 0 ) {
		return false;
	}

	$gwolle_gb_formdata = gwolle_gb_get_formdata();
	$meta_key = 'gwolle_gb_addon_' . $meta_key;

	if ( is_array($gwolle_gb_formdata) && ! empty($gwolle_gb_formdata) ) {
		if ( isset($gwolle_gb_formdata["$meta_key"]) ) {
			$meta_value = $gwolle_gb_formdata["$meta_key"];
			$meta_value = maybe_unserialize( $meta_value );
			$meta_value = gwolle_gb_sanitize_output( $meta_value );
			return $meta_value;
		}
	}

	// no meta_value was found. Return false.
	return false;

}
