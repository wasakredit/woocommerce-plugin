<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once plugin_dir_path( __FILE__ ) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_List_Widget {
	public function __construct() {
		$settings = get_option( 'wasa_kredit_settings' );

		// Connect to WASA PHP SDK
		$this->_client = new Sdk\Client(
			$settings['partner_id'],
			$settings['client_secret'],
			'yes' === $settings['test_mode'] ? true : false
		);

		// Hooks
		add_action(
			'woocommerce_before_shop_loop',
			array( $this, 'save_product_prices' ),
			10
		);

		add_action(
			'woocommerce_after_shop_loop_item',
			array( $this, 'display_leasing_price_per_product' ),
			9
		);

		add_shortcode( 'wasa_kredit_list_widget', array(
			$this,
			'display_leasing_price_per_product',
		));
	}

	public function display_leasing_price_per_product() {
		// Adds financing info betweeen price and Add to cart button
		global $product;

		$settings = get_option( 'wasa_kredit_settings' );

		if ( 'yes' !== $settings['widget_on_product_list'] ) {
			return;
		}

		$monthly_cost = 0;

		if ( isset( $GLOBALS['product_leasing_prices'] ) ) {
			$monthly_cost = $GLOBALS['product_leasing_prices'][ $product->get_id() ];
		}

		if ( $monthly_cost < 1 ) {
			return;
		}

		echo '<p>' .
			__( 'Financing', 'wasa-kredit-checkout' ) . ' ' .
			wc_price( $monthly_cost ) .
			'</p>';
	}

	public function save_product_prices() {
		// Collects all financing costs for all shown products
		// Store as global variable to be accessed in display_leasing_price_per_product()
		$settings = get_option( 'wasa_kredit_settings' );

		if ( 'yes' !== $settings['widget_on_product_list'] ) {
			return;
		}

		$payload['items'] = [];
		// Payload will contain all products with price, currency and id
		$current_currency = get_woocommerce_currency();
		$page_info        = get_queried_object();

		// Get all products from woocommerce
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => 1000,
		);

		if ( isset( $page_info->term_id ) ) {
			// Only include products in the currenct category, if a category is chosen
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $page_info->term_id,
				'operator' => 'IN',
			);
		}

		$loop = new WP_Query( $args );

		// Loop through all products
		while ( $loop->have_posts() ) :
			$loop->the_post();
			global $product;

			// Add this product to payload
			$payload['items'][] = array(
				'financed_price' => array(
					'amount'   => $product->get_price(),
					'currency' => $current_currency,
				),
				'product_id'     => $product->get_id(),
			);
		endwhile;

		wp_reset_query();

		// Get resposne from API with all products defined in $payload
		$response      = $this->_client->calculate_monthly_cost( $payload );
		$monthly_costs = [];

		if ( isset( $response ) && 200 === $response->statusCode ) {
			foreach ( $response->data['monthly_costs'] as $current_product ) {
				$monthly_costs[
					$current_product['product_id']
				] = $current_product['monthly_cost']['amount'];
			}

			// Save prices to global variable to access it from template
			$GLOBALS['product_leasing_prices'] = $monthly_costs;
		}
	}
}
