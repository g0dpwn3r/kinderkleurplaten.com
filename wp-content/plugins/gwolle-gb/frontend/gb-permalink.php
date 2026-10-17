<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Permalink for single entry.
 * Not adding this metabox line on preview or ajax for security reasons, do not accept permalink in $_POST.
 */
function gwolle_gb_entry_metabox_lines_permalink_v2( $gb_metabox, $entry ) {

	if (get_option( 'gwolle_gb_addon-permalink', 'true') === 'true') {
		$permalink = gwolle_gb_get_permalink(get_the_ID());

		// Do not test if we are on an AJAX request, if on AJAX, do not add this metabox line.
		if ( $permalink ) {

			$permalink = add_query_arg( 'entry_id', $entry->get_id(), $permalink );

			$gb_metabox .= '
					<div class="gb-metabox-line gb-metabox-line-permalink">
						<a class="gwolle_gb_permalink" href="' . esc_url( $permalink ) . '" title="' . esc_attr__('Permanent link to this single entry', 'gwolle-gb') . '">' . esc_html__('Permalink', 'gwolle-gb') . '</a>
					</div>';
		}
	}
	return $gb_metabox;

}
add_filter( 'gwolle_gb_entry_metabox_lines', 'gwolle_gb_entry_metabox_lines_permalink_v2', 70, 2 );
