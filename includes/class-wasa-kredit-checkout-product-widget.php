<?php
/**
 * Product widget class file.
 *
 * @package Wasa_Kredit/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

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

		// Hooks.
		add_shortcode(
			'wasa_kredit_product_widget',
			array(
				$this,
				'wasa_kredit_product_widget',
			)
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

					if ( ! is_wp_error( $monthly_cost_response ) ) {
						$monthly_cost = $monthly_cost_response['monthly_costs'][0]['monthly_cost']['amount'];

						$formatted_financing_price = wc_price( $monthly_cost, array( 'decimals' => 0 ) ) . __( '/month', 'wasa-kredit-checkout' );

						return $default_formatted_price . ' (+ ' . $formatted_financing_price . ')';
					}
				}

				return $default_formatted_price;
			},
			10,
			3
		);

		// Extend the Wasa Kredit settings with product page settings.
		add_filter( 'wasa_kredit_settings', array( $this, 'extend_settings' ) );

		add_action( 'woocommerce_single_product_summary', array( $this, 'product_widget_hook' ), 1 );

	}

	/**
	 * Add product widget via hook.
	 */
	public function add_product_widget_to_product_page() {
		if ( 'yes' !== $this->settings['widget_on_product_details'] ) {
			return;
		}

		$widget                    = $this->get_product_widget();
		$widget_without_css        = preg_replace( '~<style(.*?)</style>~Usi', '', $widget );
		$widget_without_css_and_js = preg_replace( '~<script(.*?)</script>~Usi', '', $widget_without_css );
		echo wp_kses( $widget_without_css_and_js, wasa_kredit_allowed_tags() );
	}

	/**
	 * Add product widget via shortcode.
	 */
	public function wasa_kredit_product_widget() {
		return $this->get_product_widget();
	}

	/**
	 * Hook onto the product page (or custom hooks) to add the product widget.
	 *
	 * @return void
	 */
	public function product_widget_hook() {
		$settings = get_option( 'wasa_kredit_settings' );

		if ( ! empty( $settings['product_page_widget_placement_location'] ) ) {
			add_action(
				'woocommerce_single_product_summary',
				array(
					$this,
					'add_product_widget_to_product_page',
				),
				absint( $settings['product_page_widget_placement_location'] )
			);
		} else {
			$hook_name = $settings['custom_product_page_widget_placement_hook'];
			$priority  = absint( $settings['custom_product_page_widget_placement_hook_priority'] );

			add_action( $hook_name, array( $this, 'add_product_widget_to_product_page' ), $priority );
		}
	}


	/**
	 * Add settings for managing product page to the Wasa Kredit settings page.
	 *
	 * @param array $settings The Wasa Kredit settings.
	 * @return array The Wasa Kredit settings with product page settings added.
	 */
	public function extend_settings( $settings ) {
		$settings['product_page_widget_section'] = array(
			'title' => 'Product Page Widget',
			'type'  => 'title',
		);

		$settings['product_page_widget_placement_location'] = array(
			'title'   => __( 'Product Page Widget placement', 'wasa-kredit-checkout' ),
			'type'    => 'select',
			'options' => array(
				''   => __( 'No placement, I handle it myself', 'wasa-kredit-checkout' ),
				'4'  => __( 'Above Title', 'wasa-kredit-checkout' ),
				'7'  => __( 'Between Title and Price', 'wasa-kredit-checkout' ),
				'15' => __( 'Between Price and Excerpt', 'wasa-kredit-checkout' ),
				'25' => __( 'Between Excerpt and Add to cart button', 'wasa-kredit-checkout' ),
				'35' => __( 'Between Add to cart button and Product meta', 'wasa-kredit-checkout' ),
				'45' => __( 'Between Product meta and Product sharing buttons', 'wasa-kredit-checkout' ),
				'55' => __( 'After Product sharing-buttons', 'wasa-kredit-checkout' ),
			),
			'default' => '15',
			'desc'    => __( 'Select where to display the widget in your product pages.', 'wasa-kredit-checkout' ),
		);

		$settings['custom_product_page_widget_placement_hook'] = array(
			'title'    => __( 'Custom placement hook', 'wasa-kredit-checkout' ),
			'desc_tip' => __( 'Enter a custom hook where you want the product page widget to be placed.', 'wasa-kredit-checkout' ),
			'type'     => 'text',
		);

		$settings['custom_product_page_widget_placement_priority'] = array(
			'title'    => __( 'Custom placement hook priority', 'wasa-kredit-checkout' ),
			'desc_tip' => __( 'Enter a priority for the custom hook where you want the product page widget to be placed.', 'wasa-kredit-checkout' ),
			'type'     => 'text',
		);

		return $settings;
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
			$log   = Wasa_Kredit_Logger::format_log( '', 'GET', 'Aborting get_monthly_cost_widget', 'Price: ' . $price . '. Lower threshold: ' . $this->widget_lower_threshold, '', '', '200' );
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
