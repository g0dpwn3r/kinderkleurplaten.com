<?php
/**
 * Plugin Name: Kinderkleurplaten SEO & Structure Engine
 * Description: Handles categorization, image SEO, and Schema markup for kleurplaten CPT.
 * Version: 1.0.0
 * Author: Kinderkleurplaten
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register hierarchical taxonomy 'kleurplaat_categorie' for 'kleurplaten' CPT
 */
add_action('init', 'kk_se_register_category_taxonomy');
function kk_se_register_category_taxonomy() {
    $labels = array(
        'name'              => __('Kleurplaat categorieën', 'kinderkleurplaten'),
        'singular_name'     => __('Kleurplaat categorie', 'kinderkleurplaten'),
        'search_items'      => __('Zoek categorieën', 'kinderkleurplaten'),
        'all_items'         => __('Alle categorieën', 'kinderkleurplaten'),
        'parent_item'       => __('Hoofd categorie', 'kinderkleurplaten'),
        'parent_item_colon' => __('Hoofd categorie:', 'kinderkleurplaten'),
        'edit_item'         => __('Bewerk categorie', 'kinderkleurplaten'),
        'update_item'       => __('Update categorie', 'kinderkleurplaten'),
        'add_new_item'      => __('Nieuwe categorie toevoegen', 'kinderkleurplaten'),
        'new_item_name'     => __('Nieuwe categorie naam', 'kinderkleurplaten'),
        'menu_name'         => __('Categorieën', 'kinderkleurplaten'),
    );

    register_taxonomy('kleurplaat_categorie', array('kleurplaten'), array(
        'labels'            => $labels,
        'public'            => true,
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_in_rest'      => true,
        'rest_base'         => 'kleurplaat_categorie',
        'rewrite'           => array('slug' => 'kleurplaat-categorie', 'with_front' => false),
    ));
}

/**
 * Auto-rename uploaded image files to SEO-friendly format
 */
add_filter('wp_handle_upload_prefilter', 'kk_se_rename_uploaded_image');
function kk_se_rename_uploaded_image($file) {
    $image_types = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $image_types, true)) {
        return $file;
    }

    $filename = pathinfo($file['name'], PATHINFO_FILENAME);
    $clean_name = strtolower($filename);
    $clean_name = preg_replace('/[_]+/', '-', $clean_name);
    $clean_name = preg_replace('/[^a-z0-9\-]+/', '-', $clean_name);
    $clean_name = preg_replace('/[\-]+/', '-', $clean_name);
    $clean_name = trim($clean_name, '-');
    
    $new_filename = $clean_name . '-gratis-kleurplaat.' . $extension;
    $file['name'] = $new_filename;

    return $file;
}

/**
 * Auto-generate alt tags for attachment images
 */
add_action('wp_generate_attachment_metadata', 'kk_se_generate_alt_tag', 10, 2);
function kk_se_generate_alt_tag($attachment_id, $attachment_metadata) {
    $mime_type = get_post_mime_type($attachment_id);
    if (!$mime_type || strpos($mime_type, 'image/') !== 0) {
        return;
    }

    $existing_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
    if (!empty($existing_alt)) {
        return;
    }

    $filename = wp_basename(get_attached_file($attachment_id));
    $filename = pathinfo($filename, PATHINFO_FILENAME);
    
    $clean_name = str_replace('-', ' ', $filename);
    $clean_name = str_replace('_', ' ', $clean_name);
    $clean_name = ucwords($clean_name);
    $clean_name = preg_replace('/\s*Gratis\s*Kleurplaat\s*$/i', '', $clean_name);
    $clean_name = trim($clean_name);
    
    $clean_name = function_exists('mb_convert_case') 
        ? mb_convert_case($clean_name, MB_CASE_TITLE, 'UTF-8') 
        : ucwords($clean_name);

    $alt_text = sprintf(__('Gratis %s kleurplaat printen voor kinderen', 'kinderkleurplaten'), $clean_name);
    update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($alt_text));
}

/**
 * Get JSON-LD description with fallback for wp_strip_all_tags()
 */
function kk_se_get_json_ld_description($post) {
    $strip_all_tags = function_exists('wp_strip_all_tags') ? 'wp_strip_all_tags' : 'strip_tags';
    $description = $post->post_excerpt ?: wp_trim_words($strip_all_tags($post->post_content), 30);
    if (empty($description)) {
        $description = sprintf(__('Gratis kleurplaat: %s', 'kinderkleurplaten'), $post->post_title);
    }
    return $description;
}

/**
 * Output JSON-LD Schema Markup for kleurplaten single posts
 */
add_action('wp_head', 'kk_se_output_json_ld_schema');
function kk_se_output_json_ld_schema() {
    if (!is_singular('kleurplaten')) {
        return;
    }

    global $post;
    
    $image_url = get_the_post_thumbnail_url($post->ID, 'full');
    if (!$image_url) {
        $image_id = get_post_thumbnail_id($post->ID);
        if ($image_id) {
            $image_data = wp_get_attachment_image_src($image_id, 'full');
            $image_url = $image_data ? $image_data[0] : '';
        }
    }

    $description = kk_se_get_json_ld_description($post);
    if (empty($description)) {
        $description = sprintf(__('Gratis kleurplaat: %s', 'kinderkleurplaten'), $post->post_title);
    }

    $name = $post->post_title;
    $image_url = esc_url($image_url);
    $permalink = esc_url(get_permalink($post->ID));

    $graph = array();

    if ($image_url) {
        $graph[] = array(
            '@type' => 'ImageObject',
            'contentUrl' => $image_url,
            'encodingFormat' => 'Image',
            'name' => $name,
            'description' => $description,
        );
    }

    $graph[] = array(
        '@type' => 'WebPage',
        'name' => $name,
        'description' => $description,
        'url' => $permalink,
        'isFamilyFriendly' => true,
    );

    $data = array(
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    );

    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    echo '</script>' . "\n";
}
