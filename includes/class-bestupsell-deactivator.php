<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.identixweb.com/
 * @since      1.2.0
 *
 * @package    Bestupsell
 * @subpackage Bestupsell/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.2.0
 * @package    Bestupsell
 * @subpackage Bestupsell/includes
 * @author     identixweb <https://www.identixweb.com/>
 */
class Bestupsell_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.2.0
     */
    public static function deactivate() {
        global $wpdb;
        $minicart = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'identixweb_bestupsell');
        if(!empty($minicart)){
            global $current_user;
            $user_detail = wp_get_current_user();
            $email =$user_detail->data->user_email;
            $get_mode = $minicart->mode;
            $main_url = $minicart->plugin_url;
            $site_url = $minicart->siteurl;
            $plugin_issue_url = $main_url."icart/client/check-store.php?issue=deactivate&get_mode=".$get_mode."&email=".$email."&checkstore=".$site_url;
            $response = wp_remote_get( $plugin_issue_url );
            $output = wp_remote_retrieve_body($response);
        }
    }

}
