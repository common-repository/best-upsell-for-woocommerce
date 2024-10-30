<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.identixweb.com/
 * @since      1.2.0
 *
 * @package    Bestupsell
 * @subpackage Bestupsell/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.2.0
 * @package    Bestupsell
 * @subpackage Bestupsell/includes
 * @author     identixweb <https://www.identixweb.com/>
 */
class Bestupsell_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.2.0
     */
    public static function activate(){

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        //$table_name = $wpdb->prefix . 'identixweb_bestupsell';
        $sql = "CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."identixweb_bestupsell (
  `id` int(11) AUTO_INCREMENT,
  `siteurl` varchar(220) DEFAULT NULL,
  `plugin_url` varchar(220) DEFAULT 'https://bu.identixweb.com/',
  `node_url` varchar(255) DEFAULT 'https://bun.identixweb.com',
  `skeleton` mediumblob DEFAULT NULL,
  `full_cart_skeleton` mediumblob DEFAULT NULL,
  `full_cart` ENUM('0','1') NOT NULL DEFAULT '1' COMMENT '0=Deactive,1=active',
  `page_type` ENUM('0','1','2') NOT NULL DEFAULT '1' COMMENT '0=Both,1=Side Cart,2=Full Cart',
  `status` ENUM('0','1') NOT NULL DEFAULT '1' COMMENT '0=Deactive,1=active',
  `mode` ENUM('0','1','2') NOT NULL DEFAULT '1' COMMENT '0=local,1=live,2=dev',
  `token` varchar(255) DEFAULT NULL,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(id)
  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
  ";
        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}identixweb_bestupsell'") != $wpdb->prefix . "identixweb_bestupsell") {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
        $length = 64;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(10, $charactersLength - 1)];
        }
        $domain = get_home_url();
        $domain_new = preg_replace( "#^[^:/.]*[:/]+#i", "", $domain );
        $minicart = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . 'identixweb_bestupsell');
        if (empty($minicart)) {
            $insert_row = $wpdb->insert(
                $wpdb->prefix . 'identixweb_bestupsell',
                array(
                    'siteurl' => $domain_new,
                    'token' => $randomString,
                )
            );
        }
        if(!empty($minicart)){
            global $current_user;
            $user_detail = wp_get_current_user();
            $email =$user_detail->data->user_email;
            $get_mode = $minicart->mode;
            $main_url = $minicart->plugin_url;
            $plugin_issue_url = $main_url."icart/client/check-store.php?issue=activate&get_mode=".$get_mode."&email=".$email."&checkstore=".$domain_new."&wp_plugin_status=0";
            $response = wp_remote_get( $plugin_issue_url );
            $output = wp_remote_retrieve_body($response);
        } else{
            global $current_user;
            $user_detail = wp_get_current_user();
            $email =$user_detail->data->user_email;
            $get_mode = "1";
            $main_url = "https://bu.identixweb.com";
            $plugin_issue_url = $main_url."icart/client/check-store.php?issue=activate&get_mode=".$get_mode."&email=".$email."&checkstore=".$domain_new."&wp_plugin_status=0";
            $response = wp_remote_get( $plugin_issue_url );
            $output = wp_remote_retrieve_body($response);
        }

    }
}
