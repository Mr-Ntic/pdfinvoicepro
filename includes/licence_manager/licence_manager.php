<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!class_exists('Wf_Woocommerce_Packing_List_Licence_Manager')){

class Wf_Woocommerce_Packing_List_Licence_Manager
{
	public $module_id='';
	public $module_base='licence_manager';
	public $api_url='https://www.webtoffee.com/';
	public $main_plugin_slug='';
	public $my_account_url;
	public $tab_icons=array(
		'active'=>'<span class="dashicons dashicons-yes" style="color:#03da01; font-size:25px;"></span>',   
	    'inactive'=>'<span class="dashicons dashicons-warning" style="color:#ff1515; font-size:25px;"></span>'
	);

	public $products=array();
	public $last_error_message;

	public function __construct()
	{
		$this->module_id 			=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
		$this->my_account_url		=$this->api_url.'my-account';
		$this->main_plugin_slug		="wt-woocommerce-invoice-addon";

		$this->products = array();
		require_once plugin_dir_path(__FILE__).'classes/class-edd.php';	

		add_action('plugins_loaded', array($this, 'init'), 1);
		add_action('plugins_loaded',array($this,'license_warning_message'),1);

		/**
		*	Add tab to settings section
		*/
		add_filter('wf_pklist_plugin_settings_tabhead', array($this, 'licence_tabhead'));
		add_action('wf_pklist_plugin_out_settings_form', array($this, 'licence_content'));

		add_filter('wt_pklist_plugin_settings_tabhead', array($this, 'licence_tabhead'));
		add_action('wt_pklist_plugin_out_settings_form', array($this, 'licence_content'));

		/**
		*	 Main Ajax hook to handle all ajax requests 
		*/
		add_action('wp_ajax_wt_pklist_licence_manager_ajax', array($this, 'ajax_main'),11);

		/**
		*	 Check for plugin updates
		*/
		add_filter( 'pre_set_site_transient_update_plugins',array($this, 'update_check'));

		/** 
		*	Check For Plugin Information to display on the update details page
		*/
		add_filter('plugins_api', array( $this, 'update_details'), 10, 3);
	}

	public function init()
	{
		/**
		*	Add products to licence manager
		*/
		$this->products=apply_filters('wt_pklist_add_licence_manager', $this->products);
	}

	public function all_products()
	{
		/**
		*	Add products to licence manager
		*/
		$this->products=apply_filters('wt_pklist_add_licence_manager', $this->products);
		return $this->products;
	}

	public function license_warning_message(){
		$all_products = $this->all_products();

		foreach($all_products as $this_plugin){
			add_action( 'after_plugin_row_' .$this_plugin['product_name'], array( $this, 'add_license_notification' ), 10, 2 );
		}
	}

	/**
	*	Licence tab head
	*/
	public function licence_tabhead($arr)
	{	
		$all_products = $this->all_products();
		if(empty($all_products)){
			return $arr;
		}

		$status=true;
		$licence_data=$this->get_licence_data();
		if(!$licence_data)
		{
			$status=false; //no licence found
		}

		if($status && count($licence_data)!=count($this->products))
		{
			$status=false; //licence misisng for some products
		}

		if($status)
		{
			$licence_statuses=array_column($licence_data, 'status');
			if(0 === count($licence_statuses) || in_array('inactive', $licence_statuses) || in_array('', $licence_statuses)) //inactive licence
			{
				$status=false;
			}		
		}

		if($status)
	    {
	        $activate_icon=$this->tab_icons['active'];   
	    }else
	    {
	        $activate_icon=$this->tab_icons['inactive'];
	    }
		$arr['wt-licence']=array(__('Licence','wt_woocommerce_invoice_addon'),$activate_icon);
		return $arr;
	}

	/**
	*	Licence tab content
	*/
	public function licence_content()
	{
		wp_enqueue_script($this->module_id, plugin_dir_url( __FILE__ ).'assets/js/main.js', array('jquery'), WF_PKLIST_VERSION);

		$params=array(
	        'ajax_url' => admin_url('admin-ajax.php'),
	        'nonce' => wp_create_nonce(WF_PKLIST_POST_TYPE),
	        'tab_icons'=>$this->tab_icons,
	        'msgs'=>array(
	        	'key_mandatory'=>__('Please enter Licence key', 'wt_woocommerce_invoice_addon'),
	        	'email_mandatory'=>__('Please enter Email', 'wt_woocommerce_invoice_addon'),
	        	'product_mandatory'=>__('Please select a product', 'wt_woocommerce_invoice_addon'),
	        	'please_wait'=>__('Please wait...', 'wt_woocommerce_invoice_addon'),
	        	'error'=>__('Error', 'wt_woocommerce_invoice_addon'),
	        	'success'=>__('Success', 'wt_woocommerce_invoice_addon'),
	        	'unable_to_fetch'=>__('Unable to fetch Licence details', 'wt_woocommerce_invoice_addon'),
	        	'no_licence_details'=>__('No Licence details found.', 'wt_woocommerce_invoice_addon'),
	        	'sure'=>__('Are you sure?', 'wt_woocommerce_invoice_addon'),
	        )
		);
		wp_localize_script($this->module_id, 'wt_pklist_licence_params', $params);


		$view_file=plugin_dir_path(__FILE__).'views/licence-settings.php';	
		$params=array(
			'products'=>$this->products
		);
		Wf_Woocommerce_Packing_List_Admin::envelope_settings_tabcontent('wt-licence', $view_file, '', $params, 0);
	}

	/**
	* Main Ajax hook to handle all ajax requests. 
	*/
	public function ajax_main()
	{
		$allowed_actions=array('activate', 'deactivate', 'delete', 'licence_list', 'check_status');
		$action=(isset($_POST['wt_pklist_licence_manager_action']) ? sanitize_text_field($_POST['wt_pklist_licence_manager_action']) : '');
		$out=array('status'=>true, 'msg'=>'');
		if(!Wf_Woocommerce_Packing_List_Admin::check_write_access(WF_PKLIST_POST_TYPE))
		{
			$out['status']=false;

		}else
		{
			if(in_array($action,$allowed_actions))
			{
				if(method_exists($this,$action))
				{
					$out=$this->{$action}($out);
				}
			}
		}
		echo json_encode($out);
		exit();	
	}

	/**
	*	Ajax sub function to get license list
	*/
	public function licence_list($out)
	{
		$licence_data_arr=$this->get_licence_data(); //taking all license info
		ob_start();
		include plugin_dir_path(__FILE__).'views/_licence_list.php';
		$out['html']=ob_get_clean();
		return $out;
	}

	private function get_licence_data($product_slug="")
	{	
		$this->products = $this->all_products();

		if("" !== $product_slug)
		{
			$licence_data=get_option($product_slug.'_licence_data', false);
		}else
		{
			$licence_data=array();
			foreach ($this->products as $product_slug => $product)
			{
				$licence_info=get_option($product_slug.'_licence_data', false);
				if($licence_info) //licence exists
				{
					$licence_data[$product_slug]=$licence_info;	
				}
			}
		}
		return $licence_data;
	}

	/**
	* 	Check for plugin updates 
	*/
	public function update_check($transient)
	{		
		$this->products = $this->all_products();
		$license_transient = array();

		foreach($this->products as $pro_key => $pro_details){
			$this->flush_errors($pro_key,$pro_details); // Remove exising errors from transient.
			if ( false === $this->check_if_license_activated($pro_key,$pro_details) ) {
				$license_transient[$pro_key] = 0;
			}
		}

		if(in_array(0,$license_transient)){
			return $transient;
		}

		if(empty( $transient->checked ))
		{
			return $transient;
		}

		$home_url=urlencode(home_url());

		/**
		*	Get all licence info
		*/
		$licence_data=$this->get_licence_data();

		/**
		*	Main product data
		*/
		$product_data=$this->products[$this->main_plugin_slug];
		
		if(!function_exists('get_plugin_data')) /* this function is required for fetching current plugin version */
		{
		    require_once ABSPATH.'wp-admin/includes/plugin.php';
		}

		$timestamp=time(); //current timestamp
		foreach ($licence_data as $product_slug => $value)
		{
			if("active" === $value['status'] && isset($this->products[$product_slug]))
			{
				$product_data=$this->products[$product_slug];

				/**
				*	Taking the last update check time
				*/
				$last_check=get_option($product_slug.'-last-update-check');
				if(false === $last_check) //first time so add a four hour back time.
				{ 
					$last_check=$timestamp-14402;
					update_option($product_slug.'-last-update-check', $last_check);
				}

				/**
				* 	Previous check is before 4 hours or Force check
				*/
				if(($timestamp-$last_check)>14400 || (isset($_GET['force-check']) && $_GET['force-check']===1)) 
				{
					$args = array(
						'edd_action'		=> 	'get_version',
						'url' 				=> 	$home_url,
						
						/* product details */
						'item_id' 			=> 	(isset($product_data['product_edd_id']) ? $product_data['product_edd_id'] : 0),
						'license' 			=> 	$value['key'],
					);


					/* fetch plugin response */
					$response = $this->fetch_plugin_info($args);
					
					if(isset($response) && is_object($response) && false !== $response )
					{
						$plugin_slug=$product_slug;
						$transient=$this->add_update_availability($transient, $plugin_slug, $response);
					}

					/**
					*	Update last check time with current time
					*/
					update_option($product_slug.'-last-update-check', $timestamp);
				}			
			}
		}
		return $transient;
	}

	/**
	*	Fetch the details of the new update.
	*	This will show in the plugins page as a popup
	*/
	public function update_details($false, $action, $args)
	{		
		$this->products = $this->all_products();
		if(!isset($args->slug))
		{
			return $false;
		}

		/**
		*	Get licence info
		*/
		$licence_data=$this->get_licence_data($args->slug);
	
		if(!$licence_data) /* no licence exists */
		{
			return $false;
		}

		/**
		*	Check product exists
		*/
		if(!isset($this->products[$args->slug]))
		{
			return $false;
		}

		/**
		*	Get product info
		*/
		$product_data=$this->products[$args->slug];

		return $this->get_license_type_obj($licence_data)->update_details($this, $product_data, $licence_data, $false, $action, $args);
	}


	/**
	*	Add plugin update availability to transient 
	*/
	public function add_update_availability($transient, $plugin_slug, $response)
	{
		/* a compatibility fix */
		$plugin_file_name=("wt-woocommerce-invoice-addon" === $plugin_slug ? 'wt-woocommerce-invoice-addon' : $plugin_slug);

		$plugin_base_path="$plugin_slug/$plugin_file_name.php";
		if(is_plugin_active($plugin_base_path)) /* checks the plugin is active */
		{
			$current_plugin_data=get_plugin_data(WP_PLUGIN_DIR."/$plugin_base_path");
			$current_version=$current_plugin_data['Version'];
			$new_version=$response->new_version;
			if(version_compare($new_version, $current_version, '>')) /* new version available */
			{
				$obj 									= new stdClass();
				$obj->slug 								= $plugin_slug;
				$obj->plugin 							= $plugin_base_path;
				$obj->new_version 						= $new_version;
				$obj->url 								= (isset($response->url) ? $response->url : '');
				$obj->package 							= (isset($response->package) ? $response->package : '');
				$obj->icons 							= (isset($response->icons) ? maybe_unserialize($response->icons) : array());
				$transient->response[$plugin_base_path] = $obj;
			}
		}

		return $transient;
	}

	/**
	*	Fetch plugin info for update check and update info
	*/
	public function fetch_plugin_info($args)
	{
		$request=$this->remote_get($args);

		if(is_wp_error($request) || wp_remote_retrieve_response_code($request)!==200)
		{
			return false;
		}

		if(isset($args['api_key'])) //WC type. In EDD `license` instead of `api_key`
		{
			$response=maybe_unserialize(wp_remote_retrieve_body($request));
		}else
		{
			$response=json_decode(wp_remote_retrieve_body($request));
		}
				
		if(is_object($response))
		{
			return $response;
		}else
		{
			return false;
		}
	}

	/**
	*	Ajax sub function to check licence status
	*/
	public function check_status($out)
	{	
		$this->products = $this->all_products();
		$licence_data_arr=$this->get_licence_data();
		
		foreach($licence_data_arr as $product_slug => $licence_data)
		{
			if(isset($this->products[$product_slug])) /* product currently exists */
			{
				$product_data=$this->products[$product_slug];
				$response=$this->fetch_status($product_data, $licence_data);
				$response_arr=json_decode($response, true);
						
				
				$new_status=$this->get_license_type_obj($licence_data)->check_status($licence_data, $response_arr);

				/* check update needed */
				if($licence_data['status'] !== $new_status)
				{
					$licence_data['status']=$new_status;
					$this->update_licence_data($product_slug, $licence_data);
				}
			}
		}

		$out['status']=true;
		return $out;		
	}

	/**
	*	Fetch licence status
	*/
	public function fetch_status($product_data, $licence_data)
	{
		$args = array(
			'edd_action' 	=> 'check_license',
			'license'		=> $licence_data['key'], 
			'item_id' 		=> (isset($product_data['product_edd_id']) ? $product_data['product_edd_id'] : 0),
			'url' 			=> urlencode(home_url()),
		);

		$request=$this->remote_get($args);
		
		$response = wp_remote_retrieve_body($request);

		return $response;
	}

	/**
	*	Ajax sub function to delete licence
	*/
	public function delete($out)
	{	
		$this->products = $this->all_products();

		$out['status']=false;
		$er=0;

		$licence_product=trim(isset($_POST['wt_pklist_licence_product']) ? sanitize_text_field($_POST['wt_pklist_licence_product']) : '');
		if("" === $licence_product)
		{
			$er=1;
			$out['msg']=__('Error !!!', 'wt_woocommerce_invoice_addon');
		}else
		{
			if(!isset($this->products[$licence_product]))
			{
				$er=1;
				$out['msg']=__('Error !!!', 'wt_woocommerce_invoice_addon');
			}
		}
		if(0 === $er)
		{
			$this->remove_licence_data($licence_product);
            $out['status']=true;
			$out['msg']=__("Successfully deleted.", 'wt_woocommerce_invoice_addon');
		}

		return $out;
	}


	/**
	*	Ajax sub function to deactivate licence
	*/
	public function deactivate($out)
	{

		$this->products = $this->all_products();
		$out['status']=false;
		$er=0;

		$licence_product=trim(isset($_POST['wt_pklist_licence_product']) ? sanitize_text_field($_POST['wt_pklist_licence_product']) : '');
		if("" === $licence_product)
		{
			$er=1;
			$out['msg']=__('Error !!!', 'wt_woocommerce_invoice_addon');
		}else
		{
			if(!isset($this->products[$licence_product]))
			{
				$er=1;
				$out['msg']=__('Error !!!', 'wt_woocommerce_invoice_addon');
			}
		}

		if(0 === $er)
		{
			$licence_data=$this->get_licence_data($licence_product);
			if(!$licence_data)
			{
				$er=1;
				$out['msg']=__('Error !!!', 'wt_woocommerce_invoice_addon');
			}
		}

		$product_data=$this->products[$licence_product];
		if(0 === $er)
		{
			$args=array(
				'edd_action'	=> 'deactivate_license',
				'license'		=> $licence_data['key'],
				//'item_name' 	=> $product_data['product_display_name'], //name in EDD
				'item_id' 		=> (isset($product_data['product_edd_id']) ? $product_data['product_edd_id'] : 0), //ID in EDD
				'url' 			=> urlencode(home_url()),
			);

			$response=$this->remote_get($args);
			
			if(is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response))
			{
				$out['msg']=__("Request failed, Please try again", 'wt_woocommerce_invoice_addon');
			}else
	        {
	        	$response=json_decode(wp_remote_retrieve_body($response), true);
	        	$success=false;
	        	
	        	if(isset($response['success']) && true === $response['success'])
	        	{
	        		$success=true;
	        	}

		        if($success)
		        {
		        	$this->remove_licence_data($licence_product);
		        	$this->check_if_license_activated($licence_product,$this->products[$licence_product]);
		            $out['status']=true;
					$out['msg']=__("Successfully deactivated.", 'wt_woocommerce_invoice_addon'); 
		        }else
		        {
		        	$out['msg']=__('Error', 'wt_woocommerce_invoice_addon');
		        }

	        }
		}
		return $out;
	}

	public function remote_get($args)
	{
		global $wp_version;
		$target_url=esc_url_raw($this->create_api_url($args));

		$def_args = array(
		    'timeout'     => 5,
		    'redirection' => 5,
		    'httpversion' => '1.0',
		    'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
		    'blocking'    => true,
		    'headers'     => array(),
		    'cookies'     => array(),
		    'body'        => null,
		    'compress'    => false,
		    'decompress'  => true,
		    'sslverify'   => false,
		    'stream'      => false,
		    'filename'    => null
		);
		return wp_remote_get($target_url, $def_args);
	}

	/**
	*	Ajax sub function to activate licence
	*/
	public function activate($out)
	{
		global $wp_version;
		$all_products = $this->all_products();
		$this->products = $all_products;
		$out['status']=false;
		$er=0;

		$licence_product=trim(isset($_POST['wt_pklist_licence_product']) ? sanitize_text_field($_POST['wt_pklist_licence_product']) : '');
		$licence_key=trim(isset($_POST['wt_pklist_licence_key']) ? sanitize_text_field($_POST['wt_pklist_licence_key']) : '');
		$licence_email=trim(isset($_POST['wt_pklist_licence_email']) ? sanitize_text_field($_POST['wt_pklist_licence_email']) : '');

		if("" === $licence_product)
		{
			$er=1;
			$out['msg']=__('Please select a product', 'wt_woocommerce_invoice_addon');
		}else
		{
			if(!isset($this->products[$licence_product]))
			{
				$er=1;
				$out['msg']=__('Invalid product', 'wt_woocommerce_invoice_addon');
			}
		}
		if(0 === $er && "" === $licence_key)
		{
			$er=1;
			$out['msg']=__('Please enter Licence key', 'wt_woocommerce_invoice_addon');
		}
		if(0 === $er && "" !== $licence_key)
		{
			/* check the licence key already applied */
			$licence_data=$this->get_licence_data();
			foreach ($licence_data as $product_slug => $licence_info)
			{
				if($product_slug==$licence_product) /* already one licence exists */
				{
					if("active" === $licence_info['status'])
					{
						$er=1;
						$out['msg']=__('The chosen plugin already has an active licence.', 'wt_woocommerce_invoice_addon');
						break;
					}
				}

				/* current licence key matches with another product */
				if($licence_key === $licence_info['key'] && $product_slug !== $licence_product && $licence_info['status'] === 'active')
				{
					$er=1;
					$out['msg']=__('This licence key has already been activated for another product. Please provide another licence key.', 'wt_woocommerce_invoice_addon');
					break;
				}
			}
		}

		if($er==0)
		{
			$product_data=$this->products[$licence_product];
			$args = array(
				'edd_action'		=> 'activate_license',
				'license'			=> $licence_key,
				//'item_name' 		=> $product_data['product_display_name'], //name in EDD
				'item_id' 			=> (isset($product_data['product_edd_id']) ? $product_data['product_edd_id'] : 0), //ID in EDD
				'url' 				=> urlencode(home_url()),
			);
			$response=$this->remote_get($args);

			// Request failed
			if(is_wp_error($response))
			{
				$out['msg']=$response->get_error_message();
			}
			elseif( 200 !== wp_remote_retrieve_response_code( $response ) )
			{
				$out['msg']=__("Request failed, Please try again", 'wt_woocommerce_invoice_addon');
			}
	        else
	        {	        	
	        	$response_arr=json_decode($response['body'], true);
		        if(isset($response_arr['success']) && true === $response_arr['success']) /* success */
	        	{
        			$licence_data=array(
						'key'			=> $licence_key,
						'email'			=> (isset($response_arr['customer_email']) ? sanitize_text_field($response_arr['customer_email']) : ''), //from EDD
						'status'		=> 'active',
						'products'		=> $product_data['product_display_name'], 
						'instance_id'	=> (isset($response_arr['checksum']) ? sanitize_text_field($response_arr['checksum']) : ''), //from EDD
					);						
					$out['status']=true;	        		
	        	}

	        	if(!$out['status']) /* error */
	        	{	
	        		$out['msg']=$this->process_error_keys( (isset($response_arr['error']) ? $response_arr['error'] : '') );
	        	}

		        if(true === $out['status']) /* success. Save license info */
		        {
		        	$this->add_new_licence_data($licence_product, $licence_data);
		        	$this->set_error_message(false,$licence_product,$this->products[$licence_product]);
		        	$out['msg']=__("Successfully activated.", 'wt_woocommerce_invoice_addon');
		        }

	        }
		}
		return $out;
	}

	/**
	*	Mask licence key
	*/
	public function mask_licence_key($key)
	{
		$total_length=strlen($key);
		$non_mask_length=6; //including both side
		$mask_length=$total_length-$non_mask_length;
		
		if($mask_length>=1) //atleast one character
		{
			$key=substr_replace($key, str_repeat("*", $mask_length), floor($non_mask_length/2), ($total_length-$non_mask_length));
		}else
		{
			$key=str_repeat("*", $total_length); //replace all character
		}
		return $key;		
	}

	public function get_status_label($status)
	{
		$color_arr=array(
			'active'=>'#37eb37',
			'inactive'=>'#ccc',
		);
		$color_css=(isset($color_arr[$status]) ? 'background:'.$color_arr[$status].';' : '');
		return '<span class="wt_pklist_badge" style="'.$color_css.'padding: 5px;border-radius: 5px;">'.ucfirst($status).'</span>';
	}

	public function get_display_name($product_slug)
	{	
		$this->products = $this->all_products();

		if(isset($this->products[$product_slug]))
		{
			return $this->products[$product_slug]['product_display_name'];
		}
		return '';
	}

	private function create_api_url($args)
	{
		return urldecode(add_query_arg($args, $this->api_url));	
	}

	/**
	*	Add new licence info
	*/
	private function add_new_licence_data($product_slug, $licence_data)
	{
		update_option($product_slug.'_licence_data', $licence_data);
	}

	private function remove_licence_data($product_slug)
	{
		delete_option($product_slug.'_licence_data');
	}

	private function update_licence_data($product_slug, $licence_data)
	{
		update_option($product_slug.'_licence_data', $licence_data);
	}

	/**
	*	Check the licence type is EDD or WC
	*/
	private function get_license_type_obj($licence_data)
	{
		return Wf_Woocommerce_Packing_List_Licence_Manager_Edd::get_instance();
	}

	private function process_error_keys($key)
	{
		$msg_arr=array(
			"missing" => __("License doesn't exist", 'wt_woocommerce_invoice_addon'),
			"missing_url" => __("URL not provided", 'wt_woocommerce_invoice_addon'),
			"license_not_activable" => __("Attempting to activate a bundle's parent license", 'wt_woocommerce_invoice_addon'),
			"disabled" => __("License key revoked", 'wt_woocommerce_invoice_addon'),
			"no_activations_left" => __("No activations left", 'wt_woocommerce_invoice_addon'),
			"expired" => __("License has expired", 'wt_woocommerce_invoice_addon'),
			"key_mismatch" => __("License is not valid for this product", 'wt_woocommerce_invoice_addon'),
			"invalid_item_id" => __("Invalid Product", 'wt_woocommerce_invoice_addon'),
			"item_name_mismatch" => __("License is not valid for this product", 'wt_woocommerce_invoice_addon'),
		);
		return (isset($msg_arr[$key]) ? $msg_arr[$key] : __("Error", 'wt_woocommerce_invoice_addon'));
	}

	public function add_license_notification( $file, $plugin ) {

		$all_products = $this->all_products();
		$r_pro_key = $this->main_plugin_slug;
		$r_pro_details = array();
		foreach($all_products as $pro_key => $pro_details){
			if($pro_details['product_name'] === $file){
				$r_pro_key = $pro_key;
				$r_pro_details = $pro_details;
				break;
			}
		}

		if(empty($all_products)){
			return;
		}
		if ( is_network_admin() ) {
			return;
		}
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		if ( ! $this->get_last_error_message($r_pro_key,$r_pro_details) ) {
			return;
		}

		$license_data = $this->get_license_data_for_checking($r_pro_key,$r_pro_details);
		
		if ( 'active' !== $license_data['status'] ) {
			echo '<tr class="plugin-update-tr installer-plugin-update-tr wt-cli-plugin-inline-notice-tr">
                <td colspan="4" class="plugin-update colspanchange">
                    <div class="update-message notice inline wt-plugin-notice-section">
                        <p>' . $this->get_last_error_message($r_pro_key,$r_pro_details) . '</p>
                        </div>
                </td>
            </tr>';
		}
	}

	public function check_if_license_activated($pro_key="",$pro_details=array()){
		$license_data = $this->get_license_data_for_checking($pro_key,$pro_details);
		$status = true;
		$licence_data_url = admin_url('admin.php?page='.WF_PKLIST_POST_TYPE.'#wt-licence');
		if ( ('' === $license_data['status']) || (false === $license_data['status'])) {
			$message = sprintf( __('The plugin license is not activated. You will not receive compatibility and security updates if the plugin license is not activated. <a href="%s" target="_blank">Activate now</a>','wt_woocommerce_invoice_addon'), $licence_data_url );
			$status  = false;
		} elseif ( 'inactive' === $license_data['status'] ) {
			$message = __('The product license has either expired or not been activated.','wt_woocommerce_invoice_addon');
			$status  = false;
		}
		if ( false === $status ) {
			$this->set_error_message($message,$pro_key,$pro_details);
		}
		return $status;
	}

	public function get_license_data_for_checking($pro_key="",$pro_details=array()) {
		$all_products = $this->all_products();
		$license   = array(
			'status'        => false,
			'licence_key'   => '',
			'instance_id'   => '',
			'licence_email' => '',
		);
		$pro_key = ("" !== $pro_key) ? $pro_key : $this->main_plugin_slug;
		$plugin_data = get_option($pro_key.'_licence_data',false);
		if(false !== $plugin_data){
			$license['status']        = sanitize_text_field($plugin_data['status']);
			$license['licence_key']   = sanitize_text_field($plugin_data['key'] );
			$license['instance_id']   = sanitize_text_field($plugin_data['instance_id'] );
			$license['licence_email'] = sanitize_text_field($plugin_data['email']);
		}
		return $license;
	}

	public function get_last_error_message($pro_key="",$pro_details=array()) {
		if ( ! $this->last_error_message ) {
			$error = get_transient($pro_key.'-license_last_error');
			if ( false !== $error ) {
				$error = wp_kses_post( $error );
			}
			$this->last_error_message = $error;
		}
		return $this->last_error_message;
	}

	public function set_error_message($message,$pro_key ="",$pro_details=array()) {
		if ( false === $message ) {
			delete_transient( $pro_key.'-license_last_error' );
			return;
		}
		$message = wp_kses_post( $message );
		set_transient( $pro_key.'-license_last_error', $message, 12 * HOUR_IN_SECONDS );
		$this->last_error_message = $message;
	}

	public function flush_errors($pro_key,$pro_details=array()) {
		$this->set_error_message( false,$pro_key,$pro_details);
	}
}
new Wf_Woocommerce_Packing_List_Licence_Manager();

}