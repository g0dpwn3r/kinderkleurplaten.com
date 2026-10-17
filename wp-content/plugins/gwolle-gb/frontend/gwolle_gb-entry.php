<?php
/*
 * File: gwolle_gb-entry.php
 * Template with function: gwolle_gb_entry_template()
 *
 * By default this file will be loaded from /wp-content/plugins/gwolle-gb-frontend/gwolle_gb-entry.php.
 * If you place it in your child theme or parent theme, that will override it.
 * Make sure you only return values, and not to use echo statements.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



if ( ! function_exists('gwolle_gb_entry_template') ) {
	/*
	 * Template file for a single guestbook entry.
	 * Is used in lists as well.
	 *
	 * @param object $entry instance of gwolle_gb_entry.
	 * @param bool $first true if it is the first entry.
	 * @param int $counter the number of the entry. (since 1.4.7)
	 * @return: string, html with a single guestbook entry.
	 */
	function gwolle_gb_entry_template( $entry, $first, $counter ) {

		// Get the needed settings.
		$form_setting = gwolle_gb_get_setting( 'form' );
		$read_setting = gwolle_gb_get_setting( 'read' );

		// Main Author div
		$entry_output = '';

		$entry_class  = 'gb-entry';
		$entry_class .= ' gb-entry_' . $entry->get_id();
		$entry_class .= ' gb-entry-count_' . $counter;
		if ( is_int( $counter / 2 ) ) {
			$entry_class .= ' gwolle-gb-even';
		} else {
			$entry_class .= ' gwolle-gb-uneven';
		}
		if ( $first === true ) {
			$entry_class .= ' gwolle-gb-first';
		}

		if ( get_option( 'gwolle_gb-admin_style', 'false' ) === 'true' ) {
			$author_id = $entry->get_author_id();
			$is_moderator = gwolle_gb_is_moderator( $author_id );
			if ( $is_moderator ) {
				$entry_class .= ' admin-entry';
			}
		}
		$entry_class = apply_filters( 'gwolle_gb_entry_class', $entry_class );

		$entry_output .= '<div class="' . $entry_class . '" id="gb-entry_' . $entry->get_id() . '" data-entry_id="' . (int) $entry->get_id() . '">';
		$entry_output .= '
				<article>';

		// Use this filter to just add something
		$entry_output .= apply_filters( 'gwolle_gb_entry_read_add_before', '', $entry );

		// Author Info
		$entry_output .= '
					<div class="gb-author-info">';
		$entry_output .= apply_filters( 'gwolle_gb_entry_read_author_info_before', '', $entry );

		// Author Avatar
		if ( isset($read_setting['read_avatar']) && $read_setting['read_avatar'] === 'true' ) {
			$avatar = get_avatar( $entry->get_author_email(), 32, '', $entry->get_author_name() );
			if ($avatar) {
				$avatar = str_replace('<img', '<img referrerpolicy="no-referrer"', $avatar);
				$entry_output .= '
						<span class="gb-author-avatar">' . $avatar . '</span>';
			}
		}

		// Author Name
		$author_name_html = '';
		$show_name = false;
		if ( isset($read_setting['read_name']) && $read_setting['read_name'] === 'true' ) {
			$show_name = true;
			$author_name_html = gwolle_gb_get_author_name_html($entry);
			$author_name_html = '
						<span class="gb-author-name">' . $author_name_html . '
						</span>';
		}

		// Author Origin
		$author_origin_html = '';
		$show_origin = false;
		if ( isset($read_setting['read_city']) && $read_setting['read_city'] === 'true' ) {
			$author_origin_html = $entry->get_author_origin();
			if ( strlen( str_replace(' ', '', $author_origin_html) ) > 0 ) {
				$show_origin = true;
				$author_origin_html = '
						<span class="gb-author-origin">
							<span class="gb-author-origin-text"> ' . gwolle_gb_sanitize_output($author_origin_html) . '</span>
						</span>';
			}
		}

		// Merge author name and origin for simpler and less code.
		if ( ( $show_name === true ) && ( $show_origin === true ) ) {
			/* translators: entry heading with author info, %1$s is the author name and %2$s is the origin or city of that author */
			$entry_output .= sprintf( esc_html__( '%1$s from %2$s', 'gwolle-gb' ), $author_name_html, $author_origin_html );
		} else if ( ( $show_name === true ) && ( $show_origin !== true ) ) {
			$entry_output .= $author_name_html;
		} else if ( ( $show_name !== true ) && ( $show_origin === true ) ) {
			$entry_output .= $author_origin_html;
		}

		// Entry Date and Time
		$date_html = '';
		$time_html = '';
		$show_time = false;
		$show_date = false;
		if ( ( isset($read_setting['read_datetime']) && $read_setting['read_datetime'] === 'true' ) || ( isset($read_setting['read_date']) && $read_setting['read_date'] === 'true' ) ) {
			$show_date = true;
			$date_html .= '
						<span class="gb-datetime">
							<span class="gb-date">
								<span class="gb-date-text"> ' . date_i18n( get_option('date_format'), $entry->get_datetime() ) . '</span>
							</span>';
			if ( isset($read_setting['read_datetime']) && $read_setting['read_datetime'] === 'true' ) {
				$show_time = true;
				$time_html .= '
							<span class="gb-time">
								<span class="gb-time-text"> ' . trim(date_i18n( get_option('time_format'), $entry->get_datetime() )) . '</span>
							</span>';
			}
		}

		if ( ( $show_name === true ) || ( $show_origin === true ) ) {
			if ( $show_date === true ) {
				$entry_output .= '<br />';
			}
		}

		if ( $show_time === true ) {
			/* translators: entry heading with date and time, %1$s is the date of the entry, %2$s the time of the entry */
			$entry_output .= sprintf( esc_html__( '%1$s at %2$s', 'gwolle-gb' ), $date_html, $time_html ) . '</span>';
		} else if ( $show_date === true ) {
			$entry_output .= $date_html . '</span>';
		}

		$entry_output .= apply_filters( 'gwolle_gb_entry_read_author_info_after', '', $entry );
		$entry_output .= '
					</div>'; // end <div class="gb-author-info">


		// Main Content
		if ( isset($read_setting['read_content']) && $read_setting['read_content'] === 'true' ) {
			$entry_output .= '
					<div class="gb-entry-content">';

			// Use this filter to just add something.
			$entry_output .= apply_filters( 'gwolle_gb_entry_read_add_content_before', '', $entry );

			$real_content = gwolle_gb_sanitize_output( $entry->get_content(), 'content' );
			// This filters the real content of the entry.
			$entry_content = apply_filters( 'gwolle_gb_entry_the_content', $real_content, $entry );

			if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
				$entry_content = nl2br($entry_content);
			}
			if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled'] === 'true' ) {
				$entry_content = gwolle_gb_bbcode_parse($entry_content);
			} else {
				$entry_content = gwolle_gb_bbcode_strip($entry_content);
			}
			$excerpt_length = (int) get_option( 'gwolle_gb-excerpt_length', 0 );
			if ( $excerpt_length > 0 ) {
				$readmore = '... <a href="#" class="gwolle-gb-readmore" title="' . esc_attr__('Expand this entry and read more', 'gwolle-gb') . '">' . esc_html__('Read more', 'gwolle-gb') . '</a>';
				$readless = '... <a href="#" class="gwolle-gb-readless" title="' . esc_attr__('Collapse this entry again', 'gwolle-gb') . '">' . esc_html__('Collapse', 'gwolle-gb') . '</a>';
				$entry_excerpt = wp_trim_words( $entry_content, $excerpt_length, $readmore );
				$entry_content = '
						<div class="gb-entry-excerpt">' . $entry_excerpt . '</div>
						<div class="gb-entry-full-content gwolle-gb-hide">' . $entry_content . $readless . '</div>';
			}
			if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
				// should be done after wp_trim_words to keep all the smileys intact.
				$entry_content = convert_smilies($entry_content);
			}
			$entry_output .= $entry_content;

			// Use this filter to just add something
			$entry_output .= apply_filters( 'gwolle_gb_entry_read_add_content', '', $entry );

			$entry_output .= '
					</div>';

			/* Admin Reply */
			$admin_reply_content = gwolle_gb_sanitize_output( $entry->get_admin_reply(), 'admin_reply' );
			// This filters the real content of the admin reply.
			$admin_reply_content = apply_filters( 'gwolle_gb_entry_the_admin_reply', $admin_reply_content, $entry );

			if ( $admin_reply_content !== '' ) {

				$class = '';
				if ( get_option( 'gwolle_gb-admin_style', 'false' ) === 'true' ) {
					$class = ' admin-entry';
				}

				$admin_reply = '
					<div class="gb-entry-admin_reply' . $class . '">';

				/* Admin Reply Author */
				$admin_reply .= '
						<div class="gb-admin-reply-uid">';
				$admin_reply_name = gwolle_gb_is_moderator( $entry->get_admin_reply_uid() );
				/* Admin Avatar */
				if ( isset($read_setting['read_aavatar']) && $read_setting['read_aavatar'] === 'true' ) {
					$user_info = get_userdata( $entry->get_admin_reply_uid() );
					if ( is_object($user_info) ) {
						$admin_reply_email = $user_info->user_email;
						$avatar = get_avatar( $admin_reply_email, 32, '', $admin_reply_name );
						if ($avatar) {
							$admin_reply .= '
								<span class="gb-admin-avatar">' . $avatar . '</span>';
						}
					}
				}
				/* Admin Header */
				if ( isset($read_setting['read_name']) && $read_setting['read_name'] === 'true' && $admin_reply_name ) {
					$admin_reply_header = '
							<em>' . esc_html__('Admin Reply by:', 'gwolle-gb') . ' ' . $admin_reply_name . '</em>';
				} else {
					$admin_reply_header = '
							<em>' . esc_html__('Admin Reply:', 'gwolle-gb') . '</em>';
				}
				$admin_reply .= apply_filters( 'gwolle_gb_admin_reply_header', $admin_reply_header, $entry );
				$admin_reply .= '
						</div> ';

				/* Admin Reply Content */
				if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
					$admin_reply_content = convert_smilies($admin_reply_content);
				}
				if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
					$admin_reply_content = nl2br($admin_reply_content);
				}
				if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled'] === 'true' ) {
					$admin_reply_content = gwolle_gb_bbcode_parse($admin_reply_content);
				} else {
					$admin_reply_content = gwolle_gb_bbcode_strip($admin_reply_content);
				}
				if ( $excerpt_length > 0 ) {
					$readmore = '... <a href="#" class="gwolle-gb-readmore-admin_reply" title="' . esc_attr__('Expand this admin reply and read more', 'gwolle-gb') . '">' . esc_html__('Read more', 'gwolle-gb') . '</a>';
					$readless = '... <a href="#" class="gwolle-gb-readless-admin_reply" title="' . esc_attr__('Collapse this admin reply again', 'gwolle-gb') . '">' . esc_html__('Collapse', 'gwolle-gb') . '</a>';

					$admin_reply_excerpt = wp_trim_words( $admin_reply_content, $excerpt_length, $readmore );
					$admin_reply .= '
						<div class="gb-admin_reply-excerpt gb-admin-reply-excerpt">' . $admin_reply_excerpt . '</div>
						<div class="gb-admin_reply-full-content gb-admin-reply-full-content gwolle-gb-hide">
						' . $admin_reply_content . $readless . '
						</div>';
				} else {
					$admin_reply .= '
						<div class="gb-admin-reply-content">
						' . $admin_reply_content . '
						</div>';
				}
				$admin_reply .= '
					</div>';

				$entry_output .= $admin_reply;
			}
		}

		/* Metabox for entry with more metabox-lines with information or actions.
		 * For the handle we use a div with a tabindex=0 instead of a button.
		 * Button elements are not easy to style on the frontend for every theme out there.
		 * With Javascript we toggle the metabox open or closed.
		 */
		$gb_metabox = apply_filters( 'gwolle_gb_entry_metabox_lines', '', $entry );
		if ( $gb_metabox ) {
			$entry_output .= '
					<button type="button" class="gb-metabox-handle" tabindex="0" aria-expanded="false">' . esc_html__('...', 'gwolle-gb' ) . '<span class="screen-reader-text"> ' . esc_html__('Toggle this metabox.', 'gwolle-gb') . '</span></button>
					<div class="gb-metabox">' .
						$gb_metabox . '
					</div>';
		}

		/* Use this filter to just add something. */
		$entry_output .= apply_filters( 'gwolle_gb_entry_read_add_after', '', $entry );

		$entry_output .= '
				</article>';

		$entry_output .= '
			</div>
			';

		return $entry_output;
	}
}


/*
 * Deprecated copy of gwolle_gb_entry_template()
 * Is used only for keeping translation strings in GlotPress, should someone use a custom template.
 *
 */
function gwolle_gb_entry_template_deprecated_translations() {

	/* translators: city or origin (deprecated) */
	esc_html__('from', 'gwolle-gb');

	/* translators: on a certain date (deprecated) */
	esc_html__('wrote on', 'gwolle-gb');

	/* translators: at a certain time (deprecated) */
	esc_html__('at', 'gwolle-gb');

}

