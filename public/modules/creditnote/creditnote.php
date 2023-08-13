<?php
/**
 * Creditnote Pro section of the plugin
 *
 * @link       
 * @since 1.0.0     
 *
 * @package  Wt_woocommerce_invoice_addon  
 */
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Wf_Woocommerce_Packing_List_Creditnote_Pro')){

class Wf_Woocommerce_Packing_List_Creditnote_Pro 
{
    public $module_id='';
    public static $module_id_static='';
    public $module_base='creditnote';
    public $customizer=null;
    public $customizer_pro;

    public function __construct()
    {
        $this->module_id = Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
        self::$module_id_static = $this->module_id;

        // menu releated hooks
        add_filter('wt_admin_menu', array($this,'add_admin_pages'),10,1);

        // save settings related hooks
        add_filter('wf_module_default_settings',array($this,'default_settings'),10,2);
        add_filter('wf_module_single_checkbox_fields', array($this, 'single_checkbox_fields'), 10, 3);
        add_filter('wf_module_multi_checkbox_fields', array($this, 'multi_checkbox_fields'), 10, 3);
        add_filter('wt_pklist_intl_alter_validation_rule', array($this,'alter_validation_rule'), 10, 2);

        //initializing customizer       
        $this->customizer = Wf_Woocommerce_Packing_List::load_modules('customizer');
        $this->customizer_pro = new Wf_Woocommerce_Packing_List_Customizer_Ipc();
        add_filter('wt_pklist_pro_customizer_'.$this->module_base,array($this,'switch_to_pro_customizer'),10,2);
        add_filter('wt_pklist_enable_code_editor',array($this,'enable_code_editor_customizer'),10,2);
        add_filter('wf_module_convert_to_design_view_html_for_'.$this->module_base,array($this,'convert_to_design_view_html'),10,3);
        add_filter('wt_pklist_default_template_path_pro',array($this,'default_template_path_pro'),10,3);
        add_filter('wf_module_generate_template_html_for_creditnote',array($this,'generate_template_html'),10,6);

        add_filter('wf_module_customizable_items',array($this,'get_customizable_items'),10,2);
        add_filter('wf_module_non_options_fields',array($this,'get_non_options_fields'),10,2);
        add_filter('wf_module_non_disable_fields',array($this,'get_non_disable_fields'),10,2);
        add_filter('wf_pklist_alter_customize_inputs',array($this,'alter_customize_inputs'),10,3);

        add_filter('wt_email_attachments', array($this,'add_email_attachments'),10,4);
        add_filter('wt_print_docdata_metabox',array($this,'add_docdata_metabox'),10,3);
        add_filter('wt_print_actions', array($this,'add_print_buttons'),10,4);     

        add_action('wt_print_doc',array($this,'print_it'),10,2); 
        add_filter('wt_pklist_alter_tooltip_data', array($this, 'register_tooltips'), 1);

        /**
        * @since 1.0.1 Add to remote printing
        */
        add_filter('wt_pklist_add_to_remote_printing', array($this, 'add_to_remote_printing'), 10, 2);

        /**
        * @since 1.0.1 Do remote printing
        */
        add_filter('wt_pklist_do_remote_printing', array($this, 'do_remote_printing'), 10, 2);

        add_filter('wt_pklist_check_prompt_'.$this->module_base,array($this,'check_prompt_for_print_node_btn'),10,3);

        add_action('admin_init',array($this,'generate_creditnote_number_auto'));
    }
    
    /**
    *   @since 1.0.1
    *   Add to remote printing, this will enable remote printing settings
    */
    public function add_to_remote_printing($arr, $remote_print_vendor)
    {
        $arr[$this->module_base]=__('Credit note', 'wt_woocommerce_invoice_addon');
        return $arr;
    }

    /**
    *   @since 1.0.1
    *   Do remote printing.
    */
    public function do_remote_printing($module_base_arr, $order_id)
    {
        return Wt_woocommerce_invoice_addon_Admin::do_remote_printing($module_base_arr, $order_id, $this);
    }

    public function check_prompt_for_print_node_btn($is_show_prompt,$order,$template_type){
        if($this->module_base === $template_type){
            $creditnote_number = self::generate_creditnote_number($order,false);
            $is_show_prompt = 0;
            $refunds = $order->get_refunds();
            if(!empty($refunds)){
                foreach($refunds as $ref_order)
                {
                    $creditnote_number = self::generate_creditnote_number($ref_order, false);
                    if("" === $creditnote_number){
                        $is_show_prompt = 11;
                        break;
                    }
                }
            }
        }
        return $is_show_prompt;
    }
    public function register_tooltips($tooltip_arr)
    {
        include(plugin_dir_path( __FILE__ ).'data/data.tooltip.php');
        $tooltip_arr[$this->module_id]=$arr;
        return $tooltip_arr;
    }

    /**
     * To add the creditnote menu under the invoice/packing
     * @since 1.0.0
     * 
     */  
    public function add_admin_pages($menus)
    {
        $menus[]=array(
            'submenu',
            WF_PKLIST_POST_TYPE,
            __('Credit Note','wt_woocommerce_invoice_addon'),
            __('Credit Note','wt_woocommerce_invoice_addon'),
            'manage_woocommerce',
            $this->module_id,
            array($this,'admin_settings_page')
        );
        return $menus;
    }

    /**
     * Callback function for adding the creditnote settings page
     * 
     * @since 1.0.0
     */
    public function admin_settings_page()
    {
        wp_enqueue_script('wc-enhanced-select');
        wp_enqueue_style('woocommerce_admin_styles',WC()->plugin_url().'/assets/css/admin.css');
        wp_enqueue_media();
        if(class_exists('Wf_Woocommerce_Packing_List_Pro_Common_Func')){
            wp_enqueue_script($this->module_id.'_seq_pro',WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'includes/js/sequential_number.js',array('jquery'),WT_PKLIST_INVOICE_ADDON_VERSION);
        }
        $params=array(
            'nonces' => array(
                'main'=>wp_create_nonce($this->module_id),
            ),
            'ajax_url' => admin_url('admin-ajax.php'),
        );
        wp_localize_script($this->module_id,$this->module_id,$params);

        //initializing necessary modules, the argument must be current module name/folder
        if(!is_null($this->customizer) && true === apply_filters('wt_pklist_switch_to_classic_customizer_'.$this->module_base, true, $this->module_base))
        {
            $this->customizer->init($this->module_base);
        }

        include(plugin_dir_path( __FILE__ ).'views/admin-settings.php');
    }

    public function default_settings($settings,$base_id)
    {
        if($base_id === $this->module_id)
        {
            $settings = array(
                'woocommerce_wf_generate_for_orderstatus'   => array('wc-refunded'),
                'woocommerce_wf_add_creditnote_in_mail'     => 'Yes',
                'sort_products_by'                          => '',
                'woocommerce_wf_packinglist_variation_data' => 'No',
                'woocommerce_wf_Current_Invoice_number'     => 1,
                'woocommerce_wf_invoice_start_number'       => 1,
                'woocommerce_wf_invoice_number_prefix'      => '',
                'woocommerce_wf_invoice_padding_number'     => 0,
                'woocommerce_wf_invoice_number_postfix'     => '',
                'woocommerce_wf_invoice_as_ordernumber'     => "Yes",
                'woocommerce_wf_orderdate_as_invoicedate'   => "Yes",
                'wf_'.$this->module_base.'_contactno_email' => array('email','contact_number'),
                'wf_'.$this->module_base.'_product_meta_fields'         => array(),
                'wt_'.$this->module_base.'_product_attribute_fields'    => array(),
                'woocommerce_wf_invoice_number_format'      => "[number]",
            );
        }
        return $settings;
    }

    public function single_checkbox_fields($settings,$base_id,$tab_name)
    {
        if($base_id === $this->module_id){
            $settings['wt_creditnote_general']['woocommerce_wf_packinglist_variation_data'] = "No";
            $settings['wt_creditnote_general']['woocommerce_wf_add_creditnote_in_mail']     =  "No";
        }
        return $settings;
    } 

    public function multi_checkbox_fields($settings,$base_id,$tab_name){
        if($base_id === $this->module_id){
            $settings['wt_creditnote_general']['wf_'.$this->module_base.'_contactno_email'] = array();
            $settings['wt_creditnote_general']['wf_'.$this->module_base.'_product_meta_fields'] = array();
            $settings['wt_creditnote_general']['wt_'.$this->module_base.'_product_attribute_fields'] = array();
            $settings['wt_creditnote_general']['woocommerce_wf_generate_for_orderstatus'] = array();
        }
        return $settings;
    }

    public function alter_validation_rule($arr, $base_id)
    {
        if($base_id === $this->module_id){
            $arr['woocommerce_wf_generate_for_orderstatus']             = array('type'=>'text_arr');
            $arr['wf_'.$this->module_base.'_contactno_email']           = array('type' => 'text_arr');
            $arr['wf_'.$this->module_base.'_product_meta_fields']       = array('type'=>'text_arr');
            $arr['wt_'.$this->module_base.'_product_attribute_fields']  = array('type'=>'text_arr');
        }
        return $arr;
    }

    public function default_template_path_pro($path,$template_type,$type){
        if($template_type === $this->module_base){
            $path = ("path" === $type) ? plugin_dir_path(WT_PKLIST_INVOICE_ADDON_FILENAME) : plugin_dir_url(WT_PKLIST_INVOICE_ADDON_FILENAME);
            if(Wt_woocommerce_invoice_addon_Public::module_exists($template_type))
            {
                $path .= 'public/';
            }
            $path .= "modules/$template_type/data/";
            if("path" === $type)
            {
                $path .= "data.templates.php";
                if(file_exists($path))
                {
                    return $path;
                }
            }else
            {
                return $path;   
            }
        }
        return $path;
    }

    public function switch_to_pro_customizer($enabled,$template_type){
        if($template_type === $this->module_base){
            return true;
        }else{
            return false;
        }
    }

    public function enable_code_editor_customizer($enabled,$template_type){
        if($template_type === $this->module_base){
            return true;
        }
        return $enabled;
    }

    public function get_customizable_items($settings,$base_id)
    {
        if($base_id === $this->module_id)
        {
            //these fields are the classname in template Eg: `company_logo` will point to `wfte_company_logo`
            return array(
                'doc_title'                     => __('Document title','wt_woocommerce_invoice_addon'),
                'company_logo'                  => __('Company Logo','wt_woocommerce_invoice_addon'),
                'order_number'                  => __('Order Number','wt_woocommerce_invoice_addon'),
                'order_date'                    => __('Order Date','wt_woocommerce_invoice_addon'),
                'creditnote_number'             => __('Credit note number','wt_woocommerce_invoice_addon'),
                'creditnote_date'               => __('Credit note Date','wt_woocommerce_invoice_addon'),
                'invoice_number'                => __('Invoice Number','wt_woocommerce_invoice_addon'),       
                'from_address'                  => __('From Address','wt_woocommerce_invoice_addon'),
                'billing_address'               => __('Billing Address','wt_woocommerce_invoice_addon'),
                'shipping_address'              => __('Shipping Address','wt_woocommerce_invoice_addon'),
                'return_address'                => __('Return Address','wt_woocommerce_invoice_addon'),
                'email'                         => __('Email Field','wt_woocommerce_invoice_addon'),
                'tel'                           => __('Tel Field','wt_woocommerce_invoice_addon'),
                'vat_number'                    => __('VAT Field','wt_woocommerce_invoice_addon'),
                'ssn_number'                    => __('SSN Field','wt_woocommerce_invoice_addon'),
                'customer_note'                 => __('Customer Note','wt_woocommerce_invoice_addon'),
                'shipping_method'               => __('Shipping Method','wt_woocommerce_invoice_addon'),
                'tracking_number'               => __('Tracking Number','wt_woocommerce_invoice_addon'),
                'product_table'                 => __('Product Table','wt_woocommerce_invoice_addon'),
                'product_table_subtotal'        => __('Subtotal','wt_woocommerce_invoice_addon'),
                'product_table_shipping'        => __('Shipping','wt_woocommerce_invoice_addon'),
                'product_table_tax_item'        => __('Tax items','wt_woocommerce_invoice_addon'),
                'product_table_total_tax'       => __('Total Tax','wt_woocommerce_invoice_addon'),
                'product_table_fee'             => __('Fee','wt_woocommerce_invoice_addon'),
                'product_table_payment_total'   => __('Total','wt_woocommerce_invoice_addon'),
                'footer'                        => __('Footer','wt_woocommerce_invoice_addon'),
                'return_policy'                 => __('Return Policy','wt_woocommerce_invoice_addon'),
            );
        }
        return $settings;
    }

    /*
    * These are the fields that have no customizable options, Just on/off
    * 
    */
    public function get_non_options_fields($settings,$base_id)
    {
        if($base_id === $this->module_id)
        {
            return array(
                'footer',
                'return_policy',
                'product_table_tax_item',
            );
        }
        return $settings;
    }

    /*
    * These are the fields that are not switchable
    * 
    */
    public function get_non_disable_fields($settings,$base_id)
    {
        if($base_id === $this->module_id)
        {
            return array(
                
            );
        }
        return $settings;
    }

    public function alter_customize_inputs($fields,$type,$template_type){
        if($template_type === $this->module_base)
        {
            if("product_table" === $type){
                foreach($fields as $k => $f_val){
                    if(is_array($f_val) && isset($f_val['trgt_elm']) && "product_table_head_tax" === $f_val['trgt_elm']){
                        unset($fields[$k]);
                    }
                }
                $new_asset_arr = array(
                    array(
                        'label'         => '&nbsp;',
                        'type'          => 'checkbox',
                        'trgt_elm'      => 'product_table_head_tax_items',
                        'event_class'   => 'wf_cst_toggler',
                        'width'         => '10%',
                    ),
                    array(
                        'label'         => __('Tax items text align','wt_woocommerce_invoice_addon'),
                        'type'          => 'select',
                        'select_options'=> Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_customizer_presets('text-align'),
                        'css_prop'      => 'text-align',
                        'trgt_elm'      => 'product_table_head_tax_items',
                        'event_class'   => 'wf_cst_change',
                        'width'         => '90%',
                        'float'         => 'left',
                    ),
                    array(
                        'label'         => '&nbsp;',
                        'type'          => 'checkbox',
                        'trgt_elm'      => 'product_table_head_tax',
                        'event_class'   => 'wf_cst_toggler',
                        'width'         => '10%',
                    ),
                    array(
                        'label'         => __('Total Tax label','wt_woocommerce_invoice_addon'),
                        'css_prop'      => 'html',
                        'trgt_elm'      => 'product_table_head_tax',
                        'width'         => '44%',
                    ),
                    array(
                        'label'         => __('Total Tax text align','wt_woocommerce_invoice_addon'),
                        'type'          => 'select',
                        'select_options'=> Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_customizer_presets('text-align'),
                        'css_prop'      => 'text-align',
                        'trgt_elm'      => 'product_table_head_tax',
                        'event_class'   => 'wf_cst_change',
                        'width'         => '44%',
                        'float'         => 'right',
                    ),
                );

                $fields = (array_merge($fields,$new_asset_arr));
            
            }elseif("product_table_tax_items" === $type){
                $summary_tax_items_fields = array(
                    array(
                        'label'         => __('Tax items value','wt_woocommerce_invoice_addon'),
                        'type'          => 'select',
                        'select_options'=> Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_customizer_presets('ind-tax-summay'),
                        'css_prop'      => 'attr-data-ind-tax-summary',
                        'trgt_elm'      => 'product_table_tax_item',
                        'event_class'   => 'wf_cst_change',
                        'width'         => '90%',
                        'float'         => 'left',
                    ),
                );
                $fields = array_merge($fields,$summary_tax_items_fields);
            }
                
        }
        return $fields;
    }

    /**
     *  Items needed to be converted to design view
     */
    public function convert_to_design_view_html($find_replace,$html,$template_type)
    {
        if($template_type==$this->module_base)
        {   
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_logo($find_replace,$template_type,$html);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_from_address($find_replace,$template_type,$html);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_billing_address($find_replace, $template_type, $html);     
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_address($find_replace,$template_type, $html);   
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_default_order_fields($find_replace,$template_type, $html);  
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_product_table($find_replace,$template_type,$html);     
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_extra_charge_fields($find_replace,$template_type,$html);       
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_other_data($find_replace,$template_type,$html);
            $find_replace = $this->set_design_view_data($find_replace, $html, $template_type);
        }
        return $find_replace;
    }

    public function set_design_view_data($find_replace, $html, $template_type)
    {
        $find_replace = $this->set_refund_entries($find_replace, $html, $template_type, null);
        $find_replace['[wfte_creditnote_number]']=123456;
        return $find_replace;
    }

    public function add_bulk_print_buttons($actions)
    {
        $actions['print_creditnote'] = __('Print Credit Note','wt_woocommerce_invoice_addon');
        return $actions;
    }

    /**
     *  To add the print and download button in the meta box section of order details page
     *  @since 1.0.0
     *  @since 1.0.1    Added the print and download button to create the creditnote manually, if there is any refunds
     */
    private function generate_print_button_data($order, $order_id, $button_location="detail_page")
    {
        $creditnote_number = self::generate_creditnote_number($order,false);
        if(!empty($creditnote_number)){
            $creditnote_number_exists = true;
        }else{
            $creditnote_number_exists = false;
        }
        $generate_creditnote_for = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus',$this->module_id);
        $is_show_prompt = 0;
        $refunds = $order->get_refunds();
        if(!empty($refunds)){
            foreach($refunds as $ref_order)
            {
                $creditnote_number = self::generate_creditnote_number($ref_order, false);
                if("" === $creditnote_number){
                    $is_show_prompt = 11;
                    break;
                }
            }
        }

        if("detail_page" === $button_location)
        {
            $args=array(
                'button_type'       => 'aggregate',
                'button_key'        => 'creditnote_actions', //unique if multiple on same page
                'button_location'   => $button_location,
                'action'            => '',
                'label'             => __('Credit Note','wt_woocommerce_invoice_addon'),
                'tooltip'           => __('Print/Download Credit Note','wt_woocommerce_invoice_addon'),
                'is_show_prompt'    => 0, //always 0
                'items'             => array(
                    'print_creditnote' => array(  
                        'action'    => 'print_creditnote',
                        'label'     => __('Print','wt_woocommerce_invoice_addon'),
                        'tooltip'   => __('Print Credit Note','wt_woocommerce_invoice_addon'),
                        'is_show_prompt' => $is_show_prompt,
                        'button_location' => $button_location,                        
                    ),
                    'download_creditnote' => array(
                        'action'    => 'download_creditnote',
                        'label'     => __('Download','wt_woocommerce_invoice_addon'),
                        'tooltip'   => __('Download Credit Note','wt_woocommerce_invoice_addon'),
                        'is_show_prompt' => $is_show_prompt,
                        'button_location'=> $button_location,
                    )
                ),
                'exist' => $creditnote_number_exists,
            );
        }else
        {
            $args=array(
                'action'            => 'print_creditnote',
                'label'             => __('Credit Note','wt_woocommerce_invoice_addon'),
                'tooltip'           => __('Print Credit Note','wt_woocommerce_invoice_addon'),
                'is_show_prompt'    => $is_show_prompt,
                'button_location'   => $button_location,
            );
        }
        return $args;
    }

    public function add_print_buttons($item_arr, $order, $order_id, $button_location)
    {   
        $refunds=$order->get_refunds();

        if("" !== self::generate_creditnote_number($order, false))
        {
            $btn_data = $this->generate_print_button_data($order, $order_id, $button_location);
            if($btn_data)
            {
                if($button_location === "detail_page"){
                    $item_arr['creditnote_details_actions'] = $btn_data;
                }else{
                    $item_arr[] = $btn_data;
                }
            }
        }else
        {
            if($refunds) //refund data exists but creditnote number not generated.
            {
                //generate credit note number
                $generate_creditnote_for =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus',$this->module_id);
                $force_generate = in_array('wc-'.$order->get_status(), $generate_creditnote_for) ? true : false;
                self::generate_creditnote_number($order, $force_generate);
                $btn_data = $this->generate_print_button_data($order, $order_id, $button_location);
                if($btn_data)
                {
                    if($button_location === "detail_page"){
                        $item_arr['creditnote_details_actions'] = $btn_data;
                    }else{
                        $item_arr[] = $btn_data;
                    }
                }
            }
        }
        /**
        *   @since 1.0.0
        *   Alter button array just after adding buttons.
        *   We are specifying `module_base` as an argument to use common callback when needed
        */
        if($refunds){
            $item_arr = apply_filters('wt_pklist_after_'.$this->module_base.'_print_button_list', $item_arr, $order, $button_location, $this->module_base);
        }
        return $item_arr;
    }

    /**
    *   Print credit note number details in meta box section of order details page
    *   @since 1.0.0
    *   @since 1.0.1    Added an option to create the credit note automatically for chosen order statuses only
    *                   For others, User can create the credit notes manually
    */
    public function add_docdata_metabox($data_arr, $order, $order_id)
    {
        $refunds=$order->get_refunds();
        if($refunds)
        {
            $generate_creditnote_for =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus',$this->module_id);
            $force_generate = in_array('wc-'.$order->get_status(), $generate_creditnote_for) ? true : false;
            if(Wf_Woocommerce_Packing_List_Public::module_exists('invoice'))
            {
                Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order,$force_generate);
            }
            //dummy array
            $data_arr[]=array(
                'label' => '',
                'value' => '',
            );
            
            foreach($refunds as $ref_order)
            {
                $creditnote_number  = self::generate_creditnote_number($ref_order, $force_generate);
                if("" !== $creditnote_number){
                    $data_arr[]         = array(
                        'label' => __('Credit Note Number','wt_woocommerce_invoice_addon'),
                        'value' => $creditnote_number,
                    );
                    $ref_id             = (WC()->version < '2.7.0') ? $ref_order->id : $ref_order->get_id();
                    $creditnote_date    = self::get_creditnote_date($ref_id, get_option( 'date_format' ), $ref_order);
                    $data_arr[]         =array(
                        'label' => __('Credit Note Date','wt_woocommerce_invoice_addon'),
                        'value' => $creditnote_date,
                    );
                }
            }
        }
        return $data_arr;
    }

    /**
     * 
     * @since 1.0.1 Create credit notes for the chosen order statuses automatically
     * 
     * @since 1.0.3 - Added HPOS Compatibility when comes to the order edit screen
     */
    public function generate_creditnote_number_auto(){
        global $pagenow, $typenow, $post;
        $do_create_creditnote = false;
        $order_id = "";
        if(Wt_Pklist_Common_Ipc::is_wc_hpos_enabled()){
			// order edit page will have these url parameters
			if('admin.php' === $pagenow && isset($_GET['page']) && "wc-orders" === $_GET['page'] && isset($_GET['action']) && 'edit' === $_GET['action'] && isset($_GET['id'])){
				$do_create_creditnote = true;
                $order_id = (int)$_GET['id'];
			}
		}else{
            if('post.php' === $pagenow){
                if ('post' === $typenow && isset($_GET['post']) && !empty($_GET['post'])) {
                    $req_type = $post->post_type;
                    $order_id = $post->ID; 
                } elseif (empty($typenow) && !empty($_GET['post'])) {
                    $post = get_post($_GET['post']);
                    $req_type = $post->post_type;
                    $order_id = $post->ID;
                }

                if("shop_order" === $req_type){
                    $do_create_creditnote = true;
                }
            }
        }
        

        if($do_create_creditnote && "" !== $order_id){
            $order = ( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);
            $refunds = $order->get_refunds();
            if(!empty($refunds)) //refund data exists but creditnote number not generated.
            {
                $generate_creditnote_for =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus',$this->module_id);
                $force_generate = in_array('wc-'.$order->get_status(), $generate_creditnote_for) ? true : false;
                if(Wf_Woocommerce_Packing_List_Public::module_exists('invoice'))
                {
                    Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order,$force_generate);
                }
                self::generate_creditnote_number($order, $force_generate);
            }
        }
    }

    public static function get_creditnote_date($order_id, $date_format, $order, $recent=false)
    {
        if(class_exists('Wf_Woocommerce_Packing_List_Sequential_Number'))
        {
            if(is_a($order, 'WC_Order'))
            {
                $creditnote_date_arr=array();
                foreach ($order->get_refunds() as $ref_order) 
                {
                    $ref_id = (WC()->version < '2.7.0') ? $ref_order->id : $ref_order->get_id();
                    $creditnote_date_arr[]  = self::get_creditnote_date($ref_id, $date_format, $ref_order, $recent);
                    if($recent){
                        break;
                    }
                }
                $creditnote_date_arr    = array_filter($creditnote_date_arr);
                return implode(", ",$creditnote_date_arr);
            }else
            {
                return Wf_Woocommerce_Packing_List_Sequential_Number::get_sequential_date($order_id, 'wf_creditnote_date', $date_format, $order);
            }
        }else
        {
            return '';
        }
    }

    public static function generate_creditnote_number($order, $force_generate=true, $recent=false) 
    {
        if(class_exists('Wf_Woocommerce_Packing_List_Sequential_Number'))
        {
            if(is_a($order, 'WC_Order'))
            {
                $creditnote_num_arr = array();
                foreach ($order->get_refunds() as $ref_order) 
                {
                    $creditnote_num_arr[]   = self::generate_creditnote_number($ref_order, $force_generate);
                    if($recent){
                        break;
                    }
                }
                $creditnote_num_arr=array_filter($creditnote_num_arr);
                return implode(", ",$creditnote_num_arr);
            }else
            {
                return Wf_Woocommerce_Packing_List_Sequential_Number::generate_sequential_number($order, self::$module_id_static, array('number'=>'wf_creditnote_number', 'date'=>'wf_creditnote_date', 'enable'=>''), $force_generate);
            }
        }else
        {
            return '';
        }
    }

    /**
     *  Items needed to be converted to HTML for print/download
     */
    public function generate_template_html($find_replace,$html,$template_type,$order,$refund_order,$refund_id,$box_packing=null,$order_package=null)
    {
        if($template_type === $this->module_base)
        {
            //Generate invoice number while printing Credit note
            if(Wf_Woocommerce_Packing_List_Public::module_exists('invoice'))
            {
                Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order);
            }           
            $find_replace = $this->set_other_data($find_replace,$template_type,$html,$order,$refund_order,$refund_id);

            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_billing_address($find_replace, $template_type, $html,$order);      
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_address($find_replace,$template_type, $html,$order);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_default_order_fields($find_replace,$template_type, $html,$order);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_product_table_creditnote($find_replace,$template_type,$html,$order,$refund_id,$refund_order);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_extra_charge_fields_creditnote($find_replace,$template_type,$html,$order,$refund_id,$refund_order);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_other_data($find_replace,$template_type,$html,$order);     
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_order_data($find_replace,$template_type,$html,$order);     
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_extra_fields($find_replace,$template_type,$html,$order);           
        }
        return $find_replace;
    }

    public function set_other_data($find_replace, $template_type, $html, $order,$refund_order,$refund_id)
    {   
        $wc_version = WC()->version;
        add_filter('wf_pklist_alter_item_quantiy', array($this, 'alter_quantity_column'), 1, 5);
        add_filter('wf_pklist_alter_item_price_formated', array($this, 'alter_price_column'), 1, 6);
        add_filter('wf_pklist_alter_item_total_formated', array($this, 'alter_total_price_column'), 1, 6);
        add_filter('wf_pklist_alter_item_individual_tax',array($this,'alter_item_individual_tax_column'),1,5);
        add_filter('wf_pklist_alter_item_tax_formated', array($this, 'alter_total_tax_column'), 1, 6);
        add_filter('wf_pklist_alter_subtotal_formated', array($this, 'alter_sub_total_row'), 1, 5);
        add_filter('wf_pklist_alter_shipping_row',array($this,'alter_shipping_row'),1,4);
        add_filter('wf_pklist_alter_total_fee',array($this,'alter_fee_row'),1,5);
        add_filter('wf_pklist_alter_taxitem_amount',array($this,'alter_extra_tax_row'),1,5);
        add_filter('wf_pklist_alter_total_tax_row',array($this,'alter_total_tax_row'),1,4);
        add_filter('wf_pklist_alter_item_quantiy_deleted_product',array($this,'alter_quantity_column_deleted_product'),1,4);
        add_filter('wf_pklist_alter_item_total_formated_deleted_product',array($this, 'alter_total_price_column_deleted_product'), 1, 5);
        add_filter('wf_pklist_alter_item_tax_formated_deleted_product', array($this, 'alter_total_tax_column_deleted_product'), 1, 5);
        
        $order_id       = $wc_version<'2.7.0' ? $order->id : $order->get_id();
        $find_replace   = $this->set_refund_entries($find_replace, $html, $template_type, $order,$refund_order,$refund_id);
        $find_replace['[wfte_creditnote_number]'] = self::generate_creditnote_number($refund_order, true, true);
        //creditnote date
        $creditnote_date_match  = array();
        $creditnote_date_format = 'm/d/Y';
        if(preg_match('/data-creditnote_date-format="(.*?)"/s',$html,$creditnote_date_match))
        {
            $creditnote_date_format = $creditnote_date_match[1];
        }
        $credit_date    = self::get_creditnote_date($refund_id,$creditnote_date_format,$refund_order);
        $credit_date    = apply_filters('wf_pklist_alter_creditnote_date',$credit_date,$template_type,$order);
        $find_replace['[wfte_creditnote_date]'] = $credit_date;
        return $find_replace; 
    }

    public static function refunded_item_details($refunded_item_id,$detail,$order){
        $refunded_item  = new WC_Order_Item_Product($refunded_item_id);
        $incl_tax_text  = '';
        $incl_tax       = false;
        $tax_type       = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_taxstatus');
        $template_type  = "creditnote";
        if(in_array('in_tax', $tax_type)) /* including tax */
        {
            $incl_tax_text = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_tax_incl_text($template_type, $order, 'product_price');
            $incl_tax_text = ("" !== $incl_tax_text ? ' ('.$incl_tax_text.')' : $incl_tax_text);
            $incl_tax=true;
        }
        switch ($detail) {
            case 'quantity':
                return $refunded_item->get_quantity();
                break;
            case 'line_total':
                $product_total = (float) (WC()->version< '2.7.0' ? $order->get_item_meta($refunded_item_id,'_line_total',true) : $order->get_line_total($refunded_item, $incl_tax, true));
                return $product_total;
                break;
            case 'line_total_tax':
                $tax_rate   = 0;
                $item_taxes = $refunded_item->get_taxes();
                $item_tax_subtotal = (isset($item_taxes['subtotal']) ? $item_taxes['subtotal'] : array());
                foreach($item_tax_subtotal as $tax_id => $tax_val)
                {
                    $tax_rate += (isset($tax_data_arr[$tax_id]) ? (float) $tax_data_arr[$tax_id] : 0);
                }
                $product_total = (float) (WC()->version< '2.7.0' ? $order->get_item_meta($refunded_item_id,'_line_total',true) : $order->get_line_total($refunded_item, $incl_tax, true));

                if($tax_rate > 0){
                    $item_tax = $product_total * ($tax_rate/100);
                }else{
                    $item_tax = $order->get_line_tax($refunded_item);
                }

                return $item_tax;
                break;
            default:
                return "";
                break;
        }
    }

    /**
    *   @since 1.0.0
    *   Alter quantity of order item if the item is refunded
    *   
    */
    public function alter_quantity_column($qty, $template_type, $_product, $order_item, $order)
    {
        $item_id        = $order_item->get_id();
        $full_refunded  = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
        if(false === $full_refunded){
            $refunded_item_id   = $order_item->this_refund_item_id;
            $qty                = self::refunded_item_details($refunded_item_id,'quantity',$order);
            if($qty<0)
            {   
                $qty_formatted  = '<span style="">'.absint($qty).'</span>';
            }else{
                $qty_formatted  = "-";
            }
        }else{
            $qty_formatted      = '<span style="">'.absint($qty).'</span>';
        }
        $qty_formatted = apply_filters('wt_pklist_alter_quantity_column_in_creditnote',$qty_formatted,$qty,$template_type,$order_item,$order);
        return $qty_formatted;
    }

    public function alter_quantity_column_deleted_product($qty, $template_type, $order_item, $order)
    {
        $item_id        = $order_item->get_id();
        $full_refunded  = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
        if(false === $full_refunded){
            $refunded_item_id = $order_item->this_refund_item_id;
            $qty    = self::refunded_item_details($refunded_item_id,'quantity',$order);
            if($qty<0)
            {   
                $qty_formatted  = '<span style="">'.absint($qty).'</span>';
            }else{
                $qty_formatted  = "-";
            }
        }else{
            $qty_formatted  = '<span style="">'.absint($qty).'</span>';
        }
        $qty_formatted = apply_filters('wt_pklist_alter_deleted_quantity_column_in_creditnote',$qty_formatted,$qty,$template_type,$order_item,$order);
        return $qty_formatted;
    }
    /**
    * @since 1.0.0
    * Show the item price when refund is done in quantity field
    * 
    */

    public function alter_price_column($item_price_formated,$template_type,$item_price,$_product,$order_item,$order){
        $item_id    = $order_item->get_id();
        $full_refunded  = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
        if(false === $full_refunded){
            $new_qty = $order->get_qty_refunded_for_item($item_id);
            if($new_qty<0)
            {
                $item_price_formated = $item_price_formated;
            }else{
                $item_price_formated = "-";
            }
        }
        $item_price_formated = apply_filters('wt_pklist_alter_unit_price_column_in_creditnote',$item_price_formated,$template_type,$item_price,$_product,$order_item,$order);
        return $item_price_formated;
    }

    /**
    *   @since 1.0.0
    *   Alter total price of order item if the item is refunded
    *   
    */
    public function alter_total_price_column($product_total_formated, $template_type, $product_total, $_product, $order_item, $order)
    {   
        $wc_version     = WC()->version;
        $order_id       = $wc_version<'2.7.0' ? $order->id : $order->get_id();
        $user_currency  = Wt_Pklist_Common_Ipc::get_order_meta($order_id,'currency',true);
        $full_refunded  = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
        if(false === $full_refunded){
            $refunded_item_id   = $order_item->this_refund_item_id;
            $refunded_tot_price = self::refunded_item_details($refunded_item_id,'line_total',$order);
            if($refunded_tot_price < 0){
                $product_total  = abs((float)$refunded_tot_price);
                $product_total_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$product_total);
            }else{
                $product_total_formated = '-';
            }
        }
        $product_total_formated = apply_filters('wt_pklist_alter_total_price_column_in_creditnote',$product_total_formated,$product_total,$template_type,$order_item,$order);
        $product_total_formated = apply_filters('wf_pklist_alter_price_creditnote',$product_total_formated,$template_type,$order);
        return $product_total_formated;
    }

    public function alter_total_price_column_deleted_product($product_total_formated, $template_type, $product_total, $order_item, $order)
    {
        $wc_version     = WC()->version;
        $order_id       = $wc_version<'2.7.0' ? $order->id : $order->get_id();
        $user_currency  = Wt_Pklist_Common_Ipc::get_order_meta($order_id,'currency',true);
        $full_refunded  = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
        if(false === $full_refunded){
            $refunded_item_id   = $order_item->this_refund_item_id;
            $refunded_tot_price = self::refunded_item_details($refunded_item_id,'line_total',$order);
            if($refunded_tot_price < 0){
                $product_total = abs((float)$refunded_tot_price);
                $product_total_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$product_total);
            }else{
                $product_total_formated = '-';
            }
        }
        $product_total_formated = apply_filters('wt_pklist_alter_total_price_column_in_creditnote',$product_total_formated,$product_total,$template_type,$order_item,$order);
        $product_total_formated = apply_filters('wf_pklist_alter_price_creditnote',$product_total_formated,$template_type,$order);
        return $product_total_formated;
    }

    /**
    *   @since 1.0.0
    *   Added individual tax column when refund is done in respective tax columns
    * 
    */
    public function alter_item_individual_tax_column($tax_val, $template_type, $tax_id, $order_item, $order){
        $wc_version             = WC()->version;
        $order_id               = $wc_version<'2.7.0' ? $order->id : $order->get_id();
        $user_currency          = Wt_Pklist_Common_Ipc::get_order_meta($order_id,'currency',true);
        $new_tax_val_formatted  = $tax_val;
        $full_refunded          = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
        if(false === $full_refunded){
            $refunded_item_id   = $order_item->this_refund_item_id;
            $new_tax_val        = 0;
            $refunded_item      = new WC_Order_Item_Product($refunded_item_id);
            $refund_tax         = $refunded_item->get_taxes();
            $new_tax_val    += isset( $refund_tax['total'][ $tax_id ] ) ? abs((float) $refund_tax['total'][ $tax_id ]) : 0;
            $new_tax_val_formatted = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$new_tax_val);
            if($new_tax_val > 0){
                $new_tax_val_formatted = '<span style="">'.$new_tax_val_formatted.'</span>';
            }
        }
        $new_tax_val_formatted = apply_filters('wf_pklist_alter_individual_tax_column_in_creditnote',$new_tax_val_formatted,$tax_val,$tax_id,$template_type,$order_item,$order);
        $new_tax_val_formatted = apply_filters('wf_pklist_alter_price_creditnote',$new_tax_val_formatted,$template_type,$order);
        return $new_tax_val_formatted;
    }

    public function alter_total_tax_column($item_tax_formated,$template_type,$item_tax,$_product,$order_item,$order){
        $wc_version     = WC()->version;
        $order_id       = $wc_version<'2.7.0' ? $order->id : $order->get_id();
        $user_currency  = Wt_Pklist_Common_Ipc::get_order_meta($order_id,'currency',true);
        $full_refunded          = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
        if(false === $full_refunded){
            $refunded_item_id   = $order_item->this_refund_item_id;
            $refund_tax         = self::refunded_item_details($refunded_item_id,'line_total_tax',$order);
            if($refund_tax < 0){
                $refund_tax = abs((float)$refund_tax);
            }
            $item_tax_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$refund_tax);
            if($refund_tax > 0){
                $item_tax_formated = '<span style="">'.$item_tax_formated.'</span>';
            }else{
                $item_tax_formated = '-';
            }
        }
            
        $item_tax_formated = apply_filters('wf_pklist_alter_total_tax_column_in_creditnote',$item_tax_formated,$item_tax,$template_type,$order_item,$order);
        $item_tax_formated = apply_filters('wf_pklist_alter_price_creditnote',$item_tax_formated,$template_type,$order);
        return $item_tax_formated;
    }

    public function alter_total_tax_column_deleted_product($item_tax_formated,$template_type,$item_tax,$order_item,$order){
        $wc_version     = WC()->version;
        $order_id       = $wc_version<'2.7.0' ? $order->id : $order->get_id();
        $user_currency  = Wt_Pklist_Common_Ipc::get_order_meta($order_id,'currency',true);
        $full_refunded  = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
        if(false === $full_refunded){
            $refunded_item_id   = $order_item->this_refund_item_id;
            $refund_tax         = self::refunded_item_details($refunded_item_id,'line_total_tax',$order);
            if($refund_tax < 0){
                $refund_tax     = abs((float)$refund_tax);
            }
            $item_tax_formated  = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$refund_tax);
            if($refund_tax > 0){
                $item_tax_formated = '<span style="">'.$item_tax_formated.'</span>';
            }else{
                $item_tax_formated = '-';
            }
        }
        $item_tax_formated = apply_filters('wf_pklist_alter_total_tax_column_in_creditnote',$item_tax_formated,$item_tax,$template_type,$order_item,$order);
        $item_tax_formated = apply_filters('wf_pklist_alter_price_creditnote',$item_tax_formated,$template_type,$order);
        return $item_tax_formated;
    }

    /**
    *   @since 1.0.0
    *   
    */
    public function alter_sub_total_row($sub_total_formated, $template_type, $sub_total, $order, $incl_tax){
        $sub_total_formated = apply_filters('wf_pklist_alter_subtotal_row_in_creditnote',$sub_total_formated,$sub_total,$incl_tax,$order);
        $sub_total_formated = apply_filters('wf_pklist_alter_price_creditnote',$sub_total_formated,$template_type,$order);
        return $sub_total_formated;
    }

    /**
    *   @since 1.0.0
    *   Show the refunded shipping amount
    */
    public function alter_shipping_row($shipping, $template_type, $order, $product_table){
        $shipping = apply_filters('wf_pklist_alter_shipping_row_in_creditnote',$shipping,$template_type,$order);
        $shipping = apply_filters('wf_pklist_alter_price_creditnote',$shipping,$template_type,$order);
        return $shipping;
    }

    /**
    *   @since 1.0.0
    *   Show the refunded individual tax amounts
    * 
    */  
    public function alter_extra_tax_row($tax_amount, $tax_item, $order, $template_type,$tax_rate_id){
        $wc_version     = WC()->version;
        $order_id       = $wc_version<'2.7.0' ? $order->id : $order->get_id();
        $user_currency  = Wt_Pklist_Common_Ipc::get_order_meta($order_id,'currency',true);
        $full_refunded  = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
        if(false === $full_refunded){
            if($tax_amount < 0 ){
                $tax_amount = abs((float)$tax_amount);
                $tax_amount = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$tax_amount);
            }else{
                $tax_amount = "";
            }
        }
        $tax_amount = apply_filters('wf_pklist_alter_extra_tax_row_in_creditnote',$tax_amount,$tax_item,$tax_rate_id,$template_type,$order);
        $tax_amount = apply_filters('wf_pklist_alter_price_creditnote',$tax_amount,$template_type,$order);
        return $tax_amount;
    }

    /**
    *   @since 1.0.0
    *   Show the refunded fee amount
    */
    public function alter_fee_row($fee_total_amount_formated,$template_type,$fee_total_amount,$user_currency,$order){
        $fee_total_amount_formated = apply_filters('wf_pklist_alter_fee_row_in_creditnote',$fee_total_amount_formated,$fee_total_amount,$template_type,$order);
        $fee_total_amount_formated = apply_filters('wf_pklist_alter_price_creditnote',$fee_total_amount_formated,$template_type,$order);
        return $fee_total_amount_formated;
    }

    public function alter_total_tax_row($tax_total,$template_type,$order,$tax_items){
        $wc_version     = WC()->version;
        $order_id       = $wc_version<'2.7.0' ? $order->id : $order->get_id();
        $user_currency  = Wt_Pklist_Common_Ipc::get_order_meta($order_id,'currency',true);
        $full_refunded          = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
        $refund_tax     = $tax_total;
        if(false === $full_refunded){
            if($refund_tax < 0){
                $refund_tax = abs((float)$refund_tax);
                $tax_total = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$refund_tax);
            }else{
                $tax_total = "";
            }
        }
        $tax_total = apply_filters('wf_pklist_alter_total_tax_row_in_creditnote',$tax_total,$tax_items,$template_type,$order);
        $tax_total = apply_filters('wf_pklist_alter_price_creditnote',$tax_total,$template_type,$order);
        return $tax_total;
    }
    /**
    *   @since 1.0.0
    *   set refund rows in product table
    *   
    */
    private function set_refund_entries($find_replace, $html, $template_type, $order, $refund_order=null,$refund_id=null)
    {
        $refund_items_match = array();
        if(preg_match('/<[^>]*data-row-type\s*=\s*"[^"]*\bwfte_refund_items\b[^"]*"[^>]*>(.*?)<\/tr>/s',$html,$refund_items_match))
        {
            $refund_items_html = '';
            $refund_items_row_html = isset($refund_items_match[0]) ? $refund_items_match[0] : '';
            if(!is_null($order) && $refund_items_row_html!='')
            {
                $refund_data_arr = $order->get_refunds();
                if(!empty($refund_data_arr))
                {
                    $wc_version     = WC()->version;
                    $order_id       = $wc_version<'2.7.0' ? $order->id : $order->get_id();
                    $user_currency  = Wt_Pklist_Common_Ipc::get_order_meta($order_id,'currency',true);
                    foreach($refund_data_arr as $refund_data)
                    {   
                        $refund_data_id = ($wc_version< '2.7.0' ? $refund_data->id : $refund_data->get_id());
                        if($refund_id === $refund_data_id){
                            $refund_label   = __('Refund Reason', 'wt_woocommerce_invoice_addon');
                            $refund_reason  = esc_html($refund_data->get_reason());
                            $refund_reason  = ("" === $refund_reason ? __('-', 'wt_woocommerce_invoice_addon') : $refund_reason);
                            $refund_items_html .= str_replace(array('[wfte_product_table_refund_item_label]', '[wfte_product_table_refund_item]'), array($refund_label, $refund_reason), $refund_items_row_html);
                        }
                    }
                }
            }
            $find_replace[$refund_items_match[0]] = $refund_items_html;
        }
        return $find_replace;
    }

    public function add_email_attachments($attachments, $order, $order_id, $email_class_id)
    { 
        $attach_to_mail_for = array('customer_partially_refunded_order', 'customer_refunded_order');
        $attach_to_mail_for = apply_filters('wf_pklist_alter_'.$this->module_base.'_attachment_mail_type', $attach_to_mail_for, $order_id, $email_class_id, $order);
        
        $generate_creditnote_for =Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus',$this->module_id);
        if(in_array($email_class_id, $attach_to_mail_for) && in_array('wc-'.$order->get_status(), $generate_creditnote_for)) 
        {                    
            if(Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_add_creditnote_in_mail', $this->module_id)== "Yes")
            {               
                if(!is_null($this->customizer))
                { 
                    $order_ids      = array($order_id);
                    $pdf_name       = $this->customizer->generate_pdf_name($this->module_base,$order_ids);
                    $this->customizer->template_for_pdf = true;
                    $html           = $this->generate_order_template($order_ids,$pdf_name);
                    $attachments[]  = $this->customizer->generate_template_pdf($html, $this->module_base, $pdf_name, 'attach');
                }
            }
        }
        return $attachments;
    }

    /* 
    * Print_window for invoice
    * @param $orders : order ids
    */    
    public function print_it($order_ids,$action) 
    {
        $is_pro_customizer  = apply_filters('wt_pklist_pro_customizer_'.$this->module_base,false,$this->module_base);
        if($is_pro_customizer)
        {
            if("print_creditnote" === $action || "download_creditnote" === $action)
            {   
                if(!is_array($order_ids))
                {
                    return;
                }    
                if(!is_null($this->customizer))
                {
                    $pdf_name   = $this->customizer->generate_pdf_name($this->module_base,$order_ids);
                    if("download_creditnote" === $action)
                    {   
                        $this->customizer->template_for_pdf = true;               
                        $html   = $this->generate_order_template($order_ids,$pdf_name);
                        $this->customizer->generate_template_pdf($html,$this->module_base,$pdf_name,'download');
                    }else
                    {   
                        $this->customizer->template_for_pdf = false;              
                        $html   = $this->generate_order_template($order_ids,$pdf_name);
                        echo $html;
                    }
                }else
                {
                    _e('Customizer module is not active.', 'wt_woocommerce_invoice_addon');
                }
                exit();
            }
        }
    }

    public function generate_order_template($orders,$page_title)
    {
        $template_type  = $this->module_base;
        //taking active template html
        $html           = $this->customizer->get_template_html($template_type);
        $style_blocks   = $this->customizer->get_style_blocks($html);
        $html           = $this->customizer->remove_style_blocks($html,$style_blocks);
        $out            = '';
        if("" !== $html)
        {
            $number_of_orders   = count($orders);
            $order_inc          = 0;
            foreach($orders as $order_id)
            {
                $order_inc++;
                $order  = ( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);
                $all_refund_orders = $order->get_refunds();
                $number_of_refunds = count($all_refund_orders);
                if(0 < $number_of_refunds){
                    $document_created = Wf_Woocommerce_Packing_List_Admin::created_document_count($order_id,$template_type);
                }
                $page = 1;
                $order_status = ( WC()->version < '2.7.0' ) ? $order->status : $order->get_status();
                foreach($all_refund_orders as $refund_order){
                    $refund_id  = (WC()->version< '2.7.0' ? $refund_order->id : $refund_order->get_id());
                    $out       .= $this->customizer_pro->generate_template_html_for_creditnote($html,$template_type,$order,$refund_order,$refund_id);
                    if($number_of_refunds>1 && $page !== $number_of_refunds)
                    {
                        $out .= '<p class="pagebreak"></p>';
                    }
                    $page++;
                }

                if($number_of_orders>1 && $order_inc<$number_of_orders)
                {
                    $out .= '<p class="pagebreak"></p>';
                }else
                {
                    //$out.='<p class="no-page-break"></p>';
                }
            }
            $out=$this->customizer->append_style_blocks($out,$style_blocks);
            $out=$this->customizer->append_header_and_footer_html($out,$template_type,$page_title);
        }
        return $out;
    }
}

new Wf_Woocommerce_Packing_List_Creditnote_Pro();
// end of class
}