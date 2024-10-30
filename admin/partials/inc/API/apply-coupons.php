<?php
/**
 * @package iCart Cart Drawer Cart Upsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'apcd', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'apply_coupon',
        'args' => array(),
        'permission_callback' => function () {
	        wp_set_current_user(GET_CURRENT_USER_ID);
            return true;
        }
    ));
});

function apply_coupon(WP_REST_Request $request){

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

    if ( WC()->cart->has_discount( $coupon_code ) ){
        $coupon_code_response['status'] = "already applied";
        wc_clear_notices();
        return new WP_REST_Response($coupon_code_response, 200);
    }
    elseif( WC()->cart->apply_coupon($coupon_code) ) {
        $message = sprintf(__('Coupon code succesfully added'), $coupon_code);
        $coupon_code_response['status'] = $message;
        wc_clear_notices();
        return new WP_REST_Response($coupon_code_response, 200);
    }
    elseif( WC()->cart->apply_coupon( $coupon_code ) != $coupon_code ){
        $message = sprintf( __('Wrong coupon code'), $coupon_code);
        $coupon_code_response['status'] = $message;
        wc_clear_notices();
        return new WP_REST_Response($coupon_code_response,200);
    }
    else{
        WC()->cart->apply_coupon($coupon_code);
        $message = sprintf( __('Something Went Wrong'), $coupon_code);
        $coupon_code_response['status'] = $message;
        wc_clear_notices();
        return new WP_REST_Response($coupon_code_response,200);
    }
}

