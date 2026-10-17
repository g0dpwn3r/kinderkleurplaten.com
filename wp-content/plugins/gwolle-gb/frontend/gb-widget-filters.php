<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Add starrating to entries in the widget.
 *
 * @since 1.2.1
 */
function gwolle_gb_addon_entry_widget_add_content_starrating_v2( $content, $entry ) {

	if ( get_option( 'gwolle_gb_addon-starrating', 'false') === 'true' ) {
		$rating = gwolle_gb_addon_get_meta_v2( $entry->get_id(), 'starrating' );

		if ( $rating != false ) {
			$content .= '
								<span class="gb-content-starrating">
									<span class="rateit" data-rateit-value="' . (int) $rating . '" data-rateit-ispreset="true" data-rateit-readonly="true"></span>
								</span>';
		}
	}

	return $content;

}
add_filter( 'gwolle_gb_entry_widget_add_after', 'gwolle_gb_addon_entry_widget_add_content_starrating_v2', 11, 2 ); // after content span and meta fields


/*
 * Add meta fields to entries in the widget.
 *
 * @since 2.4.0
 */
function gwolle_gb_addon_entry_widget_add_content_meta_fields_v2( $content, $entry ) {

	$items = gwolle_gb_addon_get_meta_fields_all_v2();
	if ( is_array( $items ) && ! empty( $items ) ) {

		$widget = get_option( 'gwolle_gb_addon-widget', array() );
		if ( is_string( $widget ) ) {
			$widget = maybe_unserialize( $widget );
		}

		foreach ( $items as $item ) {
			if ( ! isset($item['slug']) || ! isset($item['name']) ) {
				continue;
			}

			$item_slug = $item['slug'];
			$item_name = $item['name'];
			$option_widget = 'off';
			if ( isset($widget["$item_slug"]) ) {
				$option_widget = $widget["$item_slug"];
			}

			if ( $option_widget === 'on' ) {
				$meta = gwolle_gb_addon_get_meta_v2( $entry->get_id(), $item_slug );
				if ( strlen( $meta ) > 0 ) {
					$content .= '
								<span class="gb-content-meta_' . esc_attr( $item_slug ) . '">
									<span class="gb-content-meta_' . esc_attr( $item_slug ) . '-name">' . esc_attr( $item_name ) . ': </span>
									<span class="gb-content-meta_' . esc_attr( $item_slug ) . '-content">' . $meta . '</span>
								</span><br />';
				}
			}
		}

	}

	return $content;

}
add_filter( 'gwolle_gb_entry_widget_add_after', 'gwolle_gb_addon_entry_widget_add_content_meta_fields_v2', 10, 2 ); // after content span
