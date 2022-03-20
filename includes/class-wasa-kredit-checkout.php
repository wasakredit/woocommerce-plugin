<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Wasa_Kredit_Checkout {
	protected $loader;
	protected $plugin_name;
	protected $version;

	/**
	 * The reference the *Singleton* instance of this class.
	 *
	 * @var Qliro_One_For_WooCommerce $instance
	 */
	private static $instance;

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Qliro_One_For_WooCommerce The *Singleton* instance.
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
		wc_doing_it_wrong( __FUNCTION__, __( 'Nope', 'qliro-one-for-woocommerce' ), '1.0' );
	}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'Nope', 'qliro-one-for-woocommerce' ), '1.0' );
	}

	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'wasa-kredit-checkout';

		// Include important plugin files
		$this->load_dependencies();

		// Add support for translation
		$this->set_locale();

		// Support for custom admin CSS and JS
		$this->define_admin_hooks();

		// Support for custom public CSS and JS
		$this->define_public_hooks();

		// Load shortcodes, like [wasa_kredit_product_widget]
		$this->product_widget = new Wasa_Kredit_Checkout_Product_Widget();

		// Load monthly cost on product listings
		$this->list_widget = new Wasa_Kredit_Checkout_List_Widget();

		// Create checkout page if not exists, override templates
		$this->page = new Wasa_Kredit_Checkout_Page();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-wasa-kredit-checkout-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-wasa-kredit-checkout-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'admin/class-wasa-kredit-checkout-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'public/class-wasa-kredit-checkout-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-wasa-kredit-checkout-payment-gateway.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-wasa-kredit-invoice-checkout-payment-gateway.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-wasa-kredit-checkout-product-widget.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-wasa-kredit-checkout-list-widget.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-wasa-kredit-checkout-page.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-wasa-kredit-logger.php';

		// Classes.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/class-wasa-kredit-checkout-api.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/class-wasa-kredit-checkout-callbacks.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/class-wasa-kredit-checkout-order-management.php';

		// Includes.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wasa-kredit-functions.php';

		// Request classes.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/class-wasa-kredit-checkout-request.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/class-wasa-kredit-checkout-request-get.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/class-wasa-kredit-checkout-request-post.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/post/class-wasa-kredit-checkout-request-auth.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/post/class-wasa-kredit-checkout-request-add-order-reference.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/post/class-wasa-kredit-checkout-request-calculate-monthly-cost.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/post/class-wasa-kredit-checkout-request-cancel-order.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/post/class-wasa-kredit-checkout-request-ship-order.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/post/class-wasa-kredit-checkout-request-create-invoice-checkout.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/post/class-wasa-kredit-checkout-request-create-leasing-checkout.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/get/class-wasa-kredit-checkout-request-get-leasing-payment-options.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/get/class-wasa-kredit-checkout-request-get-monthly-cost-widget.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/get/class-wasa-kredit-checkout-request-get-order-status.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/get/class-wasa-kredit-checkout-request-get-order.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/get/class-wasa-kredit-checkout-request-get-payment-methods.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/get/class-wasa-kredit-checkout-request-validate-financed-invoice-amount.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'classes/requests/get/class-wasa-kredit-checkout-request-validate-financed-leasing-amount.php';

		$this->loader = new Wasa_Kredit_Checkout_Loader();

		$this->order_management = new Wasa_Kredit_Checkout_Order_Management();
		$this->api              = new Wasa_Kredit_Checkout_API();
	}

	private function set_locale() {
		$plugin_i18n = new Wasa_Kredit_Checkout_I18n();

		$this->loader->add_action(
			'plugins_loaded',
			$plugin_i18n,
			'load_plugin_textdomain'
		);
	}

	private function define_admin_hooks() {
		// Add custom admin CSS and JS
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

	private function define_public_hooks() {
		// Add custom public css and JS
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

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}
}
