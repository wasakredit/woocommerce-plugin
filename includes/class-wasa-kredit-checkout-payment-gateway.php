<?php
if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

add_action('plugins_loaded', 'init_wasa_kredit_gateway');
add_filter('woocommerce_payment_gateways', 'add_wasa_kredit_gateway');

function add_wasa_kredit_gateway($methods)
{
    $methods[] = 'WC_Gateway_Wasa_Kredit';

    return $methods;
}

function init_wasa_kredit_gateway()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_Gateway_Wasa_Kredit extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'wasa_kredit';
            $this->plugin_id = 'wasa_kredit';
            $this->name = 'Wasa Kredit';
            $this->title = "Wasa Kredit";
            $this->method_title = "Wasa Kredit";
            $this->description = "Use to pay with Wasa Kredit Checkout.";
            $this->method_description = "Use to pay with Wasa Kredit Checkout.";
            $this->selected_currency = get_woocommerce_currency();

            $this->options_key = "wasa_kredit_settings";

            $this->form_fields = $this->init_form_fields();
            $this->init_settings();

            if ($this->settings['title']) {
                $this->title = $this->settings['title'];
            }

            if ($this->settings['description']) {
                $this->description = $this->settings['description'];
            }

            add_action(
                'woocommerce_update_options_payment_gateways_' . $this->id,
                array($this, 'process_admin_options')
            );
        }

        function init_settings()
        {
            $this->settings = get_option($this->options_key, null);

            // If there are no settings defined, use defaults.
            if (!is_array($this->settings)) {
                $form_fields = $this->get_form_fields();

                $this->settings = array_merge(
                    array_fill_keys(array_keys($form_fields), ''),
                    wp_list_pluck($form_fields, 'default')
                );
            }
        }

        function init_form_fields()
        {
            return array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'wasa-kredit-checkout'),
                    'type' => 'checkbox',
                    'label' => __(
                        'Enable Wasa Kredit Checkout',
                        'wasa-kredit-checkout'
                    ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Title', 'wasa-kredit-checkout'),
                    'type' => 'text',
                    'description' => __(
                        'This controls the title which the user sees during checkout.',
                        'wasa-kredit-checkout'
                    ),
                    'default' => __(
                        'Wasa Kredit Checkout',
                        'wasa-kredit-checkout'
                    )
                ),
                'description' => array(
                    'title' => __('Description', 'wasa-kredit-checkout'),
                    'type' => 'textarea',
                    'description' => __(
                        'This controls the description which the user sees during checkout.',
                        'wasa-kredit-checkout'
                    ),
                    'default' => __(
                        "Pay via Wasa Kredit Checkout.",
                        'wasa-kredit-checkout'
                    )
                ),
                'merchant_id' => array(
                    'title' => __('Merchant ID', 'wasa-kredit-checkout'),
                    'type' => 'text',
                    'description' => __(
                        'Please enter your Merchant ID; this is needed in order to take payment.',
                        'wasa-kredit-checkout'
                    ),
                    'default' => ''
                ),
                'api_username' => array(
                    'title' => __('API Username', 'wasa-kredit-checkout'),
                    'type' => 'text',
                    'description' => __(
                        'Get your API credentials from Wasa.',
                        'wasa-kredit-checkout'
                    ),
                    'default' => '',
                    'desc_tip' => true,
                    'placeholder' => __('Optional', 'wasa-kredit-checkout')
                ),
                'api_password' => array(
                    'title' => __('API Password', 'wasa-kredit-checkout'),
                    'type' => 'text',
                    'description' => __(
                        'Get your API credentials from DIBS.',
                        'wasa-kredit-checkout'
                    ),
                    'default' => '',
                    'desc_tip' => true,
                    'placeholder' => __('Optional', 'wasa-kredit-checkout')
                ),
                'debug' => array(
                    'title' => __('Debug', 'wasa-kredit-checkout'),
                    'type' => 'checkbox',
                    'label' => __('Enable logging', 'wasa-kredit-checkout'),
                    'default' => 'no'
                )
            );
        }

        function process_admin_options()
        {
            $this->init_settings();

            $post_data = $this->get_post_data();

            foreach ($this->get_form_fields() as $key => $field) {
                if ('title' !== $this->get_field_type($field)) {
                    try {
                        $this->settings[$key] = $this->get_field_value(
                            $key,
                            $field,
                            $post_data
                        );
                    } catch (Exception $e) {
                        $this->add_error($e->getMessage());
                    }
                }
            }

            return update_option(
                $this->options_key,
                apply_filters(
                    'woocommerce_settings_api_sanitized_fields_' . $this->id,
                    $this->settings
                )
            );
        }

        function process_payment($order_id)
        {
            global $woocommerce;
            $order = new WC_Order($order_id);

            // Mark as on-hold (we're awaiting the cheque)
            $order->update_status(
                'on-hold',
                __('Awaiting Wasa Kredit Checkout payment', 'wasa_kredit')
            );

            // Reduce stock levels
            $order->reduce_order_stock();

            // Remove cart
            $woocommerce->cart->empty_cart();

            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        }
    }
}
