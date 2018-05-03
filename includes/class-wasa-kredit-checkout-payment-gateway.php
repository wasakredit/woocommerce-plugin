<?php
if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . '../php-checkout-sdk/Wasa.php';

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
            // Setup payment gateway properties
            $this->id                   = 'wasa_kredit';
            $this->plugin_id            = 'wasa_kredit';
            $this->name                 = 'Wasa Kredit';
            $this->title                = 'Wasa Kredit';
            $this->method_title         = 'Wasa Kredit';
            $this->description          = 'Use to pay with Wasa Kredit Checkout.';
            $this->method_description   = 'Use to pay with Wasa Kredit Checkout.';
            $this->order_button_text    = __( 'Proceed', 'wasa-kredit-checkout' );
            $this->selected_currency    = get_woocommerce_currency();

            // Where to store settings in DB
            $this->options_key = 'wasa_kredit_settings';

            $this->form_fields = $this->init_form_fields();
            $this->init_settings();

            // Setup dynamic gateway properties
            if ( $this->settings['enabled'] ) {
                $this->enabled = $this->settings['enabled'];
            }

            if ( $this->settings['title'] ) {
                $this->title = $this->settings['title'];
            }

            if ( $this->settings['description'] ) {
                $this->description = $this->settings['description'];
            }

            // Connect to WASA PHP SDK
            $this->_client = new Sdk\Client(
                $this->settings['partner_id'],
                $this->settings['client_secret'],
                $this->settings['test_mode'] == 'yes' ? true : false
            );

            // Hooks
            add_action(
                'woocommerce_update_options_payment_gateways_' . $this->id,
                array( $this, 'process_admin_options' )
            );
        }

        public function init_settings()
        {
            $this->settings = get_option( $this->options_key, null );

            // If there are no settings defined, use defaults.
            if ( ! is_array( $this->settings ) ) {
                $form_fields = $this->get_form_fields();

                $this->settings = array_merge(
                    array_fill_keys( array_keys( $form_fields ), '' ),
                    wp_list_pluck( $form_fields, 'default' )
                );
            }
        }

        public function init_form_fields()
        {
            // Defines settings fields on WooCommerce > Settings > Checkout > Wasa Kredit
            return array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __(
                        'Enable Wasa Kredit Checkout',
                        'wasa-kredit-checkout'
                    ),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __( 'Title', 'wasa-kredit-checkout' ),
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
                    'title' => __( 'Description', 'wasa-kredit-checkout' ),
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
                'countries' => array(
                    'title' => __(
                        'Enable for these countries',
                        'wasa-kredit-checkout'
                    ),
                    'desc' => '',
                    'id' => 'woocommerce_specific_allowed_countries',
                    'css' => 'min-width: 350px;',
                    'default' => '',
                    'type' => 'multiselect',
                    'options' => WC()->countries->get_countries()
                ),
                'cart_on_checkout' => array(
                    'title' => __( 'Enable/Disable', 'wasa-kredit-checkout' ),
                    'type' => 'checkbox',
                    'label' => __(
                        'Show cart content on checkout',
                        'wasa-kredit-checkout'
                    ),
                    'default' => 'no'
                ),
                'widget_on_product_list' => array(
                    'title' => __( 'Enable/Disable', 'wasa-kredit-checkout' ),
                    'type' => 'checkbox',
                    'label' => __(
                        'Show monthly cost in product list',
                        'wasa-kredit-checkout'
                    ),
                    'default' => 'yes'
                ),
                'partner_id' => array(
                    'title' => __( 'Partner ID', 'wasa-kredit-checkout' ),
                    'type' => 'text',
                    'description' => __(
                        'Partner ID is issued by Wasa Kredit.',
                        'wasa-kredit-checkout'
                    ),
                    'default' => ''
                ),
                'client_secret' => array(
                    'title' => __( 'Client secret', 'wasa-kredit-checkout' ),
                    'type' => 'text',
                    'description' => __(
                        'Client Secret is issued by Wasa Kredit.',
                        'wasa-kredit-checkout'
                    ),
                    'default' => ''
                ),
                'test_mode' => array(
                    'title' => __('Test mode', 'wasa-kredit-checkout'),
                    'type' => 'checkbox',
                    'label' => __( 'Enable test mode', 'wasa-kredit-checkout' ),
                    'default' => 'no'
                )
            );
        }

        public function process_admin_options()
        {
            // On save in admin settings
            $this->init_settings();

            $post_data = $this->get_post_data();

            foreach ( $this->get_form_fields() as $key => $field ) {
                if ( 'title' !== $this->get_field_type( $field ) ) {
                    try {
                        $this->settings[ $key ] = $this->get_field_value(
                            $key,
                            $field,
                            $post_data
                        );
                    } catch ( Exception $e ) {
                        $this->add_error( $e->getMessage() );
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

        public function is_available()
        {
            // If payment gateway should be available for customers
            $cart_total = WC()->cart->total;
            $financed_amount_status = $this->_client->validate_financed_amount($cart_total);
            $amount_is_ok = $financed_amount_status->data['validation_result'];

            if ( ! $amount_is_ok ) {
                // If total order value is too small or too large
                return false;
            }

            $shipping_country = WC()->customer->get_shipping_country();
            $available_countries = array_flip( $this->get_option( 'countries' ) );
            $enabled = $this->get_option( 'enabled' );

            // Only enable checkout if users country is in defined contries in settings
            if (
                $enabled === 'yes' &&
                array_key_exists( $shipping_country, $available_countries )
            ) {
                return true;
            }

            return false;
        }

        public function process_payment( $order_id )
        {
            // When clicking Proceed button, create a on-hold order
            global $woocommerce;
            $order = new WC_Order( $order_id );

            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );
        }

        public function get_return_url( $order = null )
        {
            // Get the URL for the checkout page, add order key to URL
            $checkout_page = get_page_by_title( 'Wasa Kredit Checkout' );
            $returnPage = get_permalink( $checkout_page );

            return $returnPage . '?key=' . $order->order_key;
        }
    }
}
