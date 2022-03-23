<?php
/**
 * Class for the request to get payment methods.
 *
 * @package Wasa_Kredit_Checkout/Classes/Requests/GET
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Wasa Kredit payment methods.
 */
class Wasa_Kredit_Checkout_Request_Get_Payment_Methods extends Wasa_Kredit_Checkout_Request_Get {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Get payment methods';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		$amount = $this->arguments['amount'];
		return $this->get_api_url_base() . '/v4/payment-methods?total_amount=' . $amount;
	}

}
