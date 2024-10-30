<?php
/**
 * @package Bestupsell
 */

add_filter( 'woocommerce_cart_item_price', 'icart_change_cart_table_price_display', 30, 3 );

function icart_change_cart_table_price_display( $price, $values, $cart_item_key ) {
    $slashed_price = $values['data']->get_price_html();
    $is_on_sale = $values['data']->is_on_sale();
    if ( $is_on_sale ) {
        $price = $slashed_price;
    }
    return $price;
}

// shows the product price on sale (if any) in the checkout table
add_filter( 'woocommerce_cart_item_subtotal', 'show_sale_price_at_checkout', 10, 3 );
function show_sale_price_at_checkout( $subtotal, $cart_item, $cart_item_key ) {

    // gets the product object
    $product = $cart_item['data'];
    // get the quantity of the product in the cart
    $quantity = $cart_item['quantity'];

    // check if the object exists
    if ( ! $product ) {
        return $subtotal;
    }

    // check if the product is on sale
    if ( $product->is_on_sale() && ! empty( $product->get_sale_price() ) ) {
        // shows sale price and regular price
        $price = wc_format_sale_price (
            // regular price
                wc_get_price_to_display(
                    $product, array(
                        'price' => $product->get_regular_price(),
                        'qty' => $quantity
                    )
                ),
                // sale price
                wc_get_price_to_display( $product, array (
                        'price' => $product->get_sale_price(),
                        'qty' => $quantity
                    )
                )
            ) . $product->get_price_suffix();
    } else {
        // shows regular price
        $price = wc_price (
            // regular price
                wc_get_price_to_display(
                    $product, array (
                        'price' => $product->get_regular_price(),
                        'qty' => $quantity
                    )
                )
            ) . $product->get_price_suffix();
    }
    return $price;
}