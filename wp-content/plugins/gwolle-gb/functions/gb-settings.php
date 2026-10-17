<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



/*
 * Register Settings
 */
function gwolle_gb_register_settings() {
	//                                      option_name                          sanitize                   default value
	register_setting( 'gwolle_gb_options', 'gwolle_gb_addon-moderation_keys',   'wp_kses_post' );        // empty by default, taken from the Add-On since 4.0.4
	register_setting( 'gwolle_gb_options', 'gwolle_gb-admin_style',             'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-adminMailContent',        'wp_kses_post' );        // empty by default
	register_setting( 'gwolle_gb_options', 'gwolle_gb-akismet-active',          'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-antispam-question',       'sanitize_text_field' ); // empty string
	register_setting( 'gwolle_gb_options', 'gwolle_gb-antispam-answer',         'sanitize_text_field' ); // empty string
	register_setting( 'gwolle_gb_options', 'gwolle_gb-authorMailContent',       'wp_kses_post' );        // empty by default
	register_setting( 'gwolle_gb_options', 'gwolle_gb-authormoderationcontent', 'wp_kses_post' );        // empty by default
	register_setting( 'gwolle_gb_options', 'gwolle_gb-entries_per_page',        'intval' );              // 20
	register_setting( 'gwolle_gb_options', 'gwolle_gb-double_entry',            'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-entriesPerPage',          'intval' );              // 20
	register_setting( 'gwolle_gb_options', 'gwolle_gb-excerpt_length',          'intval' );              // 0
	register_setting( 'gwolle_gb_options', 'gwolle_gb-form',                    'gwolle_gb_setting_array_sanitize' ); // serialized array, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-form_ajax',               'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-header',                  'sanitize_text_field' ); // string, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-honeypot',                'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-honeypot_value',          'intval' );              // random 1 - 100
	register_setting( 'gwolle_gb_options', 'gwolle_gb-labels_float',            'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-linkAuthorWebsite',       'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-linkchecker',             'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-longtext',                'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-mail-from',               'sanitize_email' );      // empty string
	register_setting( 'gwolle_gb_options', 'gwolle_gb-mail_admin_replyContent', 'wp_kses_post' );        // empty by default
	register_setting( 'gwolle_gb_options', 'gwolle_gb-mail_author',             'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-mail_author_moderation',  'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-moderate-entries',        'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-navigation',              'intval' );              // 0 or 1, default is 0
	register_setting( 'gwolle_gb_options', 'gwolle_gb-nonce',                   'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-notifyByMail',            'sanitize_text_field' ); // comma separated list of User IDs, initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-notify-with-spam',        'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-notice',                  'sanitize_text_field' ); // string, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-paginate_all',            'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-read',                    'gwolle_gb_setting_array_sanitize' ); // serialized array, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-refuse-spam',             'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-require_login',           'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-sfs',                     'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-store_ip',                'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showEntryIcons',          'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showLineBreaks',          'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showSmilies',             'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-timeout',                 'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb_version',                 'sanitize_text_field' ); // string, mind the underscore
}
add_action( 'admin_init', 'gwolle_gb_register_settings' );


/*
 * Register Settings
 */
function gwolle_gb_addon_register_settings_v2() {
	//                                           option_name                           sanitize                 default value
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-auto_delete',        'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-auto_delete_time',   'intval' );              // 5
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-auto_anonymize',     'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-auto_anonymize_time','intval' );              // 5
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-delete_link',        'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-delete_link_author', 'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-email',              'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-form_top',           'gwolle_gb_setting_array_sanitize' );               // serialized array
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-form_name',          'gwolle_gb_setting_array_sanitize' );               // serialized array
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-form_city',          'gwolle_gb_setting_array_sanitize' );               // serialized array
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-form_email',         'gwolle_gb_setting_array_sanitize' );               // serialized array
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-form_website',       'gwolle_gb_setting_array_sanitize' );               // serialized array
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-form_message',       'gwolle_gb_setting_array_sanitize' );               // serialized array
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-likes',              'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-likes_loc',          'intval' );              // 1
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-permalink',          'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-preview',            'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-reading',            'gwolle_gb_setting_array_sanitize' );               // serialized array
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-report',             'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-sitemap',            'sanitize_text_field' ); // 'true'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-social_media',       'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-social_media_loc',   'intval' );              // 2
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-social_services',    'gwolle_gb_setting_array_sanitize' );               // serialized array
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-starrating',         'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-starrating_avg',     'sanitize_text_field' ); // 'false' or int
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-starrating_loc',     'intval' );              // 2
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-starrating_type',    'sanitize_text_field' ); // ''
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-strings',            'gwolle_gb_setting_array_sanitize' );               // serialized array
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-upload',             'sanitize_text_field' ); // 'false'
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-version',            'sanitize_text_field' ); // string
	register_setting( 'gwolle_gb_addon_options', 'gwolle_gb_addon-widget',             'gwolle_gb_setting_array_sanitize' );               // serialized array
}
add_action( 'admin_init', 'gwolle_gb_addon_register_settings_v2' );


/*
 * Get the setting for Gwolle-GB that is saved as serialized data.
 *
 * @param string $request value 'form' or 'read'.
 *
 * @return
 * - Array with settings for that request.
 * - or false if no setting.
 */
function gwolle_gb_get_setting( $request ) {

	$provided = array( 'form', 'read' );
	if ( in_array( $request, $provided ) ) {
		switch ( $request ) {
			case 'form':
				$defaults = array(
					'form_name_enabled'       => 'true',
					'form_name_mandatory'     => 'true',
					'form_city_enabled'       => 'true',
					'form_city_mandatory'     => 'false',
					'form_email_enabled'      => 'true',
					'form_email_mandatory'    => 'true',
					'form_homepage_enabled'   => 'true',
					'form_homepage_mandatory' => 'false',
					'form_message_enabled'    => 'true',
					'form_message_mandatory'  => 'true',
					'form_message_maxlength'  => 0,
					'form_bbcode_enabled'     => 'false',
					'form_antispam_enabled'   => 'false',
					'form_privacy_enabled'    => 'false',
					);
				$setting = get_option( 'gwolle_gb-form', array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_array($setting) && ! empty($setting) ) {
					$setting = array_merge( $defaults, $setting );
					return $setting;
				}
				return $defaults;

			case 'read':
				if ( get_option('show_avatars') ) {
					$avatar = 'true';
				} else {
					$avatar = 'false';
				}

				$defaults = array(
					'read_avatar'   => $avatar,
					'read_name'     => 'true',
					'read_city'     => 'true',
					'read_datetime' => 'true',
					'read_date'     => 'false',
					'read_content'  => 'true',
					'read_aavatar'  => 'false',
					'read_editlink' => 'true',
					);
				$setting = get_option( 'gwolle_gb-read', array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_array($setting) && ! empty($setting) ) {
					$setting = array_merge( $defaults, $setting );
					return $setting;
				}
				return $defaults;

			default:
				return false;
		}
	}
	return false;

}


/*
 * Sanitize arrays.
 *
 * @since 4.9.4
 */
function gwolle_gb_setting_array_sanitize( $data ) {

	$new_data = array();

	if ( is_object( $data ) ) {
		$data = get_object_vars( $data );
	}

	if ( is_array( $data ) ) {
		foreach ( $data as $key => $val ) {

			if ( is_string( $key ) ) {
				$key = sanitize_text_field( $key ); // do not use sanitize_key, it is way too strict for the strings option.
			} else if ( is_int( $key ) ) {
				// $key = $key; // yes, no need to sanitize.
			} else {
				return array(); // does this even exist and happen?
			}

			if ( is_array( $val ) ) {
				// recursive; array in an array. happens with the social media option.
				$new_data[$key] = gwolle_gb_setting_array_sanitize( $val );
			} else if ( is_string( $val ) ) {
				$new_data[$key] = gwolle_gb_sanitize_input( $val, 'setting_textarea' );
			} else if ( is_bool( $val ) || is_int( $val ) || is_float( $val ) ) {
				$new_data[$key] = $val; // no need to sanitize.
			}
			// no other data types accepted.

		}
	} else if ( is_string( $data ) ) {
			$new_data[] = gwolle_gb_sanitize_input( $data, 'setting_textarea' );
	} else if ( is_bool( $data ) || is_int( $data ) || is_float( $data ) ) {
			$new_data[] = $data; // no need to sanitize.
	}
	// no other data types accepted.

	return $new_data;

}
