<?php
/**
 * Class for the request to capture the order.
 *
 * @package Wasa_Kredit_Checkout/Classes/Requests/POST
 */

defined( 'ABSPATH' ) || exit;

/**
 * Wasa_Kredit_Checkout_Request_Auth class.
 */
class Wasa_Kredit_Checkout_Request_Auth extends Wasa_Kredit_Checkout_Request_Post {

	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request arguments.
	 */
	public function __construct( $arguments ) {
		parent::__construct( $arguments );
		// todo order id.
		$this->log_title = 'Auth request';
	}

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	protected function get_request_url() {
		if ( 'yes' === $this->settings['test_mode'] ) {
			return 'https://auth.inttest-b2b.wasakredit.se/connect/token';
		}
		return 'https://b2b.services.wasakredit.se/auth/connect/token';
	}

	/**
	 * Get the request headers.
	 *
	 * @param array $body not used for auth request.
	 *
	 * @return array
	 */
	protected function get_request_headers( $body = '' ) {
		return array(
			'Content-Type' => 'application/x-www-form-urlencoded',
		);
	}

	/**
	 * Get the body for the request.
	 *
	 * @return string
	 */
	protected function get_body() {
		$encoded_id     = rawurlencode( $this->get_partner_id() );
		$encoded_secret = rawurlencode( $this->get_secret() );
		$fields         = "client_id=$encoded_id&client_secret=$encoded_secret&grant_type=client_credentials";

		return $fields;
	}
}
