<?php
/**
 * WPCode Snippet: Homepage Grid for Kleurplaten
 *
 * Installation:
 * 1. Go to WPCode > + Add Snippet in your WordPress admin.
 * 2. Paste this entire code into the snippet editor.
 * 3. Set the snippet type to "PHP Snippet".
 * 4. Save and activate.
 *
 * Or paste this code into your theme's functions.php file.
 */

/* ==========================================================================
   1. Register Custom Post Type: 'kleurplaten'
   ========================================================================== */

function kk_register_kleurplaten_cpt() {

    $labels = array(
        'name'                  => _x( 'Kleurplaten', 'Post Type General Name', 'kk' ),
        'singular_name'         => _x( 'Kleurplaat', 'Post Type Singular Name', 'kk' ),
        'menu_name'             => __( 'Kleurplaten', 'kk' ),
        'name_admin_bar'        => __( 'Kleurplaat', 'kk' ),
        'attributes'            => __( 'Kleurplaat Attributes', 'kk' ),
        'parent_item_colon'     => __( 'Parent Kleurplaat:', 'kk' ),
        'all_items'             => __( 'All Kleurplaten', 'kk' ),
        'view_item'             => __( 'View Kleurplaat', 'kk' ),
        'add_new_item'          => __( 'Add New Kleurplaat', 'kk' ),
        'add_new'               => __( 'Add New', 'kk' ),
        'edit_item'             => __( 'Edit Kleurplaat', 'kk' ),
        'update_item'           => __( 'Update Kleurplaat', 'kk' ),
        'search_items'          => __( 'Search Kleurplaten', 'kk' ),
        'not_found'             => __( 'Not found', 'kk' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'kk' ),
        'featured_image'        => __( 'Featured Image', 'kk' ),
        'set_featured_image'    => __( 'Set featured image', 'kk' ),
        'remove_featured_image' => __( 'Remove featured image', 'kk' ),
        'use_featured_image'    => __( 'Use as featured image', 'kk' ),
        'insert_into_item'      => __( 'Insert into kleurplaat', 'kk' ),
        'uploaded_to_this_item' => __( 'Uploaded to this kleurplaat', 'kk' ),
        'filter_items_list'     => __( 'Filter kleurplaten list', 'kk' ),
        'items_list_navigation' => __( 'Kleurplaten list navigation', 'kk' ),
        'items_list'            => __( 'Kleurplaten list', 'kk' ),
    );

    $args = array(
        'label'               => __( 'kleurplaten', 'kk' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'thumbnail', 'excerpt' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-art',
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => true,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'rewrite'             => array( 'slug' => 'kleurplaten' ),
    );

    register_post_type( 'kleurplaten', $args );

}
add_action( 'init', 'kk_register_kleurplaten_cpt' );


/* ==========================================================================
   2. Register Custom Meta Fields: kk_print_url & kk_svg_url
   ========================================================================== */

function kk_register_kleurplaten_meta_boxes() {

    add_meta_box(
        'kk_kleurplaat_details',
        __( 'Kleurplaat Details', 'kk' ),
        'kk_kleurplaat_meta_box_callback',
        'kleurplaten',
        'normal',
        'high'
    );

}
add_action( 'add_meta_boxes', 'kk_register_kleurplaten_meta_boxes' );

function kk_kleurplaat_meta_box_callback( $post ) {

    wp_nonce_field( 'kk_kleurplaat_meta_box', 'kk_kleurplaat_nonce' );

    $print_url = get_post_meta( $post->ID, '_kk_print_url', true );
    $svg_url   = get_post_meta( $post->ID, '_kk_svg_url', true );

    ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="kk_print_url"><?php _e( 'Print URL', 'kk' ); ?></label></th>
            <td>
                <input type="url" id="kk_print_url" name="kk_print_url" value="<?php echo esc_url( $print_url ); ?>" class="regular-text" />
                <p class="description"><?php _e( 'Full URL to the printable version of this kleurplaat.', 'kk' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="kk_svg_url"><?php _e( 'SVG URL', 'kk' ); ?></label></th>
            <td>
                <input type="url" id="kk_svg_url" name="kk_svg_url" value="<?php echo esc_url( $svg_url ); ?>" class="regular-text" />
                <p class="description"><?php _e( 'Direct URL to the SVG file for download.', 'kk' ); ?></p>
            </td>
        </tr>
    </table>
    <?php

}

function kk_save_kleurplaten_meta( $post_id ) {

    if ( ! isset( $_POST['kk_kleurplaat_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['kk_kleurplaat_nonce'], 'kk_kleurplaat_meta_box' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['kk_print_url'] ) ) {
        update_post_meta( $post_id, '_kk_print_url', esc_url_raw( $_POST['kk_print_url'] ) );
    }

    if ( isset( $_POST['kk_svg_url'] ) ) {
        update_post_meta( $post_id, '_kk_svg_url', esc_url_raw( $_POST['kk_svg_url'] ) );
    }

}
add_action( 'save_post_kleurplaten', 'kk_save_kleurplaten_meta' );


/* ==========================================================================
   3. Shortcode: [kk_homepage_grid]
   ========================================================================== */

function kk_homepage_grid_shortcode( $atts ) {

    $atts = shortcode_atts( array(
        'posts_per_page' => 12,
    ), $atts, 'kk_homepage_grid' );

    $query_args = array(
        'post_type'      => 'kleurplaten',
        'posts_per_page' => intval( $atts['posts_per_page'] ),
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $query_args );

    if ( ! $query->have_posts() ) {
        return '<p>' . __( 'No kleurplaten found.', 'kk' ) . '</p>';
    }

    ob_start();

    ?>
    <div class="kk-grid">

        <?php while ( $query->have_posts() ) : $query->the_post(); ?>

            <article class="kk-card">

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="kk-card-image">
                        <?php the_post_thumbnail( 'medium' ); ?>
                    </div>
                <?php endif; ?>

                <div class="kk-card-content">

                    <h2 class="kk-card-title">
                        <?php echo esc_html( get_the_title() ); ?>
                    </h2>

                    <?php if ( get_the_excerpt() ) : ?>
                        <p class="kk-card-excerpt">
                            <?php echo esc_html( get_the_excerpt() ); ?>
                        </p>
                    <?php endif; ?>

                    <div class="kk-card-actions">

                        <?php
                        $print_url = get_post_meta( get_the_ID(), '_kk_print_url', true );
                        if ( ! empty( $print_url ) ) : ?>
                            <a class="kk-btn kk-btn--print"
                               href="<?php echo esc_url( $print_url ); ?>"
                               target="_blank"
                               rel="noopener noreferrer">
                                <?php _e( 'Print', 'kk' ); ?>
                            </a>
                        <?php endif; ?>

                        <?php
                        $svg_url = get_post_meta( get_the_ID(), '_kk_svg_url', true );
                        if ( ! empty( $svg_url ) ) : ?>
                            <a class="kk-btn kk-btn--download"
                               href="<?php echo esc_url( $svg_url ); ?>">
                                <?php _e( 'Download SVG', 'kk' ); ?>
                            </a>
                        <?php endif; ?>

                    </div>

                </div>

            </article>

        <?php endwhile; ?>

    </div>
    <?php

    wp_reset_postdata();

    return ob_get_clean();

}
add_shortcode( 'kk_homepage_grid', 'kk_homepage_grid_shortcode' );
