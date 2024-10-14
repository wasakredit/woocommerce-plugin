<?php
/**
 * Ajax class file.
 *
 * @package Wasa_Kredit_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Ajax class.
 */
class Wasa_Kredit_Ajax extends WC_AJAX {
	/**
	 * Hook in ajax handlers.
	 */
	public static function init() {
		self::add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */
	public static function add_ajax_events() {
		$ajax_events = array(
			'wasa_kredit_update_monthly_widget' => true,
		);
		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_woocommerce_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				// WC AJAX can be used for frontend ajax requests.
				add_action( 'wc_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}
		}
	}


	/**
	 * Update the monthly widget with new price.
	 *
	 * @return void An HTML snippet with the updated cost.
	 */
	public static function wasa_kredit_update_monthly_widget() {
		$nonce = isset( $_POST['nonce'] ) ? sanitize_key( $_POST['nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wasa_kredit_update_monthly_widget' ) ) {
			wp_send_json_error( 'bad_nonce' );
		}

		$price = filter_input( INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		if ( empty( $price ) ) {
			wp_send_json_error( 'invalid price' );
		}

		$price          = number_format( $price, 2, '.', '' );
		$product_widget = Wasa_Kredit_WC()->product_widget->get_product_widget( $price );
		empty( $product_widget ) ? wp_send_json_error( 'widget update failed' ) : wp_send_json_success( $product_widget );

	}
}
Wasa_Kredit_Ajax::init();
