<?php
/**
 * Metadata API
 *
 * Functions for retrieving and manipulating metadata.
 * Metadata for an entry is represented by a simple key-value pair.
 * Entries may contain multiple metafields.
 *
 * @package Gwolle-GB-AddOn
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Give list of meta fields to use.
 * Extended use of type checking, since return value of maybe_unserialize is mixed.
 *
 * @since 1.0.0
 *
 * @param string $request the type of request, which setting to fetch.
 *
 * @return array The list of meta types. Empty array if not found.
 */
function gwolle_gb_addon_get_meta_fields_v2( $request ) {

	$provided = array( 'top', 'name', 'city', 'email', 'website', 'message' );
	if ( in_array( $request, $provided ) ) {
		switch ( $request ) {
			case 'top':
				$setting = get_option( 'gwolle_gb_addon-form_top', array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_string( $setting ) ) {
					$setting = array( $setting );
				}
				if ( is_bool( $setting ) ) {
					$setting = array();
				}
				return $setting;

			case 'name':
				$setting = get_option( 'gwolle_gb_addon-form_name', array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_string( $setting ) ) {
					$setting = array( $setting );
				}
				if ( is_bool( $setting ) ) {
					$setting = array();
				}
				return $setting;

			case 'city':
				$setting = get_option( 'gwolle_gb_addon-form_city', array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_string( $setting ) ) {
					$setting = array( $setting );
				}
				if ( is_bool( $setting ) ) {
					$setting = array();
				}
				return $setting;

			case 'email':
				$setting = get_option( 'gwolle_gb_addon-form_email', array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_string( $setting ) ) {
					$setting = array( $setting );
				}
				if ( is_bool( $setting ) ) {
					$setting = array();
				}
				return $setting;

			case 'website':
				$setting = get_option( 'gwolle_gb_addon-form_website', array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_string( $setting ) ) {
					$setting = array( $setting );
				}
				if ( is_bool( $setting ) ) {
					$setting = array();
				}
				return $setting;

			case 'message':
				$setting = get_option( 'gwolle_gb_addon-form_message', array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_string( $setting ) ) {
					$setting = array( $setting );
				}
				if ( is_bool( $setting ) ) {
					$setting = array();
				}
				return $setting;

			default:
				return array();
		}
	}
	return array();
}


/*
 * Give full list of meta fields to use.
 *
 * @since 1.0.0
 *
 * @return array The list of meta types. Empty if not found.
 *
 */
function gwolle_gb_addon_get_meta_fields_all_v2() {
	$fields_top     = gwolle_gb_addon_get_meta_fields_v2( 'top' );
	$fields_name    = gwolle_gb_addon_get_meta_fields_v2( 'name' );
	$fields_city    = gwolle_gb_addon_get_meta_fields_v2( 'city' );
	$fields_email   = gwolle_gb_addon_get_meta_fields_v2( 'email' );
	$fields_website = gwolle_gb_addon_get_meta_fields_v2( 'website' );
	$fields_message = gwolle_gb_addon_get_meta_fields_v2( 'message' );

	$fields = array_merge( $fields_top, $fields_name, $fields_city, $fields_email, $fields_website, $fields_message );

	// cleanup array from misinformation.
	$_fields = array();
	foreach ( $fields as $field ) {
		if ( ! isset($field['slug']) || ! isset($field['name']) ) {
			continue;
		}
		$_fields[] = $field;
	}

	return $_fields;
}


/*
 * Save meta for the specified entry.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int    $entry_id   ID of the entry metadata is for
 * @param string $meta_key   Metadata key
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 *
 * @return int|bool Meta ID, false on failure.
 */
function gwolle_gb_addon_save_meta_v2( $entry_id, $meta_key, $meta_value ) {
	global $wpdb;

	if ( ! $meta_key || ! is_numeric( $entry_id ) ) {
		return false;
	}

	$entry_id = absint( $entry_id );
	if ( ! $entry_id ) {
		return false;
	}

	if ( is_array( $meta_value ) ) {
		foreach ( $meta_value as $value ) {
			$value = stripslashes( $value );
			$value = gwolle_gb_sanitize_input( $value );
			if ( function_exists( 'wp_encode_emoji' ) && function_exists( 'mb_convert_encoding' ) ) {
				$value = wp_encode_emoji( $value );
			}
		}
	} else {
		$meta_value = stripslashes( $meta_value );
		$meta_value = gwolle_gb_sanitize_input( $meta_value );
		if ( function_exists( 'wp_encode_emoji' ) && function_exists( 'mb_convert_encoding' ) ) {
			$meta_value = wp_encode_emoji( $meta_value );
		}
	}
	$meta_value = maybe_serialize( $meta_value );
	$meta_id = gwolle_gb_addon_get_meta_id_v2( $entry_id, $meta_key );
	if ( $meta_id ) {
		// meta exists, use UPDATE

		$sql = "
			UPDATE $wpdb->gwolle_gb_meta
			SET
				entry_id = %d,
				meta_key = %s,
				meta_value = %s
			WHERE
				meta_id = %d
			";

		$values = array(
				$entry_id,
				$meta_key,
				$meta_value,
				$meta_id,
			);

		$result = $wpdb->query(
				$wpdb->prepare( $sql, $values )
			);

		return $meta_id;

	} else {
		// meta is new, use INSERT

		$result = $wpdb->query( $wpdb->prepare(
			"
			INSERT INTO $wpdb->gwolle_gb_meta
			(
				`entry_id`,
				`meta_key`,
				`meta_value`
			) VALUES (
				%d,
				%s,
				%s
			)
			",
			array(
				$entry_id,
				$meta_key,
				$meta_value,
			)
		) );

		if ( $result > 0 ) {
			// Meta saved successfully.
			return $wpdb->insert_id;
		}
	}

	return false;
}


/*
 * Retrieve meta ID for the specified entry_id/meta_key.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param int    $entry_id ID of the entry metadata is for.
 * @param string $meta_key  Optional. Metadata key.
 *
 * @return int/bool Meta ID, false if not found.
 */
function gwolle_gb_addon_get_meta_id_v2( $entry_id, $meta_key ) {
	global $wpdb;

	$where = " 1 = %d";
	$values = array(1);

	if ( ! is_numeric($entry_id) ) {
		return false;
	}

	if ((int) $entry_id > 0) {
		$where .= "
			AND
			entry_id = %d";
		$values[] = $entry_id;
	} else {
		return false;
	}

	if ( strlen($meta_key) > 0) {
		$where .= "
			AND
			meta_key = %s";
		$values[] = $meta_key;
	} else {
		return false;
	}

	$tablename = $wpdb->prefix . "gwolle_gb_meta";

	$sql = "
			SELECT
				`meta_id`,
				`entry_id`,
				`meta_key`,
				`meta_value`
			FROM
				" . $tablename . "
			WHERE
				" . $where . "
			;";

	$sql = $wpdb->prepare( $sql, $values );

	$data = $wpdb->get_row( $sql, ARRAY_A );

	if ( isset( $data['meta_id'] ) ) {
		return (int) $data['meta_id'];
	}

	return false;
}


/*
 * Retrieve metadata for the specified entry.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @static gwolle_gb_metas $gwolle_gb_metas Array that keeps the metadata of each entry in memory.
 *
 * @param int    $object_id ID of the entry metadata is for
 * @param string $meta_key  Optional. Metadata key. If not specified, retrieve all metadata for the specified object.
 * @param bool   $flush     Optional, flush the cache, in case you need fresh meta data. Since 2.0.0
 *
 * @return mixed/bool/array Single metadata value. false if not found. Array with meta_fields if no meta_key parameter was given.
 */
function gwolle_gb_addon_get_meta_v2( $entry_id, $meta_key = '', $flush = false ) {
	static $gwolle_gb_metas;
	global $wpdb;

	if ( $flush == true ) {
		$gwolle_gb_metas = array();
	}

	$where = " 1 = %d";
	$values = Array(1);

	if ( ! is_numeric($entry_id) ) {
		return false;
	}

	if ( (int) $entry_id > 0 ) {
		$where .= "
			AND
			entry_id = %d";
		$values[] = (int) $entry_id;
	} else {
		return false;
	}

	if ( empty( $meta_key ) ) {
		// return all meta keys for this entry.
		if ( isset( $gwolle_gb_metas["$entry_id"] ) && is_array( $gwolle_gb_metas["$entry_id"]) ) {
			return $gwolle_gb_metas["$entry_id"];
		}
	}

	// Use static var to fetch all metas first, and return only one meta field.
	if ( isset( $gwolle_gb_metas["$entry_id"] ) && is_array( $gwolle_gb_metas["$entry_id"]) ) {
		foreach ( $gwolle_gb_metas["$entry_id"] as $meta ) {
			if ( $meta_key == $meta['meta_key'] ) {
				if ( isset($meta['meta_value']) ) {
					$meta_value = $meta['meta_value'];
					$meta_value = maybe_unserialize( $meta_value );
					if ( is_array( $meta_value ) ) {
						foreach ( $meta_value as $value ) {
							$value = gwolle_gb_sanitize_output( $value );
						}
					} else {
						$meta_value = gwolle_gb_sanitize_output( $meta_value );
					}
					return $meta_value;
				}
			}
		}
		// entry_id was already fetched, but no meta_value was found. Return false.
		return false;
	}

	// No var set yet, fetch from database.
	$tablename = $wpdb->prefix . "gwolle_gb_meta";

	$sql = "
			SELECT
				`meta_id`,
				`entry_id`,
				`meta_key`,
				`meta_value`
			FROM
				" . $tablename . "
			WHERE
				" . $where . "
			;";

	$sql = $wpdb->prepare( $sql, $values );

	$data = $wpdb->get_results( $sql, ARRAY_A );

	// Set data in the static array.
	if ( isset( $data ) && is_array( $data ) ) {
		$gwolle_gb_metas["$entry_id"] = $data;
	}

	if ( empty( $meta_key ) ) {
		if ( isset( $gwolle_gb_metas["$entry_id"] ) && is_array( $gwolle_gb_metas["$entry_id"]) ) {
			return $gwolle_gb_metas["$entry_id"];
		}
	}

	if ( isset( $gwolle_gb_metas["$entry_id"] ) && is_array( $gwolle_gb_metas["$entry_id"]) ) {
		foreach ( $gwolle_gb_metas["$entry_id"] as $meta ) {
			if ( $meta_key == $meta['meta_key'] ) {
				if ( isset($meta['meta_value']) ) {
					$meta_value = $meta['meta_value'];
					$meta_value = maybe_unserialize( $meta_value );
					if ( is_array( $meta_value ) ) {
						foreach ( $meta_value as $value ) {
							$value = gwolle_gb_sanitize_output( $value );
						}
					} else {
						$meta_value = gwolle_gb_sanitize_output( $meta_value );
					}
					return $meta_value;
				}
			}
		}
	}

	return false;
}


/*
 * Truncate a slug.
 *
 * @since 1.0.0
 *
 * @see utf8_uri_encode()
 *
 * @param string $slug   The slug to truncate.
 *
 * @return string The truncated slug.
 */
function gwolle_gb_addon_truncate_slug_v2( $slug ) {
	$slug = substr( $slug, 0, 100 );
	$slug = utf8_uri_encode( $slug, 100 );
	$slug = remove_accents( $slug);
	$slug = sanitize_file_name( $slug);
	return rtrim( $slug, '-' );
}


/*
 * gwolle_gb_addon_del_entry_meta()
 * Delete the meta fields for a guestbook entry
 *
 * Parameters:
 *   - entry_id: (int) the id of the entry
 *
 * Return: (bool) true or false, depending on succes
 */

function gwolle_gb_addon_del_entry_meta_v2( $entry_id ) {
		global $wpdb;

		$entry_id = (int) $entry_id;

		if ( $entry_id == 0 || $entry_id < 0 ) {
			return false;
		}

		$sql = "
			DELETE
			FROM
				$wpdb->gwolle_gb_meta
			WHERE
				entry_id = %d";

		$values = array(
				$entry_id,
			);

		$result = $wpdb->query(
				$wpdb->prepare( $sql, $values )
			);


		if ( $result > 0 ) {
			return true;
		}
		return false;
}
add_action( 'gwolle_gb_delete_entry', 'gwolle_gb_addon_del_entry_meta_v2' );
