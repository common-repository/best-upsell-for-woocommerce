<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'bundle-cart', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'bundle_product',
        'args' => array(),
        'permission_callback' => function () {
	        wp_set_current_user(GET_CURRENT_USER_ID);
            return true;
        }
    ));
});

function bundle_product(WP_REST_Request $request){
    if ( defined( 'WC_ABSPATH' ) ) {
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
    $item_data = json_decode($request->get_body());
    if(!empty($item_data->items) && isset($item_data->items)){
        $items = $item_data->items;
	    foreach ($items as $product_data){
		    $product_id = $product_data->product_id;
		    $quantity = $product_data->quantity;
		    $variation_id = $product_data->variation_id;
		    $prod_type = wc_get_product($product_id);
		    $variant_type = wc_get_product($variation_id);
		    $stock_available = $prod_type->get_stock_quantity();
		    $product_name = $prod_type->get_title();
		    $variant_stock_available = ($variant_type == true) ? $variant_type->get_stock_quantity() : 0 ;
		    if(isset($variation_id) && !empty($variation_id)){
			    $stock_available = $variant_stock_available;
		    }
		    $qty = 0;
		    $manage_stock = ($variant_type == true) ? $variant_type->get_manage_stock() :$prod_type->get_manage_stock();
		    if ($manage_stock == false) {
			    $item_data1["item_data"] = (array)$product_data->item_data;
		    }else{
			    if ($stock_available === (int)$qty) { // check if the product stock and cart quantity is same
				    $not_in_stock['error'] = "You Cannot Add That Amount Of Product $product_name  To The Cart Because There Is Not Enough Stock (We Have Total $stock_available Quantity).";
				    return new WP_REST_Response($not_in_stock, 200);
			    } elseif ($stock_available < (int)$quantity + (int)$qty) { // check if the product stock is less than input quantity
				    $not_in_stock['error'] = "You Cannot Add That Amount Of Product $product_name To The Cart Because There Is Not Enough Stock (We Have Total $stock_available Quantity).";
				    return new WP_REST_Response($not_in_stock, 200);
			    }

		    }
	    }

        foreach ( $items as $key => $product_data ) {
            $item_data1 = [];
            $product_id = $product_data->product_id;
            $quantity = $product_data->quantity;
            $variation_id = $product_data->variation_id;
            $item_data1["item_data"] = (array)$product_data->item_data;
            $variable = WC()->cart->add_to_cart( $product_id,$quantity,$variation_id,'',$item_data1);
            $product_item_key = '';
            $cart_data = WC()->cart->get_cart();
            foreach ($cart_data as $cart_item_key => $cart_item) {
                if (in_array($product_id, array($cart_item['product_id']))) {
                    $product_item_key = $cart_item_key;
                }
            }
            if($item_data->attributes && isset($item_data->attributes)) {
                $attributes = $item_data->attributes;
                foreach($attributes as $x => $val) {
                    foreach($val as $val1){
                        $attributesData = $val1[0];
                        if($attributesData->product_id == $product_id) {
                            $attributesData->product_item_key = $product_item_key;
                        }
                    }
                }
            }
        }
        $attributes = $item_data->attributes;
        if($attributes && isset($attributes)){
            WC()->session->set("attributes", $attributes);
        }
        $cart_data = WC()->cart->get_cart();
        $get_attributes = WC()->session->get("attributes");
        $get_attributes = isset($get_attributes) ?json_encode($get_attributes) : null;
        $cart_data['attributes'] = $get_attributes;
        return new WP_REST_Response($cart_data,200);
    }else if(!empty($item_data->custom_product) && isset($item_data->custom_product)){
        $custom_data =$item_data->custom_product;
        $custom_attributes_data = $custom_data->attributes;
        $custom_datas = $custom_data->custom_product_data;
        $custom_product_datas = array();
        foreach ($custom_datas as $custom_data_key=>$custom_Product_data){
            $item_data1 = [];
            $product_id = $custom_Product_data->product_id;
            $variant_id = $custom_Product_data->variant_id;
            $quantity = 1;
            $item_data1["item_data"] = "custom_data";
            $datas = WC()->cart->add_to_cart( $product_id,$quantity,$variant_id,'',$item_data1);
            array_push($custom_product_datas,$datas);
        }
	    if($custom_attributes_data && isset($custom_attributes_data)){
		    WC()->session->set("attributes", $custom_attributes_data);
	    }
        return new WP_REST_Response($custom_product_datas,200);
    }else if(!empty($item_data->free_product) && isset($item_data->free_product)){
	    $free_product =$item_data->free_product;
	    $attributes = $free_product->attributes;
	    $item_data1 = [];
	    $product_id = $free_product->product_id;
        $variant_id = $free_product->variant_id;
        $id = (isset($variant_id) && !empty($variant_id)) ? $variant_id : $product_id;
        $_product = wc_get_product( $id );
	    $quantity = $free_product->quantity;
	    $item_data1["item_data"] = "progress_bar_free_product";
	    $datas = WC()->cart->add_to_cart( $product_id,$quantity,$variant_id,'',$item_data1);
	    if($attributes && isset($attributes)){
		    WC()->session->set("attributes", $attributes);
	    }
        $price = $_product->get_price();
	    $abc = (array("item_key"=>$datas,"price"=>$price));
	    return new WP_REST_Response($abc,200);
    } else{
        return new WP_REST_Response(true,200);
    }
}
