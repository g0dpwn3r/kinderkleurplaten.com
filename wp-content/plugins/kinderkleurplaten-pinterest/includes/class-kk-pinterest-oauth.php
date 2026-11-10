<?php
/**
 * Pinterest OAuth Handler
 * Manages OAuth flow, token storage, and authorization state
 */

if (!defined('ABSPATH')) {
    exit;
}

class KK_Pinterest_OAuth {

    private $api;

    public function __construct() {
        $this->api = kk_pinterest()->get_api();
        add_action('admin_init', array($this, 'handle_oauth_callback'));
        add_action('admin_post_kk_pinterest_disconnect', array($this, 'disconnect'));
    }

    public function create_options() {
        add_option('kk_pinterest_app_id', '');
        add_option('kk_pinterest_app_secret', '');
        add_option('kk_pinterest_access_token', '');
        add_option('kk_pinterest_refresh_token', '');
        add_option('kk_pinterest_token_expires_at', 0);
        add_option('kk_pinterest_oauth_state', '');
    }

    public function handle_oauth_callback() {
        // Pinterest redirects back to the clean redirect URI (admin.php without query params)
        // We detect the callback by checking for the presence of 'code' or 'error' in $_GET
        // and the 'kk_pinterest_oauth' action set during the authorization request.

        if (!isset($_GET['code']) && !isset($_GET['error'])) {
            return;
        }

        // Only process if this is our OAuth callback (not just any admin.php page with query params)
        // We verify by checking that we have a stored OAuth state, meaning an auth flow was initiated.
        $stored_state = get_option('kk_pinterest_oauth_state', '');
        if (empty($stored_state) && !isset($_GET['error'])) {
            return;
        }

        if (isset($_GET['error'])) {
            $error = sanitize_text_field($_GET['error']);
            $error_desc = isset($_GET['error_description']) ? sanitize_text_field($_GET['error_description']) : '';
            $this->log_error('OAuth error from Pinterest: ' . $error . ' - ' . $error_desc);
            add_settings_error('kk_pinterest_oauth', 'oauth_error', sprintf(__('OAuth error: %s - %s', 'kinderkleurplaten-pinterest'), $error, $error_desc), 'error');
            // Redirect to settings page to show the error
            $this->redirect_to_settings();
            return;
        }

        $code = sanitize_text_field($_GET['code']);
        $state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';

        // Log state comparison for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('KK Pinterest OAuth: state received=' . substr($state, 0, 20) . ' stored=' . substr($stored_state, 0, 20) . ' match=' . ($state === $stored_state ? 'yes' : 'no'));
        }

        if (!empty($stored_state) && $state !== $stored_state) {
            $this->log_error('OAuth state mismatch. Received: ' . substr($state, 0, 20) . ' Stored: ' . substr($stored_state, 0, 20));
            add_settings_error('kk_pinterest_oauth', 'state_mismatch', __('Invalid OAuth state. Please try again.', 'kinderkleurplaten-pinterest'), 'error');
            $this->redirect_to_settings();
            return;
        }

        $redirect_uri = $this->get_redirect_uri();
        $this->log_error('Exchanging OAuth code for token. redirect_uri=' . $redirect_uri);

        $result = $this->api->exchange_code_for_token($code, $redirect_uri);

        if (is_wp_error($result)) {
            $this->log_error('Token exchange failed: ' . $result->get_error_message());
            add_settings_error('kk_pinterest_oauth', 'token_exchange', $result->get_error_message(), 'error');
            $this->redirect_to_settings();
            return;
        }

        delete_option('kk_pinterest_oauth_state');
        add_settings_error('kk_pinterest_oauth', 'oauth_success', __('Successfully connected to Pinterest!', 'kinderkleurplaten-pinterest'), 'updated');
        $this->redirect_to_settings();
    }

    /**
     * Redirect to the settings page after OAuth callback processing.
     */
    private function redirect_to_settings() {
        wp_safe_redirect(admin_url('admin.php?page=kinderkleurplaten-pinterest-settings'));
        exit;
    }

    private function log_error($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('KK Pinterest OAuth: ' . $message);
        }
    }

    public function get_redirect_uri() {
        // Pinterest requires redirect URIs without query parameters.
        // Use clean admin.php URL; after callback we redirect to the settings page.
        return admin_url('admin.php');
    }

    public function get_authorization_url() {
        $state = wp_create_nonce('kk_pinterest_oauth_' . time());
        update_option('kk_pinterest_oauth_state', $state);
        return $this->api->get_oauth_url($this->get_redirect_uri(), array(), $state);
    }

    public function disconnect() {
        check_admin_referer('kk_pinterest_disconnect');

        delete_option('kk_pinterest_access_token');
        delete_option('kk_pinterest_refresh_token');
        delete_option('kk_pinterest_token_expires_at');
        delete_option('kk_pinterest_oauth_state');

        $this->api->update_credentials(
            get_option('kk_pinterest_app_id', ''),
            get_option('kk_pinterest_app_secret', '')
        );

        wp_safe_redirect(add_query_arg('disconnected', '1', $this->get_redirect_uri()));
        exit;
    }

    public function get_connection_status() {
        $credentials = $this->api->get_credentials();
        
        if (empty($credentials['app_id']) || empty($credentials['app_secret'])) {
            return array(
                'status' => 'not_configured',
                'message' => __('App credentials not configured', 'kinderkleurplaten-pinterest'),
            );
        }

        if (empty($credentials['access_token'])) {
            return array(
                'status' => 'not_connected',
                'message' => __('Not connected to Pinterest', 'kinderkleurplaten-pinterest'),
                'auth_url' => $this->get_authorization_url(),
            );
        }

        if (!$this->api->is_token_valid()) {
            return array(
                'status' => 'token_expired',
                'message' => __('Access token expired. Attempting refresh...', 'kinderkleurplaten-pinterest'),
                'auth_url' => $this->get_authorization_url(),
            );
        }

        return array(
            'status' => 'connected',
            'message' => __('Connected to Pinterest', 'kinderkleurplaten-pinterest'),
            'token_expires' => $credentials['token_expires_at'],
        );
    }

    public function test_connection() {
        if (!$this->api->is_authenticated()) {
            return new WP_Error('not_authenticated', __('Not authenticated with Pinterest', 'kinderkleurplaten-pinterest'));
        }

        $result = $this->api->get_user_account();
        
        if (is_wp_error($result)) {
            return $result;
        }

        return $result;
    }
}