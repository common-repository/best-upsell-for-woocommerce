<?php
/**
 * @package Bestupsell
 */

function common_variables() {
    if ( class_exists( 'WooCommerce' ) ){
        $product_resut_new = array();
        $items = WC()->cart->get_cart();
        foreach ($items as $cart_item_key => $cart_item) {
            if($cart_item['variation_id'] != 0){
                $cart_item_id = $cart_item['variation_id'];
            }else{
                $cart_item_id = $cart_item['product_id'];
            }
            $product_resut['id'] = $cart_item_id;
            $product_resut['parent_id'] = $cart_item['product_id'];
            $product_resut['quantity'] = $cart_item['quantity'];
            $product_resut['name'] = $cart_item["data"]->get_title();
            $product_resut['description'] = $cart_item["data"]->get_description();
            $product_resut['price'] = $cart_item["data"]->get_price();
            $product_resut['regular_price'] = $cart_item["data"]->get_regular_price();
            $product_resut['sale_price'] = $cart_item["data"]->get_sale_price();
            $product_resut['sku'] = $cart_item["data"]->get_sku();
            $product_resut['handle'] = $cart_item["data"]->get_slug();
            $product_resut['permalink'] = $cart_item["data"]->get_permalink();
            $product_resut['subtotal'] = WC()->cart->subtotal;
            $product_resut['subtotal_ex_tax'] = WC()->cart->subtotal_ex_tax;
            $product_resut['displayed_subtotal'] = WC()->cart->get_displayed_subtotal();
            $product_resut['taxes_total'] = WC()->cart->get_taxes_total();
            $product_resut['shipping_total'] = WC()->cart->get_shipping_total();
            $product_resut['coupons'] = WC()->cart->get_coupons();
            $product_resut['total'] = WC()->cart->total;
            $product_resut['tax_totals'] = WC()->cart->get_tax_totals();
            $product_resut['cart_contents_tax'] = WC()->cart->get_cart_contents_tax();
            $product_resut['fee_tax'] = WC()->cart->get_fee_tax();
            $product_resut['discount_tax'] = WC()->cart->get_discount_tax();
            $product_resut['shipping_taxes'] = WC()->cart->get_shipping_taxes();
            $product_resut['coupon_discount_amoun'] = WC()->cart->get_coupon_discount_amount('coupon_code');
            $product_resut['image'] = wp_get_attachment_url($cart_item["data"]->get_image_id());
            if(WC()->cart->get_shipping_total() == 0){
                $product_resut['requires_shipping'] = false;
            } else{
                $product_resut['requires_shipping'] = true;
            }
            $product_resut_new[$cart_item_id] = $product_resut;
        }
        $vis_ip = WC_Geolocation::get_ip_address();
        $ipdat = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $vis_ip));
        $userLocation =  $ipdat->geoplugin_countryCode;
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $allPlugins = get_plugins();
        foreach($allPlugins as $key => $value) {
            if($value['Name'] == 'Best Upsell for WooCommerce'){
                $version = $value['Version'];
            }
        }
        global $wpdb;
        $results = $wpdb->get_row("SELECT birthdate FROM " . $wpdb->prefix . "users");
        ?>
        <script>
            var iwp = {
                customerId :'<?php esc_html_e(get_current_user_id()); ?>',
                storename: '<?php esc_html(bloginfo() ); ?>',
                siteurl: '<?php echo esc_url(get_home_url() ); ?>',
                currency: '<?php esc_html_e(get_woocommerce_currency()); ?>',
                currencysign: '<?php  esc_html_e(get_woocommerce_currency_symbol()); ?>',
                userlocale: '<?php esc_html_e(get_user_locale()); ?>',
                decimals: '<?php  esc_html_e(wc_get_price_decimals()); ?>',
                checkouturl: '<?php echo esc_url( wc_get_checkout_url() ); ?>',
                vesrion: '<?php esc_html_e($version); ?>',
                country: '<?php esc_html_e($userLocation); ?>',
                currencyPosition: '<?php esc_html_e(get_option( 'woocommerce_currency_pos' )); ?>',
                currencyDecimal: '<?php esc_html_e( wc_get_price_decimals()); ?>',
                currencyDecimalSign: '<?php esc_html_e(wc_get_price_decimal_separator()); ?>',
                currencyThousandSign: '<?php esc_html_e(wc_get_price_thousand_separator()); ?>',
                birthdate :'<?php esc_html_e($results->birthdate); ?>',
                cart_url : '<?php echo esc_url(wc_get_cart_url());?>'
            };
            var icartLineItemData = <?php echo wp_json_encode($product_resut_new); ?>

        </script><?php
    }
}
add_action('wp_head', 'common_variables');
