<?php
/**
 * Wasa_Kredit_Checkout_List_Widget class.
 *
 * @package Wasa_Kredit_Checkout\Classes
 * Class that handles display of Wasa Kredit monthly cost price on archive pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Wasa_Kredit_Checkout_List_Widget class.
 */
class Wasa_Kredit_Checkout_List_Widget {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->settings               = get_option( 'wasa_kredit_settings' );
		$this->widget_lower_threshold = isset( $this->settings['widget_lower_threshold'] ) ? $this->settings['widget_lower_threshold'] : '';

		// Hooks.
		add_action(
			'woocommerce_before_shop_loop',
			array( $this, 'save_product_prices' ),
			10
		);

		add_action(
			'woocommerce_shortcode_before_products_loop',
			array( $this, 'save_product_prices_shortcodes' ),
			10
		);

		add_action(
			'woocommerce_after_shop_loop_item',
			array( $this, 'display_leasing_price_per_product' ),
			9
		);

		add_shortcode(
			'wasa_kredit_list_widget',
			array(
				$this,
				'display_leasing_price_per_product',
			)
		);
	}

	/**
	 * Display price per month function.
	 */
	public function display_leasing_price_per_product() {

		// Adds financing info betweeen price and Add to cart button.
		global $product;

		if ( 'yes' !== $this->settings['widget_on_product_list'] ) {
			return;
		}

		$monthly_cost = 0;

		if ( isset( $GLOBALS['product_leasing_prices'] ) &&
			isset( $GLOBALS['product_leasing_prices'][ $product->get_id() ] ) ) {
			$monthly_cost = $GLOBALS['product_leasing_prices'][ $product->get_id() ];
		}

		if ( $monthly_cost < 1 ) {
			return;
		}

		echo '<p>' .
		esc_html( __( 'Financing', 'wasa-kredit-checkout' ) ) . ' <span style="white-space:nowrap;">' .
		wp_kses_post( wc_price( $monthly_cost, array( 'decimals' => 0 ) ) ) . esc_html( __( '/month', 'wasa-kredit-checkout' ) ) .
			'</span></p>';

	}

	/**
	 * Fetch and save prices.
	 */
	public function save_product_prices() {
		// Collects all financing costs for all shown products.
		// Store as global variable to be accessed in display_leasing_price_per_product().

		if ( 'yes' !== $this->settings['widget_on_product_list'] ) {
			return;
		}

		$payload['items'] = array();
		// Payload will contain all products with price, currency and id.
		$current_currency = get_woocommerce_currency();

		global $wp_query;
		$loop = $wp_query;

		// Loop through all products.
		while ( $loop->have_posts() ) :
			$loop->the_post();
			global $product;

			// Don't try to add the product if we don't have a product object.
			if ( ! is_object( $product ) ) {
				continue;
			}

			if ( $product->is_type( 'variable' ) ) {
				$price = $product->get_variation_price( 'min' );
			} else {
				$price = wc_get_price_to_display( $product );
			}

			// Don't add product if price is lower thant lower threshold setting.
			if ( ! empty( $this->widget_lower_threshold ) && $this->widget_lower_threshold > $price ) {
				continue;
			}

			// Add this product to payload.
			$payload['items'][] = array(
				'financed_price' => array(
					'amount'   => wc_format_decimal( $product->get_price(), 2 ),
					'currency' => $current_currency,
				),
				'product_id'     => $product->get_id(),
			);
		endwhile;

		// Get resposne from API with all products defined in $payload.
		$response      = Wasa_Kredit_WC()->api->calculate_monthly_cost( $payload );
		$monthly_costs = array();

		if ( ! is_wp_error( $response ) ) {
			foreach ( $response['monthly_costs'] as $current_product ) {
				$monthly_costs[ $current_product['product_id'] ] = $current_product['monthly_cost']['amount'];
			}

			// Save prices to global variable to access it from template.
			$GLOBALS['product_leasing_prices'] = $monthly_costs;
		}
	}

	/**
	 * Fetch and save product pricces, via shortcode.
	 *
	 * @param array $args The shortcode args.
	 */
	public function save_product_prices_shortcodes( $args ) {
		// Collects all financing costs for all shown products.
		// Store as global variable to be accessed in display_leasing_price_per_product().

		if ( 'yes' !== $this->settings['widget_on_product_list'] ) {
			return;
		}

		$payload['items'] = array();
		// Payload will contain all products with price, currency and id.
		$current_currency = get_woocommerce_currency();

		$args = array(
			'post_type'      => 'product',
			'cat'            => $args['category'],
			'page'           => $args['page'],
			'orderby'        => $args['orderby'],
			'order'          => $args['order'],
			'posts_per_page' => $args['limit'],
			'tag'            => $args['tag'],
		);

		if ( $args['page'] > 1 && $args['limit'] != -1 ) {
			$args['paged'] = true;
		}

		if ( ! empty( $args['ids'] ) ) {
			$args['post__in'] = array_map( 'trim', explode( ',', $args['ids'] ) );
		}

		$wp_query = new WP_Query( $args );
		$loop     = $wp_query;

		// Loop through all products.
		while ( $loop->have_posts() ) :
			$loop->the_post();
			global $product;

			// Add this product to payload.
			$payload['items'][] = array(
				'financed_price' => array(
					'amount'   => wc_format_decimal( $product->get_price(), 2 ),
					'currency' => $current_currency,
				),
				'product_id'     => $product->get_id(),
			);
		endwhile;

		wp_reset_postdata();

		// Get resposne from API with all products defined in $payload.
		$response      = Wasa_Kredit_WC()->api->calculate_monthly_cost( $payload );
		$monthly_costs = array();

		if ( ! is_wp_error( $response ) ) {
			foreach ( $response['monthly_costs'] as $current_product ) {
				$monthly_costs[ $current_product['product_id'] ] = $current_product['monthly_cost']['amount'];
			}

			// Save prices to global variable to access it from template.
			$GLOBALS['product_leasing_prices'] = $monthly_costs;
		}
	}
}
