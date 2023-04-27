<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://developer.wasakredit.se
 * @since      1.0.0
 *
 * @package    Wasa_Kredit_Checkout
 * @subpackage Wasa_Kredit_Checkout/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wasa_Kredit_Checkout
 * @subpackage Wasa_Kredit_Checkout/admin
 * @author     Wasa Kredit <ehandel@wasakredit.se>
 */
class Wasa_Kredit_Checkout_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_filter( 'wasa_kredit_settings', array( $this, 'extend_settings' ), 10, 2 );

		add_filter( 'woocommerce_gateway_title', array( $this, 'custom_gateway_title' ), 10, 2 );
		add_filter( 'woocommerce_gateway_description', array( $this, 'custom_gateway_description' ), 10, 2 );
	}

	/**
	 * Used for adding additional settings that apply to all Wasa Kredit gateways.
	 *
	 * @param array $settings
	 * @return array
	 */
	public function extend_settings( $settings, $gateway_id ) {
		$extended_settings[ $gateway_id . '_custom_gateway_title' ] = array(
			'title'       => __( 'Title', 'wasa-kredit-checkout' ),
			'type'        => 'text',
			'description' => __( 'This controls the payment gateway <b>title</b> which the user sees during checkout.', 'wasa-kredit-checkout' ),
			'default'     => '',
			'placeholder' => 'Leave empty to use default.',
		);

		$extended_settings[ $gateway_id . '_custom_gateway_description' ] = array(
			'title'       => __( 'Description', 'wasa-kredit-checkout' ),
			'type'        => 'text',
			'description' => __( 'This controls the payment gateway <b>description</b> which the user sees during checkout.', 'wasa-kredit-checkout' ),
			'default'     => '',
			'placeholder' => 'Leave empty to use default.',
		);

		// Insert the extended settings after the first setting.
		$head = array_slice( $settings, 0, 1, true );
		$tail = array_slice( $settings, 1, null, true );
		return array_merge( $head, $extended_settings, $tail );
	}

	/**
	 * Used for overriding the gateway title.
	 *
	 * @param string $title
	 * @param string $gateway_id
	 * @return string
	 */
	public function custom_gateway_title( $title, $gateway_id ) {
		if ( false !== strpos( $gateway_id, 'wasa_kredit' ) ) {
			$settings = get_option( 'wasa_kredit_settings' );
			if ( ! empty( $settings[ $gateway_id . '_custom_gateway_title' ] ) ) {
				$title = $settings[ $gateway_id . '_custom_gateway_title' ];
			}
		}
		return $title;
	}

	/**
	 * Used for overriding the gateway description.
	 *
	 * @param string $description
	 * @param string $gateway_id
	 * @return string
	 */
	public function custom_gateway_description( $description, $gateway_id ) {
		if ( false !== strpos( $gateway_id, 'wasa_kredit' ) ) {
			$settings = get_option( 'wasa_kredit_settings' );
			if ( ! empty( $settings[ $gateway_id . '_custom_gateway_description' ] ) ) {
				$description = $settings[ $gateway_id . '_custom_gateway_description' ];
			}
		}
		return $description;
	}

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wasa_Kredit_Checkout_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wasa_Kredit_Checkout_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/wasa-kredit-checkout-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wasa_Kredit_Checkout_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wasa_Kredit_Checkout_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/wasa-kredit-checkout-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);
	}
}
