<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'related-product', array(
        'methods' => 'GET',
        'callback' => 'related_product',
        'args' => array(),
        'permission_callback' => function () {
            return true;
        }
    ));
});

function related_product(WP_REST_Request $request) {
    $product_id= $request->get_param('product_id');
    $prod_type = wc_get_product($product_id);
    if($product_id == ""){
        $relate["error"] = "Product ID is required";
    }
    elseif($prod_type == false){
        $relate['error'] = "Product not exist";
    }
    else{
        $product_id_integer= (int)$product_id;
        $products = wc_get_related_products($product_id_integer);
        $i= 0;
        foreach ($products as $id) {
            $related_get = wc_get_product($id);
            if($related_get->is_type( 'grouped' ) || $related_get->is_type( 'external' ) || !$related_get->get_price()){
                continue;
            }
            $terms = get_the_terms($related_get->get_id(),'product_tag' );
            $term_array = array();
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                foreach ( $terms as $term ) {
                    $term_array[] = $term->name;
                }
            }
            $variation_variations = array();
            if($related_get->is_type('variable' ) ){
                $variation_variations = $related_get->get_available_variations();
            }
            $related['tags'] = $term_array;
            $related['variants'] = $variation_variations; /* get all child variations */
            $related['purchasable'] = $related_get->is_purchasable();
            $related['name'] = $related_get->get_title();
            $related['title'] = $related_get->get_title();
            $related['type'] = $related_get->get_type();
	        $related['id'] = ($related_get->get_parent_id() == 0) ? $related_get->get_id() : $related_get->get_parent_id();
            $related['slug'] = $related_get->get_slug();
            $related['sku'] = $related_get->get_sku();
            $related['stock_status'] = $related_get->get_stock_status();
            $related['price'] = $related_get->get_price();
            $related['regular_price'] = $related_get->get_regular_price();
            $related['sale_price'] = $related_get->get_sale_price();
            $related['permalink'] = $related_get->get_permalink();
            $related['product_to_display'] = "woocommerce_recommandation";
            /*IW0102*/
            $related['average_rating'] = $related_get->get_average_rating();
            $related['rating_count'] = $related_get->get_rating_count();
            /*IW0102*/
            if (wp_get_attachment_url($related_get->get_image_id()) == false) {
                $related['images'] = wc_placeholder_img_src();
            } else {
                $related['images'] = wp_get_attachment_url($related_get->get_image_id());
            }
            $relate[$i] = $related;
            $i++;
        }
    }
    return new WP_REST_Response($relate,200);
}
