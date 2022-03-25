<?php
/**
 * Class for the request to get order.
 *
 * @package Wasa_Kredit_Checkout/Classes/Requests/GET
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Wasa Kredit order.
 */
class Wasa_Kredit_Checkout_Request_Get_Monthly_Cost_Widget extends Wasa_Kredit_Checkout_Request_Get {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Get monthly cost widget';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		$amount = $this->arguments['amount'];
		$format = $this->arguments['format'];
		return $this->get_api_url_base() . '/v4/leasing/widgets/monthly-cost?amount=' . $amount . '&currency=SEK&format=' . $format;
	}

}
