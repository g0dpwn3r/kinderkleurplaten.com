<?php
/**
 * Plugin Name: Kinderkleurplaten SEO & Structure Engine
 * Description: Automated categorization, image SEO, and Schema markup for the 'kleurplaten' CPT.
 * Version: 1.0.0
 * Author: Custom Developer
 * License: GPL2
 * Text Domain: kinderkleurplaten-seo
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Requirement 1: Register custom hierarchical taxonomy 'kleurplaat_categorie' for CPT 'kleurplaten'.
 */
function kkse_register_taxonomy()
{
    $labels = array(
        'name'              => __('Kleurplaat Categorieën', 'kinderkleurplaten-seo'),
        'singular_name'     => __('Kleurplaat Categorie', 'kinderkleurplaten-seo'),
        'search_items'      => __('Zoek categorieën', 'kinderkleurplaten-seo'),
        'all_items'         => __('Alle categorieën', 'kinderkleurplaten-seo'),
        'parent_item'       => __('Hoofdcategorie', 'kinderkleurplaten-seo'),
        'parent_item_colon' => __('Hoofdcategorie:', 'kinderkleurplaten-seo'),
        'edit_item'         => __('Categorie bewerken', 'kinderkleurplaten-seo'),
        'update_item'       => __('Bijwerken', 'kinderkleurplaten-seo'),
        'add_new_item'      => __('Nieuwe categorie', 'kinderkleurplaten-seo'),
        'new_item_name'     => __('Nieuwe categorienaam', 'kinderkleurplaten-seo'),
        'menu_name'         => __('Kleurplaat Categorieën', 'kinderkleurplaten-seo'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'kleurplaat-categorie'),
    );

    register_taxonomy('kleurplaat_categorie', array('kleurplaten'), $args);
}
add_action('init', 'kkse_register_taxonomy');

/**
 * Requirement 2: Auto-rename uploaded image files to SEO-friendly format with "-gratis-kleurplaat" suffix.
 */
function kkse_rename_uploaded_file($file)
{
    $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml');

    if (!in_array($file['type'], $allowed_types, true)) {
        return $file;
    }

    $post_title = '';
    if (!empty($_POST['post_title'])) {
        $post_title = sanitize_text_field($_POST['post_title']);
    } elseif (!empty($file['name'])) {
        $post_title = pathinfo($file['name'], PATHINFO_FILENAME);
    }

    $clean_name = kkse_sanitize_filename($post_title);
    $new_name   = $clean_name . '-gratis-kleurplaat.' . pathinfo($file['name'], PATHINFO_EXTENSION);

    $file['name'] = $new_name;

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'kkse_rename_uploaded_file');

/**
 * Sanitize a string into a clean, SEO-friendly filename (lowercase, hyphens, no special chars).
 */
function kkse_sanitize_filename($string)
{
    $string = remove_accents($string);
    $string = preg_replace('/[^a-z0-9\s-]/i', '', strtolower($string));
    $string = preg_replace('/[\s-]+/', '-', trim($string));
    $string = trim($string, '-');

    return $string;
}

/**
 * Requirement 3: Auto-generate Alt tags for uploaded images in Dutch.
 * Hooks into add_attachment to set _wp_attachment_image_alt meta.
 */
function kkse_auto_generate_alt_text($attachment_id)
{
    $file = get_attached_file($attachment_id);
    if (!$file) {
        return;
    }

    $filename = pathinfo($file, PATHINFO_FILENAME);
    $clean_title = kkse_sanitize_filename($filename);
    $clean_title = str_replace('-', ' ', $clean_title);

    $alt_text = sprintf(
        'Gratis %s kleurplaat printen voor kinderen',
        ucwords($clean_title)
    );

    update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($alt_text));
}
add_action('add_attachment', 'kkse_auto_generate_alt_text');

/**
 * Requirement 4: Output JSON-LD Schema markup on single 'kleurplaten' CPT pages.
 */
function kkse_output_schema_markup()
{
    if (!is_singular('kleurplaten')) {
        return;
    }

    global $post;
    if (!$post) {
        return;
    }

    $title     = get_the_title($post);
    $excerpt   = get_the_excerpt($post);
    $content   = wp_strip_all_tags($post->post_content);
    $permalink = get_permalink($post);

    if (empty($excerpt)) {
        $excerpt = wp_trim_words($content, 55, '...');
    }

    $description = $excerpt;

    $featured_image_id = get_post_thumbnail_id($post->ID);
    if ($featured_image_id) {
        $image_url = wp_get_attachment_image_url($featured_image_id, 'full');
    }

    if (empty($image_url)) {
        return;
    }

    $schema = array(
        '@context'    => 'https://schema.org',
        '@graph'      => array(
            array(
                '@type'             => 'ImageObject',
                '@id'               => $permalink . '#imageobject',
                'contentUrl'        => esc_url_raw($image_url),
                'license'           => esc_url_raw($permalink),
                'acquireLicensePage' => esc_url_raw($permalink),
                'creditText'        => __('Kinderkleurplaten', 'kinderkleurplaten-seo'),
                'creator'           => get_bloginfo('name'),
            ),
            array(
                '@type'    => 'WebPage',
                '@id'      => $permalink . '#webpage',
                'url'      => esc_url_raw($permalink),
                'name'     => sanitize_text_field($title),
                'description' => sanitize_text_field($description),
                'isPartOf' => array(
                    '@id' => get_site_url() . '/#website',
                ),
                'primaryImageOfPage' => array(
                    '@id' => $permalink . '#imageobject',
                ),
                'inLanguage' => get_bloginfo('language'),
                'isFamilyFriendly' => 'http://schema.org/True',
                'copyrightYear'  => date('Y'),
                'copyrightHolder' => array(
                    '@type' => 'Organization',
                    'name'  => get_bloginfo('name'),
                ),
            ),
        ),
    );

    $json_ld = wp_json_encode($schema);
    if (!$json_ld) {
        return;
    ?>

    <script type="application/ld+json">
        <?php echo wp_kses_post($json_ld); ?>
    </script>

    <?php
}
add_action('wp_head', 'kkse_output_schema_markup');
