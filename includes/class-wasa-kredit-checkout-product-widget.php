<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

require_once plugin_dir_path(__FILE__) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_Product_Widget
{
    public function __construct()
    {
        $this->settings = get_option( 'wasa_kredit_settings' );

        $this->_client = new Sdk\Client(
            $this->settings['partner_id'],
            $this->settings['client_secret'],
            $this->settings['test_mode'] == 'yes' ? true : false
        );

        // Hooks
        add_shortcode( 'wasa_kredit_product_widget' , array(
            $this,
            'wasa_kredit_product_widget'
        ));

        add_action( 'woocommerce_before_add_to_cart_button', array(
            $this,
            'add_product_widget_to_product_page'
        ));
    }

    public function add_product_widget_to_product_page() {
        global $product;

        if ( $this->settings['widget_on_product_details'] != "yes" ) {
            return;
        }

        if ( ! $product ) {
            return;
        }

        echo $this->get_product_widget( $product->get_price() );
    }

    public function wasa_kredit_product_widget( $atts = [] )
    {
        $atts = array_change_key_case( (array) $atts, CASE_LOWER );

        if ( ! $atts['price'] ) {
            echo '<p style="color: red">Wrong widget attributes!</p>';
            return;
        }
        
        echo $this->get_product_widget( $atts['price'], $atts['currency'] );
    }

    private function get_product_widget($price, $currency = null) {
        if ( ! $currency ) {
            $currency = get_woocommerce_currency();
        }

        $payload = array(
            'financial_product' => 'leasing',
            'price_ex_vat' => array(
                'amount' => $price,
                'currency' => $currency
            )
        );

        $response = $this->_client->create_product_widget( $payload );

        if ( isset( $response ) && $response->statusCode == '201' ) {
            return '<div class="wasa-kredit-product-widget-container">' . $response->data . '</div>';
        }

        return false;
    }
}
