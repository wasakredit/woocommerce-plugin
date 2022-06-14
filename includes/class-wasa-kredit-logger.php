<?php
/**
 * Logger class file.
 *
 * @package Wasa_Kredit/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Logger class.
 */
class Wasa_Kredit_Logger {
	/**
	 * Log message string
	 *
	 * @var $log
	 */
	public static $log;

	/**
	 * Logs an event.
	 *
	 * @param string $data The data string.
	 * @param string $level info or error.
	 * @param string $log_type type of log request.
	 */
	public static function log( $data, $level = 'info', $log_type = 'checkout' ) {
		$wasa_kredit_settings = get_option( 'wasa_kredit_settings' );
		if ( ( 'all' === $wasa_kredit_settings['logging'] || $log_type === $wasa_kredit_settings['logging'] ) || 'error' === $level ) {
			$message = self::format_data( $data );
			if ( empty( self::$log ) ) {
				self::$log = new WC_Logger();
			}
			self::$log->add( 'wasa_kredit', wp_json_encode( $message ) );
		}
	}

	/**
	 * Formats the log data to prevent json error.
	 *
	 * @param string $data Json string of data.
	 * @return array
	 */
	public static function format_data( $data ) {
		if ( isset( $data['request']['body'] ) ) {
			$request_body            = json_decode( $data['request']['body'], true );
			$data['request']['body'] = $request_body;
		}

		return $data;
	}

	/**
	 * Formats the log data to be logged.
	 *
	 * @param string $checkout_id The gateway Checkout ID.
	 * @param string $method The method.
	 * @param string $title The title for the log.
	 * @param array  $request_args The request args.
	 * @param string $request_url The request url.
	 * @param array  $response The response.
	 * @param string $code The status code.
	 * @return array
	 */
	public static function format_log( $checkout_id, $method, $title, $request_args, $request_url, $response, $code ) {
		// Unset the snippet to prevent issues in the response.
		// Add logic to remove any HTML snippets from the response.

		// Unset the snippet to prevent issues in the request body.
		// Add logic to remove any HTML snippets from the request body.
		if ( 'Get monthly cost widget' === $title && ( $code > 199 && $code < 300 ) ) {
			$response = 'Response body removed since it is returning html.';
		}

		return array(
			'id'             => $checkout_id,
			'type'           => $method,
			'title'          => $title,
			'request_url'    => $request_url,
			'request'        => $request_args,
			'response'       => array(
				'body' => $response,
				'code' => $code,
			),
			'timestamp'      => date( 'Y-m-d H:i:s' ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions -- Date is not used for display.
			'stack'          => self::get_stack(),
			'plugin_version' => WASA_KREDIT_CHECKOUT_VERSION,
		);
	}

	/**
	 * Gets the stack for the request.
	 *
	 * @return array
	 */
	public static function get_stack() {
		$debug_data = debug_backtrace(); // phpcs:ignore WordPress.PHP.DevelopmentFunctions -- Data is not used for display.
		$stack      = array();
		foreach ( $debug_data as $data ) {
			$extra_data = '';
			if ( ! in_array( $data['function'], array( 'get_stack', 'format_log' ), true ) ) {
				if ( in_array( $data['function'], array( 'do_action', 'apply_filters' ), true ) ) {
					if ( isset( $data['object'] ) ) {
						$priority   = method_exists( $data['object'], 'current_priority' ) ? $data['object']->current_priority() : '';
						$name       = method_exists( $data['object'], 'current' ) ? key( $data['object']->current() ) : '';
						$extra_data = $name . ' : ' . $priority;

					}
				}
			}
			$stack[] = $data['function'] . $extra_data;
		}
		return $stack;
	}
}
