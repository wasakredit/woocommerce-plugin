<?php
/**
 * Class for the request to capture/ship the order.
 *
 * @package Wasa_Kredit_Checkout/Classes/Requests/POST
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wasa_Kredit_Checkout_Request_Ship_Order class.
 */
class Wasa_Kredit_Checkout_Request_Ship_Order extends Wasa_Kredit_Checkout_Request_Post {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Capture/ship order';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		$order_id = $this->arguments['order_id'];
		return $this->get_api_url_base() . '/v4/orders/' . $order_id . '/ship';
	}

	/**
	 * Get the body for the request.
	 *
	 * @return array
	 */
	protected function get_body() {
		return array();
	}
}
