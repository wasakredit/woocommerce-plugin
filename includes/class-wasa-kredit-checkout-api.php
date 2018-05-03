<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

class Wasa_Kredit_Checkout_API
{
    public function __construct()
    {
        // Hooks
        add_action( 'woocommerce_api_wasa-order-update-status' , array(
            $this,
            'api_order_update_status'
        ));

        add_action( 'woocommerce_api_wasa-order-payment-complete' , array(
            $this,
            'api_order_payment_complete'
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
            'initialized'   => 'processing',
            'canceled'      => 'cancelled',
            'pending'       => 'on-hold',
            'ready_to_ship' => 'processing',
            'shipped'       => 'completed'
        );

        if ( array_key_exists( $_GET['status'], $approved_statuses ) ) {
            // Set order status if valid status

            $order->update_status(
                $approved_statuses[ $_GET['status'] ],
                __( 'Wasa Kredit Checkout API change order status callback.' )
            );
        }
    }
}
