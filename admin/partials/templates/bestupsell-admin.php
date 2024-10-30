<?php
/**
 * @package Bestupsell
 */
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
$allPlugins = get_plugins();
foreach($allPlugins as $key => $value) {
    if($value['Name'] == 'Best Upsell for WooCommerce') {
        $version_bu = $value['Version'];
    }
    if( $value['Name'] == 'WooCommerce'){
        $version_wc = $value['Version'];
    }
}
$bu_version = $version_bu;
$wc_version = $version_wc;
$survey = (isset($_GET['menu']) && !empty($_GET['menu'])) ? $_GET['menu'] : ""; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

$site_title = get_bloginfo('name');
$address_line_1 = get_option('woocommerce_store_address');
$address_line_2 = get_option('woocommerce_store_address_2');
$city_name = get_option('woocommerce_store_city');
$country_name = get_option('woocommerce_default_country');
$theme_name = esc_html( wp_get_theme()->get( 'TextDomain' ) );
$pincode = get_option('woocommerce_store_postcode');
$wordpress_version= get_bloginfo( 'version' );
$min    = 60 * get_option('gmt_offset');
$sign   = $min < 0 ? "-" : "+";
$absmin = abs($min);
$tz     = sprintf("UTC%s%02d:%02d", $sign, $absmin/60, $absmin%60);
$timezone = get_option('timezone_string');
$cart_url = wc_get_cart_url();
global $wpdb;
//$tableName = $wpdb->prefix . 'identixweb_bestupsell';
$results = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'identixweb_bestupsell');
// Query to fetch data from database table and storing in $results
$domain_new = preg_replace( "#^[^:/.]*[:/]+#i", "", get_home_url() );
if( function_exists('get_woocommerce_currency_symbol')) {
    $currencysign = get_woocommerce_currency_symbol();
} else {
    $currencysign = '$';
}
$currency = get_option('woocommerce_currency');
$http_host_localhost = isset($_SERVER['HTTP_HOST']) ? wp_unslash($_SERVER['HTTP_HOST']  ) : ""; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
/*$request_scheme_http = isset($_SERVER['REQUEST_SCHEME']) ? wp_unslash($_SERVER['REQUEST_SCHEME'] ) :""; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized*/
if ( (! empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ||
    (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
    (! empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') ) {
    $request_scheme_http = 'https';
} else {
    $request_scheme_http = 'http';
}
if(!empty($results)){
    $get_mode = $results->mode;
    $token = $results->token;
    $main_url = $results->plugin_url;
    if($get_mode == 0){
        $shop_url = "without_wp=1&shop";
    }else{
        $shop_url = "shop";
    }
    global $current_user;
    $user_detail = wp_get_current_user();
    $name =$user_detail->data->display_name;
    $email =$user_detail->data->user_email;
    $time_zone = $tz;
    $iana_timezone = $timezone;
    if($http_host_localhost == 'localhost'){
        $plugin_issue_url = $main_url."icart/client/check-store.php?issue=localhost&get_mode=".$get_mode."&email=".$email."&checkstore=".$domain_new;
        $response = wp_remote_get( $plugin_issue_url );
        $output = wp_remote_retrieve_body($response);
        echo wp_kses_post($output);
    }elseif ($request_scheme_http == "http"){
        $plugin_issue_url = $main_url."icart/client/check-store.php?issue=request_scheme&get_mode=".$get_mode."&email=".$email."&checkstore=".$domain_new;
        $response = wp_remote_get( $plugin_issue_url );
        $output = wp_remote_retrieve_body($response);
        echo wp_kses_post($output);
    } elseif(!class_exists('Woocommerce')){
        $plugin_issue_url = $main_url."icart/client/check-store.php?issue=woocommerce&get_mode=".$get_mode."&email=".$email."&checkstore=".$domain_new;
        $response = wp_remote_get( $plugin_issue_url );
        $output = wp_remote_retrieve_body($response);
        echo wp_kses_post($output);
    }elseif ($survey == "survey"){
        $plugin_issue_url = $main_url."icart/client/check-store.php?issue=survey&get_mode=".$get_mode."&email=".$email."&checkstore=".$domain_new;
        $response = wp_remote_get( $plugin_issue_url );
        $output = wp_remote_retrieve_body($response);
        echo wp_kses_post($output);
    }elseif ($survey == "setting"){
        $plugin_issue_url = $main_url."icart/client/check-store.php?issue=setting&get_mode=".$get_mode."&email=".$email."&checkstore=".$domain_new;
        $response = wp_remote_get( $plugin_issue_url );
        $output = wp_remote_retrieve_body($response);
        echo wp_kses_post($output);
    }else{
        $url = $main_url.'icart/client/check-store.php?checkstore='.$domain_new.'&token='.$token .'&name='.$name.'&email='.$email.'&sign='.urlencode($currencysign).'&symbol='.$currency.'&domain='.get_home_url().'&get_mode='.$get_mode.'&timezone='.$time_zone.'&iana_timezone='.$iana_timezone.'&bu_version='.$bu_version.'&wp_version='.$wordpress_version.'&wc_version='.$wc_version.'&address_line_1='.$address_line_1.'&address_line_2='.$address_line_2.'&city_name='.$city_name.'&country_name='.$country_name.'&pincode='.$pincode.'&site_title='.$site_title.'&theme_name='.$theme_name.'&cart_url='.$cart_url;
        $response = wp_remote_get( $url );
        $output = wp_remote_retrieve_body($response);
        $output = json_decode($output);
        if($output->status == "true" ){
            echo wp_kses_post($output->html);
        }elseif($output->status == "false"){
            echo wp_kses_post($output->html);
        }else{
            $plugin_issue_url = $main_url."icart/client/check-store.php?issue=iframe&get_mode=".$get_mode."&email=".$email."&checkstore=".$domain_new;;
            $response = wp_remote_get( $plugin_issue_url );
            $output = wp_remote_retrieve_body($response);
            echo wp_kses_post($output);
        }
    }
}
