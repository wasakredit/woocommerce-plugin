<?php
if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly
}

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.starrepublic.com
 * @since             1.0.0
 * @package           Wasa_Kredit_Checkout
 *
 * @wordpress-plugin
 * Plugin Name:       Wasa Kredit Checkout
 * Plugin URI:        https://www.github.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Star Republic AB
 * Author URI:        https://www.starrepublic.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wasa-kredit-checkout
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die();
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WASA_KREDIT_CHECKOUT_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wasa-kredit-checkout-activator.php
 */
function activate_wasa_kredit_checkout()
{
    require_once plugin_dir_path(__FILE__) .
        'includes/class-wasa-kredit-checkout-activator.php';
    Wasa_Kredit_Checkout_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wasa-kredit-checkout-deactivator.php
 */
function deactivate_wasa_kredit_checkout()
{
    require_once plugin_dir_path(__FILE__) .
        'includes/class-wasa-kredit-checkout-deactivator.php';
    Wasa_Kredit_Checkout_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wasa_kredit_checkout');
register_deactivation_hook(__FILE__, 'deactivate_wasa_kredit_checkout');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wasa-kredit-checkout.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wasa_kredit_checkout()
{
    $plugin = new Wasa_Kredit_Checkout();
    $plugin->run();
}
run_wasa_kredit_checkout();