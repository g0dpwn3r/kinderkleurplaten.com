<?php
/**
 * Plugin Name: Kinderkleurplaten Pinterest Integration
 * Description: Complete Pinterest API v5 integration with OAuth, board management, pin publishing, dashboard and history logging for kinderkleurplaten.com
 * Version: 1.0.0
 * Author: Kinderkleurplaten.com
 * Text Domain: kinderkleurplaten-pinterest
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('KK_PINTEREST_VERSION', '1.0.0');
define('KK_PINTEREST_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('KK_PINTEREST_PLUGIN_URL', plugin_dir_url(__FILE__));
define('KK_PINTEREST_DB_VERSION', '1.0.0');

require_once KK_PINTEREST_PLUGIN_DIR . 'includes/class-kk-pinterest-api.php';
require_once KK_PINTEREST_PLUGIN_DIR . 'includes/class-kk-pinterest-oauth.php';
require_once KK_PINTEREST_PLUGIN_DIR . 'includes/class-kk-pinterest-history.php';
require_once KK_PINTEREST_PLUGIN_DIR . 'includes/class-kk-pinterest-board-manager.php';
require_once KK_PINTEREST_PLUGIN_DIR . 'includes/class-kk-pinterest-pin-publisher.php';
require_once KK_PINTEREST_PLUGIN_DIR . 'includes/class-kk-pinterest-admin.php';
require_once KK_PINTEREST_PLUGIN_DIR . 'includes/class-kk-pinterest-ajax.php';

class Kinderkleurplaten_Pinterest {

    private static $instance = null;
    public $api;
    public $oauth;
    public $history;
    public $board_manager;
    public $pin_publisher;
    public $admin;
    public $ajax;

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        self::$instance = $this;
        $this->init_hooks();
        $this->init_components();
    }

    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_action('init', array($this, 'init_textdomain'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_links'));
    }

    private function init_components() {
        $this->api = new KK_Pinterest_API();
        $this->oauth = new KK_Pinterest_OAuth();
        $this->history = new KK_Pinterest_History();
        $this->board_manager = new KK_Pinterest_Board_Manager($this->api);
        $this->pin_publisher = new KK_Pinterest_Pin_Publisher($this->api, $this->history);
        $this->admin = new KK_Pinterest_Admin($this);
        $this->ajax = new KK_Pinterest_AJAX($this);
    }

    public function activate() {
        $this->history->create_table();
        $this->board_manager->create_table();
        $this->oauth->create_options();
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function init_textdomain() {
        load_plugin_textdomain('kinderkleurplaten-pinterest', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Pinterest Integration', 'kinderkleurplaten-pinterest'),
            __('Pinterest', 'kinderkleurplaten-pinterest'),
            'manage_options',
            'kinderkleurplaten-pinterest',
            array($this->admin, 'render_dashboard'),
            'dashicons-pinterest',
            56
        );

        add_submenu_page(
            'kinderkleurplaten-pinterest',
            __('Dashboard', 'kinderkleurplaten-pinterest'),
            __('Dashboard', 'kinderkleurplaten-pinterest'),
            'manage_options',
            'kinderkleurplaten-pinterest',
            array($this->admin, 'render_dashboard')
        );

        add_submenu_page(
            'kinderkleurplaten-pinterest',
            __('Boards', 'kinderkleurplaten-pinterest'),
            __('Boards', 'kinderkleurplaten-pinterest'),
            'manage_options',
            'kinderkleurplaten-pinterest-boards',
            array($this->admin, 'render_boards')
        );

        add_submenu_page(
            'kinderkleurplaten-pinterest',
            __('Publish Pin', 'kinderkleurplaten-pinterest'),
            __('Publish Pin', 'kinderkleurplaten-pinterest'),
            'manage_options',
            'kinderkleurplaten-pinterest-publish',
            array($this->admin, 'render_publish')
        );

        add_submenu_page(
            'kinderkleurplaten-pinterest',
            __('History', 'kinderkleurplaten-pinterest'),
            __('History', 'kinderkleurplaten-pinterest'),
            'manage_options',
            'kinderkleurplaten-pinterest-history',
            array($this->admin, 'render_history')
        );

        add_submenu_page(
            'kinderkleurplaten-pinterest',
            __('Settings', 'kinderkleurplaten-pinterest'),
            __('Settings', 'kinderkleurplaten-pinterest'),
            'manage_options',
            'kinderkleurplaten-pinterest-settings',
            array($this->admin, 'render_settings')
        );
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'kinderkleurplaten-pinterest') === false) {
            return;
        }

        wp_enqueue_style(
            'kk-pinterest-admin',
            KK_PINTEREST_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            KK_PINTEREST_VERSION
        );

        wp_enqueue_script(
            'kk-pinterest-admin',
            KK_PINTEREST_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-api'),
            KK_PINTEREST_VERSION,
            true
        );

        wp_localize_script('kk-pinterest-admin', 'kk_pinterest_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('kk_pinterest_nonce'),
            'current_page' => isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'kinderkleurplaten-pinterest',
            'auth_url' => $this->oauth->get_authorization_url(),
            'default_placeholder' => KK_PINTEREST_PLUGIN_URL . 'assets/images/placeholder.svg',
            'i18n' => array(
                'loading' => __('Loading...', 'kinderkleurplaten-pinterest'),
                'success' => __('Success', 'kinderkleurplaten-pinterest'),
                'error' => __('Error', 'kinderkleurplaten-pinterest'),
                'confirm_delete' => __('Are you sure?', 'kinderkleurplaten-pinterest'),
                'confirm_delete_board' => __('Are you sure you want to delete board "%s"?', 'kinderkleurplaten-pinterest'),
                'confirm_clear_history' => __('Are you sure you want to clear all history? This cannot be undone.', 'kinderkleurplaten-pinterest'),
                'confirm_delete_item' => __('Are you sure you want to delete this history item?', 'kinderkleurplaten-pinterest'),
                'confirm_disconnect' => __('Are you sure you want to disconnect from Pinterest?', 'kinderkleurplaten-pinterest'),
                'confirm_reset' => __('This will delete ALL plugin settings and history. Are you absolutely sure?', 'kinderkleurplaten-pinterest'),
                'create_board' => __('Create Board', 'kinderkleurplaten-pinterest'),
                'edit_board' => __('Edit Board', 'kinderkleurplaten-pinterest'),
                'board_created' => __('Board created successfully', 'kinderkleurplaten-pinterest'),
                'board_updated' => __('Board updated successfully', 'kinderkleurplaten-pinterest'),
                'board_deleted' => __('Board deleted successfully', 'kinderkleurplaten-pinterest'),
                'default_board_set' => __('Default board set successfully', 'kinderkleurplaten-pinterest'),
                'boards_synced' => __('Successfully synced %d boards from Pinterest', 'kinderkleurplaten-pinterest'),
                'section_created' => __('Section created successfully', 'kinderkleurplaten-pinterest'),
                'no_sections' => __('No sections found for this board', 'kinderkleurplaten-pinterest'),
                'loading_sections' => __('Loading sections...', 'kinderkleurplaten-pinterest'),
                'error_loading_sections' => __('Error loading sections', 'kinderkleurplaten-pinterest'),
                'pin_published' => __('Pin published successfully', 'kinderkleurplaten-pinterest'),
                'no_post_selected' => __('No post selected', 'kinderkleurplaten-pinterest'),
                'no_posts_selected' => __('No posts selected for bulk publishing', 'kinderkleurplaten-pinterest'),
                'searching' => __('Searching...', 'kinderkleurplaten-pinterest'),
                'no_posts_found' => __('No posts found', 'kinderkleurplaten-pinterest'),
                'no_title' => __('(No title)', 'kinderkleurplaten-pinterest'),
                'no_description' => __('(No description)', 'kinderkleurplaten-pinterest'),
                'starting' => __('Starting bulk publish...', 'kinderkleurplaten-pinterest'),
                'publishing' => __('Publishing %d of %total%...', 'kinderkleurplaten-pinterest'),
                'completed' => __('Completed: %success% successful, %failed% failed', 'kinderkleurplaten-pinterest'),
                'history_cleared' => __('History cleared successfully', 'kinderkleurplaten-pinterest'),
                'item_deleted' => __('History item deleted', 'kinderkleurplaten-pinterest'),
                'credentials_saved' => __('Credentials saved successfully', 'kinderkleurplaten-pinterest'),
                'error_missing_credentials' => __('Please enter both App ID and App Secret', 'kinderkleurplaten-pinterest'),
                'ajax_error' => __('AJAX request failed. Please check console for details.', 'kinderkleurplaten-pinterest'),
                'settings_saved' => __('Settings saved successfully', 'kinderkleurplaten-pinterest'),
                'token_refreshed' => __('Access token refreshed successfully', 'kinderkleurplaten-pinterest'),
                'connection_ok' => __('Connection test successful', 'kinderkleurplaten-pinterest'),
                'disconnected' => __('Disconnected from Pinterest', 'kinderkleurplaten-pinterest'),
                'plugin_reset' => __('Plugin reset complete', 'kinderkleurplaten-pinterest'),
                'copied' => __('Copied to clipboard', 'kinderkleurplaten-pinterest'),
                'test_oauth' => __('Run OAuth Diagnostics', 'kinderkleurplaten-pinterest'),
                'view_debug_log' => __('View Debug Log', 'kinderkleurplaten-pinterest'),
                'refresh_log' => __('Refresh', 'kinderkleurplaten-pinterest'),
            )
        ));
    }

    public function add_plugin_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=kinderkleurplaten-pinterest') . '">' . __('Dashboard', 'kinderkleurplaten-pinterest') . '</a>',
            '<a href="' . admin_url('admin.php?page=kinderkleurplaten-pinterest-settings') . '">' . __('Settings', 'kinderkleurplaten-pinterest') . '</a>',
        );
        return array_merge($plugin_links, $links);
    }

    public function get_api() {
        return $this->api;
    }

    public function get_oauth() {
        return $this->oauth;
    }

    public function get_history() {
        return $this->history;
    }

    public function get_board_manager() {
        return $this->board_manager;
    }

    public function get_pin_publisher() {
        return $this->pin_publisher;
    }
}

function kk_pinterest() {
    return Kinderkleurplaten_Pinterest::instance();
}

kk_pinterest();