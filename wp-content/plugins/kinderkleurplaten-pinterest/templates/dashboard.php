<?php
/**
 * Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$connection_status = $connection_status ?? array('status' => 'unknown');
$stats = $stats ?? array();
$recent_history = $recent_history ?? array('items' => array());
$recent_pins = $recent_pins ?? array();

$status_colors = array(
    'connected' => 'success',
    'not_configured' => 'warning',
    'not_connected' => 'info',
    'token_expired' => 'warning',
);

$status_color = $status_colors[$connection_status['status']] ?? 'info';
?>

<div class="wrap kk-pinterest-wrap">
    <h1><?php _e('Pinterest Dashboard', 'kinderkleurplaten-pinterest'); ?></h1>

    <div class="kk-pinterest-dashboard">
        <!-- Connection Status Card -->
        <div class="kk-card kk-connection-status">
            <div class="kk-card-header">
                <h2><?php _e('Connection Status', 'kinderkleurplaten-pinterest'); ?></h2>
            </div>
            <div class="kk-card-body">
                <div class="kk-status-badge kk-status-<?php echo esc_attr($status_color); ?>">
                    <?php echo esc_html($connection_status['message']); ?>
                </div>
                <?php if (!empty($connection_status['auth_url'])): ?>
                    <a href="<?php echo esc_url($connection_status['auth_url']); ?>" class="button button-primary" style="margin-top: 10px;">
                        <?php echo $connection_status['status'] === 'connected' ? __('Reconnect', 'kinderkleurplaten-pinterest') : __('Connect to Pinterest', 'kinderkleurplaten-pinterest'); ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($connection_status['token_expires'])): ?>
                    <p class="description"><?php printf(__('Token expires: %s', 'kinderkleurplaten-pinterest'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $connection_status['token_expires'])); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="kk-stats-grid">
            <div class="kk-card kk-stat-card">
                <div class="kk-stat-value"><?php echo esc_html($stats['total'] ?? 0); ?></div>
                <div class="kk-stat-label"><?php _e('Total Activities', 'kinderkleurplaten-pinterest'); ?></div>
            </div>
            <div class="kk-card kk-stat-card success">
                <div class="kk-stat-value"><?php echo esc_html($stats['published'] ?? 0); ?></div>
                <div class="kk-stat-label"><?php _e('Published', 'kinderkleurplaten-pinterest'); ?></div>
            </div>
            <div class="kk-card kk-stat-card error">
                <div class="kk-stat-value"><?php echo esc_html($stats['failed'] ?? 0); ?></div>
                <div class="kk-stat-label"><?php _e('Failed', 'kinderkleurplaten-pinterest'); ?></div>
            </div>
            <div class="kk-card kk-stat-card warning">
                <div class="kk-stat-value"><?php echo esc_html($stats['pending'] ?? 0); ?></div>
                <div class="kk-stat-label"><?php _e('Pending', 'kinderkleurplaten-pinterest'); ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="kk-card kk-quick-actions">
            <div class="kk-card-header">
                <h2><?php _e('Quick Actions', 'kinderkleurplaten-pinterest'); ?></h2>
            </div>
            <div class="kk-card-body">
                <div class="kk-action-buttons">
                    <a href="<?php echo admin_url('admin.php?page=kinderkleurplaten-pinterest-publish'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-plus"></span> <?php _e('Publish Pin', 'kinderkleurplaten-pinterest'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=kinderkleurplaten-pinterest-boards'); ?>" class="button">
                        <span class="dashicons dashicons-admin-page"></span> <?php _e('Manage Boards', 'kinderkleurplaten-pinterest'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=kinderkleurplaten-pinterest-history'); ?>" class="button">
                        <span class="dashicons dashicons-history"></span> <?php _e('View History', 'kinderkleurplaten-pinterest'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=kinderkleurplaten-pinterest-settings'); ?>" class="button">
                        <span class="dashicons dashicons-admin-generic"></span> <?php _e('Settings', 'kinderkleurplaten-pinterest'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="kk-card kk-recent-activity">
            <div class="kk-card-header">
                <h2><?php _e('Recent Activity', 'kinderkleurplaten-pinterest'); ?></h2>
                <a href="<?php echo admin_url('admin.php?page=kinderkleurplaten-pinterest-history'); ?>" class="kk-view-all"><?php _e('View All', 'kinderkleurplaten-pinterest'); ?></a>
            </div>
            <div class="kk-card-body">
                <?php if (!empty($recent_history['items'])): ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Action', 'kinderkleurplaten-pinterest'); ?></th>
                                <th><?php _e('Status', 'kinderkleurplaten-pinterest'); ?></th>
                                <th><?php _e('Title', 'kinderkleurplaten-pinterest'); ?></th>
                                <th><?php _e('Board', 'kinderkleurplaten-pinterest'); ?></th>
                                <th><?php _e('Date', 'kinderkleurplaten-pinterest'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_history['items'] as $item): ?>
                                <tr>
                                    <td><?php echo esc_html(ucfirst($item['action'])); ?></td>
                                    <td><span class="kk-status-badge kk-status-<?php echo esc_attr($item['status'] === 'published' ? 'success' : ($item['status'] === 'failed' ? 'error' : 'warning')); ?>"><?php echo esc_html(ucfirst($item['status'])); ?></span></td>
                                    <td><?php echo esc_html(wp_trim_words($item['title'], 10)); ?></td>
                                    <td><?php echo esc_html($item['board_id'] ?: '-'); ?></td>
                                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['created_at']))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="description"><?php _e('No recent activity', 'kinderkleurplaten-pinterest'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Pins -->
        <div class="kk-card kk-recent-pins">
            <div class="kk-card-header">
                <h2><?php _e('Recent Pins from Pinterest', 'kinderkleurplaten-pinterest'); ?></h2>
            </div>
            <div class="kk-card-body">
                <?php if (!empty($recent_pins) && !is_wp_error($recent_pins) && !empty($recent_pins['items'])): ?>
                    <div class="kk-pins-grid">
                        <?php foreach ($recent_pins['items'] as $pin): ?>
                            <div class="kk-pin-item">
                                <?php if (!empty($pin['media']['images']['original']['url'])): ?>
                                    <img src="<?php echo esc_url($pin['media']['images']['original']['url']); ?>" alt="" loading="lazy">
                                <?php endif; ?>
                                <div class="kk-pin-info">
                                    <h4><?php echo esc_html(wp_trim_words($pin['title'] ?? '', 8)); ?></h4>
                                    <p><?php echo esc_html(wp_trim_words($pin['description'] ?? '', 15)); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="description"><?php _e('No recent pins found', 'kinderkleurplaten-pinterest'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>