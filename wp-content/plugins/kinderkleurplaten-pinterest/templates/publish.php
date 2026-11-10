<?php
/**
 * Publish Pin Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$boards = $board_manager->get_boards_for_select();
$default_board = $board_manager->get_default_board();
?>

<div class="wrap kk-pinterest-wrap">
    <h1><?php _e('Publish Pin', 'kinderkleurplaten-pinterest'); ?></h1>

    <div class="kk-pinterest-publish">
        <!-- Publish Mode Tabs -->
        <nav class="nav-tab-wrapper kk-publish-tabs" role="tablist">
            <a href="#kk-tab-manual" class="nav-tab nav-tab-active" data-tab="manual" role="tab" aria-selected="true"><?php _e('Manual Pin', 'kinderkleurplaten-pinterest'); ?></a>
            <a href="#kk-tab-from-post" class="nav-tab" data-tab="from-post" role="tab" aria-selected="false"><?php _e('From Post', 'kinderkleurplaten-pinterest'); ?></a>
            <a href="#kk-tab-bulk" class="nav-tab" data-tab="bulk" role="tab" aria-selected="false"><?php _e('Bulk Publish', 'kinderkleurplaten-pinterest'); ?></a>
        </nav>

        <!-- Manual Pin Form -->
        <div class="kk-tab-panel kk-tab-active" id="kk-tab-manual" role="tabpanel">
            <form id="kk-manual-pin-form" class="kk-pin-form">
                <div class="kk-form-row">
                    <div class="kk-form-col kk-form-col-2">
                        <div class="form-group">
                            <label for="kk-pin-board"><?php _e('Board', 'kinderkleurplaten-pinterest'); ?> <span class="required">*</span></label>
                            <select name="board_id" id="kk-pin-board" required>
                                <option value=""><?php _e('Select a board...', 'kinderkleurplaten-pinterest'); ?></option>
                                <?php foreach ($boards as $board): ?>
                                    <option value="<?php echo esc_attr($board['id']); ?>" <?php selected($default_board && $default_board['pinterest_board_id'] === $board['id']); ?>>
                                        <?php echo esc_html($board['name']); ?> (<?php echo number_format_i18n($board['pin_count']); ?> pins)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kk-pin-section"><?php _e('Board Section (optional)', 'kinderkleurplaten-pinterest'); ?></label>
                            <select name="board_section_id" id="kk-pin-section">
                                <option value=""><?php _e('No section', 'kinderkleurplaten-pinterest'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kk-pin-title"><?php _e('Title', 'kinderkleurplaten-pinterest'); ?> <span class="required">*</span></label>
                    <input type="text" name="title" id="kk-pin-title" class="regular-text" required maxlength="100" placeholder="<?php _e('Enter pin title...', 'kinderkleurplaten-pinterest'); ?>">
                    <p class="description"><?php _e('Max 100 characters', 'kinderkleurplaten-pinterest'); ?></p>
                </div>

                <div class="form-group">
                    <label for="kk-pin-description"><?php _e('Description', 'kinderkleurplaten-pinterest'); ?></label>
                    <textarea name="description" id="kk-pin-description" rows="4" class="large-text" maxlength="500" placeholder="<?php _e('Enter pin description...', 'kinderkleurplaten-pinterest'); ?>"></textarea>
                    <p class="description"><?php _e('Max 500 characters', 'kinderkleurplaten-pinterest'); ?></p>
                </div>

                <div class="kk-form-row">
                    <div class="kk-form-col kk-form-col-2">
                        <div class="form-group">
                            <label for="kk-pin-link"><?php _e('Link URL', 'kinderkleurplaten-pinterest'); ?> <span class="required">*</span></label>
                            <input type="url" name="link" id="kk-pin-link" class="regular-text" required placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label for="kk-pin-image"><?php _e('Image URL', 'kinderkleurplaten-pinterest'); ?> <span class="required">*</span></label>
                            <input type="url" name="image_url" id="kk-pin-image" class="regular-text" required placeholder="https://example.com/image.jpg">
                            <p class="description"><?php _e('Direct link to image (JPG, PNG, WebP)', 'kinderkleurplaten-pinterest'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kk-pin-alt"><?php _e('Alt Text (optional)', 'kinderkleurplaten-pinterest'); ?></label>
                    <input type="text" name="alt_text" id="kk-pin-alt" class="regular-text" maxlength="500" placeholder="<?php _e('Accessibility description for the image', 'kinderkleurplaten-pinterest'); ?>">
                </div>

                <div class="form-group">
                    <div id="kk-pin-preview" class="kk-pin-preview" style="display: none;">
                        <h4><?php _e('Preview', 'kinderkleurplaten-pinterest'); ?></h4>
                        <div class="kk-preview-card">
                            <img id="kk-preview-image" src="" alt="" style="max-width: 100%; height: auto;">
                            <div class="kk-preview-info">
                                <h5 id="kk-preview-title"></h5>
                                <p id="kk-preview-description"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group kk-form-submit">
                    <button type="submit" class="button button-primary button-large" id="kk-publish-manual-btn">
                        <span class="dashicons dashicons-share"></span> <?php _e('Publish Pin', 'kinderkleurplaten-pinterest'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </form>
        </div>

        <!-- From Post Form -->
        <div class="kk-tab-panel" id="kk-tab-from-post" role="tabpanel">
            <form id="kk-post-pin-form" class="kk-pin-form">
                <div class="form-group">
                    <label for="kk-post-search"><?php _e('Search Posts', 'kinderkleurplaten-pinterest'); ?></label>
                    <div class="kk-post-search">
                        <input type="text" id="kk-post-search" class="regular-text" placeholder="<?php _e('Type to search kleurplaten posts...', 'kinderkleurplaten-pinterest'); ?>">
                        <span class="spinner"></span>
                    </div>
                    <div id="kk-post-search-results" class="kk-post-search-results" style="display: none;"></div>
                </div>

                <div id="kk-selected-post" style="display: none;">
                    <h4><?php _e('Selected Post', 'kinderkleurplaten-pinterest'); ?></h4>
                    <div class="kk-selected-post-info">
                        <img id="kk-selected-thumb" src="" alt="" style="max-width: 150px;">
                        <div>
                            <strong id="kk-selected-title"></strong>
                            <p id="kk-selected-excerpt" class="description"></p>
                            <input type="hidden" name="post_id" id="kk-selected-post-id">
                        </div>
                    </div>
                    <button type="button" class="button" id="kk-clear-post"><?php _e('Clear Selection', 'kinderkleurplaten-pinterest'); ?></button>
                </div>

                <div class="kk-form-row">
                    <div class="kk-form-col kk-form-col-2">
                        <div class="form-group">
                            <label for="kk-post-pin-board"><?php _e('Board', 'kinderkleurplaten-pinterest'); ?> <span class="required">*</span></label>
                            <select name="board_id" id="kk-post-pin-board" required>
                                <option value=""><?php _e('Select a board...', 'kinderkleurplaten-pinterest'); ?></option>
                                <?php foreach ($boards as $board): ?>
                                    <option value="<?php echo esc_attr($board['id']); ?>" <?php selected($default_board && $default_board['pinterest_board_id'] === $board['id']); ?>>
                                        <?php echo esc_html($board['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kk-post-pin-section"><?php _e('Board Section (optional)', 'kinderkleurplaten-pinterest'); ?></label>
                            <select name="board_section_id" id="kk-post-pin-section">
                                <option value=""><?php _e('No section', 'kinderkleurplaten-pinterest'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kk-post-pin-title"><?php _e('Title', 'kinderkleurplaten-pinterest'); ?></label>
                    <input type="text" name="title" id="kk-post-pin-title" class="regular-text" maxlength="100">
                    <p class="description"><?php _e('Leave empty to use post title', 'kinderkleurplaten-pinterest'); ?></p>
                </div>

                <div class="form-group">
                    <label for="kk-post-pin-description"><?php _e('Description', 'kinderkleurplaten-pinterest'); ?></label>
                    <textarea name="description" id="kk-post-pin-description" rows="4" class="large-text" maxlength="500"></textarea>
                    <p class="description"><?php _e('Leave empty to use post excerpt', 'kinderkleurplaten-pinterest'); ?></p>
                </div>

                <div class="kk-form-row">
                    <div class="kk-form-col kk-form-col-2">
                        <div class="form-group">
                            <label for="kk-post-pin-link"><?php _e('Link', 'kinderkleurplaten-pinterest'); ?></label>
                            <input type="url" name="link" id="kk-post-pin-link" class="regular-text">
                            <p class="description"><?php _e('Leave empty to use post permalink', 'kinderkleurplaten-pinterest'); ?></p>
                        </div>
                        <div class="form-group">
                            <label for="kk-post-pin-image"><?php _e('Image URL', 'kinderkleurplaten-pinterest'); ?></label>
                            <input type="url" name="image_url" id="kk-post-pin-image" class="regular-text">
                            <p class="description"><?php _e('Leave empty to use featured image', 'kinderkleurplaten-pinterest'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kk-post-pin-alt"><?php _e('Alt Text (optional)', 'kinderkleurplaten-pinterest'); ?></label>
                    <input type="text" name="alt_text" id="kk-post-pin-alt" class="regular-text" maxlength="500">
                </div>

                <div class="form-group kk-form-submit">
                    <button type="submit" class="button button-primary button-large" id="kk-publish-post-btn" disabled>
                        <span class="dashicons dashicons-share"></span> <?php _e('Publish from Post', 'kinderkleurplaten-pinterest'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </form>
        </div>

        <!-- Bulk Publish Form -->
        <div class="kk-tab-panel" id="kk-tab-bulk" role="tabpanel">
            <form id="kk-bulk-pin-form" class="kk-pin-form">
                <div class="form-group">
                    <label><?php _e('Select Posts to Publish', 'kinderkleurplaten-pinterest'); ?></label>
                    <div class="kk-bulk-post-search">
                        <input type="text" id="kk-bulk-post-search" class="regular-text" placeholder="<?php _e('Search posts...', 'kinderkleurplaten-pinterest'); ?>">
                        <span class="spinner"></span>
                    </div>
                    <div id="kk-bulk-post-results" class="kk-bulk-post-results" style="display: none;"></div>
                </div>

                <div id="kk-bulk-selected" style="display: none;">
                    <h4><?php _e('Selected Posts', 'kinderkleurplaten-pinterest'); ?> (<span id="kk-bulk-count">0</span>)</h4>
                    <div id="kk-bulk-selected-list" class="kk-bulk-selected-list"></div>
                    <button type="button" class="button" id="kk-clear-bulk"><?php _e('Clear All', 'kinderkleurplaten-pinterest'); ?></button>
                </div>

                <div class="kk-form-row">
                    <div class="kk-form-col kk-form-col-2">
                        <div class="form-group">
                            <label for="kk-bulk-board"><?php _e('Board', 'kinderkleurplaten-pinterest'); ?> <span class="required">*</span></label>
                            <select name="board_id" id="kk-bulk-board" required>
                                <option value=""><?php _e('Select a board...', 'kinderkleurplaten-pinterest'); ?></option>
                                <?php foreach ($boards as $board): ?>
                                    <option value="<?php echo esc_attr($board['id']); ?>" <?php selected($default_board && $default_board['pinterest_board_id'] === $board['id']); ?>>
                                        <?php echo esc_html($board['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kk-bulk-section"><?php _e('Board Section (optional)', 'kinderkleurplaten-pinterest'); ?></label>
                            <select name="board_section_id" id="kk-bulk-section">
                                <option value=""><?php _e('No section', 'kinderkleurplaten-pinterest'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kk-bulk-delay"><?php _e('Delay between pins (seconds)', 'kinderkleurplaten-pinterest'); ?></label>
                    <input type="number" name="delay" id="kk-bulk-delay" value="2" min="1" max="60" class="small-text">
                    <p class="description"><?php _e('Recommended: 2+ seconds to avoid rate limits', 'kinderkleurplaten-pinterest'); ?></p>
                </div>

                <div class="form-group kk-form-submit">
                    <button type="submit" class="button button-primary button-large" id="kk-publish-bulk-btn" disabled>
                        <span class="dashicons dashicons-share"></span> <?php _e('Publish All Selected', 'kinderkleurplaten-pinterest'); ?>
                    </button>
                    <span class="spinner"></span>
                </div>
            </form>

            <!-- Bulk Progress Modal -->
            <div id="kk-bulk-progress-modal" class="kk-modal" style="display: none;">
                <div class="kk-modal-overlay"></div>
                <div class="kk-modal-content">
                    <div class="kk-modal-header">
                        <h3><?php _e('Bulk Publishing Progress', 'kinderkleurplaten-pinterest'); ?></h3>
                    </div>
                    <div class="kk-modal-body">
                        <div class="kk-progress-bar">
                            <div class="kk-progress-fill" id="kk-bulk-progress-fill" style="width: 0%"></div>
                        </div>
                        <p id="kk-bulk-progress-text"><?php _e('Starting...', 'kinderkleurplaten-pinterest'); ?></p>
                        <ul id="kk-bulk-progress-log" class="kk-progress-log"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>