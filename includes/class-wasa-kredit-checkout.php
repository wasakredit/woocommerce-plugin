<?php
if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.starrepublic.com
 * @since      1.0.0
 *
 * @package    Wasa_Kredit_Checkout
 * @subpackage Wasa_Kredit_Checkout/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wasa_Kredit_Checkout
 * @subpackage Wasa_Kredit_Checkout/includes
 * @author     Star Republic AB <support@starrepublic.com>
 */
class Wasa_Kredit_Checkout
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Wasa_Kredit_Checkout_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('PLUGIN_NAME_VERSION')) {
            $this->version = PLUGIN_NAME_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'wasa-kredit-checkout';

        $this->load_dependencies();
        $this->setup_shortcodes();
        $this->setup_product_list_prices();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->create_checkout_page();

        add_filter('page_template', array($this, 'checkout_template_override'));
    }

    function checkout_template_override($page_template)
    {
        $checkout_page = get_page_by_title('Wasa Kredit Checkout');
        
        if (is_page($checkout_page)) {
            $page_template = dirname(__FILE__) . '/../templates/checkout-page.php';
        }

        return $page_template;
    }

    public function create_checkout_page() {
        // This page is used with the template ./templates/checkout-page.php to give the
        // the ability to edit text above and the checkout

        if (is_admin()) {
            return;
        }

        if (get_page_by_title('Wasa Kredit Checkout') == NULL) {
            global $user_ID;

            $new_page = array(
                'post_title' => 'Wasa Kredit Checkout',
                'post_content' => 'Please fill out the forms in the Wasa Kredit Checkout and follow the wizard.',
                'post_status' => 'publish',
                'post_date' => date('Y-m-d H:i:s'),
                'post_author' => $user_ID,
                'post_type' => 'page'
            );

            $page_id = wp_insert_post($new_page);
        }
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Wasa_Kredit_Checkout_Loader. Orchestrates the hooks of the plugin.
     * - Wasa_Kredit_Checkout_i18n. Defines internationalization functionality.
     * - Wasa_Kredit_Checkout_Admin. Defines all hooks for the admin area.
     * - Wasa_Kredit_Checkout_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            'includes/class-wasa-kredit-checkout-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            'includes/class-wasa-kredit-checkout-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            'admin/class-wasa-kredit-checkout-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            'public/class-wasa-kredit-checkout-public.php';

        require_once plugin_dir_path(dirname(__FILE__)) .
            'includes/class-wasa-kredit-checkout-payment-gateway.php';

        require_once plugin_dir_path(dirname(__FILE__)) .
            'includes/class-wasa-kredit-checkout-shortcodes.php';

        require_once plugin_dir_path(dirname(__FILE__)) .
            'includes/class-wasa-kredit-checkout-list-product-prices.php';

        $this->loader = new Wasa_Kredit_Checkout_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Wasa_Kredit_Checkout_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Wasa_Kredit_Checkout_i18n();

        $this->loader->add_action(
            'plugins_loaded',
            $plugin_i18n,
            'load_plugin_textdomain'
        );
    }

    private function setup_shortcodes()
    {
        $shortcodes = new Wasa_Kredit_Checkout_Shortcodes();
    }

    private function setup_product_list_prices()
    {
        $product_prices = new Wasa_Kredit_Checkout_List_Product_Prices();
    }

    private function load_payment_gateway()
    {
        $plugin_settings = new Wasa_Kredit_Checkout_Payment_Gateway();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
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
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
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
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Wasa_Kredit_Checkout_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
