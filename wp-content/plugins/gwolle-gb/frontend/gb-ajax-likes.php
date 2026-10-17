<?php
/*
 * ajax.php
 * Processes AJAX requests.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Likes above content.
 *
 * @since 2.0.0
 */
function gwolle_gb_entry_read_add_content_before_likes_v2( $gb_metabox, $entry ) {
	if (get_option( 'gwolle_gb_addon-likes', 'false') === 'true') {
		if ( get_option( 'gwolle_gb_addon-likes_loc', 1 ) == 0 ) {
			$gb_metabox .= '
						<div class="gb-metabox-content gb-metabox-content-likes">
							' . gwolle_gb_entry_likes_get_link_v2( $entry ) . '
						</div>';
		}
	}
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_read_add_content_before', 'gwolle_gb_entry_read_add_content_before_likes_v2', 80, 2 );


/*
 * Likes under content.
 *
 * @since 2.0.0
 */
function gwolle_gb_entry_read_add_content_likes_v2( $gb_metabox, $entry ) {
	if (get_option( 'gwolle_gb_addon-likes', 'false') === 'true') {
		if ( get_option( 'gwolle_gb_addon-likes_loc', 1 ) == 1 ) {
			$gb_metabox .= '
						<div class="gb-metabox-content gb-metabox-content-likes">
							' . gwolle_gb_entry_likes_get_link_v2( $entry ) . '
						</div>';
		}
	}
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_read_add_content', 'gwolle_gb_entry_read_add_content_likes_v2', 80, 2 );


/*
 * Likes in metabox.
 *
 * @since 2.0.0
 */
function gwolle_gb_entry_metabox_lines_likes_v2( $gb_metabox, $entry ) {
	if (get_option( 'gwolle_gb_addon-likes', 'false') === 'true') {
		if ( get_option( 'gwolle_gb_addon-likes_loc', 1 ) == 2 ) {
			$gb_metabox .= '
						<div class="gb-metabox-line gb-metabox-line-likes">
							' . gwolle_gb_entry_likes_get_link_v2( $entry ) . '
						</div>';
		}
	}
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_metabox_lines', 'gwolle_gb_entry_metabox_lines_likes_v2', 80, 2 );


/*
 * Show Likes for metabox and content.
 * Uses no ajax icon, it's not needed and ugly.
 *
 * @since 2.7.0
 */
function gwolle_gb_entry_likes_get_link_v2( $entry ) {

	$user_ip = gwolle_gb_get_user_ip();

	$likes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'likes' );
	if ( ! is_array( $likes ) ) {
		$count_likes = 0;
	} else {
		$count_likes = count( $likes );
	}
	if ( is_array( $likes ) && in_array( $user_ip, $likes ) ) {
		// already liked.
		$class = ' gb-already-liked';
	} else {
		$class = '';
	}

	$like_link = '
							<a class="gwolle-gb-like-link' . $class . '" href="#" data-entry-id="' . (int) $entry->get_id() . '" title="' . esc_attr__('Like this entry', 'gwolle-gb') . '">
								<img src="' . GWOLLE_GB_URL . 'assets/like/like.png" alt="' . esc_attr__('Like this entry', 'gwolle-gb') . '" style="margin-top:-2px;width:20px;" />
								<span class="gb-likes">' . (int) $count_likes . '</span>
							</a>&nbsp;&nbsp;&nbsp;';

	$unlikes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'unlikes' );
	if ( ! is_array( $unlikes ) ) {
		$count_unlikes = 0;
	} else {
		$count_unlikes = count( $unlikes );
	}
	if ( is_array( $unlikes ) && in_array( $user_ip, $unlikes ) ) {
		// already unliked.
		$class = ' gb-already-unliked';
	} else {
		$class = '';
	}

	$unlike_link = '
							<a class="gwolle-gb-unlike-link' . $class . '" href="#" data-entry-id="' . (int) $entry->get_id() . '" title="' . esc_attr__('Unlike this entry', 'gwolle-gb') . '">
								<img src="' . GWOLLE_GB_URL . 'assets/like/unlike.png" alt="' . esc_attr__('Unlike this entry', 'gwolle-gb') . '" style="margin-top:2px;width:20px;" />
								<span class="gb-unlikes">' . (int) $count_unlikes . '</span>
							</a>';

	return $like_link . $unlike_link;

}


/*
 * Callback function for handling the Ajax request that is generated from the JavaScript above in gwolle_gb_like_javascript.
 * Also for non-logged-in users.
 * Make sure to flush cache when re-fetching likes/unlikes.
 */
add_action( 'wp_ajax_gwolle_gb_like', 'gwolle_gb_like_callback_v2' );
add_action( 'wp_ajax_nopriv_gwolle_gb_like', 'gwolle_gb_like_callback_v2' );
function gwolle_gb_like_callback_v2() {

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

	if (get_option( 'gwolle_gb_addon-likes', 'false') == 'false') {
		echo 'error, like function is disabled.';
		die();
	}

	if (isset($_POST['id'])) {
		$id = (int) $_POST['id'];
	}
	if (isset($_POST['setter'])) {
		$setter = (string) $_POST['setter'];
	}

	if ( isset($id) && $id > 0 && isset($setter) && $setter == 'like' ) {
		$entry = new gwolle_gb_entry();
		$result = $entry->load( $id );
		if ( ! $result ) {
			echo 'error, no such entry.';
			die();
		}

		$entry_id = $entry->get_id();
		$user_ip = gwolle_gb_get_user_ip();
		$likes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'likes' );
		$success = false;

		if ( ! is_array( $likes ) ) {
			// add like.
			$success = gwolle_gb_addon_add_like_v2( $entry_id, $user_ip, array() );
			if ( $success ) {
				// remove unlike, since both make no sense.
				$unlikes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'unlikes' );
				gwolle_gb_addon_delete_unlike_v2( $entry_id, $user_ip, $unlikes );
			}
		} else if ( ! in_array( $user_ip, $likes ) ) {
			// add like.
			$success = gwolle_gb_addon_add_like_v2( $entry_id, $user_ip, $likes );
			if ( $success ) {
				// remove unlike, since both make no sense.
				$unlikes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'unlikes' );
				gwolle_gb_addon_delete_unlike_v2( $entry_id, $user_ip, $unlikes );
			}
		} else if ( in_array( $user_ip, $likes ) ) {
			// delete like.
			$success = gwolle_gb_addon_delete_like_v2( $entry_id, $user_ip, $likes );
		}

		$data = array();
		$data['success'] = $success;
		$likes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'likes', true );
		if ( is_array( $likes ) ) {
			$data['likes'] = count($likes);
		} else {
			$data['likes'] = 0;
		}
		$data['class_likes'] = '';
		if ( is_array( $likes ) && in_array( $user_ip, $likes ) ) {
			$data['class_likes'] = 'gb-already-liked';
		}
		$unlikes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'unlikes', true );
		if ( is_array( $unlikes ) ) {
			$data['unlikes'] = count($unlikes);
		} else {
			$data['unlikes'] = 0;
		}
		$data['class_unlikes'] = '';
		if ( is_array( $unlikes ) && in_array( $user_ip, $unlikes ) ) {
			$data['class_unlikes'] = 'gb-already-unliked';
		}

		echo json_encode( $data );
		die();

	} else {
		echo 'error, not the right data';
		die();
	}

}


/*
 * Callback function for handling the Ajax request that is generated from the JavaScript above in gwolle_gb_like_javascript.
 * Also for non-logged-in users.
 * Make sure to flush cache when re-fetching likes/unlikes.
 */
add_action( 'wp_ajax_gwolle_gb_unlike', 'gwolle_gb_unlike_callback_v2' );
add_action( 'wp_ajax_nopriv_gwolle_gb_unlike', 'gwolle_gb_unlike_callback_v2' );
function gwolle_gb_unlike_callback_v2() {

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

	if (get_option( 'gwolle_gb_addon-likes', 'false') == 'false') {
		echo 'error, like function is disabled.';
		die();
	}

	if (isset($_POST['id'])) {
		$id = (int) $_POST['id'];
	}
	if (isset($_POST['setter'])) {
		$setter = (string) $_POST['setter'];
	}

	if ( isset($id) && $id > 0 && isset($setter) && $setter == 'unlike' ) {
		$entry = new gwolle_gb_entry();
		$result = $entry->load( $id );
		if ( ! $result ) {
			echo 'error, no such entry.';
			die();
		}

		$entry_id = $entry->get_id();
		$user_ip = gwolle_gb_get_user_ip();
		$unlikes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'unlikes' );
		$success = false;

		if ( ! is_array( $unlikes ) ) {
			// add unlike.
			$success = gwolle_gb_addon_add_unlike_v2( $entry_id, $user_ip, array() );
			if ( $success ) {
				// remove like, since both make no sense.
				$likes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'likes' );
				gwolle_gb_addon_delete_like_v2( $entry_id, $user_ip, $likes );
			}
		} else if ( ! in_array( $user_ip, $unlikes ) ) {
			// add unlike.
			$success = gwolle_gb_addon_add_unlike_v2( $entry_id, $user_ip, $unlikes );
			if ( $success ) {
				// remove like, since both make no sense.
				$likes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'likes' );
				gwolle_gb_addon_delete_like_v2( $entry_id, $user_ip, $likes );
			}
		} else if ( in_array( $user_ip, $unlikes ) ) {
			// delete unlike.
			$success = gwolle_gb_addon_delete_unlike_v2( $entry_id, $user_ip, $unlikes );
		}

		$data = array();
		$data['success'] = $success;
		$likes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'likes', true );
		if ( is_array( $likes ) ) {
			$data['likes'] = count($likes);
		} else {
			$data['likes'] = 0;
		}
		$data['class_likes'] = '';
		if ( is_array( $likes ) && in_array( $user_ip, $likes ) ) {
			$data['class_likes'] = 'gb-already-liked';
		}
		$unlikes = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'unlikes', true );
		if ( is_array( $unlikes ) ) {
			$data['unlikes'] = count($unlikes);
		} else {
			$data['unlikes'] = 0;
		}
		$data['class_unlikes'] = '';
		if ( is_array( $unlikes ) && in_array( $user_ip, $unlikes ) ) {
			$data['class_unlikes'] = 'gb-already-unliked';
		}

		echo json_encode( $data );
		die();

	} else {
		echo 'error, not the right data';
		die();
	}

}


/*
 * Add like to value for meta field.
 *
 * @return bool true on success, false otherwise.
 *
 * @since 2.7.0
 */
function gwolle_gb_addon_add_like_v2( $entry_id, $user_ip, $likes ) {

	if ( is_array( $likes ) && ! in_array( $user_ip, $likes ) ) {
		$likes[] = $user_ip;
		$return = gwolle_gb_addon_save_meta_v2( $entry_id, 'likes', $likes );

		if ( (int) $return > 0 ) {
			return true;
		}
	}

	return false;

}


/*
 * Delete like from value for meta field.
 *
 * @return bool true on success, false otherwise.
 *
 * @since 2.7.0
 */
function gwolle_gb_addon_delete_like_v2( $entry_id, $user_ip, $likes ) {

	if ( is_array( $likes ) && in_array( $user_ip, $likes ) ) {
		$likes_ = array();
		foreach ( $likes as $like ) {
			if ( $user_ip == $like ) {
				continue; // clean it out.
			}
			$likes_[] = $like;
		}
		$likes = $likes_;
		$return = gwolle_gb_addon_save_meta_v2( $entry_id, 'likes', $likes );

		if ( (int) $return > 0 ) {
			return true;
		}
	}

	return false;

}


/*
 * Add unlike to value for meta field.
 *
 * @return bool true on success, false otherwise.
 *
 * @since 2.7.0
 */
function gwolle_gb_addon_add_unlike_v2( $entry_id, $user_ip, $unlikes ) {

	if ( is_array( $unlikes ) && ! in_array( $user_ip, $unlikes ) ) {
		$unlikes[] = $user_ip;
		$return = gwolle_gb_addon_save_meta_v2( $entry_id, 'unlikes', $unlikes );

		if ( (int) $return > 0 ) {
			return true;
		}
	}

	return false;

}


/*
 * Delete unlike from value for meta field.
 *
 *  @return bool true on success, false otherwise.
 *
 * @since 2.7.0
 */
function gwolle_gb_addon_delete_unlike_v2( $entry_id, $user_ip, $unlikes ) {

	if ( is_array( $unlikes ) && in_array( $user_ip, $unlikes ) ) {
		$unlikes_ = array();
		foreach ( $unlikes as $unlike ) {
			if ( $user_ip == $unlike ) {
				continue; // clean it out.
			}
			$unlikes_[] = $unlike;
		}
		$unlikes = $unlikes_;
		$return = gwolle_gb_addon_save_meta_v2( $entry_id, 'unlikes', $unlikes );

		if ( (int) $return > 0 ) {
			return true;
		}
	}

	return false;

}
