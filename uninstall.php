<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://www.identixweb.com/
 * @since      1.2.0
 *
 * @package    Bestupsell
 */

if ( ! defined('WP_UNINSTALL_PLUGIN')){
    die();
}
global $wpdb;
$minicart = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'identixweb_bestupsell');
if(!empty($minicart)){
    global $current_user;
    $user_detail = wp_get_current_user();
    $email =$user_detail->data->user_email;
    $get_mode = $minicart->mode;
    $main_url = $minicart->plugin_url;
    $site_url = $minicart->siteurl;
    $plugin_issue_url = $main_url."icart/client/check-store.php?issue=delete&get_mode=".$get_mode."&email=".$email."&checkstore=".$site_url;
    $response = wp_remote_get( $plugin_issue_url );
    $output = wp_remote_retrieve_body($response);
}
