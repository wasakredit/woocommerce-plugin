<?php
/**
 * Handles the callbacks for the Wasa Kredit integration.
 *
 * @package Wasa_Kredit_Checkout/Classes
 */

/**
 * Class for handling the callbacks for the Qliro One integration
 */
class Wasa_Kredit_Callbacks {

	private static $status_mapping = array(
		'initialized'   => 'pending',
		'pending'       => 'pending',
		'ready_to_ship' => 'processing',
		'shipped'       => 'completed',
		'canceled'      => 'cancelled',
	);
	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action(
			'rest_api_init',
			function () {
				register_rest_route(
					'wasa-kredit-checkout/v1',
					'/update_order_status',
					array(
						'methods'             => 'POST',
						'callback'            => array(
							$this,
							'order_update_status',
						),
						'permission_callback' => array(
							$this,
							'order_update_stats_authorize',
						),
					)
				);
			}
		);
	}

	public function order_update_status( WP_REST_Request $request ) {
		/**
		 * Updates the order status from Wasa order-id
		 * Ie: domain/wc-api/wasa-order-update-status?order_id=7002f7b1-9dff-4a60-8e54-3933cc1d1205&order_status=ready_to_ship
		 */

		$wasa_order_id = $request->get_param( 'order_id' );
		$order_status  = $request->get_param( 'order_status' );

		// Check input parameters.
		if ( ! isset( $wasa_order_id ) || ! isset( $order_status ) ) {
			error_log( 'no order id or status set!' );
			return;
		}

		// Find the Woo order with the correct Wasa order-id.
		$orders = wc_get_orders(
			array(
				'limit'          => 1,
				'transaction_id' => $wasa_order_id, // input var okay.
			)
		);

		// Make sure we have an order.
		if ( ! $orders || count( $orders ) < 1 ) {
			$wasa_order = Wasa_Kredit_WC()->api->get_wasa_kredit_order( $wasa_order_id );

			if ( is_wp_error( $wasa_order ) ) {
				return;
			}

			foreach ( $wasa_order['order_references'] as $item ) {
				if ( $item['key'] === 'wasa_kredit_woocommerce_order_key' ) {
					$woo_order_key = $item['value'];
					break;
				}
			}
			if ( ! isset( $woo_order_key ) ) {
				error_log( 'No order found to update with id = "' . $wasa_order_id . '"' );
				return;
			}

			$woo_order_id = wc_get_order_id_by_order_key( $woo_order_key );
			$order        = wc_get_order( $woo_order_id );
			// Only allow changing wasa order associations as long as the order is in status pending,
			// meaning that no payment has been completed on wasa. This is because one order on woocommerce
			// can in rare scenarios be associated with multiple orders in wasa.
			if ( ! $order->has_status( 'pending' ) ) {
				$order->add_order_note(
					'Wasa Kredit sent order update for id ' . $wasa_order_id . ' -> ' .
					$order_status . ' but order was in state ' . $order->get_status() . ', ignoring update.'
				);
				return;
			}

			update_post_meta( $order->get_id(), '_transaction_id', $wasa_order_id );
			$order->add_order_note( __( 'Woocommerce associated order with wasa kredit id', 'wasa-kredit-checkout' ) . ' "' . $wasa_order_id . '"' );
		} else {
			$order = $orders[0];
		}

		if ( array_key_exists( wp_unslash( $order_status ), self::$status_mapping ) ) { // Input is ok
			// Set order status if valid status.
			$status = sanitize_text_field( wp_unslash( $order_status ) ); // Input is ok.

			$order->update_status(
				self::$status_mapping[ $status ],
				__( 'Wasa Kredit changed order status to', 'wasa-kredit-checkout' ) . ' ' . $status . ' -> '
			);

			// If status ready_to_ship Wasa Kredit has approved the financing. Complete payment of order.
			if ( 'ready_to_ship' === $order_status ) {
				$order->payment_complete();
			}
		} else {
			$order->add_order_note( __( 'Failed to find a mapping for Wasa Kredit status', 'wasa-kredit-checkout' ) . ' "' . $order_status . '"' );
		}
	}

	public function order_update_stats_authorize( WP_REST_Request $request ) {
		return true;
	}


} new Wasa_Kredit_Callbacks();
