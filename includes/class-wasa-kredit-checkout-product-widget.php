<?php // phpcs:ignore
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/lib/client-php-sdk/Wasa.php';

/**
 * Wasa_Kredit_Checkout_Product_Widget class.
 */
class Wasa_Kredit_Checkout_Product_Widget {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->settings               = get_option( 'wasa_kredit_settings' );
		$this->widget_format          = isset( $this->settings['widget_format'] ) ? $this->settings['widget_format'] : 'small';
		$this->widget_lower_threshold = isset( $this->settings['widget_lower_threshold'] ) ? $this->settings['widget_lower_threshold'] : '';

		$this->_client = Wasa_Kredit_Checkout_SdkHelper::CreateClient();

		// Hooks.
		add_shortcode(
			'wasa_kredit_product_widget',
			array(
				$this,
				'wasa_kredit_product_widget',
			)
		);

		add_action(
			'woocommerce_single_product_summary',
			array(
				$this,
				'add_product_widget_to_product_page',
			),
			15
		);

		add_filter(
			'woocommerce_product_addons_option_price',
			function ( $default_formatted_price, $options ) {
				if ( $options['price'] ) {
					$payload['items'][] = array(
						'financed_price' => array(
							'amount'   => $options['price'],
							'currency' => 'SEK',
						),
						'product_id'     => 'ADDON_PRICE',
					);

					$monthly_cost_response = Wasa_Kredit_WC()->api->calculate_monthly_cost( $payload );

					if ( isset( $monthly_cost_response ) && 200 === $monthly_cost_response->statusCode ) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
						$monthly_cost = $monthly_cost_response->data['monthly_costs'][0]['monthly_cost']['amount'];

						$formatted_financing_price = wc_price( $monthly_cost, array( 'decimals' => 0 ) ) . __( '/month', 'wasa-kredit-checkout' );

						return $default_formatted_price . ' (+ ' . $formatted_financing_price . ')';
					}
				}

				return $default_formatted_price;
			},
			10,
			3
		);

	}

	/**
	 * Add product widget via hook.
	 */
	public function add_product_widget_to_product_page() {
		if ( 'yes' !== $this->settings['widget_on_product_details'] ) {
			return;
		}

		echo $this->get_product_widget();
	}

	/**
	 * Add product widget via shortcode.
	 */
	public function wasa_kredit_product_widget() {
		return wp_kses_post( $this->get_product_widget() );
	}

	/**
	 * HTML for product widget.
	 */
	private function get_product_widget() {
		$product = wc_get_product();

		if ( ! $product ) {
			return;
		}

		if ( $product->is_type( 'variable' ) ) {
			$price = $product->get_variation_price( 'min' );
		} else {
			$price = wc_get_price_to_display( $product );
		}

		// Don't display widget if price is lower thant lower threshold setting.
		if ( ! empty( $this->widget_lower_threshold ) && $this->widget_lower_threshold > $price ) {
			$log      = Wasa_Kredit_Logger::format_log( '', 'GET', 'Aborting get_monthly_cost_widget', 'Price: ' . $price . '. Lower threshold: ' . $this->widget_lower_threshold, '', '', '200' ); // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
			$level = 'info';
			Wasa_Kredit_Logger::log( $log, $level, 'monthly_cost' );

			return;
		}

		$response = Wasa_Kredit_WC()->api->get_monthly_cost_widget( $price, $this->widget_format );

		if ( ! is_wp_error( $response ) ) {
			return '<div class="wasa-kredit-product-widget-container">' . $response . '</div>';
		}

		return false;
	}
}
