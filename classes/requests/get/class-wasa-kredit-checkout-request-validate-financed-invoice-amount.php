<?php
/**
 * Class for the request to validate the invoice amount.
 *
 * @package Wasa_Kredit_Checkout/Classes/Requests/GET
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get Wasa Kredit order.
 */
class Wasa_Kredit_Checkout_Request_Validate_Financed_Invoice_Amount extends Wasa_Kredit_Checkout_Request_Get {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		$this->log_title = 'Validate financed invoice amount';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		$amount = $this->arguments['amount'];
		return $this->get_api_url_base() . '/v4/invoice/validate-financed-amount?amount=' . $amount;
	}

}
