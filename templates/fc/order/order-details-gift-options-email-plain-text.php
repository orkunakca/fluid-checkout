<?php
/**
 * Checkout gift options order details section
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/fc/order/order-details-gift-options-email.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package fluid-checkout
 * @version 1.2.0
 */

defined( 'ABSPATH' ) || exit;

echo _x( 'Gift message:', 'Gift options section title in the order details', 'fluid-checkout' );

if ( isset( $gift_options ) && array_key_exists( '_fc_gift_message', $gift_options ) && ! empty( $gift_options[ '_fc_gift_message' ] ) ) {
    echo esc_attr( __( 'Gift Message:', 'fluid-checkout' ) );
    echo esc_attr( $gift_options[ '_fc_gift_message' ] );
}

if ( isset( $gift_options ) && array_key_exists( '_fc_gift_from', $gift_options ) && ! empty( $gift_options[ '_fc_gift_from' ] ) ) {
    echo esc_attr( __( 'Gift Message From:', 'fluid-checkout' ) );
    echo esc_attr( $gift_options[ '_fc_gift_from' ] );
}