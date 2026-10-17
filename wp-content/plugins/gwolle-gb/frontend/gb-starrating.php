<?php
/*
 *
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Starrating Vote in Form.
 *
 * @since 1.0.0
 */
function gwolle_gb_addon_form_starrating_v2( $output ) {
	$gwolle_gb_errors   = gwolle_gb_get_errors();
	$gwolle_gb_formdata = gwolle_gb_get_formdata();

	if (get_option( 'gwolle_gb_addon-starrating', 'false') === 'true') {
		$label = apply_filters( 'gwolle_gb_addon_starrating_label', esc_html__('Rating', 'gwolle-gb') );

		// Only show old data when there are errors.
		$value = 5;
		if ( $gwolle_gb_errors ) {
			if ( is_array($gwolle_gb_formdata) && ! empty($gwolle_gb_formdata) ) {
				if ( isset($gwolle_gb_formdata['gwolle_gb_addon_starrating']) ) {
					$value = (int) ( $gwolle_gb_formdata['gwolle_gb_addon_starrating'] );
				}
			}
		}
		$field_id = gwolle_gb_get_field_id( 'gwolle_gb_addon_starrating' );

		$output .= '
			<div class="gwolle-gb-starrating">
				<div class="label">
					<label for="' . esc_attr( $field_id ) . '" class="text-info">' . esc_html( $label ) . '
					</label>
				</div>
				<div class="input rateit" data-rateit-value="' . esc_attr( $value ) . '" data-rateit-step="1" data-rateit-ispreset="true">
					<input class="wp-exclude-emoji gwolle_gb_addon_starrating" value="' . esc_attr( $value ) . '" type="hidden" id="' . $field_id . '" name="gwolle_gb_addon_starrating" />
				</div>
				<div class="clearBoth">&nbsp;</div>
			</div>';
	}
	return $output;
}
add_filter( 'gwolle_gb_write_add_before', 'gwolle_gb_addon_form_starrating_v2', 15, 2 );


/*
 * Save Star Rating as meta field.
 *
 * @since 1.0.0
 */
function gwolle_gb_addon_save_entry_frontend_starrating_v2( $entry ) {
	if (get_option( 'gwolle_gb_addon-starrating', 'false') === 'true') {
		if (isset($_POST['gwolle_gb_addon_starrating'])) {
			$gwolle_gb_starrating = (int) $_POST['gwolle_gb_addon_starrating'];
			gwolle_gb_add_formdata( 'gwolle_gb_addon_starrating', $gwolle_gb_starrating );

			$return = gwolle_gb_addon_save_meta_v2( $entry->get_id(), 'starrating', $gwolle_gb_starrating );
		}
	}
}
add_action( 'gwolle_gb_save_entry_frontend', 'gwolle_gb_addon_save_entry_frontend_starrating_v2' );


/*
 * Return Star Rating as meta field to the form if submit failed.
 *
 * @since 2.0.0
 */
function gwolle_gb_addon_notsaved_entry_frontend_starrating_v2( $entry ) {
	if (get_option( 'gwolle_gb_addon-starrating', 'false') === 'true') {
		if (isset($_POST['gwolle_gb_addon_starrating'])) {
			$gwolle_gb_starrating = (int) $_POST['gwolle_gb_addon_starrating'];
			gwolle_gb_add_formdata( 'gwolle_gb_addon_starrating', $gwolle_gb_starrating );
		}
	}
}
add_action( 'gwolle_gb_notsaved_entry_frontend', 'gwolle_gb_addon_notsaved_entry_frontend_starrating_v2' );


/*
 * Starrating Result above content.
 *
 * @since 1.0.7
 */
function gwolle_gb_entry_read_add_content_before_starrating_v2( $gb_metabox, $entry ) {
	if (get_option( 'gwolle_gb_addon-starrating', 'false') === 'true') {
		if ( get_option( 'gwolle_gb_addon-starrating_loc', 2 ) == 0 ) {
			$meta = gwolle_gb_addon_get_meta_for_preview_v2( $entry->get_id(), 'starrating' );
			if ( ! $meta ) {
				$meta = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'starrating' );
			}

			if ( $meta != false ) {
				$gb_metabox .= '
						<div class="gb-metabox-content gb-metabox-content-starrating">
							<div class="rateit" data-rateit-value="' . (int) $meta . '" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
						</div>';
			}
		}
	}
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_read_add_content_before', 'gwolle_gb_entry_read_add_content_before_starrating_v2', 8, 2 );


/*
 * Starrating Result under content.
 *
 * @since 1.0.7
 */
function gwolle_gb_entry_read_add_content_starrating_v2( $gb_metabox, $entry ) {
	if (get_option( 'gwolle_gb_addon-starrating', 'false') === 'true') {
		if ( get_option( 'gwolle_gb_addon-starrating_loc', 2 ) == 1 ) {
			$meta = gwolle_gb_addon_get_meta_for_preview_v2( $entry->get_id(), 'starrating' );
			if ( ! $meta ) {
				$meta = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'starrating' );
			}

			if ( $meta != false ) {
				$gb_metabox .= '
						<div class="gb-metabox-content gb-metabox-content-starrating">
							<div class="rateit" data-rateit-value="' . (int) $meta . '" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
						</div>';
			}
		}
	}
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_read_add_content', 'gwolle_gb_entry_read_add_content_starrating_v2', 8, 2 );


/*
 * Starrating Result in top position in metabox.
 *
 * @since 1.0.0
 */
function gwolle_gb_entry_metabox_lines_starrating_v2( $gb_metabox, $entry ) {
	if (get_option( 'gwolle_gb_addon-starrating', 'false') === 'true') {
		if ( get_option( 'gwolle_gb_addon-starrating_loc', 2 ) == 2 ) {
			$meta = gwolle_gb_addon_get_meta_for_preview_v2( $entry->get_id(), 'starrating' );
			if ( ! $meta ) {
				$meta = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'starrating' );
			}

			if ( $meta != false ) {
				$gb_metabox .= '
						<div class="gb-metabox-line gb-metabox-line-starrating">
							<div class="rateit" data-rateit-value="' . (int) $meta . '" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
						</div>';
			}
		}
	}
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_metabox_lines', 'gwolle_gb_entry_metabox_lines_starrating_v2', 8, 2 );


/*
 * Starrating Result in author info heading, at the end.
 *
 * @since 2.6.0
 */
function gwolle_gb_entry_read_author_info_after_starrating_v2( $html, $entry ) {
	if (get_option( 'gwolle_gb_addon-starrating', 'false') === 'true') {
		if ( get_option( 'gwolle_gb_addon-starrating_loc', 2 ) == 4 ) {
			$meta = gwolle_gb_addon_get_meta_for_preview_v2( $entry->get_id(), 'starrating' );
			if ( ! $meta ) {
				$meta = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'starrating' );
			}

			if ( $meta != false ) {
				$html .= '
						&nbsp;<span class="gb-metabox-author-info gb-metabox-author-info-starrating">
							<div class="rateit" data-rateit-value="' . (int) $meta . '" data-rateit-ispreset="true" data-rateit-readonly="true"></div>
						</span>';
			}
		}
	}
	return $html;
}
add_filter( 'gwolle_gb_entry_read_author_info_after', 'gwolle_gb_entry_read_author_info_after_starrating_v2', 8, 2 );
