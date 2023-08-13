<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wt_woocommerce_invoice_addon
 * @subpackage Wt_woocommerce_invoice_addon/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wt_woocommerce_invoice_addon
 * @subpackage Wt_woocommerce_invoice_addon/admin
 * @author     Webtoffee <info@webtoffee.com>
 */
class Wt_woocommerce_invoice_addon_Admin {

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

	public $module_base;
	public $module_id;
	public static $return_dummy_invoice_number = false;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->module_base = "";
		$this->module_id = "";

		add_filter('wt_pklist_add_additional_tab_item_into_module',array($this,'add_additional_tab'),10,3);
		add_action('wt_pklist_add_additional_tab_content_into_module', array($this, 'out_settings_form'),10,2);
		add_filter('wt_pklist_add_fields_to_settings',array($this,'add_remove_fields_from_settings'),10,4);
		add_filter('wf_module_single_checkbox_fields',array($this,'single_checkbox_fields'),10,3);
		add_filter('wf_module_multi_checkbox_fields',array($this,'multi_checkbox_fields'),10,3);
		add_filter('wf_module_default_settings',array($this,'default_settings'),10,2);
		add_filter('wt_pklist_intl_alter_validation_rule',array($this,'alter_validation_rule'),10,2);
		add_filter('wt_pklist_alter_tooltip_data', array($this, 'register_tooltips'), 1);

		// hook to switch the pro checkout fields
		add_filter('wt_pklist_switch_pro_for_checkout_fields',array($this,'switch_to_pro_checkout_fields'),10,1);
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
		 * defined in Wt_woocommerce_invoice_addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wt_woocommerce_invoice_addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wt_woocommerce_invoice_addon-admin.css', array(), $this->version, 'all' );

		$tab_content_arr = apply_filters('wt_pklist_tab_content_arr_css',array(),$this->module_id);
		if(!in_array('wt_main_advanced',$tab_content_arr)){
			add_filter('wt_pklist_tab_content_arr_css',array($this,'add_value_to_tab_content'),10,2);
			wp_enqueue_style( $this->plugin_name.'-admin-common-css', plugin_dir_url( __FILE__ ) . 'css/wt_woocommerce_invoice_addon_admin_common.css', array(), $this->version, 'all' );
		}
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
		 * defined in Wt_woocommerce_invoice_addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wt_woocommerce_invoice_addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$tab_content_arr = apply_filters('wt_pklist_tab_content_arr_js',array(),$this->module_id);
		if(!in_array('wt_main_advanced',$tab_content_arr) && isset($_GET['page']) && "wf_woocommerce_packing_list" === $_GET['page']){
			add_filter('wt_pklist_tab_content_arr_js',array($this,'add_value_to_tab_content'),10,2);
			wp_enqueue_script( $this->plugin_name.'-ipc-admin-common-js', plugin_dir_url( __FILE__ ) . 'js/wt_woocommerce_invoice_addon_admin_common.js', array( 'jquery' ), $this->version, false );

			$order_meta_autocomplete = Wf_Woocommerce_Packing_List_Admin::order_meta_dropdown_list();
			$wf_admin_img_path=WF_PKLIST_PLUGIN_URL . 'admin/images/uploader_sample_img.png';
			$is_rtl = is_rtl() ? 'rtl' : 'ltr';
			$params=array(
				'nonces' => array(
			            'wf_packlist' => wp_create_nonce(WF_PKLIST_PLUGIN_NAME),
			     ),
				'ajaxurl' => admin_url('admin-ajax.php'),
				'no_image'=>$wf_admin_img_path,
				'print_action_url'=>admin_url('?print_packinglist=true'),
				'order_meta_autocomplete' => json_encode($order_meta_autocomplete),
				'is_rtl' => $is_rtl,
				'msgs'=>array(
					'settings_success'=>__('Settings updated.','wt_woocommerce_invoice_addon'),
					'all_fields_mandatory'=>__('All fields are mandatory','wt_woocommerce_invoice_addon'),
					'enter_mandatory_non_numeric_fields'=>__('Meta key should not be numeric','wt_woocommerce_invoice_addon'),
					'settings_error'=>sprintf(__('Unable to update settings due to an internal error. %s To troubleshoot please click %s here. %s', 'wt_woocommerce_invoice_addon'), '<br />', '<a href="https://www.webtoffee.com/how-to-fix-the-unable-to-save-settings-issue/" target="_blank">', '</a>'),
					'select_orders_first'=>__('You have to select order(s) first!','wt_woocommerce_invoice_addon'),
					'invoice_not_gen_bulk'=>__('One or more order do not have invoice generated. Generate manually?','wt_woocommerce_invoice_addon'),
					'error'=>__('Error','wt_woocommerce_invoice_addon'),
					'please_wait'=>__('Please wait','wt_woocommerce_invoice_addon'),
					'is_required'=>__("is required",'wt_woocommerce_invoice_addon'),
					'sure'=>__("You can't undo this action. Are you sure?",'wt_woocommerce_invoice_addon'),
					'invoice_title_prompt' => __("Invoice",'wt_woocommerce_invoice_addon'),
					'invoice_number_prompt' => __("number has not been generated yet. Do you want to manually generate one ?",'wt_woocommerce_invoice_addon'),
					'invoice_number_prompt_free_order' => __("‘Generate invoice for free orders’ is disabled in Invoice settings > Advanced. You are attempting to generate invoice for this free order. Proceed?",'wt_woocommerce_invoice_addon'),
					'invoice_number_prompt_no_from_addr' => __("Please fill the `from address` in the plugin's general settings.",'wt_woocommerce_invoice_addon'),
					'fitler_code_copied' => __("Code Copied","wt_woocommerce_invoice_addon"),
					'close'=>__("Close",'wt_woocommerce_invoice_addon'),
					'save'=>__("Save",'wt_woocommerce_invoice_addon'),
					'default'=>__("Default",'wt_woocommerce_invoice_addon'),
					'enter_mandatory_fields'=>__('Please enter mandatory fields','wt_woocommerce_invoice_addon'),
					'buy_pro_prompt_order_meta' => __('You can add more than 1 order meta in','wt_woocommerce_invoice_addon'),
					'buy_pro_prompt_edit_order_meta' => __('Edit','wt_woocommerce_invoice_addon'),
					'buy_pro_prompt_edit_order_meta_desc' => __('You can edit an existing item by using its key.','wt_woocommerce_invoice_addon'),
				)
			);
			wp_localize_script($this->plugin_name.'-ipc-admin-common-js', 'wf_pklist_params_ipc_admin_param', $params);
		}
	}

	/**
	 * @since 1.0.0
	 * To add the advanced tab on the plugin main settings page
	 */
	public function add_additional_tab($tab_items,$module_base,$module_id){
		if($module_base === ""){
			$new_element = array('advanced' => __('Advanced','wt_woocommerce_invoice_addon'));
			$tab_items = self::wt_add_array_element_to_position($tab_items,$new_element,'general');
		}
		return $tab_items;
	}

	/**
	 * @since 1.0.0
	 * To get all the tooltip of plugin main settings page
	 */
	public function register_tooltips($tooltip_arr)
	{
		include(plugin_dir_path( __FILE__ ).'data/data.tooltip.php');
		$tooltip_arr["main"]=$arr;
		return $tooltip_arr;
	}

	/**
	 * 	To switch over to the pro checkout fields from basic
	 * 	@since 1.0.0
	 */
	public function switch_to_pro_checkout_fields($enabled){
		return true;
	}

	/**
	 *	To add the checkout fields on checkout page instead of using basic plugin
	 *  @since 1.0.0
	 */
	public function add_checkout_fields($fields){
		$already_checkout_fields_added = apply_filters('wt_pklist_checkout_fields_already_added',false,$this->module_id);
		if(!$already_checkout_fields_added){
			$user_selected_data_flds=Wf_Woocommerce_Packing_List::get_option('wf_invoice_additional_checkout_data_fields');
	        if(is_array($user_selected_data_flds) && count(array_filter($user_selected_data_flds))>0)
	        {
	        	add_filter('wt_pklist_checkout_fields_already_added',array($this,'enable_checkout_fields_already_added'),10,2);
			    $data_flds=self::get_checkout_field_list();

			    $priority_inc=110; //110 is the last item(billing email priority so our fields will be after that.)
			    $additional_checkout_field_options=Wf_Woocommerce_Packing_List::get_option('wt_additional_checkout_field_options');
	            foreach($user_selected_data_flds as $value)
	            {
	            	$priority_inc++;
	                if(isset($data_flds[$value])) //field exists in the user created/default field list
	                {
		                $add_data=isset($additional_checkout_field_options[$value]) ? $additional_checkout_field_options[$value] : array();
		                $is_required=(int) (isset($add_data['is_required']) ? $add_data['is_required'] : 0);
		                $placeholder=(isset($add_data['placeholder']) ? $add_data['placeholder'] : 'Enter '.$data_flds[$value]);
		                $title=(isset($add_data['title']) && "" !== trim($add_data['title']) ? $add_data['title'] : $data_flds[$value]);

		                $fields['billing']['billing_' . $value] = array(
		                    'type' => 'text',
		                    'label' => __($title, 'woocommerce'),
		                    'placeholder' => _x($placeholder, 'placeholder','woocommerce'),
		                    'required' =>$is_required,
		                    'class' => array('form-row-wide', 'align-left'),
		                    'clear' => true,
		                    'priority'=>$priority_inc,
		                );
	            	}
	            }
	        }
		}
		return $fields;
	}

	private static function get_checkout_field_list()
	{
        /* built in checkout fields */
        $default_checkout_fields=Wf_Woocommerce_Packing_List::$default_additional_checkout_data_fields;
        
        /* list of user created items */
        $user_created_checkout_fields=Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');
        $user_created_checkout_fields=Wf_Woocommerce_Packing_List_Pro_Common_Func::process_checkout_fields($user_created_checkout_fields);

	    return array_merge($default_checkout_fields, $user_created_checkout_fields);
	}

	/**
	 * @since 1.0.0
	 * Function to identify whether advaned tab is already added or not
	 */
	public function enable_checkout_fields_already_added($enabled,$base_id){
		if("" === $base_id && !$enabled){
			$enabled = true;
		}
		return $enabled;
	}

	/**
	 * @since 1.0.0
	 * To add the element after particular key in array
	 */
	public static function wt_add_array_element_to_position($settings,$new_element,$after_key){
		$pos = 1;
		foreach($settings as $key => $value){
			if($key === $after_key){
				break;
			}else{
				$pos++;
			}
		}

		$settings = array_slice($settings, 0, $pos) + $new_element + array_slice($settings, $pos);
		return $settings;
	}

	/**
	 * @since 1.0.0
	 * To add the advanced tab settings fields
	 */
	public function out_settings_form($base_id,$template_type)
	{	
		$tab_content_arr = apply_filters('wt_pklist_tab_content_arr',array(),$base_id);
		if("" === $base_id && !in_array('wt_main_advanced',$tab_content_arr)){
			add_filter('wt_pklist_tab_content_arr',array($this,'add_value_to_tab_content'),10,2);
			$target_id = "advanced";
			$pdf_libs=Wf_Woocommerce_Packing_List::get_pdf_libraries();
			$wf_admin_img_path=WF_PKLIST_PLUGIN_URL . 'admin/images';
			$view_file=plugin_dir_path( __FILE__ ).'views/advanced.php';
			include $view_file;
		}
	}

	/**
	 * @since 1.0.0
	 * Function to identify whether advaned tab is already added or not
	 */
	public function add_value_to_tab_content($tab_content_arr,$base_id){
		if("" === $base_id && !in_array('wt_main_advanced',$tab_content_arr)){
			array_push($tab_content_arr, 'wt_main_advanced');
		}
		return $tab_content_arr;
	}

	/**
	 * Function to add or remove the fields in base plugin general settings
	 * 
	 * - Remove the advanced option fields from general settings page to add it to the advance tab
	 * 
	 * @since 1.0.0
	 */
	public function add_remove_fields_from_settings($settings,$target_id,$template_type,$base_id){
		if("" === $base_id){
			if("general" === $target_id){
				if(isset($settings['general_company_details'])){
					$company_details = $settings['general_company_details'];
					$return_policy = array(
							'woocommerce_wf_packinglist_return_policy' => array(
	                            'type'  =>  'wt_textarea',
	                            'label' =>  __("Return Policy",'wt_woocommerce_invoice_addon'),
	                            'name'  =>    "woocommerce_wf_packinglist_return_policy",
	                            'class' => 'woocommerce_wf_packinglist_return_policy',
	                            'ref_id'=>  'woocommerce_wf_packinglist_return_policy',
	                            'help_text'=>__("Set up a footer which will be used across the respective documents.",'wt_woocommerce_invoice_addon'),
	                            'tooltip'=> true,
                        	)
						);
					$company_details = Wt_woocommerce_invoice_addon_Admin::wt_add_array_element_to_position($company_details,$return_policy,'woocommerce_wf_packinglist_sender_vat');
					$settings['general_company_details'] = $company_details;
				}
				
				if(isset($settings['advanced_option'])){
					unset($settings['advanced_option']);
				}
			}
		}
		return $settings;
	}

	/**
	 * @since 1.0.0
	 * To add the default main settings of pro version
	 */
	public function default_settings($settings,$base_id){

		if($base_id === "main"){
			$settings['woocommerce_wf_packinglist_return_policy'] = "";
			$settings['woocommerce_wf_tracking_number'] = '_tracking_number';
			$settings['wf_pklist_auto_temp_clear'] = 'No';
			$settings['wf_pklist_auto_temp_clear_interval'] = '1440'; //one day
			$settings['woocommerce_wf_packinglist_boxes'] = array();
			$settings['wf_invoice_additional_checkout_data_fields'] = array();

			// fields for saving the user defined checkout fields
			$settings['wt_additional_checkout_field_options'] = array();
			$settings['wf_additional_checkout_data_fields']= array();
		}
		return $settings;
	}

	/**
	 * @since 1.0.0
	 * Add pro version fields validation
	 */
	public function alter_validation_rule($arr, $base_id)
	{
		if("" === $base_id || "main" === $base_id)
		{	
			$arr['woocommerce_wf_packinglist_return_policy'] = array('type'=>'textarea');
			$arr['wf_invoice_additional_checkout_data_fields']=array('type'=>'text_arr');
			$arr['wt_additional_checkout_field_options'] = array('type'=>'text_arr');
			$arr['wf_additional_checkout_data_fields'] = array('type'=>'text_arr');
		}
		return $arr;
	}

	/**
	 * @since 1.0.0
	 * To add the values for the single checkboxes, when they are unchecked
	 */
	public function single_checkbox_fields($settings,$base_id,$tab_name){

		// remove the single checkbox field for general tab
		if("main" === $base_id && "wt_main_general" === $tab_name){
			$remove_keys = array('woocommerce_wf_packinglist_preview','woocommerce_wf_state_code_disable','woocommerce_wf_add_rtl_support');
			if(isset($settings['wt_main_general'])){
				foreach($remove_keys as $k){
					if(array_key_exists($k, $settings['wt_main_general'])){
						unset($settings['wt_main_general'][$k]);
					}
				}
			}
		}

		// add all the checkbox fields of
		if("main" === $base_id && "wt_main_advanced" === $tab_name){
			$settings['wt_main_advanced'] = array(
				'woocommerce_wf_packinglist_preview' => 'disabled',
				'woocommerce_wf_state_code_disable' => "no",
				'woocommerce_wf_add_rtl_support' => "No",
			);
		}
		return $settings;
	}

	/**
	 * @since 1.0.0
	 * To add the values for the multiple checkboxes, when they are unchecked
	 */
	public function multi_checkbox_fields($settings,$base_id,$tab_name){
		if("main" === $base_id && "wt_main_advanced" === $tab_name){
			$settings['wt_main_advanced'] = array(
				'wf_invoice_additional_checkout_data_fields' => array()
			);
		}
		return $settings;
	}

	/**
	 * @since 1.0.0
	 * To get the value for document creation date (Invoice and creditnote)
	 */
	public static function get_sequential_date($order_id, $key, $date_format, $order)
    {
		$invoice_date 	= Wt_Pklist_Common_Ipc::get_order_meta($order_id,'_'.$key,true);
    	if($invoice_date)
    	{
    		return (empty($invoice_date) ? '' : date_i18n($date_format, $invoice_date));
    	}else
    	{
    		if(self::$return_dummy_invoice_number)
	    	{
	    		return date_i18n($date_format);
	    	}else
	    	{
	    		return '';
	    	}
    	}
    }
    
	/**
	*	@since 1.0.0
	* 	Recursively calculating and retriveing total files in the plugin temp directory
	*
	*/
	public static function get_total_temp_files()
	{
		$file_count=0;
		$upload_dir=Wf_Woocommerce_Packing_List::get_temp_dir('path');
		if(is_dir($upload_dir))
		{
			$files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload_dir, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::LEAVES_ONLY);		
			foreach($files as $name=>$file)
			{
				if(!$file->isDir())
				{
					$file_name=$file->getFilename();
					$file_ext_arr=explode('.', $file_name);
					$file_ext=end($file_ext_arr);
					if(('pdf' === $file_ext) || ('html' === $file_ext)) //we are creating pdf files as temp files
					{
						$file_count++;
					}
				}
			} 
		}	
		return $file_count;
	}

	/**
	 * Add plugin action links
	 *
	 * @param array $links links array
	 */
	public function plugin_action_links($links) 
	{
	   $links[] = '<a href="'.admin_url('admin.php?page='.WF_PKLIST_POST_TYPE).'">'.__('Settings', 'wt_woocommerce_invoice_addon').'</a>';
	   $links[] = '<a href="https://www.webtoffee.com/woocommerce-pdf-invoices-packing-slips-and-credit-notes-plugin/" target="_blank">' . __('Documentation','wt_woocommerce_invoice_addon') . '</a>';
	   return $links;
	}

	/**
	*	Do remote printing. 
	*	Checks the current module needes a remote printing now 
	* 	@since 1.0.1
	*/
	public static function do_remote_printing($module_base_arr, $order_id, $doc_obj)
	{
		if(!is_null($doc_obj->customizer) && isset($module_base_arr[$doc_obj->module_base]))
        { 
        	$order_ids=array($order_id);
        	$pdf_name=$doc_obj->customizer->generate_pdf_name($doc_obj->module_base, $order_ids);
        	$doc_obj->customizer->template_for_pdf = true;
        	$html=$doc_obj->generate_order_template($order_ids, $pdf_name);

        	$module_base_arr[$doc_obj->module_base]=array(
        		'html'=>$html,
        		'pdf_file'=>$doc_obj->customizer->generate_template_pdf($html, $doc_obj->module_base, $pdf_name, 'attach'),
        		'title'=>$pdf_name
        	);
        }
        return $module_base_arr;
	}
}