<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



/*
 * Use a custom field name for the form fields that are different for each website.
 *
 * @param string field name of the requested field.
 * @return string hashed fieldname or fieldname, prepended with gwolle_gb.
 *
 * @since 2.4.1
 */
function gwolle_gb_get_field_name( $field ) {

	if ( ! in_array( $field, array( 'name', 'city', 'email', 'website', 'honeypot', 'honeypot2', 'nonce', 'custom', 'timeout', 'timeout2' ) ) ) {
		return 'gwolle_gb_' . $field;
	}

	$blog_url = get_option( 'siteurl' );
	// $blog_url = get_bloginfo('wpurl'); // Will be different depending on scheme (http/https).

	$key = 'gwolle_gb_' . $field . '_field_name_' . $blog_url;
	$field_name = wp_hash( $key, 'auth' );
	$field_name = 'gwolle_gb_' . $field_name;

	return $field_name;

}


/*
 * Use a custom and unique field id based on field name plus a counter for the form fields.
 * Labels need to point to an id of the form field, for the 'for' attribute.
 *
 * @param string field name of the requested field.
 * @return string fieldname appended with a counter.
 *
 * @uses static $ids array with counters for each field.
 *
 * @since 4.8.0
 */
function gwolle_gb_get_field_id( $field_name ) {

	static $ids;

	if ( ! is_array( $ids ) ) {
		$ids = array();
	}

	if ( ! isset( $ids["$field_name"] )  ) {
		$ids["$field_name"] = 0;
	}
	$ids["$field_name"]++;

	$field_id = $field_name . '-' . $ids["$field_name"];

	return $field_id;

}
