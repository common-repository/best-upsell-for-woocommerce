<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'add-cart', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'add_icart',
        'args' => array(),
        'permission_callback' => function () {
	        wp_set_current_user(GET_CURRENT_USER_ID);
            return true;
        }
    ));
});

function add_icart(WP_REST_Request $request) {
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

	/*$icartadd = json_decode($request->get_body());*/
	$icartadd = (object)$request->get_params();
    $item_data = [];
    if(isset($icartadd->item_data)){
        $item_data["item_data"] = (array)$icartadd->item_data;
    }
    $prodid= $icartadd->product_id;
    $qty = $icartadd->quantity;
    $variant_id = $icartadd->variation_id;
    $prod_type = wc_get_product($prodid);
    $variant_type = wc_get_product($variant_id);
/*    if($icartadd->attributes && isset($icartadd->attributes)){
        WC()->session->set("attributes", $icartadd->attributes);
    }*/
    $stock_available = $prod_type->get_stock_quantity();
    $variant_stock_available = ($variant_type == true) ? $variant_type->get_stock_quantity() : 0 ;
    if($prod_type == false){
        $not_in_stock['error'] = "Product not exist";
        return new WP_REST_Response($not_in_stock,200);
    }
    elseif( $prod_type->is_type( 'grouped' ) ) {
        foreach ($qty as $id => $itemqty) {
            WC()->cart->add_to_cart($id, $itemqty, $variant_id);
        }
    }
    elseif( $prod_type->is_type( 'variation' ) ) {
        $not_in_stock['error'] = "Product not exist";
        return new WP_REST_Response($not_in_stock,200);
    }
    else {
        $items = WC()->cart->get_cart();
        if (!empty($items)) { // if cart data is not null
            $manage_stock = ($variant_type == true) ? $variant_type->get_manage_stock() :$prod_type->get_manage_stock();
            if ($manage_stock == false) {
                WC()->cart->add_to_cart($prodid, $qty, $variant_id, [], $item_data);
            } else {
                $quantity = 0;
                foreach ($items as $cart_item) { // cart data loop for get quantity and available stock
                    if ($cart_item['variation_id'] === (int)$variant_id && !empty($variant_id)) { // if product type is variant and the product is inside of cart data
                        $quantity = $cart_item['quantity'];
                        $stock_available = $cart_item["data"]->get_stock_quantity();
                    } elseif ($cart_item['product_id'] === (int)$prodid && empty($variant_id)) { // if product type is simple and the product is inside of cart data
                        $quantity = $cart_item['quantity'];
                        $stock_available = $cart_item["data"]->get_stock_quantity();
                    } elseif ($cart_item['variation_id'] !== (int)$variant_id && !empty($variant_id)) { // if product type is variant and product is not in cart data
                        $stock_available = $variant_stock_available;
                    } elseif ($cart_item['product_id'] !== (int)$prodid && empty($variant_id)) { // if product type is simple and product is not in cart data
                        $stock_available = $stock_available; // not required but add for flow
                    }
                }
    //            You cannot add that amount of $product_name product to the cart because there is not enough stock (we have $qty_remaining remaining).
                if ($stock_available === (int)$quantity) { // check if the product stock and cart quantity is same
                    $not_in_stock['error'] = "You Cannot Add That Amount Of Product To The Cart Because There Is Not Enough Stock (We Have Total $stock_available Quantity).";
                    return new WP_REST_Response($not_in_stock, 200);
                } elseif ($stock_available < (int)$quantity + (int)$qty) { // check if the product stock is less than input quantity
                    $not_in_stock['error'] = "You Cannot Add That Amount Of Product To The Cart Because There Is Not Enough Stock (We Have Total $stock_available Quantity).";
                    return new WP_REST_Response($not_in_stock, 200);
                } else { // add to cart
                    WC()->cart->add_to_cart($prodid, $qty, $variant_id, [], $item_data);
                }
            }
        }
        else{ // if cart data is null
            $manage_stock = ($variant_type == true) ? $variant_type->get_manage_stock() :$prod_type->get_manage_stock();
            if ($manage_stock == false) {
                WC()->cart->add_to_cart($prodid, $qty, $variant_id, [], $item_data);
            } else {
                if ($variant_stock_available < $qty && !empty($variant_id)) { // if product type is variant and input quantity is bigger than product stock
                    $not_in_stock['error'] = "You Cannot Add That Amount Of Product To The Cart Because There Is Not Enough Stock (We Have Total $variant_stock_available Quantity).";
                    return new WP_REST_Response($not_in_stock, 200);
                } elseif ($stock_available < $qty && empty($variant_id)) { // if product type is simple and input quantity is bigger than product stock
                    $not_in_stock['error'] = "You Cannot Add That Amount Of Product To The Cart Because There Is Not Enough Stock (We Have Total $stock_available Quantity).";
                    return new WP_REST_Response($not_in_stock, 200);
                } else { // add to cart
                    WC()->cart->add_to_cart($prodid, $qty, $variant_id, [], $item_data);
                }
            }
        }
    }
    $items = WC()->cart->get_cart();
    $quantity = 0;
    foreach ( $items as $cart_item ) {
        if ($cart_item['variation_id'] === (int)$variant_id && !empty($variant_id)) {
            $quantity = $cart_item['quantity'];
            $stock_available = $cart_item["data"]->get_stock_quantity();
        } elseif($cart_item['product_id'] === (int)$prodid && empty($variant_id)) {
            $quantity = $cart_item['quantity'];
            $stock_available = $cart_item["data"]->get_stock_quantity();
        }
    }
    $product_item_key = '';
    $items = WC()->cart->get_cart();
    foreach ($items as $cart_item_key => $cart_item) {
        if (in_array($prodid, array($cart_item['product_id']))) {
            $product_item_key = $cart_item_key;
        }
    }
    if($icartadd->attributes && isset($icartadd->attributes)) {
        $attributes = $icartadd->attributes;
        foreach($attributes as $x => $val) {
            foreach($val as $val1){
                $attributesData = $val1[0];
               if($attributesData->product_id == $prodid) {
                   $attributesData->product_item_key = $product_item_key;
               }
            }
        }
        WC()->session->set("attributes", $attributes);
    }

    $items = WC()->cart->get_cart();
    if($prod_type->is_in_stock() && $stock_available >= (int)$quantity && !$prod_type->is_type( 'grouped' )) { // check product type is simple and product quantity is small than product stock
        foreach ($items as $cart_item_key => $cart_item) {
            $add_cart_result['item_key'] = $cart_item_key;
            $add_cart_result['id'] = $cart_item['product_id'];
            $add_cart_result['quantity'] = $cart_item['quantity'];
            $add_cart_result['slug'] = $cart_item["data"]->get_slug();
            $add_cart_result['sku'] = $cart_item["data"]->get_sku();
            $add_cart_result['price'] = $cart_item["data"]->get_price();
            $add_cart_result['regular_price'] = $cart_item["data"]->get_regular_price();
            $add_cart_result['sale_price'] = $cart_item["data"]->get_sale_price();
            if(wp_get_attachment_url($cart_item["data"]->get_image_id()) == false){
                $add_cart_result['image'] = wc_placeholder_img_src();
            }
            else{
                $add_cart_result['image'] = wp_get_attachment_url($cart_item["data"]->get_image_id());
            }
            if (!is_null($variant_id)) {
                if (in_array($prodid, array($cart_item['product_id']))) {
                    $add_cart_result['name'] = $cart_item["data"]->get_title();
                    $add_cart_result['variant_name'] = $cart_item["data"]->get_name();
                    $add_cart_result['variation_id'] = $cart_item["variation_id"];
                    $add_cart_result['variant_title'] = $cart_item['variation'];
                    break; // stop the loop if product is found
                }
            }
            if (is_null($variant_id)) {
                if (in_array($prodid, array($cart_item['product_id']))) {
                    $add_cart_result['name'] = $cart_item["data"]->get_name();
                    break; // stop the loop if product is found
                }
            }
        }
        $add_cart_result['cart_contents_count'] = WC()->cart->get_cart_contents_count();
        $add_cart_result['subtotal_ex_tax'] = WC()->cart->subtotal_ex_tax;
        $add_cart_result['subtotal'] = WC()->cart->subtotal;
        $add_cart_result['displayed_subtotal'] = WC()->cart->get_displayed_subtotal();
        $add_cart_result['taxes_total'] = WC()->cart->get_taxes_total();
        $add_cart_result['shipping_total'] = WC()->cart->get_shipping_total();
        $add_cart_result['coupons'] = WC()->cart->get_coupons();
        $add_cart_result['coupon_discount_amoun'] = WC()->cart->get_coupon_discount_amount('coupon_code');
        $add_cart_result['fees'] = WC()->cart->get_fees();
        $add_cart_result['discount_total'] = WC()->cart->get_discount_total();
        $add_cart_result['total'] = WC()->cart->total;
        $add_cart_result['tax_totals'] = WC()->cart->get_tax_totals();
        $add_cart_result['cart_contents_tax'] = WC()->cart->get_cart_contents_tax();
        $add_cart_result['fee_tax'] = WC()->cart->get_fee_tax();
        $add_cart_result['discount_tax'] = WC()->cart->get_discount_tax();
        $add_cart_result['shipping_taxes'] = WC()->cart->get_shipping_taxes();
        return new WP_REST_Response($add_cart_result,200);
    }
    elseif (!$prod_type->managing_stock() && $prod_type->is_type( 'variable' )){
        $product_name = $prod_type->get_title();
        $var_data = "";
        $variations = $prod_type->get_available_variations();

        foreach ($variations as $variation) {
            if($variation['variation_id'] == $variant_id){
                $var_data = $variation['max_qty'];
            }
        }
        if(!$var_data == "") {
            if ($var_data < $quantity + $qty) {
                $add_cart_result['error'] = "You cannot add that amount of $product_name product to the cart because there is not enough stock (we have $var_data remaining).";
                return new WP_REST_Response($add_cart_result, 200);
            }
        }
        else{
            foreach ($items as $cart_item_key => $cart_item) {
                if (!is_null($variant_id)) {
                    if (in_array($prodid, array($cart_item['product_id']))) {
                        $add_cart_result['item_key'] = $cart_item_key;
                        $add_cart_result['id'] = $cart_item['product_id'];
                        $add_cart_result['name'] = $cart_item["data"]->get_title();
                        $add_cart_result['variant_name'] = $cart_item["data"]->get_name();
                        $add_cart_result['quantity'] = $cart_item['quantity'];
                        $add_cart_result['slug'] = $cart_item["data"]->get_slug();
                        $add_cart_result['sku'] = $cart_item["data"]->get_sku();
                        $add_cart_result['price'] = $cart_item["data"]->get_price();
                        $add_cart_result['regular_price'] = $cart_item["data"]->get_regular_price();
                        $add_cart_result['sale_price'] = $cart_item["data"]->get_sale_price();
                        if (wp_get_attachment_url($cart_item["data"]->get_image_id()) == false) {
                            $add_cart_result['image'] = wc_placeholder_img_src();
                        } else {
                            $add_cart_result['image'] = wp_get_attachment_url($cart_item["data"]->get_image_id());
                        }
                        $add_cart_result['variation_id'] = $cart_item["variation_id"];
                        $add_cart_result['variant_title'] = $cart_item['variation'];
                        break; // stop the loop if product is found
                    }
                }
            }
            return new WP_REST_Response($add_cart_result,200);
        }
    }
    elseif ($prod_type->is_type( 'grouped' )){
        $items = WC()->cart->get_cart();
        foreach ($qty as $id => $itemqty) {
            $child_type = wc_get_product($id);
            $product_name = $child_type->get_title();
            $stock_available = $child_type->get_stock_quantity();
            $group_manage_stock = $child_type->get_manage_stock();
            if($group_manage_stock == false) {
                foreach ($items as $cart_item_key => $cart_item) {
                    $add_cart_results['item_key'] = $cart_item_key;
                    $add_cart_results['id'] = $cart_item['product_id'];
                    $add_cart_results['name'] = $cart_item["data"]->get_name();
                    $add_cart_results['quantity'] = $cart_item['quantity'];
                    $add_cart_results['slug'] = $cart_item["data"]->get_slug();
                    $add_cart_results['sku'] = $cart_item["data"]->get_sku();
                    $add_cart_results['price'] = $cart_item["data"]->get_price();
                    $add_cart_results['regular_price'] = $cart_item["data"]->get_regular_price();
                    $add_cart_results['sale_price'] = $cart_item["data"]->get_sale_price();
                    if (wp_get_attachment_url($cart_item["data"]->get_image_id()) == false) {
                        $add_cart_results['image'] = wc_placeholder_img_src();
                    } else {
                        $add_cart_results['image'] = wp_get_attachment_url($cart_item["data"]->get_image_id());
                    }
                    $add_cart_result[$cart_item_key] = array($add_cart_results);
                }
                $add_cart_result['cart_contents_count'] = WC()->cart->get_cart_contents_count();
                $add_cart_result['subtotal_ex_tax'] = WC()->cart->subtotal_ex_tax;
                $add_cart_result['subtotal'] = WC()->cart->subtotal;
                $add_cart_result['displayed_subtotal'] = WC()->cart->get_displayed_subtotal();
                $add_cart_result['taxes_total'] = WC()->cart->get_taxes_total();
                $add_cart_result['shipping_total'] = WC()->cart->get_shipping_total();
                $add_cart_result['coupons'] = WC()->cart->get_coupons();
                $add_cart_result['coupon_discount_amoun'] = WC()->cart->get_coupon_discount_amount('coupon_code');
                $add_cart_result['fees'] = WC()->cart->get_fees();
                $add_cart_result['discount_total'] = WC()->cart->get_discount_total();
                $add_cart_result['total'] = WC()->cart->total;
                $add_cart_result['tax_totals'] = WC()->cart->get_tax_totals();
                $add_cart_result['cart_contents_tax'] = WC()->cart->get_cart_contents_tax();
                $add_cart_result['fee_tax'] = WC()->cart->get_fee_tax();
                $add_cart_result['discount_tax'] = WC()->cart->get_discount_tax();
                $add_cart_result['shipping_taxes'] = WC()->cart->get_shipping_taxes();
            } else {
                $quantity = 0;
                foreach ( $items as $child_cart_item ) {
                    if ($child_cart_item['product_id'] === (int)$id) {
                        $quantity = $child_cart_item['quantity'];
                    }
                }
                if ($stock_available < $quantity + $itemqty) {
                    $add_cart_result['error'] = "You cannot add that amount of $product_name product to the cart because there is not enough stock.";
                }else {
                    foreach ($items as $cart_item_key => $cart_item) {
                        $add_cart_results['item_key'] = $cart_item_key;
                        $add_cart_results['id'] = $cart_item['product_id'];
                        $add_cart_results['name'] = $cart_item["data"]->get_name();
                        $add_cart_results['quantity'] = $cart_item['quantity'];
                        $add_cart_results['slug'] = $cart_item["data"]->get_slug();
                        $add_cart_results['sku'] = $cart_item["data"]->get_sku();
                        $add_cart_results['price'] = $cart_item["data"]->get_price();
                        $add_cart_results['regular_price'] = $cart_item["data"]->get_regular_price();
                        $add_cart_results['sale_price'] = $cart_item["data"]->get_sale_price();
                        if (wp_get_attachment_url($cart_item["data"]->get_image_id()) == false) {
                            $add_cart_results['image'] = wc_placeholder_img_src();
                        } else {
                            $add_cart_results['image'] = wp_get_attachment_url($cart_item["data"]->get_image_id());
                        }
                        $add_cart_result[$cart_item_key] = array($add_cart_results);
                    }

                    $add_cart_result['cart_contents_count'] = WC()->cart->get_cart_contents_count();
                    $add_cart_result['subtotal_ex_tax'] = WC()->cart->subtotal_ex_tax;
                    $add_cart_result['subtotal'] = WC()->cart->subtotal;
                    $add_cart_result['displayed_subtotal'] = WC()->cart->get_displayed_subtotal();
                    $add_cart_result['taxes_total'] = WC()->cart->get_taxes_total();
                    $add_cart_result['shipping_total'] = WC()->cart->get_shipping_total();
                    $add_cart_result['coupons'] = WC()->cart->get_coupons();
                    $add_cart_result['coupon_discount_amoun'] = WC()->cart->get_coupon_discount_amount('coupon_code');
                    $add_cart_result['fees'] = WC()->cart->get_fees();
                    $add_cart_result['discount_total'] = WC()->cart->get_discount_total();
                    $add_cart_result['total'] = WC()->cart->total;
                    $add_cart_result['tax_totals'] = WC()->cart->get_tax_totals();
                    $add_cart_result['cart_contents_tax'] = WC()->cart->get_cart_contents_tax();
                    $add_cart_result['fee_tax'] = WC()->cart->get_fee_tax();
                    $add_cart_result['discount_tax'] = WC()->cart->get_discount_tax();
                    $add_cart_result['shipping_taxes'] = WC()->cart->get_shipping_taxes();
                }
            }
        }
        return new WP_REST_Response($add_cart_result,200);
    }
    elseif(!$prod_type->managing_stock()) {
        foreach ($items as $cart_item_key => $cart_item) {
            $add_cart_result['item_key'] = $cart_item_key;
            $add_cart_result['id'] = $cart_item['product_id'];
            $add_cart_result['quantity'] = $cart_item['quantity'];
            $add_cart_result['slug'] = $cart_item["data"]->get_slug();
            $add_cart_result['sku'] = $cart_item["data"]->get_sku();
            $add_cart_result['price'] = $cart_item["data"]->get_price();
            $add_cart_result['regular_price'] = $cart_item["data"]->get_regular_price();
            $add_cart_result['sale_price'] = $cart_item["data"]->get_sale_price();
            if(wp_get_attachment_url($cart_item["data"]->get_image_id()) == false){
                $add_cart_result['image'] = wc_placeholder_img_src();
            }
            else{
                $add_cart_result['image'] = wp_get_attachment_url($cart_item["data"]->get_image_id());
            }
            if (!is_null($variant_id)) {
                if (in_array($prodid, array($cart_item['product_id']))) {
                    $add_cart_result['name'] = $cart_item["data"]->get_title();
                    $add_cart_result['variant_name'] = $cart_item["data"]->get_name();
                    $add_cart_result['variation_id'] = $cart_item["variation_id"];
                    $add_cart_result['variant_title'] = $cart_item['variation'];
                    break; // stop the loop if product is found
                }
            }
            if (is_null($variant_id)) {
                if (in_array($prodid, array($cart_item['product_id']))) {
                    $add_cart_result['name'] = $cart_item["data"]->get_name();
                    break; // stop the loop if product is found
                }
            }
        }
        $add_cart_result['cart_contents_count'] = WC()->cart->get_cart_contents_count();
        $add_cart_result['subtotal_ex_tax'] = WC()->cart->subtotal_ex_tax;
        $add_cart_result['subtotal'] = WC()->cart->subtotal;
        $add_cart_result['displayed_subtotal'] = WC()->cart->get_displayed_subtotal();
        $add_cart_result['taxes_total'] = WC()->cart->get_taxes_total();
        $add_cart_result['shipping_total'] = WC()->cart->get_shipping_total();
        $add_cart_result['coupons'] = WC()->cart->get_coupons();
        $add_cart_result['coupon_discount_amoun'] = WC()->cart->get_coupon_discount_amount('coupon_code');
        $add_cart_result['fees'] = WC()->cart->get_fees();
        $add_cart_result['discount_total'] = WC()->cart->get_discount_total();
        $add_cart_result['total'] = WC()->cart->total;
        $add_cart_result['tax_totals'] = WC()->cart->get_tax_totals();
        $add_cart_result['cart_contents_tax'] = WC()->cart->get_cart_contents_tax();
        $add_cart_result['fee_tax'] = WC()->cart->get_fee_tax();
        $add_cart_result['discount_tax'] = WC()->cart->get_discount_tax();
        $add_cart_result['shipping_taxes'] = WC()->cart->get_shipping_taxes();
        return new WP_REST_Response($add_cart_result,200);
    }
    elseif ( $prod_type->managing_stock() && ! $prod_type->backorders_allowed() ) {
        $product_name = $prod_type->get_title();
        $qty_remaining = $prod_type->get_stock_quantity();

        if ( $qty_remaining < $quantity + $qty ) {

            $not_in_stock['error'] = "You cannot add that amount of $product_name product to the cart because there is not enough stock (we have $qty_remaining remaining).";
            return new WP_REST_Response($not_in_stock,200);
        }
    }
}
