<?php
/**
 * Functions file for the plugin.
 *
 * @package  Wasa_Kredit_Checkout/Includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/**
 * Echoes Wasa Kredit Checkout invoice iframe snippet.
 *
 * @param int $order_id The WooCommerce order id.
 * @return void
 */
function wasa_kredit_get_invoice_snippet( $order_id ) {
	$wasa_kredit_invoice_checkout = Wasa_Kredit_WC()->api->create_wasa_kredit_invoice_checkout( $order_id );

	if ( ! empty( $wasa_kredit_invoice_checkout ) ) {
		echo $wasa_kredit_invoice_checkout; // phpcs:ignore WordPress -- Can not escape this, since its the iframe snippet.
	}
}

/**
 * Echoes Wasa Kredit Checkout leasing iframe snippet.
 *
 * @param int $order_id The WooCommerce order id.
 * @return void
 */
function wasa_kredit_get_leasing_snippet( $order_id ) {
	$wasa_kredit_leasing_checkout = Wasa_Kredit_WC()->api->create_wasa_kredit_leasing_checkout( $order_id );

	if ( ! empty( $wasa_kredit_leasing_checkout ) ) {
		echo $wasa_kredit_leasing_checkout; // phpcs:ignore WordPress -- Can not escape this, since its the iframe snippet.
	}
}



/**
 * Prints error message as notices.
 *
 * @param WP_Error $wp_error A WordPress error object.
 * @return void
 */
function wasa_kredit_print_error_message( $wp_error ) {
	wc_print_notice( $wp_error->get_error_message(), 'error' );
}


