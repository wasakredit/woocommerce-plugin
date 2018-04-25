<?php
if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_Shortcodes
{
    private $_client;

    public function __construct()
    {
        $this->_client = new Sdk\Client(
            "03efa7fb-e3bf-4699-84d1-927de133dba7",
            "abc123",
            true
        );

        add_shortcode('wasa_kredit_product_widget', array(
            $this,
            'wasa_kredit_product_widget'
        ));
    }

    public function wasa_kredit_product_widget($atts = [])
    {
        $atts = array_change_key_case((array) $atts, CASE_LOWER);

        if (!$atts['price'] || !$atts['currency']) {
            echo '<p style="color: red">Wrong widget attributes!</p>';
            return;
        }

        $payload = array(
            'financial_product' => 'leasing',
            'price_ex_vat' => array(
                'amount' => $atts['price'],
                'currency' => $atts['currency']
            )
        );

        $response = $this->_client->create_product_widget($payload);

        if ($response->statusCode == "201") {
            echo '<div>' . $response->data . '</div>';
        }
    }
}
