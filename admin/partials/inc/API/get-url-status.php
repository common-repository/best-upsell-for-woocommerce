<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'url-status', array(
        'methods' => 'GET',
        'callback' => 'get_url_status',
        'args' => array(),
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));
});
function get_url_status(WP_REST_Request $request) {
    global $wpdb;
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT siteurl, status, mode FROM ".$wpdb->prefix."identixweb_bestupsell") );
    /* Query to fetch data from database table and storing in $results */
    return new WP_REST_Response($results,200);
}