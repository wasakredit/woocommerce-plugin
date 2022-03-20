<?php
/**
 * Class for the request to get order status.
 *
 * @package Wasa_Kredit_Checkout/Classes/Requests/GET
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Wasa Kredit order status.
 */
class Wasa_Kredit_Checkout_Request_Get_Order_Status extends Wasa_Kredit_Checkout_Request_Get {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Get order status';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		$order_id = $this->arguments['order_id'];
		return $this->get_api_url_base() . '/v4/orders/' . $order_id . '/status';
	}

}
