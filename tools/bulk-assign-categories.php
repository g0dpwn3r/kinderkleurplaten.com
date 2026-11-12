#!/usr/bin/env php
<?php

/**
 * Bulk assign kleurplaat_categorie terms to kleurplaten posts based on their kleurplaat_thema assignments.
 * 
 * Usage: php /mnt/data_ssd/kinderkleurplaten.com/tools/bulk-assign-categories.php
 */

// Load WordPress environment
require_once __DIR__ . '/../wp-load.php';

// Configuration
const BATCH_SIZE = 100;
const POST_TYPE = 'kleurplaten';
const THEMA_TAXONOMY = 'kleurplaat_thema';
const CATEGORIE_TAXONOMY = 'kleurplaat_categorie';

// Mapping: thema slug/name -> categorie slug
$thema_to_categorie = [
    // Dieren
    'dieren' => 'dieren',
    'boerderijdieren' => 'dieren',
    'dieren-in-het-bos' => 'dieren',
    'dieren in het bos' => 'dieren',
    'honden' => 'dieren',
    'katten' => 'dieren',
    'konijntjes' => 'dieren',
    'paarden' => 'dieren',
    'eenden' => 'dieren',
    'kippen' => 'dieren',
    'vogels' => 'dieren',
    'vlinders' => 'dieren',
    'bijen' => 'dieren',
    'bijen-en-bloemen' => 'dieren',
    'bijen en bloemen' => 'dieren',

    // Dinosaurussen
    'dinosaurussen' => 'dinosaurussen',

    // Voertuigen
    'auto\'s' => 'voertuigen',
    'autos' => 'voertuigen',
    'vliegtuigen' => 'voertuigen',
    'schepen' => 'voertuigen',
    'treinen' => 'voertuigen',
    'vrachtwagens' => 'voertuigen',
    'fietsen' => 'voertuigen',
    'bouwplaats' => 'voertuigen',

    // Feestdagen
    'kerst' => 'feestdagen',
    'sinterklaas' => 'feestdagen',
    'halloween' => 'feestdagen',
    'carnaval' => 'feestdagen',
    'lente' => 'feestdagen',
    'zomer' => 'feestdagen',
    'herfst' => 'feestdagen',
    'winter' => 'feestdagen',
    'seizoenen' => 'feestdagen',

    // Fantasie & Sprookjes
    'prinsen-en-prinsessen' => 'fantasie-sprookjes',
    'prinsen en prinsessen' => 'fantasie-sprookjes',
    'prinsessen' => 'fantasie-sprookjes',
    'feeën' => 'fantasie-sprookjes',
    'feeen' => 'fantasie-sprookjes',
    'feeënhuisjes' => 'fantasie-sprookjes',
    'feenhuizen' => 'fantasie-sprookjes',
    'eenhoorns' => 'fantasie-sprookjes',
    'draken' => 'fantasie-sprookjes',
    'kastelen' => 'fantasie-sprookjes',
    'ridders' => 'fantasie-sprookjes',
    'magische-bossen' => 'fantasie-sprookjes',
    'magische bossen' => 'fantasie-sprookjes',
    'onderwaterwereld' => 'fantasie-sprookjes',
    'zeemeerminnen' => 'fantasie-sprookjes',
    'harten' => 'fantasie-sprookjes',
    'hertjes' => 'fantasie-sprookjes',

    // Educatief
    'cijfers' => 'educatief',
    'letters' => 'educatief',
    'forms' => 'educatief',
    'shapes' => 'educatief',
    'tellen' => 'educatief',
    'rekenen' => 'educatief',

    // Gevoelens & Emoties
    'gevoelens' => 'gevoelens-emoties',
    'emoties' => 'gevoelens-emoties',

    // Muziek & Geluid
    'muziek' => 'muziek-geluid',
    'geluid' => 'muziek-geluid',
    'instrumenten' => 'muziek-geluid',

    // Natuur & Planten
    'bomen' => 'natuur-planten',
    'bloemen' => 'natuur-planten',
    'planten' => 'natuur-planten',
    'rozen' => 'natuur-planten',
    'zonnebloemen' => 'natuur-planten',
    'paddestoelen' => 'natuur-planten',
    'tuin' => 'natuur-planten',
    'fruit' => 'natuur-planten',
    'groenten' => 'natuur-planten',
    'natuur' => 'natuur-planten',

    // Ruimte
    'planeten' => 'ruimte',
    'sterren' => 'ruimte',
    'sterren-en-maan' => 'ruimte',
    'sterren en maan' => 'ruimte',
    'maan' => 'ruimte',
    'ruimte' => 'ruimte',
    'astronauten' => 'ruimte',
    'raketten' => 'ruimte',
    'raket' => 'ruimte',
    'raketje' => 'ruimte',
    'raketjes' => 'ruimte',
    'raket-vriendjes' => 'ruimte',
    'raketvriendjes' => 'ruimte',

    // Sport & Bewegen
    'sport' => 'sport-bewegen',
    'voetbal' => 'sport-bewegen',
    'balans' => 'sport-bewegen',
    'bewegen' => 'sport-bewegen',

    // Voedsel
    'appels' => 'voedsel',
    'peren' => 'voedsel',
    'kersen' => 'voedsel',
    'taarten' => 'voedsel',
    'ijsjes' => 'voedsel',
];

// Normalize function for matching
function normalize_term($term) {
    $term = strtolower(trim($term));
    $term = str_replace(['\'', '’'], '', $term);
    $term = preg_replace('/\s+/', '-', $term);
    return $term;
}

// Get or create "Algemeen" category term
function get_algemeen_category() {
    $term = get_term_by('slug', 'algemeen', CATEGORIE_TAXONOMY);
    if (!$term) {
        $term = wp_insert_term('Algemeen', CATEGORIE_TAXONOMY, ['slug' => 'algemeen']);
        if (is_wp_error($term)) {
            error_log("Failed to create Algemeen category: " . $term->get_error_message());
            return null;
        }
        $term = get_term($term['term_id'], CATEGORIE_TAXONOMY);
    }
    return $term;
}

$algemeen_term = get_algemeen_category();

// Statistics
$stats = [
    'total' => 0,
    'processed' => 0,
    'assigned' => 0,
    'skipped_already_has_category' => 0,
    'skipped_no_thema' => 0,
    'assigned_algemeen' => 0,
    'errors' => 0,
];

// Get total count
$total_query = new WP_Query([
    'post_type' => POST_TYPE,
    'post_status' => 'publish',
    'posts_per_page' => 1,
    'no_found_rows' => true,
    'fields' => 'ids',
]);
$stats['total'] = $total_query->found_posts;

echo "=== Bulk Category Assignment ===\n";
echo "Total posts to process: {$stats['total']}\n";
echo "Batch size: " . BATCH_SIZE . "\n";
echo "Taxonomy (source): " . THEMA_TAXONOMY . "\n";
echo "Taxonomy (target): " . CATEGORIE_TAXONOMY . "\n\n";

$offset = 0;
$batch_num = 0;

while (true) {
    $batch_num++;
    
    $query = new WP_Query([
        'post_type' => POST_TYPE,
        'post_status' => 'publish',
        'posts_per_page' => BATCH_SIZE,
        'offset' => $offset,
        'no_found_rows' => true,
        'fields' => 'ids',
    ]);

    $post_ids = $query->posts;
    
    if (empty($post_ids)) {
        break;
    }

    echo "Batch {$batch_num}: Processing " . count($post_ids) . " posts (offset {$offset})...\n";

    foreach ($post_ids as $post_id) {
        $stats['processed']++;

        // Check if post already has categorie terms
        $existing_cats = wp_get_object_terms($post_id, CATEGORIE_TAXONOMY, ['fields' => 'slugs']);
        if (!empty($existing_cats) && !is_wp_error($existing_cats)) {
            $stats['skipped_already_has_category']++;
            continue;
        }

        // Get thema terms for this post
        $thema_terms = wp_get_object_terms($post_id, THEMA_TAXONOMY, ['fields' => 'slugs']);
        
        if (empty($thema_terms) || is_wp_error($thema_terms)) {
            $stats['skipped_no_thema']++;
            continue;
        }

        // Find matching category
        $assigned_cat_slug = null;
        
        foreach ($thema_terms as $thema_slug) {
            $normalized = normalize_term($thema_slug);
            
            if (isset($thema_to_categorie[$normalized])) {
                $assigned_cat_slug = $thema_to_categorie[$normalized];
                break;
            }
            
            // Also try matching by name (not just slug)
            $thema_term_obj = get_term_by('slug', $thema_slug, THEMA_TAXONOMY);
            if ($thema_term_obj) {
                $normalized_name = normalize_term($thema_term_obj->name);
                if (isset($thema_to_categorie[$normalized_name])) {
                    $assigned_cat_slug = $thema_to_categorie[$normalized_name];
                    break;
                }
            }
        }

        // If no match found, assign to Algemeen
        if (!$assigned_cat_slug && $algemeen_term) {
            $assigned_cat_slug = 'algemeen';
            $stats['assigned_algemeen']++;
        }

        if ($assigned_cat_slug) {
            $result = wp_set_object_terms($post_id, $assigned_cat_slug, CATEGORIE_TAXONOMY, true);
            
            if (is_wp_error($result)) {
                $stats['errors']++;
                error_log("Error assigning category to post {$post_id}: " . $result->get_error_message());
            } else {
                $stats['assigned']++;
            }
        }
    }

    $offset += BATCH_SIZE;
    
    // Progress update every 5 batches
    if ($batch_num % 5 === 0) {
        echo "  Progress: {$stats['processed']}/{$stats['total']} posts processed\n";
    }
    
    // Memory management
    wp_cache_flush();
}

// Final summary
echo "\n=== Summary ===\n";
echo "Total posts found:     {$stats['total']}\n";
echo "Posts processed:       {$stats['processed']}\n";
echo "Categories assigned:   {$stats['assigned']}\n";
echo "  - Matched categories: " . ($stats['assigned'] - $stats['assigned_algemeen']) . "\n";
echo "  - Assigned Algemeen:  {$stats['assigned_algemeen']}\n";
echo "Skipped (has category): {$stats['skipped_already_has_category']}\n";
echo "Skipped (no thema):    {$stats['skipped_no_thema']}\n";
echo "Errors:                {$stats['errors']}\n";
echo "\nDone!\n";

