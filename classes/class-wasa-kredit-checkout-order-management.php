<?php
/**
 * Order management class file.
 *
 * @package Wasa_Kredit_Checkout/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order management class.
 */
class Wasa_Kredit_Checkout_Order_Management {

	/**
	 * The plugin settings.
	 *
	 * @var array
	 */
	protected $settings;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$settings = get_option( 'wasa_kredit_settings' );

		if ( ! empty( $settings ) && empty( $settings['order_management'] ) ) {
			$settings['order_management'] = 'yes';
		}
		if ( 'yes' === $settings['order_management'] ) {
			add_action( 'woocommerce_order_status_completed', array( $this, 'order_status_change_completed' ) );
			add_action( 'woocommerce_order_status_cancelled', array( $this, 'order_status_change_cancelled' ) );
		}
		add_action( 'admin_notices', array( $this, 'no_credential_notice' ) );

	}

	public function order_status_change_completed( $order_id ) {
		// When an order is set to status Completed in WooCommerce.
		$this->send_order_status_to_wasa_api( $order_id, 'shipped' );
	}

	public function order_status_change_cancelled( $order_id ) {
		// When an order is set to status Cancelled in WooCommerce.
		$this->send_order_status_to_wasa_api( $order_id, 'canceled' );
	}

	private function send_order_status_to_wasa_api( $order_id, $order_status ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		// If this order wasn't created using Wasa payment methods, bail.
		if ( ! in_array( $order->get_payment_method(), array( 'wasa_kredit', 'wasa_kredit_invoice' ), true ) ) {
			return;
		}

		// Check if the order has been paid, otherwise bail.
		if ( empty( $order->get_date_paid() ) ) {
			return;
		}

		$transaction_id = $order->get_transaction_id();

		if ( empty( $transaction_id ) ) {
			return;
		}

		$wasa_status = Wasa_Kredit_WC()->api->get_wasa_kredit_order_status( $transaction_id );

		if ( is_wp_error( $wasa_status ) ) {
			$note = __( 'Error when trying to get Wasa Kredit order status.', 'wasa-kredit-checkout' );
			$order->add_order_note( $note );
			$order->save();
			return;
		}

		if ( $order_status === $wasa_status['status'] ) {
			return;
		}

		if ( $order_status === 'shipped' ) {
			$response = Wasa_Kredit_WC()->api->ship_order( $transaction_id );
		}

		if ( $order_status === 'canceled' ) {
			$response = Wasa_Kredit_WC()->api->cancel_order( $transaction_id );
		}

		if ( ! is_wp_error( $response ) ) {
			$order->add_order_note( __( 'Wasa Kredit order: ', 'wasa-kredit-checkout' ) . $order_status );
		} else {
			$note = __( 'Error: You changed order status to ', 'wasa-kredit-checkout' ) . $order_status . __( ' but the order could not be changed at Wasa Kredit.', 'wasa-kredit-checkout' );
			$order->add_order_note( $note );
		}
		
		$order->save();
	}

	function no_credential_notice() {
		$settings = get_option( 'wasa_kredit_settings' );

		if ( 'yes' === $settings['enabled'] && ( strlen( $settings['partner_id'] ) === 0 || strlen( $settings['client_secret'] ) === 0 ) ) {
			?>
			<div class="error notice">
				<p>
					<b><?php esc_html_e( 'Wasa Kredit Checkout:', 'wasa-kredit-checkout' ); ?></b> <?php esc_html_e( 'Please set your partner credentials on the', 'wasa-kredit-checkout' ); ?>
					<a href="/wp-admin/admin.php?page=wc-settings&tab=checkout&section=wasa_kredit"><?php esc_html_e( 'settings page', 'wasa-kredit-checkout' ); ?></a>.
				</p>
			</div>
			<?php
		}
	}
}
