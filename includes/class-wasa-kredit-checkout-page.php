<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Wasa_Kredit_Checkout_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'wc_get_template', array( $this, 'replace_order_pay' ), 10, 2 );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
	}

	/**
	 * Replace the order-pay page with Wasa Kredit's checkout.
	 *
	 * Provided that Wasa Kredit is the chosen payment method.
	 *
	 * @param string $template Template name.
	 * @param string $template_name Template path.
	 * @return string
	 */
	public function replace_order_pay( $template, $template_name ) {
		if ( isset( WC()->session ) && false !== strpos( WC()->session->get( 'chosen_payment_method' ), 'wasa_kredit' ) ) {
			if ( 'checkout/form-pay.php' === $template_name && isset( WC()->session ) && is_wc_endpoint_url( 'order-pay' ) ) {
				$wasa_kredit_payment_method = get_query_var( 'wasa_kredit_payment_method' );
				if ( 'invoice' === $wasa_kredit_payment_method ) {
					return plugin_dir_path( __file__ ) . '../templates/invoice-checkout-page.php';
				} elseif ( 'leasing' === $wasa_kredit_payment_method ) {
					return plugin_dir_path( __file__ ) . '../templates/checkout-page.php';
				}
			}
		}
		return $template;
	}

	/**
	 * Add query vars related to Wasa Kredit.
	 *
	 * @param array $vars WP Query parameters.
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'wasa_kredit_checkout';
		$vars[] = 'wasa_kredit_payment_method';
		return $vars;
	}
}
