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
		echo wp_kses( $wasa_kredit_invoice_checkout, wasa_kredit_allowed_tags() );
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
		echo wp_kses( $wasa_kredit_leasing_checkout, wasa_kredit_allowed_tags() );
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

/**
 * Function wasa_kredit_allowed_tags.
 * Set which tags are allowed in the content returned from Wasa Kredit.
 *
 * @return array
 */
function wasa_kredit_allowed_tags() {

	$allowed_tags = array(
		'a'          => array(
			'class' => array(),
			'href'  => array(),
			'rel'   => array(),
			'title' => array(),
		),
		'abbr'       => array(
			'title' => array(),
		),
		'b'          => array(),
		'blockquote' => array(
			'cite' => array(),
		),
		'cite'       => array(
			'title' => array(),
		),
		'code'       => array(),
		'del'        => array(
			'datetime' => array(),
			'title'    => array(),
		),
		'dd'         => array(),
		'div'        => array(
			'class'             => array(),
			'title'             => array(),
			'style'             => array(),
			'id'                => array(),
			'data-id'           => array(),
			'data-redirect-url' => array(),
		),
		'dl'         => array(),
		'dt'         => array(),
		'em'         => array(),
		'h1'         => array(),
		'h2'         => array(),
		'h3'         => array(),
		'h4'         => array(),
		'h5'         => array(),
		'h6'         => array(),
		'i'          => array(),
		'img'        => array(
			'alt'    => array(),
			'class'  => array(),
			'height' => array(),
			'src'    => array(),
			'width'  => array(),
		),
		'li'         => array(
			'class' => array(),
		),
		'ol'         => array(
			'class' => array(),
		),
		'p'          => array(
			'class' => array(),
		),
		'q'          => array(
			'cite'  => array(),
			'title' => array(),
		),
		'span'       => array(
			'class' => array(),
			'title' => array(),
			'style' => array(),
		),
		'strike'     => array(),
		'strong'     => array(),
		'ul'         => array(
			'class' => array(),
		),
		'style'      => array(
			'types' => array(),
		),
		'iframe'     => array(
			'src'             => array(),
			'height'          => array(),
			'width'           => array(),
			'frameborder'     => array(),
			'allowfullscreen' => array(),
		),
		'script'     => array(
			'type'  => array(),
			'src'   => array(),
			'async' => array(),
		),
	);

	return apply_filters( 'wasa_kredit_allowed_tags', $allowed_tags );
}
