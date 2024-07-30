<?php // phpcs:ignore WordPress.NamingConventions.ValidFileName
if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://developer.wasakredit.se/
 * @since             1.0.0
 * @package           Wasa_Kredit_Checkout
 *
 * @wordpress-plugin
 * Plugin Name:       Wasa Kredit Checkout
 * Plugin URI:        https://github.com/wasakredit/woocommerse-plugin
 * Description:       Wasa Kredit Checkout offers financing as a payment method for B2B.
 * Author:            Wasa Kredit
 * Version:           2.6.0

 * Author URI:        https://developer.wasakredit.se
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wasa-kredit-checkout
 * Domain Path:       /languages
 *
 * WC requires at least: 5.0.0
 * WC tested up to: 8.4.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

define( 'WASA_KREDIT_CHECKOUT_VERSION', '2.6.0' );
define( 'WASA_KREDIT_CHECKOUT_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WASA_KREDIT_CHECKOUT_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );


if ( ! class_exists( 'Wasa_Kredit_Checkout' ) ) {
	/**
	 * Class Wasa_Kredit_Checkout
	 */
	class Wasa_Kredit_Checkout {

		/**
		 * Loader.
		 *
		 * @var $loader
		 */
		protected $loader;

		/**
		 * Plugin name.
		 *
		 * @var $loplugin_nameader
		 */
		protected $plugin_name;

		/**
		 * Version.
		 *
		 * @var $version
		 */
		protected $version;

		/**
		 * The reference the *Singleton* instance of this class.
		 *
		 * @var $instance
		 */
		protected static $instance;


		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return self::$instance The *Singleton* instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Private clone method to prevent cloning of the instance of the
		 * *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}

		/**
		 * Public unserialize method to prevent unserializing of the *Singleton*
		 * instance.
		 *
		 * @return void
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}

		/**
		 * Protected constructor to prevent creating a new instance of the
		 * *Singleton* via the `new` operator from outside of this class.
		 */
		protected function __construct() {

			$this->version     = WASA_KREDIT_CHECKOUT_VERSION;
			$this->plugin_name = 'wasa-kredit-checkout';

			add_action( 'plugins_loaded', array( $this, 'init' ) );

		}

		/**
		 * Init the plugin after plugins_loaded so environment variables are set.
		 */
		public function init() {

			if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
				return;
			}

			load_plugin_textdomain( 'wasa-kredit-checkout', false, plugin_basename( __DIR__ ) . '/languages' );
			add_filter( 'plugin_action_links_wasa-kredit-checkout/wasa-kredit-checkout.php', array( $this, 'plugin_add_settings_link' ) );

			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateways' ) );

			$this->include_files();

			// Support for custom admin CSS and JS.
			$this->define_admin_hooks();

			// Support for custom public CSS and JS.
			$this->define_public_hooks();

			// Load shortcodes, like [wasa_kredit_product_widget].
			$this->product_widget = new Wasa_Kredit_Checkout_Product_Widget();

			// Load monthly cost on product listings.
			$this->list_widget = new Wasa_Kredit_Checkout_List_Widget();

			$this->order_management = new Wasa_Kredit_Checkout_Order_Management();
			$this->api              = new Wasa_Kredit_Checkout_API();
			$this->run();
		}



		/**
		 * Add settings link to plugin list view
		 *
		 * @param array $links The plugin links.
		 *
		 * @since    1.0.0
		 */
		public function plugin_add_settings_link( $links ) {
			$settings_link = '<a href="admin.php?page=wc-settings&tab=checkout&section=wasa_kredit">' . __( 'Settings', 'wasa-kredit-checkout' ) . '</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}


		/**
		 * Includes the files for the plugin
		 *
		 * @return void
		 */
		public function include_files() {

			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/includes/class-wasa-kredit-checkout-loader.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/admin/class-wasa-kredit-checkout-admin.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/public/class-wasa-kredit-checkout-public.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/includes/class-wasa-kredit-checkout-payment-gateway.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/includes/class-wasa-kredit-invoice-checkout-payment-gateway.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/includes/class-wasa-kredit-checkout-product-widget.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/includes/class-wasa-kredit-checkout-list-widget.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/includes/class-wasa-kredit-logger.php';

			// Classes.
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/class-wasa-kredit-checkout-api.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/class-wasa-kredit-checkout-callbacks.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/class-wasa-kredit-checkout-order-management.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/class-wasa-kredit-checkout-ajax.php';

			// Includes.
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/includes/wasa-kredit-functions.php';

			// Request classes.
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/class-wasa-kredit-checkout-request.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/class-wasa-kredit-checkout-request-get.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/class-wasa-kredit-checkout-request-post.php';

			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/post/class-wasa-kredit-checkout-request-auth.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/post/class-wasa-kredit-checkout-request-add-order-reference.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/post/class-wasa-kredit-checkout-request-calculate-monthly-cost.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/post/class-wasa-kredit-checkout-request-cancel-order.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/post/class-wasa-kredit-checkout-request-ship-order.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/post/class-wasa-kredit-checkout-request-create-invoice-checkout.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/post/class-wasa-kredit-checkout-request-create-leasing-checkout.php';

			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/get/class-wasa-kredit-checkout-request-get-leasing-payment-options.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/get/class-wasa-kredit-checkout-request-get-monthly-cost-widget.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/get/class-wasa-kredit-checkout-request-get-order-status.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/get/class-wasa-kredit-checkout-request-get-order.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/get/class-wasa-kredit-checkout-request-get-payment-methods.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/get/class-wasa-kredit-checkout-request-validate-financed-invoice-amount.php';
			require_once WASA_KREDIT_CHECKOUT_PLUGIN_PATH . '/classes/requests/get/class-wasa-kredit-checkout-request-validate-financed-leasing-amount.php';

			add_action( 'before_woocommerce_init', array( $this, 'declare_wc_compatibility' ) );

			$this->loader = new Wasa_Kredit_Checkout_Loader();
		}

		/**
		 * Declare compatibility with WooCommerce features.
		 *
		 * @return void
		 */
		public function declare_wc_compatibility() {
			// Declare HPOS compatibility.
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}

		/**
		 * Define admin hooks.
		 *
		 * @return void
		 */
		private function define_admin_hooks() {
			// Add custom admin CSS and JS.
			$plugin_admin = new Wasa_Kredit_Checkout_Admin(
				$this->get_plugin_name(),
				$this->get_version()
			);

			$this->loader->add_action(
				'admin_enqueue_scripts',
				$plugin_admin,
				'enqueue_styles'
			);
			$this->loader->add_action(
				'admin_enqueue_scripts',
				$plugin_admin,
				'enqueue_scripts'
			);
		}

		/**
		 * Define public hooks.
		 *
		 * @return void
		 */
		private function define_public_hooks() {
			// Add custom public css and JS.
			$plugin_public = new Wasa_Kredit_Checkout_Public(
				$this->get_plugin_name(),
				$this->get_version()
			);

			$this->loader->add_action(
				'wp_enqueue_scripts',
				$plugin_public,
				'enqueue_styles'
			);
			$this->loader->add_action(
				'wp_enqueue_scripts',
				$plugin_public,
				'enqueue_scripts'
			);
		}

		/**
		 * Activate hooks.
		 */
		public function run() {
			$this->loader->run();
		}

		/**
		 * Get plugin name.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}

		/**
		 * Get plugin loader.
		 */
		public function get_loader() {
			return $this->loader;
		}

		/**
		 * Get version number.
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 *
		 * Adds new gateway.
		 *
		 * @param array $methods list of supported methods.
		 *
		 * @return array
		 */
		public function add_gateways( $methods ) {
			$methods[] = Wasa_Kredit_Checkout_Payment_Gateway::class;
			$methods[] = Wasa_Kredit_InvoiceCheckout_Payment_Gateway::class;
			return $methods;
		}


	}
	Wasa_Kredit_Checkout::get_instance();
}


/**
 * Main instance QOC WooCommerce.
 *
 * Returns the main instance of QOC.
 *
 * @return Qliro_One_For_WooCommerce
 */
function Wasa_Kredit_WC() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	return Wasa_Kredit_Checkout::get_instance();
}




