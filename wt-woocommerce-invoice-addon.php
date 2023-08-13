<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.webtoffee.com/
 * @since             1.0.0
 * @package           Wt_woocommerce_invoice_addon
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce PDF Invoices, Packing Slips and Credit Notes (Pro)
 * Plugin URI:        https://www.webtoffee.com/
 * Description:       Extension for the WooCommerce PDF Invoices and Packingslip by Webtoffee
 * Version:           1.0.3
 * Author:            Webtoffee
 * Author URI:        https://www.webtoffee.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wt_woocommerce_invoice_addon
 * Domain Path:       /languages
 * WC tested up to:   7.8
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

include_once(ABSPATH.'wp-admin/includes/plugin.php');


$current_plugin_name='WooCommerce PDF Invoices, Packing Slips and Credit Notes (Pro)';
$wt_pklist_no_plugin_conflict=true;

//check if premium version is there
if(is_plugin_active('wt-woocommerce-packing-list/wf-woocommerce-packing-list.php')) 
{
    $active_plugin_name='WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels (Pro)';
    $wt_pklist_no_plugin_conflict=false;

}else if (is_plugin_active('shipping-labels-for-woo/wf-woocommerce-packing-list.php'))
{
    $active_plugin_name='WooCommerce Shipping Label (Basic)';
    $wt_pklist_no_plugin_conflict=false;
}

if(!$wt_pklist_no_plugin_conflict)
{
    //return;
    deactivate_plugins(plugin_basename(__FILE__));
    wp_die(sprintf(__("The plugins %s and %s cannot be active in your store at the same time. Kindly deactivate one of these prior to activating the other.", 'wt_woocommerce_invoice_addon'), $active_plugin_name, $current_plugin_name), "", array('link_url' => admin_url('plugins.php'), 'link_text' => __('Go to plugins page', 'wt_woocommerce_invoice_addon') ));
}

if(!is_plugin_active('print-invoices-packing-slip-labels-for-woocommerce/print-invoices-packing-slip-labels-for-woocommerce.php')){
    deactivate_plugins(plugin_basename(__FILE__));
    unset($_GET['activate']);
    add_action('admin_notices','base_plugin_not_active_ipc');
    return;
}
function base_plugin_not_active_ipc(){
    // $base_plugin_name =  "WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels (Free)";
    $class = 'notice notice-error';
	$message = __( 'WooCommerce PDF Invoices, Packing Slips and Credit Notes (Pro) plugin could not be activated. Please ensure the WooCommerce PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels (Free) is active', 'wt_woocommerce_invoice_addon' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}
/**
 * Currently plugin version.
 */
if(!defined('WT_PKLIST_INVOICE_ADDON_VERSION')){
    define( 'WT_PKLIST_INVOICE_ADDON_VERSION', '1.0.3' );
}
if(!defined('WT_PKLIST_INVOICE_ADDON_PLUGIN_URL')){
    define ( 'WT_PKLIST_INVOICE_ADDON_PLUGIN_PATH', plugin_dir_path(__FILE__) );
    define ( 'WT_PKLIST_INVOICE_ADDON_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if(!defined('WT_PKLIST_INVOICE_ADDON_FILENAME')){
    define ( 'WT_PKLIST_INVOICE_ADDON_FILENAME',__FILE__);
}
if(!defined('WT_PKLIST_INVOICE_ADDON_PLUGIN_NAME')){
    define('WT_PKLIST_INVOICE_ADDON_PLUGIN_NAME','wt-woocommerce-invoice-addon');
}

if(!defined('WT_PKLIST_INVOICE_ADDON_PLUGIN_BASENAME')){
    define( 'WT_PKLIST_INVOICE_ADDON_PLUGIN_BASENAME', plugin_basename(__FILE__) );
}
define( 'WT_PKLIST_INVOICE_ADDON_EDD_ACTIVATION_ID','470965');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wt_woocommerce_invoice_addon-activator.php
 */
function activate_wt_woocommerce_invoice_addon() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wt_woocommerce_invoice_addon-activator.php';
	Wt_woocommerce_invoice_addon_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wt_woocommerce_invoice_addon-deactivator.php
 */
function deactivate_wt_woocommerce_invoice_addon() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wt_woocommerce_invoice_addon-deactivator.php';
	Wt_woocommerce_invoice_addon_Deactivator::deactivate();
}
if(class_exists('Wf_Woocommerce_Packing_List')){
    register_activation_hook( __FILE__, 'activate_wt_woocommerce_invoice_addon' );
}
register_deactivation_hook( __FILE__, 'deactivate_wt_woocommerce_invoice_addon' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wt_woocommerce_invoice_addon.php';

/**
 *  Declare compatibility with custom order tables for WooCommerce.
 * 
 *  @since 1.0.3
 *  
 */
add_action(
    'before_woocommerce_init',
    function () {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
);

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wt_woocommerce_invoice_addon() {

	$plugin = new Wt_woocommerce_invoice_addon();
	$plugin->run();

}
if(class_exists('Wf_Woocommerce_Packing_List')){
    run_wt_woocommerce_invoice_addon();
}
