<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'stamped', array(
        'methods' => 'get',
        'callback' => 'is_plugin_installed',
        'args' => array(),
        'permission_callback' => function () {
            return true;
        }
    ));
});

 function is_plugin_installed( WP_REST_Request $request  ) {
     include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

     $plugin = 'stampedio-product-reviews/woocommerce-stamped-io.php';
     $exist =  file_exists( WP_PLUGIN_DIR . '/' . $plugin );
     if($exist == false ) {
         return new WP_REST_Response('notinstalled', 200);
     }else {
         if (is_plugin_active('stampedio-product-reviews/woocommerce-stamped-io.php')) {
             return new WP_REST_Response('true', 200);
         } else {
             return new WP_REST_Response('false', 500);
         }
     }
}

