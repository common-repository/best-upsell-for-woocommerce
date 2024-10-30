<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'lookup', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'customer_lookup',
        'args' => array(),
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));
});

function customer_lookup(WP_REST_Request $request) {
    $data = json_decode($request->get_body());
    $user_id = $data->user_id;
    $birthdate = $data->birthdate;
    global $wpdb;
    $tablename = $wpdb->prefix .'users';
    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "users" ));
     if (!empty($results)) {
        $field = [];
        if (!empty($birthdate) || $birthdate !='') {
            $field["birthdate"] = $birthdate;
        }
        $update_row = $wpdb->update(
            $tablename,
            $field,
            array(
                'id' => $user_id,
            )
        );
        $result['response'] = 'Successfully Updated';
        return new WP_REST_Response($result, 200);

        $wpdb->flush();
    }
}