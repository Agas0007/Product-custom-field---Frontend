<?php

// The product custom field - Frontend
add_action( 'woocommerce_before_add_to_cart_button', 'custom_discount_price_product_field' );
function custom_discount_price_product_field() {
global $product;

$curs = get_woocommerce_currency_symbol(); // Currency symbol

// Get the discounted value (from product backend)
$discount = (float) get_post_meta( $product->get_id(), '_price_discount', true );

// jQuery will get the discount here for calculations
echo '<input type="hidden" name="price_discount" value="'.$discount.'">';

echo '<div>';

	woocommerce_form_field( 'select_price', array(
	'type' => 'select',
	'class' => array('my-field-class form-row-wide'),
	'label' => __('Discount'),
	'options' => array(
	'' => __( 'Select your discount', 'woocommerce' ),
	'5' => $curs . '5',
	'10' => $curs . '10',
	'15' => $curs . '15',
	'20' => $curs . '20',
	),
	), '' );

	// This field will be used to send the calculated price
	// jQuery will set the calculated price on this field
	echo '<input type="hidden" name="custom_price" value="52">'; // 52 is a fake value for testing purpose

	echo '</div><br>';

// BELOW your jquery code to calculate price and update "custom_price" hidden field
?>
<script type="text/javascript">
	jQuery(function($) {
		// Here
	});

</script>
<?php
}

// Add a custom field to product in backend
add_action( 'woocommerce_product_options_pricing', 'add_field_product_options_pricing' );
function add_field_product_options_pricing() {
    global $post;

    echo '<div class="options_group">';

    woocommerce_wp_text_input( array(
        'id'            => '_price_discount',
        'label'         => __('Discount price', 'woocommerce') . ' (%)',
        'placeholder'   => __('Set the Discount price…', 'woocommerce'),
        'description'   => __('Enter the custom value here.', 'woocommerce'),
        'desc_tip'      => 'true',
    ));

    echo '</div>';
}

// Save product custom field to database when submitted in Backend
add_action( 'woocommerce_process_product_meta', 'save_product_options_custom_fields', 30, 1 );
function save_product_options_custom_fields( $post_id ){
    // Saving custom field value
    if( isset( $_POST['_price_discount'] ) ){
        update_post_meta( $post_id, '_price_discount', sanitize_text_field( $_POST['_price_discount'] ) );
    }
}

// Add custom calculated price conditionally as custom data to cart items
add_filter( 'woocommerce_add_cart_item_data', 'add_custom_price_to_cart_item_data', 20, 2 );
function add_custom_price_to_cart_item_data( $cart_item_data, $product_id ){
    if( ! isset($_POST['custom_price']) )
        return $cart_item_data;

    $cart_item_data['custom_price'] = (float) sanitize_text_field( $_POST['custom_price'] );
    $cart_item_data['unique_key'] = md5( microtime() . rand() ); // Make each item unique

    return $cart_item_data;
}

// Set conditionally a custom item price
add_action('woocommerce_before_calculate_totals', 'set_cutom_cart_item_price', 20, 1);
function set_cutom_cart_item_price( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    foreach (  $cart->get_cart() as $cart_item ) {
        if ( isset( $cart_item['custom_price'] ) )
            $cart_item['data']->set_price( $cart_item['custom_price'] );
    }
}
