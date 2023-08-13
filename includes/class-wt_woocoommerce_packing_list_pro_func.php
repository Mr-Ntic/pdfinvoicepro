<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

if(!class_exists('Wf_Woocommerce_Packing_List_Pro_Common_Func'))
{

class Wf_Woocommerce_Packing_List_Pro_Common_Func {

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
		
		add_action( 'wp_ajax_wf_pklist_advanced_fields', array($this, 'advanced_settings'), 10);
	}

	public function enqueue_scripts() {
		$page_js_arr = array(
			'wf_woocommerce_packing_list_invoice',
			'wf_woocommerce_packing_list_packinglist',
			'wf_woocommerce_packing_list_creditnote'
		);
		if(isset($_GET['page']) && in_array($_GET['page'],$page_js_arr)){
			wp_enqueue_script( $this->plugin_name.'-ipc-pro-common-js', plugin_dir_url( __FILE__ ) . 'js/wf-woocommerce-packing-list-admin-pro-common.js', array( 'jquery','wp-color-picker','jquery-tiptip'), $this->version, false );
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
			wp_localize_script($this->plugin_name.'-ipc-pro-common-js', 'wf_pklist_params_ipc_common_param', $params);
		}
	}
	
	public function default_settings(){
		
	}
	/**
	* Checking an array is associative or not
	* @since 1.0.0
	* @param array $array input array
	* @return bool 
	*/
	public static function is_assoc(array $array)
	{
	    // Keys of the array
	    $keys = array_keys($array);

	    // If the array keys of the keys match the keys, then the array must
	    // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
	    return array_keys($keys) !== $keys;
	}

	/**
	*
	* @since 1.0.0
	* @param array $array checkout field value unprocessed
	* @return array $array checkout field value processed
	* 
	*/
	public static function process_checkout_fields($arr)
	{
		$arr=!is_array($arr) ? array() : $arr;
		/* not associative array, That mean's old version,then convert it */
		if(!self::is_assoc($arr) && count($arr)>0)
		{
			$arr_keys=array_map(function($vl){ 
			  return self::process_checkout_key($vl);
			},$arr);
			$arr=array_combine($arr_keys,$arr); //creating an array
		}
		return $arr;
	}

	/**
	* Filtering unwanted characters from checkout field meta key
	* @since 1.0.0
	* @param string $meta_key meta key user input
	* @return string $meta_key processed meta key
	*/
	public static function process_checkout_key($meta_key)
	{
		return strtolower(preg_replace("/[^A-Za-z]/",'_', $meta_key));
	}

	/**
	* Ajax function to list additional checkout fields/ product meta/ order meta etc
	* @since 1.0.0
	* - Role checking and nonce checking added
	* - Added compatibility to product meta, order meta
	*/
    public function custom_field_list_view()
    {
    	if(!Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
    	{
    		exit();
    	}
    	$custom_field_type=(isset($_POST['wt_pklist_custom_field_type']) ? sanitize_text_field($_POST['wt_pklist_custom_field_type']) : '');

    	if("" !== $custom_field_type)
    	{                
	        
	        $module_base=(isset($_POST['wt_pklist_settings_base']) ? sanitize_text_field($_POST['wt_pklist_settings_base']) : 'main');
			$module_id=("main" === $module_base ? '' : Wf_Woocommerce_Packing_List::get_module_id($module_base));

			$field_config=array(
				'checkout'=>array(
					'list'=>'wf_additional_checkout_data_fields',
					'selected'=>'wf_invoice_additional_checkout_data_fields',
				),
				'order_meta'=>array(
					'list'=>'wf_additional_data_fields',
					'selected'=>'wf_'.$module_base.'_contactno_email',
				),
				'product_meta'=>array(
					'list'=>'wf_product_meta_fields',
					'selected'=>'wf_'.$module_base.'_product_meta_fields',
				),
				'product_attribute'=>array(
					'list'=>'wt_product_attribute_fields',
		        	'selected'=>'wt_'.$module_base.'_product_attribute_fields',
				),
			);

			/* option key names for full list, selected list */
			$list_field=$field_config[$custom_field_type]['list'];
			$val_field=$field_config[$custom_field_type]['selected'];

			/* list of user created items */
        	$user_created=Wf_Woocommerce_Packing_List::get_option($list_field);
        	$user_created =$user_created && is_array($user_created) ? $user_created : array();

        	$default_fields=array();
        	$additional_field_options=array();
        	if("checkout" === $custom_field_type)
        	{
        		/* if it is a numeric array convert it to associative.*/
	        	$user_created=self::process_checkout_fields($user_created);
	        	$additional_field_options=Wf_Woocommerce_Packing_List::get_option('wt_additional_checkout_field_options');
	        	$default_fields=Wf_Woocommerce_Packing_List::$default_additional_checkout_data_fields;
        	}

        	$vl=Wf_Woocommerce_Packing_List::get_option($val_field, $module_id);
			$user_selected_arr =($vl!= '' && is_array($vl) ? $vl : array());


    		//delete action
	        if(isset($_POST['wf_delete_custom_field'])) 
		    {
		    	$data_key=sanitize_text_field($_POST['wf_delete_custom_field']);
		    	unset($user_created[$data_key]); //remove from field list
		    	Wf_Woocommerce_Packing_List::update_option($list_field, $user_created);

		    	if("checkout" === $custom_field_type)
        		{
		    		unset($additional_field_options[$data_key]); //remove from field additional options
		    		Wf_Woocommerce_Packing_List::update_option('wt_additional_checkout_field_options', $additional_field_options);
		    	}
		    	
		    	//remove from user selected array
		    	if(($delete_key=array_search($data_key, $user_selected_arr))!==false)
		    	{
				    unset($user_selected_arr[$delete_key]);
				    Wf_Woocommerce_Packing_List::update_option($val_field, $user_selected_arr, $module_id);
				}
		    }

		    if(!empty($default_fields)){
		    	$fields=array_merge($default_fields, $user_created);
		    }else{
		    	$fields = $user_created;
		    }

		    if(count($fields)>0)
		    {
		    	foreach($fields as $key=>$field)
				{
					$add_data=isset($additional_field_options[$key]) ? $additional_field_options[$key] : array();
					$is_required=(int) (isset($add_data['is_required']) ? $add_data['is_required'] : 0);
					$placeholder=(isset($add_data['placeholder']) ? $add_data['placeholder'] : '');

					/* we are giving option to edit title of builtin items */
					$field=(isset($add_data['title']) && trim($add_data['title'])!="" ? $add_data['title'] : $field);

					$is_required_display=($is_required>0 ? ' <span style="color:red;">*</span>' : '');
					$placeholder_display=("" !== $placeholder ? '<br /><i style="color:#666;">'.$placeholder.'</i>' : '');
					
					$is_builtin=(isset($default_fields[$key]) ? 1 : 0);
					$delete_btn='<span title="'.__('Delete','wt_woocommerce_invoice_addon').'" class="dashicons dashicons-trash wt_pklist_custom_field_delete '.($is_builtin==1 ? 'disabled_btn' : '').'"></span>';
					$edit_btn='<span title="'.__('Edit','wt_woocommerce_invoice_addon').'" class="dashicons dashicons-edit wt_pklist_custom_field_edit"></span>';
					
					//$delete_btn=($is_builtin==1 ? '' : $delete_btn); 
					$is_selected=(in_array($key, $user_selected_arr) ? '<span class="dashicons dashicons-yes-alt" style="color:green; float:right;"></span>' : '');
					$is_selected='';

					$meta_key_display=Wf_Woocommerce_Packing_List::get_display_key($key);
					?>
					<div class="wt_pklist_custom_field_item" data-key="<?php echo esc_attr($key);?>" data-builtin="<?php echo esc_attr($is_builtin);?>"><?php echo wp_kses_post($edit_btn.$delete_btn.$is_selected.$field.$meta_key_display.$is_required_display.$placeholder_display);?>
						<div class="wt_pklist_custom_field_title" style="display:none;"><?php echo esc_html($field);?></div>				
						<div class="wt_pklist_custom_field_placeholder" style="display:none;"><?php echo esc_html($placeholder);?></div>				
						<div class="wt_pklist_custom_field_is_required" style="display:none;"><?php echo esc_html($is_required);?></div>				
					</div>
					<?php
				}
			}else
			{
				?>
				<div style="text-align:center;"><?php _e('No data found', 'wt_woocommerce_invoice_addon'); ?></div>
				<?php
			}
		}
		exit();
    }

    /**
	* Fields like `Order meta fields`, `Product meta fields` etc have extra popup for saving item. Ajax hook
	* @since 1.0.0
	* - added separate fields for key and value for checkout fields and added compatibility to old users
	* - is_required and placeholder options added
	* - Combined independent hooks from each modules
	* - Edit option added to Order meta, Product meta etc, Added Product attribute
	*/
	public static function advanced_settings($module_base='',$module_id='')
	{
		$out=array('key'=>'', 'val'=>'', 'success'=>false, 'msg'=>__('Error', 'wt_woocommerce_invoice_addon'));
		$warn_msg=__('Please enter mandatory fields','wt_woocommerce_invoice_addon');
		
		if(Wf_Woocommerce_Packing_List_Admin::check_write_access()) 
    	{
			if(isset($_POST['wt_pklist_custom_field_btn']))  
			{
			    //additional fields for checkout
				if(isset($_POST['wt_pklist_new_custom_field_title']) && isset($_POST['wt_pklist_new_custom_field_key']) && isset($_POST['wt_pklist_custom_field_type'])) 
		        {
		        	if("" !== trim($_POST['wt_pklist_new_custom_field_title']) && "" !== trim($_POST['wt_pklist_new_custom_field_key']))
		        	{
		        		$custom_field_type=sanitize_text_field($_POST['wt_pklist_custom_field_type']);
		        		//checkout
		        		if("checkout" === $custom_field_type)
		        		{
		        			$out=self::edit_checkout_fields($out);

		        		}elseif("order_meta" === $custom_field_type || "product_meta" === $custom_field_type || "product_attribute" === $custom_field_type)
		        		{
		        			$module_base=(isset($_POST['wt_pklist_settings_base']) ? sanitize_text_field($_POST['wt_pklist_settings_base']) : 'main');
							$module_id = ("main" === $module_base ? '' : Wf_Woocommerce_Packing_List::get_module_id($module_base));
							$add_only = (isset($_POST['add_only']) ? true : false);
		        			$field_config=array(
		        				'order_meta'=>array(
		        					'list'=>'wf_additional_data_fields',
		        					'selected'=>'wf_'.$module_base.'_contactno_email',
		        				),
		        				'product_meta'=>array(
		        					'list'=>'wf_product_meta_fields',
		        					'selected'=>'wf_'.$module_base.'_product_meta_fields',
		        				),
		        				'product_attribute'=>array(
		        					'list'=>'wt_product_attribute_fields',
		        					'selected'=>'wt_'.$module_base.'_product_attribute_fields',
		        				),
		        			);

		        			/* form input */
		        			$new_meta_key=sanitize_text_field($_POST['wt_pklist_new_custom_field_key']);		            
        					$new_meta_vl=sanitize_text_field($_POST['wt_pklist_new_custom_field_title']);

        					/* option key names for full list, selected list */
        					$list_field=$field_config[$custom_field_type]['list'];
        					$val_field=$field_config[$custom_field_type]['selected'];
        					
        					/* list of user created items */
        					$user_created=Wf_Woocommerce_Packing_List::get_option($list_field); //this is plugin main setting so no need to specify module base

        					/* updating new item to user created list */
        					$user_created =$user_created && is_array($user_created) ? $user_created : array();
        					$action=(isset($user_created[$new_meta_key]) ? 'edit' : 'add');
				            
				            $can_add_item=true;
        					if("edit" === $action && $add_only)
        					{
        						$can_add_item=false;
        					}

        					if($can_add_item)
        					{
				            	$user_created[$new_meta_key] = $new_meta_vl;
				            	Wf_Woocommerce_Packing_List::update_option($list_field, $user_created);
				            }

				            if(!$add_only)
				            {
					            $vl=Wf_Woocommerce_Packing_List::get_option($val_field, $module_id);
					            $user_selected_arr =($vl!= '' && is_array($vl) ? $vl : array());			            

					            if(!in_array($new_meta_key, $user_selected_arr)) 
					            {
					                $user_selected_arr[] = $new_meta_key;
					                Wf_Woocommerce_Packing_List::update_option($val_field, $user_selected_arr, $module_id);			                
					            }
					        }

					        if($can_add_item)
					        {
					            $new_meta_key_display=Wf_Woocommerce_Packing_List::get_display_key($new_meta_key);

					            $dc_slug=Wf_Woocommerce_Packing_List_Admin::sanitize_css_class_name($new_meta_key_display); /* This is for Dynamic customizer */

					            $out=array('key'=>$new_meta_key, 'val'=>$new_meta_vl.$new_meta_key_display, 'dc_slug'=>$dc_slug, 'success'=>true, 'action'=>$action);
					        }else
					        {
					        	$out['msg']=__('Item with same meta key already exists', 'wt_woocommerce_invoice_addon');
					        }
		        		}

		        	}else
		        	{
		        		$out['msg']=$warn_msg;
		        	}
		        }
		    }
		}
	    echo json_encode($out);
		exit();
	}

	/**
	*	Alter custom checkout fields. (Ajax sub function)
	*	@since 1.0.0
	*/
	private static function edit_checkout_fields($out)
	{
		/* currently selected values */ 
    	$vl=Wf_Woocommerce_Packing_List::get_option('wf_invoice_additional_checkout_data_fields');
    	$user_selected_array = ($vl && is_array($vl)) ? $vl : array();
    	
    	/* list of user created items */
    	$user_created=Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');

        /* if it is a numeric array convert it to associative. */
        $user_created=self::process_checkout_fields($user_created);

        /* built in checkout fields */
        $add_checkout_data_flds=Wf_Woocommerce_Packing_List::$default_additional_checkout_data_fields;

        /* form input for adding new key */
        $new_meta_key=self::process_checkout_key($_POST['wt_pklist_new_custom_field_key']);		            
        $new_meta_vl=sanitize_text_field($_POST['wt_pklist_new_custom_field_title']);

        /* check the new key is not a built-in or custom */
        if(!isset($user_created[$new_meta_key]) && !isset($add_checkout_data_flds[$new_meta_key]))
        {
        	/* updating new item to user created list */
            $user_created[$new_meta_key]=$new_meta_vl;
            Wf_Woocommerce_Packing_List::update_option('wf_additional_checkout_data_fields', $user_created);
                       
            if(!in_array($new_meta_key, $user_selected_array))  /* checks not already selected */
            {
            	/* add to currently selected values */ 
                $user_selected_array[]=$new_meta_key;
                Wf_Woocommerce_Packing_List::update_option('wf_invoice_additional_checkout_data_fields', $user_selected_array);		                
            }
            $action='add';		            
    	}else
    	{
   			//editing...
   			$action='edit';
    	}

    	//add metakey extra information (required, placeholder etc)
        $field_extra_info=Wf_Woocommerce_Packing_List::get_option('wt_additional_checkout_field_options');
        $placeholder=(isset($_POST['wt_pklist_new_custom_field_title_placeholder']) ? sanitize_text_field($_POST['wt_pklist_new_custom_field_title_placeholder']) : '');
        $is_required=(isset($_POST['wt_pklist_cst_chkout_required']) ? intval($_POST['wt_pklist_cst_chkout_required']) : 0);
        $field_extra_info[$new_meta_key]=array('placeholder'=>$placeholder, 'is_required'=>$is_required, 'title'=>$new_meta_vl);
        Wf_Woocommerce_Packing_List::update_option('wt_additional_checkout_field_options', $field_extra_info);

		$out=array(
    		'key'=>$new_meta_key, 
    		'val'=>$new_meta_vl.' ('.$new_meta_key.')'.(1 === $is_required ? ' ('.__('required','wt_woocommerce_invoice_addon').')' : ''),
    		'success'=>true,
    		'action'=>$action
    	);
		
        return $out;
	}
}
// end of class
}
?>