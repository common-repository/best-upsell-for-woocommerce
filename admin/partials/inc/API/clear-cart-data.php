<?php
/**
 * @package Bestupsell
 */

//creat API for clear product data
add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'clear-cart', array(
        'methods' => 'GET',
        'callback' => 'clear_cart',
        'args' => array(),
        'permission_callback' => function () {
	        wp_set_current_user(GET_CURRENT_USER_ID);
            return true;
        }
    ));
});

//register function to get cart data
function clear_cart(WP_REST_Request $request)
{
    if (defined('WC_ABSPATH')) {
        // WC 3.6+ - Cart and other frontend functions are not included for REST requests.
        /*include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
        include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
        include_once WC_ABSPATH . 'includes/wc-template-hooks.php';*/
        include_once WP_PLUGIN_DIR.'/woocommerce/includes/wc-cart-functions.php';
        include_once WP_PLUGIN_DIR.'/woocommerce/includes/wc-notice-functions.php';
        include_once WP_PLUGIN_DIR.'/woocommerce/includes/wc-template-hooks.php';
    }

    if (null === WC()->session) {
        $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');

        WC()->session = new $session_class();
        WC()->session->init();
    }

    if (null === WC()->customer) {
        WC()->customer = new WC_Customer(get_current_user_id(), true);
    }

    if (null === WC()->cart) {
        WC()->cart = new WC_Cart();

        // We need to force a refresh of the cart contents from session here (cart contents are normally refreshed on wp_loaded, which has already happened by this point).
        WC()->cart->get_cart();
    }

    WC()->cart->empty_cart();
    WC()->session->set('cart', array()); // Empty the session cart data

    if ( WC()->cart->is_empty() ) {
        $result['clear_cart'] = 'Cart is cleared.';
        return new WP_REST_Response($result,200);
    } else {
        $result['clear_cart'] = 'Clearing the cart failed!';
        return new WP_REST_Response($result,500);
    }

}

