<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'set-quantity-icart', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'set_quantity_icart',
        'args' => array(),
        'permission_callback' => function () {
	        wp_set_current_user(GET_CURRENT_USER_ID);
            return true;
        }
    ));
});

function set_quantity_icart(WP_REST_Request $request) {
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
    $qty = $icartadd->quantity;
    $items = WC()->cart->get_cart();
    $cartItemKey = $itemkey;
    if($icartadd->attributes && isset($icartadd->attributes)){
        WC()->session->set("attributes", $icartadd->attributes);
    }
    if($items[$cartItemKey]["key"] == $cartItemKey){
        WC()->cart->set_quantity( $cartItemKey, $qty);
        $items = WC()->cart->get_cart();
        $i = 0;
        foreach ( $items as $cart_item_key => $cart_item ) {
            $set_cart_results['id'] = $cart_item['product_id'];
            $set_cart_results['name'] = $cart_item["data"]->get_name();
            $set_cart_results['quantity'] = $cart_item['quantity'];
            $set_cart_results['slug'] = $cart_item["data"]->get_slug();
            $set_cart_results['sku'] = $cart_item["data"]->get_sku();
            $set_cart_results['weight'] = $cart_item["data"]->get_weight();
            $set_cart_results['length'] = $cart_item["data"]->get_length();
            $set_cart_results['width'] = $cart_item["data"]->get_width();
            $set_cart_results['height'] = $cart_item["data"]->get_height();
            $set_cart_results['price'] = $cart_item["data"]->get_price();
            $set_cart_results['regular_price'] = $cart_item["data"]->get_regular_price();
            $set_cart_results['sale_price'] = $cart_item["data"]->get_sale_price();
            $set_cart_results['product_in_stock'] = $cart_item["data"]->get_stock_quantity();
            $set_cart_results['stock_status'] = $cart_item["data"]->get_stock_status();
            if($cart_item["data"]->managing_stock() === 'parent'){
                $parentProduct = $cart_item["data"]->get_parent_data();
                if($parentProduct['manage_stock']=='yes'){
                    $set_cart_results['product_in_stock'] = $parentProduct["stock_quantity"];
                    if($parentProduct["backorders"] == 'no'){
                        $set_cart_results['stock_management'] = false;
                    }
                    else{
                        $set_cart_results['stock_management'] = true;
                    }
                }
            }else{
                if ( $cart_item["data"]->managing_stock() != 1 && ($set_cart_results['stock_status'] == "instock" || $set_cart_results['stock_status'] == "onbackorder" ) ){
                    $set_cart_results['stock_management'] = true;
                } else if($cart_item["data"]->backorders_allowed() == 1){
                    $set_cart_results['stock_management'] = true;
                } else{
                    $set_cart_results['stock_management'] = false;
                }
            }

            if(wp_get_attachment_url($cart_item["data"]->get_image_id()) == false){
                $set_cart_results['image'] = wc_placeholder_img_src();
            }
            else{
                $set_cart_results['image'] = wp_get_attachment_url($cart_item["data"]->get_image_id());
            }

            $set_cart_data[$cart_item_key] = array($set_cart_results);
            $i++;
        }
        $set_cart_data['product_count'] = $i;
        $set_cart_data['cart_count'] = WC()->cart->get_cart_contents_count();
        $set_cart_data['subtotal_ex_tax'] = WC()->cart->subtotal_ex_tax;
        $set_cart_data['subtotal'] = WC()->cart->subtotal;
        $set_cart_data['displayed_subtotal'] = WC()->cart->get_displayed_subtotal();
        $set_cart_data['taxes_total'] = WC()->cart->get_taxes_total();
        $set_cart_data['shipping_total'] = WC()->cart->get_shipping_total();
        $set_cart_data['coupons'] = WC()->cart->get_coupons();
        $set_cart_data['coupon_discount_amoun'] = WC()->cart->get_coupon_discount_amount( 'coupon_code' );
        $set_cart_data['fees'] = WC()->cart->get_fees();
        $set_cart_data['discount_total'] = WC()->cart->get_discount_total();
        $set_cart_data['total'] = WC()->cart->total;
        $set_cart_data['tax_totals'] = WC()->cart->get_tax_totals();
        $set_cart_data['cart_contents_tax'] = WC()->cart->get_cart_contents_tax();
        $set_cart_data['fee_tax'] = WC()->cart->get_fee_tax();
        $set_cart_data['discount_tax'] = WC()->cart->get_discount_tax();
        $set_cart_data['shipping_taxes'] = WC()->cart->get_shipping_taxes();
    }
    else{
        $set_cart_data =  "This product not in cart";
    }
    return new WP_REST_Response($set_cart_data,200);
}