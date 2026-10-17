<?php
/**
 * Metadata Frontend
 *
 * @package Gwolle-GB-AddOn
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Give list of meta fields to use.
 *
 * @since 1.0.0
 *
 * @param string $slug the slug of the meta field.
 *
 * @return int the display type.
 *             0: before content
 *             1: after content
 *             2: in metabox (default)
 *             3: none
 */
function gwolle_gb_addon_meta_display_where_v2( $slug ) {
	$reading = get_option( 'gwolle_gb_addon-reading', false );
	if ( is_string( $reading ) ) {
		$reading = maybe_unserialize( $reading );
	}
	if ( isset($reading[$slug]) ) {
		return (int) $reading[$slug];
	}
	return 2;
}


/*
 * Meta fields before Content.
 */
function gwolle_gb_entry_read_add_content_before_meta_v2( $gb_metabox, $entry ) {
	$fields = gwolle_gb_addon_get_meta_fields_all_v2();
	foreach ( $fields as $field ) {
		if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
			continue;
		}
		$display = gwolle_gb_addon_meta_display_where_v2( $field['slug'] );
		if ( $display === 0 ) {
			$meta = gwolle_gb_addon_get_meta_for_preview_v2( $entry->get_id(), $field['slug'] );
			if ( ! $meta ) {
				$meta = gwolle_gb_addon_get_meta_v2( $entry->get_id(), $field['slug'] );
			}
			if ( strlen($meta) > 0 ) {
				$gb_metabox .= '
					<div class="gb-metabox-content gb-metabox-content-meta gb-metabox-content-meta_' . esc_attr( $field['slug'] ) . '">
						<span class="gb-metabox-' . esc_attr( $field['slug'] ) . '-name">' . esc_attr( $field['name'] ) . ': </span><span class="gb-metabox-' . esc_attr( $field['slug'] ) . '-content">' .	$meta .	'</span>
					</div>';
			}
		}
	}
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_read_add_content_before', 'gwolle_gb_entry_read_add_content_before_meta_v2', 10, 2 );


/*
 * Meta fields after Content.
 */
function gwolle_gb_entry_read_add_content_meta_v2( $gb_metabox, $entry ) {
	$fields = gwolle_gb_addon_get_meta_fields_all_v2();
	foreach ( $fields as $field ) {
		if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
			continue;
		}
		$display = gwolle_gb_addon_meta_display_where_v2( $field['slug'] );
		if ( $display === 1 ) {
			$meta = gwolle_gb_addon_get_meta_for_preview_v2( $entry->get_id(), $field['slug'] );
			if ( ! $meta ) {
				$meta = gwolle_gb_addon_get_meta_v2( $entry->get_id(), $field['slug'] );
			}
			if ( strlen($meta) > 0 ) {
				$gb_metabox .= '
					<div class="gb-metabox-content gb-metabox-content-meta gb-metabox-content-meta_' . esc_attr( $field['slug'] ) . '">
						<span class="gb-metabox-' . esc_attr( $field['slug'] ) . '-name">' . esc_attr( $field['name'] ) . ': </span><span class="gb-metabox-' . esc_attr( $field['slug'] ) . '-content">' .	$meta .	'</span>
					</div>';
			}
		}
	}
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_read_add_content', 'gwolle_gb_entry_read_add_content_meta_v2', 10, 2 );


/*
 * Meta fields in Metabox.
 */
function gwolle_gb_entry_metabox_lines_meta_v2( $gb_metabox, $entry ) {
	$fields = gwolle_gb_addon_get_meta_fields_all_v2();
	foreach ( $fields as $field ) {
		if ( ! isset( $field['slug']) || ! isset( $field['name']) ) {
			continue;
		}
		$display = gwolle_gb_addon_meta_display_where_v2( $field['slug'] );
		if ( $display === 2 ) {
			$meta = gwolle_gb_addon_get_meta_for_preview_v2( $entry->get_id(), $field['slug'] );
			if ( ! $meta ) {
				$meta = gwolle_gb_addon_get_meta_v2( $entry->get_id(), $field['slug'] );
			}
			if ( strlen($meta) > 0 ) {
				$gb_metabox .= '
					<div class="gb-metabox-line gb-metabox-line-meta gb-metabox-line-meta_' . esc_attr( $field['slug'] ) . '">
						<span class="gb-metabox-' . esc_attr( $field['slug'] ) . '-name">' . esc_attr( $field['name'] ) . ': </span><span class="gb-metabox-' . esc_attr( $field['slug'] ) . '-content">' .	$meta .	'</span>
					</div>';
			}
		}
	}
	return $gb_metabox;
}
add_filter( 'gwolle_gb_entry_metabox_lines', 'gwolle_gb_entry_metabox_lines_meta_v2', 94, 2 );
