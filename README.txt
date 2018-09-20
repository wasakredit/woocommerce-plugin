=== Wasa Kredit Checkout ===
Contributors: aos06wasakredit
Donate link: https://developer.wasakredit.se
Tags: woocommerce, ecommerce, e-commerce, checkout
Requires at least: 3.0.1
Tested up to: 4.9.6
Requires PHP: 5.7
Stable tag: 1.2.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Wasa Kredit Checkout provides a payment solution where you can pay through their services.

== Description ==

Wasa Kredit Checkout provides a payment solution where you can pay through their services.

=== Checkout ===

The checkout can be turned on or off in settings page.
The order of this payment provider can be changed in WooCommerce > Settings > Checkout.
The title and description of this payment provider can be changed in WooCommerce > Settings > Checkout > Wasa Kredit.

=== Show financing on product lists ===

Turned on by detault but can be turned off in settings page.

You can also add this by adding the shortcode [wasa_kredit_list_widget].

=== Short code for specific product ===

You can show financing for a specific product with the following shortcode:

`<?php echo do_shortcode("[wasa_kredit_product_widget]") ?>`

Or

[wasa_kredit_product_widget]

== Installation ==

1. Upload plugin folder to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go WooCommerce Settings –> Payment Gateways and configure your Wasa Kredit settings.

== Frequently Asked Questions ==


== Changelog ==

= 1.0 =
* First version of the Wasa Kredit Checkout plugin.
* The plugin is based on Wasa Kredit PHP SDK v2.2.

= 1.1 =
* The plugin is based on Wasa Kredit PHP SDK v2.3.
* Description now displays possible financing options.
* Added integration for WooCommerce product Add-Ons.

= 1.2 =
* The plugin is based on Wasa Kredit PHP SDK v2.4.

= 1.2.1 =
* The client_secret field is now able to contain special characters in the database
* Added missing domain to translation texts

= 1.2.2 =
* Added redirect to standard checkout widget
* Added admin settings for new redirect widget

= 1.2.2.1 =
* Fixed issue with TAX not being fetched properly
* Fixed Php-notice when using new redirect widget

= 1.2.3 =
* Replace Monthly cost widget with updated look and feel

= 1.2.4 =
* Fix of shortcode for product widget

= 1.2.5 =
* Fix a rounding issue with product list widget, when sending to many decimals to the api
* Fix a possible rounding issue in checkout by preventing to many decimals in api call when displaying possible financing options

= 1.2.6 =
* Fix problem with null reference when navigating certain admin pages
* Secure possible rounding issue when create_checkout is called with to many decimals

== Upgrade Notice ==

= 1.2 =
The description now displays possible financing options.
