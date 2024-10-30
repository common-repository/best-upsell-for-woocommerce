<?php
/**
 * @package iCart Cart Drawer Cart Upsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'rpcd', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'remove_coupons',
        'args' => array(),
        'permission_callback' => function () {
	        wp_set_current_user(GET_CURRENT_USER_ID);
            return true;
        }
    ));
});

function remove_coupons(WP_REST_Request $request){
    if ( defined( 'WC_ABSPATH' ) ) {
        // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
        include_once WP_PLUGIN_DIR.'/woocommerce/includes/wc-cart-functions.php';
        include_once WP_PLUGIN_DIR.'/woocommerce/includes/wc-notice-functions.php';
        include_once WP_PLUGIN_DIR.'/woocommerce/includes/wc-template-hooks.php';
    }
    if ( null === WC()->session ) {
        $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
        WC()->session = new $session_class();
        WC()->session->init();
    }

    if ( null === WC()->customer ) {
        WC()->customer = new WC_Customer( get_current_user_id(), true );
    }

    if ( null === WC()->cart ) {
        WC()->cart = new WC_Cart();

        // We need to force a refresh of the cart contents from session here (cart contents are normally refreshed on wp_loaded, which has already happened by this point).
        WC()->cart->get_cart();
    }

    $coupon_item = json_decode($request->get_body());
    $coupon_code = $coupon_item->coupon_code;

    if( WC()->cart->remove_coupon( sanitize_text_field( $coupon_code )) ){
        $message = sprintf( __('succesfully removed'), $coupon_code);
        $coupon_codea['status'] = $message;
        wc_clear_notices();
        return new WP_REST_Response($coupon_codea,200);
    }
}

