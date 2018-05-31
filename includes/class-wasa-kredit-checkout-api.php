<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once plugin_dir_path( __FILE__ ) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_API {
	public function __construct() {
		// Hooks
		add_action( 'woocommerce_api_wasa-order-update-status', array(
			$this,
			'order_update_status',
		));

		add_action( 'woocommerce_api_wasa-order-payment-complete', array(
			$this,
			'order_payment_complete',
		));

		add_action( 'woocommerce_order_status_completed', array(
			$this,
			'order_status_change_completed',
		));

		add_action( 'woocommerce_order_status_cancelled', array(
			$this,
			'order_status_change_cancelled',
		));

		add_action( 'admin_notices', array(
			$this,
			'no_credential_notice',
		));
	}

	public function order_payment_complete() {
		/**
		 * Is run onComplete after Wasa Checkout Payment is accepted.
		 * It will complete the payment, decrease stock, set it to status Processing.
		 * Ie: domain/wc-api/wasa-order-payment-complete?key=wc_order_6543116e&transactionId=6e-9f2e-4b4a-a25f-004068e9d210
		 */

		if ( ! isset( $_GET['key'] ) ) { //input var okay
			return;
		}

		$order_key = sanitize_text_field( wp_unslash( $_GET['key'] ) ); //input var okay
		// Wasa ID
		$order_id = wc_get_order_id_by_order_key( $order_key );
		// WooCommerce ID
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( ! empty( $_GET['transactionId'] ) ) { //input var okay
			// Add transaction ID to order, which is the WASA ID
			$order->payment_complete( sanitize_text_field( wp_unslash( $_GET['transactionId'] ) ) ); //input var okay
		} else {
			$order->payment_complete();
		}
	}

	public function order_update_status() {
		/**
		 * Updates the order status from WASA order ID
		 * Ie: domain/wc-api/wasa-order-update-status?id=6e-9f2e-4b4a-a25f-004068e9d210&status=processing
		 */
		if ( ! isset( $_GET['id'] ) || ! isset( $_GET['status'] ) ) { //input var okay
			return;
		}

		// Find the woo order with the correct WASA ID
		$orders = wc_get_orders( array(
			'limit'          => 1,
			'transaction_id' => sanitize_text_field( wp_unslash( $_GET['id'] ) ), //input var okay
		));

		if ( ! $orders || count( $orders ) < 1 ) {
			return;
		}

		$order = $orders[0];

		$approved_statuses = array(
			'initialized'   => 'pending',
			'pending'       => 'pending',
			'ready_to_ship' => 'processing',
			'shipped'       => 'completed',
			'canceled'      => 'cancelled',
		);

		if ( array_key_exists( wp_unslash( $_GET['status'] ), $approved_statuses ) ) { //input var okay
			// Set order status if valid status
			$status = sanitize_text_field( wp_unslash( $_GET['status'] ) ); //input var okay
			$order->update_status(
				$approved_statuses[ $status ],
				__( 'Wasa Kredit Checkout API change order status callback to' ) . ' ' . $status
			);
		}
	}

	public function order_status_change_completed( $order_id ) {
		// When an order is set to status Completed in WooCommerce
		$this->send_order_status_to_wasa_api( $order_id, 'shipped' );
	}

	public function order_status_change_cancelled( $order_id ) {
		// When an order is set to status Cancelled in WooCommerce
		$this->send_order_status_to_wasa_api( $order_id, 'canceled' );
	}

	private function send_order_status_to_wasa_api( $order_id, $order_status ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$transaction_id = $order->get_transaction_id();

		if ( empty( $transaction_id ) ) {
			return;
		}

		$settings = get_option( 'wasa_kredit_settings' );

		// Connect to WASA PHP SDK
		$this->_client = new Sdk\Client(
			$settings['partner_id'],
			$settings['client_secret'],
			'yes' === $settings['test_mode'] ? true : false
		);

		$response = $this->_client->update_order_status( $transaction_id, $order_status );

		if ( 200 !== $response->statusCode ) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
			$note = __( 'Error: You changed order status to ' ) . $order_status . __( ' but the order could not be changed at Wasa Kredit.' );
			$order->add_order_note( $note );
			$order->save();
		}
	}

	function no_credential_notice() {
		$settings = get_option( 'wasa_kredit_settings' );

		if ( 'yes' === $settings['enabled'] && ( strlen( $settings['partner_id'] ) === 0 || strlen( $settings['client_secret'] ) === 0 ) ) {
			?>
				<div class="error notice">
					<p>
						<b><?php esc_html_e( 'Wasa Kredit Checkout:', 'wasa-kredit-checkout' ); ?></b> <?php esc_html_e( 'Please set your partner credentials on the', 'wasa-kredit-checkout' ); ?> <a href="/wp-admin/admin.php?page=wc-settings&tab=checkout&section=wasa_kredit"><?php esc_html_e( 'settings page', 'wasa-kredit-checkout' ); ?></a>.</p>
				</div>
			<?php
		}
	}
}
