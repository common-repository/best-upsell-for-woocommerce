<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'update-cart', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'update_cart',
        'args' => array(),
        'permission_callback' => function () {
	        wp_set_current_user(GET_CURRENT_USER_ID);
            return true;
        }
    ));
});

function update_cart(WP_REST_Request $request) {
    if ( defined( 'WC_ABSPATH' ) ) {
        // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
        /*include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
        include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
        include_once WC_ABSPATH . 'includes/wc-template-hooks.php';*/
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
    $icartadd = json_decode($request->get_body());
    $item_key = $icartadd->item_key;
    $item_data = $icartadd->item_data;
    $cart = WC()->cart->cart_contents;
    $attributes = $icartadd->attributes;
    foreach( $cart as $cart_item_id=>$cart_item ) {
        if($cart_item_id == $item_key){
            if(isset($cart_item['item_data']) && !empty($cart_item['item_data'])) {
                $cart_item['item_data'] = $item_data;
                WC()->cart->cart_contents[$cart_item_id] = $cart_item;
            }
        }
    }
    WC()->cart->set_session();
    WC()->session->set("attributes", $attributes);
    $new_item = WC()->cart->get_cart();
    return new WP_REST_Response($new_item,200);
}
