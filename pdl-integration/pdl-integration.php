<?php
/**
 * Plugin Name: PDL Integration
 * Plugin URI: https://yourpluginwebsite.com
 * Description: A plugin for Promise Delivery Ltd requiring WooCommerce.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: pdl
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * WC requires at least: 4.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Declare compatibility with WooCommerce features.
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

// Define plugin constants.
define('PDL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PDL_API_BASE_URL', 'https://promisedelivery.com.bd/api/');

// Include admin files.
require_once PDL_PLUGIN_DIR . 'admin/activations.php';
require_once PDL_PLUGIN_DIR . 'admin/helpers.php';
require_once PDL_PLUGIN_DIR . 'admin/pdl-wc-checkout.php';
if (!class_exists('PDL_Admin_Order')) {
    require_once PDL_PLUGIN_DIR . 'admin/pdl-admin-order.php';
}
// require_once PDL_PLUGIN_DIR . 'admin/pdl-admin-order.php';
require_once PDL_PLUGIN_DIR . 'admin/pdl-order-check.php';

// Include additional functionality.
require_once PDL_PLUGIN_DIR . 'includes/settings.php';
require_once PDL_PLUGIN_DIR . 'includes/area-mapping.php';
require_once PDL_PLUGIN_DIR . 'includes/test.php';

// Add admin menus and submenus.
add_action('admin_menu', 'pdl_add_admin_menu');
add_action('admin_menu', 'pdl_area_mapping_submenu');
add_action('admin_menu', 'pdl_test_area_submenu');
