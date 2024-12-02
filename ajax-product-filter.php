<?php
/**
 * Plugin Name: AJAX Product Filter for WooCommerce Shop
 * Description: A plugin that adds AJAX-based product filtering to WooCommerce.
 * Version: 1.0
 * Author: Mausam10
 * Text Domain: ajax-product-filter
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Define constants
define( 'APF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include files
require_once APF_PLUGIN_DIR . 'includes/ajax-product-filter-widget.php';
require_once APF_PLUGIN_DIR . 'includes/ajax-product-filter-functions.php';
require_once APF_PLUGIN_DIR . 'includes/ajax-product-filter-ajax.php';

// Enqueue assets
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'apf-styles', plugins_url( 'assets/css/styles.css', __FILE__ ) );
    wp_enqueue_script( 'apf-scripts', plugins_url( 'assets/js/ajax-filter.js', __FILE__ ), array( 'jquery' ), null, true );
    wp_localize_script( 'apf-scripts', 'apf_vars', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
    ));
});

// Enqueue scripts and styles
add_action( 'wp_enqueue_scripts', 'apf_enqueue_scripts' );
function apf_enqueue_scripts() {
    wp_enqueue_style( 'apf-styles', plugins_url( '/assets/css/styles.css', __FILE__ ) );
    wp_enqueue_script( 'apf-scripts', plugins_url( '/assets/js/ajax-filter.js', __FILE__ ), array( 'jquery' ), null, true );

    wp_localize_script( 'apf-scripts', 'apf_vars', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ) );
}

// Shortcode for displaying filter form
add_shortcode( 'apf_filter', 'apf_filter_form' );
function apf_filter_form() {
    ob_start();
    ?>
    <form id="apf-filter">
        <select name="product_category">
            <option value="">Select Category</option>
            <?php
            $categories = get_terms( 'product_cat' );
            foreach ( $categories as $category ) {
                echo '<option value="' . esc_attr( $category->slug ) . '">' . esc_html( $category->name ) . '</option>';
            }
            ?>
        </select>
        <button type="submit">Filter</button>
    </form>
    <div id="apf-results"></div>
    <?php
    return ob_get_clean();
}
// Include the widget file
require_once APF_PLUGIN_DIR . 'includes/ajax-product-filter-widget.php';

// Register the widget
add_action( 'widgets_init', 'apf_register_widget' );
function apf_register_widget() {
    register_widget( 'APF_Product_Filter_Widget' );
}

// Add menu item in WooCommerce
add_action( 'admin_menu', 'apf_add_woocommerce_menu' );
function apf_add_woocommerce_menu() {
    add_submenu_page(
        'woocommerce',
        __( 'AJAX Filter Presets', 'ajax-product-filter' ),
        __( 'AJAX Filter Presets', 'ajax-product-filter' ),
        'manage_woocommerce',
        'apf-filter-presets',
        'apf_filter_presets_page'
    );
}

// Callback for the menu page
function apf_filter_presets_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Filter Presets', 'ajax-product-filter' ); ?></h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="preset_name"><?php _e( 'Preset Name', 'ajax-product-filter' ); ?></label></th>
                    <td><input type="text" id="preset_name" name="preset_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="taxonomy"><?php _e('Taxonomy', 'ajax-product-filter'); ?></label>
                    </th>
                    <td>
                        <select id="taxonomy" name="taxonomy" required>
                            <option value=""><?php _e('Select Taxonomy', 'ajax-product-filter'); ?></option>
                            <option value="product_cat"><?php _e('Category', 'ajax-product-filter'); ?></option>
                            <option value="product_tag"><?php _e('Tag', 'ajax-product-filter'); ?></option>
                            <?php
                            // Dynamically fetch product attributes
                            $attributes = wc_get_attribute_taxonomies();
                            foreach ($attributes as $attribute) {
                                echo '<option value="pa_' . esc_attr($attribute->attribute_name) . '">' . esc_html($attribute->attribute_label) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>

            </table>
            <?php submit_button( __( 'Save Preset', 'ajax-product-filter' ) ); ?>
        </form>
        <!-- Table for the Saved Preset -->
        <h2><?php _e( 'Saved Presets', 'ajax-product-filter' ); ?></h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th><?php _e( 'Preset Name', 'ajax-product-filter' ); ?></th>
                    <th><?php _e( 'Category', 'ajax-product-filter' ); ?></th>
                    <th><?php _e( 'Actions', 'ajax-product-filter' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $presets = get_option( 'apf_filter_presets', array() );
                if ( ! empty( $presets ) ) {
                    foreach ( $presets as $key => $preset ) {
                        echo '<tr>';
                        echo '<td>' . esc_html( $preset['name'] ) . '</td>';
                        echo '<td>' . esc_html( $preset['category'] ) . '</td>';
                        echo '<td><a href="?page=apf-filter-presets&delete=' . $key . '">' . __( 'Delete', 'ajax-product-filter' ) . '</a></td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="3">' . __( 'No presets found.', 'ajax-product-filter' ) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Handle saving and deleting presets
add_action( 'admin_init', 'apf_handle_presets' );
function apf_handle_presets() {
    if ( isset( $_POST['preset_name'] ) ) {
        $presets = get_option( 'apf_filter_presets', array() );
        $presets[] = array(
            'name' => sanitize_text_field( $_POST['preset_name'] ),
            'category' => sanitize_text_field( $_POST['preset_category'] ),
        );
        update_option( 'apf_filter_presets', $presets );
        wp_redirect( admin_url( 'admin.php?page=apf-filter-presets' ) );
        exit;
    }

    if ( isset( $_GET['delete'] ) ) {
        $presets = get_option( 'apf_filter_presets', array() );
        unset( $presets[ $_GET['delete'] ] );
        update_option( 'apf_filter_presets', $presets );
        wp_redirect( admin_url( 'admin.php?page=apf-filter-presets' ) );
        exit;
    }
}
