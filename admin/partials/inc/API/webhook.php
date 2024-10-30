<?php
/**
 * @package Bestupsell
 */

//creat API for get webhook secret key
add_action('rest_api_init', function() {
    register_rest_route('wc/v3', 'webhook', array(
        'methods' =>  WP_REST_SERVER::CREATABLE,
        'callback' => 'web_hook',
        'args' => array(),
        'permission_callback' => function () {
            return true;
        }
    ));
});

function web_hook(WP_REST_Request $request)
{
    $data = json_decode($request->get_body());
    $secretkey = $data->id;

    if($webhook =  wc_get_webhook((int)$secretkey)){
        $new_key['secret_key'] = $webhook->get_secret();
    }else{
        $new_key['secret_key'] ='';
        $new_key['error'] ='something went wrong';
    }
    return new WP_REST_Response($new_key, 200);
}