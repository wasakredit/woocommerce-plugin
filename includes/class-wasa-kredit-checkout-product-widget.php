<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once plugin_dir_path( __FILE__ ) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_Product_Widget {
	public function __construct() {
		$this->settings = get_option( 'wasa_kredit_settings' );

		$this->_client = new Sdk\Client(
			$this->settings['partner_id'],
			$this->settings['client_secret'],
			'yes' === $this->settings['test_mode'] ? true : false
		);

		// Hooks
		add_shortcode( 'wasa_kredit_product_widget', array(
			$this,
			'wasa_kredit_product_widget',
		));

		add_action( 'woocommerce_before_add_to_cart_button', array(
			$this,
			'add_product_widget_to_product_page',
		));

		add_filter( 'woocommerce_product_addons_option_price', function ( $default_formatted_price, $options ) {
			if ( $options['price'] ) {
				$payload['items'][] = array(
					'financed_price' => array(
						'amount'   => $options['price'],
						'currency' => 'SEK',
					),
					'product_id'     => 'ADDON_PRICE',
				);

				$monthly_cost_response = $this->_client->calculate_monthly_cost( $payload );

				if ( isset( $monthly_cost_response ) && 200 === $monthly_cost_response->statusCode ) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
					$monthly_cost = $monthly_cost_response->data['monthly_costs'][0]['monthly_cost']['amount'];

					$formatted_financing_price = wc_price( $monthly_cost, array( 'decimals' => 0 ) ) . __( '/month', 'wasa-kredit-checkout' );

					return $default_formatted_price . ' (+ ' . $formatted_financing_price . ')';
				}
			}

			return $default_formatted_price;
		}, 10, 3);

	}

	public function add_product_widget_to_product_page() {
		if ( 'yes' !== $this->settings['widget_on_product_details'] ) {
			return;
		}

		echo $this->get_product_widget(); // @codingStandardsIgnoreLine - Should output html from our Backend
	}

	public function wasa_kredit_product_widget() {
		echo $this->get_product_widget(); // @codingStandardsIgnoreLine - Should output html from our Backend
	}

	private function get_product_widget() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$price    = $product->get_price();
		$response = $this->_client->get_monthly_cost_widget( $price );

		if ( isset( $response ) && 200 === $response->statusCode ) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
			return '<div class="wasa-kredit-product-widget-container">' . $response->data . '</div>';
		}

		return false;
	}
}
