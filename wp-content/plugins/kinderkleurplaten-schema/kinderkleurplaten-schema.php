<?php
/**
 * Plugin Name: Kinderkleurplaten Schema
 * Description: JSON-LD Schema.org markup for single kleurplaten posts.
 * Version: 1.0
 * Text Domain: kinderkleurplaten-schema
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_head', function () {
    if (!is_singular('kleurplaten')) {
        return;
    }

    global $post;

    if (!$post) {
        return;
    }

    $name = get_the_title($post);
    $desc = wp_strip_all_tags($post->post_content);
    $desc = trim($desc);
    if ('' === $desc) {
        $desc = $name;
    }

    $url = get_permalink($post);

    if ('' === $url) {
        return;
    }

    $image_url = '';
    $thumb_id = get_post_thumbnail_id($post);
    if ($thumb_id) {
        $image_url = wp_get_attachment_url($thumb_id);
    }
    if (!$image_url) {
        $meta_url = get_post_meta($post->ID, 'kk_print_url', true);
        if ($meta_url) {
            $meta_url = trim($meta_url);
            if ('' !== $meta_url && preg_match('#^https?://#i', $meta_url)) {
                $image_url = $meta_url;
            }
        }
    }

    if ('' === $image_url) {
        return;
    }

    $escaped_url = esc_url_raw($url);
    $escaped_image_url = esc_url_raw($image_url);

    $webpage_id = $escaped_url . '#webpage';
    $image_id = $webpage_id . '-image';

    $graph = array(
        array(
            '@type' => 'WebPage',
            '@id' => $webpage_id,
            'name' => $name,
            'description' => $desc,
            'url' => $escaped_url,
            'image' => $image_id,
            'genre' => array('Coloring Page', 'Kinderkleurplaat'),
            'isFamilyFriendly' => true,
        ),
        array(
            '@type' => 'ImageObject',
            '@id' => $image_id,
            'contentUrl' => $escaped_image_url,
        ),
    );

    $data = array(
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    );

    $json = wp_json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if (false === $json) {
        return;
    }

    echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
});
