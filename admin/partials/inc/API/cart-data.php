<?php
/**
 * @package Bestupsell
 */

/* creat API for get cart product data */
add_action('rest_api_init', function() {
	$user_id =  get_current_user_id();
	if($user_id == 0){
		$user_id = '';
	}
	define('GET_CURRENT_USER_ID',$user_id);
    register_rest_route('wc/v3', 'cartdata', array(
        'methods' => 'GET',
        'callback' => 'cart_data',
        'args' => array(),
        'permission_callback' => function () {
	        wp_set_current_user(GET_CURRENT_USER_ID);
            return true;
        }
    ));
});

function cart_data(WP_REST_Request $request)
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
    $items = WC()->cart->get_cart();
    $taxData = $taxPercantage = '';
    if(WC()->cart->get_tax_totals()){
        $taxData = WC()->cart->get_tax_totals();
        $tKey = key($taxData);
        $taxPercantage = WC_Tax::get_rate_percent($taxData[$tKey]->tax_rate_id);
        $taxPercantage = str_replace('%', '', $taxPercantage);
    }
    $product_resut_new1['note'] = null;
    $product_resut_new1['attributes'] = null;
    $product_resut_new1['coupons'] = WC()->cart->get_coupons();
	foreach ($product_resut_new1['coupons'] as $data) {
		$cpn = array();
		$cpn['discount_type'] = $data->get_discount_type('coupon_code');
		$cpn['discount_amount'] = $data->get_amount();
		$cpn['coupon_allowed_free_shipping'] = $data->get_free_shipping();
		$product_resut_new1['coupons'][$data->get_code()] = $cpn;
	}

 	/*$coupon = new WC_Coupon(key((array)$product_resut_new1['coupons']));*/
	/*$product_resut_new1['discount_type'] = $coupon->get_discount_type('coupon_code');
	$product_resut_new1['discount_amount']= $coupon->get_amount();*/
    $product_resut_new1['total_discount'] = WC()->cart->get_discount_total();
//    $product_resut_new1['subtotal'] = WC()->cart->subtotal;
    $product_resut_new1['item_count'] =  WC()->cart->get_cart_contents_count();
    $product_resut_new1['currency_code'] =  get_woocommerce_currency();
    $product_resut_new1['subtotal'] = WC()->cart->subtotal;
    $product_resut_new1['total'] = WC()->cart->total;
    $product_resut_new1['total_tax'] = $taxData;
    $product_resut_new1['tax_percantage'] = $taxPercantage;
    $product_resut_new1['content_tax'] = WC()->cart->get_cart_contents_tax();
    $product_resut_new1['fee_tax'] = WC()->cart->get_fee_tax();
    $product_resut_new1['discount_tax'] = WC()->cart->get_discount_tax();
    $product_resut_new1['total_shiping'] = WC()->cart->get_shipping_total();
    $product_resut_new1['shipping_taxes'] =  WC()->cart->get_shipping_taxes();
    $product_resut_new1['currency_code'] =  get_woocommerce_currency();
    $product_resut_new1['currency_symbol'] = get_woocommerce_currency_symbol();
    $attributes = WC()->session->get("attributes");
    $attributes = isset($attributes) ?json_encode($attributes) : null;
    if(WC()->cart->get_shipping_total() == 0){
        $product_resut_new1['requires_shipping'] = false;
    }
    else{
        $product_resut_new1['requires_shipping'] = true;
    }
    $cart_item["item_data"]=[];
    $dSetting = get_option( 'woocommerce_tax_display_cart' );
    $displayPriceInShop = get_option( 'woocommerce_tax_display_shop' );
    /* Product data item foreach loop iw0107 */
    foreach ($items as $cart_item_key => $cart_item) {

        $terms = get_the_terms( $cart_item['product_id'], 'product_tag' );
        $product_tag = array();
        $product_cat_name = array();
        $x = 0;
        if(is_array($terms)){
            while($x < count($terms)) {
                $product_tag[] = $terms[$x]->name;
                $x++;
            }
        }
        $terms = wp_get_post_terms( $cart_item['product_id'], 'product_cat' );
        foreach ($terms  as $term  ) {
            $product_cat_name[] = $term->term_id;
        }
        if($cart_item['variation_id'] != 0){
            $cart_item_id = $cart_item['variation_id'];
            $variation = new WC_Product_Variation($cart_item_id);
	        foreach ($variation->get_variation_attributes() as $value){
		        if(!empty($value)){
			        $variationName = implode(" / ", $variation->get_variation_attributes());
		        }else{
			        $variationName = '';
		        }
	        }
        }else{
            $cart_item_id = $cart_item['product_id'];
            $variationName = "";
        }
        $productData = wc_get_product($cart_item_id);
        $tax_rates = WC_Tax::get_rates( $productData->get_tax_class() );
        if (!empty($tax_rates)) {
            $tax_rate = reset($tax_rates);
            $product_resut['product_tax_per'] = $tax_rate['rate'];
        }else{
            $product_resut['product_tax_per'] = 0;
        }
        $product_resut['id'] = $cart_item_id;
        $product_resut['parent_id'] = $cart_item['product_id'];
        $product_resut['quantity'] = $cart_item['quantity'];
        $product_resut['tags'] = $product_tag;
        $product_resut['key'] = $cart_item_key;
        $product_resut['name'] = $cart_item["data"]->get_title();
        $product_resut['description'] = $cart_item["data"]->get_description();
        $product_resut['sku'] = $cart_item["data"]->get_sku();
        $product_resut['slug'] = $cart_item["data"]->get_slug();
        $product_resut['permalink'] = $cart_item["data"]->get_permalink();
	    if(PRODUCT_ADD_ONS_WOOCOMMERCE == 1) {
		    if ($dSetting == 'incl') {
			    $product_resut['price'] = wc_format_decimal(wc_get_price_including_tax( $productData, array( 'price' => $cart_item["data"]->get_price(),'qty'=>$cart_item['quantity'] )));
		    }else{
		        $product_resut['price'] = $cart_item["data"]->get_price();
		    }
	    }else{
		    if ($dSetting == 'incl') {
			    $product_resut['price'] = wc_format_decimal(wc_get_price_including_tax($productData), 2);
		    } else {
			    $product_resut['price'] = wc_format_decimal(wc_get_price_excluding_tax($productData), 2);
		    }
	    }
        $product_resut['regular_price'] = $cart_item["data"]->get_regular_price();
        $product_resut['sale_price'] = $cart_item["data"]->get_sale_price();
        $product_resut['subtotal'] = WC()->cart->subtotal;
        $product_resut['subtotal_ex_tax'] = WC()->cart->subtotal_ex_tax;
        $product_resut['displayed_subtotal'] = WC()->cart->get_displayed_subtotal();
        $product_resut['taxes_total'] = WC()->cart->get_taxes_total();
        $product_resut['shipping_total'] = WC()->cart->get_shipping_total();
//        $product_resut['coupons'] = WC()->cart->get_coupons();
        $product_resut['total'] = WC()->cart->total;
        $product_resut['tax_totals'] = WC()->cart->get_tax_totals();
        $product_resut['cart_contents_tax'] = WC()->cart->get_cart_contents_tax();
        $product_resut['fee_tax'] = WC()->cart->get_fee_tax();
        $product_resut['discount_tax'] = WC()->cart->get_discount_tax();
        $product_resut['shipping_taxes'] = WC()->cart->get_shipping_taxes();
        $product_resut['image'] = (wp_get_attachment_url($cart_item["data"]->get_image_id()) == true) ? wp_get_attachment_url($cart_item["data"]->get_image_id()) : wc_placeholder_img_src();
        $product_resut['product_type'] = $cart_item['data']->get_type();  //get product type
        $product_resut['variation_id'] = $cart_item["variation_id"];
        $product_resut['variation'] = $cart_item['data']->get_attributes();
        $product_resut['variationName'] = $variationName;
        $product_resut['product_in_stock'] = $cart_item['data']->get_stock_quantity();
        $product_resut['stock_status'] = $cart_item["data"]->get_stock_status();
        if($cart_item["data"]->managing_stock() === 'parent'){
            $parentProduct = $cart_item["data"]->get_parent_data();
            if($parentProduct['manage_stock']=='yes'){
                $product_resut['product_in_stock'] = $parentProduct["stock_quantity"];
                if($parentProduct["backorders"] == 'no'){
                    $product_resut['stock_management'] = false;
                }
                else{
                    $product_resut['stock_management'] = true;
                }
            }
        }else{
            if ( $cart_item["data"]->managing_stock() != 1 && ($product_resut['stock_status'] == "instock" || $product_resut['stock_status'] == "onbackorder" ) ){
                $product_resut['stock_management'] = true;
            } else if($cart_item["data"]->backorders_allowed() == 1){
                $product_resut['stock_management'] = true;
            } else{
                $product_resut['stock_management'] = false;
            }
        }
        $product_resut['tax_status'] = $productData->get_tax_status();
        $product_resut['item_data'] = (isset($cart_item["item_data"])) ? $cart_item["item_data"]:[];
	    $product_resut['items_details'] = wc_get_formatted_cart_item_data($cart_item);
        /*0102 set variable for product review*/
        $productReviewData = wc_get_product($cart_item['product_id']);
        $product_resut['average_rating'] = $productReviewData->get_average_rating();
        $product_resut['rating_count'] = $productReviewData->get_review_count();
        /*0102*/
        $product_resut['categories'] = $product_cat_name;
        $product_resut_new1["items"][] = $product_resut;
    }

    $product_resut_new['cart_data'] = $product_resut_new1;
    $product_resut_new["cart_data"]['attributes'] = $attributes;

    /* Product cart item data foreach loop iw0107 */
    $i = 0;
    foreach ($items as $cart_item_key => $cart_item) {
        $terms = get_the_terms( $cart_item['product_id'], 'product_tag' );
        $product_tag = array();
        $product_cat_name = array();
        $x = 0;
        if(is_array($terms)){
            while($x < count($terms)) {
                $product_tag[] = $terms[$x]->name;
                $x++;
            }
        }
        $terms = wp_get_post_terms( $cart_item['product_id'], 'product_cat' );
        foreach ($terms  as $term  ) {
            $product_cat_name[] = $term->term_id;
        }
        if($cart_item['variation_id'] != 0){
            $cart_item_id = $cart_item['variation_id'];
        }else{
            $cart_item_id = $cart_item['product_id'];
        }
        $productData = wc_get_product($cart_item_id);
        $tax_rates = WC_Tax::get_rates( $productData->get_tax_class() );
        if (!empty($tax_rates)) {
            $tax_rate = reset($tax_rates);
            $product_result['product_tax_per'] = $tax_rate['rate'];
        }else{
	        $product_result['product_tax_per'] = 0;
        }
	    $product_result['tax_status'] = $productData->get_tax_status();
        $product_result['id'] = $cart_item_id;
        $product_result['parent_id'] = $cart_item['product_id'];
        $product_result['quantity'] = $cart_item['quantity'];
        $product_result['tags'] = $product_tag;
        $product_result['key'] = $cart_item_key;
        $product_result['name'] = $cart_item["data"]->get_title();
        $product_result['description'] = $cart_item["data"]->get_description();
	    if(PRODUCT_ADD_ONS_WOOCOMMERCE == 1) {
		    if ($dSetting == 'incl') {
			    $product_resut['price'] = wc_format_decimal(wc_get_price_including_tax( $productData, array( 'price' => $cart_item["data"]->get_price(),'qty'=>$cart_item['quantity'] )));
		    }else{
			    $product_resut['price'] = $cart_item["data"]->get_price();
		    }
	    }else{
		    if($dSetting == 'incl'){
			    $product_result['price'] = wc_format_decimal(wc_get_price_including_tax($productData),2);
		    }else{
			    $product_result['price'] = wc_format_decimal(wc_get_price_excluding_tax($productData),2);
		    }
	    }
        $product_result['regular_price'] = $cart_item["data"]->get_regular_price();
        $product_result['sale_price'] = $cart_item["data"]->get_sale_price();
        $product_result['sku'] = $cart_item["data"]->get_sku();
        $product_result['slug'] = $cart_item["data"]->get_slug();
        $product_result['handle'] = $cart_item["data"]->get_slug();
        $product_result['permalink'] = $cart_item["data"]->get_permalink();
        $product_result['subtotal'] = WC()->cart->subtotal;
        $product_result['subtotal_ex_tax'] = WC()->cart->subtotal_ex_tax;
        $product_result['displayed_subtotal'] = WC()->cart->get_displayed_subtotal();
        $product_result['taxes_total'] = WC()->cart->get_taxes_total();
        $product_result['shipping_total'] = WC()->cart->get_shipping_total();
        $product_result['coupons'] = WC()->cart->get_coupons();
        $product_result['total'] = WC()->cart->total;
        $product_result['tax_totals'] = WC()->cart->get_tax_totals();
        $product_result['cart_contents_tax'] = WC()->cart->get_cart_contents_tax();
        $product_result['fee_tax'] = WC()->cart->get_fee_tax();
        $product_result['discount_tax'] = WC()->cart->get_discount_tax();
        $product_result['shipping_taxes'] = WC()->cart->get_shipping_taxes();
        $product_resut['image'] = (wp_get_attachment_url($cart_item["data"]->get_image_id()) == true) ? wp_get_attachment_url($cart_item["data"]->get_image_id()) : wc_placeholder_img_src();
        $product_result['product_type'] = $cart_item['data']->get_type();  //get product type
        $product_result['variation_id'] = $cart_item["variation_id"];
        $product_result['product_in_stock'] = $cart_item['data']->get_stock_quantity();
        $product_result['stock_status'] = $cart_item["data"]->get_stock_status();
        if($cart_item["data"]->managing_stock() === 'parent'){
            $parentProduct = $cart_item["data"]->get_parent_data();
            if($parentProduct['manage_stock']=='yes'){
                $product_result['product_in_stock'] = $parentProduct["stock_quantity"];
                if($parentProduct["backorders"] == 'no'){
                    $product_result['stock_management'] = false;
                }
                else{
                    $product_result['stock_management'] = true;
                }
            }
        }else{
            if ( $cart_item["data"]->managing_stock() != 1 && ($product_result['stock_status'] == "instock" || $product_result['stock_status'] == "onbackorder" ) ){
                $product_result['stock_management'] = true;
            } else if($cart_item["data"]->backorders_allowed() == 1){
                $product_result['stock_management'] = true;
            } else{
                $product_result['stock_management'] = false;
            }
        }
        $product_result['variation'] = $cart_item['data']->get_attributes();
        $product_result['categories'] = $product_cat_name;
        if(WC()->cart->get_shipping_total() == 0){
            $product_result['requires_shipping'] = false;
        }
        else{
            $product_result['requires_shipping'] = true;
        }
        $product_resut_new[$cart_item_id] = $product_result;
        $product_resut_new['prod_tag_'.$i] = $product_tag;
        $product_resut_new['prod_coll_'.$i] = $product_cat_name;
        $i++;
    }
    if(empty($items)){
        $product_resut_new["cart_data"]["items"] = array();
    }
    $isIncludeTax = false;
    $isTaxInShop = false;
    if($dSetting == 'incl'){
        $isIncludeTax = true;
    }else{
        $isIncludeTax = false;
    }
    if($displayPriceInShop == 'incl') {
        $isTaxInShop = true;
    } else {
        $isTaxInShop = false;
    }
    $product_resut_new["cart_data"]['tax_show_in_shop'] = $isTaxInShop;
    $product_resut_new["cart_data"]['tax_show_in_cart_checkout'] = $isIncludeTax;
    $product_resut_new['cart_prod_id_arr'] = array();
    $product_resut_new['cart_prod_coll_id_arr'] = array();
    $product_resut_new['coupons'] = array();

    return new WP_REST_Response($product_resut_new,200);
}
