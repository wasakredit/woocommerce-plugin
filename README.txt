=== Wasa Kredit Checkout ===
Contributors: aos06wasakredit
Donate link: https://developer.wasakredit.se
Tags: woocommerce, ecommerce, e-commerce, checkout
Requires at least: 4.0.0
Tested up to: 6.4.2
Requires PHP: 7.2
WC requires at least: 5.0.0
WC tested up to: 8.4.0
Stable tag: 2.5.8
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

= Does the plugin work with other checkout providers than the default? =

As of version 1.2.2 of Wasa Checkout for Woo commerce, there is a solution to the problem of working in parallel with other checkout providers taking over the checkout process.
In the settings for Wasa's checkout, there are two new settings with the heading Advanced. When you activate the first one, a banner will be placed on the second supplier's checkout with a link to Woo-commerce's standard checkout. There is Wasa as a payment option together with other payment methods that are activated.
1. Click on "Enable widget for redirection to regular checkout" or in swedish "Aktivera widget för omdirigering till ordinarie checkout".
2. In the text field below, enter the path to the default checkout. For example, in my checkout, it is the "checkout". If it is not the same for you, you can easily get it by activating the standard checkout and copying the uri. Dy activates it at Settings> Advanced> Page Settings> Checkout Page> "Checkout".

== Changelog ==
= 2024-03-12    - version 2.5.8 =
* Fix           - Fixed a critical occur that would occur where leasing would still be shown as an available payment option although the minimum order amount was not met. This issue only happened if the cart contained a coupon.

= 2024-01-23    - version 2.5.7 =
* Fix           - Fixed an issue related to the monthly cost widget that would happen when switching to an unsupported currency.

= 2023-12-20    - version 2.5.6 =
* Fix           - Solves issue with monthly cost not displayed correctly for variations in some cases.

= 2023-11-28    - version 2.5.5 =
* Tweak         - You can now enable leasing and invoicing separately.
* Fix           - The monthly price widget should now update to reflect a change in variable product and quantity.
* Fix           - Fixed an issue related to discounts and coupons with leasing.
* Fix           - Whitespace in the phone number should no longer cause the phone validation to fail. There is still an upper limit of 15 characters.

= 2023-10-09    - version 2.5.4 =
* Fix           - Fixed a critical error that sometimes happened when attempting to show an error notice.

= 2023-07-24    - version 2.5.3 =
* Fix           - Leasing should now work expected. Prior to this fix, an incorrect template was being used, causing the invoice template to be displayed instead.

= 2023-07-12    - version 2.5.2 =
* Fix           - Removed an action that references an undefined method which would cause a fatal error.

= 2023-06-28    - version 2.5.1 =
* Tweak         - The checkout page has been modified to replace the order receipt with the payment form.

= 2023-06-12    - version 2.5.0 =
* Feature       - The customer will now be redirected to a new checkout page that is more compatible with the store's theming and styling.
* Fix           - Fixed an undefined index notice that happened due to missing a default value.

= 2023-05-04    - version 2.4.0 =
* Feature       - Added a setting to let you change the placement of the monthly cost widget on the product page or attach it onto a custom hook.
* Feature       - You can now customize the payment gateways' title and description as shown to the customer.

= 2022-12-19    - version 2.3.4 =
* Fix           - Improve calculate monthly cost logic in checkout page.

= 2022-12-14    - version 2.3.3 =
* Feature       - Adds setting for enable/disable order management.
* Tweak         - Send WooCommerce order number to Wasa Kredit as reference for orders.
* Tweak         - CSS file tweak.
* Fix           - Error handling improvement. Fix undefined index, array to string conversion.
* Fix           - Fix 404 error that could happen in some stores when loading plugin template files.
* Fix           - Use wc_format_decimal instead of number_format in monthly cost widget calculation to avoid possible issues.

= 2022-06-14    - version 2.3.2 =
* Fix           - Logging improvement. Check that current_priority method_exists. Avoids potential issue with Rank math plugin.

= 2022-04-25    - version 2.3.1 =
* Fix           - Solve issue with monthly cost widget popup couldn't be opened when clicked.

= 2022-04-20    - version 2.3.0 =
* Feature       - Adds payment gateway icon.
* Tweak         - Adds logic to determine if Leasing payment method should be displayed as Wasa Kredit Leasing or Wasa Kredit Rental.
* Tweak         - Change payment method titles (to Wasa Kredit Leasing, Wasa Kredit Rental & Wasa Kredit Invoice).
* Tweak         - Change/tweak payment method descriptions.
* Tweak         - Updates Swedish translation.

= 2022-04-06    - version 2.2.1 =
* Tweak         - Escape monthly cost widget html.
* Tweak         - Escape checkout iframe html.
* Tweak         - PHPCS changes.

= 2022-03-24    - version 2.2.0 =
* Enhancement   - Use WP http api instead of external SDK to communicate with Wasa Kredit.
* Tweak         - PHPCS changes.

= 2022-03-09    - version 2.1.4 =
* Tweak         - PHPCS changes.

= 2022-02-21    - version 2.1.3 =
* Tweak         - Readme updates.

= 2022-02-07    - version 2.1.2 =
* Tweak         - Readme updates.

= 2022-01-28    - version 2.1.1 =
* Fix           - Avoid potential error when displaying monthly cost widget in product list, if no product object is found.

= 2021-12-07    - version 2.1.0 =
* Feature       - Adds support for different widget formats when displaying monthly cost widget.
* Feature       - Adds lower threshold setting to be able to control when the monthly cost widget should be displayed.
* Tweak         - Avoid floating precision errors. Change from round to number_format for prices sent to Wasa Kredit.
* Tweak         - Remove deprecated Advanced settings (redirect to standard checkout).
* Fix           - Don't try to make cancel or activate request to Wasa Kredit if the order hasn't been paid for.

= 2021-09-28    - version 2.0.1 =
* Tweak         - Adds logging to cancel_order & ship_order requests.
* Tweak         - Bumped required PHP version to 7.0.
* Fixed         - Use return instead of echo when printing wasa_kredit_product_widget shortcode.


= 2021-06-01    - version 2.0.0 =
* Support for new v4 api.
* Plugin now supports invoice payments.
* Adds test environment endpoints + settings to add test mode merchant credentials.
* Fixed an issue where status updates would fail to change status of woocommerce orders if received during checkout.
* Removes monthly cost price printed together with payment method name in WooCommerce order.
* Adds request logging via WooCommerce logger (plus setting to turn it on/off).

= 1.2.11 =
* Fix stopped showing throwing error when leasing price cannot be displayed

= 1.2.10 =
* Fixed missing leasing price in woocommerce shortcode [products]

= 1.2.9 =
* Fixed incorrect order status-pingback handling. 

= 1.2.8 =
* Improved product listing performance.

= 1.2.7 =
* Fix incorrect shipping cost calculations

= 1.2.6 =
* Fix problem with null reference when navigating certain admin pages
* Secure possible rounding issue when create_checkout is called with to many decimals.

= 1.2.5 =
* Fix a rounding issue with product list widget, when sending to many decimals to the api
* Fix a possible rounding issue in checkout by preventing to many decimals in api call when displaying possible financing options

= 1.2.4 =
* Fix of shortcode for product widget.

= 1.2.3 =
* Replace Monthly cost widget with updated look and feel

= 1.2.2.1 =
* Fixed issue with TAX not being fetched properly
* Fixed Php-notice when using new redirect widget.

= 1.2.2 =
* Added redirect to standard checkout widget
* Added admin settings for new redirect widget

= 1.2.1 =
* The client_secret field is now able to contain special characters in the database
* Added missing domain to translation texts

= 1.2 =
* The plugin is based on Wasa Kredit PHP SDK v2.4.

= 1.1 =
* The plugin is based on Wasa Kredit PHP SDK v2.3.
* Description now displays possible financing options.
* Added integration for WooCommerce product Add-Ons.

= 1.0 =
* First version of the Wasa Kredit Checkout plugin.
* The plugin is based on Wasa Kredit PHP SDK v2.2.


== Upgrade Notice ==

= 1.2 =
The description now displays possible financing options.
