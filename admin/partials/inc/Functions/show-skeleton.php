<?php
/**
 * @package Bestupsell
 */

function show_skeleton_front() {
    if (class_exists('WooCommerce')) {
        if (empty(is_wc_endpoint_url('order-received')))
            echo do_shortcode('[best_upsell_skeleton]');
    }
}
add_action( 'wp_footer', 'show_skeleton_front' );

function upsell_skeleton_query() {
    global $wpdb;
    $minicart = $wpdb->get_row(  "SELECT * FROM ".$wpdb->prefix."identixweb_bestupsell");
//    $minicart = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."identixweb_bestupsell") );
	$skeleton = $minicart->skeleton;
	$status = $minicart->status;
	$page_type = $minicart->page_type;
	if($page_type == 1){
		if($status == 1) {
			echo html_entity_decode(esc_html($skeleton)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}else{
		if (!is_cart() || !is_page( 'cart' )) {
			if($status == 1 && ($page_type == 1|| $page_type == 0)) {
				echo html_entity_decode(esc_html($skeleton)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
	if (is_cart() || is_page( 'cart' )) {
		global $wpdb;
		$minicart = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix .'identixweb_bestupsell');
		$full_cart_length = $minicart->full_cart;
		$page_type = $minicart->page_type;
		$status = $minicart->status;
		if($full_cart_length == 1 && ($page_type == 2|| $page_type == 0) && $status ==1){
			$full_cart = $minicart->full_cart_skeleton;
			echo html_entity_decode(esc_html($full_cart)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
add_shortcode( 'best_upsell_skeleton', 'upsell_skeleton_query' );

add_action('woocommerce_thankyou', 'bestupsell_thankyou', 10 , 1);
function bestupsell_thankyou( $order_id ) {
    global $wpdb;
    $minicart = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix .'identixweb_bestupsell');
    $main_url = $minicart->plugin_url;
    wp_enqueue_script( 'best-upsell',   $main_url.'/icart/assets/js/icart-thankyou.js', array(), '102', true );
}
/*add_filter( 'woocommerce_locate_template', 'intercept_wc_template', 10, 3 );
function intercept_wc_template( $template, $template_name, $template_path ) {
	if ( 'cart.php' === basename( $template ) ) {
		global $wpdb;
		$minicart = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix .'identixweb_bestupsell');
		$full_cart = $minicart->full_cart;
		if($full_cart == 1){
			$template = plugin_dir_path( __FILE__ ).'cart.php';
		}
	}elseif ('cart-empty.php' === basename( $template )){
		global $wpdb;
		$minicart = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix .'identixweb_bestupsell');
		$full_cart = $minicart->full_cart;
		if($full_cart == 1){
			if ( WC()->cart->get_cart_contents_count() == 0 ) {
				$template = plugin_dir_path( __FILE__ ).'cart-empty.php';
			}
		}
	}
	return $template;
}*/
