<?php
/**
 * Class for the request to create a invoice checkout.
 *
 * @package Wasa_Kredit_Checkout/Classes/Requests/POST
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wasa_Kredit_Checkout_Request_Create_Invoice_Checkout class.
 */
class Wasa_Kredit_Checkout_Request_Create_Invoice_Checkout extends Wasa_Kredit_Checkout_Request_Post {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		// todo order id.
		$this->log_title = 'Create invoice checkout';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . '/v4/invoice/checkout';
	}

	/**
	 * Get the body for the request.
	 *
	 * @return array
	 */
	protected function get_body() {
		$order_id = $this->arguments['order_id'];
		$order    = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$order_data      = $order->get_data();
		$shipping_ex_vat = number_format( $order_data['shipping_total'], 2, '.', '' );
		$shipping_vat    = $order_data['shipping_tax'];
		$cart_items      = WC()->cart->get_cart();
		$wasa_cart_items = array();

		foreach ( $cart_items as $cart_item_key => $cart_item ) {
			$product = apply_filters(
				'woocommerce_cart_item_product',
				$cart_item['data'],
				$cart_item,
				$cart_item_key
			);

			$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );

			if ( ! empty( $tax_rates ) ) {
				$tax_rate = reset( $tax_rates )['rate'];
			} else {
				$tax_rate = '0';
			}

			$id   = $cart_item['product_id'];
			$name = $product->get_name();

			$price_inc_vat  = number_format( wc_get_price_including_tax( $product ), 2, '.', '' );
			$price_ex_vat   = number_format( wc_get_price_excluding_tax( $product ), 2, '.', '' );
			$vat_percentage = round( $tax_rate );
			$price_vat      = number_format( $price_inc_vat - $price_ex_vat, 2, '.', '' );
			$quantity       = $cart_item['quantity'];

			array_push(
				$wasa_cart_items,
				array(
					'product_id'           => $id,
					'product_name'         => $name,
					'price_ex_vat'         => $this->apply_currency( $price_ex_vat ),
					'price_incl_vat'       => $this->apply_currency( $price_inc_vat ),
					'quantity'             => $quantity,
					'vat_percentage'       => $vat_percentage,
					'vat_amount'           => $this->apply_currency( $price_vat ),
					'total_price_ex_vat'   => $this->apply_currency( number_format( $price_ex_vat * $quantity, 2, '.', '' ) ),
					'total_price_incl_vat' => $this->apply_currency( number_format( $price_inc_vat * $quantity, 2, '.', '' ) ),
					'total_vat'            => $this->apply_currency( number_format( $price_vat * $quantity, 2, '.', '' ) ),
				)
			);
		}

		// Create an array of all tax rates.
		$all_tax_rates = array_replace(
			WC_Tax::get_rates(),
			...array_map(
				function ( $tc ) {
					return WC_Tax::get_rates( $tc );
				},
				WC_Tax::get_tax_classes()
			)
		);

		// Add shipping cost for all shipping lines.
		foreach ( $order->get_data()['shipping_lines'] as $shipping_key => $line ) {
			$ex_vat       = intval( $line['total'] );
			$vat          = intval( $line['total_tax'] );
			$total        = $this->apply_currency( $ex_vat + $vat );
			$total_ex_vat = $this->apply_currency( $ex_vat );
			$total_vat    = $this->apply_currency( $vat );

			$shipping_vat_rate = 0;
			// Check if taxes are set for shipping line.
			if ( isset( $line['taxes'] ) &&
				array_key_exists( 'total', $line['taxes'] ) &&
				! empty( $line['taxes']['total'] ) ) {

				// Check to find tax rate, set if found.
				$tax = $all_tax_rates[ array_key_first( $line['taxes']['total'] ) ];
				if ( ! empty( $tax ) ) {
					$shipping_vat_rate = intval( $tax['rate'] );
				}
			}

			array_push(
				$wasa_cart_items,
				array(
					'product_id'           => '-',
					'product_name'         => $line['name'],
					'price_ex_vat'         => $total_ex_vat,
					'price_incl_vat'       => $total,
					'quantity'             => 1,
					'vat_percentage'       => $shipping_vat_rate,
					'vat_amount'           => $total_vat,
					'total_price_incl_vat' => $total,
					'total_price_ex_vat'   => $total_ex_vat,
					'total_vat'            => $total_vat,
				)
			);
		}

		// Create payload from collected data.
		$payload = array(
			'payment_types'             => 'invoice',
			'purchaser_name'            => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'purchaser_email'           => $order->get_billing_email(),
			'purchaser_phone'           => $order->get_billing_phone(),
			'recipient_name'            => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
			'recipient_phone'           => $order->get_billing_phone(),
			'billing_address'           => array(
				'company_name'   => $order->get_billing_company(),
				'street_address' => $order->get_billing_address_1(),
				'postal_code'    => $order->get_billing_postcode(),
				'city'           => $order->get_billing_city(),
				'country'        => $order->get_billing_country(),
			),
			'delivery_address'          => array(
				'company_name'   => $order->get_shipping_company(),
				'street_address' => $order->get_shipping_address_1(),
				'postal_code'    => $order->get_shipping_postcode(),
				'city'           => $order->get_shipping_city(),
				'country'        => $order->get_shipping_country(),
			),
			'order_references'          => array(
				array(
					'key'   => 'wasa_kredit_woocommerce_order_key',
					'value' => $order->get_id(),
				),
			),
			'cart_items'                => $wasa_cart_items,
			'shipping_cost_ex_vat'      => $this->apply_currency( $shipping_ex_vat ),
			'request_domain'            => get_site_url(),
			'confirmation_callback_url' => $order->get_checkout_order_received_url(),
			'ping_url'                  => get_rest_url( null, 'wasa-kredit-checkout/v1/update_order_status' ),
			'total_price_incl_vat'      => $this->apply_currency( $order_data['total'] ),
			'total_price_ex_vat'        => $this->apply_currency( number_format( ( $order_data['total'] - $order_data['total_tax'] ), 2, '.', '' ) ),
			'total_vat'                 => $this->apply_currency( number_format( $order_data['total_tax'], 2, '.', '' ) ),
		);

		return $payload;
	}
}
