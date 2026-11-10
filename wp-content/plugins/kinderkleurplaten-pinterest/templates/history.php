<?php
/**
 * History Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$history = $history ?? array('items' => array(), 'total' => 0);
$per_page = 50;
$page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
$total_pages = ceil(($history['total'] ?? 0) / $per_page);

$action_labels = array(
    'publish' => __('Publish', 'kinderkleurplaten-pinterest'),
    'publish_post' => __('Publish from Post', 'kinderkleurplaten-pinterest'),
    'bulk_publish' => __('Bulk Publish', 'kinderkleurplaten-pinterest'),
    'create_board' => __('Create Board', 'kinderkleurplaten-pinterest'),
    'update_board' => __('Update Board', 'kinderkleurplaten-pinterest'),
    'delete_board' => __('Delete Board', 'kinderkleurplaten-pinterest'),
    'create_section' => __('Create Section', 'kinderkleurplaten-pinterest'),
    'sync_boards' => __('Sync Boards', 'kinderkleurplaten-pinterest'),
    'test_connection' => __('Test Connection', 'kinderkleurplaten-pinterest'),
);

$status_labels = array(
    'published' => __('Published', 'kinderkleurplaten-pinterest'),
    'failed' => __('Failed', 'kinderkleurplaten-pinterest'),
    'pending' => __('Pending', 'kinderkleurplaten-pinterest'),
);

$status_colors = array(
    'published' => 'success',
    'failed' => 'error',
    'pending' => 'warning',
);
?>

<div class="wrap kk-pinterest-wrap">
    <h1><?php _e('Pinterest History', 'kinderkleurplaten-pinterest'); ?></h1>

    <div class="kk-pinterest-history">
        <!-- Toolbar -->
        <div class="kk-toolbar">
            <div class="kk-toolbar-left">
                <button type="button" class="button" id="kk-clear-history-btn" data-confirm="<?php _e('Are you sure you want to clear all history? This cannot be undone.', 'kinderkleurplaten-pinterest'); ?>">
                    <span class="dashicons dashicons-trash"></span> <?php _e('Clear History', 'kinderkleurplaten-pinterest'); ?>
                </button>
            </div>
            <div class="kk-toolbar-right">
                <form method="get" class="kk-search-form">
                    <input type="hidden" name="page" value="kinderkleurplaten-pinterest-history">
                    <input type="search" name="s" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" placeholder="<?php _e('Search history...', 'kinderkleurplaten-pinterest'); ?>">
                    <select name="action_filter">
                        <option value=""><?php _e('All Actions', 'kinderkleurplaten-pinterest'); ?></option>
                        <?php foreach ($action_labels as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected(isset($_GET['action_filter']) ? $_GET['action_filter'] : '', $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status_filter">
                        <option value=""><?php _e('All Statuses', 'kinderkleurplaten-pinterest'); ?></option>
                        <?php foreach ($status_labels as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected(isset($_GET['status_filter']) ? $_GET['status_filter'] : '', $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="button"><?php _e('Filter', 'kinderkleurplaten-pinterest'); ?></button>
                </form>
            </div>
        </div>

        <!-- History Table -->
        <table class="wp-list-table widefat fixed striped" id="kk-history-table">
            <thead>
                <tr>
                    <th style="width: 80px;"><?php _e('ID', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 120px;"><?php _e('Action', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 100px;"><?php _e('Status', 'kinderkleurplaten-pinterest'); ?></th>
                    <th><?php _e('Title', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 150px;"><?php _e('Board / Section', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 100px;"><?php _e('Post ID', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 120px;"><?php _e('Pinterest ID', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 150px;"><?php _e('Error Message', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 180px;"><?php _e('Date', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 80px;"><?php _e('Actions', 'kinderkleurplaten-pinterest'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($history['items'])): ?>
                    <?php foreach ($history['items'] as $item): ?>
                        <tr data-history-id="<?php echo esc_attr($item['id']); ?>">
                            <td><?php echo esc_html($item['id']); ?></td>
                            <td>
                                <span class="kk-action-badge"><?php echo esc_html($action_labels[$item['action']] ?? $item['action']); ?></span>
                            </td>
                            <td>
                                <span class="kk-status-badge kk-status-<?php echo esc_attr($status_colors[$item['status']] ?? 'info'); ?>">
                                    <?php echo esc_html($status_labels[$item['status']] ?? $item['status']); ?>
                                </span>
                            </td>
                            <td>
                                <strong><?php echo esc_html(wp_trim_words($item['title'] ?? '', 15)); ?></strong>
                                <?php if (!empty($item['description'])): ?>
                                    <div class="description"><?php echo esc_html(wp_trim_words($item['description'], 20)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['board_id'])): ?>
                                    <div><?php echo esc_html($item['board_id']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['board_section_id'])): ?>
                                    <div class="description"><?php _e('Section:', 'kinderkleurplaten-pinterest'); ?> <?php echo esc_html($item['board_section_id']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $item['post_id'] ? '<a href="' . esc_url(get_edit_post_link($item['post_id'])) . '" target="_blank">' . esc_html($item['post_id']) . '</a>' : '-'; ?></td>
                            <td><?php echo !empty($item['pinterest_id']) ? '<code>' . esc_html($item['pinterest_id']) . '</code>' : '-'; ?></td>
                            <td>
                                <?php if (!empty($item['error_message'])): ?>
                                    <span class="kk-error-text" title="<?php echo esc_attr($item['error_message']); ?>">
                                        <?php echo esc_html(wp_trim_words($item['error_message'], 10)); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item['created_at']))); ?></td>
                            <td>
                                <div class="kk-history-actions">
                                    <?php if (!empty($item['pinterest_id']) && $item['action'] === 'publish'): ?>
                                        <a href="https://www.pinterest.com/pin/<?php echo esc_attr($item['pinterest_id']); ?>/" target="_blank" class="button button-small" title="<?php _e('View on Pinterest', 'kinderkleurplaten-pinterest'); ?>">
                                            <span class="dashicons dashicons-external"></span>
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" class="button button-small kk-delete-history-item" data-id="<?php echo esc_attr($item['id']); ?>" title="<?php _e('Delete', 'kinderkleurplaten-pinterest'); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="kk-no-data"><?php _e('No history found', 'kinderkleurplaten-pinterest'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $base_url = admin_url('admin.php?page=kinderkleurplaten-pinterest-history');
                    if (isset($_GET['s'])) $base_url .= '&s=' . urlencode($_GET['s']);
                    if (isset($_GET['action_filter'])) $base_url .= '&action_filter=' . urlencode($_GET['action_filter']);
                    if (isset($_GET['status_filter'])) $base_url .= '&status_filter=' . urlencode($_GET['status_filter']);
                    $base_url .= '&paged=%#%';
                    echo paginate_links(array(
                        'base' => $base_url,
                        'format' => '',
                        'current' => $page,
                        'total' => $total_pages,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                    ));
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Error Detail Modal -->
    <div id="kk-error-modal" class="kk-modal" style="display: none;">
        <div class="kk-modal-overlay"></div>
        <div class="kk-modal-content">
            <div class="kk-modal-header">
                <h3><?php _e('Error Details', 'kinderkleurplaten-pinterest'); ?></h3>
                <button type="button" class="kk-modal-close">&times;</button>
            </div>
            <div class="kk-modal-body">
                <pre id="kk-error-detail" class="kk-error-pre"></pre>
            </div>
            <div class="kk-modal-footer">
                <button type="button" class="button button-primary kk-modal-close"><?php _e('Close', 'kinderkleurplaten-pinterest'); ?></button>
            </div>
        </div>
    </div>
</div>