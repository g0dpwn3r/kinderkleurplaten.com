<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Add metabox for Meta Fields on the admin editor.
 * Is being called from the main plugin, due to the way metaboxes get added.
 *
 * @since 1.0.0
 */
function gwolle_gb_addon_editor_metabox_meta_v2( $entry ) {
	$output = '';

	if (get_option( 'gwolle_gb_addon-starrating', 'false') === 'true') {
		$name = apply_filters( 'gwolle_gb_addon_starrating_label', __('Rating', 'gwolle-gb') );
		$value = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'starrating' );

		// Enqueue.
		$output .= '
			<div class="gwolle-gb-starrating">
				<div class="label">
					<label for="gwolle_gb_addon_starrating" class="text-info">' . esc_html( $name ) . ':
					</label>
				</div>
				<div class="input rateit" data-rateit-value="' . esc_attr( $value ) . '" data-rateit-step="1" data-rateit-ispreset="true">
					<input class="wp-exclude-emoji gwolle_gb_addon_starrating" value="' . esc_attr( $value ) . '" type="hidden" id="gwolle_gb_addon_starrating" name="gwolle_gb_addon_starrating" />
				</div>
				<div class="clearBoth">&nbsp;</div>
			</div>';
	}

	if (get_option( 'gwolle_gb_addon-likes', 'false') === 'true') {
		$value = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'likes' );
		if ( ! is_array( $value ) ) {
			$likes = 0;
		} else {
			$likes = count( $value );
		}
		$value = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'unlikes' );
		if ( ! is_array( $value ) ) {
			$unlikes = 0;
		} else {
			$unlikes = count( $value );
		}
		$output .= '
						<div class="gwolle-gb-likes">
							<span class="gwolle-gb-like-link">
								<img src="' . GWOLLE_GB_URL . 'assets/like/like.png" style="margin-top:-2px;width:20px;" />
								<span class="gb-likes">&nbsp;' . (int) $likes . '</span>
							</span>&nbsp;&nbsp;&nbsp;
							<span class="gwolle-gb-unlike-link">
								<img src="' . GWOLLE_GB_URL . 'assets/like/unlike.png" style="margin-top:2px;width:20px;" />
								<span class="gb-unlikes">&nbsp;' . (int) $unlikes . '</span>
							</span>
						</div>
						';
	}

	$fields = gwolle_gb_addon_get_meta_fields_all_v2();
	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$slug = 'gwolle_gb_addon_' . $field['slug'];
			$name = $field['name'];
			$value = gwolle_gb_addon_get_meta_v2( $entry->get_id(), $field['slug'] );

			if ( ! isset($field['type']) ) {
				$field['type']    = 'text';
				$field['options'] = '';
			}

			if ( isset($field['type']) && $field['type'] == 'checkbox' ) {
				$check = esc_html__('Yes', 'gwolle-gb');
				$form_field = '
									<label><input type="checkbox" name="' . esc_attr( $slug ) . '" class="' . esc_attr( $slug ) . '" ' . checked($check, $value, false) . ' /></label>';

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
									<label><input type="radio" id="' . esc_attr($slug) . '" name="' . esc_attr($slug) . '" value="' . (int) $counter . '" ' . checked($option, $value, false) . ' class="' . esc_attr( $slug ) . '" />' . esc_html($option) . '</label><br />';
						$counter++;
					}
				}

			} else if ( isset($field['type']) && $field['type'] == 'select' ) {
				$options = $field['options'];
				if ( ! empty( $options ) ) {
					$options = explode( "\n", $options );
					$form_field = '
									<select class="' . esc_attr( $slug ) . '" name="' . esc_attr( $slug ) . '">';
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
										<option value="' . (int) $counter . '" ' . selected($option, $value, false) . ' >' . esc_html($option) . '</option>';
						$counter++;
					}
					$form_field .= '
									</select>';
				}

			} else if ( isset($field['type']) && $field['type'] == 'textarea' ) {
				$form_field = '
									<textarea name="' . esc_attr( $slug ) . '" class="' . esc_attr( $slug ) . ' wp-exclude-emoji">' . esc_textarea( $value ) . '</textarea>';

			} else { // text input.
				$form_field = '
									<input value="' . esc_attr( $value ) . '" type="text" id="' . esc_attr( $slug ) . '" name="' . esc_attr( $slug ) . '" class="' . esc_attr( $slug ) . ' wp-exclude-emoji" />';
			}


			$output .= '
							<div class="' . esc_attr( $slug ) . '">
								<div class="label">
									<label for="' . esc_attr( $slug ) . '" class="text-info">' . esc_html( $name ) . ': </label>
								</div>
								<div class="input">' .
									$form_field . '
								</div>
								<div class="clearBoth">&nbsp;</div>
							</div>';

		}
	} else {
		$output .= '
			<div class="gwolle-gb-no-meta">
				' . esc_html__('There are no Meta Fields saved in settings yet.', 'gwolle-gb') . '
			</div>';
	}

	if (get_option( 'gwolle_gb_addon-report', 'false') === 'true') {
		$reports = (int) gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'report-abuse' );
		$class_positive = 'gwolle-gb-hide';
		$class_negative = 'gwolle-gb-hide';
		if ( $reports == -1 ) {
			$class_negative = '';
		}
		if ( $reports > 0 ) {
			$class_positive = '';
		}
		$output .= '
			<div class="gwolle-gb-report-abuse">
				<span class="gwolle-gb-report-abuse-positive ' . esc_attr( $class_positive ) . '">' . esc_html__('Abuse Reports', 'gwolle-gb') . ': ' . (int) $reports .
					' &nbsp; (<a class="gwolle-gb-report-abuse-uncheck" data-entry-id="' . (int) $entry->get_id() . '">' . esc_html__('Remove and moderate', 'gwolle-gb') . '</a>).
				</span>
			</div>
			<div class="gwolle-gb-report-abuse">
				<span class="gwolle-gb-report-abuse-negative ' . esc_attr( $class_negative ) . '">' . esc_html__('Abuse Reports', 'gwolle-gb') . ': -1
					 &nbsp; (' . esc_html__('Already moderated', 'gwolle-gb') . ').
				</span>
				<div class="clearBoth">&nbsp;</div>
			</div>';
	}

	echo $output;

}


/*
 * Save Meta Fields in the admin editor.
 *
 * @since 1.0.0
 */
function gwolle_gb_addon_save_entry_admin_metabox_v2( $entry ) {
	if (get_option( 'gwolle_gb_addon-starrating', 'false') === 'true') {
		if (isset($_POST['gwolle_gb_addon_starrating'])) {
			$gwolle_gb_starrating = (int) $_POST['gwolle_gb_addon_starrating'];
			$return = gwolle_gb_addon_save_meta_v2( $entry->get_id(), 'starrating', $gwolle_gb_starrating );
		}
	}

	$fields = gwolle_gb_addon_get_meta_fields_all_v2();
	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $field ) {
			if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
				continue;
			}
			$slug = 'gwolle_gb_addon_' . $field['slug'];
			$type = 'text';
			$options = array();

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
					$gwolle_gb_addon_meta = esc_html__('Yes', 'gwolle-gb');
				} else {
					$gwolle_gb_addon_meta = '';
				}
			} else if ( $type == 'radio' ) {
				if ( isset($_POST["$slug"]) && is_numeric($_POST["$slug"]) ) {
					$key = (int) $_POST["$slug"];
					$gwolle_gb_addon_meta = $options[$key];
				}
			} else if ( $type == 'select' ) {
				if ( isset($_POST["$slug"]) && is_numeric($_POST["$slug"]) ) {
					$key = (int) $_POST["$slug"];
					if ( $key > 0 ) {
						$realkey = ( $key - 1 ); // Frontend starts counting at 1, 0 means nothing was selected.
						$gwolle_gb_addon_meta = $options["$realkey"];
						gwolle_gb_add_formdata( $slug, $key );
					} else {
						$gwolle_gb_addon_meta = ''; // Nothing selected.
					}
				}
			} else if ( isset( $_POST["$slug"] ) ) {
				$gwolle_gb_addon_meta = (string) $_POST["$slug"];
			}

			if ( isset( $gwolle_gb_addon_meta ) ) {
				$returnvalue = gwolle_gb_addon_save_meta_v2( $entry->get_id(), $field['slug'], $gwolle_gb_addon_meta );
			}
		}
	}
}
add_action( 'gwolle_gb_save_entry_admin', 'gwolle_gb_addon_save_entry_admin_metabox_v2' );
