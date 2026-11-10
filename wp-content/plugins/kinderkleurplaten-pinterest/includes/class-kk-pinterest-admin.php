<?php
/**
 * Pinterest Admin Dashboard
 * Handles admin UI rendering for dashboard, boards, publish, history, and settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Pinterest_Admin {

    private $plugin;

    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    public function render_dashboard() {
        $this->render_template('dashboard');
    }

    public function render_boards() {
        $this->render_template('boards');
    }

    public function render_publish() {
        $this->render_template('publish');
    }

    public function render_history() {
        $this->render_template('history');
    }

    public function render_settings() {
        $this->render_template('settings');
    }

    private function render_template($template) {
        $template_path = KK_PINTEREST_PLUGIN_DIR . "templates/{$template}.php";
        
        if (!file_exists($template_path)) {
            $template_path = KK_PINTEREST_PLUGIN_DIR . "templates/default.php";
        }

        $api = $this->plugin->get_api();
        $oauth = $this->plugin->get_oauth();
        $history = $this->plugin->get_history();
        $board_manager = $this->plugin->get_board_manager();
        $pin_publisher = $this->plugin->get_pin_publisher();

        $connection_status = $oauth->get_connection_status();
        $stats = $history->get_stats();
        $boards = $board_manager->get_local_boards(array('per_page' => 100));
        $recent_history = $history->get_history(array('per_page' => 10));
        $recent_pins = $pin_publisher->get_pins(array('page_size' => 10));

        include $template_path;
    }
}