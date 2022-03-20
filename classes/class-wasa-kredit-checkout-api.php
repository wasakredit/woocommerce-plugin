<?php
/**
 * API Class file.
 *
 * @package Wasa_Kredit_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wasa_Kredit_Checkout_API class.
 *
 * Class that has functions for the Wasa Kredit communication.
 */
class Wasa_Kredit_Checkout_API {

	/**
	 * Creates a Wasa Kredit invoice order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return mixed
	 */
	public function calculate_monthly_cost( $arguments = array() ) {
		$request  = new Wasa_Kredit_Checkout_Request_Calculate_Monthly_Cost( $arguments );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Creates a Wasa Kredit invoice order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return mixed
	 */
	public function create_wasa_kredit_invoice_checkout( $order_id = false ) {
		$request  = new Wasa_Kredit_Checkout_Request_Create_Invoice_Checkout( array( 'order_id' => $order_id ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Creates a Wasa Kredit leasing order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return mixed
	 */
	public function create_wasa_kredit_leasing_checkout( $order_id = false ) {
		$request  = new Wasa_Kredit_Checkout_Request_Create_Leasing_Checkout( array( 'order_id' => $order_id ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Validates the financed leasing amount.
	 *
	 * @param int $amount The amount to be validated.
	 * @return mixed
	 */
	public function validate_financed_leasing_amount( $amount = false ) {
		$request  = new Wasa_Kredit_Checkout_Request_Validate_Financed_Leasing_Amount( array( 'amount' => $amount ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Validates the financed invoice amount.
	 *
	 * @param int $amount The amount to be validated.
	 * @return mixed
	 */
	public function validate_financed_invoice_amount( $amount = false ) {
		$request  = new Wasa_Kredit_Checkout_Request_Validate_Financed_Invoice_Amount( array( 'amount' => $amount ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get monthly cost widget.
	 *
	 * @param int    $amount The amount to be validated.
	 * @param string $format The format of the widget.
	 * @return mixed
	 */
	public function get_monthly_cost_widget( $amount, $format = 'small' ) {
		$request  = new Wasa_Kredit_Checkout_Request_Get_Monthly_Cost_Widget(
			array(
				'amount' => $amount,
				'format' => $format,
			)
		);
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Add order reference to Wasa Kredit.
	 *
	 * Currently not used.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return mixed
	 */
	public function add_order_reference( $order_id ) {
		$request  = new Wasa_Kredit_Checkout_Request_Add_Order_Reference(
			array(
				'order_id' => $order_id,
			)
		);
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get a Wasa Kredit Order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return mixed
	 */
	public function get_wasa_kredit_order( $order_id = false ) {
		$request  = new Wasa_Kredit_Checkout_Get_Order( array( 'order_id' => $order_id ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get order status from a Wasa Kredit order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return mixed
	 */
	public function get_wasa_kredit_order_status( $order_id = false ) {
		$request  = new Wasa_Kredit_Checkout_Get_Order_Status( array( 'order_id' => $order_id ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Ship / capture Wasa Kredit order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return mixed
	 */
	public function ship_order( $order_id = false ) {
		$request  = new Wasa_Kredit_Checkout_Request_Ship_Order( array( 'order_id' => $order_id ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Cancel Wasa Kredit order.
	 *
	 * @param int $order_id The WooCommerce order id.
	 * @return mixed
	 */
	public function cancel_order( $order_id = false ) {
		$request  = new Wasa_Kredit_Checkout_Cancel_Order( array( 'order_id' => $order_id ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get available payment methods depending on the cart total.
	 *
	 * @param string $amount The cart amount.
	 * @return mixed
	 */
	public function get_payment_methods( $amount = false ) {
		$request  = new Wasa_Kredit_Checkout_Request_Get_Payment_Methods( array( 'amount' => $amount ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}

	/**
	 * Get leasing payment options.
	 *
	 * @param string $amount The cart amount.
	 * @return mixed
	 */
	public function get_leasing_payment_options( $amount = false ) {
		$request  = new Wasa_Kredit_Checkout_Request_Get_Leasing_Payment_Options( array( 'amount' => $amount ) );
		$response = $request->request();

		return $this->check_for_api_error( $response );
	}


	/**
	 * Checks for WP Errors and returns either the response as array or a false.
	 *
	 * @param array $response The response from the request.
	 * @return mixed
	 */
	private function check_for_api_error( $response ) {
		if ( is_wp_error( $response ) ) {
			if ( ! is_admin() ) {
				wasa_kredit_print_error_message( $response );
			}
		}
		return $response;
	}


}
