<?php
/*
 * Settings page for the guestbook addon
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function gwolle_gb_addon_menu_settings_v2() {
	// Admin page: admin/settings.php
	add_submenu_page( 'gwolle-gb/gwolle-gb.php', esc_html__('The Add-On', 'gwolle-gb'), /* translators: Menu entry */ esc_html__('The Add-On', 'gwolle-gb'), 'manage_options', 'gwolle-gb/addon-settings-v2.php', 'gwolle_gb_addon_page_settings_v2' );
}
add_action( 'admin_menu', 'gwolle_gb_addon_menu_settings_v2', 11 );



function gwolle_gb_addon_page_settings_v2() {

	if ( ! current_user_can('manage_options') ) {
		die( esc_html__('You need a higher level of permission.', 'gwolle-gb') );
	}

	gwolle_gb_admin_enqueue();

	$active_tab = 'gwolle_gb_form';
	$saved = false;

	if ( isset( $_POST['option_page']) && $_POST['option_page'] == 'gwolle_gb_addon_options' ) {
		gwolle_gb_addon_page_settings_update_v2();
		$saved = true;
		$active_tab = gwolle_gb_addon_settings_active_tab_v2();
	}
	$gwolle_gb_messages = gwolle_gb_get_messages();
	$gwolle_gb_errors = gwolle_gb_get_errors();
	?>

	<div class="wrap gwolle_gb">

		<div id="icon-gwolle-gb"><br /></div>
		<h1><?php esc_html_e('Add-On Settings', 'gwolle-gb'); ?> (Gwolle Guestbook) - v<?php echo GWOLLE_GB_VER; ?></h1>

		<?php
		if ( $gwolle_gb_errors ) {
			echo '
				<div id="message" class="updated fade notice is-dismissible error">' .
					$gwolle_gb_messages .
				'</div>';
		} else if ( $saved ) {
			echo '
				<div id="message" class="updated fade notice is-dismissible">
					<p>' . esc_html__('Changes saved.', 'gwolle-gb') . '</p>' .
					$gwolle_gb_messages .
			'</div>';
		}

		/* The rel attribute will be the form that becomes active */
		/* Do not use nav but h2, since it is using (in)visible content, not real navigation. */
		?>
		<h2 class="nav-tab-wrapper gwolle-nav-tab-wrapper" role="tablist">
			<a href="#" role="tab" class="nav-tab <?php if ($active_tab === 'gwolle_gb_form')       { echo "nav-tab-active";} ?>" rel="gwolle_gb_form"><?php /* translators: Settings page tab */ esc_html_e('Form Fields', 'gwolle-gb'); ?></a>
			<a href="#" role="tab" class="nav-tab <?php if ($active_tab === 'gwolle_gb_reading')    { echo "nav-tab-active";} ?>" rel="gwolle_gb_reading"><?php /* translators: Settings page tab */ esc_html_e('Reading Fields', 'gwolle-gb'); ?></a>
			<a href="#" role="tab" class="nav-tab <?php if ($active_tab === 'gwolle_gb_social')     { echo "nav-tab-active";} ?>" rel="gwolle_gb_social"><?php /* translators: Settings page tab */ esc_html_e('Social Media', 'gwolle-gb'); ?></a>
			<a href="#" role="tab" class="nav-tab <?php if ($active_tab === 'gwolle_gb_starrating') { echo "nav-tab-active";} ?>" rel="gwolle_gb_starrating"><?php /* translators: Settings page tab */ esc_html_e('Star Rating', 'gwolle-gb'); ?></a>
			<a href="#" role="tab" class="nav-tab <?php if ($active_tab === 'gwolle_gb_misc')       { echo "nav-tab-active";} ?>" rel="gwolle_gb_misc"><?php /* translators: Settings page tab */ esc_html_e('Miscellaneous', 'gwolle-gb'); ?></a>
			<a href="#" role="tab" class="nav-tab <?php if ($active_tab === 'gwolle_gb_strings')    { echo "nav-tab-active";} ?>" rel="gwolle_gb_strings"><?php /* translators: Settings page tab */ esc_html_e('Strings', 'gwolle-gb'); ?></a>
		</h2>

		<form name="gwolle_gb_options" role="tabpanel" class="gwolle_gb_options gwolle_gb_form <?php if ($active_tab === 'gwolle_gb_form') { echo "active";} ?>" method="post" action="#">
			<?php gwolle_gb_addon_page_settingstab_form_v2(); ?>
		</form>


		<form name="gwolle_gb_options" role="tabpanel" class="gwolle_gb_options gwolle_gb_reading <?php if ($active_tab === 'gwolle_gb_reading') { echo "active";} ?>" method="post" action="#">
			<?php gwolle_gb_addon_page_settingstab_reading_v2(); ?>
		</form>


		<form name="gwolle_gb_options" role="tabpanel" class="gwolle_gb_options gwolle_gb_social <?php if ($active_tab === 'gwolle_gb_social') { echo "active";} ?>" method="post" action="#">
			<?php gwolle_gb_addon_page_settingstab_social_v2(); ?>
		</form>


		<form name="gwolle_gb_options" role="tabpanel" class="gwolle_gb_options gwolle_gb_starrating <?php if ($active_tab === 'gwolle_gb_starrating') { echo "active";} ?>" method="post" action="#">
			<?php gwolle_gb_addon_page_settingstab_starrating_v2(); ?>
		</form>


		<form name="gwolle_gb_options" role="tabpanel" class="gwolle_gb_options gwolle_gb_misc <?php if ($active_tab === 'gwolle_gb_misc') { echo "active";} ?>" method="post" action="#">
			<?php gwolle_gb_addon_page_settingstab_misc_v2(); ?>
		</form>


		<form name="gwolle_gb_options" role="tabpanel" class="gwolle_gb_options gwolle_gb_strings <?php if ($active_tab === 'gwolle_gb_strings') { echo "active";} ?>" method="post" action="#">
			<?php gwolle_gb_addon_page_settingstab_strings_v2(); ?>
		</form>

	</div> <!-- wrap -->
	<?php
}



function gwolle_gb_addon_page_settings_update_v2() {

	if ( ! current_user_can('manage_options') ) {
		die( esc_html__('You need a higher level of permission.', 'gwolle-gb') );
	}

	if ( isset( $_POST['option_page']) && $_POST['option_page'] === 'gwolle_gb_addon_options' ) {
		if ( isset( $_POST['gwolle_gb_tab'] ) ) {
			$active_tab = $_POST['gwolle_gb_tab'];
			gwolle_gb_addon_settings_active_tab_v2( $active_tab );

			switch ( $active_tab ) {
				case 'gwolle_gb_form':
					/* Form Settings */

					/* Check Nonce */
					$verified = false;
					if ( isset($_POST['gwolle_gb_addon_form_nonce']) ) {
						$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gwolle_gb_addon_form_nonce'] ) ), 'gwolle_gb_addon_form_nonce' );
					}
					if ( $verified == false ) {
						// Nonce is invalid.
						gwolle_gb_add_message( '<p>' . esc_html__('The Nonce did not validate. Please reload the page and try again.', 'gwolle-gb') . '</p>', true, false);
						break;
					}

					if ( isset($_POST['gb_form']) ) {
						if ( isset($_POST['gb_form']['top']) ) {
							$data = $_POST['gb_form']['top'];
							$meta_fields = gwolle_gb_addon_postdata_to_meta_field_v2( $data );
							update_option( 'gwolle_gb_addon-form_top', $meta_fields, true );
						}
						if ( isset($_POST['gb_form']['name']) ) {
							$data = $_POST['gb_form']['name'];
							$meta_fields = gwolle_gb_addon_postdata_to_meta_field_v2( $data );
							update_option( 'gwolle_gb_addon-form_name', $meta_fields, true );
						}
						if ( isset($_POST['gb_form']['city']) ) {
							$data = $_POST['gb_form']['city'];
							$meta_fields = gwolle_gb_addon_postdata_to_meta_field_v2( $data );
							update_option( 'gwolle_gb_addon-form_city', $meta_fields, true );
						}
						if ( isset($_POST['gb_form']['email']) ) {
							$data = $_POST['gb_form']['email'];
							$meta_fields = gwolle_gb_addon_postdata_to_meta_field_v2( $data );
							update_option( 'gwolle_gb_addon-form_email', $meta_fields, true );
						}
						if ( isset($_POST['gb_form']['website']) ) {
							$data = $_POST['gb_form']['website'];
							$meta_fields = gwolle_gb_addon_postdata_to_meta_field_v2( $data );
							update_option( 'gwolle_gb_addon-form_website', $meta_fields, true );
						}
						if ( isset($_POST['gb_form']['message']) ) {
							$data = $_POST['gb_form']['message'];
							$meta_fields = gwolle_gb_addon_postdata_to_meta_field_v2( $data );
							update_option( 'gwolle_gb_addon-form_message', $meta_fields, true );
						}
					}
					break;

				case 'gwolle_gb_reading':
					/* Reading Settings */

					/* Check Nonce */
					$verified = false;
					if ( isset($_POST['gwolle_gb_addon_reading_nonce']) ) {
						$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gwolle_gb_addon_reading_nonce'] ) ), 'gwolle_gb_addon_reading_nonce' );
					}
					if ( $verified == false ) {
						// Nonce is invalid.
						gwolle_gb_add_message( '<p>' . esc_html__('The Nonce did not validate. Please reload the page and try again.', 'gwolle-gb') . '</p>', true, false);
						break;
					}

					if ( isset($_POST['gwolle_gb_addon_read']) ) {
						$data = $_POST['gwolle_gb_addon_read'];
						$reading = array();
						$items = gwolle_gb_addon_get_meta_fields_all_v2();
						if ( is_array( $items ) ) {
							foreach ( $items as $item ) {
								$item_slug = $item['slug'];
								if ( isset($data["$item_slug"] ) ) {
									$reading["$item_slug"] = $data["$item_slug"];
								}
							}
						}
						update_option( 'gwolle_gb_addon-reading', $reading, true );
					}

					if ( isset($_POST['gwolle_gb_addon_widget']) ) {
						$data = $_POST['gwolle_gb_addon_widget'];
						$widget = array();
						$items = gwolle_gb_addon_get_meta_fields_all_v2();
						if ( is_array( $items ) ) {
							foreach ( $items as $item ) {
								$item_slug = $item['slug'];
								if ( isset($data["$item_slug"] ) ) {
									$widget["$item_slug"] = $data["$item_slug"];
								}
							}
						}
						update_option( 'gwolle_gb_addon-widget', $widget, true );
					} else {
						update_option( 'gwolle_gb_addon-widget', array(), true );
					}
					break;

				case 'gwolle_gb_social':
					/* Social Media Settings */

					/* Check Nonce */
					$verified = false;
					if ( isset($_POST['gwolle_gb_addon_social_nonce']) ) {
						$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gwolle_gb_addon_social_nonce'] ) ), 'gwolle_gb_addon_social_nonce' );
					}
					if ( $verified == false ) {
						// Nonce is invalid.
						gwolle_gb_add_message( '<p>' . esc_html__('The Nonce did not validate. Please reload the page and try again.', 'gwolle-gb') . '</p>', true, false);
						break;
					}

					if (isset($_POST['gb_social_media']) && $_POST['gb_social_media'] == 'on') {
						update_option( 'gwolle_gb_addon-social_media', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-social_media', 'false', true );
					}

					if (isset($_POST['gb_social_services']) && is_array( $_POST['gb_social_services'] ) ) {
						$services = gwolle_gb_addon_get_social_media_v2();
						$settings = array();
						foreach ( $services as $key => $service ) {
							if ( isset($_POST['gb_social_services'][$key]) && $_POST['gb_social_services'][$key] == 'on' ) {
								$settings[$key] = array('check' => 'true');
							}
						}
						update_option( 'gwolle_gb_addon-social_services', $settings, true );
					} else {
						update_option( 'gwolle_gb_addon-social_services', '', true );
					}

					if (isset($_POST['gwolle_gb_addon_social_media_loc'])) {
						update_option( 'gwolle_gb_addon-social_media_loc', (int) $_POST['gwolle_gb_addon_social_media_loc'], true );
					}
					break;

				case 'gwolle_gb_starrating':
					/* Star Rating Settings */

					/* Check Nonce */
					$verified = false;
					if ( isset($_POST['gwolle_gb_addon_starrating_nonce']) ) {
						$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gwolle_gb_addon_starrating_nonce'] ) ), 'gwolle_gb_addon_starrating_nonce' );
					}
					if ( $verified == false ) {
						// Nonce is invalid.
						gwolle_gb_add_message( '<p>' . esc_html__('The Nonce did not validate. Please reload the page and try again.', 'gwolle-gb') . '</p>', true, false);
						break;
					}

					if (isset($_POST['gb_starrating']) && $_POST['gb_starrating'] == 'on') {
						update_option( 'gwolle_gb_addon-starrating', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-starrating', 'false', true );
					}

					if (isset($_POST['gb_starrating_avg']) && $_POST['gb_starrating_avg'] == 'on') {
						update_option( 'gwolle_gb_addon-starrating_avg', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-starrating_avg', 'false', true );
					}

					if (isset($_POST['gwolle_gb_addon_starrating_loc'])) {
						update_option( 'gwolle_gb_addon-starrating_loc', (int) $_POST['gwolle_gb_addon_starrating_loc'], true );
					}

					if (isset($_POST['gb_starrating_type'])) {
						update_option( 'gwolle_gb_addon-starrating_type', trim( wp_kses_post( $_POST['gb_starrating_type'] ) ), true );
					}
					break;

				case 'gwolle_gb_misc':
					/* Misc Settings */

					/* Check Nonce */
					$verified = false;
					if ( isset($_POST['gwolle_gb_addon_misc_nonce']) ) {
						$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gwolle_gb_addon_misc_nonce'] ) ), 'gwolle_gb_addon_misc_nonce' );
					}
					if ( $verified == false ) {
						// Nonce is invalid.
						gwolle_gb_add_message( '<p>' . esc_html__('The Nonce did not validate. Please reload the page and try again.', 'gwolle-gb') . '</p>', true, false);
						break;
					}

					if (isset($_POST['gb_preview']) && $_POST['gb_preview'] == 'on') {
						update_option( 'gwolle_gb_addon-preview', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-preview', 'false', true );
					}

					if (isset($_POST['gb_permalink']) && $_POST['gb_permalink'] == 'on') {
						update_option( 'gwolle_gb_addon-permalink', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-permalink', 'false', true );
					}

					if (isset($_POST['gb_email']) && $_POST['gb_email'] == 'on') {
						update_option( 'gwolle_gb_addon-email', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-email', 'false', true );
					}

					if (isset($_POST['gb_report']) && $_POST['gb_report'] == 'on') {
						update_option( 'gwolle_gb_addon-report', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-report', 'false', true );
					}

					if (isset($_POST['gb_upload']) && $_POST['gb_upload'] == 'on') {
						update_option( 'gwolle_gb_addon-upload', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-upload', 'false', true );
					}

					if (isset($_POST['gb_likes']) && $_POST['gb_likes'] == 'on') {
						update_option( 'gwolle_gb_addon-likes', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-likes', 'false', true );
					}

					if (isset($_POST['gwolle_gb_addon_likes_loc'])) {
						update_option( 'gwolle_gb_addon-likes_loc', (int) $_POST['gwolle_gb_addon_likes_loc'], true );
					}

					if (isset($_POST['gwolle-gb-sitemap']) && $_POST['gwolle-gb-sitemap'] == 'on') {
						update_option( 'gwolle_gb_addon-sitemap', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-sitemap', 'false', true );
					}

					if (isset($_POST['delete_link']) && $_POST['delete_link'] == 'on') {
						update_option( 'gwolle_gb_addon-delete_link', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-delete_link', 'false', true );
					}

					if (isset($_POST['delete_link_author']) && $_POST['delete_link_author'] == 'on') {
						update_option( 'gwolle_gb_addon-delete_link_author', 'true', true );
					} else {
						update_option( 'gwolle_gb_addon-delete_link_author', 'false', true );
					}

					if (isset($_POST['gwolle_gb_addon_auto_anonymize']) && $_POST['gwolle_gb_addon_auto_anonymize'] == 'on') {
						update_option( 'gwolle_gb_addon-auto_anonymize', 'true', false);
					} else {
						update_option( 'gwolle_gb_addon-auto_anonymize', 'false', false);
					}

					if (isset($_POST['gwolle_gb_addon_auto_anonymize_time'])) {
						update_option( 'gwolle_gb_addon-auto_anonymize_time', (int) $_POST['gwolle_gb_addon_auto_anonymize_time'], false);
					}

					if (isset($_POST['gwolle_gb_addon_auto_delete']) && $_POST['gwolle_gb_addon_auto_delete'] == 'on') {
						update_option( 'gwolle_gb_addon-auto_delete', 'true', false);
					} else {
						update_option( 'gwolle_gb_addon-auto_delete', 'false', false );
					}

					if (isset($_POST['gwolle_gb_addon_auto_delete_time'])) {
						update_option( 'gwolle_gb_addon-auto_delete_time', (int) $_POST['gwolle_gb_addon_auto_delete_time'], false );
					}
					break;

				case 'gwolle_gb_strings':
					/* String Replacements */

					/* Check Nonce */
					$verified = false;
					if ( isset($_POST['gwolle_gb_addon_strings_nonce']) ) {
						$verified = wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gwolle_gb_addon_strings_nonce'] ) ), 'gwolle_gb_addon_strings_nonce' );
					}
					if ( $verified == false ) {
						// Nonce is invalid.
						gwolle_gb_add_message( '<p>' . esc_html__('The Nonce did not validate. Please reload the page and try again.', 'gwolle-gb') . '</p>', true, false);
						break;
					}

					$data = $_POST['gb_strings'];
					$strings = array();
					if ( isset( $data ) && ! empty( $data ) ) {
						$count_oldstring = count( $data['oldstring'] );
						for ($i = 0; $i < $count_oldstring; ++$i) {
							$oldstring = gwolle_gb_sanitize_input( $data['oldstring'][$i], 'setting_textarea' );
							$newstring = gwolle_gb_sanitize_input( $data['newstring'][$i], 'setting_textarea' );

							if ($oldstring == '' || $newstring == '') {
								continue;
							} else {
								$strings["$oldstring"] = $newstring;
							}
						}
					}
					update_option( 'gwolle_gb_addon-strings', $strings, true );
					break;

				default:
					/* Just load the first tab */
					gwolle_gb_addon_settings_active_tab_v2( 'gwolle_gb_form' );
					break;
			}
		}
	}
}


/*
 * Set and Get active tab for settings page.
 *
 * @param  string $active_tab text string with active tab (optional).
 * @return string text string with active tab.
 *
 * @since 1.2.5
 */
function gwolle_gb_addon_settings_active_tab_v2( $active_tab = false ) {

	static $active_tab_static;

	if ( $active_tab ) {
		$active_tab_static = $active_tab;
	}

	return $active_tab_static;

}


/*
 * Get meta field setting from postdata for saving.
 *
 * @param  array $data array with postdata for one or more meta fields.
 * @return array with data ready to be saved.
 * @since 2.0.0
 */
function gwolle_gb_addon_postdata_to_meta_field_v2( $data ) {

	$meta_fields = array();
	if ( isset($data['slug']) ) {
		for ( $i = 0; $i < count($data['slug']); ++$i ) {
			$meta_field = array();

			$meta_field['slug'] = gwolle_gb_addon_truncate_slug_v2( trim( sanitize_text_field( $data['slug'][$i] ) ) );

			$meta_field['name'] = trim( sanitize_text_field( $data['name'][$i] ) );

			if ( $meta_field['slug'] == '' || $meta_field['name'] == '' ) {
				continue;
			}
			if ( $meta_field['slug'] == 'starrating' || $meta_field['slug'] == 'likes' ) {
				gwolle_gb_add_message( '<p>' . sprintf( esc_html__('The slug %s is a reserved slug, please use a different slug.', 'gwolle-gb'), $meta_field['slug'] ) . '</p>', false, false);
				continue;
			}

			if ( isset($data['required'][$i]) && $data['required'][$i] == 'on' ) {
				$meta_field['required'] = 1;
			} else {
				$meta_field['required'] = 0;
			}

			if ( isset( $data['type'][$i] ) ) {
				$type = $data['type'][$i];
				$types = array( 'text', 'checkbox', 'radio', 'select', 'textarea' );
				if ( in_array( $type, $types ) ) {
					$meta_field['type'] = $type;
				}
			}

			if ( isset( $data['options'][$i] ) ) {
				$options = $data['options'][$i] ;
				$options = gwolle_gb_sanitize_input( $options, 'setting_textarea' );
				$options = explode( "\n", $options );
				$options = array_filter( array_map( 'trim', $options ) );
				$options = array_unique( $options );
				$options = implode( "\n", $options );
				$meta_field['options'] = $options;
			}

			$meta_fields[] = $meta_field;
		}
	}

	return $meta_fields;
}
