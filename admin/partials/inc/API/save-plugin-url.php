<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'save-plugin-url', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'save_plugin_url',
        'args' => array(),
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));
});

function save_plugin_url(WP_REST_Request $request)
{
    global $wpdb;
    $data = json_decode($request->get_body());
    $siteurl = $data->siteurl;
    $pluginurl = $data->plugin_url;
    $nodeurl = $data->node_url;
    $tablename = $wpdb->prefix . 'identixweb_bestupsell';
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "identixweb_bestupsell"));

    // Query to fetch data from database table and storing in $results
    if (!empty($results)) {
        $field = [];
        if (!empty($pluginurl) || $pluginurl == '0') {
            $field["plugin_url"] = $pluginurl;
        }
        if (!empty($nodeurl) || $nodeurl == "0" || $nodeurl !='') {
            $field["node_url"] = $nodeurl;
        }
        $update_row = $wpdb->update(
            $tablename,
            $field,
            array(
                'siteurl' => $siteurl,
            )
        );

        if ($update_row > 0) {
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "identixweb_bestupsell"));
            $results['response'] = 'Successfully Updated';
            return new WP_REST_Response($results, 200);
        } else {
            $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "identixweb_bestupsell"));
            $results['response'] = ['somthing went wrong'];
            return new WP_REST_Response($results, 200);
        }
        $wpdb->flush();
    }
}


