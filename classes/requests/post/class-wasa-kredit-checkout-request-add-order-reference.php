<?php
/**
 * Class for the request to add an order reference to a Wasa Kredit order.
 *
 * @package Wasa_Kredit_Checkout/Classes/Requests/POST
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wasa_Kredit_Checkout_Request_Add_Order_Reference class.
 */
class Wasa_Kredit_Checkout_Request_Add_Order_Reference extends Wasa_Kredit_Checkout_Request_Post {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Add order reference';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		$order_id = $this->arguments['order_id'];
		return $this->get_api_url_base() . '/v4/orders/' . $order_id . '/order-references';
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

		// Create payload from collected data.
		$body = array(
			'partner_order_number' => $order->get_order_number(),
		);

		return $body;
	}
}
