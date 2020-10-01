<?php
if (!defined('ABSPATH')) {
    exit();
}

require_once plugin_dir_path(__FILE__) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_API
{
    private static $status_mapping = array(
        'initialized' => 'pending',
        'pending' => 'pending',
        'ready_to_ship' => 'processing',
        'shipped' => 'completed',
        'canceled' => 'cancelled',
    );

    public function __construct()
    {
        add_action('rest_api_init', function () {
            register_rest_route('wasa-kredit-checkout/v1', '/update_order_status', array(
                'methods' => 'POST',
                'callback' => array(
                    $this,
                    'order_update_status',
                ),
                'permission_callback' => array(
                    $this,
                    'order_update_stats_authorize'
                )
            ));
        });

        add_action('woocommerce_order_status_completed', array(
            $this,
            'order_status_change_completed',
        ));

        add_action('woocommerce_order_status_cancelled', array(
            $this,
            'order_status_change_cancelled',
        ));

        add_action('admin_notices', array(
            $this,
            'no_credential_notice',
        ));
    }

    public function order_update_stats_authorize(WP_REST_Request $request)
    {
        return true;
    }

    public function order_update_status(WP_REST_Request $request)
    {
        /**
         * Updates the order status from Wasa order-id
         * Ie: domain/wc-api/wasa-order-update-status?order_id=7002f7b1-9dff-4a60-8e54-3933cc1d1205&order_status=ready_to_ship
         */

        $wasa_order_id = $request->get_param('order_id');
        $order_status = $request->get_param('order_status');

        // Check input parameters
        if (!isset($wasa_order_id) || !isset($order_status)) {
            error_log("no order id or status set!");
            return;
        }

        // Find the Woo order with the correct Wasa order-id
        $orders = wc_get_orders(array(
            'limit' => 1,
            'transaction_id' => $wasa_order_id, //input var okay
        ));

        // Make sure we have an order
        if ($order_status === 'initialized' || !$orders || count($orders) < 1) {
            $client = Wasa_Kredit_Checkout_SdkHelper::CreateClient();
            $wasa_order = $client->get_order($wasa_order_id);
            foreach ($wasa_order->data['order_references'] as $item) {
                if ($item['key'] === 'wasa_kredit_woocommerce_order_key') {
                    $woo_order_key = $item['value'];
                    break;
                }
            }
            if (!isset($woo_order_key)) {
                error_log("No order found to update with id = \"" . $wasa_order_id . "\"");
                return;
            }

            $woo_order_id = wc_get_order_id_by_order_key($woo_order_key);
            $order = wc_get_order($woo_order_id);
            update_post_meta($order->get_id(), '_transaction_id', $wasa_order_id);
            $order->add_order_note(__('Woocommerce associated order with wasa kredit id', 'wasa-kredit-checkout') . ' "' . $wasa_order_id . '"');
        } else {
            $order = $orders[0];
        }

        if (array_key_exists(wp_unslash($order_status), self::$status_mapping)) { //Input is ok
            // Set order status if valid status
            $status = sanitize_text_field(wp_unslash($order_status)); //Input is ok

            $order->update_status(
                self::$status_mapping[$status],
                __('Wasa Kredit changed order status to', 'wasa-kredit-checkout') . ' ' . $status . ' -> '
            );

            //If status ready_to_ship Wasa Kredit has approved the financing. Complete payment of order
            if ('ready_to_ship' === $order_status) {
                $order->payment_complete();
            }
        } else {
            $order->add_order_note(__('Failed to find a mapping for Wasa Kredit status', 'wasa-kredit-checkout') . ' "' . $order_status . '"');
        }
    }

    public function order_status_change_completed($order_id)
    {
        // When an order is set to status Completed in WooCommerce
        $this->send_order_status_to_wasa_api($order_id, 'shipped');
    }

    public function order_status_change_cancelled($order_id)
    {
        // When an order is set to status Cancelled in WooCommerce
        $this->send_order_status_to_wasa_api($order_id, 'canceled');
    }

    private function send_order_status_to_wasa_api($order_id, $order_status)
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            return;
        }

        $transaction_id = $order->get_transaction_id();

        if (empty($transaction_id)) {
            return;
        }

        // Connect to WASA PHP SDK

        $client = Wasa_Kredit_Checkout_SdkHelper::CreateClient();
        $wasa_status = $client->get_order_status($transaction_id);
        if ($order_status === $wasa_status->data['status']) {
            return;
        }

        if ($order_status === 'shipped') {
            $response = $client->ship_order($transaction_id);

        }
        if ($order_status === 'canceled') {
            $response = $client->cancel_order($transaction_id);
        }

        if (200 !== $response->statusCode) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
            $note = __('Error: You changed order status to ', 'wasa-kredit-checkout') . $order_status . __(' but the order could not be changed at Wasa Kredit.', 'wasa-kredit-checkout');
            $order->add_order_note($note);
            $order->save();
        }
    }

    function no_credential_notice()
    {
        $settings = get_option('wasa_kredit_settings');

        if ('yes' === $settings['enabled'] && (strlen($settings['partner_id']) === 0 || strlen($settings['client_secret']) === 0)) {
            ?>
            <div class="error notice">
                <p>
                    <b><?php esc_html_e('Wasa Kredit Checkout:', 'wasa-kredit-checkout'); ?></b> <?php esc_html_e('Please set your partner credentials on the', 'wasa-kredit-checkout'); ?>
                    <a href="/wp-admin/admin.php?page=wc-settings&tab=checkout&section=wasa_kredit"><?php esc_html_e('settings page', 'wasa-kredit-checkout'); ?></a>.
                </p>
            </div>
            <?php
        }
    }
}
