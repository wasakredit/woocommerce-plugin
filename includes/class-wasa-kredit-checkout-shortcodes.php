<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

require_once plugin_dir_path(__FILE__) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_Shortcodes
{
    public function __construct()
    {
        $settings = get_option( 'wasa_kredit_settings' );

        $this->_client = new Sdk\Client(
            $settings['partner_id'],
            $settings['client_secret'],
            $settings['test_mode'] == 'yes' ? true : false
        );

        // Hooks
        add_shortcode( 'wasa_kredit_product_widget' , array(
            $this,
            'wasa_kredit_product_widget'
        ));
    }

    public function wasa_kredit_product_widget( $atts = [] )
    {
        $atts = array_change_key_case( (array) $atts, CASE_LOWER );

        if ( ! $atts['price'] ) {
            echo '<p style="color: red">Wrong widget attributes!</p>';
            return;
        }

        if ( ! $atts['currency'] ) {
            // Fallback if no currency is defined
            $atts['currency'] = get_woocommerce_currency();
        }

        $payload = array(
            'financial_product' => 'leasing',
            'price_ex_vat' => array(
                'amount' => $atts['price'],
                'currency' => $atts['currency']
            )
        );

        $response = $this->_client->create_product_widget( $payload );

        if ( isset( $response ) && $response->statusCode == '201' ) {
            echo '<div>' . $response->data . '</div>';
        }
    }
}
