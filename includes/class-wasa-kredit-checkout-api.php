<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

require_once plugin_dir_path(__FILE__) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_API
{
    public function __construct()
    {
        $settings = get_option( 'wasa_kredit_settings' );

        // Connect to WASA PHP SDK
        $this->_client = new Sdk\Client(
            $settings['partner_id'],
            $settings['client_secret'],
            $settings['test_mode'] == 'yes' ? true : false
        );

        // Hooks
        add_action( 'woocommerce_api_wasa-order-update-status' , array(
            $this,
            'api_order_update_status'
        ));

        add_action( 'woocommerce_api_wasa-order-payment-complete' , array(
            $this,
            'api_order_payment_complete'
        ));

        add_action( 'woocommerce_order_status_completed' , array(
            $this,
            'api_order_status_change_completed'
        ));

        add_action( 'woocommerce_order_status_cancelled' , array(
            $this,
            'api_order_status_change_cancelled'
        ));
    }

    public function api_order_payment_complete()
    {
        /* Is run onComplete after Wasa Checkout Payment is accepted.
            It will complete the payment, decrease stock, set it to status Processing.
            Ie: domain/wc-api/wasa-order-payment-complete?key=wc_order_6543116e&transactionId=6e-9f2e-4b4a-a25f-004068e9d210 */

        if ( ! isset( $_GET['key'] ) ) {
            return;
        }

        $order_key = $_GET['key'];
        // Wasa ID
        $order_id = wc_get_order_id_by_order_key( $order_key );
        // WooCommerce ID
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        if ( ! empty( $_GET['transactionId'] ) ) {
            // Add transaction ID to order, which is the WASA ID
            $order->payment_complete( $_GET['transactionId'] );
        } else {
            $order->payment_complete();
        }
    }

    public function api_order_update_status()
    {
        /* Updates the order status from WASA order ID
         Ie: domain/wc-api/wasa-order-update-status?id=6e-9f2e-4b4a-a25f-004068e9d210&status=processing */

        if ( !isset($_GET['id']) || !isset( $_GET['status']) ) {
            return;
        }

        // Find the woo order with the correct WASA ID
        $orders = wc_get_orders(array(
            'limit' => 1,
            'transaction_id' => $_GET['id']
        ));

        if ( !$orders || count( $orders ) < 1 ) {
            return;
        }

        $order = $orders[0];

        $approved_statuses = array(
            'initialized'   => 'pending',
            'pending'       => 'pending',
            'ready_to_ship' => 'processing',
            'shipped'       => 'completed',
            'canceled'      => 'cancelled'
        );

        if ( array_key_exists( $_GET['status'], $approved_statuses ) ) {
            // Set order status if valid status

            $order->update_status(
                $approved_statuses[ $_GET['status'] ],
                __( 'Wasa Kredit Checkout API change order status callback.' )
            );
        }
    }

    public function api_order_status_change_completed( $order_id ) {
        // When an order is set to status Completed in WooCommerce

        $order = wc_get_order( $order_id );
        $transaction_id = $order->get_transaction_id();
        $order_status = 'shipped';

        $this->_client->update_order_status($transaction_id, $order_status);
    }

    public function api_order_status_change_cancelled( $order_id ) {
        // When an order is set to status Cancelled in WooCommerce

        $order = wc_get_order( $order_id );
        $transaction_id = $order->get_transaction_id();
        $order_status = 'cancelled';

        $this->_client->update_order_status($transaction_id, $order_status);
    }
}
