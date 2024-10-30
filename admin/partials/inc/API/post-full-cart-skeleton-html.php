<?php
/**
 * @package Bestupsell
 */

add_action('rest_api_init', function() {
	register_rest_route('wc/v3', 'post-full-cart-skeleton-html', array(
		'methods' => WP_REST_SERVER::CREATABLE,
		'callback' => 'post_full_icart_view',
		'args' => array(),
		'permission_callback' => function () {
			return is_user_logged_in();
		}
	));
});

function post_full_icart_view(WP_REST_Request $request) {
	global $wpdb;
	$data = json_decode($request->get_body());
	$skeleton = $data->skeleton;
	$siteurl= $data->siteurl;
	$status = $data->status;
	$tablename = $wpdb->prefix .'identixweb_bestupsell';
	/* Query to fetch data from database table and storing in $results */
	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."identixweb_bestupsell") );
	foreach( $results as $result ) {
		$get_siteurl = $result->siteurl;
	}
	if(!empty($results)){
		$field = [];
		if(!empty($skeleton) || $skeleton == '0'){
			$field["full_cart_skeleton"] = $skeleton;
			$field['full_cart'] = "1";
		}
		if(!empty($status) || $status == "0"){
			$field["status"] = $status;
		}
		$update_row = $wpdb->update(
			$tablename,
			$field,
			array(
				'siteurl' => $siteurl,
			)
		);

		if($update_row > 0){
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."identixweb_bestupsell") );
			$results['response']='Successfully Updated';
			return new WP_REST_Response($results,200);

		}
		elseif($siteurl != $get_siteurl){
			$results['error']='Something wrong please try again.';
			return new WP_REST_Response($results,200);
		}
		else{
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->prefix."identixweb_bestupsell") );
			$results['response']=['Successfully Updated'];
			return new WP_REST_Response($results,200);

		}
		$wpdb->flush();
	}else{
		$insert_row = $wpdb->insert(
			$tablename,
			array(
				'full_cart_skeleton' => $skeleton,
				'full_cart' => '1',
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