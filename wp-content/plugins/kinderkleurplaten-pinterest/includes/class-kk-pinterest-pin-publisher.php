<?php
/**
 * Pinterest Pin Publisher
 * Handles pin creation, publishing from posts, and bulk operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Pinterest_Pin_Publisher {

    private $api;
    private $history;
    private $board_manager;

    public function __construct($api, $history) {
        $this->api = $api;
        $this->history = $history;
        $this->board_manager = kk_pinterest()->get_board_manager();
    }

    public function publish_from_post($post_id, $board_id = '', $board_section_id = '', $custom_data = array()) {
        $post = get_post($post_id);
        
        if (!$post) {
            return new WP_Error('post_not_found', __('Post not found', 'kinderkleurplaten-pinterest'));
        }

        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        $board_id = $board_id ?: $this->get_default_board_id();
        
        if (!$board_id) {
            return new WP_Error('no_board', __('No board selected', 'kinderkleurplaten-pinterest'));
        }

        $pin_data = $this->prepare_pin_data($post, $custom_data);
        $pin_data['board_id'] = $board_id;
        
        if ($board_section_id) {
            $pin_data['board_section_id'] = $board_section_id;
        }

        $history_id = $this->history->log(array(
            'action' => 'publish_pin',
            'status' => 'pending',
            'post_id' => $post_id,
            'board_id' => $board_id,
            'board_section_id' => $board_section_id,
            'title' => $pin_data['title'],
            'description' => $pin_data['description'],
            'image_url' => $pin_data['media_source']['source_url'] ?? '',
            'link' => $pin_data['link'],
        ));

        $result = $this->api->create_pin($pin_data);

        if (is_wp_error($result)) {
            $this->history->update_status($history_id, 'failed', $result->get_error_message(), $result->get_error_data());
            return $result;
        }

        $this->history->update_status($history_id, 'published', '', $result);

        $pin_id = $result['id'] ?? '';
        if ($pin_id) {
            update_post_meta($post_id, '_kk_pinterest_pin_id', $pin_id);
            update_post_meta($post_id, '_kk_pinterest_board_id', $board_id);
            update_post_meta($post_id, '_kk_pinterest_published_at', current_time('mysql'));
        }

        return array(
            'success' => true,
            'pin_id' => $pin_id,
            'pin_url' => $result['pin_url'] ?? '',
            'history_id' => $history_id,
        );
    }

    public function publish_multiple($post_ids, $board_id = '', $board_section_id = '', $delay = 2) {
        $results = array();
        $board_id = $board_id ?: $this->get_default_board_id();
        
        if (!$board_id) {
            return new WP_Error('no_board', __('No board selected', 'kinderkleurplaten-pinterest'));
        }

        foreach ($post_ids as $post_id) {
            $result = $this->publish_from_post($post_id, $board_id, $board_section_id);
            $results[] = array(
                'post_id' => $post_id,
                'result' => $result,
            );
            
            if ($delay > 0) {
                sleep($delay);
            }
        }

        return $results;
    }

    public function publish_pin($data) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        $board_id = $data['board_id'] ?? $this->get_default_board_id();
        
        if (!$board_id) {
            return new WP_Error('no_board', __('No board selected', 'kinderkleurplaten-pinterest'));
        }

        $pin_data = array(
            'board_id' => $board_id,
            'title' => sanitize_text_field($data['title']),
            'description' => sanitize_textarea_field($data['description']),
            'link' => esc_url_raw($data['link']),
            'media_source' => array(
                'source_type' => 'image_url',
                'source_url' => esc_url_raw($data['image_url']),
            ),
        );

        if (!empty($data['board_section_id'])) {
            $pin_data['board_section_id'] = sanitize_text_field($data['board_section_id']);
        }

        if (!empty($data['alt_text'])) {
            $pin_data['alt_text'] = sanitize_text_field($data['alt_text']);
        }

        $history_id = $this->history->log(array(
            'action' => 'publish_pin',
            'status' => 'pending',
            'board_id' => $board_id,
            'board_section_id' => $data['board_section_id'] ?? '',
            'title' => $pin_data['title'],
            'description' => $pin_data['description'],
            'image_url' => $pin_data['media_source']['source_url'],
            'link' => $pin_data['link'],
        ));

        $result = $this->api->create_pin($pin_data);

        if (is_wp_error($result)) {
            $this->history->update_status($history_id, 'failed', $result->get_error_message(), $result->get_error_data());
            return $result;
        }

        $this->history->update_status($history_id, 'published', '', $result);

        return array(
            'success' => true,
            'pin_id' => $result['id'] ?? '',
            'pin_url' => $result['pin_url'] ?? '',
            'history_id' => $history_id,
        );
    }

    private function prepare_pin_data($post, $custom_data = array()) {
        $title = $custom_data['title'] ?? get_the_title($post);
        $description = $custom_data['description'] ?? $this->generate_description($post);
        $link = $custom_data['link'] ?? get_permalink($post);
        
        $image_url = $custom_data['image_url'] ?? $this->get_post_image_url($post);
        
        if (!$image_url) {
            $image_url = get_option('kk_pinterest_default_image', '');
        }

        $pin_data = array(
            'title' => wp_trim_words($title, 100, ''),
            'description' => wp_trim_words($description, 500, ''),
            'link' => $link,
            'media_source' => array(
                'source_type' => 'image_url',
                'source_url' => $image_url,
            ),
        );

        if (!empty($custom_data['alt_text'])) {
            $pin_data['alt_text'] = sanitize_text_field($custom_data['alt_text']);
        }

        return $pin_data;
    }

    private function generate_description($post) {
        $excerpt = get_the_excerpt($post);
        
        if (!empty($excerpt)) {
            return $excerpt;
        }

        $content = get_the_content('', true, $post);
        $content = wp_strip_all_tags($content);
        
        return wp_trim_words($content, 50, '');
    }

    private function get_post_image_url($post) {
        if (has_post_thumbnail($post)) {
            $image_id = get_post_thumbnail_id($post);
            $image_url = wp_get_attachment_image_url($image_id, 'full');
            return $image_url;
        }

        $content = get_the_content('', true, $post);
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches)) {
            return $matches[1];
        }

        return '';
    }

    private function get_default_board_id() {
        $default_board = $this->board_manager->get_default_board();
        return $default_board ? $default_board['pinterest_board_id'] : '';
    }

    public function update_pin($pin_id, $data) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        $history_id = $this->history->log(array(
            'action' => 'update_pin',
            'status' => 'pending',
            'pin_id' => $pin_id,
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
        ));

        $result = $this->api->update_pin($pin_id, $data);

        if (is_wp_error($result)) {
            $this->history->update_status($history_id, 'failed', $result->get_error_message(), $result->get_error_data());
            return $result;
        }

        $this->history->update_status($history_id, 'published', '', $result);

        return array(
            'success' => true,
            'pin_id' => $pin_id,
        );
    }

    public function delete_pin($pin_id) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        $history_id = $this->history->log(array(
            'action' => 'delete_pin',
            'status' => 'pending',
            'pin_id' => $pin_id,
        ));

        $result = $this->api->delete_pin($pin_id);

        if (is_wp_error($result)) {
            $this->history->update_status($history_id, 'failed', $result->get_error_message(), $result->get_error_data());
            return $result;
        }

        $this->history->update_status($history_id, 'published', '', $result);

        global $wpdb;
        $wpdb->delete($wpdb->postmeta, array(
            'meta_key' => '_kk_pinterest_pin_id',
            'meta_value' => $pin_id,
        ));

        return array(
            'success' => true,
        );
    }

    public function get_pin($pin_id) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        return $this->api->get_pin($pin_id);
    }

    public function get_pins($params = array()) {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        return $this->api->get_pins($params);
    }
}