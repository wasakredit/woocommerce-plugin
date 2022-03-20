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
			// return 'https://auth.inttest-b2b.wasakredit.se/';
			return 'https://auth.inttest-b2b.wasakredit.se/connect/token';
			// return 'https://private-anon-98780a8c83-checkoutgatewayapi.apiary-mock.com/auth/connect/token';
		}

		// return 'https://b2b.services.wasakredit.se/auth';
		return 'https://b2b.services.wasakredit.se/auth/connect/token';
	}

	/**
	 * Get the request headers.
	 *
	 * @return array
	 */
	protected function get_request_headers( $body = '' ) {
		return array(
			/*'Authorization' => 'Basic ' . base64_encode( $this->get_partner_id() . ':' . $this->get_secret() ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- Base64 used to calculate auth header. */
			'Content-Type' => 'application/x-www-form-urlencoded',
		);
	}

	/**
	 * Get the body for the request.
	 *
	 * @return void
	 */
	protected function get_body() {
		$encodedID     = urlencode( $this->get_partner_id() );
		$encodedSecret = urlencode( $this->get_secret() );
		$fields        = "client_id=$encodedID&client_secret=$encodedSecret&grant_type=client_credentials";
		// $fields        = 'client_id=cbbd3568-2471-4416-b4ef-0596cdd1dc38&client_secret=%5Dk_lPnOu8zsUvntO&grant_type=client_credentials';
		/*
		$fields = array(
			'client_id'     => $this->get_partner_id(),
			'client_secret' => $this->get_secret(),
			'grant_type'    => 'client_credentials',
		);
		*/

		return $fields;
	}
}
