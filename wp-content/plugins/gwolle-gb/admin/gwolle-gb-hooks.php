<?php

/*
 * WordPress Actions and Filters.
 * See the Plugin API in the Codex:
 * https://codex.wordpress.org/Plugin_API
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



/*
 * Add a menu in the WordPress backend.
 * Load JavaSCript and CSS for Admin.
 */
function gwolle_gb_adminmenu() {
	/*
	 * How to add new menu-entries:
	 * add_menu_page( $page_title, $menu_title, $access_level, $file, $function = '', $icon_url = '' )
	 *
	 * How to add new sub-menu-entries:
	 * add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function = '', $position = null )
	 */

	// Counter
	$count_unchecked = get_transient( 'gwolle_gb_menu_counter' );
	if ( false === $count_unchecked ) {
		$count_unchecked = (int) gwolle_gb_get_entry_count(
			array(
				'checked' => 'unchecked',
				'trash'   => 'notrash',
				'spam'    => 'nospam',
			)
		);
		set_transient( 'gwolle_gb_menu_counter', $count_unchecked, DAY_IN_SECONDS );
	}

	// Main navigation entry
	// Admin page: admin/welcome.php
	$menu_text = esc_html__('Guestbook', 'gwolle-gb') . '<span class="awaiting-mod count-' . $count_unchecked . '"><span>' . $count_unchecked . '</span></span>';
	add_menu_page(
		esc_html__('Guestbook', 'gwolle-gb'), /* translators: Menu entry */
		$menu_text,
		'gwolle_gb_moderate_comments',
		GWOLLE_GB_FOLDER . '/gwolle-gb.php',
		'gwolle_gb_welcome',
		'dashicons-testimonial'
	);

	// Admin page: admin/entries.php
	$menu_text = esc_html__('Entries', 'gwolle-gb') . '<span class="awaiting-mod count-' . $count_unchecked . '"><span>' . $count_unchecked . '</span></span>';
	add_submenu_page(
		GWOLLE_GB_FOLDER . '/gwolle-gb.php',
		esc_html__('Entries', 'gwolle-gb'), /* translators: Menu entry */
		$menu_text,
		'gwolle_gb_moderate_comments',
		GWOLLE_GB_FOLDER . '/entries.php',
		'gwolle_gb_page_entries'
	);

	// Admin page: admin/editor.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', esc_html__('Entry editor', 'gwolle-gb'), /* translators: Menu entry */ esc_html__('Add/Edit entry', 'gwolle-gb'), 'gwolle_gb_moderate_comments', GWOLLE_GB_FOLDER . '/editor.php', 'gwolle_gb_page_editor' );

	// Admin page: admin/settings.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', esc_html__('Settings', 'gwolle-gb'), /* translators: Menu entry */ esc_html__('Settings', 'gwolle-gb'), 'manage_options', GWOLLE_GB_FOLDER . '/settings.php', 'gwolle_gb_page_settings' );

	// Admin page: admin/import.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', esc_html__('Import', 'gwolle-gb'), /* translators: Menu entry */ esc_html__('Import', 'gwolle-gb'), 'manage_options', GWOLLE_GB_FOLDER . '/import.php', 'gwolle_gb_page_import' );

	// Admin page: admin/export.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', esc_html__('Export', 'gwolle-gb'), /* translators: Menu entry */ esc_html__('Export', 'gwolle-gb'), 'manage_options', GWOLLE_GB_FOLDER . '/export.php', 'gwolle_gb_page_export' );
}
add_action('admin_menu', 'gwolle_gb_adminmenu');


/*
 * Load CSS for admin.
 */
function gwolle_gb_admin_enqueue_style() {
	wp_enqueue_style( 'gwolle-gb-admin-css', GWOLLE_GB_URL . 'admin/css/gwolle-gb-admin.css', false, GWOLLE_GB_VER, 'all' );
}
add_action( 'admin_enqueue_scripts', 'gwolle_gb_admin_enqueue_style' );


/*
 * Load JavaScript for admin.
 * It's called directly on the adminpages, it's not being used as a hook.
 */
function gwolle_gb_admin_enqueue() {

	wp_enqueue_script( 'gwolle-gb-admin-js', GWOLLE_GB_URL . 'admin/js/gwolle-gb-admin.js', 'jquery', GWOLLE_GB_VER, true );

	$data_to_be_passed = array(
		'delete_meta'   => esc_html__('Delete this meta field?', 'gwolle-gb' ),
		'delete_string' => esc_html__('Delete this string row?', 'gwolle-gb' ),
	);
	wp_localize_script( 'gwolle-gb-admin-js', 'gwolle_gb_admin', $data_to_be_passed );

}
//add_action( 'admin_enqueue_scripts', 'gwolle_gb_admin_enqueue' );


/*
 * Add Settings link to the main plugin page
 */
function gwolle_gb_links( $links, $file ) {
	if ( $file === GWOLLE_GB_FOLDER . '/gwolle-gb.php' ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=gwolle-gb/settings.php' ) . '">' . esc_html__( 'Settings', 'gwolle-gb' ) . '</a>';
	}
	return $links;
}
add_filter( 'plugin_action_links', 'gwolle_gb_links', 10, 2 );
