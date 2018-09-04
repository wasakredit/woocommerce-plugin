# Wasa Kredit Client PHP SDK v2.5

**Table of Content**

* [Change log](#change_log)
* [Available methods](#available_methods)
  * [Calculate Monthly Cost](#calculate_monthly_cost)
  * [Create Checkout](#create_checkout)
  * [Validate Financed Amount](#validate_financed_amount)
  * [Get Monthly Cost Widget](#get_monthly_cost_widget)
  * [Get Order](#get_order)  
  * [Get Order status](#get_order_status)
  * [Update Order status](#update_order_status)
  * [Add Order Reference](#add_order_reference)
  * [Get Payment Methods](#get_payment_methods)
* [Handling the Response](#handling_the_response)

## <a name="change_log"></a>Change log

### What's new in 2.5

The *Create Product Widget* has got a replacement function called *Get Monthly Cost Widget*, which will result in a more clean and simple look that is easier to style to your needs.

### What's new in 2.4

Minor update with respect to a missing semicolon.

### What's new in 2.3

Added new operation "Get Payment Methods". This can be used for a more detailed description of the Wasa Kredit Checkout payment method.  
Resolve an issue with session scoping when running multiple partners on the same session/domain.

### What's new in v2.2

New generic operation names matching api changes.  
Added tracking of SDK and Plugin version to header of API calls.  

### What's new in v2.1

Added support for the missing endpoints *Get Order Status* and *Update Order Status*.
Also documentation for *Add Order Reference* is now in place.

### What's new in v2.0

Custom callbacks on events from the Checkout has been introduced.  
The snippet from create_checkout will no longer auto initialize the checkout.  
This to allow the integrating partner to be able to handle the callbacks manually.

## Getting Started

This documentation is about the PHP SDK for communicating with Wasa Kredit checkout services.

### Prerequisites

  * Partner credentials
  * PHP 5.7 or above

### Acquiring the SDK

You can apply to receive Partner credentials by sending a mail to [ehandel@wasakredit.se](mailto:ehandel@wasakredit.se).

### Initialization

Initialize the main *Client* class by passing in your issued *Client ID* and *Client Secret*.
You can optionally supply a *Test Mode* parameter which is by default is set to true.

```
 /**
  * Checks if the input is valid JSON or not   
  * @param   string       clientId
  * @param   string       clientSecret
  * @param   boolean      testMode
  *
  * @return Client
  */  

new Client({CLIENT ID}, {CLIENT SECRET}, {TEST MODE})
```

### Client

Orchestrates the main flow. *Client* will fetch and store an access token upon an initial request and save it in a PHP session for future requests.

#### Example

```
$this->_client = new Client(clientId, clientSecret, testMode);
```

#### Parameters

| Name | Type | Description |
|---|---|---|
| clientId | *string* (required) | The client id that has been issued by Wasa Kredit |
| clientSecret | *string* (required) | Your client secret issued by Wasa Kredit |
| testMode | *boolean* | A boolean value if SDK should make requests in test mode or not |

## <a name="available_methods"></a>Available methods

### <a name="calculate_monthly_cost"></a>Calculate Monthly Cost

When presenting a product list view this method calculates the monthly price for each of the products.

```
public function calculate_monthly_cost({ITEMS})
```

#### Parameters

| Name | Type | Description |
|---|---|---|
| items | *array[**Item**]* (required) | An array containing the data type **Item** |

##### Item

| Name | Type | Description |
|---|---|---|
| financed_price | *Price* (required) | ... |
| product_id | *string* (required) | Your unique product identifier |

##### Price

| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | A string value that will be parsed to a decimal, e.g. 199 is '199.00' |
| currency | *string* (required) | The currency |

#### Example usage:

```
$payload = array(
  'items' => array(
    [0] => array(
      'financed_price' => array(
        'amount' => '14995.00',
        'currency' => 'SEK'
      ),
      'product_id' => '12345'
    )
  )
);

$response = $this->_client->calculate_monthly_cost($payload);
```

#### Response

| Name | Type | Description |
|---|---|---|
| monthly_costs | *array[**Response Item**]* | An array containing the data type **Response Item** |

##### Response Item

| Name | Type | Description |
|---|---|---|
| monthly_cost | *Price* | |
| product_id | *string* | Your unique product identifier |

##### Price

| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | A string value that will be parsed to a decimal, e.g. 199 is '199.00' |
| currency | *string* | The currency |


#### Example response:

```
$response->data

{
  'monthly_costs': [
    {
      'monthly_cost': {
        'amount': '1152.00',
        'currency': 'SEK'
      },
      'product_id': '12345'
    }
  ]
}
```

### <a name="create_checkout"></a>Create Checkout

The Checkout is inserted as a Payment Method in the checkout. It could be used either with or without input fields for address. Post the cart to Create Checkout to initiate the checkout.

An alternative use case for the Checkout is as a complete checkout if there is no need for other payment methods.

```
public function create_checkout({CHECKOUT})
```

#### Parameters

| Name | Type | Description |
|---|---|---|
| payment_types | *string* | Selected payment type to use in the checkout, e.g. 'leasing' |
| order_references | *array* | The order reference of the partner | A list containing order references. |
| cart_items | *array[Cart Item]* (required) | An array of the items in the cart as Cart Item objects |
| shipping_cost_ex_vat | *Price* (required) | Price object containing the shipping cost excluding VAT |
| customer_organization_number | *string* | Optional customer organization number |
| purchaser_name | *string* | Optional name of the purchaser |
| purchaser_email | *string* | Optional e-mail of the purchaser |
| purchaser_phone | *string* | Optional phone number of the purchaser |
| billing_address | *Address* | Optional Address object containing the billing address |
| delivery_address | *Address* | Optional Address object containing the delivery address |
| recipient_name | *string* | Optional name of the recipient |
| recipient_phone | *string* | Optional phone number of the recipient |
| request_domain | *string* (required)| The domain of the partner, used to allow CORS |
| confirmation_callback_url | *string* (required) | Url to the partner's confirmation page |
| ping_url | *string* (required) | Receiver url for order status changes notifications |

##### Cart Item

| Name | Type | Description |
|---|---|---|
| product_id | *string* (required) | Id of the Product |
| product_name | *string* (required) | Name of the product |
| price_ex_vat | *Price* (required) | Price object containing the price of the product excluding VAT |
| quantity | *int* (required) | Quantity of the product |
| vat_percentage | *string* (required) | VAT percentage as a parsable string, e.g. '25' is 25%  |
| vat_amount | *Price* (required) | Price object containing the calculated VAT of the product |
| image_url | *string* | An optional image url of the product |

##### Price

| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | A string value that will be parsed to a decimal, e.g. 199 is '199.00' |
| currency | *string* (required) | The currency |

##### Address

| Name | Type | Description |
|---|---|---|
| company_name | *string* | Company name |
| street_address | *string* | Street address |
| postal_code | *string* | Postal code |
| city | *string* | City |
| country | *string* | Country |


#### Response

The response will return a unique html snippet to be embedded in your checkout html.

| Name | Type | Description |
|---|---|---|
| HtmlSnippet | *string* | The checkout snippet for embedding. |

#### Example usage:

```
$payload = array(
  'order_references' => array(
    [0] => array(
      'key' => 'magento_quote_id',
      'value' => $orderId
    )
  ),  
  'cart_items' => array(
    array(
      'product_id' => 'ez-3000b-1',
      'product_name' =>'Kylskåp EZ3',
      'price_ex_vat' => array(
        'amount' => '11996.00',
        'currency' => 'SEK'
      ),
      'quantity' => 1,
      'vat_percentage' => '25',
      'vat_amount' => array(
        'amount' => '2999.00',
        'currency' => 'SEK'
      )
    )
  ),
  'shipping_cost_ex_vat' => array(
    'amount' => '448.00',
    'currency' => 'SEK'
  ),
  'request_domain' => 'https://YOUR-BASE-DOMAIN/',
  'confirmation_callback_url' => 'https://YOUR-BASE-DOMAIN/payment-callback/',
  'ping_url' => 'https://YOUR-BASE-DOMAIN/ping-callback/'
);           

$response = $this->_client->create_checkout($payload);
```

##### Initialize checkout

After creating a Wasa Kredit Checkout by calling the `create_checkout` function and embedding the resulting html snippet in your web page, as described above, the checkout html snippet needs to be explicitly initialized through a javascript call to the global `window.wasaCheckout.init()` function. The `init` method call will populate the \<div\> contained in the html snippet and link it to an internal iframe.

```javascript
<script>
    window.wasaCheckout.init();
</script>
```

##### <a name="handle_custom_callbacks"></a>Handling custom checkout callbacks

Optionally, you're able to pass an options object to the `init`-function. Use this if you want to manually handle the onComplete, onRedirect and onCancel events.

```javascript
<script>
    var options = {
      onComplete: function(orderReferences){
        //[...]
      },
      onRedirect: function(orderReferences){
        //[...]
      },
      onCancel: function(orderReferences){
        //[...]
      }
    };   
    window.wasaCheckout.init(options);
</script>
```

The `onComplete` event will be raised when a User has completed the checkout process. We recommend that you convert your cart/checkout to an order here if you haven't done it already.

The `onRedirect` event will be raised the user clicks the "back to store/proceed"-button. The default behaviour will redirect the user to the `confirmation_callback_url` passed into the `create_checkout`-function.

The `onCancel` event will be raised if the checkout process is canceled by the user or Wasa Kredit.

All callback functions will get the `orderReferences` parameter passed from the checkout. This parameter consists of an Array of `KeyValue` objects.
These are the same values as the ones that was passed to the `create_checkout`-function as the `order_references` property.

```javascript
orderReferences = [
  { key: "partner_checkout_id", value: "900123" },
  { key: "partner_reserved_order_number", value: "123456" }
];    
```

### <a name="validate_financed_amount"></a>Validate Financed Amount

Validates that the amount is within the min/max financing amount for the partner.

```
public function validate_financed_amount($amount)
```

#### Parameters

| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | The amount excluding VAT to be validated as a string, e.g. 199 is '199.00' |

#### Example usage:

```
$amount = '14995.00';

$response = $this->_client->validate_financed_amount($amount);
```

#### Response

| Name | Type | Description |
|---|---|---|
| validation_result | *boolean* | Amount sent is between min/max limit for the partner |

```
$response->data

{
  "validation_result": true
}
```

### <a name="get_monthly_cost_widget"></a>Get Monthly Cost Widget

To inform the customer about Wasa Kredit financing as a Payment Method the Monthly Cost Widget should be displayed close to the price information on the product details page.

```
public function get_monthly_cost_widget($amount)
```

#### Parameters

| Name | Type | Description |
|---|---|---|
| amount | *string* (required) | A string value that will be parsed to a decimal, e.g. 199 is '199.00' |

#### Example usage:

```
$response = $this->_client->get_monthly_cost_widget('10275.00');
```

#### Response

The response will return a unique html snippet to be embedded in your product view html.

```
$response->data

   "<div> ... </div>"
```

### <a name="get_order"></a>Get Order

#### Parameters

| Name | Type | Description |
|---|---|---|
| order_id | *string* (required) | The id of the desired order object |

#### Example usage:

```
$orderId = 'f404e318-7180-47ab-91db-fbb66addf577'
$response = $this->_client->get_order($orderId);
```

#### Response

```
{
  "customer_organization_number": "222222-2222",
  "delivery_address": {
    "company_name": "Star Republic",
    "street_address": "Ekelundsgatan 9",
    "postal_code": "41118",
    "city": "Gothenburg",
    "country": "Sweden"
  },
  "billing_address": {
    "company_name": "Star Republic",
    "street_address": "Ekelundsgatan 9",
    "postal_code": "41118",
    "city": "Gothenburg",
    "country": "Sweden"
  },
  "order_references": [
    {
      "key": "partner_order_number",
      "value": "123456"
    }
  ],
  "purchaser_email": "anders.svensson@starrepublic.com",
  "recipient_name": "Anders Svensson",
  "recipient_phone": "070-1234567",
  "status": {
    "status": "shipped"
  },
  "cart_items": [
    {
      "product_id": "ez-41239b",
      "product_name": "Kylskåp EZ3",
      "price_ex_vat": {
        "amount": "14995.50",
        "currency": "SEK"
      },
      "quantity": 1,
      "vat_percentage": "25",
      "vat_amount": {
        "amount": "14995.50",
        "currency": "SEK"
      },
      "image_url": "https://unsplash.it/500/500"
    }
  ]
}
```

### <a name="get_order_status"></a>Get Order Status

Gets the current status of a Wasa Kredit order.

When an order status change notification is received. This method may be called to check the current status of the order.

###### <a name="order_statuses">Possible order statuses</a>

* **initialized** - The order has been created but the order agreement has not yet been signed by the customer.
* **canceled** - The purchase was not approved by Wasa Kredit or it has been canceled by you as a partner. If you have created an order in your system it can safely be deleted.
* **pending** - The checkout has been completed and a customer has signed the order agreement, but additional signees is required or the order has not yet been fully approved by Wasa Kredit.
* **ready\_to\_ship** - All necessary signees have signed the order agreement and the order has been fully approved by Wasa Kredit. The order must now be shipped to the customer before Wasa Kredit will issue the payment to you as a partner.
* **shipped** - This status is set by the partner when the order item(s) have been shipped to the customer.

#### Parameters

| Name | Type | Description |
|---|---|---|
| order_id | *string* (required) | The id of the desired order object |

#### Example usage:

```
$orderId = 'f404e318-7180-47ab-91db-fbb66addf577';
$response = $this->_client->get_order_status($orderId);
```

#### Response

```
{
  "status": "shipped"
}
```


### <a name="update_order_status"></a>Update Order Status

Changes the status of the Wasa Kredit order. This method should be used to update the Wasa Kredit order status if you have shipped or canceled the order. Thus it is only possible to set the status to "canceled" or "shipped". The status can only be set to "canceled" if it has not already been shipped or completed and to "shipped" if its current status is "ready_to_ship."

##### Order statuses

* canceled - The order has been canceled for some reason.
* shipped - The order has been shipped to the customer.


#### Parameters

| Name | Type | Description |
|---|---|---|
| order_id | *string* (required) | The id of the order object          |
| status   | *string* (required) | The status to update the order with |

#### Example usage:

```
$orderId = 'f404e318-7180-47ab-91db-fbb66addf577';
$orderStatus = 'shipped';
$response = $this->_client->update_order_status($orderId, $orderStatus);
```

#### Response

```
{
  "status": "shipped"
}
```

### <a name="add_order_reference"></a>Add Order Reference

Adds a new order reference and appends it to the current order references of the order. The purpose of supporting multiple order references for a single order is to provide generic support for e-commerce platforms and solutions that use multiple references in their purchase and order flow.

#### Parameters

| Name | Type | Description |
|---|---|---|
| order_id         | *string* (required)         | The id of the order object                            |
| order_reference  | *OrderReference*-object (required) | The order reference that should be added to the order |

##### OrderReference

| Name | Type | Description |
|---|---|---|
| key   | *string* (required) | The key of the order reference   |
| value | *string* (required) | The value of the order reference |


#### Example usage:

```
$orderId = 'f404e318-7180-47ab-91db-fbb66addf577';
$orderReference = array(
                    'key' => 'Quote',
                    'value' => '0b5e6f69-20ac-45b1-93d5-2c1be556488b'
                  );

$response = $this->_client->add_order_reference($orderId, $orderReference);
```

#### Response

```
{
  "status": "shipped"
}
```
### <a name="get_payment_methods"></a>Get Payment Methods

Get information from Wasa Kredit about the different payment options available in the checkout. This can be used to compose a description of which options are available in the checkout before it's loaded.

#### Parameters
| Name | Type | Description |
|---|---|---|
| total_amount   | *string* (required) | A string value that will be parsed to a decimal, e.g. 19999 is '19999.00' |
| currency | *string* (required) | The currency |

#### Example usage:

```
$total_amount = "10000.00";
$currency = "SEK";

$response = $this->_client->get_payment_methods($total_amount, $currency);
```

#### Response

```
{
  "payment_methods": [
    {
      "id": "leasing",
      "display_name": "Leasing",
      "options": {
        "default_contract_length": 24,
        "contract_lengths": [
          {
            "contract_length": 12,
            "monthly_cost": {
              "amount": "802",
              "currency": "SEK"
            }
          },
          {
            "contract_length": 24,
            "monthly_cost": {
              "amount": "442",
              "currency": "SEK"
            }
          },
          {
            "contract_length": 36,
            "monthly_cost": {
              "amount": "321",
              "currency": "SEK"
            }
          }
        ]
      }
    }
  ]
}

```

## <a name="handling_the_response"></a>Handling the Response

We are using a Response class when passing information through the SDK.

###  Properties

| Name | Type | Description |
|---|---|---|
| statusCode | *string* | Http status code of the response |
| data | *string* | Contains the body of the response |
| error | *string* | Error code passed from the API |
| errorMessage | *string* | Developer error message |
| curlError | *string* | Curl error message |


## Running the tests

```
php vendor/bin/phpunit
```
