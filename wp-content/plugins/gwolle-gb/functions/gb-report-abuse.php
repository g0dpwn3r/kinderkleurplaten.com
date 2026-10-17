<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Set cookie with int of entry_id.
 * If no cookie support, use a transient.
 *
 * @since 1.2.0
 */
function gwolle_gb_addon_report_set_cookie_v2( $entry_id ) {

	$storagecookie      = 'gbra_flags';
	$cookie_lifetime    = 604800; // lifetime of the cookie ( 1 week ). After this duration the user can report an entry again.
	$transient_lifetime = 86400; // lifetime of fallback transients. lower to keep things usable.
	$user_ip            = gwolle_gb_get_user_ip();

	gwolle_gb_addon_report_add_test_cookie_v2();
	$data = array();
	if ( isset( $_COOKIE[ TEST_COOKIE ] ) ) {
		if ( isset( $_COOKIE["$storagecookie"] ) ) {
			$data = gwolle_gb_addon_report_unserialize_cookie_v2( $_COOKIE["$storagecookie"] );
			$count = 1;
			if ( isset( $data["$entry_id"] ) ) {
				$count = $data["$entry_id"];
				$count++;
			}
			$data["$entry_id"] = $count;
			$cookie = gwolle_gb_addon_report_serialize_cookie_v2( $data );
			@setcookie( $storagecookie, $cookie, ( time() + $cookie_lifetime ), COOKIEPATH, COOKIE_DOMAIN );
			if ( SITECOOKIEPATH != COOKIEPATH ) {
				@setcookie( $storagecookie, $cookie, ( time() + $cookie_lifetime ), SITECOOKIEPATH, COOKIE_DOMAIN);
			}
		} else {
			$count = 1;
			if ( isset( $data["$entry_id"] ) ) {
				$count = $data["$entry_id"];
				$count++;
			}
			$data["$entry_id"] = $count;
			$cookie = gwolle_gb_addon_report_serialize_cookie_v2( $data );
			@setcookie( $storagecookie, $cookie, ( time() + $cookie_lifetime ), COOKIEPATH, COOKIE_DOMAIN );
			if ( SITECOOKIEPATH != COOKIEPATH ) {
				@setcookie( $storagecookie, $cookie, ( time() + $cookie_lifetime ), SITECOOKIEPATH, COOKIE_DOMAIN);
			}
		}
	}
	// In case we don't have cookies, fall back to transients. Block based on IP, shorter timeout to keep mem usage low and don't lock out whole companies.
	$transient = get_transient( md5( $storagecookie . $user_ip ) );
	if ( ! $transient ) {
		set_transient( md5( $storagecookie . $user_ip ), array( $entry_id => 1 ), $transient_lifetime );
	} else {
		$count = 1;
		if ( isset( $transient["$entry_id"] ) ) {
			$count = (int) $transient["$entry_id"];
			$count++;
		}
		$transient["$entry_id"] = $count;
		set_transient( md5( $storagecookie . $user_ip ), $transient, $transient_lifetime );
	}

}


/*
 * Set a test cookie, so we have something to see if cookies work.
 *
 * @since 1.2.0
 */
function gwolle_gb_addon_report_add_test_cookie_v2() {
	// Set a cookie now to see if they are supported by the browser.
	// Don't add cookie if it's already set; and don't do it for feeds
	if ( ! isset( $_COOKIE[ TEST_COOKIE ] ) ) {
		@setcookie(TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN);
		if ( SITECOOKIEPATH != COOKIEPATH ) {
			@setcookie(TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN);
		}
	}
}


/*
 * Helper functions to (un)/serialize cookie values.
 *
 * @since 1.2.0
 */
function gwolle_gb_addon_report_serialize_cookie_v2( $value ) {
	$value = gwolle_gb_addon_report_clean_cookie_data_v2( $value );
	return base64_encode( json_encode( $value ) );
}
function gwolle_gb_addon_report_unserialize_cookie_v2( $value ) {
	$data = json_decode( base64_decode( $value ) );
	return gwolle_gb_addon_report_clean_cookie_data_v2( $data );
}
function gwolle_gb_addon_report_clean_cookie_data_v2( $data ) {
	$clean_data = array();

	if ( is_object( $data ) ) {
		// json_decode decided to make an object. Turn it into an array.
		$data = get_object_vars( $data );
	}

	if ( ! is_array( $data ) ) {
		$data = array();
	}

	foreach ( $data as $entry_id => $count ) {
		if ( is_numeric( $entry_id ) && is_numeric( $count ) ) {
			$clean_data["$entry_id"] = $count;
		}
	}
	return $clean_data;
}


/*
 * Check if this comment was flagged by the user before.
 *
 * @param int   entry_id ID of the guestbook entry to check on.
 * @return bool true/false depending on if the entry was already reported by this user.
 *
 * @since 1.2.0
 */
function gwolle_gb_addon_report_already_flagged_v2( $entry_id ) {
	$storagecookie   = 'gbra_flags';
	$no_cookie_grace = 3;

	// Check if cookies are enabled and use cookie store
	if ( isset( $_COOKIE[ TEST_COOKIE ] ) ) {
		if ( isset( $_COOKIE["$storagecookie"] ) ) {
			$data = gwolle_gb_addon_report_unserialize_cookie_v2( $_COOKIE["$storagecookie"] );
			if ( is_array( $data ) && isset( $data["$entry_id"] ) ) {
				return true;
			}
		}
	}

	// In case we don't have cookies, fall back to transients. Block based on IP/User Agent.
	if ( $transient = get_transient( md5( $storagecookie . gwolle_gb_get_user_ip() ) ) ) {
		if (
			// check if no cookie and transient is set
			 ( ! isset( $_COOKIE[ TEST_COOKIE ] ) && isset( $transient["$entry_id"] ) ) ||
			// or check if cookies are enabled and entry is not flagged but transients show a relatively high number and assume fraud.
			 ( isset( $_COOKIE[ TEST_COOKIE ] ) && isset( $transient["$entry_id"] ) && $transient["$entry_id"] >= $no_cookie_grace )
			) {
				return true;
		}
	}
	return false;
}
