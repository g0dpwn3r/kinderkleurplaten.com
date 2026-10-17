<?php
/*
 * Upload media for frontend form
 *
 * Functions for uploading media files through the guestbook form to the media library.
 * Display is done as bbcode for images.
 * Entries may contain multiple media uploads.
 *
 * @package Gwolle-GB-AddOn
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Add form field for uploading media files to the media library.
 *
 * @since 2.3.0
 *
 * @param string $form_html the html for form fields.
 * @return string $form_html the html for form fields.
 *
 * @uses content field and markitup are required for this to work.
 */
function gwolle_gb_addon_write_add_after_content_upload_media_v2( $form_html ) {

	$gwolle_gb_upload_files_free_for_all = apply_filters( 'gwolle_gb_upload_files_free_for_all', false );

	if ( ( ! current_user_can( 'gwolle_gb_upload_files' ) ) && ( ! $gwolle_gb_upload_files_free_for_all ) ) {
		return $form_html;
	}

	if (get_option( 'gwolle_gb_addon-upload', 'false') !== 'true') {
		return $form_html;
	}

	$form_setting = gwolle_gb_get_setting( 'form' );
	if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled'] === 'true' ) {
		if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled'] === 'true' ) {
			$field_id = gwolle_gb_get_field_id( 'gwolle-gb-addon-upload-media' );
			$allowed_filesize = gwolle_gb_addon_get_upload_media_size_v2();
			$allowed_filesize = (int) floor( $allowed_filesize / 1048576 ); //1024 x 1024

			$form_html .= '
				<div class="gwolle-gb-addon-upload-container">
					<div class="label"><label for="' . esc_attr( $field_id ) . '" class="text-info">' . sprintf( esc_html__('Upload images (Max %d MiB)', 'gwolle-gb'), $allowed_filesize ) . '</label></div>
					<div class="input gwolle-gb-upload">
						<input class="gwolle-gb-addon-upload-media" id="' . esc_attr( $field_id ) . '" name="gwolle-gb-addon-upload-media" type="file" accept=".jpg,.png,.webp,.gif" /><br />
						<input type="button" name="gwolle-gb-addon-upload-button" class="button btn btn-default gwolle-gb-addon-upload-button" value="' . /* translators: Button text */ esc_attr__('Upload and add image', 'gwolle-gb') . '" />
						<div class="gwolle-gb-addon-upload-message"></div>
					</div>
					<div class="clearBoth">&nbsp;</div>
				</div>
				';
		}
	}

	return $form_html;

}
add_filter( 'gwolle_gb_write_add_after_content', 'gwolle_gb_addon_write_add_after_content_upload_media_v2' );


/*
 * Callback function for handling the Ajax request that is generated from the JavaScript above in gwolle_gb_addon_write_add_after_content_upload_media
 *
 * @since 2.3.0
 */
add_action( 'wp_ajax_gwolle_gb_addon_upload_media', 'gwolle_gb_addon_upload_media_v2' );
add_action( 'wp_ajax_nopriv_gwolle_gb_addon_upload_media', 'gwolle_gb_addon_upload_media_v2' );
function gwolle_gb_addon_upload_media_v2() {

	$image_url = '';

	$gwolle_gb_upload_files_free_for_all = apply_filters( 'gwolle_gb_upload_files_free_for_all', false );

	if ( ( ! current_user_can( 'gwolle_gb_upload_files' ) ) && ( ! $gwolle_gb_upload_files_free_for_all ) ) {
		return 'error: no-permission';
	}

	if (get_option( 'gwolle_gb_addon-upload', 'false') !== 'true') {
		return 'error: no-permission';
	}

	$field_name = gwolle_gb_get_field_name( 'nonce' );
	$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'gwolle_gb_add_entry' );
	if ( $verified === false ) {
		// Nonce is invalid, so considered spam
		gwolle_gb_add_message( '<p class="refuse-upload-nonce"><strong>' . esc_html__('The Nonce did not validate. Please reload the page and try again.', 'gwolle-gb') . '</strong></p>', true, false );
	} else {
		$image_url = gwolle_gb_addon_upload_media_handler_v2();
	}

	$data = array();
	$data['image_url']          = esc_url( $image_url );
	$data['gwolle_gb_messages'] = gwolle_gb_get_messages();

	echo json_encode( $data );
	die(); // This is required to return a proper result.

}


/*
 * Handler for uploading media files to the media library.
 *
 * @since 2.3.0
 *
 * @uses array $_FILES the uploaded media.
 *
 * @return string $attachment_src_large the url for the uploaded media file in 'large'. Will be '' in case of an error.
 */
function gwolle_gb_addon_upload_media_handler_v2() {

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	if ( isset($_FILES['gwolle-gb-addon-upload-media']) ) {
		if ($_FILES['gwolle-gb-addon-upload-media']['name']) {
			$filename = gwolle_gb_addon_truncate_slug_v2( sanitize_text_field( $_FILES['gwolle-gb-addon-upload-media']['name'] ) );
			$description = sanitize_text_field( $_FILES['gwolle-gb-addon-upload-media']['name'] );
			$mimetype = sanitize_text_field( $_FILES['gwolle-gb-addon-upload-media']['type'] ); // like "image/png"
			// $tmp_name = sanitize_text_field( $_FILES['gwolle-gb-addon-upload-media']['tmp_name'] ); // unused
			$error = sanitize_text_field( $_FILES['gwolle-gb-addon-upload-media']['error'] );
			$filesize = sanitize_text_field( $_FILES['gwolle-gb-addon-upload-media']['size'] );
			$allowed_filesize = gwolle_gb_addon_get_upload_media_size_v2();
			$allowed_filesize_mib = (int) floor( $allowed_filesize / 1048576 ); //1024 x 1024
			$allowed_images = array( 'image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp' );
			$allowed_extensions = array( 'jpg', 'jpeg', 'jpe', 'png', 'gif', 'webp' );

			/*
			 * Filters the list of allowed file extensions when sideloading an image from a URL.
			 *
			 * @param string[] $allowed_extensions Array of allowed file extensions.
			 * @param string   $filename           The URL of the image to download.
			 */
			$allowed_extensions = apply_filters( 'image_sideload_extensions', $allowed_extensions, $filename );
			$allowed_extensions = array_map( 'preg_quote', $allowed_extensions );

			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(' . implode( '|', $allowed_extensions ) . ')\b/i', $filename, $matches );

			if ( ! $error ) { // if no errors...
				if ( $filesize > ( $allowed_filesize ) ) { // Can't be larger than what is allowed.
					gwolle_gb_add_message( '<p>' . sprintf( esc_html__('Your file is too large (Max %d MiB)', 'gwolle-gb'), $allowed_filesize_mib ) . '</p>', true, false);
				} else if ( ! in_array( $mimetype, $allowed_images ) ) {
					gwolle_gb_add_message( '<p>' . esc_html__('Your file has the wrong mime type.', 'gwolle-gb') . '</p>', true, false);
				} else if ( ! $matches ) {
					gwolle_gb_add_message( '<p>' . esc_html__('Your file has the wrong file extension.', 'gwolle-gb') . '</p>', true, false);
				} else {

					$attachment_id = media_handle_sideload( $_FILES['gwolle-gb-addon-upload-media'], 0, $description, array() );

					if ( is_wp_error( $attachment_id ) ) {
						gwolle_gb_add_message( '<p>' . esc_html__('Something went wrong. Please try again or contact an admin.', 'gwolle-gb') . '</p>', true, false );
						return '';
					}

					if ( $attachment_id > 0 ) {
						gwolle_gb_add_message( '<p>' . esc_html__('File was uploaded successfully and is now added into the content as bbcode.', 'gwolle-gb') . '</p>', false, false);

						$attachment_src = wp_get_attachment_image_src( $attachment_id, 'large', false );
						if ( is_array( $attachment_src ) && isset( $attachment_src[0] ) && strlen( $attachment_src[0] ) > 0 ) {

							$attachment_src_large = $attachment_src[0];

							/*
							* Fires after media has been uploaded and added to the media library.
							*
							* @since 2.3.1
							*
							* @param int    $attachment_id        ID of the uploaded attachment.
							* @param string $attachment_src_large URL of the uploaded attachment as media source (large).
							*/
							do_action( 'gwolle_gb_addon_upload_media', $attachment_id, $attachment_src_large );

							return $attachment_src_large;
						}

					}

				}
			}
		}
	}

	return '';

}


/*
 * Size of upload media.
 *
 * @since 2.8.0
 *
 * @return int $filesize size of upload media in kibibytes.
 */
function gwolle_gb_addon_get_upload_media_size_v2() {

	// 6 x 1024 x 1024
	$filesize = 6291456; // 6 MiB

	$filesize = (int) apply_filters( 'gwolle_gb_addon_get_upload_media_size', $filesize );

	return $filesize;

}
