<?php
/**
 * Settings Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$app_id = get_option('kk_pinterest_app_id', '');
$app_secret = get_option('kk_pinterest_app_secret', '');
$access_token = get_option('kk_pinterest_access_token', '');
$default_image = get_option('kk_pinterest_default_image', '');
$token_expires = get_option('kk_pinterest_token_expires_at', 0);

$is_configured = !empty($app_id) && !empty($app_secret);
$is_connected = !empty($access_token);
$token_expired = $is_connected && $token_expires && $token_expires < time();

$oauth = kk_pinterest()->get_oauth();
$redirect_uri = $oauth->get_redirect_uri();
?>

<div class="wrap kk-pinterest-wrap">
    <h1><?php _e('Pinterest Settings', 'kinderkleurplaten-pinterest'); ?></h1>

    <?php settings_errors('kk_pinterest_oauth'); ?>
    <?php settings_errors('kk_pinterest_settings'); ?>

    <div class="kk-pinterest-settings">
        <!-- App Credentials -->
        <div class="kk-card kk-settings-section">
            <div class="kk-card-header">
                <h2><?php _e('Pinterest App Credentials', 'kinderkleurplaten-pinterest'); ?></h2>
            </div>
            <div class="kk-card-body">
                <?php if (!$is_configured): ?>
                    <div class="notice notice-warning inline">
                        <p><?php _e('Pinterest app credentials not configured. Please enter your App ID and App Secret to enable Pinterest integration.', 'kinderkleurplaten-pinterest'); ?></p>
                    </div>
                <?php endif; ?>

                <form id="kk-credentials-form" class="kk-settings-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="kk-app-id"><?php _e('App ID', 'kinderkleurplaten-pinterest'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text" name="app_id" id="kk-app-id" class="regular-text" value="<?php echo esc_attr($app_id); ?>" required>
                                <p class="description"><?php _e('Your Pinterest App ID from developers.pinterest.com', 'kinderkleurplaten-pinterest'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="kk-app-secret"><?php _e('App Secret', 'kinderkleurplaten-pinterest'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="password" name="app_secret" id="kk-app-secret" class="regular-text" value="<?php echo esc_attr($app_secret); ?>" required autocomplete="off">
                                <p class="description"><?php _e('Your Pinterest App Secret from developers.pinterest.com', 'kinderkleurplaten-pinterest'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary" id="kk-save-credentials"><?php _e('Save Credentials', 'kinderkleurplaten-pinterest'); ?></button>
                        <span class="spinner"></span>
                    </p>
                </form>
            </div>
        </div>

        <!-- Connection Status -->
        <div class="kk-card kk-settings-section">
            <div class="kk-card-header">
                <h2><?php _e('Connection Status', 'kinderkleurplaten-pinterest'); ?></h2>
            </div>
            <div class="kk-card-body">
                <div class="kk-connection-info">
                    <div class="kk-status-indicator">
                        <?php if ($is_connected && !$token_expired): ?>
                            <span class="kk-status-badge kk-status-success">
                                <span class="dashicons dashicons-yes"></span> <?php _e('Connected', 'kinderkleurplaten-pinterest'); ?>
                            </span>
                        <?php elseif ($token_expired): ?>
                            <span class="kk-status-badge kk-status-warning">
                                <span class="dashicons dashicons-warning"></span> <?php _e('Token Expired', 'kinderkleurplaten-pinterest'); ?>
                            </span>
                        <?php else: ?>
                            <span class="kk-status-badge kk-status-info">
                                <span class="dashicons dashicons-no"></span> <?php _e('Not Connected', 'kinderkleurplaten-pinterest'); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($is_connected): ?>
                        <div class="kk-token-info">
                            <?php if ($token_expires): ?>
                                <p><?php printf(__('Token expires: %s', 'kinderkleurplaten-pinterest'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $token_expires)); ?></p>
                            <?php endif; ?>
                            <p class="description"><?php _e('Access token is stored securely in the database.', 'kinderkleurplaten-pinterest'); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="kk-connection-actions">
                        <?php if ($is_configured): ?>
                            <?php if (!$is_connected): ?>
                                <button type="button" class="button button-primary" id="kk-connect-btn">
                                    <span class="dashicons dashicons-plugins"></span> <?php _e('Connect to Pinterest', 'kinderkleurplaten-pinterest'); ?>
                                </button>
                            <?php else: ?>
                                <button type="button" class="button" id="kk-refresh-token-btn" <?php disabled($token_expired); ?>>
                                    <span class="dashicons dashicons-update"></span> <?php _e('Refresh Token', 'kinderkleurplaten-pinterest'); ?>
                                </button>
                                <button type="button" class="button" id="kk-test-connection-btn">
                                    <span class="dashicons dashicons-visibility"></span> <?php _e('Test Connection', 'kinderkleurplaten-pinterest'); ?>
                                </button>
                                <button type="button" class="button" id="kk-disconnect-btn">
                                    <span class="dashicons dashicons-trash"></span> <?php _e('Disconnect', 'kinderkleurplaten-pinterest'); ?>
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="description"><?php _e('Please save your app credentials first to connect to Pinterest.', 'kinderkleurplaten-pinterest'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Default Settings -->
        <div class="kk-card kk-settings-section">
            <div class="kk-card-header">
                <h2><?php _e('Default Settings', 'kinderkleurplaten-pinterest'); ?></h2>
            </div>
            <div class="kk-card-body">
                <form id="kk-defaults-form" class="kk-settings-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="kk-default-image"><?php _e('Default Image URL', 'kinderkleurplaten-pinterest'); ?></label>
                            </th>
                            <td>
                                <input type="url" name="default_image" id="kk-default-image" class="regular-text" value="<?php echo esc_attr($default_image); ?>">
                                <p class="description"><?php _e('Fallback image when post has no featured image', 'kinderkleurplaten-pinterest'); ?></p>
                                <?php if ($default_image): ?>
                                    <div class="kk-image-preview">
                                        <img src="<?php echo esc_url($default_image); ?>" alt="" style="max-width: 200px; height: auto;">
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php _e('Auto-publish on Post Save', 'kinderkleurplaten-pinterest'); ?></label>
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e('Auto-publish on Post Save', 'kinderkleurplaten-pinterest'); ?></span></legend>
                                    <label>
                                        <input type="checkbox" name="auto_publish" id="kk-auto-publish" value="1" <?php checked(get_option('kk_pinterest_auto_publish', 0), 1); ?>>
                                        <?php _e('Automatically publish to Pinterest when a kleurplaat post is published', 'kinderkleurplaten-pinterest'); ?>
                                    </label>
                                    <p class="description"><?php _e('Uses the default board if set', 'kinderkleurplaten-pinterest'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php _e('Default Board for Auto-publish', 'kinderkleurplaten-pinterest'); ?></label>
                            </th>
                            <td>
                                <select name="default_board" id="kk-default-board" class="regular-text" disabled>
                                    <option value=""><?php _e('Select a board after connecting...', 'kinderkleurplaten-pinterest'); ?></option>
                                </select>
                                <p class="description"><?php _e('Will be populated after connecting to Pinterest', 'kinderkleurplaten-pinterest'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary" id="kk-save-defaults"><?php _e('Save Default Settings', 'kinderkleurplaten-pinterest'); ?></button>
                        <span class="spinner"></span>
                    </p>
                </form>
            </div>
        </div>

        <!-- Advanced Settings -->
        <div class="kk-card kk-settings-section">
            <div class="kk-card-header">
                <h2><?php _e('Advanced Settings', 'kinderkleurplaten-pinterest'); ?></h2>
            </div>
            <div class="kk-card-body">
                <form id="kk-advanced-form" class="kk-settings-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label><?php _e('History Retention', 'kinderkleurplaten-pinterest'); ?></label>
                            </th>
                            <td>
                                <select name="history_retention" id="kk-history-retention">
                                    <option value="30" <?php selected(get_option('kk_pinterest_history_retention', 30), 30); ?>><?php _e('30 days', 'kinderkleurplaten-pinterest'); ?></option>
                                    <option value="60" <?php selected(get_option('kk_pinterest_history_retention', 30), 60); ?>><?php _e('60 days', 'kinderkleurplaten-pinterest'); ?></option>
                                    <option value="90" <?php selected(get_option('kk_pinterest_history_retention', 30), 90); ?>><?php _e('90 days', 'kinderkleurplaten-pinterest'); ?></option>
                                    <option value="180" <?php selected(get_option('kk_pinterest_history_retention', 30), 180); ?>><?php _e('180 days', 'kinderkleurplaten-pinterest'); ?></option>
                                    <option value="365" <?php selected(get_option('kk_pinterest_history_retention', 30), 365); ?>><?php _e('1 year', 'kinderkleurplaten-pinterest'); ?></option>
                                    <option value="0" <?php selected(get_option('kk_pinterest_history_retention', 30), 0); ?>><?php _e('Forever (no auto-cleanup)', 'kinderkleurplaten-pinterest'); ?></option>
                                </select>
                                <p class="description"><?php _e('Older history entries will be automatically deleted', 'kinderkleurplaten-pinterest'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php _e('Debug Logging', 'kinderkleurplaten-pinterest'); ?></label>
                            </th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e('Debug Logging', 'kinderkleurplaten-pinterest'); ?></span></legend>
                                    <label>
                                        <input type="checkbox" name="debug_logging" id="kk-debug-logging" value="1" <?php checked(get_option('kk_pinterest_debug_logging', 0), 1); ?>>
                                        <?php _e('Enable debug logging for Pinterest API requests', 'kinderkleurplaten-pinterest'); ?>
                                    </label>
                                    <p class="description"><?php _e('Logs will be written to WordPress debug.log (requires WP_DEBUG)', 'kinderkleurplaten-pinterest'); ?></p>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?php _e('Request Timeout', 'kinderkleurplaten-pinterest'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="request_timeout" id="kk-request-timeout" class="small-text" value="<?php echo esc_attr(get_option('kk_pinterest_request_timeout', 30)); ?>" min="5" max="120">
                                <span class="description"><?php _e('seconds', 'kinderkleurplaten-pinterest'); ?></span>
                                <p class="description"><?php _e('Maximum time to wait for Pinterest API response', 'kinderkleurplaten-pinterest'); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary" id="kk-save-advanced"><?php _e('Save Advanced Settings', 'kinderkleurplaten-pinterest'); ?></button>
                        <span class="spinner"></span>
                    </p>
                </form>
            </div>
        </div>

        <!-- OAuth Callback Info -->
        <div class="kk-card kk-settings-section">
            <div class="kk-card-header">
                <h2><?php _e('OAuth Callback URL', 'kinderkleurplaten-pinterest'); ?></h2>
            </div>
            <div class="kk-card-body">
                <p><?php _e('Add this URL to your Pinterest App settings under "Allowed Redirect URIs":', 'kinderkleurplaten-pinterest'); ?></p>
                <div class="kk-callback-url">
                    <code><?php echo esc_url($redirect_uri); ?></code>
                    <button type="button" class="button button-small kk-copy-callback" data-clipboard-text="<?php echo esc_attr($redirect_uri); ?>">
                        <span class="dashicons dashicons-clipboard"></span>
                    </button>
                </div>
                <p class="description"><?php _e('Make sure this exact URL is added to your Pinterest App configuration under "Allowed Redirect URIs".', 'kinderkleurplaten-pinterest'); ?></p>
            </div>
        </div>

        <!-- Tools -->
        <div class="kk-card kk-settings-section">
            <div class="kk-card-header">
                <h2><?php _e('Tools', 'kinderkleurplaten-pinterest'); ?></h2>
            </div>
            <div class="kk-card-body">
                <div class="kk-tools-grid">
                    <div class="kk-tool">
                        <h4><?php _e('Sync Boards', 'kinderkleurplaten-pinterest'); ?></h4>
                        <p><?php _e('Fetch all boards from Pinterest and update local cache', 'kinderkleurplaten-pinterest'); ?></p>
                        <button type="button" class="button" id="kk-sync-boards-tool" <?php disabled(!$is_connected); ?>><?php _e('Sync Now', 'kinderkleurplaten-pinterest'); ?></button>
                    </div>
                    <div class="kk-tool">
                        <h4><?php _e('Clear History', 'kinderkleurplaten-pinterest'); ?></h4>
                        <p><?php _e('Delete all publishing history records', 'kinderkleurplaten-pinterest'); ?></p>
                        <button type="button" class="button" id="kk-clear-history-tool" data-confirm="<?php _e('Are you sure you want to clear all history? This cannot be undone.', 'kinderkleurplaten-pinterest'); ?>"><?php _e('Clear History', 'kinderkleurplaten-pinterest'); ?></button>
                    </div>
                    <div class="kk-tool">
                        <h4><?php _e('Reset Plugin', 'kinderkleurplaten-pinterest'); ?></h4>
                        <p><?php _e('Remove all plugin settings and data (irreversible)', 'kinderkleurplaten-pinterest'); ?></p>
                        <button type="button" class="button" id="kk-reset-plugin" data-confirm="<?php _e('This will delete ALL plugin settings and history. Are you absolutely sure?', 'kinderkleurplaten-pinterest'); ?>"><?php _e('Reset Plugin', 'kinderkleurplaten-pinterest'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Diagnostics / Debug -->
        <div class="kk-card kk-settings-section">
            <div class="kk-card-header">
                <h2><?php _e('Diagnostics', 'kinderkleurplaten-pinterest'); ?></h2>
            </div>
            <div class="kk-card-body">
                <div class="kk-tools-grid">
                    <div class="kk-tool">
                        <h4><?php _e('Test OAuth Configuration', 'kinderkleurplaten-pinterest'); ?></h4>
                        <p><?php _e('Run a diagnostic check on OAuth configuration and token state', 'kinderkleurplaten-pinterest'); ?></p>
                        <button type="button" class="button" id="kk-test-oauth-btn" <?php disabled(!$is_configured); ?>><?php _e('Run Diagnostics', 'kinderkleurplaten-pinterest'); ?></button>
                    </div>
                    <div class="kk-tool">
                        <h4><?php _e('View Debug Log', 'kinderkleurplaten-pinterest'); ?></h4>
                        <p><?php _e('View recent Pinterest-related debug log entries', 'kinderkleurplaten-pinterest'); ?></p>
                        <button type="button" class="button" id="kk-view-debug-log"><?php _e('View Log', 'kinderkleurplaten-pinterest'); ?></button>
                    </div>
                </div>
                
                <div id="kk-oauth-diagnostics" style="display: none; margin-top: 20px;"></div>
                <div id="kk-debug-log" style="display: none; margin-top: 20px;">
                    <div class="kk-log-container">
                        <div class="kk-log-header">
                            <h4><?php _e('Recent Debug Log Entries', 'kinderkleurplaten-pinterest'); ?></h4>
                            <button type="button" class="button button-small" id="kk-refresh-debug-log"><?php _e('Refresh', 'kinderkleurplaten-pinterest'); ?></button>
                        </div>
                        <pre class="kk-log-output" style="background: #f0f0f1; padding: 15px; max-height: 400px; overflow: auto; font-size: 12px; font-family: monospace;"><?php _e('Click "View Log" to load...', 'kinderkleurplaten-pinterest'); ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>