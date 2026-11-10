<?php
/**
 * Pinterest API v5 Client
 * Handles all API communication with Pinterest v5 endpoints
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Pinterest_API {

    const API_BASE_URL = 'https://api.pinterest.com/v5/';
    const OAUTH_URL = 'https://www.pinterest.com/oauth/';
    const TOKEN_URL = 'https://api.pinterest.com/v5/oauth/token';

    private $app_id;
    private $app_secret;
    private $access_token;
    private $refresh_token;
    private $token_expires_at;

    public function __construct() {
        $this->load_credentials();
    }

    private function log_debug($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('KK Pinterest API: ' . $message);
        }
    }

    private function load_credentials() {
        $this->app_id = get_option('kk_pinterest_app_id', '');
        $this->app_secret = get_option('kk_pinterest_app_secret', '');
        $this->access_token = get_option('kk_pinterest_access_token', '');
        $this->refresh_token = get_option('kk_pinterest_refresh_token', '');
        $this->token_expires_at = get_option('kk_pinterest_token_expires_at', 0);
    }

    public function is_configured() {
        return !empty($this->app_id) && !empty($this->app_secret);
    }

    public function is_authenticated() {
        return !empty($this->access_token) && $this->is_token_valid();
    }

    private function is_token_valid() {
        if (empty($this->token_expires_at)) {
            return false;
        }
        return time() < ($this->token_expires_at - 300);
    }

    public function get_app_id() {
        return $this->app_id;
    }

    public function get_access_token() {
        if (!$this->is_token_valid()) {
            $this->refresh_access_token();
        }
        return $this->access_token;
    }

    public function refresh_access_token() {
        if (empty($this->refresh_token)) {
            $this->log_debug('Refresh token is empty, cannot refresh');
            return new WP_Error('no_refresh_token', __('No refresh token available. Please re-authorize.', 'kinderkleurplaten-pinterest'));
        }

        $this->log_debug('Refreshing access token');

        $response = wp_remote_post(self::TOKEN_URL, array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode($this->app_id . ':' . $this->app_secret),
            ),
            'body' => array(
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refresh_token,
            ),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            $this->log_debug('wp_remote_post error during refresh: ' . $response->get_error_message());
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $status_code = wp_remote_retrieve_response_code($response);

        $this->log_debug('Token refresh response: HTTP ' . $status_code . ' body=' . substr($body, 0, 500));

        if (empty($data['access_token'])) {
            $error_desc = $data['error_description'] ?? ($data['error'] ?? 'Unknown error');
            $this->log_debug('Token refresh failed: ' . $error_desc);
            return new WP_Error('token_refresh_failed', __('Failed to refresh access token: ', 'kinderkleurplaten-pinterest') . $error_desc);
        }

        $this->access_token = $data['access_token'];
        $this->refresh_token = $data['refresh_token'] ?? $this->refresh_token;
        $expires_in = $data['expires_in'] ?? 3600;
        $this->token_expires_at = time() + $expires_in;

        update_option('kk_pinterest_access_token', $this->access_token);
        update_option('kk_pinterest_refresh_token', $this->refresh_token);
        update_option('kk_pinterest_token_expires_at', $this->token_expires_at);

        $this->log_debug('Token refreshed successfully, expires at ' . $this->token_expires_at);

        return true;
    }

    private function request($method, $endpoint, $params = array(), $body = null) {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            $this->log_debug('Request failed - token error: ' . $token->get_error_message());
            return $token;
        }

        $url = self::API_BASE_URL . ltrim($endpoint, '/');

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $headers = array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        );

        $args = array(
            'method' => $method,
            'headers' => $headers,
            'timeout' => 30,
        );

        if ($method !== 'GET' && $body !== null) {
            $args['body'] = wp_json_encode($body);
        }

        $this->log_debug("Request: $method $endpoint");

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $this->log_debug('wp_remote_request error: ' . $response->get_error_message());
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        $this->log_debug("Response: HTTP $status_code body=" . substr($response_body, 0, 500));

        if ($status_code === 401) {
            $this->log_debug('Got 401, attempting token refresh');
            $refresh_result = $this->refresh_access_token();
            if (is_wp_error($refresh_result)) {
                $this->log_debug('Token refresh failed: ' . $refresh_result->get_error_message());
                return $refresh_result;
            }
            $headers['Authorization'] = 'Bearer ' . $this->get_access_token();
            $args['headers'] = $headers;
            $response = wp_remote_request($url, $args);
            $status_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $data = json_decode($response_body, true);
            $this->log_debug("Retry response: HTTP $status_code body=" . substr($response_body, 0, 500));
        }

        if ($status_code >= 400) {
            $error_msg = $data['message'] ?? $data['error_description'] ?? 'API Error';
            $this->log_debug("API error: HTTP $status_code - $error_msg");
            return new WP_Error('api_error', sprintf(__('Pinterest API error (%d): %s', 'kinderkleurplaten-pinterest'), $status_code, $error_msg), $data);
        }

        return $data;
    }

    public function get_user_account() {
        return $this->request('GET', 'user_account');
    }

    public function get_boards($params = array()) {
        $defaults = array(
            'bookmark' => '',
            'page_size' => 25,
        );
        $params = array_merge($defaults, $params);
        return $this->request('GET', 'boards', $params);
    }

    public function get_board($board_id) {
        return $this->request('GET', "boards/{$board_id}");
    }

    public function get_board_sections($board_id, $params = array()) {
        $defaults = array(
            'bookmark' => '',
            'page_size' => 25,
        );
        $params = array_merge($defaults, $params);
        return $this->request('GET', "boards/{$board_id}/sections", $params);
    }

    public function create_board($data) {
        return $this->request('POST', 'boards', array(), $data);
    }

    public function update_board($board_id, $data) {
        return $this->request('PATCH', "boards/{$board_id}", array(), $data);
    }

    public function delete_board($board_id) {
        return $this->request('DELETE', "boards/{$board_id}");
    }

    public function create_board_section($board_id, $data) {
        return $this->request('POST', "boards/{$board_id}/sections", array(), $data);
    }

    public function get_pins($params = array()) {
        $defaults = array(
            'bookmark' => '',
            'page_size' => 25,
        );
        $params = array_merge($defaults, $params);
        return $this->request('GET', 'pins', $params);
    }

    public function get_pin($pin_id) {
        return $this->request('GET', "pins/{$pin_id}");
    }

    public function create_pin($data) {
        return $this->request('POST', 'pins', array(), $data);
    }

    public function create_pin_for_board($board_id, $data) {
        return $this->request('POST', "boards/{$board_id}/pins", array(), $data);
    }

    public function update_pin($pin_id, $data) {
        return $this->request('PATCH', "pins/{$pin_id}", array(), $data);
    }

    public function delete_pin($pin_id) {
        return $this->request('DELETE', "pins/{$pin_id}");
    }

    public function get_boards_for_pin($pin_id, $params = array()) {
        return $this->request('GET', "pins/{$pin_id}/boards", $params);
    }

    public function get_oauth_url($redirect_uri, $scopes = array(), $state = '') {
        $default_scopes = array('boards:read', 'boards:write', 'pins:read', 'pins:write', 'user_accounts:read');
        $scopes = empty($scopes) ? $default_scopes : $scopes;
        
        $params = array(
            'response_type' => 'code',
            'client_id' => $this->app_id,
            'redirect_uri' => $redirect_uri,
            'scope' => implode(',', $scopes),
        );
        
        if (!empty($state)) {
            $params['state'] = $state;
        }
        
        return self::OAUTH_URL . '?' . http_build_query($params);
    }

    public function exchange_code_for_token($code, $redirect_uri) {
        $this->log_debug('Exchanging OAuth code for token. redirect_uri=' . $redirect_uri . ' app_id=' . $this->app_id);

        $response = wp_remote_post(self::TOKEN_URL, array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode($this->app_id . ':' . $this->app_secret),
            ),
            'body' => array(
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirect_uri,
            ),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            $this->log_debug('wp_remote_post error during token exchange: ' . $response->get_error_message());
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $status_code = wp_remote_retrieve_response_code($response);

        $this->log_debug('Token exchange response: HTTP ' . $status_code . ' body=' . substr($body, 0, 500));

        if (empty($data['access_token'])) {
            $error_desc = $data['error_description'] ?? ($data['error'] ?? 'Unknown error');
            $this->log_debug('Token exchange failed: ' . $error_desc);
            return new WP_Error('token_exchange_failed', __('Failed to exchange code for token: ', 'kinderkleurplaten-pinterest') . $error_desc);
        }

        $this->access_token = $data['access_token'];
        $this->refresh_token = $data['refresh_token'] ?? '';
        $expires_in = $data['expires_in'] ?? 3600;
        $this->token_expires_at = time() + $expires_in;

        update_option('kk_pinterest_access_token', $this->access_token);
        update_option('kk_pinterest_refresh_token', $this->refresh_token);
        update_option('kk_pinterest_token_expires_at', $this->token_expires_at);

        $this->log_debug('Token exchanged successfully. expires_in=' . $expires_in . ' has_refresh_token=' . (!empty($this->refresh_token) ? 'yes' : 'no'));

        return $data;
    }

    public function update_credentials($app_id, $app_secret, $access_token = '', $refresh_token = '') {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
        $this->token_expires_at = $access_token ? (time() + 3600) : 0;

        update_option('kk_pinterest_app_id', $app_id);
        update_option('kk_pinterest_app_secret', $app_secret);
        if ($access_token) {
            update_option('kk_pinterest_access_token', $access_token);
        }
        if ($refresh_token) {
            update_option('kk_pinterest_refresh_token', $refresh_token);
        }
        update_option('kk_pinterest_token_expires_at', $this->token_expires_at);
    }

    public function get_credentials() {
        return array(
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret,
            'access_token' => $this->access_token,
            'refresh_token' => $this->refresh_token,
            'token_expires_at' => $this->token_expires_at,
        );
    }
}