<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Wasa_Kredit_Checkout {
	protected $loader;
	protected $plugin_name;
	protected $version;

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

		// Load API parts and callbacks
		$this->api = new Wasa_Kredit_Checkout_API();

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
			'includes/class-wasa-kredit-checkout-api.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-wasa-kredit-checkout-page.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) .
			'includes/class-wasa-kredit-checkout-sdk-helper.php';

		$this->loader = new Wasa_Kredit_Checkout_Loader();
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
