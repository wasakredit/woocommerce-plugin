<?php
/**
 * Main request class
 *
 * @package Wasa_Kredit_Checkout/Classes/Requests
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base class for all request classes.
 */
abstract class Wasa_Kredit_Checkout_Request {

	/**
	 * The request method.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * The request title.
	 *
	 * @var string
	 */
	protected $log_title;

	/**
	 * The Qliro One order id.
	 *
	 * @var string
	 */
	protected $qliro_order_id;

	/**
	 * The request arguments.
	 *
	 * @var array
	 */
	protected $arguments;

	/**
	 * The plugin settings.
	 *
	 * @var array
	 */
	protected $settings;


	/**
	 * Class constructor.
	 *
	 * @param array $arguments The request args.
	 */
	public function __construct( $arguments = array() ) {
		$this->arguments = $arguments;
		$this->load_settings();
	}

	/**
	 * Loads the Qliro settings and sets them to be used here.
	 *
	 * @return void
	 */
	protected function load_settings() {
		$this->settings = get_option( 'wasa_kredit_settings' );
	}

	/**
	 * Get the API base URL.
	 *
	 * @return string
	 */
	protected function get_api_url_base() {
		if ( 'yes' === $this->settings['test_mode'] ) {
			return 'https://api.inttest-b2b.wasakredit.se';
		}

		return 'https://b2b.services.wasakredit.se';
	}

	/**
	 * Get the request headers.
	 *
	 * @param string $body json_encoded body.
	 * @return array
	 */
	protected function get_request_headers( $body = '' ) {
		return array(
			'Content-type'  => 'application/json',
			'Authorization' => 'Bearer ' . $this->calculate_auth( $body ),
		);
	}

	/**
	 * Calculates the basic auth.
	 *
	 * @return string
	 */
	protected function calculate_auth() {

		$wasa_kredit_access_token = json_decode( get_transient( 'wasa_kredit_access_token' ), true );

		// Return the token if we already have one.
		if ( ! empty( $wasa_kredit_access_token[ $this->get_partner_id() ] ) && ! empty( $wasa_kredit_access_token[ $this->get_partner_id() ]['token_expiry'] ) && ! $this->has_expired( $wasa_kredit_access_token ) ) {
			return $wasa_kredit_access_token[ $this->get_partner_id() ]['access_token'];
		}

		// We don't have a valid token. Let's request a new one.
		$auth_request = new Wasa_Kredit_Checkout_Request_Auth( array() );
		$response     = $auth_request->request();

		if ( ! is_wp_error( $response ) && isset( $response['access_token'] ) ) {
			$access_token_expiry                                 = ! empty( $response['expires_in'] ) ? $this->get_expires_at( $response['expires_in'] ) : $this->get_date_now_utc();
			$wasa_kredit_access_token                            = array();
			$wasa_kredit_access_token[ $this->get_partner_id() ] = array(
				'access_token' => $response['access_token'],
				'token_expiry' => $access_token_expiry,
			);

			set_transient( 'wasa_kredit_access_token', wp_json_encode( $wasa_kredit_access_token ), $response['expires_in'] );
			return $response['access_token'];
		}

		// FIXME:  If obtaining a token failed, and is_wp_error($token), then that should clearly be handled somehow.
		return null;
	}

	/**
	 * Get the user agent.
	 *
	 * @return string
	 */
	protected function get_user_agent() {
		return apply_filters(
			'http_headers_useragent',
			'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' )
		) . ' - WooCommerce: ' . WC()->version . ' - Wasa Kredit plugin: ' . WASA_KREDIT_CHECKOUT_VERSION . ' - PHP Version: ' . phpversion() . ' - Krokedil';
	}

	/**
	 * Get the request args.
	 *
	 * @return array
	 */
	abstract protected function get_request_args();

	/**
	 * Get the request url.
	 *
	 * @return string
	 */
	abstract protected function get_request_url();

	/**
	 * Make the request.
	 *
	 * @return object|WP_Error
	 */
	public function request() {
		$url      = $this->get_request_url();
		$args     = $this->get_request_args();
		$response = wp_remote_request( $url, $args );
		return $this->process_response( $response, $args, $url );
	}

	/**
	 * Processes the response checking for errors.
	 *
	 * @param object|WP_Error $response The response from the request.
	 * @param array           $request_args The request args.
	 * @param string          $request_url The request url.
	 * @return array|WP_Error
	 */
	protected function process_response( $response, $request_args, $request_url ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code < 200 || $response_code > 299 ) {
			$data          = 'URL: ' . $request_url . ' - ' . wp_json_encode( $request_args );
			$error_message = '';
			// Get the error messages.
			if ( null !== json_decode( $response['body'], true ) ) {
				$errors = json_decode( $response['body'], true );

				foreach ( $errors as $error ) {
					$error_message .= ' ' . $error;
				}
			}
			$code          = wp_remote_retrieve_response_code( $response );
			$error_message = empty( $response['body'] ) ? "API Error ${code}" : json_decode( $response['body'], true )['ErrorMessage'];
			$return        = new WP_Error( $code, $error_message, $data );
		} else {
			$return = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( empty( $return ) ) {
				$return = wp_remote_retrieve_body( $response );
			}
		}

		$this->log_response( $response, $request_args, $request_url );
		return $return;
	}

	/**
	 * Logs the response from the request.
	 *
	 * @param object|WP_Error $response The response from the request.
	 * @param array           $request_args The request args.
	 * @param string          $request_url The request URL.
	 * @return void
	 */
	protected function log_response( $response, $request_args, $request_url ) {
		$method   = $this->method;
		$title    = $this->log_title;
		$code     = wp_remote_retrieve_response_code( $response );
		$order_id = $response['OrderID'] ?? null;
		$log      = Wasa_Kredit_Logger::format_log( $order_id, $method, $title, $request_args, $request_url, $response, $code );
		$level    = ( $code < 200 || $code > 299 ) ? 'error' : 'info';

		Wasa_Kredit_Logger::log( $log, $level );
	}

	/**
	 * Returns a partner id
	 *
	 * @return string
	 */
	protected function get_partner_id() {
		return $this->is_test_mode() ? $this->settings['test_partner_id'] : $this->settings['partner_id'];
	}

	/**
	 * Returns a client secret
	 *
	 * @return string
	 */
	protected function get_secret() {
		return $this->is_test_mode() ? $this->settings['test_client_secret'] : $this->settings['client_secret'];
	}

	/**
	 * Is testmode enabled.
	 *
	 * @return bool
	 */
	protected function is_test_mode() {
		return 'yes' === $this->settings['test_mode'];
	}

	/**
	 * Logs the response from the request.
	 *
	 * @param int $seconds The number of seconds the token is valid.
	 * @return object
	 */
	protected function get_expires_at( $seconds ) {
		$expires_at = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$expires_at->add( new DateInterval( 'PT' . $seconds . 'S' ) );
		return $expires_at;
	}

	/**
	 * Get current date and time.
	 *
	 * @return object
	 */
	protected function get_date_now_utc() {
		return new DateTime( 'now', new DateTimeZone( 'UTC' ) );
	}

	/**
	 * Check if access token has expired or not..
	 *
	 * @param array $wasa_kredit_access_token The access token data.
	 * @return bool
	 */
	protected function has_expired( $wasa_kredit_access_token ) {
		return ! empty( $wasa_kredit_access_token[ $this->get_partner_id() ]['token_expiry'] ) ? $this->get_date_now_utc() >= DateTime::__set_state( $wasa_kredit_access_token[ $this->get_partner_id() ]['token_expiry'] ) : true;
	}

	/**
	 * Apply currency function.
	 *
	 * @param string $amount The current price.
	 * @return array
	 */
	protected function apply_currency( $amount ) {
		return array(
			'amount'   => $amount,
			'currency' => get_woocommerce_currency(),
		);
	}

}
