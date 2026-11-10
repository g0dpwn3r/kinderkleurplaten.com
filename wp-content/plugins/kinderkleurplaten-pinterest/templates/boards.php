<?php
/**
 * Boards Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$boards = $boards ?? array('items' => array(), 'total' => 0);
$per_page = 50;
$page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
$total_pages = ceil(($boards['total'] ?? 0) / $per_page);
?>

<div class="wrap kk-pinterest-wrap">
    <h1><?php _e('Pinterest Boards', 'kinderkleurplaten-pinterest'); ?></h1>

    <div class="kk-pinterest-boards">
        <!-- Toolbar -->
        <div class="kk-toolbar">
            <div class="kk-toolbar-left">
                <button type="button" class="button button-primary" id="kk-create-board-btn">
                    <span class="dashicons dashicons-plus"></span> <?php _e('Create Board', 'kinderkleurplaten-pinterest'); ?>
                </button>
                <button type="button" class="button" id="kk-sync-boards-btn">
                    <span class="dashicons dashicons-update"></span> <?php _e('Sync from Pinterest', 'kinderkleurplaten-pinterest'); ?>
                </button>
            </div>
            <div class="kk-toolbar-right">
                <form method="get" class="kk-search-form">
                    <input type="hidden" name="page" value="kinderkleurplaten-pinterest-boards">
                    <input type="search" name="s" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" placeholder="<?php _e('Search boards...', 'kinderkleurplaten-pinterest'); ?>">
                    <select name="privacy">
                        <option value=""><?php _e('All Privacy', 'kinderkleurplaten-pinterest'); ?></option>
                        <option value="public" <?php selected(isset($_GET['privacy']) ? $_GET['privacy'] : '', 'public'); ?>><?php _e('Public', 'kinderkleurplaten-pinterest'); ?></option>
                        <option value="secret" <?php selected(isset($_GET['privacy']) ? $_GET['privacy'] : '', 'secret'); ?>><?php _e('Secret', 'kinderkleurplaten-pinterest'); ?></option>
                    </select>
                    <button type="submit" class="button"><?php _e('Filter', 'kinderkleurplaten-pinterest'); ?></button>
                </form>
            </div>
        </div>

        <!-- Boards Table -->
        <table class="wp-list-table widefat fixed striped" id="kk-boards-table">
            <thead>
                <tr>
                    <th style="width: 60px;"><?php _e('Default', 'kinderkleurplaten-pinterest'); ?></th>
                    <th><?php _e('Name', 'kinderkleurplaten-pinterest'); ?></th>
                    <th><?php _e('Description', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 100px;"><?php _e('Privacy', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 80px;"><?php _e('Pins', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 100px;"><?php _e('Followers', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 100px;"><?php _e('Status', 'kinderkleurplaten-pinterest'); ?></th>
                    <th style="width: 150px;"><?php _e('Actions', 'kinderkleurplaten-pinterest'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($boards['items'])): ?>
                    <?php foreach ($boards['items'] as $board): ?>
                        <tr data-board-id="<?php echo esc_attr($board['pinterest_board_id']); ?>">
                            <td>
                                <?php if ($board['is_default']): ?>
                                    <span class="dashicons dashicons-star-filled kk-default-star" title="<?php _e('Default board', 'kinderkleurplaten-pinterest'); ?>"></span>
                                <?php else: ?>
                                    <button type="button" class="kk-set-default" data-board-id="<?php echo esc_attr($board['pinterest_board_id']); ?>" title="<?php _e('Set as default', 'kinderkleurplaten-pinterest'); ?>">
                                        <span class="dashicons dashicons-star-empty"></span>
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo esc_html($board['name']); ?></strong>
                                <div class="kk-board-id"><?php echo esc_html($board['pinterest_board_id']); ?></div>
                            </td>
                            <td><?php echo esc_html(wp_trim_words($board['description'], 20)); ?></td>
                            <td>
                                <span class="kk-privacy-badge kk-privacy-<?php echo esc_attr($board['privacy']); ?>">
                                    <?php echo esc_html(ucfirst($board['privacy'])); ?>
                                </span>
                            </td>
                            <td><?php echo number_format_i18n($board['pin_count']); ?></td>
                            <td><?php echo number_format_i18n($board['follower_count']); ?></td>
                            <td>
                                <?php if ($board['is_archived']): ?>
                                    <span class="kk-status-badge kk-status-warning"><?php _e('Archived', 'kinderkleurplaten-pinterest'); ?></span>
                                <?php else: ?>
                                    <span class="kk-status-badge kk-status-success"><?php _e('Active', 'kinderkleurplaten-pinterest'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="kk-board-actions">
                                    <button type="button" class="button button-small kk-edit-board" data-board-id="<?php echo esc_attr($board['pinterest_board_id']); ?>" data-board-name="<?php echo esc_attr($board['name']); ?>" data-board-description="<?php echo esc_attr($board['description']); ?>" data-board-privacy="<?php echo esc_attr($board['privacy']); ?>">
                                        <span class="dashicons dashicons-edit"></span>
                                    </button>
                                    <button type="button" class="button button-small kk-view-sections" data-board-id="<?php echo esc_attr($board['pinterest_board_id']); ?>">
                                        <span class="dashicons dashicons-admin-page"></span>
                                    </button>
                                    <button type="button" class="button button-small kk-delete-board" data-board-id="<?php echo esc_attr($board['pinterest_board_id']); ?>" data-board-name="<?php echo esc_attr($board['name']); ?>">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="kk-no-data"><?php _e('No boards found. Click "Create Board" or "Sync from Pinterest" to get started.', 'kinderkleurplaten-pinterest'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="tablenav bottom">
                <div class="tablenav-pages">
                    <?php
                    $base_url = admin_url('admin.php?page=kinderkleurplaten-pinterest-boards');
                    if (isset($_GET['s'])) $base_url .= '&s=' . urlencode($_GET['s']);
                    if (isset($_GET['privacy'])) $base_url .= '&privacy=' . urlencode($_GET['privacy']);
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

    <!-- Create/Edit Board Modal -->
    <div id="kk-board-modal" class="kk-modal" style="display: none;">
        <div class="kk-modal-overlay"></div>
        <div class="kk-modal-content">
            <div class="kk-modal-header">
                <h3 id="kk-board-modal-title"><?php _e('Create Board', 'kinderkleurplaten-pinterest'); ?></h3>
                <button type="button" class="kk-modal-close">&times;</button>
            </div>
            <form id="kk-board-form">
                <input type="hidden" name="board_id" id="kk-board-form-id">
                <div class="kk-modal-body">
                    <table class="form-table">
                        <tr>
                            <th><label for="kk-board-name"><?php _e('Board Name', 'kinderkleurplaten-pinterest'); ?></label></th>
                            <td><input type="text" name="name" id="kk-board-name" class="regular-text" required maxlength="100"></td>
                        </tr>
                        <tr>
                            <th><label for="kk-board-description"><?php _e('Description', 'kinderkleurplaten-pinterest'); ?></label></th>
                            <td><textarea name="description" id="kk-board-description" rows="3" class="large-text" maxlength="500"></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="kk-board-privacy"><?php _e('Privacy', 'kinderkleurplaten-pinterest'); ?></label></th>
                            <td>
                                <select name="privacy" id="kk-board-privacy">
                                    <option value="public"><?php _e('Public', 'kinderkleurplaten-pinterest'); ?></option>
                                    <option value="secret"><?php _e('Secret', 'kinderkleurplaten-pinterest'); ?></option>
                                    <option value="protected"><?php _e('Protected', 'kinderkleurplaten-pinterest'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="kk-modal-footer">
                    <button type="button" class="button kk-modal-cancel"><?php _e('Cancel', 'kinderkleurplaten-pinterest'); ?></button>
                    <button type="submit" class="button button-primary"><?php _e('Save', 'kinderkleurplaten-pinterest'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Board Sections Modal -->
    <div id="kk-sections-modal" class="kk-modal" style="display: none;">
        <div class="kk-modal-overlay"></div>
        <div class="kk-modal-content kk-modal-large">
            <div class="kk-modal-header">
                <h3><?php _e('Board Sections', 'kinderkleurplaten-pinterest'); ?></h3>
                <button type="button" class="kk-modal-close">&times;</button>
            </div>
            <div class="kk-modal-body">
                <div class="kk-create-section">
                    <h4><?php _e('Create New Section', 'kinderkleurplaten-pinterest'); ?></h4>
                    <form id="kk-create-section-form">
                        <input type="hidden" name="board_id" id="kk-section-board-id">
                        <div class="kk-form-row">
                            <input type="text" name="title" class="regular-text" placeholder="<?php _e('Section title', 'kinderkleurplaten-pinterest'); ?>" required maxlength="60">
                            <button type="submit" class="button button-primary"><?php _e('Create', 'kinderkleurplaten-pinterest'); ?></button>
                        </div>
                    </form>
                </div>
                <hr>
                <h4><?php _e('Existing Sections', 'kinderkleurplaten-pinterest'); ?></h4>
                <div id="kk-sections-list" class="kk-sections-list">
                    <p class="description"><?php _e('Loading...', 'kinderkleurplaten-pinterest'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>