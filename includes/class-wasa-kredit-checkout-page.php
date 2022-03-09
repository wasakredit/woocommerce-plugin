<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Wasa_Kredit_Checkout_Page {
	public function __construct() {
		// Hooks.
		add_filter(
			'template_redirect',
			function( $template ) {
				$wasa_kredit_payment_method = get_query_var( 'wasa_kredit_payment_method' );
				if ( $wasa_kredit_payment_method === 'invoice' ) {
					include plugin_dir_path( __FILE__ ) . '../templates/invoice-checkout-page.php';
					die;
				} elseif ( $wasa_kredit_payment_method === 'leasing' ) {
					include plugin_dir_path( __FILE__ ) . '../templates/checkout-page.php';
					die;
				}
			}
		);

		add_filter(
			'query_vars',
			function ( $vars ) {
				$vars[] = 'wasa_kredit_checkout';
				$vars[] = 'wasa_kredit_payment_method';
				return $vars;
			}
		);
	}
}
