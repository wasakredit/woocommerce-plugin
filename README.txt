=== Wasa Kredit Checkout ===
Contributors: aos06wasakredit
Donate link: https://developer.wasakredit.se
Tags: woocommerce, ecommerce, e-commerce, checkout
Requires at least: 3.0.1
Tested up to: 4.9.6
Requires PHP: 5.7
Stable tag: 1.1
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
* The plugin is based on Wasa Kredit PHP SDK v2.3 
* Description now displays possible financing options.
* Added integration for WooCommerce product Add-Ons.

== Upgrade Notice ==

= 1.1 =
The description now displays possible financing options.