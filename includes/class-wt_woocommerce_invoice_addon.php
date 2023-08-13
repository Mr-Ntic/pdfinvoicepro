<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wt_woocommerce_invoice_addon
 * @subpackage Wt_woocommerce_invoice_addon/includes
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
 * @package    Wt_woocommerce_invoice_addon
 * @subpackage Wt_woocommerce_invoice_addon/includes
 * @author     Webtoffee <info@webtoffee.com>
 */
class Wt_woocommerce_invoice_addon {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wt_woocommerce_invoice_addon_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	public static $pro_base_version;
	public $plugin_admin;
	public $plugin_public;
	public $plugin_common;
	public $pro_common_func;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WT_PKLIST_INVOICE_ADDON_VERSION' ) ) {
			$this->version = WT_PKLIST_INVOICE_ADDON_VERSION;
			self::$pro_base_version = WT_PKLIST_INVOICE_ADDON_VERSION;
		} else {
			$this->version = '1.0.3';
			self::$pro_base_version = '1.0.3';
		}
		$this->plugin_name = 'wt_woocommerce_invoice_addon';

		$this->load_libraries();
		$this->set_locale();
		$this->load_dependencies();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wt_woocommerce_invoice_addon_Loader. Orchestrates the hooks of the plugin.
	 * - Wt_woocommerce_invoice_addon_i18n. Defines internationalization functionality.
	 * - Wt_woocommerce_invoice_addon_Admin. Defines all hooks for the admin area.
	 * - Wt_woocommerce_invoice_addon_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_libraries() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wt_woocommerce_invoice_addon-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wt_woocommerce_invoice_addon-i18n.php';

		$this->loader = new Wt_woocommerce_invoice_addon_Loader();

	}

	/**
	 * Check the prerequisites before including the plugin files
	 *
	 * Includes all the files, if all prerequisites are present
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies(){
		if(false === $this->plugin_prerequisite_to_active()){
			return;
		}
		$this->include_plugin_files();
	}

	/**
	 * Includes the plugin dependecy files after checking the prerequisites
	 * 
	 * Include the following files that make up the plugin:
	 * 
	 * - Wt_woocommerce_invoice_addon_Admin. Defines all hooks for the admin area.
	 * - Wt_woocommerce_invoice_addon_Public. Defines all hooks for the public side of the site.
	 *
	 * @since 	1.0.0
	 * @access 	private
	 */ 
	private function include_plugin_files() {
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wt_woocommerce_invoice_addon-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wt_woocommerce_invoice_addon-public.php';

		/**
		 * The class responsible for defining all actions that occur in the admin and public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'common/class-wt-pklist-common.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wf-woocommerce-packing-list-pay-later-payment.php';

		require_once plugin_dir_path( dirname( __FILE__ ) )."admin/views/_form_field_generator_pro.php";
		
		if(class_exists('Wf_Woocommerce_Packing_List_Customizer') && !class_exists('Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO')){
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/modules/customizer/customizer.php';
		}

		if(!class_exists('Wf_Woocommerce_Packing_List_Template_Load')){
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/template-functions/load_template_element_attributes.php';
		}

		if(!class_exists('Wf_Woocommerce_Packing_List_Template_Render')){
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/template-functions/render_template_element_attributes.php';
		}

		if(!class_exists('Wf_Woocommerce_Packing_List_Order_Func_Pro')){
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/template-functions/class-wf-woocommerce-packing-list-order-func-pro.php';
		}

		if(!class_exists('Wf_Woocommerce_Packing_List_Template_Load_Epo_Tm')){
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/template-functions/compatibility_epo_theme_complete.php';
		}

		$this->plugin_admin = new Wt_woocommerce_invoice_addon_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->plugin_public = new Wt_woocommerce_invoice_addon_Public( $this->get_plugin_name(), $this->get_version() );
		$this->plugin_common = new Wt_Pklist_Common_Ipc( $this->get_plugin_name(), $this->get_version() );

		if(!class_exists('Wf_Woocommerce_Packing_List_Pro_Common_Func')){
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wt_woocoommerce_packing_list_pro_func.php';
			$this->pro_common_func = new Wf_Woocommerce_Packing_List_Pro_Common_Func( $this->get_plugin_name(), $this->get_version() );
		}

		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_common_hooks();

		/* License manager tab */
		$license_tab_exist = apply_filters('wt_pklist_add_licence_manager_tab',false);

		if(!$license_tab_exist){
			require_once plugin_dir_path( dirname( __FILE__ ) )."includes/licence_manager/licence_manager.php";
			add_filter("wt_pklist_add_licence_manager_tab", array($this, "add_licence_manager_tab"),10,1);
		}
		add_filter("wt_pklist_add_licence_manager", array($this, "add_licence_manager"));
	}

	public function add_licence_manager_tab($enabled){
		return true;
	}

	public function add_licence_manager($products)
	{
		$plugin_slug = dirname(WT_PKLIST_INVOICE_ADDON_PLUGIN_BASENAME);
		$products[$plugin_slug] = array(
			'product_id'			=>	WT_PKLIST_INVOICE_ADDON_EDD_ACTIVATION_ID,
			'product_edd_id'		=>	WT_PKLIST_INVOICE_ADDON_EDD_ACTIVATION_ID,
			'plugin_settings_url'	=>	admin_url('admin.php?page='.WF_PKLIST_POST_TYPE.'#wt-licence'),
			'product_version'		=>	WT_PKLIST_INVOICE_ADDON_VERSION,
			'product_name'			=>	WT_PKLIST_INVOICE_ADDON_PLUGIN_BASENAME,
			'product_slug'			=>	$plugin_slug,
			'product_display_name'	=>	'WooCommerce PDF Invoices, Packing Slips and Credit Notes (Pro)', //plugin name, no translation needed
		);
		return $products;
	}

	/**
	 * Check the following prerequisites
	 * 
	 * - If WooCommerce is installed
	 * - If Basic plugin is installed
	 * 
	 * @since 	1.0.0
	 * @access  private
	 */ 
	private function plugin_prerequisite_to_active(){
		if ( $this->check_woocommerce_activated() === false ) {
			add_action( 'admin_notices', array ( $this, 'require_woocommerce_notice' ) );
			return false;
		}

		if ( $this->check_basic_plugin_activated() === false ) {
			add_action( 'admin_notices', array ( $this, 'require_basic_plugin_notice' ) );
			return false;
		}

		return true;
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wt_woocommerce_invoice_addon_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wt_woocommerce_invoice_addon_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->plugin_public->common_modules();
		//hook for print checkout field list view in popup
		$this->loader->add_action('wp_ajax_wt_pklist_custom_field_list_view',$this->pro_common_func,'custom_field_list_view');
		
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_enqueue_scripts', $this->pro_common_func, 'enqueue_scripts' );

		$this->loader->add_filter('woocommerce_checkout_fields',$this->plugin_admin,'add_checkout_fields'); /* Add additional checkout fields */	

		do_action('wt_run_payment_link_module',$this->get_plugin_name(), $this->get_version());

		// Add plugin settings link: 
		$this->loader->add_filter('plugin_action_links_'.plugin_basename(WT_PKLIST_INVOICE_ADDON_FILENAME),$this->plugin_admin,'plugin_action_links');
	}

	private function define_common_hooks(){
		$this->plugin_common= Wt_Pklist_Common_Ipc::get_instance( $this->get_plugin_name(), $this->get_version() );
		$this->plugin_common->load_common_modules();
	}
	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wt_woocommerce_invoice_addon_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function check_woocommerce_activated(){
		$site_plugins = get_option( 'active_plugins', array() );
		$multi_site_plugins = get_site_option( 'active_sitewide_plugins', array() );

		if ( in_array( 'woocommerce/woocommerce.php', $site_plugins ) || isset( $multi_site_plugins['woocommerce/woocommerce.php'] ) ) {
			return true;
		}

		return false;
	}

	public function require_woocommerce_notice(){
		$notice_message = sprintf(__('%1$s WooCommerce PDF Invoices, Packing Slips and Credit Notes (Pro) %2$s is activated but not effective. It requires %3$s WooCommerce %4$s in order to work','wt_woocommerce_invoice_addon'),'<b>','</b>','<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">','</a>');
		$notice_div = '<div class="error"><p>' . $notice_message . '</p></div>';
	
		echo $notice_div;
	}

	/**
	 * Check if basic plugin is activated
	 */
	public function check_basic_plugin_activated() {
		if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

		if ( !class_exists('Wf_Woocommerce_Packing_List') || (class_exists('Wf_Woocommerce_Packing_List') && isset(Wf_Woocommerce_Packing_List::$base_version) && version_compare( Wf_Woocommerce_Packing_List::$base_version, '4.1.0' ) < 0) ) {
			return false;
		}else{
			return true;
		}

		return false;
	}

	public function require_basic_plugin_notice(){
		$notice_message = sprintf(__('%1$s WooCommerce PDF Invoices, Packing Slips and Credit Notes (Pro) %2$s is enabled but not effective. It requires %3$s WooCommerce PDF Invoices, Packing Slips, Delivery Notes & Shipping Labels (Basic) by WebToffee %4$s with minimum version 4.1.0 in order to work.', 'wt_woocommerce_invoice_addon'), '<b>', '</b>', '<a href="https://wordpress.org/plugins/print-invoices-packing-slip-labels-for-woocommerce" target="_blank">', '</a>');
		$notice_div = '<div class="error"><p>' . $notice_message . '</p></div>';
	
		echo $notice_div;
	}	
}