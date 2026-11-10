<?php
/**
 * Pinterest History Logger
 * Logs all pin publishing activities, API calls, and errors
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Pinterest_History {

    const TABLE_NAME = 'kk_pinterest_history';
    const MAX_RECORDS = 5000;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . self::TABLE_NAME;
    }

    public function create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $this->table_name;

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            action varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            pin_id varchar(100) DEFAULT NULL,
            board_id varchar(100) DEFAULT NULL,
            board_section_id varchar(100) DEFAULT NULL,
            post_id bigint(20) unsigned DEFAULT NULL,
            title text DEFAULT NULL,
            description longtext DEFAULT NULL,
            image_url text DEFAULT NULL,
            link text DEFAULT NULL,
            error_message text DEFAULT NULL,
            api_response longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY action (action),
            KEY status (status),
            KEY pin_id (pin_id),
            KEY board_id (board_id),
            KEY post_id (post_id),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        add_option('kk_pinterest_db_version', KK_PINTEREST_DB_VERSION);
    }

    public function log($args) {
        global $wpdb;

        $defaults = array(
            'action' => 'unknown',
            'status' => 'pending',
            'pin_id' => null,
            'board_id' => null,
            'board_section_id' => null,
            'post_id' => null,
            'title' => '',
            'description' => '',
            'image_url' => '',
            'link' => '',
            'error_message' => '',
            'api_response' => '',
        );

        $data = wp_parse_args($args, $defaults);

        $data['action'] = sanitize_text_field($data['action']);
        $data['status'] = sanitize_text_field($data['status']);
        $data['pin_id'] = $data['pin_id'] ? sanitize_text_field($data['pin_id']) : null;
        $data['board_id'] = $data['board_id'] ? sanitize_text_field($data['board_id']) : null;
        $data['board_section_id'] = $data['board_section_id'] ? sanitize_text_field($data['board_section_id']) : null;
        $data['post_id'] = $data['post_id'] ? absint($data['post_id']) : null;
        $data['title'] = sanitize_textarea_field($data['title']);
        $data['description'] = sanitize_textarea_field($data['description']);
        $data['image_url'] = esc_url_raw($data['image_url']);
        $data['link'] = esc_url_raw($data['link']);
        $data['error_message'] = sanitize_textarea_field($data['error_message']);
        $data['api_response'] = is_array($data['api_response']) ? wp_json_encode($data['api_response']) : sanitize_textarea_field($data['api_response']);

        $result = $wpdb->insert($this->table_name, $data);

        if ($result === false) {
            return new WP_Error('db_insert_error', $wpdb->last_error);
        }

        $this->cleanup_old_records();

        return $wpdb->insert_id;
    }

    public function update_status($id, $status, $error_message = '', $api_response = '') {
        global $wpdb;

        $data = array(
            'status' => sanitize_text_field($status),
        );

        if (!empty($error_message)) {
            $data['error_message'] = sanitize_textarea_field($error_message);
        }

        if (!empty($api_response)) {
            $data['api_response'] = is_array($api_response) ? wp_json_encode($api_response) : sanitize_textarea_field($api_response);
        }

        $where = array('id' => absint($id));
        return $wpdb->update($this->table_name, $data, $where);
    }

    public function get_history($args = array()) {
        global $wpdb;

        $defaults = array(
            'per_page' => 50,
            'page' => 1,
            'action' => '',
            'status' => '',
            'post_id' => 0,
            'board_id' => '',
            'search' => '',
            'orderby' => 'created_at',
            'order' => 'DESC',
        );

        $args = wp_parse_args($args, $defaults);

        $where = array();
        $where_sql = '';

        if (!empty($args['action'])) {
            $where[] = $wpdb->prepare('action = %s', $args['action']);
        }

        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare('status = %s', $args['status']);
        }

        if (!empty($args['post_id'])) {
            $where[] = $wpdb->prepare('post_id = %d', $args['post_id']);
        }

        if (!empty($args['board_id'])) {
            $where[] = $wpdb->prepare('board_id = %s', $args['board_id']);
        }

        if (!empty($args['search'])) {
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = $wpdb->prepare('(title LIKE %s OR description LIKE %s OR error_message LIKE %s)', $search, $search, $search);
        }

        if (!empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }

        $orderby = in_array($args['orderby'], array('created_at', 'action', 'status', 'pin_id')) ? $args['orderby'] : 'created_at';
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $offset = ($args['page'] - 1) * $args['per_page'];

        $sql = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} $where_sql ORDER BY $orderby $order LIMIT %d OFFSET %d",
            $args['per_page'],
            $offset
        );

        $results = $wpdb->get_results($sql, ARRAY_A);

        $count_sql = "SELECT COUNT(*) FROM {$this->table_name} $where_sql";
        $total = $wpdb->get_var($count_sql);

        return array(
            'items' => $results,
            'total' => (int) $total,
            'page' => $args['page'],
            'per_page' => $args['per_page'],
            'total_pages' => ceil($total / $args['per_page']),
        );
    }

    public function get_stats() {
        global $wpdb;

        $stats = array(
            'total' => 0,
            'published' => 0,
            'failed' => 0,
            'pending' => 0,
            'by_action' => array(),
            'recent' => array(),
        );

        $stats['total'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        $stats['published'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'published'");
        $stats['failed'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'failed'");
        $stats['pending'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'pending'");

        $by_action = $wpdb->get_results("SELECT action, COUNT(*) as count FROM {$this->table_name} GROUP BY action");
        foreach ($by_action as $row) {
            $stats['by_action'][$row->action] = (int) $row->count;
        }

        $recent = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT 5", ARRAY_A);
        $stats['recent'] = $recent;

        return $stats;
    }

    private function cleanup_old_records() {
        global $wpdb;

        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");

        if ($count > self::MAX_RECORDS) {
            $delete_count = $count - self::MAX_RECORDS;
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE id IN (
                    SELECT id FROM {$this->table_name} ORDER BY created_at ASC LIMIT %d
                )",
                $delete_count
            ));
        }
    }

    public function delete_record($id) {
        global $wpdb;
        return $wpdb->delete($this->table_name, array('id' => absint($id)));
    }

    public function clear_history() {
        global $wpdb;
        return $wpdb->query("TRUNCATE TABLE {$this->table_name}");
    }

    public function get_by_post_id($post_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE post_id = %d ORDER BY created_at DESC",
            absint($post_id)
        ), ARRAY_A);
    }
}