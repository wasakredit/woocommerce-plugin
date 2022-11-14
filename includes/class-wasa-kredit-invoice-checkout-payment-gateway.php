<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
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

		// Hooks.
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array( $this, 'process_admin_options' )
		);
	}

	/**
	 * Get gateway icon.
	 *
	 * @return string
	 */
	public function get_icon() {

		$icon_src   = WASA_KREDIT_CHECKOUT_PLUGIN_URL . '/assets/images/wasa-kredit-icon.png';
		$icon_width = '26';
		$icon_html  = '<img src="' . $icon_src . '" alt="Wasa Kredit" style="max-width:' . $icon_width . 'px"/>';
		return apply_filters( 'truelayer_icon_html', $icon_html );
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
			'invoice_enabled'    => array(
				'title'   => __( 'Enable/Disable', 'wasa-kredit-checkout' ),
				'type'    => 'checkbox',
				'label'   => __(
					'Enable Wasa Kredit Invoice Checkout',
					'wasa-kredit-checkout'
				),
				'default' => 'no',
			),
			'partner_id'         => array(
				'title'       => __( 'Partner ID', 'wasa-kredit-checkout' ),
				'type'        => 'text',
				'description' => __(
					'Partner ID is issued by Wasa Kredit.',
					'wasa-kredit-checkout'
				),
				'default'     => '',
			),
			'client_secret'      => array(
				'title'       => __( 'Client secret', 'wasa-kredit-checkout' ),
				'type'        => 'password',
				'description' => __(
					'Client Secret is issued by Wasa Kredit.',
					'wasa-kredit-checkout'
				),
				'default'     => '',
			),
			'test_partner_id'    => array(
				'title'       => __( 'Test Partner ID', 'wasa-kredit-checkout' ),
				'type'        => 'text',
				'description' => __(
					'Test Partner ID is issued by Wasa Kredit.',
					'wasa-kredit-checkout'
				),
				'default'     => '',
			),
			'test_client_secret' => array(
				'title'       => __( 'Test Client secret', 'wasa-kredit-checkout' ),
				'type'        => 'password',
				'description' => __(
					'Test Client Secret is issued by Wasa Kredit.',
					'wasa-kredit-checkout'
				),
				'default'     => '',
			),
			'test_mode'          => array(
				'title'       => __( 'Test mode', 'wasa-kredit-checkout' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable test mode', 'wasa-kredit-checkout' ),
				'default'     => 'yes',
				'description' => __(
					'This controls if the test API should be called or not. Do not use in production.',
					'wasa-kredit-checkout'
				),
			),
			'logging'            => array(
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
			'order_management'   => array(
				'title'   => __( 'Enable Order Management', 'wasa-kredit-checkout' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Wasa Kredit order capture on WooCommerce order completion.', 'wasa-kredit-checkout' ),
				'default' => 'yes',
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

		$enabled = $this->get_option( 'invoice_enabled' );

		// Plugin is enabled.
		if ( 'yes' !== $enabled || is_null( WC()->cart ) ) {
			return false;
		}

		$cart_totals = WC()->cart->get_totals();
		$cart_total  = $cart_totals['subtotal'] + $cart_totals['shipping_total'];
		if ( empty( $this->financed_amount_status ) ) {
			$this->financed_amount_status = Wasa_Kredit_WC()->api->validate_financed_invoice_amount( $cart_total );
		}

		// Cart value is within partner limits.
		if ( is_wp_error( $this->financed_amount_status ) || empty( $this->financed_amount_status['validation_result'] ) ) {
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
				'wasa_kredit_payment_method' => 'invoice',
			),
			get_home_url()
		);
	}

	public function get_title() {
		return __( 'Wasa Kredit Invoice', 'wasa-kredit-checkout' );
	}

	public function get_description() {
		$desc  = '<p>' . __( 'Pay within 30 days', 'wasa-kredit-checkout' ) . '</p>';
		$desc .= '<p>' . __( 'Pay after you receive the item', 'wasa-kredit-checkout' ) . '</p>';
		return $desc;
	}
}
