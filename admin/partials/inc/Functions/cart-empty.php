<?php
/**
 * Empty cart page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-empty.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked wc_empty_cart_message - 10
 */
global $wpdb;
$minicart = $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix .'identixweb_bestupsell');
$full_cart = $minicart->full_cart_skeleton;
echo html_entity_decode(esc_html($full_cart)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
