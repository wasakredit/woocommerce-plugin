<?php
/**
 * Base class for all POST requests.
 *
 * @package Wasa_Kredit_Checkout/Classes/Request
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 *  The main class for POST requests.
 */
abstract class Wasa_Kredit_Checkout_Request_Post extends Wasa_Kredit_Checkout_Request {

	/**
	 * Wasa_Kredit_Checkout_Request_Post constructor.
	 *
	 * @param  array $arguments  The request arguments.
	 */
	public function __construct( $arguments = array() ) {
		parent::__construct( $arguments );
		$this->method = 'POST';
	}

	/**
	 * Build and return proper request arguments for this request type.
	 *
	 * @return array Request arguments
	 */
	protected function get_request_args() {
		if ( 'Auth request' === $this->log_title ) {
			$body = $this->get_body();
		} else {
			$body = wp_json_encode( apply_filters( 'wasa_kredit_request_args', $this->get_body() ) );
		}

		return array(
			'headers'    => $this->get_request_headers( $body ),
			'user-agent' => $this->get_user_agent(),
			'method'     => $this->method,
			'timeout'    => apply_filters( 'wasa_kredit_request_timeout', 10 ),
			'body'       => $body,
		);
	}

	/**
	 * Builds the request args for a POST request.
	 *
	 * @return array
	 */
	abstract protected function get_body();
}
