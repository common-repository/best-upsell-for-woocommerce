<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
	register_rest_route('wc/v3', 'cart_url', array(
		'methods' => 'GET',
		'callback' => 'cart_url',
		'args' => array(),
		'permission_callback' => function () {
			return true;
		}
	));
});

function cart_url(){
	$cart_url = array("cart_url"=>wc_get_cart_url());
	return new WP_REST_Response($cart_url,200);
}

