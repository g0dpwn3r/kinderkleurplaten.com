<?php
/**
 * Default template fallback
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div class="wrap kk-pinterest-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <div class="notice notice-info">
        <p><?php _e('Template not found. Please check plugin installation.', 'kinderkleurplaten-pinterest'); ?></p>
    </div>
</div>