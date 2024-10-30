<?php
/**
 * @package Bestupsell
 */

add_filter( 'woocommerce_loop_add_to_cart_link', 'icart_replace_add_to_cart_button', 10, 3 );

function icart_replace_add_to_cart_button( $button, $product,$args ) {
		$plugins = 0;
		if(PRODUCT_ADD_ONS_WOOCOMMERCE == 1){
			$plugins = 1;
		}
        $product_type = $product->get_type();
        $button_text = $product->add_to_cart_text();
        $button_link = $product->add_to_cart_url();
        if($plugins == 1){
	        $icartClass = $product->is_purchasable() && $product->is_in_stock() ? '' : '';
        }else{
	        $icartClass = $product->is_purchasable() && $product->is_in_stock() ? 'iCartSingleAddCart' : '';
        }
        $defaultClass = isset( $args['class'] ) ? $args['class'] : 'button';
        if($product_type == "simple") {
            $button = '<a class="'.$icartClass.' '.$defaultClass.'" href="' . $button_link . '" value="'.$product->get_id().'">' . $button_text . '</a>';
            return $button;
        }
        elseif($product_type == "grouped") {
            $button = '<a class="'.$defaultClass.'" href="' . $button_link . '">' . $button_text . '</a>';
            return $button;
        }
        elseif($product_type == "variable") {
            $button = '<a class="'.$defaultClass.'" href="' . $button_link . '">' . $button_text . '</a>';
            return $button;
        }
        else{
            $button = '<a class="button '. $product_type .' " href="' . $button_link . '" value="'.$product->get_id().'">' . $button_text . '</a>';
            return $button;
        }
}