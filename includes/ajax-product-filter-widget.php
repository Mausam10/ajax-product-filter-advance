<?php
/**
 * AJAX Product Filter Widget
 */

class APF_Product_Filter_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'apf_product_filter_widget', // Base ID
            __( 'AJAX Product Filter', 'ajax-product-filter' ), // Name
            array( 'description' => __( 'A widget for AJAX product filtering', 'ajax-product-filter' ) ) // Args
        );
    }

    // Output the widget form in the admin
   // Modify widget form
public function form( $instance ) {
    $presets = get_option( 'apf_filter_presets', array() );
    $selected_preset = !empty( $instance['preset'] ) ? $instance['preset'] : '';
    ?>
    <p>
        <label for="<?php echo $this->get_field_id( 'preset' ); ?>"><?php _e( 'Select Preset:', 'ajax-product-filter' ); ?></label>
        <select id="<?php echo $this->get_field_id( 'preset' ); ?>" name="<?php echo $this->get_field_name( 'preset' ); ?>" class="widefat">
            <option value=""><?php _e( 'None', 'ajax-product-filter' ); ?></option>
            <?php
            foreach ( $presets as $key => $preset ) {
                echo '<option value="' . esc_attr( $key ) . '" ' . selected( $selected_preset, $key, false ) . '>' . esc_html( $preset['name'] ) . '</option>';
            }
            ?>
        </select>
    </p>
    <?php
}

// Update widget to display selected preset
public function widget( $args, $instance ) {
    echo $args['before_widget'];

    if ( ! empty( $instance['preset'] ) ) {
        $presets = get_option( 'apf_filter_presets', array() );
        $preset = $presets[ $instance['preset'] ];
        echo '<p>' . __( 'Preset:', 'ajax-product-filter' ) . ' ' . esc_html( $preset['name'] ) . '</p>';
    }

    echo do_shortcode( '[apf_filter]' );
    echo $args['after_widget'];
}

}
