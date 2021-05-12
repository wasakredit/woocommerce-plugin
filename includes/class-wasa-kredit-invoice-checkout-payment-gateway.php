<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

require_once plugin_dir_path( __FILE__ ) . '../vendor/wasa/client-php-sdk/Wasa.php';

add_action( 'plugins_loaded', 'init_wasa_kredit_invoice_gateway' );
add_action( 'woocommerce_before_checkout_form', 'create_redirect_to_standard_checkout_view', 10, 1 );
add_filter( 'woocommerce_payment_gateways', 'add_wasa_kredit_invoice_gateway' );

function add_wasa_kredit_invoice_gateway( $methods ) {
	$methods[] = 'Wasa_Kredit_InvoiceCheckout_Payment_Gateway';

	return $methods;
}

/*
function create_redirect_to_standard_checkout_view() {
	include plugin_dir_path( __FILE__ ) . '../templates/redirect-to-standard-checkout.php';
}*/

function init_wasa_kredit_invoice_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	class Wasa_Kredit_InvoiceCheckout_Payment_Gateway extends WC_Payment_Gateway {
		public function __construct() {
			// Setup payment gateway properties.
			$this->id                 = 'wasa_kredit_invoice';
			$this->plugin_id          = 'wasa_kredit_invoice';
			$this->name               = 'Wasa Kredit Faktura';
			$this->title              = 'Wasa Kredit Faktura';
			$this->method_title       = 'Wasa Kredit Faktura';
			$this->description        = 'Use to pay with Wasa Kredit Faktura Checkout.';
			$this->method_description = 'Use to pay with Wasa Kredit Faktura Checkout.';
			$this->order_button_text  = __( 'Proceed', 'wasa-kredit-checkout' );
			$this->selected_currency  = get_woocommerce_currency();
			// Where to store settings in DB.
			$this->options_key = 'wasa_kredit_settings';

			$this->form_fields = $this->init_form_fields();
			$this->init_settings();

			// Setup dynamic gateway properties.
			$this->enabled = isset( $this->settings['invoice_enabled'] ) ? $this->settings['invoice_enabled'] : 'no';

			// Connect to WASA PHP SDK.
			$this->_client = Wasa_Kredit_Checkout_SdkHelper::CreateClient();

			// Hooks.
			add_action(
				'woocommerce_update_options_payment_gateways_' . $this->id,
				array( $this, 'process_admin_options' )
			);
		}

		public function init_settings() {
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

		public function init_form_fields() {
			// Defines settings fields on WooCommerce > Settings > Checkout > Wasa Kredit
			return array(
				'invoice_enabled'                          => array(
					'title'   => __( 'Enable/Disable', 'wasa-kredit-checkout' ),
					'type'    => 'checkbox',
					'label'   => __(
						'Enable Wasa Kredit Invoice Checkout',
						'wasa-kredit-checkout'
					),
					'default' => 'no',
				),
				'partner_id'                               => array(
					'title'       => __( 'Partner ID', 'wasa-kredit-checkout' ),
					'type'        => 'text',
					'description' => __(
						'Partner ID is issued by Wasa Kredit.',
						'wasa-kredit-checkout'
					),
					'default'     => '',
				),
				'client_secret'                            => array(
					'title'       => __( 'Client secret', 'wasa-kredit-checkout' ),
					'type'        => 'password',
					'description' => __(
						'Client Secret is issued by Wasa Kredit.',
						'wasa-kredit-checkout'
					),
					'default'     => '',
				),
				'test_partner_id'                          => array(
					'title'       => __( 'Test Partner ID', 'wasa-kredit-checkout' ),
					'type'        => 'text',
					'description' => __(
						'Test Partner ID is issued by Wasa Kredit.',
						'wasa-kredit-checkout'
					),
					'default'     => '',
				),
				'test_client_secret'                       => array(
					'title'       => __( 'Test Client secret', 'wasa-kredit-checkout' ),
					'type'        => 'password',
					'description' => __(
						'Test Client Secret is issued by Wasa Kredit.',
						'wasa-kredit-checkout'
					),
					'default'     => '',
				),
				'test_mode'                                => array(
					'title'       => __( 'Test mode', 'wasa-kredit-checkout' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable test mode', 'wasa-kredit-checkout' ),
					'default'     => 'yes',
					'description' => __(
						'This controls if the test API should be called or not. Do not use in production.',
						'wasa-kredit-checkout'
					),
				),
				'logging'                                  => array(
					'title'       => __( 'Logging', 'wasa-kredit-checkout' ),
					'type'        => 'select',
					'label'       => __( 'Enable logging', 'wasa-kredit-checkout' ),
					'default'     => 'checkout',
					'description' => __( 'Save request data to the WooCommerce System Status log.', 'wasa-kredit-checkout' ),
					'options'     => array(
						'monthly_cost' => __( 'Log monthly cost requests', 'wasa-kredit-checkout' ),
						'checkout'     => __( 'Log checkout requests', 'wasa-kredit-checkout' ),
						'all'          => __( 'Log both monthly cost & checkout requests', 'wasa-kredit-checkout' ),
					),
				),
				'add_redirect_to_standard_checkout_widget' => array(
					'title'       => __( 'Advanced', 'wasa-kredit-checkout' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable redirect to standard checkout widget', 'wasa-kredit-checkout' ),
					'default'     => 'no',
					'description' => __(
						'This is an advanced setting that is not needed for most integrations. Enable it if you have replaced the standard woocommerce checkout page with another checkout page. It will present a widget where the user can navigate to the standard checkout and use Wasa Kredit as a payment method.',
						'wasa-kredit-checkout'
					),
				),
				'standard_checkout_page_route'             => array(
					'title'       => __( 'Advanced', 'wasa-kredit-checkout' ),
					'type'        => 'text',
					'label'       => __( 'Standard checkout page route', 'wasa-kredit-checkout' ),
					'default'     => '',
					'description' => __(
						'This is an advanced setting that is not needed for most integrations. If the setting redirect to standard checkout widget above is enabled, this setting is the route of the standard checkout page the user will be redirected to.',
						'wasa-kredit-checkout'
					),
				),
			);
		}

		public function process_admin_options() {
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

		public function is_available() {
			// If payment gateway should be available for customers

			$enabled = $this->get_option( 'invoice_enabled' );

			// Plugin is enabled
			if ( 'yes' !== $enabled || is_null( WC()->cart ) ) {
				return false;
			}

			$cart_totals            = WC()->cart->get_totals();
			$cart_total             = $cart_totals['subtotal'] + $cart_totals['shipping_total'];
			$financed_amount_status = $this->_client->validate_financed_invoice_amount( $cart_total );

			// Cart value is within partner limits
			if ( ! isset( $financed_amount_status )
				|| ( 200 !== $financed_amount_status->statusCode // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing
				|| ! $financed_amount_status->data['validation_result'] ) ) {
				// If total order value is too small or too large
				return false;
			}

			$shipping_country = WC()->customer->get_billing_country();
			$currency         = get_woocommerce_currency();

			// Country is Sweden and currency is Swedish krona
			if ( 'SE' !== $shipping_country || 'SEK' !== $currency ) {
				return false;
			}

			// Everything is fine, show payment method.
			return true;
		}

		public function process_payment( $order_id ) {
			// When clicking Proceed button, create a on-hold order
			global $woocommerce;
			$order = new WC_Order( $order_id );

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}

		public function get_return_url( $order = null ) {
			// Add order key to custom endpoint route as query param
			return add_query_arg(
				array(
					'wasa_kredit_checkout'       => $order->get_order_key(),
					'wasa_kredit_payment_method' => 'invoice',
				),
				get_site_url()
			);
		}

		public function get_title() {
			// Set custom title to payment to display in checkout
			if ( isset( WC()->cart ) ) {

				$cart_totals = WC()->cart->get_totals();

				$total_costs =
					$cart_totals['subtotal'] +
					$cart_totals['shipping_total'] +
					$cart_totals['fee_total'];

				return __( 'Invoice', 'wasa-kredit-checkout' );
			}

			return __( 'Financing with Wasa Kredit Invoice Checkout', 'wasa-kredit-checkout' );
		}

		public function get_description() {

			/*
			//Set custom description to display in checkout
			if ( isset( WC()->cart ) ) {

				$cart_totals = WC()->cart->get_totals();
				$cart_total  = $cart_totals['subtotal'] + $cart_totals['shipping_total'];

				$response2 = $this->_client->get_payment_methods( round($cart_total, 2) );

				if ( isset( $response2 ) && 200 === $response2->statusCode ) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing

					foreach ( $response2->data['payment_methods'] as $key => $value ) {
						if ( 'leasing' === $value['id'] || 'rental' === $value['id'] ) {

							$desc = '<p><b>' . __( 'Finance your purchase with Wasa Kredit', 'wasa-kredit-checkout' ) . '</b><br>';
							if ( 'leasing' === $value['id'] ) {
								$desc = '<p><b>' . __( 'Finance your purchase with Wasa Kredit leasing', 'wasa-kredit-checkout' ) . '</b><br>';
							}
							if ( 'rental' === $value['id'] ) {
								$desc = '<p><b>' . __( 'Finance your purchase with Wasa Kredit rental', 'wasa-kredit-checkout' ) . '</b><br>';
							}
							$desc .= '<br>';

							$options          = $value['options'];
							$contract_lengths = $options['contract_lengths'];

							foreach ( $contract_lengths as $key3 => $value3 ) {
								$months = $value3['contract_length'];
								$amount = $value3['monthly_cost']['amount'];

								// translators: %s placeholder is a number to display number of months
								$desc_months = sprintf( __( ' for %s months.', 'wasa-kredit-checkout' ), $months );
								$desc_item   = '<br>' . wc_price( $amount, array( 'decimals' => 0 ) ) . __( '/month', 'wasa-kredit-checkout' ) . $desc_months;
								$desc       .= $desc_item;
							}
							$desc .= '<br><br>';
							$desc .= __( 'Proceed to select your monthly cost.', 'wasa-kredit-checkout' );
						}
					}
					$desc .= '</p>';
			return $desc;
				}
			} */
			return __( 'Financing with Wasa Kredit Checkout', 'wasa-kredit-checkout' );
		}
	}
}
