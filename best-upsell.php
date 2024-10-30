<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.identixweb.com/
 * @since             1.2.0
 * @package           Bestupsell
 * @author            identixweb
 * @copyright         2019  identixweb
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Best Upsell for WooCommerce
 * Plugin URI:        https://www.identixweb.com/
 * Description:       WooCommerce Upsell & Cross-Sell Plugin to Reduce Cart Abandonment And Boost Sales
 * Version:           1.2.0
 * Requires at least: 5.0
 * Requires PHP:      5.6
 * Author:            identixweb
 * Author URI:        https://www.identixweb.com/?utm_campaign=plugin&utm_medium=admin&utm_source=best-upsell
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bestupsell
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! defined( 'WPINC' ) ) {
    die;
}
function custom_wpkses_post_tags( $tags, $context ) {
    if ( 'post' === $context ) {
        $tags['iframe'] = array(
            'src'             => true,
            'height'          => true,
            'width'           => true,
            'frameborder'     => true,
            'allowfullscreen' => true,
            'style' => true
        );
    }

    return $tags;
}

add_filter( 'wp_kses_allowed_html', 'custom_wpkses_post_tags', 10, 2 );
/**
 * Currently plugin version.
 * Start at version 1.2.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BESTUPSELL_VERSION', '1.2.0' );

$plugin_status = 0;
if(is_plugin_active( 'product-add-ons-woocommerce/index.php' )){
    $plugin_status = 1;
}

define('PRODUCT_ADD_ONS_WOOCOMMERCE',$plugin_status);


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bestupsell-activator.php
 */
function activate_bestupsell() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-bestupsell-activator.php';
    Bestupsell_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bestupsell-deactivator.php
 */
function deactivate_bestupsell() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-bestupsell-deactivator.php';
    Bestupsell_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bestupsell' );
register_deactivation_hook( __FILE__, 'deactivate_bestupsell' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bestupsell.php';

//include('inc/Functions/icart-add-to-cart-ajax.php');

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/Functions/show-skeleton.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/Functions/common-variables.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/Functions/override-add-cart-button-for-listing-page.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/Functions/show-prices.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/Functions/update-order-meta.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/Functions/checkout-create-order.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/post-skeleton-html.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/post-full-cart-skeleton-html.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/get-skeleton-html.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/post-url-status.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/get-url-status.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/add-product-cart.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/remove-product-cart.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/set-quantity-cart.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/get-related-product.php';

require plugin_dir_path( __FILE__ ) .'admin/partials/inc/API/cart-data.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/clear-cart-data.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/set-discount-session.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/webhook.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/save-plugin-url.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/get-cart-url.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/check-stamped.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/apply-coupons.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/remove-coupons.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/image-upload.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/bundle-product.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/customer-lookup.php';

require plugin_dir_path( __FILE__ )  .'admin/partials/inc/API/update-cart.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.2.0
 */
function run_bestupsell() {

    $plugin = new Bestupsell();
    $plugin->run();

}
run_bestupsell();

add_filter( 'plugin_row_meta', 'bu_plugin_row_meta', 10, 2 );

function bu_plugin_row_meta( $links, $file ) {

	if ( strpos( $file, 'best-upsell.php' ) !== false ) {
		$new_links = array(
			'<a href="https://www.identixweb.com/wp-helpdesk/?utm_campaign=plugin&utm_medium=admin&utm_source=best-upsell" aria-label="View Best Upsell documentation" target="_blank">Docs</a>',
			'<a href="https://www.identixweb.com/contact/?utm_campaign=plugin&utm_medium=admin&utm_source=best-upsell" aria-label="View Contact Page" target="_blank">Contact Us</a>',
		);

		$links = array_merge( $links, $new_links );
	}
	return $links;
}

function myplugin_plugin_path() {

    /* gets the absolute path to this plugin directory */
    return untrailingslashit( plugin_dir_path( __FILE__ ) );
}
add_filter( 'woocommerce_locate_template', 'myplugin_woocommerce_locate_template', 10, 3 );


function myplugin_woocommerce_locate_template( $template, $template_name, $template_path ) {
    global $woocommerce;

    $_template = $template;

    if ( ! $template_path ) $template_path = $woocommerce->template_url;

    $plugin_path  = myplugin_plugin_path() . '/woocommerce/';

    /* Look within passed path within the theme - this is priority */
    $template = locate_template(

        array(
            $template_path . $template_name,
            $template_name
        )
    );

    /* Modification: Get the template from this plugin, if it exists */
    if ( ! $template && file_exists( $plugin_path . $template_name ) )
        $template = $plugin_path . $template_name;

    /* Use default template */
    if ( ! $template )
        $template = $_template;

    /* Return what we found */
    return $template;
}

add_action( 'upgrader_process_complete', 'plugin_update',10, 2);

function plugin_update( $upgrader_object, $options ) {
    $current_plugin_path_name = plugin_basename( __FILE__ );
    if ($options['action'] == 'update' && $options['type'] == 'plugin' ) {
        foreach($options['plugins'] as $each_plugin) {
            if ($each_plugin==$current_plugin_path_name) {
                global $wpdb;
                $wpdb->query("ALTER TABLE " . $wpdb->prefix . "identixweb_bestupsell
                CHANGE `skeleton` `skeleton` MEDIUMBLOB DEFAULT NULL");
                $wpdb->query("ALTER TABLE " . $wpdb->prefix . "identixweb_bestupsell
                CHANGE `mode` `mode` ENUM('0','1','2') DEFAULT NULL COMMENT '0=local,1=live, 2=Dev'");
                $wpdb->query("ALTER TABLE " . $wpdb->prefix . "identixweb_bestupsell
                CHANGE `node_url` `node_url` DEFAULT NULL");

            }
        }
    }
}

add_action( 'woocommerce_thankyou', 'bu_order_recieved', 20 );
if ( ! function_exists( 'bu_order_recieved' ) ) {
    function bu_order_recieved( $order_id ) {
        if ( $order_id > 0 ) {
            $order = wc_get_order( $order_id );
            if ( $order instanceof WC_Order ) {
                $email  = $order->get_billing_email();
                global $wpdb;
                $result = $wpdb->get_var( $wpdb->prepare( "
                    SELECT customer_id FROM {$wpdb->prefix}wc_customer_lookup
                    WHERE email = %s ", $email ) );
                ?>
                <script type="text/javascript">
                    var buCustomerOrderId = '<?php esc_html_e($result); ?>';
                    var buCustomerOrderEmail  = '<?php esc_html_e($email); ?>';
                    var buOrderId  = '<?php esc_html_e($order_id); ?>';
                </script>
                <?php
            }
        }
    }
}

global $wpdb;
$Customer = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "users");
$Customer= (array)$Customer;
if(array_key_exists('birthdate',$Customer)){
}else{
    $wpdb->query("ALTER TABLE `" . $wpdb->prefix . "users`  ADD `birthdate` VARCHAR(30) NULL DEFAULT NULL  AFTER `user_email`;");
}

$field_data = (array)$wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "identixweb_bestupsell");
if(!array_key_exists('full_cart_skeleton',$field_data)){
	$wpdb->query("ALTER TABLE `" . $wpdb->prefix . "identixweb_bestupsell`  ADD `full_cart_skeleton` MEDIUMBLOB NULL DEFAULT NULL AFTER `skeleton`;");
}
if(!array_key_exists('full_cart',$field_data)){
	$wpdb->query("ALTER TABLE `" . $wpdb->prefix . "identixweb_bestupsell` ADD `full_cart` ENUM('0','1') NOT NULL DEFAULT '1' COMMENT '0=Deactive,1=active' AFTER `full_cart_skeleton`;");
}
if(!array_key_exists('page_type',$field_data)){
	$wpdb->query("ALTER TABLE `" . $wpdb->prefix . "identixweb_bestupsell` ADD `page_type` ENUM('0','1','2') NOT NULL DEFAULT '1' COMMENT '0=Both,1=Side Cart,2=Full Cart' AFTER `full_cart`;");
}
