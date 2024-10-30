<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'set-discount-session', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'set_discount_session',
        'args' => array(),
        'permission_callback' => function () {
	        wp_set_current_user(GET_CURRENT_USER_ID);
            return true;
        }
    ));
});

function set_discount_session(WP_REST_Request $request){

	if( !function_exists('apache_request_headers') ) {
		function apache_request_headers() {
			$arh = array();
			$rx_http = '/\AHTTP_/';
			foreach ($_SERVER as $key => $val) {
				if (preg_match($rx_http, $key)) {
					$arh_key = preg_replace($rx_http, '', $key);
					$rx_matches = array();
					/* do some nasty string manipulations to restore the original letter case */
					/* this should work in most cases */
					$rx_matches = explode('_', $arh_key);
					if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
						foreach ($rx_matches as $ak_key => $ak_val)
							$rx_matches[$ak_key] = ucfirst($ak_val);
						$arh_key = implode('-', $rx_matches);
					}
					$arh[$arh_key] = $val;
				}
			}
			return( $arh );
		}
	}

    $headers = apache_request_headers();
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

	if(isset($headers['AUTHENTICATION']) && $headers['AUTHENTICATION'] != ''){
		$authentication_header = $headers['AUTHENTICATION'];
	}else{
		$authentication_header = $headers['Authentication'];
	}

    $discount_item = $request->get_params();
    WC()->session->set("discount_attributes", $discount_item['discount_attributes']);
    WC()->session->set("authorization", $authentication_header);
    $session_data = WC()->session->get( 'discount_attributes' );
    if($discount_item["discount_attributes"] == $session_data){
        return new WP_REST_Response(true,200);
    }else{
        return new WP_REST_Response(false,500);
    }
}
