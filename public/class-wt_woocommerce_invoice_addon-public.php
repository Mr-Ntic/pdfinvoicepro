<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wt_woocommerce_invoice_addon
 * @subpackage Wt_woocommerce_invoice_addon/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wt_woocommerce_invoice_addon
 * @subpackage Wt_woocommerce_invoice_addon/public
 * @author     Webtoffee <info@webtoffee.com>
 */
class Wt_woocommerce_invoice_addon_Public {

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

	public static $basic_modules = array(
		'invoice',
		'packinglist',
		'deliverynote',
		'shippinglabel',
		'dispatchlabel',
	);

	public static $basic_modules_df_state = array(
		'invoice'=>1,
		'packinglist'=>1,
		'deliverynote'=>1,
		'shippinglabel'=>1,
		'dispatchlabel'=>1,
	);

	public static $modules = array(
		'invoice',
		'packinglist',
		'creditnote'
	);

	public static $modules_label = array(
		'invoice' => "Invoice",
		'packinglist' => "Packing slip",
		'creditnote' => "Credit note",
	);

	public static $existing_modules=array();
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_filter('wt_pklist_add_module_tiles',array($this,'add_document_tiles'),10,2);
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wt_woocommerce_invoice_addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wt_woocommerce_invoice_addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wt_woocommerce_invoice_addon-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wt_woocommerce_invoice_addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wt_woocommerce_invoice_addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wt_woocommerce_invoice_addon-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register common modules Invoice, packingslip and creditnote
	 */
	public function common_modules(){
		$wt_pklist_common_modules=get_option('wt_pklist_common_modules');
		$common_modules_set = true;

		if(false === $wt_pklist_common_modules){
			$common_modules_set = false;
		}else{
			$temp_common_modules = $wt_pklist_common_modules;
		}

		foreach (self::$modules as $module) //loop through module list and include its file
		{
			$is_active = 1;
			if(isset($wt_pklist_common_modules[$module])){
				$is_active = $wt_pklist_common_modules[$module];
			}else{
				$wt_pklist_common_modules[$module]=1; //default status is active
			}

			$module_file=plugin_dir_path( __FILE__ )."modules/$module/$module.php";
			if(file_exists($module_file) && (1 === $is_active || '1' === $is_active)){
				self::$existing_modules[]=$module; //this is for module_exits checking
				require_once $module_file;
			}elseif(!in_array($module, self::$basic_modules)){
				$wt_pklist_common_modules[$module]=0;
			}
		}

		if(!empty(array_merge(array_diff_assoc($wt_pklist_common_modules, $temp_common_modules),array_diff_assoc($temp_common_modules,$wt_pklist_common_modules))) ){
			$common_modules_set = false;
		}
		

		if(false === $common_modules_set){
			update_option('wt_pklist_common_modules',$wt_pklist_common_modules);
		}
		$wt_pklist_common_modules=get_option('wt_pklist_common_modules');
	}

	public function add_document_tiles($module_key,$module_checked){

		if(in_array($module_key,self::$modules)){
			$modules_label = array_key_exists($module_key,self::$modules_label) ? self::$modules_label[$module_key]: "";
			$module_id=Wf_Woocommerce_Packing_List::get_module_id($module_key);
	        $settings_url=admin_url('admin.php?page='.$module_id);
	        $module_logo_url = WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'assets/images/'.$module_key.'_logo.png';
	        $checked =  (1 === $module_checked || "1" === $module_checked) ? "checked" : "";
			$html = sprintf('<div class="wfte_doc_col-3">
								<div class="wfte_doc_outter_div">
			                		<div class="wfte_doc_title_image">
			                			<a class="doc_module_link" href="'.$settings_url.'" data-href="'.$settings_url.'">
			                				<img src="'.esc_url($module_logo_url).'">
			                				<h3>'.$modules_label.'</h3>
			                			</a>
			                		</div>
			                		<div class="wfte_doc_setting_toggle">
					                    <div  class="wf_pklist_dashboard_box_footer_up" style="">
					                        <div class="wf_pklist_dashboard_checkbox">
					                            <input type="checkbox" value="1" name="wt_pklist_common_modules['.$module_key.']" class="wf_slide_switch wt_document_module_enable" id="wt_pklist_'.$module_key.'" '.$checked.'>   
					                        </div>
					                    </div>
					                </div>
		                		</div>
							</div>');
			echo $html;
		}
	}

	public static function module_exists($module)
	{
		return in_array($module,self::$existing_modules);
	}
}