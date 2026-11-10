<?php
/**
 * Pinterest Board Manager
 * Manages board synchronization, local storage, and board/section operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Pinterest_Board_Manager {

    const TABLE_NAME = 'kk_pinterest_boards';
    const CACHE_KEY = 'kk_pinterest_boards_cache';
    const CACHE_TTL = 3600;

    private $api;
    private $table_name;

    public function __construct($api) {
        $this->api = $api;
        global $wpdb;
        $this->table_name = $wpdb->prefix . self::TABLE_NAME;
    }

    public function create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $this->table_name;

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            pinterest_board_id varchar(100) NOT NULL,
            name varchar(255) NOT NULL,
            description text DEFAULT NULL,
            privacy varchar(20) DEFAULT 'public',
            owner_id varchar(100) DEFAULT NULL,
            owner_username varchar(100) DEFAULT NULL,
            pin_count int(11) DEFAULT 0,
            follower_count int(11) DEFAULT 0,
            is_archived tinyint(1) DEFAULT 0,
            is_default tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT NULL,
            updated_at datetime DEFAULT NULL,
            synced_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY pinterest_board_id (pinterest_board_id),
            KEY name (name),
            KEY privacy (privacy),
            KEY synced_at (synced_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function sync_boards($force = false) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        if (!$force && !$this->should_sync()) {
            return $this->get_cached_boards();
        }

        $all_boards = array();
        $bookmark = null;

        do {
            $params = array('page_size' => 100);
            if ($bookmark) {
                $params['bookmark'] = $bookmark;
            }

            $result = $this->api->get_boards($params);

            if (is_wp_error($result)) {
                return $result;
            }

            if (!empty($result['items'])) {
                foreach ($result['items'] as $board) {
                    $this->save_board($board);
                    $all_boards[] = $this->format_board($board);
                }
            }

            $bookmark = $result['bookmark'] ?? null;

        } while ($bookmark);

        $this->set_cache($all_boards);
        update_option('kk_pinterest_last_sync', time());

        return $all_boards;
    }

    private function should_sync() {
        $last_sync = get_option('kk_pinterest_last_sync', 0);
        return (time() - $last_sync) > self::CACHE_TTL;
    }

    private function get_cached_boards() {
        $cached = get_transient(self::CACHE_KEY);
        if ($cached !== false) {
            return $cached;
        }
        return $this->get_local_boards();
    }

    private function set_cache($boards) {
        set_transient(self::CACHE_KEY, $boards, self::CACHE_TTL);
    }

    private function save_board($board) {
        global $wpdb;

        $data = array(
            'pinterest_board_id' => sanitize_text_field($board['id']),
            'name' => sanitize_text_field($board['name']),
            'description' => isset($board['description']) ? sanitize_textarea_field($board['description']) : '',
            'privacy' => isset($board['privacy']) ? sanitize_text_field($board['privacy']) : 'public',
            'owner_id' => isset($board['owner']['id']) ? sanitize_text_field($board['owner']['id']) : '',
            'owner_username' => isset($board['owner']['username']) ? sanitize_text_field($board['owner']['username']) : '',
            'pin_count' => isset($board['pin_count']) ? absint($board['pin_count']) : 0,
            'follower_count' => isset($board['follower_count']) ? absint($board['follower_count']) : 0,
            'is_archived' => isset($board['is_archived']) ? (int) $board['is_archived'] : 0,
            'is_default' => 0,
            'created_at' => isset($board['created_at']) ? sanitize_text_field($board['created_at']) : null,
            'updated_at' => isset($board['updated_at']) ? sanitize_text_field($board['updated_at']) : null,
        );

        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE pinterest_board_id = %s",
            $data['pinterest_board_id']
        ));

        if ($existing) {
            $wpdb->update($this->table_name, $data, array('id' => $existing->id));
        } else {
            $wpdb->insert($this->table_name, $data);
        }

        delete_transient(self::CACHE_KEY);
    }

    private function format_board($board) {
        return array(
            'id' => $board['id'],
            'name' => $board['name'],
            'description' => $board['description'] ?? '',
            'privacy' => $board['privacy'] ?? 'public',
            'pin_count' => $board['pin_count'] ?? 0,
            'follower_count' => $board['follower_count'] ?? 0,
            'is_archived' => $board['is_archived'] ?? false,
            'owner' => $board['owner'] ?? array(),
            'created_at' => $board['created_at'] ?? null,
            'updated_at' => $board['updated_at'] ?? null,
        );
    }

    public function get_local_boards($args = array()) {
        global $wpdb;

        $defaults = array(
            'per_page' => 50,
            'page' => 1,
            'search' => '',
            'privacy' => '',
            'archived' => '',
            'orderby' => 'name',
            'order' => 'ASC',
        );

        $args = wp_parse_args($args, $defaults);

        $where = array();
        $where_sql = '';

        if (!empty($args['search'])) {
            $search = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = $wpdb->prepare('name LIKE %s', $search);
        }

        if (!empty($args['privacy'])) {
            $where[] = $wpdb->prepare('privacy = %s', $args['privacy']);
        }

        if ($args['archived'] !== '') {
            $where[] = $wpdb->prepare('is_archived = %d', (int) $args['archived']);
        }

        if (!empty($where)) {
            $where_sql = 'WHERE ' . implode(' AND ', $where);
        }

        $orderby = in_array($args['orderby'], array('name', 'pin_count', 'follower_count', 'created_at', 'synced_at')) ? $args['orderby'] : 'name';
        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

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

    public function get_board($board_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE pinterest_board_id = %s",
            sanitize_text_field($board_id)
        ), ARRAY_A);
    }

    public function get_board_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            absint($id)
        ), ARRAY_A);
    }

    public function create_board($data) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        $result = $this->api->create_board($data);

        if (is_wp_error($result)) {
            return $result;
        }

        $this->save_board($result);
        delete_transient(self::CACHE_KEY);

        return $this->format_board($result);
    }

    public function update_board($board_id, $data) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        $result = $this->api->update_board($board_id, $data);

        if (is_wp_error($result)) {
            return $result;
        }

        $this->save_board($result);
        delete_transient(self::CACHE_KEY);

        return $this->format_board($result);
    }

    public function delete_board($board_id) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        $result = $this->api->delete_board($board_id);

        if (is_wp_error($result)) {
            return $result;
        }

        global $wpdb;
        $wpdb->delete($this->table_name, array('pinterest_board_id' => sanitize_text_field($board_id)));
        delete_transient(self::CACHE_KEY);

        return true;
    }

    public function get_board_sections($board_id) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        return $this->api->get_board_sections($board_id);
    }

    public function create_board_section($board_id, $data) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        return $this->api->create_board_section($board_id, $data);
    }

    public function get_default_board() {
        global $wpdb;
        $board = $wpdb->get_row("SELECT * FROM {$this->table_name} WHERE is_default = 1 LIMIT 1", ARRAY_A);
        
        if (!$board) {
            $boards = $this->get_local_boards(array('per_page' => 1));
            if (!empty($boards['items'])) {
                $board = $boards['items'][0];
                $this->set_default_board($board['pinterest_board_id']);
            }
        }

        return $board;
    }

    public function set_default_board($board_id) {
        global $wpdb;
        $wpdb->update($this->table_name, array('is_default' => 0), array('is_default' => 1));
        return $wpdb->update($this->table_name, array('is_default' => 1), array('pinterest_board_id' => sanitize_text_field($board_id)));
    }

    public function get_boards_for_select($include_archived = false) {
        global $wpdb;
        $where = $include_archived ? '' : 'WHERE is_archived = 0';
        return $wpdb->get_results("SELECT pinterest_board_id as id, name, pin_count FROM {$this->table_name} $where ORDER BY is_default DESC, name ASC", ARRAY_A);
    }
}