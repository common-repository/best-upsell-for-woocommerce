<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
        register_rest_route('wc/v3', 'url-status', array(
        'methods' => WP_REST_SERVER::CREATABLE,
        'callback' => 'url_status',
        'args' => array(),
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ));
});

function url_status(WP_REST_Request $request) {
    global $wpdb;
    $data = json_decode($request->get_body());
    $siteurl= $data->siteurl;
    $status = $data->status;
    $mode = $data->mode;
    $page_type = $data->page_type;
    $tablename = $wpdb->prefix .'identixweb_bestupsell';
    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."identixweb_bestupsell") );
    /* Query to fetch data from database table and storing in $results*/
    foreach( $results as $result ) {
        $get_siteurl = $result->siteurl;
        /*$page_type_db = $result->page_type;*/
    }
    /*if(empty($page_type)){
	    $page_type = $page_type_db;
    }*/
   if(!empty($results)){
        $field = [];
        if(!empty($status) || $status == '0'){
            $field["status"] = $status;
        }
        if(!empty($mode) || $mode == "0"){
            $field["mode"] = $mode;
        }
        $field['page_type'] = $page_type;
        $update_row = $wpdb->update(
            $tablename,
            $field,
            array(
                'siteurl' => $siteurl,
            )
        );
        
        if($update_row > 0){
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT siteurl, status, mode,page_type FROM ".$wpdb->prefix."identixweb_bestupsell") );
            $results['response']='Successfully Updated.';
            return new WP_REST_Response($results,200);

        }
        elseif($siteurl != $get_siteurl){
            $results['error']='Something wrong please try again.';
            return new WP_REST_Response($results,200);
        }
        else{
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT siteurl, status, mode,page_type FROM ".$wpdb->prefix."identixweb_bestupsell") );
            $results['response']=['Successfully Updated'];
            return new WP_REST_Response($results,200);

        }
        $wpdb->flush();
    }
    elseif(is_null($status)){
        $insert_row = $wpdb->insert(
            $tablename,
            array(
                'siteurl' => $siteurl,
                'mode' => $mode,
	            'page_type' => $page_type,
            )
        );
        // if row inserted in table
        if($insert_row){
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."identixweb_bestupsell") );
            $results['response']=['Successfully Inserted'];
            return new WP_REST_Response($results,200);
        }else{
            echo "Something went wrong. Please try again later.";
        }
    }
    elseif(!is_null($status)){
        $insert_row = $wpdb->insert(
            $tablename,
            array(
                'siteurl' => $siteurl,
                'status' => $status,
                'mode' => $mode,
	            'page_type' => $page_type,
            )
        );
        // if row inserted in table
        if($insert_row){
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."identixweb_bestupsell") );
            $results['response']=['Successfully Inserted'];
            return new WP_REST_Response($results,200);
        }else{
            echo "Something went wrong. Please try again later.";
        }
    }
    else{
        $insert_row = $wpdb->insert(
            $tablename,
            array(
                'siteurl' => $siteurl,
                'status' => $status,
                'mode' => $mode,
	            'page_type' => $page_type,
            )
        );
        // if row inserted in table
        if($insert_row){
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."identixweb_bestupsell") );
            $results['response']=['Successfully Inserted'];
            return new WP_REST_Response($results,200);
        }else{
            echo "Something went wrong. Please try again later.";
        }
    }
}
