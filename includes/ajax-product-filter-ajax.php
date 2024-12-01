<?php

add_action( 'wp_ajax_apf_filter', 'apf_filter_products' );
add_action( 'wp_ajax_nopriv_apf_filter', 'apf_filter_products' );

function apf_filter_products() {
    // Parse the submitted data
    $category = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    );

    if ( $category ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category,
            ),
        );
    }

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            wc_get_template_part( 'content', 'product' );
        }
    } else {
        echo '<p>No products found.</p>';
    }

    wp_die();
}
