<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://developer.wasakredit.se
 * @since      1.0.0
 *
 * @package    Wasa_Kredit_Checkout
 * @subpackage Wasa_Kredit_Checkout/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wasa_Kredit_Checkout
 * @subpackage Wasa_Kredit_Checkout/includes
 * @author     Wasa Kredit <ehandel@wasakredit.se>
 */
class Wasa_Kredit_Checkout_i18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'wasa-kredit-checkout',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
