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
 * Add form fields to the selected location in the frontend form.
 *
 * @since 1.0.0
 *
 * @param string $form_html the html for form fields.
 *
 * @return string $form_html the html for form fields.
 */
function gwolle_gb_addon_write_add_before_v2( $form_html ) {
	$fields = gwolle_gb_addon_get_meta_fields_v2( 'top' );

	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$form_html .= gwolle_gb_addon_write_input_v2( $field );
		}
	}
	return $form_html;
}
add_filter( 'gwolle_gb_write_add_before', 'gwolle_gb_addon_write_add_before_v2' );


/*
 * Add form fields to the selected location in the frontend form.
 *
 * @since 1.0.0
 *
 * @param string $form_html the html for form fields.
 *
 * @return string $form_html the html for form fields.
 */
function gwolle_gb_addon_write_add_after_name_v2( $form_html ) {
	$fields = gwolle_gb_addon_get_meta_fields_v2( 'name' );

	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$form_html .= gwolle_gb_addon_write_input_v2( $field );
		}
	}
	return $form_html;
}
add_filter( 'gwolle_gb_write_add_after_name', 'gwolle_gb_addon_write_add_after_name_v2' );


/*
 * Add form fields to the selected location in the frontend form.
 *
 * @since 1.0.0
 *
 * @param string $form_html the html for form fields.
 *
 * @return string $form_html the html for form fields.
 */
function gwolle_gb_addon_write_add_after_origin_v2( $form_html ) {
	$fields = gwolle_gb_addon_get_meta_fields_v2( 'city' );

	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$form_html .= gwolle_gb_addon_write_input_v2( $field );
		}
	}
	return $form_html;
}
add_filter( 'gwolle_gb_write_add_after_origin', 'gwolle_gb_addon_write_add_after_origin_v2' );


/*
 * Add form fields to the selected location in the frontend form.
 *
 * @since 1.0.0
 *
 * @param string $form_html the html for form fields.
 *
 * @return string $form_html the html for form fields.
 */
function gwolle_gb_addon_write_add_after_email_v2( $form_html ) {
	$fields = gwolle_gb_addon_get_meta_fields_v2( 'email' );

	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$form_html .= gwolle_gb_addon_write_input_v2( $field );
		}
	}
	return $form_html;
}
add_filter( 'gwolle_gb_write_add_after_email', 'gwolle_gb_addon_write_add_after_email_v2' );


/*
 * Add form fields to the selected location in the frontend form.
 *
 * @since 1.0.0
 *
 * @param string $form_html the html for form fields.
 *
 * @return string $form_html the html for form fields.
 */
function gwolle_gb_addon_write_add_after_website_v2( $form_html ) {
	$fields = gwolle_gb_addon_get_meta_fields_v2( 'website' );

	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$form_html .= gwolle_gb_addon_write_input_v2( $field );
		}
	}
	return $form_html;
}
add_filter( 'gwolle_gb_write_add_after_website', 'gwolle_gb_addon_write_add_after_website_v2' );


/*
 * Add form fields to the selected location in the frontend form.
 *
 * @since 1.0.0
 *
 * @param string $form_html the html for form fields.
 *
 * @return string $form_html the html for form fields.
 */
function gwolle_gb_addon_write_add_after_content_v2( $form_html ) {
	$fields = gwolle_gb_addon_get_meta_fields_v2( 'message' );

	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$form_html .= gwolle_gb_addon_write_input_v2( $field );
		}
	}
	return $form_html;
}
add_filter( 'gwolle_gb_write_add_after_content', 'gwolle_gb_addon_write_add_after_content_v2' );


/*
 * Add form fields to the selected location in the frontend form.
 *
 * @since 1.0.0
 *
 * @param string $form_html the html for form fields.
 *
 * @return string $form_html the html for form fields.
 */
function gwolle_gb_addon_write_add_after_antispam_v2( $form_html ) {
	$fields = gwolle_gb_addon_get_meta_fields_v2( 'top' );

	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$form_html .= gwolle_gb_addon_write_input_v2( $field );
		}
	}
	return $form_html;
}
//add_filter( 'gwolle_gb_write_add_after_antispam', 'gwolle_gb_addon_write_add_after_antispam_v2' );


/*
 * Generate html with input field for each field data for the frontend form.
 *
 * @since 1.6.0
 *
 * @param array $field data of the form fields.
 *
 * @return string $form_html the html for form field.
 */
function gwolle_gb_addon_write_input_v2( $field ) {
	$gwolle_gb_errors       = gwolle_gb_get_errors();
	$gwolle_gb_error_fields = gwolle_gb_get_error_fields();
	$gwolle_gb_formdata     = gwolle_gb_get_formdata();

	if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
		return '';
	}
	$slug = 'gwolle_gb_addon_' . $field['slug'];
	$fieldname = $field['name'];
	$field_id = gwolle_gb_get_field_id( $slug );

	if ( isset($field['required']) && (int) $field['required'] == 1 ) {
		$asterisk = ' ' . gwolle_gb_wp_required_field_indicator();
		$required = ' required';
	} else {
		$asterisk = '';
		$required = '';
	}

	$error = '';
	$div_error = '';
	if ( in_array($slug, $gwolle_gb_error_fields) ) {
		$error .= ' error';
	}

	// Only show old data when there are errors.
	$value = '';
	if ( $gwolle_gb_errors ) {
		if ( is_array($gwolle_gb_formdata) && ! empty($gwolle_gb_formdata) ) {
			if (isset($gwolle_gb_formdata[$slug])) {
				$value = trim($gwolle_gb_formdata[$slug]);
			}
		}
	}

	if ( isset($field['type']) && $field['type'] == 'checkbox' ) {
		$form_field = '
							<label><input type="checkbox" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $slug ) . '" class="' . esc_attr( $slug ) . $error . '" ' . checked('on', $value, false) . $required . ' /></label>';

	} else if ( isset($field['type']) && $field['type'] == 'radio' && isset($field['options']) ) {
		$options = $field['options'];
		if ( ! empty( $options ) ) {
			$options = explode( "\n", $options );
			$form_field = '';
			$counter = 0;
			foreach ( (array) $options as $option ) {
				$option = trim( $option );
				if ( empty( $option ) ) {
					continue;
				}
				$form_field .= '
							<label><input type="radio" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $slug ) . '" value="' . (int) $counter . '" ' . checked($counter, $value, false) . ' class="' . esc_attr( $slug ) . $error . '" />' . esc_html($option) . '</label><br />';
				$counter++;
			}
			$div_error = $error;
		}

	} else if ( isset($field['type']) && $field['type'] == 'select' ) {
		$options = $field['options'];
		if ( ! empty( $options ) ) {
			$options = explode( "\n", $options );
			$form_field = '
							<select class="' . esc_attr( $slug ) . $error . '" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $slug ) . '"' . $required . '>';
			$counter = 0;
			$form_field .= '
								<option value="' . (int) $counter . '">' . esc_html__('Select...', 'gwolle-gb') . '</option>';
			$counter++;
			foreach ( (array) $options as $option ) {
				$option = trim( $option );
				if ( empty( $option ) ) {
					continue;
				}
				$form_field .= '
								<option value="' . (int) $counter . '" ' . selected($counter, $value, false) . ' >' . esc_html($option) . '</option>';
				$counter++;
			}
			$form_field .= '
							</select>';
			$div_error = $error;
		}

	} else if ( isset($field['type']) && $field['type'] == 'textarea' ) {
		$form_field = '
							<label><textarea id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $slug ) . '" class="' . esc_attr( $slug ) . $error . ' wp-exclude-emoji"'. $required . ' >' . esc_textarea( $value ) . '</textarea></label>';

	} else { // text input.
		$form_field = '
							<label><input value="' . esc_attr( $value ) . '" type="text" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $slug ) . '" class="' . esc_attr( $slug ) . $error . ' wp-exclude-emoji"' . $required . ' /></label>';
	}


	$form_html = '
					<div class="' . esc_attr( $slug ) . '">
						<div class="label">
							<label for="' . esc_attr( $slug ) . '" class="text-info">' . esc_html( $fieldname ) . $asterisk . '</label>
						</div>
						<div class="input' . $div_error . '">' .
							$form_field . '
						</div>
						<div class="clearBoth">&nbsp;</div>
					</div>';

	return $form_html;

}
