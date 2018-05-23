# Wasa Kredit Checkout

Wasa Kredit Checkout provides a payment solution where you can pay through their services.

## Description

Wasa Kredit Checkout provides a payment solution where you can pay through their services.

### Checkout

The checkout can be turned on or off in settings page.
The order of this payment provider can be changed in WooCommerce > Settings > Checkout.
The title and description of this payment provider can be changed in WooCommerce > Settings > Checkout > Wasa Kredit.

### Show financing on product lists

Turned on by detault but can be turned off in settings page.

You can also add this by adding the shortcode `[wasa_kredit_list_widget]`.

### Short code for specific product

You can show financing for a specific product with the following shortcode:

`<?php echo do_shortcode("[wasa_kredit_product_widget]") ?>`

Or

`[wasa_kredit_product_widget]`

# Installation

1. Upload plugin folder to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go WooCommerce Settings –> Payment Gateways and configure your Wasa Kredit settings.

# Behind the scenes

## Structure

* ./php-checkout-sdk: The PHP SDK for talking to the Wasa Kredit SDK.
* ./wasa-kredit-checkout.php: Handling plugin stuff, activation/deactivation and such.
* ./includes/class-wasa-kredit-checkout.php: Defines the plugin, ability to add custom CSS and JS for frontend and backend, includes needed files.
* ./includes/class-wasa-kredit-checkout-payment-gateway.php: Handles settings page, adds the payment provider to checkout, controls when it should be shown and not. Redirects users to checkout.
* ./templates/checkout.php: Is the Wasa Checkout page, the one after you choose payment gateway. Gets all needed data and gets a checkout from the PHP SDK.
* ./includes/class-wasa-kredit-checkout-api.php: Adds endpoints for the API to access / update orders.
* ./includes/class-wasa-kredit-list-products.php: Adds a financing price info under the ordinary price if the settings say so.
* ./includes/class-wasa-checkout-shortcodes.php: Adds the ability to show specific product widget with a shortcode.
* ./includes/class-wasa-kredit-checkout-page.php: Controls the page for the checkout, redirects to our template.

## Translations

The plugin uses `__("Default name", "wasa-kredit-checkout")` to make it possible to translate it.
For now it is translated to Swedish. To translate it to other languages we recommend Loco Translate (a plugin for Wordpress).

## Checkout

The checkout is a pretty standard WooCommerce Payment Gateway. You can find documentation here:
https://docs.woocommerce.com/document/payment-gateway-api/

A special page for the checkout is automatically generated and if the user accesses this page it will
always use the template in ./templates/checkout.php. This is a way for give the administrator the ability
to add information or content on top of the checkout page.

