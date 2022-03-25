<?php
/**
 * Class for calculating montly cost.
 *
 * @package Wasa_Kredit_Checkout/Classes/Requests/POST
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wasa_Kredit_Checkout_Request_Calculate_Monthly_Cost class.
 */
class Wasa_Kredit_Checkout_Request_Calculate_Monthly_Cost extends Wasa_Kredit_Checkout_Request_Post {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Calculate monthly cost';

		// Todo - create body logic in this class instead of when calling the get montly cost function.
		$this->arguments = $arguments;
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		return $this->get_api_url_base() . '/v4/leasing/monthly-cost';
	}

	/**
	 * Get the body for the request.
	 *
	 * @return array
	 */
	protected function get_body() {
		return $this->arguments;
	}
}
