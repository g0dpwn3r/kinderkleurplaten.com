<?php
/**
 * Pinterest AJAX Handlers
 * Handles all AJAX requests for the Pinterest integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Pinterest_AJAX {

    private $plugin;

    public function __construct($plugin) {
        $this->plugin = $plugin;
        $this->init_hooks();
    }

private function init_hooks() {
        add_action('wp_ajax_kk_pinterest_test_connection', array($this, 'test_connection'));
        add_action('wp_ajax_kk_pinterest_test_oauth', array($this, 'test_oauth'));
        add_action('wp_ajax_kk_pinterest_get_auth_url', array($this, 'get_auth_url'));
        add_action('wp_ajax_kk_pinterest_get_debug_log', array($this, 'get_debug_log'));
        add_action('wp_ajax_kk_pinterest_sync_boards', array($this, 'sync_boards'));
        add_action('wp_ajax_kk_pinterest_get_boards', array($this, 'get_boards'));
        add_action('wp_ajax_kk_pinterest_create_board', array($this, 'create_board'));
        add_action('wp_ajax_kk_pinterest_update_board', array($this, 'update_board'));
        add_action('wp_ajax_kk_pinterest_delete_board', array($this, 'delete_board'));
        add_action('wp_ajax_kk_pinterest_get_board_sections', array($this, 'get_board_sections'));
        add_action('wp_ajax_kk_pinterest_create_board_section', array($this, 'create_board_section'));
        add_action('wp_ajax_kk_pinterest_publish_pin', array($this, 'publish_pin'));
        add_action('wp_ajax_kk_pinterest_publish_from_post', array($this, 'publish_from_post'));
        add_action('wp_ajax_kk_pinterest_bulk_publish', array($this, 'bulk_publish'));
        add_action('wp_ajax_kk_pinterest_get_history', array($this, 'get_history'));
        add_action('wp_ajax_kk_pinterest_clear_history', array($this, 'clear_history'));
        add_action('wp_ajax_kk_pinterest_delete_history_item', array($this, 'delete_history_item'));
        add_action('wp_ajax_kk_pinterest_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_kk_pinterest_save_advanced', array($this, 'save_advanced'));
        add_action('wp_ajax_kk_pinterest_get_post_data', array($this, 'get_post_data'));
        add_action('wp_ajax_kk_pinterest_get_posts', array($this, 'get_posts'));
        add_action('wp_ajax_kk_pinterest_set_default_board', array($this, 'set_default_board'));
        add_action('wp_ajax_kk_pinterest_refresh_token', array($this, 'refresh_token'));
        add_action('wp_ajax_kk_pinterest_disconnect', array($this, 'disconnect'));
        add_action('wp_ajax_kk_pinterest_reset_plugin', array($this, 'reset_plugin'));
    }

    public function get_debug_log() {
        $this->check_nonce();
        $this->check_permissions();

        $debug_log = WP_CONTENT_DIR . '/debug.log';
        $log_content = '';

        if (file_exists($debug_log)) {
            // Read last 500 lines of debug.log
            $lines = file($debug_log);
            if ($lines) {
                $pinterest_lines = array_filter($lines, function($line) {
                    return stripos($line, 'KK Pinterest') !== false;
                });
                // Get last 100 Pinterest-related lines
                $pinterest_lines = array_slice($pinterest_lines, -100);
                $log_content = implode('', $pinterest_lines);
            }
        }

        if (empty($log_content)) {
            $log_content = 'No KK Pinterest debug log entries found. Ensure WP_DEBUG is enabled in wp-config.php.';
        }

        wp_send_json_success(array('log' => $log_content));
    }

    public function test_oauth() {
        $this->check_nonce();
        $this->check_permissions();

        $api = $this->plugin->get_api();
        $oauth = $this->plugin->get_oauth();

        $redirect_uri = $oauth->get_redirect_uri();
        $auth_url = $api->get_oauth_url($redirect_uri);

        wp_send_json_success(array(
            'app_id' => $api->get_app_id(),
            'app_secret_set' => !empty(get_option('kk_pinterest_app_secret', '')),
            'access_token_set' => !empty(get_option('kk_pinterest_access_token', '')),
            'refresh_token_set' => !empty(get_option('kk_pinterest_refresh_token', '')),
            'token_expires_at' => get_option('kk_pinterest_token_expires_at', 0),
            'redirect_uri' => $redirect_uri,
            'auth_url' => $auth_url,
            'is_authenticated' => $api->is_authenticated(),
            'is_configured' => $api->is_configured(),
        ));
    }

    public function get_auth_url() {
        $this->check_nonce();
        $this->check_permissions();

        $oauth = $this->plugin->get_oauth();
        $auth_url = $oauth->get_authorization_url();

        wp_send_json_success(array('auth_url' => $auth_url));
    }

    private function check_nonce() {
        check_ajax_referer('kk_pinterest_nonce', 'nonce');
    }

    private function check_permissions() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'kinderkleurplaten-pinterest')), 403);
        }
    }

    public function test_connection() {
        $this->check_nonce();
        $this->check_permissions();

        $oauth = $this->plugin->get_oauth();
        $result = $oauth->test_connection();

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success($result);
    }

    public function sync_boards() {
        $this->check_nonce();
        $this->check_permissions();

        $board_manager = $this->plugin->get_board_manager();
        $result = $board_manager->sync_boards(true);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'boards' => $result,
            'count' => count($result),
        ));
    }

    public function get_boards() {
        $this->check_nonce();
        $this->check_permissions();

        $board_manager = $this->plugin->get_board_manager();
        
        $args = array(
            'per_page' => isset($_POST['per_page']) ? absint($_POST['per_page']) : 50,
            'page' => isset($_POST['page']) ? absint($_POST['page']) : 1,
            'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
            'privacy' => isset($_POST['privacy']) ? sanitize_text_field($_POST['privacy']) : '',
            'archived' => isset($_POST['archived']) ? (int) $_POST['archived'] : '',
            'orderby' => isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'name',
            'order' => isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'ASC',
        );

        $result = $board_manager->get_local_boards($args);
        wp_send_json_success($result);
    }

    public function create_board() {
        $this->check_nonce();
        $this->check_permissions();

        $board_manager = $this->plugin->get_board_manager();

        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'description' => isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '',
            'privacy' => isset($_POST['privacy']) ? sanitize_text_field($_POST['privacy']) : 'public',
        );

        $result = $board_manager->create_board($data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success($result);
    }

    public function update_board() {
        $this->check_nonce();
        $this->check_permissions();

        $board_manager = $this->plugin->get_board_manager();

        $board_id = sanitize_text_field($_POST['board_id']);
        $data = array();

        if (isset($_POST['name'])) {
            $data['name'] = sanitize_text_field($_POST['name']);
        }
        if (isset($_POST['description'])) {
            $data['description'] = sanitize_textarea_field($_POST['description']);
        }
        if (isset($_POST['privacy'])) {
            $data['privacy'] = sanitize_text_field($_POST['privacy']);
        }

        $result = $board_manager->update_board($board_id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success($result);
    }

    public function delete_board() {
        $this->check_nonce();
        $this->check_permissions();

        $board_manager = $this->plugin->get_board_manager();
        $board_id = sanitize_text_field($_POST['board_id']);

        $result = $board_manager->delete_board($board_id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('deleted' => true));
    }

    public function get_board_sections() {
        $this->check_nonce();
        $this->check_permissions();

        $board_manager = $this->plugin->get_board_manager();
        $board_id = sanitize_text_field($_POST['board_id']);

        $result = $board_manager->get_board_sections($board_id);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success($result);
    }

    public function create_board_section() {
        $this->check_nonce();
        $this->check_permissions();

        $board_manager = $this->plugin->get_board_manager();
        $board_id = sanitize_text_field($_POST['board_id']);
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
        );

        $result = $board_manager->create_board_section($board_id, $data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success($result);
    }

    public function publish_pin() {
        $this->check_nonce();
        $this->check_permissions();

        $pin_publisher = $this->plugin->get_pin_publisher();

        $data = array(
            'board_id' => sanitize_text_field($_POST['board_id']),
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'link' => esc_url_raw($_POST['link']),
            'image_url' => esc_url_raw($_POST['image_url']),
            'board_section_id' => isset($_POST['board_section_id']) ? sanitize_text_field($_POST['board_section_id']) : '',
            'alt_text' => isset($_POST['alt_text']) ? sanitize_text_field($_POST['alt_text']) : '',
        );

        $result = $pin_publisher->publish_pin($data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success($result);
    }

    public function publish_from_post() {
        $this->check_nonce();
        $this->check_permissions();

        $pin_publisher = $this->plugin->get_pin_publisher();

        $post_id = absint($_POST['post_id']);
        $board_id = isset($_POST['board_id']) ? sanitize_text_field($_POST['board_id']) : '';
        $board_section_id = isset($_POST['board_section_id']) ? sanitize_text_field($_POST['board_section_id']) : '';

        $custom_data = array();
        if (isset($_POST['title'])) {
            $custom_data['title'] = sanitize_text_field($_POST['title']);
        }
        if (isset($_POST['description'])) {
            $custom_data['description'] = sanitize_textarea_field($_POST['description']);
        }
        if (isset($_POST['image_url'])) {
            $custom_data['image_url'] = esc_url_raw($_POST['image_url']);
        }
        if (isset($_POST['link'])) {
            $custom_data['link'] = esc_url_raw($_POST['link']);
        }
        if (isset($_POST['alt_text'])) {
            $custom_data['alt_text'] = sanitize_text_field($_POST['alt_text']);
        }

        $result = $pin_publisher->publish_from_post($post_id, $board_id, $board_section_id, $custom_data);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success($result);
    }

    public function bulk_publish() {
        $this->check_nonce();
        $this->check_permissions();

        $pin_publisher = $this->plugin->get_pin_publisher();

        $post_ids = isset($_POST['post_ids']) ? array_map('absint', (array) $_POST['post_ids']) : array();
        $board_id = isset($_POST['board_id']) ? sanitize_text_field($_POST['board_id']) : '';
        $board_section_id = isset($_POST['board_section_id']) ? sanitize_text_field($_POST['board_section_id']) : '';
        $delay = isset($_POST['delay']) ? absint($_POST['delay']) : 2;

        if (empty($post_ids)) {
            wp_send_json_error(array('message' => __('No posts selected', 'kinderkleurplaten-pinterest')));
        }

        $results = $pin_publisher->publish_multiple($post_ids, $board_id, $board_section_id, $delay);

        wp_send_json_success(array('results' => $results));
    }

    public function get_history() {
        $this->check_nonce();
        $this->check_permissions();

        $history = $this->plugin->get_history();

        $args = array(
            'per_page' => isset($_POST['per_page']) ? absint($_POST['per_page']) : 50,
            'page' => isset($_POST['page']) ? absint($_POST['page']) : 1,
            'action' => isset($_POST['action_filter']) ? sanitize_text_field($_POST['action_filter']) : '',
            'status' => isset($_POST['status_filter']) ? sanitize_text_field($_POST['status_filter']) : '',
            'post_id' => isset($_POST['post_id']) ? absint($_POST['post_id']) : 0,
            'board_id' => isset($_POST['board_id']) ? sanitize_text_field($_POST['board_id']) : '',
            'search' => isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '',
            'orderby' => isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'created_at',
            'order' => isset($_POST['order']) ? sanitize_text_field($_POST['order']) : 'DESC',
        );

        $result = $history->get_history($args);
        wp_send_json_success($result);
    }

    public function clear_history() {
        $this->check_nonce();
        $this->check_permissions();

        $history = $this->plugin->get_history();
        $result = $history->clear_history();

        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to clear history', 'kinderkleurplaten-pinterest')));
        }

        wp_send_json_success(array('cleared' => true));
    }

    public function delete_history_item() {
        $this->check_nonce();
        $this->check_permissions();

        $history = $this->plugin->get_history();
        $id = absint($_POST['id']);

        $result = $history->delete_record($id);

        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to delete history item', 'kinderkleurplaten-pinterest')));
        }

        wp_send_json_success(array('deleted' => true));
    }

    public function save_settings() {
        $this->check_nonce();
        $this->check_permissions();

        $api = $this->plugin->get_api();

        $app_id = sanitize_text_field($_POST['app_id']);
        $app_secret = sanitize_text_field($_POST['app_secret']);
        $default_image = esc_url_raw($_POST['default_image']);

        // Log the save attempt for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('KK Pinterest save_settings: app_id=' . $app_id . ' default_image=' . $default_image);
        }

        $api->update_credentials($app_id, $app_secret);

        if ($default_image) {
            update_option('kk_pinterest_default_image', $default_image);
        }

        // Verify the credentials were actually saved
        $saved_app_id = get_option('kk_pinterest_app_id');
        $saved_app_secret = get_option('kk_pinterest_app_secret');

        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('KK Pinterest save_settings: verified app_id=' . $saved_app_id);
        }

        wp_send_json_success(array(
            'saved' => true,
            'app_id' => $saved_app_id,
            'app_secret_set' => !empty($saved_app_secret),
        ));
    }

    public function get_post_data() {
        $this->check_nonce();
        $this->check_permissions();

        $post_id = absint($_POST['post_id']);
        $post = get_post($post_id);

        if (!$post) {
            wp_send_json_error(array('message' => __('Post not found', 'kinderkleurplaten-pinterest')));
        }

        $pin_publisher = $this->plugin->get_pin_publisher();

        $data = array(
            'id' => $post->ID,
            'title' => get_the_title($post),
            'excerpt' => get_the_excerpt($post),
            'permalink' => get_permalink($post),
            'image_url' => $pin_publisher->get_post_image_url($post) ?: '',
            'thumbnail' => has_post_thumbnail($post) ? get_the_post_thumbnail_url($post, 'thumbnail') : '',
        );

        wp_send_json_success($data);
    }

    public function get_posts() {
        $this->check_nonce();
        $this->check_permissions();

        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 20;
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'kleurplaten';

        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'orderby' => 'date',
            'order' => 'DESC',
        );

        if (!empty($search)) {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);

        $posts = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post = $query->post;
                $pin_publisher = $this->plugin->get_pin_publisher();
                
                $posts[] = array(
                    'id' => $post->ID,
                    'title' => get_the_title($post),
                    'excerpt' => get_the_excerpt($post),
                    'permalink' => get_permalink($post),
                    'image_url' => $pin_publisher->get_post_image_url($post) ?: '',
                    'thumbnail' => has_post_thumbnail($post) ? get_the_post_thumbnail_url($post, 'thumbnail') : '',
                );
            }
        }
        wp_reset_postdata();

        wp_send_json_success(array('posts' => $posts));
    }

    public function set_default_board() {
        $this->check_nonce();
        $this->check_permissions();

        $board_manager = $this->plugin->get_board_manager();
        $board_id = sanitize_text_field($_POST['board_id']);

        $result = $board_manager->set_default_board($board_id);

        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to set default board', 'kinderkleurplaten-pinterest')));
        }

        wp_send_json_success(array('default_board' => $board_id));
    }

    public function refresh_token() {
        $this->check_nonce();
        $this->check_permissions();

        $api = $this->plugin->get_api();
        $result = $api->refresh_access_token();

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array('refreshed' => true));
    }

    public function disconnect() {
        $this->check_nonce();
        $this->check_permissions();

        delete_option('kk_pinterest_access_token');
        delete_option('kk_pinterest_refresh_token');
        delete_option('kk_pinterest_token_expires_at');
        delete_option('kk_pinterest_oauth_state');

        $api = $this->plugin->get_api();
        $api->update_credentials(
            get_option('kk_pinterest_app_id', ''),
            get_option('kk_pinterest_app_secret', '')
        );

        wp_send_json_success(array('disconnected' => true));
    }

    public function save_advanced() {
        $this->check_nonce();
        $this->check_permissions();

        $settings = array(
            'history_retention' => isset($_POST['settings']['history_retention']) ? absint($_POST['settings']['history_retention']) : 30,
            'debug_logging' => isset($_POST['settings']['debug_logging']) ? (int) $_POST['settings']['debug_logging'] : 0,
            'request_timeout' => isset($_POST['settings']['request_timeout']) ? absint($_POST['settings']['request_timeout']) : 30,
        );

        update_option('kk_pinterest_history_retention', $settings['history_retention']);
        update_option('kk_pinterest_debug_logging', $settings['debug_logging']);
        update_option('kk_pinterest_request_timeout', $settings['request_timeout']);

        wp_send_json_success(array('saved' => true));
    }

    public function reset_plugin() {
        $this->check_nonce();
        $this->check_permissions();

        delete_option('kk_pinterest_app_id');
        delete_option('kk_pinterest_app_secret');
        delete_option('kk_pinterest_access_token');
        delete_option('kk_pinterest_refresh_token');
        delete_option('kk_pinterest_token_expires_at');
        delete_option('kk_pinterest_oauth_state');
        delete_option('kk_pinterest_default_image');
        delete_option('kk_pinterest_auto_publish');
        delete_option('kk_pinterest_history_retention');
        delete_option('kk_pinterest_debug_logging');
        delete_option('kk_pinterest_request_timeout');
        delete_option('kk_pinterest_last_sync');

        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}kk_pinterest_history");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}kk_pinterest_boards");

        wp_send_json_success(array('reset' => true));
    }
}