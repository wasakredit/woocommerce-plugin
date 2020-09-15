<?php

namespace Client;

use PHPUnit\Framework\TestCase;

use Sdk\Client;
use Sdk\ClientFactory;

class ClientTest extends TestCase
{

    public function test_ClientFactory_create() {
        $client = ClientFactory::CreateClient(getenv('clientId'), getenv('clientSecret'), true);
        $response = $client->create_invoice_checkout($this->createInvoiceCheckoutPayload());
        $this->assertTrue($response->__get("statusCode") == 201);
    }

    public function test_CreateInvoiceCheckout()
    {

        $client = new Client(getenv('clientId'), getenv('clientSecret'),
            'test_base_url',
            'test_access_token_url',
            true);
        $response = $client->create_invoice_checkout($this->createInvoiceCheckoutPayload());
        $this->assertTrue($response->__get("statusCode") == 201);

    }

    private function createInvoiceCheckoutPayload() {
        return array(
            'payment_types' => 'invoice',
            'order_reference_id' => 'order-10000123',
            'cart_items' => $this->getInvoiceCartItems(),
            'customer_organization_number'=> '5560268343',
            'purchaser_name' => 'Janne Jannesson',
            'purchaser_email' => 'aktiebolagett@gmail.com',
            'purchaser_phone' => '0702872364',
            'billing_address' => array(
                'company_name' => 'Jannes Färghandel AB',
                'street_address' => 'Box 5182',
                'postal_code' => '13140',
                'city' => 'Nacka',
                'country' => 'Sverige'
            ),
            'billing_details' => array(
                'billing_tag' => 'Julklapp',
                'billing_reference' => 'Ekonomiavdelningen'
            ),
            'delivery_address' => array(
                'company_name' => 'Jannes Färghandel AB',
                'street_address' => 'Box 5182',
                'postal_code' => '13140',
                'city' => 'Nacka',
                'country' => 'Sverige'
            ),
            'recipient_name' => 'Janne',
            'recipient_phone' => '08-6353700',
            'request_domain' => 'http://my-shop.se',
            'confirmation_callback_url' => 'http://my-shop.se/thanks-for-order',
            'total_price_incl_vat' => array(
                'amount' => '15487.50',
                'currency' => 'SEK'),
            'total_price_ex_vat' => array(
                'amount' => '12390.00',
                'currency' => 'SEK'),
            'total_vat' => array(
                'amount' => '3097.50',
                'currency' => 'SEK'),
            'partner_reference'=>'Sven Säljgren, vitvaror'
        );
    }

    private function getInvoiceCartItems()
    {
        return
            array(
                array(
                    'product_id' => '3038-4025-12',
                    'product_name' => 'Toapapper',
                    'price_ex_vat' => array(
                        'amount' => '2590.00',
                        'currency' => 'SEK'
                    ),
                    'price_incl_vat' => array(
                        'amount' => '3237.50',
                        'currency' => 'SEK'
                    ),
                    'quantity' => 1,
                    'vat_percentage' => '25',
                    'vat_amount' => array(
                        'amount' => '647.50',
                        'currency' => 'SEK'
                    ),
                    'total_price_ex_vat' => array(
                        'amount' => '2590.00',
                        'currency' => 'SEK'
                    ),
                    'total_price_incl_vat' => array(
                        'amount' => '3237.50',
                        'currency' => 'SEK'
                    ),
                    'total_vat' => array(
                        'amount' => '647.50',
                        'currency' => 'SEK'
                    )
                ),
                array(
                    'product_id' => '3038-4025-14',
                    'product_name' => 'Slippapper',
                    'price_ex_vat' => array(
                        'amount' => '4870.00',
                        'currency' => 'SEK'
                    ),
                    'price_incl_vat' => array(
                        'amount' => '6087.50',
                        'currency' => 'SEK'
                    ),
                    'quantity' => 2,
                    'vat_percentage' => '25',
                    'vat_amount' => array(
                        'amount' => '1217.50',
                        'currency' => 'SEK'
                    ),
                    'total_price_ex_vat' => array(
                        'amount' => '9740.00',
                        'currency' => 'SEK'
                    ),
                    'total_price_incl_vat' => array(
                        'amount' => '12175.00',
                        'currency' => 'SEK'
                    ),
                    'total_vat' => array(
                        'amount' => '2435.00',
                        'currency' => 'SEK'
                    )
                ),
                array(
                    'product_id' => '1234-1234-12',
                    'product_name' => 'Fakturaavgift',
                    'price_ex_vat' => array(
                        'amount' => '40.00',
                        'currency' => 'SEK'
                    ),
                    'price_incl_vat' => array(
                        'amount' => '50.00',
                        'currency' => 'SEK'
                    ),
                    'quantity' => 1,
                    'vat_percentage' => '25',
                    'vat_amount' => array(
                        'amount' => '10.00',
                        'currency' => 'SEK'
                    ),
                    'total_price_ex_vat' => array(
                        'amount' => '40.00',
                        'currency' => 'SEK'
                    ),
                    'total_price_incl_vat' => array(
                        'amount' => '50.00',
                        'currency' => 'SEK'
                    ),
                    'total_vat' => array(
                        'amount' => '10.00',
                        'currency' => 'SEK'
                    )
                ),
                array(
                    'product_id' => '1234-1234-11',
                    'product_name' => 'Frakt',
                    'price_ex_vat' => array(
                        'amount' => '20.00',
                        'currency' => 'SEK'
                    ),
                    'price_incl_vat' => array(
                        'amount' => '25.00',
                        'currency' => 'SEK'
                    ),
                    'quantity' => 1,
                    'vat_percentage' => '25',
                    'vat_amount' => array(
                        'amount' => '5.00',
                        'currency' => 'SEK'
                    ),
                    'total_price_ex_vat' => array(
                        'amount' => '20.00',
                        'currency' => 'SEK'
                    ),
                    'total_price_incl_vat' => array(
                        'amount' => '25.00',
                        'currency' => 'SEK'
                    ),
                    'total_vat' => array(
                        'amount' => '25.00',
                        'currency' => 'SEK'
                    )
                )
            );

    }

}