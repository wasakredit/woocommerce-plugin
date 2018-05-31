<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Wasa_Kredit_Checkout_Page {
	public function __construct() {
		//Hooks
		add_filter( 'template_redirect', function( $template ) {
			$wasa_kredit_checkout_id = get_query_var( 'wasa_kredit_checkout' );
			if ( $wasa_kredit_checkout_id ) {
				include plugin_dir_path( __FILE__ ) . '../templates/checkout-page.php';
				die;
			}
		});

		add_filter( 'query_vars', function ( $vars ) {
			$vars[] = 'wasa_kredit_checkout';
			return $vars;
		} );
	}
}
