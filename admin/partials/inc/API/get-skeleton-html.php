<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
        register_rest_route('wc/v3', 'save-skeleton-html', array(
        'methods' => 'GET',
        'callback' => 'get_icart_view',
        'args' => array(),
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));
});

function get_icart_view(WP_REST_Request $request) {
    global $wpdb;
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."identixweb_bestupsell") );
    /* Query to fetch data from database table and storing in $results */
    return new WP_REST_Response($results,200);
}