<?php
if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . '../php-checkout-sdk/Wasa.php';

class Wasa_Kredit_Checkout_List_Product_Prices
{
    private $_client;

    public function __construct()
    {
        $this->_client = new Sdk\Client(
            "03efa7fb-e3bf-4699-84d1-927de133dba7",
            "abc123",
            true
        );

        add_action(
            'woocommerce_before_shop_loop',
            array($this, 'wasa_save_product_prices'),
            10
        );
    }

    public function wasa_save_product_prices()
    {
        //TODO: Figure out how to get all products in list
        $product_leasing_prices = [];

        $payload['items'][] = array(
                    'financed_price' => array(
                        'amount' => '14995.00',
                        'currency' => 'SEK'
                    ),
                    'product_id' => '12345'
                );

        $response = $this->_client->calculate_monthly_cost($payload);

        if ($response->statusCode == "200") {
            $GLOBALS['product_leasing_prices'] = $response->data;
        }

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 10000
        );
    
        $loop = new WP_Query( $args );
    
        while ( $loop->have_posts() ) : $loop->the_post();
            global $product;
            $product_leasing_prices[$product->get_id()] = 'kaka';
            //echo '<br /><a href="'.get_permalink().'">' . woocommerce_get_product_thumbnail().' '.get_the_title().'</a>';
        endwhile;
    
        wp_reset_query();

        echo '<pre>'; print_r($response->data); print_r($product_leasing_prices); echo '</pre>';
    }
}
