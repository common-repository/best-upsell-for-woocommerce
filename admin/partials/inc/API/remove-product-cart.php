<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'remove-product-icart', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'remove_product_icart',
        'args' => array(),
        'permission_callback' => function () {
	        wp_set_current_user(GET_CURRENT_USER_ID);
            return true;
        }
    ));
});

function remove_product_icart(WP_REST_Request $request) {
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
    $itemkey= $icartadd->item_key;
    $items = WC()->cart->get_cart();
    $cartItemKey = $itemkey;
    if($icartadd->attributes && isset($icartadd->attributes) && !empty($icartadd->attributes)){
        WC()->session->set("attributes", $icartadd->attributes);
    }
    if($items[$cartItemKey]["key"] == $cartItemKey){
        WC()->cart->remove_cart_item( $cartItemKey );
        $items = WC()->cart->get_cart();
        $i = 0;
        foreach ( $items as $cart_item_key => $cart_item ) {
            $remove_cart_results['id'] = $cart_item['product_id'];
            $remove_cart_results['variant_id'] = ($cart_item['variation_id'] != 0) ? $cart_item['variation_id'] : $cart_item['product_id'];
            $remove_cart_results['name'] = $cart_item["data"]->get_name();
            $remove_cart_results['quantity'] = $cart_item['quantity'];
            $remove_cart_results['slug'] = $cart_item["data"]->get_slug();
            $remove_cart_results['sku'] = $cart_item["data"]->get_sku();
            $remove_cart_results['weight'] = $cart_item["data"]->get_weight();
            $remove_cart_results['length'] = $cart_item["data"]->get_length();
            $remove_cart_results['width'] = $cart_item["data"]->get_width();
            $remove_cart_results['height'] = $cart_item["data"]->get_height();
            $remove_cart_results['price'] = $cart_item["data"]->get_price();
            $remove_cart_results['regular_price'] = $cart_item["data"]->get_regular_price();
            $remove_cart_results['sale_price'] = $cart_item["data"]->get_sale_price();
            $remove_cart_results['key'] = $cart_item_key;
            $remove_cart_results['item_data'] = (isset($cart_item["item_data"])) ? $cart_item["item_data"]:[];
            if(wp_get_attachment_url($cart_item["data"]->get_image_id()) == false){
                $remove_cart_results['image'] = wc_placeholder_img_src();
            }
            else{
                $remove_cart_results['image'] = wp_get_attachment_url($cart_item["data"]->get_image_id());
            }
            $remove_cart_data_new[$cart_item_key] = array($remove_cart_results);
            $i++;
        }
        $remove_cart_data['items'] = $remove_cart_data_new;
        $attributes = WC()->session->get("attributes");
        $attributes = isset($attributes) ?json_encode($attributes) : null;
        $remove_cart_data['attributes'] = $attributes;
        $remove_cart_data['note'] = null;
        $remove_cart_data['cart_level_discount_applications'] = "";
        $remove_cart_data['currency'] = get_woocommerce_currency();
        $remove_cart_data['item_count'] = $i;
        $remove_cart_data['cart_count'] = WC()->cart->get_cart_contents_count();
        $remove_cart_data['subtotal_ex_tax'] = WC()->cart->subtotal_ex_tax;
        $remove_cart_data['subtotal'] = WC()->cart->subtotal;
        $remove_cart_data['displayed_subtotal'] = WC()->cart->get_displayed_subtotal();
        $remove_cart_data['taxes_total'] = WC()->cart->get_taxes_total();
        $remove_cart_data['shipping_total'] = WC()->cart->get_shipping_total();
        $remove_cart_data['coupons'] = WC()->cart->get_coupons();
        $remove_cart_data['coupon_discount_amoun'] = WC()->cart->get_coupon_discount_amount( 'coupon_code' );
        $remove_cart_data['fees'] = WC()->cart->get_fees();
        $remove_cart_data['discount_total'] = WC()->cart->get_discount_total();
        $remove_cart_data['total'] = WC()->cart->total;
        $remove_cart_data['tax_totals'] = WC()->cart->get_tax_totals();
        $remove_cart_data['cart_contents_tax'] = WC()->cart->get_cart_contents_tax();
        $remove_cart_data['fee_tax'] = WC()->cart->get_fee_tax();
        $remove_cart_data['discount_tax'] = WC()->cart->get_discount_tax();
        $remove_cart_data['shipping_taxes'] = WC()->cart->get_shipping_taxes();
        if(WC()->cart->get_shipping_total() == 0){
            $remove_cart_data['requires_shipping'] = false;
        }
        else{
            $remove_cart_data['requires_shipping'] = true;
        }
    }
    else{
        $remove_cart_data =  "This product not in cart";
    }
    return new WP_REST_Response($remove_cart_data,200);
}
