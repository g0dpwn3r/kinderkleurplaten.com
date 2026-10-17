<?php
/*
 **	This file is used during activation or upgrading from a prior version.
 **
 **	It uses the PHP function version_compare(). Usage:
 **	version_compare(OLD_VERSION_NUMBER,NEW_VERSION_NUMBER,'<')
 **	=> TRUE, if the old version number is smaller than the new one.
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/*
 * Install plugin structure.
 */
function gwolle_gb_install() {
	global $wpdb;

	// Install the table for the entries

	// Declare database table names
	$wpdb->gwolle_gb_entries = $wpdb->prefix . 'gwolle_gb_entries';
	$wpdb->gwolle_gb_log = $wpdb->prefix . 'gwolle_gb_log';

	$sql = "
		CREATE TABLE IF NOT EXISTS
			" . $wpdb->gwolle_gb_entries . "
		(
			id int(10) NOT NULL auto_increment,
			author_name text NOT NULL,
			author_id int(5) NOT NULL default '0',
			author_email text NOT NULL,
			author_origin text NOT NULL,
			author_website text NOT NULL,
			author_ip text NOT NULL,
			author_host text NOT NULL,
			content longtext NOT NULL,
			datetime bigint(8) UNSIGNED NOT NULL,
			ischecked tinyint(1) NOT NULL,
			checkedby int(5) NOT NULL,
			istrash varchar(1) NOT NULL default '0',
			isspam varchar(1) NOT NULL default '0',
			admin_reply longtext NOT NULL,
			admin_reply_uid int(5) NOT NULL default '0',
			book_id int(5) NOT NULL default '1',
			PRIMARY KEY  (id)
		) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
	$result = $wpdb->query($sql); // returns bool

	$sql = "
		CREATE TABLE IF NOT EXISTS
			" . $wpdb->gwolle_gb_log . "
		(
			id int(8) NOT NULL auto_increment,
			subject text NOT NULL,
			entry_id int(5) NOT NULL,
			author_id int(5) NOT NULL,
			datetime bigint(8) UNSIGNED NOT NULL,
			PRIMARY KEY  (id)
		) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
	$result = $wpdb->query($sql); // returns bool

	/* Set default options if not set yet. */
	gwolle_gb_set_defaults();

	/* Save plugin version to database only when we did install. */
	$result_after = $wpdb->query("SHOW TABLES LIKE '" . $wpdb->prefix . "gwolle_gb_entries'");
	$result_after2 = $wpdb->query("SHOW TABLES LIKE '" . $wpdb->prefix . "gwolle_gb_log'");
	if ( $result_after !== 0 && $result_after2 !== 0 ) {
		add_option( 'gwolle_gb_version', GWOLLE_GB_VER, '', true );
	}

	/* Add-on */

	// Declare database table names
	$wpdb->gwolle_gb_meta = $wpdb->prefix . 'gwolle_gb_meta';

	$sql = "
		CREATE TABLE IF NOT EXISTS
			" . $wpdb->gwolle_gb_meta . "
		(
			meta_id bigint(20) NOT NULL auto_increment,
			entry_id bigint(20) NOT NULL,
			meta_key varchar(255) NOT NULL default '0',
			meta_value longtext NOT NULL,
			PRIMARY KEY  (meta_id)
		) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
	$result = $wpdb->query($sql);

	/* Save plugin version to database only when we did install. */
	$result_after3 = $wpdb->query("SHOW TABLES LIKE '" . $wpdb->prefix . "gwolle_gb_meta'");
	if ( $result_after3 != 0 ) {
		update_option( 'gwolle_gb_addon_version', GWOLLE_GB_ADDON_VER, true );
	}

	/* Call flush_rules() as a method of the $wp_rewrite object for the RSS Feed. */
	global $wp_rewrite;
	$wp_rewrite->flush_rules( false );

}


/*
 * Uninstall plugin structure.
 * Called from the settingspage or from multisite uninstall function.
 */
function gwolle_gb_uninstall() {

	// Delete the plugin's tables
	global $wpdb;
	$wpdb->query("DROP TABLE " . $wpdb->prefix . "gwolle_gb_log");
	$wpdb->query("DROP TABLE " . $wpdb->prefix . "gwolle_gb_entries");
	$wpdb->query("DROP TABLE " . $wpdb->prefix . "gwolle_gb_meta");

	// Delete the plugin's preferences and version-nr in the wp-options table
	$wpdb->query("
			DELETE
				FROM " . $wpdb->prefix . "options
			WHERE
				option_name LIKE 'gwolle_gb%'
		");

	do_action( 'gwolle_gb_uninstall' );

	// Deactivate ourselves
	deactivate_plugins( GWOLLE_GB_FOLDER . '/gwolle-gb.php' );

}


/*
 * Upgrade plugin structure.
 */
function gwolle_gb_upgrade() {
	global $wpdb;
	$installed_ver = get_option('gwolle_gb_version');

	if (version_compare($installed_ver, '0.9.3', '<')) {
		/*
		 **	0.9.2->0.9.3
		 **	Added Akismet integration
		 **	Add an option row and a new column to the entry table
		 **	to be able to mark entries as spam automatically.
		 */
		add_option( 'gwolle_gb-akismet-active', 'false', '', true );
		$wpdb->query("
				ALTER
				TABLE " . $wpdb->gwolle_gb_entries . "
				ADD
					entry_isSpam
						VARCHAR( 1 )
						NOT NULL
						DEFAULT '0'
						AFTER entry_isDeleted
			");
	}

	if (version_compare($installed_ver, '0.9.4', '<')) {
		/*
		 * 0.9.3->0.9.4
		 * moderate on/off.
		 */
		add_option( 'gwolle_gb-moderate-entries', 'true', '', true);
	}

	if (version_compare($installed_ver, '0.9.4.1', '<')) {
		/*
		 **	0.9.4->0.9.4.1
		 **	add an option to show icons in the admin entry list.
		 */
		add_option( 'gwolle_gb-showEntryIcons', 'true', '', false );
	}

	if (version_compare($installed_ver, '0.9.4.2', '<')) {
		/*
		 **	0.9.4.1->0.9.4.2
		 **	add the possibility to set the numbers of entries per page.
		 */
		add_option( 'gwolle_gb-entriesPerPage', '20', '', true);
	}

	if (version_compare($installed_ver, '0.9.4.5', '<')) {
		/*
		 **	0.9.4.2.3->0.9.4.5
		 **	Support for utf8 chars.
		 **	Added option to toggle line breaks-visibility.
		 */
		$wpdb->query("
				ALTER
				TABLE " . $wpdb->gwolle_gb_entries . "
					DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
			");
		$wpdb->query("
				ALTER
				TABLE " . $wpdb->gwolle_gb_entries . "
					CHANGE `entry_id` `entry_id` INT(10) NOT NULL AUTO_INCREMENT,
					CHANGE `entry_author_name` `entry_author_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
					CHANGE `entry_authorAdminId` `entry_authorAdminId` INT(5) NOT NULL DEFAULT '0',
					CHANGE `entry_author_email` `entry_author_email` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
					CHANGE `entry_author_origin` `entry_author_origin` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
					CHANGE `entry_author_website` `entry_author_website` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
					CHANGE `entry_author_ip` `entry_author_ip` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
					CHANGE `entry_author_host` `entry_author_host` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
					CHANGE `entry_content` `entry_content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
					CHANGE `entry_date` `entry_date` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
					CHANGE `entry_isChecked` `entry_isChecked` TINYINT(1) NOT NULL,
					CHANGE `entry_checkedBy` `entry_checkedBy` INT(5) NOT NULL,
					CHANGE `entry_isDeleted` `entry_isDeleted` VARCHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
					CHANGE `entry_isSpam` `entry_isSpam` VARCHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0'
			");
		add_option( 'gwolle_gb-showLineBreaks', 'false', '', true );
	}

	if (version_compare($installed_ver, '0.9.6', '<')) {
		/**
		 * 0.9.5->0.9.6
		 * Added the following options:
		 * - toggle replacing of smilies
		 * - toggle link to author's website
		 */
		add_option( 'gwolle_gb-showSmilies', 'true', '', true);
		add_option( 'gwolle_gb-linkAuthorWebsite', 'true', '', true );
	}

	if (version_compare($installed_ver, '0.9.9.1', '<')) {
		/*
		 *  0.9.8.1->0.9.9.0
		 *  Removed the access level option, use standard WordPress capabilities.
		 *  Save Users that are subcribed to notification mails in an option with the array of user_id's
		 */
		delete_option('gwolle_gb-access-level');

		// Get users from database who have subscribed to the notification service.
		$sql = "
				SELECT *
				FROM
					" . $wpdb->prefix . "options
				WHERE
					option_name LIKE 'gwolle_gb-notifyByMail-%'
				ORDER BY
					option_name
			";
		$notify_user_result = $wpdb->get_results($sql, ARRAY_A);
		if ( count($notify_user_result) > 0 ) {
			$user_ids = array();
			foreach ( $notify_user_result as $option ) {
				$user_id = (int) str_replace('gwolle_gb-notifyByMail-', '', $option['option_name']);
				$user_info = get_userdata($user_id);
				if ($user_info === FALSE) {
					// Invalid $user_id
					continue;
				}
				if ($user_id > 0) {
					$user_ids[] = $user_id;
				}
			}
			$user_ids = implode(",", $user_ids);
			update_option('gwolle_gb-notifyByMail', $user_ids);
		}
	}

	if (version_compare($installed_ver, '0.9.9.2', '<')) {
		/*
		 *  0.9.9.1->0.9.9.2
		 *  Remove the options of Users that are subcribed to notification mails
		 */
		$wpdb->query("
				DELETE
					FROM " . $wpdb->prefix . "options
				WHERE
					option_name LIKE 'gwolle_gb-notifyByMail-%'
				OR
					option_name LIKE 'gwolle_gb-notifyAll-%'
			");
	}

	if (version_compare($installed_ver, '1.0.5', '<')) {
		/*
		 *  1.0.4->1.0.5
		 *  Remove obsolete options
		 */
		delete_option('gwolle_gb-access-level');
		delete_option('gwolle_gb-checkForImport');
		delete_option('gwolle_gb-post_ID');

		/* Alter table of logs */
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_log
				CHANGE log_id id int(8) NOT NULL auto_increment,
				CHANGE log_subject subject text NOT NULL,
				CHANGE log_subjectId entry_id int(5) NOT NULL,
				CHANGE log_authorId author_id int(5) NOT NULL,
				CHANGE log_date date varchar(12) NOT NULL
			");
	}

	if (version_compare($installed_ver, '1.0.6', '<')) {
		/*
		 * 1.0.5->1.0.6
		 * Alter table of entries
		 */
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_entries
				CHANGE `entry_id` `id` INT(10) NOT NULL AUTO_INCREMENT,
				CHANGE `entry_author_name` `author_name` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `entry_authorAdminId` `author_id` INT(5) NOT NULL DEFAULT '0',
				CHANGE `entry_author_email` `author_email` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `entry_author_origin` `author_origin` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `entry_author_website` `author_website` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `entry_author_ip` `author_ip` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `entry_author_host` `author_host` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `entry_content` `content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `entry_date` `date` VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
				CHANGE `entry_isChecked` `ischecked` TINYINT(1) NOT NULL,
				CHANGE `entry_checkedBy` `checkedby` INT(5) NOT NULL,
				CHANGE `entry_isDeleted` `istrash` VARCHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
				CHANGE `entry_isSpam` `isspam` VARCHAR(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0'
			");
	}

	if (version_compare($installed_ver, '1.1.3', '<')) {
		/*
		 * 1.1.2->1.1.3
		 */
		delete_option('gwolle_gb-guestbookOnly');
		delete_option('gwolle_gb-defaultMailText');
		delete_option('gwolle_gb-recaptcha-active');
	}

	if (version_compare($installed_ver, '1.3.8', '<')) {
		/*
		 * 1.3.7->1.3.8
		 * Call flush_rules() as a method of the $wp_rewrite object for the RSS Feed.
		 */
		global $wp_rewrite;
		$wp_rewrite->flush_rules( false );
	}

	if (version_compare($installed_ver, '1.4.2', '<')) {
		/*
		 * 1.4.1->1.4.2
		 * Add datetime field to database and fill it from the date column.
		 */
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_entries ADD `datetime` BIGINT(8) UNSIGNED NOT NULL AFTER `date`;
		");
		$wpdb->query( "
			UPDATE `$wpdb->gwolle_gb_entries` SET `datetime` = `date`;
		");

		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_log ADD `datetime` BIGINT(8) UNSIGNED NOT NULL AFTER `date`;
		");
		$wpdb->query( "
			UPDATE `$wpdb->gwolle_gb_log` SET `datetime` = `date`;
		");
	}

	if (version_compare($installed_ver, '1.4.3', '<')) {
		/*
		 * 1.4.2->1.4.3
		 * Remove date field from database.
		 */
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_entries DROP COLUMN `date`;
		");

		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_log DROP COLUMN `date`;
		");
	}

	if (version_compare($installed_ver, '1.4.8', '<')) {
		/*
		 * 1.4.7->1.4.8
		 * Add admin_reply and admin_reply_uid field to database.
		 */
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_entries ADD `admin_reply` LONGTEXT NOT NULL AFTER `isspam`;
		");
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_entries ADD `admin_reply_uid` INT(5) NOT NULL AFTER `admin_reply`;
		");
	}

	if (version_compare($installed_ver, '1.5.1', '<')) {
		/*
		 * 1.5.0->1.5.1
		 * Add book_id field to database and fill it with value '1'.
		 */
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_entries ADD `book_id` INT(8) UNSIGNED NOT NULL default '1' AFTER `admin_reply_uid`;
		");
	}

	if (version_compare($installed_ver, '3.0.0', '<')) {
		/*
		 * 2.6.7->3.0.0
		 * Switch from MyISAM to InnoDB database engine.
		 */
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_entries ENGINE=InnoDB;
		");
		$wpdb->query( "
			ALTER TABLE $wpdb->gwolle_gb_log ENGINE=InnoDB;
		");
	}

	if (version_compare($installed_ver, '4.0.0', '<')) {
		/*
		 * 3.1.9->4.0.0
		 * Fix serialized options.
		 */
		$options = array(
			'gwolle_gb-form',
			'gwolle_gb-read',
		);
		foreach ( $options as $option ) {
			$value = get_option( $option, array() );
			$value = maybe_unserialize( $value );
			update_option( $option, $value );
		}
	}

	if (version_compare($installed_ver, '4.10.0', '<')) {
		/*
		 * 4.9.3->4.10.0
		 */
		delete_option('gwolle_gb-cleantalk-active');
	}

	if (version_compare($installed_ver, '5.0.1', '<')) {
		/*
		 * 4.10.1->5.0.1
		 * Add-on is now integrated into main plugin.
		 * Use real slug, constant might not exist anymore.
		 */
		deactivate_plugins( 'gwolle-gb-addon/gwolle-gb-addon.php' );
	}

	if (version_compare($installed_ver, '5.1.1', '<')) {
		/*
		 * 4.10.1->5.1.1
		 * Add-on is now integrated into main plugin.
		 * Make sure database table does exist for meta fields.
		 */
		$wpdb->gwolle_gb_meta = $wpdb->prefix . 'gwolle_gb_meta';

		$sql = "
			CREATE TABLE IF NOT EXISTS
				" . $wpdb->gwolle_gb_meta . "
			(
				meta_id bigint(20) NOT NULL auto_increment,
				entry_id bigint(20) NOT NULL,
				meta_key varchar(255) NOT NULL default '0',
				meta_value longtext NOT NULL,
				PRIMARY KEY  (meta_id)
			) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
		$result = $wpdb->query($sql);
	}

	/* Upgrade to new shiny db collation. Since WP 4.2 */
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	if ( function_exists('maybe_convert_table_to_utf8mb4') ) {
		$table = $wpdb->query("SHOW TABLES LIKE '" . $wpdb->prefix . "gwolle_gb_entries'");
		if ( $table != 0 ) {
			maybe_convert_table_to_utf8mb4( $wpdb->gwolle_gb_entries );
		}
		$table = $wpdb->query("SHOW TABLES LIKE '" . $wpdb->prefix . "gwolle_gb_log'");
		if ( $table != 0 ) {
			maybe_convert_table_to_utf8mb4( $wpdb->gwolle_gb_log );
		}
		$table = $wpdb->query("SHOW TABLES LIKE '" . $wpdb->prefix . "gwolle_gb_meta'");
		if ( $table != 0 ) {
			maybe_convert_table_to_utf8mb4( $wpdb->gwolle_gb_meta );
		}
	}

	/* Set default options if not set yet. */
	gwolle_gb_set_defaults();

	/* Update the plugin version option. */
	update_option( 'gwolle_gb_version', GWOLLE_GB_VER, true );


	/* Add-on */
	$installed_addon = get_option('gwolle_gb_addon_version');

	if (version_compare($installed_addon, '2.0.0', '<')) {
		/*
		 * 1.5.1->2.0.0
		 * Fix serialized options.
		 */
		$options = array(
			'gwolle_gb_addon-form_top',
			'gwolle_gb_addon-form_name',
			'gwolle_gb_addon-form_city',
			'gwolle_gb_addon-form_email',
			'gwolle_gb_addon-form_website',
			'gwolle_gb_addon-form_message',
			'gwolle_gb_addon-reading',
			'gwolle_gb_addon-social_services',
			'gwolle_gb_addon-strings',
		);
		foreach ( $options as $option ) {
			$value = get_option( $option, array() );
			$value = maybe_unserialize( $value );
			update_option( $option, $value, true );
		}
	}

	/* Update the plugin version option */
	update_option( 'gwolle_gb_addon_version', GWOLLE_GB_ADDON_VER, true );

}


/*
 * Set default options.
 * Idea is to have all options in the database and thus cached, so we hit an empty cache less often.
 * It is always called on upgrade from gwolle_gb_upgrade().
 *
 * @since 2.5.0
 */
function gwolle_gb_set_defaults() {
	if ( get_option('gwolle_gb-admin_style', false) === false ) {
		update_option( 'gwolle_gb-admin_style', 'false', true );
	}
	if ( get_option('gwolle_gb-akismet-active', false) === false ) {
		update_option( 'gwolle_gb-akismet-active', 'false', true );
	}
	if ( get_option('gwolle_gb-double_entry', false) === false ) {
		update_option( 'gwolle_gb-double_entry', 'true', true );
	}
	if ( get_option('gwolle_gb-entries_per_page', false) === false ) {
		update_option( 'gwolle_gb-entries_per_page', 20, false ); // admin
	}
	if ( get_option('gwolle_gb-entriesPerPage', false) === false ) {
		update_option( 'gwolle_gb-entriesPerPage', 20, true ); // frontend
	}
	if ( get_option('gwolle_gb-excerpt_length', false) === false ) {
		update_option( 'gwolle_gb-excerpt_length', 0, true );
	}
	if ( get_option('gwolle_gb-form', false) === false ) {
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
		update_option( 'gwolle_gb-form', $defaults, true );
	}
	if ( get_option('gwolle_gb-form_ajax', false) === false ) {
		update_option( 'gwolle_gb-form_ajax', 'true', true );
	}
	if ( get_option('gwolle_gb-honeypot', false) === false ) {
		update_option( 'gwolle_gb-honeypot', 'true', true );
	}
	if ( get_option('gwolle_gb-honeypot_value', false) === false ) {
		$random = rand( 1, 99 );
		update_option( 'gwolle_gb-honeypot_value', $random, true );
	}
	if ( get_option('gwolle_gb-labels_float', false) === false ) {
		update_option( 'gwolle_gb-labels_float', 'true', true );
	}
	if ( get_option('gwolle_gb-linkAuthorWebsite', false) === false ) {
		update_option( 'gwolle_gb-linkAuthorWebsite', 'true', true );
	}
	if ( get_option('gwolle_gb-linkchecker', false) === false ) {
		update_option( 'gwolle_gb-linkchecker', 'true', true );
	}
	if ( get_option('gwolle_gb-longtext', false) === false ) {
		update_option( 'gwolle_gb-longtext', 'true', true );
	}
	if ( get_option('gwolle_gb-mail_author', false) === false ) {
		update_option( 'gwolle_gb-mail_author', 'false', true );
	}
	if ( get_option('gwolle_gb-mail_author_moderation', false) === false ) {
		update_option( 'gwolle_gb-mail_author_moderation', 'false', true );
	}
	if ( get_option('gwolle_gb-moderate-entries', false) === false ) {
		update_option( 'gwolle_gb-moderate-entries', 'true', true );
	}
	if ( get_option('gwolle_gb-navigation', false) === false ) {
		update_option( 'gwolle_gb-navigation', 0, true );
	}
	if ( get_option('gwolle_gb-nonce', false) === false ) {
		update_option( 'gwolle_gb-nonce', 'false', true );
	}
	if ( get_option('gwolle_gb-notify-with-spam', false) === false ) {
		update_option( 'gwolle_gb-notify-with-spam', 'true', true );
	}
	if ( get_option('gwolle_gb-paginate_all', false) === false ) {
		update_option( 'gwolle_gb-paginate_all', 'false', true );
	}
	if ( get_option('gwolle_gb-read', false) === false ) {
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
		update_option( 'gwolle_gb-read', $defaults, true );
	}
	if ( get_option('gwolle_gb-refuse-spam', false) === false ) {
		update_option( 'gwolle_gb-refuse-spam', 'false', true );
	}
	if ( get_option('gwolle_gb-require_login', false) === false ) {
		update_option( 'gwolle_gb-require_login', 'false', true );
	}
	if ( get_option('gwolle_gb-sfs', false) === false ) {
		update_option( 'gwolle_gb-sfs', 'false', true );
	}
	if ( get_option('gwolle_gb-store_ip', false) === false ) {
		update_option( 'gwolle_gb-store_ip', 'true', true );
	}
	if ( get_option('gwolle_gb-showEntryIcons', false) === false ) {
		update_option( 'gwolle_gb-showEntryIcons', 'true', false ); // admin
	}
	if ( get_option('gwolle_gb-showLineBreaks', false) === false ) {
		update_option( 'gwolle_gb-showLineBreaks', 'false', true );
	}
	if ( get_option('gwolle_gb-showSmilies', false) === false ) {
		update_option( 'gwolle_gb-showSmilies', 'true', true );
	}
	if ( get_option('gwolle_gb-timeout', false) === false ) {
		update_option( 'gwolle_gb-timeout', 'true', true );
	}

	/* Add-on */
	if ( get_option('gwolle_gb_addon-auto_anonymize', false) === false ) {
		update_option( 'gwolle_gb_addon-auto_anonymize', 'false', false );
	}
	if ( get_option('gwolle_gb_addon-auto_delete', false) === false ) {
		update_option( 'gwolle_gb_addon-auto_delete', 'false', false );
	}
	if ( get_option('gwolle_gb_addon-delete_link', false) === false ) {
		update_option( 'gwolle_gb_addon-delete_link', 'false', true );
	}
	if ( get_option('gwolle_gb_addon-delete_link_author', false) === false ) {
		update_option( 'gwolle_gb_addon-delete_link_author', 'false', true );
	}
	if ( get_option('gwolle_gb_addon-email', false) === false ) {
		update_option( 'gwolle_gb_addon-email', 'false', true );
	}
	if ( get_option('gwolle_gb_addon-upload', false) === false ) {
		update_option( 'gwolle_gb_addon-upload', 'false', true );
	}
	if ( get_option('gwolle_gb_addon-report', false) === false ) {
		update_option( 'gwolle_gb_addon-report', 'false', true );
	}
	if ( get_option('gwolle_gb_addon-preview', false) === false ) {
		update_option( 'gwolle_gb_addon-preview', 'true', true );
	}
	if ( get_option('gwolle_gb_addon-permalink', false) === false ) {
		update_option( 'gwolle_gb_addon-permalink', 'true', true );
	}
	if ( get_option('gwolle_gb_addon-likes', false) === false ) {
		update_option( 'gwolle_gb_addon-likes', 'false', true );
	}
	if ( get_option('gwolle_gb_addon-likes_loc', false) === false ) {
		update_option( 'gwolle_gb_addon-likes_loc', 1, true );
	}
	if ( get_option('gwolle_gb_addon-sitemap', false) === false ) {
		update_option( 'gwolle_gb_addon-sitemap', 'true', true );
	}
	if ( get_option('gwolle_gb_addon-social_media', false) === false ) {
		update_option( 'gwolle_gb_addon-social_media', 'false', true );
	}
	if ( get_option('gwolle_gb_addon-social_media_loc', false) === false ) {
		update_option( 'gwolle_gb_addon-social_media_loc', 2, true );
	}
	if ( get_option('gwolle_gb_addon-social_services', false) === false ) {
		update_option( 'gwolle_gb_addon-social_services', array(), true );
	}
	if ( get_option('gwolle_gb_addon-starrating', false) === false ) {
		update_option( 'gwolle_gb_addon-starrating', 'false', true );
	}
	if ( get_option('gwolle_gb_addon-starrating_avg', false) === false ) {
		update_option( 'gwolle_gb_addon-starrating_avg', 'false', true );
	}
	if ( get_option('gwolle_gb_addon-starrating_loc', false) === false ) {
		update_option( 'gwolle_gb_addon-starrating_loc', 2, true );
	}
	if ( get_option('gwolle_gb_addon-strings', false) === false ) {
		update_option( 'gwolle_gb_addon-strings', 'false', true );
	}

}


/*
 * Support uninstall for MultiSite through a filter.
 * Take Note: This will do an uninstall on all sites.
 * Only run on admin_init, no need for the frontend.
 *
 * @since 2.1.0
 */
function gwolle_gb_multisite_uninstall() {
	global $wpdb;

	if ( is_admin() ) {
		if ( is_multisite() ) {
			$do_uninstall = apply_filters( 'gwolle_gb_multisite_uninstall', false );
			if ( $do_uninstall ) {
				$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					gwolle_gb_uninstall();
					restore_current_blog();
				}
				// Avoid database errors and PHP notices, don't run these actions anymore.
				remove_action( 'admin_menu', 'gwolle_gb_adminmenu', 10 );
				remove_action( 'wp_dashboard_setup', 'gwolle_gb_dashboard_setup', 10 );
				remove_action( 'admin_bar_menu', 'gwolle_gb_admin_bar_menu', 61 );
			}
		}
	}
}
add_action('admin_init', 'gwolle_gb_multisite_uninstall', 99);
