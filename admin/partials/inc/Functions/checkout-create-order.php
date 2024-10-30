<?php
/**
 * @package Bestupsell
 */
add_action( 'woocommerce_checkout_create_order', 'change_total_on_checking', 20, 1 );
function change_total_on_checking( $order ) {
    $session_data = WC()->session->get( 'discount_attributes' );
    $authentication = WC()->session->get( 'authorization' );
    global $wpdb;
    $bestupsell = $wpdb->get_results( $wpdb->prepare( "SELECT node_url FROM ".$wpdb->prefix."identixweb_bestupsell") );
    $node_url = '';
    if(isset($bestupsell) && !empty($bestupsell)){
        $node_url = $bestupsell[0]->node_url;
    }

    $plugin_issue_url = $node_url.'/api';
    $args = [
        'headers' => [
            'Authentication' => $authentication,
            'Accept'=> 'application/json, text/javascript'
        ],
        'body'    => [
            'discount_attributes' => $session_data,
            'method_name' => 'verify_decrypt_attribute'
        ],
    ];
    $response = wp_remote_post( $plugin_issue_url, $args );
    $discount_attributes = json_decode($response['body']);
    $discount_attributes = json_decode($discount_attributes->data);
    $custom_sepcific_product_data_length = count((array)$discount_attributes->custom_specific_product_data);
    if($custom_sepcific_product_data_length > 0){
        $custom_sepcific_product_data = $discount_attributes->custom_specific_product_data;
        foreach ($custom_sepcific_product_data as $key => $value){
            $specific_product_price = $value->price;
            $specific_variant_id = $value->variant_id;
            $specific_product_id = $value->product_id;
            $quantity = 1;
            if($specific_variant_id != ""){
                $product = wc_get_product( $specific_variant_id );
                $product->set_price( $specific_product_price );
                $order->add_product( $product, $quantity);
                $order->calculate_totals();
                $order->save();
            }else{
                $product = wc_get_product( $specific_product_id );
                $product->set_price( $specific_product_price );
                $order->add_product( $product, $quantity);
                $order->calculate_totals();
                $order->save();
            }
        }
    }
    $discount_attributes = !empty($discount_attributes->discount_data) ? $discount_attributes->discount_data : $discount_attributes;
    if($discount_attributes->product_id !=""){
        $product_id = $discount_attributes->product_id;
	    $variant_id = $discount_attributes->variant_id;
        $new_product_price = 0;
        $quantity = 1;
        if($variant_id != ""){
	        $product = wc_get_product( $variant_id );
	        $product->set_price( $new_product_price );
	        $order->add_product( $product, $quantity);
	        $order->calculate_totals();
	        $order->save();
        }else{
	        $product = wc_get_product( $product_id );
	        $product->set_price( $new_product_price );
	        $order->add_product( $product, $quantity);
	        $order->calculate_totals();
	        $order->save();
        }
    }

    foreach( $order->get_items() as $item_id => $item ){
        /*$session_data = WC()->session->get( 'discount_attributes' );
        $discount_attributes = json_decode(base64_decode($session_data));*/
        $cart_item_key = $item->legacy_cart_item_key;
        if(isset($discount_attributes->$cart_item_key)){
            $product_price =  $item->get_total();
            // discount apply
            $new_line_item_price =(float)$product_price - (float)$discount_attributes->$cart_item_key;
            $item->set_subtotal( $new_line_item_price );
            $item->set_total( $new_line_item_price );
            // Make new taxes calculations
            $item->calculate_taxes();
            $item->save();
        }
    }
    $order->calculate_totals();
}
