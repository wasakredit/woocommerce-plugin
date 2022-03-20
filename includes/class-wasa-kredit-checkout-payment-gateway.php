<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

add_action( 'plugins_loaded', 'init_wasa_kredit_gateway' );
add_filter( 'woocommerce_payment_gateways', 'add_wasa_kredit_gateway' );

function add_wasa_kredit_gateway( $methods ) {
	$methods[] = 'Wasa_Kredit_Checkout_Payment_Gateway';

	return $methods;
}

function init_wasa_kredit_gateway() {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	class Wasa_Kredit_Checkout_Payment_Gateway extends WC_Payment_Gateway {

		public function __construct() {
			// Setup payment gateway properties.
			$this->id                 = 'wasa_kredit';
			$this->plugin_id          = 'wasa_kredit';
			$this->name               = 'Wasa Kredit';
			$this->title              = 'Wasa Kredit Leasing';
			$this->method_title       = 'Wasa Kredit Leasing';
			$this->description        = 'Use to pay with Wasa Kredit Leasing Checkout.';
			$this->method_description = 'Use to pay with Wasa Kredit Leasing Checkout.';
			$this->order_button_text  = __( 'Proceed', 'wasa-kredit-checkout' );
			$this->selected_currency  = get_woocommerce_currency();
			// Where to store settings in DB.
			$this->options_key = 'wasa_kredit_settings';

			$this->form_fields = $this->init_form_fields();
			$this->init_settings();

			// Setup dynamic gateway properties.
			if ( $this->settings['enabled'] ) {
				$this->enabled = $this->settings['enabled'];
			}

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
			// Defines settings fields on WooCommerce > Settings > Checkout > Wasa Kredit.
			return array(
				'enabled'                   => array(
					'title'   => __( 'Enable/Disable', 'wasa-kredit-checkout' ),
					'type'    => 'checkbox',
					'label'   => __(
						'Enable Wasa Kredit Checkout',
						'wasa-kredit-checkout'
					),
					'default' => 'yes',
				),
				'partner_id'                => array(
					'title'       => __( 'Partner ID', 'wasa-kredit-checkout' ),
					'type'        => 'text',
					'description' => __(
						'Partner ID is issued by Wasa Kredit.',
						'wasa-kredit-checkout'
					),
					'default'     => '',
				),
				'client_secret'             => array(
					'title'       => __( 'Client secret', 'wasa-kredit-checkout' ),
					'type'        => 'password',
					'description' => __(
						'Client Secret is issued by Wasa Kredit.',
						'wasa-kredit-checkout'
					),
					'default'     => '',
				),
				'test_partner_id'           => array(
					'title'       => __( 'Test Partner ID', 'wasa-kredit-checkout' ),
					'type'        => 'text',
					'description' => __(
						'Test Partner ID is issued by Wasa Kredit.',
						'wasa-kredit-checkout'
					),
					'default'     => '',
				),
				'test_client_secret'        => array(
					'title'       => __( 'Test Client secret', 'wasa-kredit-checkout' ),
					'type'        => 'password',
					'description' => __(
						'Test Client Secret is issued by Wasa Kredit.',
						'wasa-kredit-checkout'
					),
					'default'     => '',
				),
				'test_mode'                 => array(
					'title'       => __( 'Test mode', 'wasa-kredit-checkout' ),
					'type'        => 'checkbox',
					'label'       => __( 'Enable test mode', 'wasa-kredit-checkout' ),
					'default'     => 'yes',
					'description' => __(
						'This controls if the test API should be called or not. Do not use in production.',
						'wasa-kredit-checkout'
					),
				),
				'logging'                   => array(
					'title'       => __( 'Logging', 'wasa-kredit-checkout' ),
					'type'        => 'select',
					'label'       => __( 'Enable logging', 'wasa-kredit-checkout' ),
					'default'     => 'checkout',
					'description' => __( 'Save request data to the WooCommerce System Status log.', 'wasa-kredit-checkout' ),
					'options'     => array(
						'no'           => __( 'Do not log requests (except errors)', 'wasa-kredit-checkout' ),
						'monthly_cost' => __( 'Log monthly cost requests', 'wasa-kredit-checkout' ),
						'checkout'     => __( 'Log checkout requests', 'wasa-kredit-checkout' ),
						'all'          => __( 'Log both monthly cost & checkout requests', 'wasa-kredit-checkout' ),
					),
				),
				'widget_section'            => array(
					'title' => __( 'Monthly cost widget', 'wasa-kredit-checkout' ),
					'type'  => 'title',
				),
				'widget_on_product_list'    => array(
					'title'       => __( 'Enable/Disable', 'wasa-kredit-checkout' ),
					'type'        => 'checkbox',
					'label'       => __(
						'Show monthly cost in product list',
						'wasa-kredit-checkout'
					),
					'description' => __(
						'Will be shown under the price in product listings (added via the <i>woocommerce_after_shop_loop_item</i> hook).',
						'wasa-kredit-checkout'
					),
					'default'     => 'yes',
				),
				'widget_on_product_details' => array(
					'title'       => __( 'Enable/Disable', 'wasa-kredit-checkout' ),
					'type'        => 'checkbox',
					'label'       => __(
						'Show monthly cost in product details',
						'wasa-kredit-checkout'
					),
					'description' => __(
						'Will be shown between the price and the add to cart button. You can also use the shortcode [wasa_kredit_product_widget] if you want to add it manually on the product page.',
						'wasa-kredit-checkout'
					),
					'default'     => 'yes',
				),
				'widget_format'             => array(
					'title'       => __( 'Widget format', 'wasa-kredit-checkout' ),
					'type'        => 'select',
					'label'       => __( 'The design of the montly cost widget', 'wasa-kredit-checkout' ),
					'default'     => 'small',
					'description' => __( 'Select the design of the monthly cost widget', 'wasa-kredit-checkout' ),
					'options'     => array(
						'small'         => __( 'Small', 'wasa-kredit-checkout' ),
						'small-no-icon' => __( 'Small with no icons', 'wasa-kredit-checkout' ),
						'large'         => __( 'Large', 'wasa-kredit-checkout' ),
						'large-no-icon' => __( 'Large with no icons', 'wasa-kredit-checkout' ),
					),
				),
				'widget_lower_threshold'    => array(
					'title'       => __( 'Widget lower threshold', 'wasa-kredit-checkout' ),
					'type'        => 'number',
					'description' => __(
						'Only display the monthly cost widget if the product price is higher thant the entered number. Leave blank to disable this feature.',
						'wasa-kredit-checkout'
					),
					'default'     => '',
				),
			);
		}

		public function process_admin_options() {
			// On save in admin settings.
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
			// If payment gateway should be available for customers.

			$enabled = $this->get_option( 'enabled' );

			// Plugin is enabled.
			if ( 'yes' !== $enabled || is_null( WC()->cart ) ) {
				return false;
			}

			$cart_totals            = WC()->cart->get_totals();
			$cart_total             = $cart_totals['subtotal'] + $cart_totals['shipping_total'];
			$financed_amount_status = Wasa_Kredit_WC()->api->validate_financed_leasing_amount( $cart_total );

			// Cart value is within partner limits.
			if ( is_wp_error( $financed_amount_status ) || empty( $financed_amount_status['validation_result'] ) ) {
				// If total order value is too small or too large.
				return false;
			}

			$shipping_country = WC()->customer->get_billing_country();
			$currency         = get_woocommerce_currency();

			// Country is Sweden and currency is Swedish krona.
			if ( 'SE' !== $shipping_country || 'SEK' !== $currency ) {
				return false;
			}

			// Everything is fine, show payment method.
			return true;
		}

		public function process_payment( $order_id ) {
			// When clicking Proceed button, create a on-hold order.
			global $woocommerce;
			$order = new WC_Order( $order_id );

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}

		public function get_return_url( $order = null ) {
			// Add order key to custom endpoint route as query param.
			return add_query_arg(
				array(
					'wasa_kredit_checkout'       => $order->get_order_key(),
					'wasa_kredit_payment_method' => 'leasing',
				),
				get_site_url()
			);
		}

		public function get_title() {
			return __( 'Financing with Wasa Kredit Checkout', 'wasa-kredit-checkout' );
		}

		public function get_description() {
			// Set custom description to display in checkout.
			if ( isset( WC()->cart ) ) {

				$cart_totals = WC()->cart->get_totals();
				$cart_total  = $cart_totals['subtotal'] + $cart_totals['shipping_total'];

				$response2                = Wasa_Kredit_WC()->api->get_payment_methods( number_format( $cart_total, 2, '.', '' ) );
				$payment_options_response = Wasa_Kredit_WC()->api->get_leasing_payment_options( number_format( $cart_total, 2, '.', '' ) );
				if ( is_wp_error( $payment_options_response ) ) {
					return;
				}

				if( ! is_wp_error( $response2 ) ) { // @codingStandardsIgnoreLine - Our backend answers in with camelCasing, not snake_casing

					foreach ( $response2['payment_methods'] as $key => $value ) {
						if ( 'leasing' === $value['id'] || 'rental' === $value['id'] ) {
							$desc = '';
							if ( 'leasing' === $value['id'] ) {
								$desc = '<p><b>' . __( 'Finance your purchase with Wasa Kredit leasing', 'wasa-kredit-checkout' ) . '</b><br>';
							}
							if ( 'rental' === $value['id'] ) {
								$desc = '<p><b>' . __( 'Finance your purchase with Wasa Kredit rental', 'wasa-kredit-checkout' ) . '</b><br>';
							}
							$desc .= '<br>';

							$contract_lengths = $payment_options_response['contract_lengths'];

							foreach ( $contract_lengths as $key3 => $value3 ) {
								$months = $value3['contract_length'];
								$amount = $value3['monthly_cost']['amount'];

								// translators: %s placeholder is a number to display number of months.
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
			}
			return __( 'Financing with Wasa Kredit Checkout', 'wasa-kredit-checkout' );
		}
	}
}
