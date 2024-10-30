<?php
/**
 * @package Bestupsell
 */

function cloudways_display_order_data( $order_id ){
    if(WC()->session->get( 'attributes' )) {
        $get_session = WC()->session->get('attributes');
        $discount_attributes = WC()->session->get('discount_attributes');
        update_post_meta( $order_id, 'bestupsell_text_attribute', $get_session);
        /*update_post_meta( $order_id, 'bestupsell_text_discount_attribute', $discount_attributes);*/
    }
}
add_action( 'woocommerce_thankyou', 'cloudways_display_order_data', 20 );
add_action( 'woocommerce_view_order', 'cloudways_display_order_data', 20 );

/* display order data in admin panel */

function cloudways_display_order_data_in_admin( $order ){
    $upsell_info = get_post_meta( $order->get_id(), 'bestupsell_text_attribute', true );
    if($upsell_info != ''){ ?>
        <div class="form-field form-field-wide wc-customer-user">
            <h4><?php esc_html_e( 'Additional Information', 'woocommerce' ); ?></h4>
            <p style="word-break:break-all;"><?php echo json_encode($upsell_info); ?></p>
        </div><?php
    }
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'cloudways_display_order_data_in_admin' );

