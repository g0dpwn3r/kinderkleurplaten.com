<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Social Media above content.
 * Not adding this metabox line on preview or ajax for security reasons, do not accept permalink in $_POST.
 *
 * @since 1.0.7
 */
function gwolle_gb_entry_read_add_content_before_social_media_v2( $gb_metabox, $entry ) {

	if ( get_option( 'gwolle_gb_addon-social_media', 'false' ) === 'true' ) {
		if ( get_option( 'gwolle_gb_addon-social_media_loc', 2 ) == 0 ) {
			$permalink = gwolle_gb_get_permalink(get_the_ID());

			// Do not test if we are on an AJAX request, if on AJAX, do not add this metabox line.
			if ( $permalink ) {

				$permalink = add_query_arg( 'entry_id', $entry->get_id(), $permalink );

				$gb_metabox .= '
						<div class="gb-metabox-content gb-social-media-share">';
				$services = gwolle_gb_addon_get_social_media_v2();
				foreach ( $services as $service ) {
					$gb_metabox .= gwolle_gb_entry_social_media_service_v2( $service, $permalink );
				}
				$gb_metabox .= '
							<div class="clear"></div>
						</div>';
			}
		}
	}
	return $gb_metabox;

}
add_filter( 'gwolle_gb_entry_read_add_content_before', 'gwolle_gb_entry_read_add_content_before_social_media_v2', 9, 2 );


/*
 * Social Media under content.
 * Not adding this metabox line on preview or ajax for security reasons, do not accept permalink in $_POST.
 *
 * @since 1.0.7
 */
function gwolle_gb_entry_read_add_content_social_media_v2( $gb_metabox, $entry ) {

	if ( get_option( 'gwolle_gb_addon-social_media', 'false' ) === 'true' ) {
		if ( get_option( 'gwolle_gb_addon-social_media_loc', 2 ) == 1 ) {
			$permalink = gwolle_gb_get_permalink(get_the_ID());

			// Do not test if we are on an AJAX request, if on AJAX, do not add this metabox line.
			if ( $permalink ) {

				$permalink = add_query_arg( 'entry_id', $entry->get_id(), $permalink );

				$gb_metabox .= '
						<div class="gb-metabox-content gb-social-media-share">';
				$services = gwolle_gb_addon_get_social_media_v2();
				foreach ( $services as $service ) {
					$gb_metabox .= gwolle_gb_entry_social_media_service_v2( $service, $permalink );
				}
				$gb_metabox .= '
							<div class="clear"></div>
						</div>';
			}
		}
	}
	return $gb_metabox;

}
add_filter( 'gwolle_gb_entry_read_add_content', 'gwolle_gb_entry_read_add_content_social_media_v2', 9, 2 );


/*
 * Social Media in the Metabox.
 * Not adding this metabox line on preview or ajax for security reasons, do not accept permalink in $_POST.
 *
 * @since 1.0.0
 */
function gwolle_gb_entry_metabox_lines_social_media_v2( $gb_metabox, $entry ) {

	if ( get_option( 'gwolle_gb_addon-social_media', 'false' ) === 'true' ) {
		if ( get_option( 'gwolle_gb_addon-social_media_loc', 2 ) == 2 ) {
			$permalink = gwolle_gb_get_permalink(get_the_ID());

			// Do not test if we are on an AJAX request, if on AJAX, do not add this metabox line.
			if ( $permalink ) {

				$permalink = add_query_arg( 'entry_id', $entry->get_id(), $permalink );

				$gb_metabox .= '
						<div class="gb-metabox-line gb-social-media-share">';
				$services = gwolle_gb_addon_get_social_media_v2();
				foreach ( $services as $service ) {
					$gb_metabox .= gwolle_gb_entry_social_media_service_v2( $service, $permalink );
				}
				$gb_metabox .= '
							<div class="clear"></div>
						</div>';
			}
		}
	}
	return $gb_metabox;

}
add_filter( 'gwolle_gb_entry_metabox_lines', 'gwolle_gb_entry_metabox_lines_social_media_v2', 20, 2 );


/*
 * Social Media service html.
 *
 * @since 1.5.1
 */
function gwolle_gb_entry_social_media_service_v2( $service, $permalink ) {

	$html = '';
	if ( $service['check'] === 'true' ) {
		$service_url = $service['url'] . rawurlencode($permalink);
		$html .= '
							<a href="' . esc_url( $service_url ) . '" rel="nofollow noopener noreferrer" target="_blank">
								<img src="' . esc_attr( $service['icon'] ) . '" alt="' . esc_attr__('Post on', 'gwolle-gb') . ' ' . esc_attr( $service['name'] ) . '" />
							</a>
							';
	}

	return $html;

}
