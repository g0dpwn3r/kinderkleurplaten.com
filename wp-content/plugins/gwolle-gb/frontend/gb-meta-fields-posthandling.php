<?php
/**
 * Metadata Frontend
 *
 * Functions for retrieving and manipulating metadata.
 * Metadata for an entry is represented by a simple key-value pair.
 * Entries may contain multiple metafields.
 *
 * @package Gwolle-GB-AddOn
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Save list of meta fields after submit and save.
 *
 * @since 1.0.0
 *
 * @param object $entry the guestbook entry the metadata belongs to.
 *
 * @return none.
 */
function gwolle_gb_addon_save_entry_frontend_meta_v2( $entry ) {
	$fields = gwolle_gb_addon_get_meta_fields_all_v2();
	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$slug = 'gwolle_gb_addon_' . $field['slug'];
			if ( isset($_POST["$slug"]) && strlen($_POST["$slug"]) > 0 ) {
				$type = 'text';
				$slug = 'gwolle_gb_addon_' . $field['slug'];

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

				if ( $type == 'checkbox' ) {
					if ( isset($_POST["$slug"]) && $_POST["$slug"] == 'on' ) {
						$gwolle_gb_addon_meta = esc_html__('Yes', 'gwolle-gb');
						gwolle_gb_add_formdata( $slug, 'on' );
					}
				} else if ( $type == 'radio' ) {
					if ( isset($_POST["$slug"]) && is_numeric($_POST["$slug"]) ) {
						$key = (int) $_POST["$slug"];
						$gwolle_gb_addon_meta = $options[$key];
						gwolle_gb_add_formdata( $slug, $key );
					}
				} else if ( $type == 'select' ) {
					if ( isset($_POST["$slug"]) && is_numeric($_POST["$slug"]) ) {
						$key = (int) $_POST["$slug"];
						if ( $key > 0 ) {
							$realkey = $key - 1; // Frontend starts counting at 1, 0 means nothing was selected.
							$gwolle_gb_addon_meta = $options["$realkey"];
							gwolle_gb_add_formdata( $slug, $key );
						} else {
							$gwolle_gb_addon_meta = ''; // Nothing selected.
						}
					}
				} else {
					$gwolle_gb_addon_meta = (string) $_POST["$slug"];
					gwolle_gb_add_formdata( $slug, $gwolle_gb_addon_meta );
				}

				$returnvalue = gwolle_gb_addon_save_meta_v2( $entry->get_id(), $field['slug'], $gwolle_gb_addon_meta );
			}
		}
	}
}
add_action( 'gwolle_gb_save_entry_frontend', 'gwolle_gb_addon_save_entry_frontend_meta_v2' );


/*
 * Return list of meta fields after submit but no saving so the form can be populated again.
 *
 * @since 1.0.0
 *
 * @param object $entry the guestbook entry the metadata belongs to.
 *
 * @return none.
 */
function gwolle_gb_addon_notsaved_entry_frontend_meta_v2( $entry ) {
	$fields = gwolle_gb_addon_get_meta_fields_all_v2();
	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$slug = 'gwolle_gb_addon_' . $field['slug'];
			if ( isset($_POST["$slug"]) && strlen($_POST["$slug"]) > 0 ) {
				$type = 'text';
				$options = array();
				$slug = 'gwolle_gb_addon_' . $field['slug'];

				if ( isset( $field['type'] ) ) {
					$types = array( 'text', 'checkbox', 'radio', 'select', 'textarea' );
					if ( in_array( $field['type'], $types ) ) {
						$type = $field['type'];
						if ( isset( $field['options'] ) ) {
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
				}

				if ( $type == 'checkbox' ) {
					if ( isset($_POST["$slug"]) && $_POST["$slug"] == 'on' ) {
						gwolle_gb_add_formdata( $slug, 'on' );
					}
				} else if ( $type == 'radio' || $type == 'select' ) {
					if ( isset($_POST["$slug"]) && is_numeric($_POST["$slug"]) ) {
						$key = (int) $_POST["$slug"];
						gwolle_gb_add_formdata( $slug, $key );
					}
				} else {
					$gwolle_gb_addon_meta = (string) $_POST["$slug"];
					gwolle_gb_add_formdata( $slug, $gwolle_gb_addon_meta );
				}

			}
		}
	}
}
add_action( 'gwolle_gb_notsaved_entry_frontend', 'gwolle_gb_addon_notsaved_entry_frontend_meta_v2' );


/*
 * Check if required meta field is filled in, if not, set a fatal error.
 *
 * @since 1.6.0
 *
 * @param object $entry the guestbook entry the metadata belongs to.
 *
 * @return object $entry the guestbook entry the metadata belongs to.
 */
function gwolle_gb_addon_check_required_meta_v2( $entry ) {

	$fields = gwolle_gb_addon_get_meta_fields_all_v2();

	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) || ! isset( $field['required']) ) {
				continue;
			}
			if ( (int) $field['required'] != 1 ) {
				continue;
			}

			$slug = 'gwolle_gb_addon_' . $field['slug'];
			$name = $field['name'];
			if ( ! isset($field['type']) ) {
				$field['type'] = 'text';
			}

			if ( ! isset($_POST["$slug"]) || strlen($_POST["$slug"]) == 0 ) {
				/* translators: %s is the name of the meta field */
				$error_message = sprintf( esc_html__('The %s field was not filled in, even though it is mandatory.', 'gwolle-gb'), $name );
				gwolle_gb_add_message( '<p class="error_fields gb-error-meta-required ' . $slug . '"><strong>' . $error_message . '</strong></p>', true, $slug); // mandatory
			}
			if ( $field['type'] == 'select' && isset($_POST["$slug"]) && $_POST["$slug"] == 0 ) {
				$error_message = sprintf( esc_html__('The %s field was not filled in, even though it is mandatory.', 'gwolle-gb'), $name );
				gwolle_gb_add_message( '<p class="error_fields gb-error-meta-required ' . $slug . '"><strong>' . $error_message . '</strong></p>', true, $slug); // mandatory
			}
		}
	}

	return $entry;

}
add_filter( 'gwolle_gb_new_entry_frontend', 'gwolle_gb_addon_check_required_meta_v2' );
