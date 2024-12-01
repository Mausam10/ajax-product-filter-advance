<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Save a filter preset to the database.
 *
 * @param string $name     Preset name.
 * @param string $category Category slug (optional).
 * @return bool Whether the preset was saved successfully.
 */
function apf_save_filter_preset( $name, $category = '' ) {
    $presets = get_option( 'apf_filter_presets', array() );

    // Add new preset
    $presets[] = array(
        'name'     => sanitize_text_field( $name ),
        'category' => sanitize_text_field( $category ),
    );

    // Update option
    return update_option( 'apf_filter_presets', $presets );
}

/**
 * Delete a filter preset by index.
 *
 * @param int $preset_index Index of the preset to delete.
 * @return bool Whether the preset was deleted successfully.
 */
function apf_delete_filter_preset( $preset_index ) {
    $presets = get_option( 'apf_filter_presets', array() );

    if ( isset( $presets[ $preset_index ] ) ) {
        unset( $presets[ $preset_index ] );

        // Reindex array and update option
        $presets = array_values( $presets );
        return update_option( 'apf_filter_presets', $presets );
    }

    return false;
}

/**
 * Get all saved filter presets.
 *
 * @return array List of saved presets.
 */
function apf_get_filter_presets() {
    return get_option( 'apf_filter_presets', array() );
}

/**
 * Get a specific filter preset by index.
 *
 * @param int $preset_index Index of the preset.
 * @return array|null Preset data or null if not found.
 */
function apf_get_filter_preset( $preset_index ) {
    $presets = apf_get_filter_presets();

    return isset( $presets[ $preset_index ] ) ? $presets[ $preset_index ] : null;
}

/**
 * Get WooCommerce product categories for use in forms or filters.
 *
 * @return array List of product categories (slug => name).
 */
function apf_get_product_categories() {
    $categories = get_terms( array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ) );

    $category_list = array();
    foreach ( $categories as $category ) {
        $category_list[ $category->slug ] = $category->name;
    }

    return $category_list;
}

/**
 * Render the filter form HTML.
 *
 * @param string $preset_id Optional preset ID to prefill the form.
 */
/**
 * Render the AJAX Product Filter form with a shortcode.
 */
function apf_render_filter_form() {
    $categories = apf_get_product_categories(); // Helper function to fetch categories
    ?>
    <form id="apf-filter" method="post">
        <label for="product_category"><?php _e( 'Select Category:', 'ajax-product-filter' ); ?></label>
        <select name="product_category" id="product_category">
            <option value=""><?php _e( 'All Categories', 'ajax-product-filter' ); ?></option>
            <?php foreach ( $categories as $slug => $name ) : ?>
                <option value="<?php echo esc_attr( $slug ); ?>">
                    <?php echo esc_html( $name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit"><?php _e( 'Filter', 'ajax-product-filter' ); ?></button>
    </form>
    <?php
}
add_shortcode( 'ajax_product_filter', 'apf_render_filter_form' );


/**
 * AJAX callback to load a filter preset.
 */
function apf_ajax_load_preset() {
    // Check nonce and permissions
    if ( ! check_ajax_referer( 'apf_ajax_nonce', 'nonce', false ) || ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json_error( __( 'Unauthorized request.', 'ajax-product-filter' ) );
    }

    $preset_id = intval( $_POST['preset_id'] ?? -1 );
    $preset    = apf_get_filter_preset( $preset_id );

    if ( $preset ) {
        ob_start();
        apf_render_filter_form( $preset_id );
        wp_send_json_success( ob_get_clean() );
    } else {
        wp_send_json_error( __( 'Preset not found.', 'ajax-product-filter' ) );
    }
}
add_action( 'wp_ajax_apf_load_preset', 'apf_ajax_load_preset' );


/**
 * Register a shortcode for the AJAX Product Filter form.
 */
function apf_filter_form_shortcode() {
    ob_start();
    apf_render_filter_form();
    return ob_get_clean();
}
add_shortcode( 'ajax_product_filter', 'apf_filter_form_shortcode' );



/**
 * AJAX callback to filter products based on category.
 */
function apf_ajax_filter_products() {
    $category = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
    );

    if ( ! empty( $category ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category,
            ),
        );
    }

    $query = new WP_Query( $args );

    ob_start();
    if ( $query->have_posts() ) {
        echo '<ul class="products">';
        while ( $query->have_posts() ) {
            $query->the_post();
            wc_get_template_part( 'content', 'product' ); // WooCommerce template for product grid
        }
        echo '</ul>';
    } else {
        echo '<p>' . __( 'No products found in this category.', 'ajax-product-filter' ) . '</p>';
    }
    wp_reset_postdata();

    wp_send_json_success( ob_get_clean() ); // Send the HTML content as a success response
}
add_action( 'wp_ajax_apf_filter', 'apf_ajax_filter_products' );
add_action( 'wp_ajax_nopriv_apf_filter', 'apf_ajax_filter_products' );


