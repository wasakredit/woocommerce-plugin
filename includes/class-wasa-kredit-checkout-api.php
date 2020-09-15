<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once plugin_dir_path( __FILE__ ) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_API {
	public function __construct() {
		// Hooks
		add_action( 'woocommerce_api_wasa-order-payment-complete', array(
			$this,
			'order_payment_complete',
		));

		add_action( 'rest_api_init', function () {
			register_rest_route( 'wasa-kredit-checkout/v1', '/update_order_status', array(
				'methods'  => 'POST',
				'callback' => array(
					$this,
					'order_update_status',
				),
                'permission_callback' => array(
                    $this,
                    'order_update_stats_authorize'
                )
			) );
		} );

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
		 * Ie: domain/wc-api/wasa-order-payment-complete?key=wc_order_6543116e&wasa_kredit_order_id=6e-9f2e-4b4a-a25f-004068e9d210
		 */
		if ( ! isset( $_GET['key'] ) ) { //Input is ok
	        error_log("key is not set!");
			return;
		}

		// WooCommerce OrderKey
		$order_key = sanitize_text_field( wp_unslash( $_GET['key'] ) ); 
		// WooCommerce ID
		$order_id = wc_get_order_id_by_order_key( $order_key );
		// WooCommerce Order
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
		    error_log("no order found!");
			return;
		}

		if ( ! empty( (string) $_GET['wasa_kredit_order_id'] ) ) { //Input is ok
			// Add transaction ID to order, which is the Wasa order-id
			update_post_meta( $order->get_id(), '_transaction_id', sanitize_text_field( wp_unslash( (string) $_GET['wasa_kredit_order_id'] ) ) );
		}
	}

	public function order_update_stats_authorize(WP_REST_Request $request) {
	    return true;
    }

	public function order_update_status( WP_REST_Request $request ) {
		/**
		 * Updates the order status from Wasa order-id
		 * Ie: domain/wc-api/wasa-order-update-status?order_id=7002f7b1-9dff-4a60-8e54-3933cc1d1205&order_status=ready_to_ship
		 */

		$order_id     = $request->get_param( 'order_id' );
		$order_status = $request->get_param( 'order_status' );

		// Check input parameters
		if ( ! isset( $order_id ) || ! isset( $order_status ) ) {
		    error_log("no order id or status set!");
			return;
		}

		// Find the Woo order with the correct Wasa order-id
		$orders = wc_get_orders( array(
			'limit'           => 1,
			'transaction_id' => $order_id, //input var okay
		));

		// Make sure we have an order
		if ( ! $orders || count( $orders ) < 1 ) {
		    error_log("no order found with id = \"" . $order_id ."\"");
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

		if ( array_key_exists( wp_unslash( $order_status ), $approved_statuses ) ) { //Input is ok
			// Set order status if valid status
			$status = sanitize_text_field( wp_unslash( $order_status ) ); //Input is ok

			$order->update_status(
				$approved_statuses[ $status ],
				__( 'Wasa Kredit Checkout API change order status callback to', 'wasa-kredit-checkout' ) . ' ' . $status
			);

			//If status ready_to_ship Wasa Kredit has approved the financing. Complete payment of order
			if ( 'ready_to_ship' === $order_status ) {
				$order->payment_complete();
			}
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

		// Connect to WASA PHP SDK

		$this->_client = Wasa_Kredit_Checkout_SdkHelper::CreateClient();

		$response = $this->_client->update_order_status( $transaction_id, $order_status );

		if ( 200 !== $response->statusCode ) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
			$note = __( 'Error: You changed order status to ', 'wasa-kredit-checkout' ) . $order_status . __( ' but the order could not be changed at Wasa Kredit.', 'wasa-kredit-checkout' );
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
