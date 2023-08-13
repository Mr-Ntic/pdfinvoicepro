<?php
/**
 * Invoice Pro section of the plugin
 *
 * @link       
 * @since 1.0.0     
 *
 * @package  Wt_woocommerce_invoice_addon  
 */
if (!defined('ABSPATH')) {
    exit;
}

class Wf_Woocommerce_Packing_List_Packinglist_Pro 
{
    public $module_id='';
    public static $module_id_static='';
    public $module_base='packinglist';
    public $module_title='';
    public $customizer=null;
    public static $attachment_files=array();

    public function __construct()
    {
        $this->module_id=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;

        $this->customizer=Wf_Woocommerce_Packing_List::load_modules('customizer');

        // tab releated hooks
        add_action('wt_pklist_customizer_enable', array($this, 'enable_pro_customizer'),10,2);
        add_filter('wt_pklist_add_additional_tab_item_into_module',array($this,'add_additional_tab'),10,3);
        add_action('wt_pklist_add_additional_tab_content_into_module', array($this, 'add_additional_tab_content'),10,2);
        add_filter('wt_pklist_add_fields_to_settings',array($this,'add_remove_fields_from_settings'),10,4);

        // customizer releated hooks
        add_filter('wt_pklist_pro_customizer_'.$this->module_base,array($this,'switch_to_pro_customizer'),10,2);
        add_filter('wt_pklist_enable_code_editor',array($this,'enable_code_editor_customizer'),10,2);
        
        add_filter('wf_module_customizable_items',array($this,'get_customizable_items'),10,2);
        add_filter('wf_module_non_options_fields',array($this,'get_non_options_fields'),10,2);
        add_filter('wt_pklist_add_pro_templates',array($this,'add_pro_template'),10,2);

        // customizer template releated hooks
        add_filter('wf_module_convert_to_design_view_html_for_'.$this->module_base,array($this,'convert_to_design_view_html'),10,3);

        add_filter('wf_module_generate_template_html_for_'.$this->module_base,array($this,'generate_template_html'),10,6);
        add_action('wt_print_doc',array($this,'print_it'),10,2);

        // send an attachment with order email for selected status
        add_filter('wt_email_attachments', array($this,'add_email_attachments'), 10, 4);

        // packingslip attachment mail to custom email id
        $vl=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus', $this->module_id);
        if(is_array($vl) && in_array('wfte_seperate_email',$vl)){

            define('WF_PKLIST_PACKINGLIST_PRO_EMAIL_TEMPLATE_PATH',untrailingslashit( plugin_dir_path( __FILE__ ) ).'/templates/');
            add_filter('woocommerce_email_classes',array($this, 'add_email_class'));
            add_action('woocommerce_order_actions', array($this, 'add_email_order_action'));
            add_action('woocommerce_order_action_wf_pklist_send_'.$this->module_base.'_email',array($this, 'send_separate_email'));
            // add_action( 'woocommerce_thankyou', array($this,'send_separate_email_for_new_order_tq'), 10, 1 ); 
        }

        // save settings related hooks
        add_filter('wf_module_default_settings',array($this,'default_settings'),11,2);
        add_filter('wf_module_single_checkbox_fields', array($this, 'single_checkbox_fields'), 11, 3);
        add_filter('wf_module_multi_checkbox_fields', array($this, 'multi_checkbox_fields'), 10, 3);
        add_filter('wt_pklist_intl_alter_validation_rule', array($this,'alter_validation_rule'), 10, 2);

        add_filter('wt_print_actions',array($this,'add_print_buttons'),10,4);

        // add print packinglist button my account order details page
        add_filter('wt_frontend_print_actions',array($this,'add_print_button_my_account_order_details_page'),10,3);
        // add print packinglist button my account order details page
        add_filter('wt_pklist_intl_frontend_order_list_page_print_actions', array($this, 'add_print_button_my_account_order_listing_page'), 10, 3);
        add_filter('wt_pklist_alter_tooltip_data_'.$this->module_base, array($this, 'register_tooltips'),10,2);
        add_filter('wt_pklist_bundled_product_css_'.$this->module_base, array($this,'bundled_prod_css'),10,3);

        /**
        * @since 1.0.1 Add to remote printing
        */
        add_filter('wt_pklist_add_to_remote_printing', array($this, 'add_to_remote_printing'), 10, 2);

        /**
        * @since 1.0.1 Do remote printing
        */
        add_filter('wt_pklist_do_remote_printing', array($this, 'do_remote_printing'), 10, 2);
        add_filter('wt_pklist_show_details_after_refund_'.$this->module_base, array($this, 'show_details_after_refund'), 10, 2);
        add_filter('wf_pklist_intl_customizer_enable_pdf_preview', array($this,'enable_pdf_preview'), 10, 2);
    }
    
    /**
    *   @since 1.0.1
    *   Add to remote printing, this will enable remote printing settings
    */
    public function add_to_remote_printing($arr, $remote_print_vendor)
    {
        $arr[$this->module_base]=__('Packing slip', 'wt_woocommerce_invoice_addon');
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

    public function register_tooltips($arr,$template_type)
    {
        if($template_type === $this->module_base){
            include(plugin_dir_path( __FILE__ ).'data/data.tooltip.php');
        }
        return $arr;
    }
    
    public function add_print_buttons($item_arr, $order, $order_id, $button_location)
    {
        if("detail_page" === $button_location && isset($item_arr['packinglist_details_actions']))
        {
            $item_arr=apply_filters('wt_pklist_after_'.$this->module_base.'_print_button_list', $item_arr, $order, $button_location, $this->module_base);
        }
        return $item_arr;
    }

    public function add_print_button_my_account_order_details_page($html,$order,$order_id)
    {
        $template_type=$this->module_base;
        $show_print_button_pages = apply_filters('wt_pklist_show_hide_print_button_in_pages',true,'order_details',$template_type,$order);
        if($show_print_button_pages){
            $show_print_button_arr = Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_invoice_show_print_button',$this->module_id);
            if(is_array($show_print_button_arr) && in_array('order_details',$show_print_button_arr)){
                $generate_invoice_for=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus',$this->module_id);
                if((in_array('wc-'.$order->get_status(),$generate_invoice_for)))
                {
                    Wf_Woocommerce_Packing_List::generate_print_button_for_user($order,$order_id,'print_packinglist',esc_html__('Print Packingslip','wt_woocommerce_invoice_addon'));
                }
            }
        }
        return $html;
    }

    public function add_print_button_my_account_order_listing_page($wt_actions, $order, $order_id)
    {
        if($this->is_show_frontend_print_button($order))
        {
            $wt_actions[$this->module_base]=array(
                'print'=>__('Print Packingslip', 'wt_woocommerce_invoice_addon'),
            );
        }
        return $wt_actions;
    }

    public function is_show_frontend_print_button($order){
        $order_id = WC()->version<'2.7.0' ? $order->id : $order->get_id();
        $template_type=$this->module_base;
        $show_print_button_pages = apply_filters('wt_pklist_show_hide_print_button_in_pages',true,'order_listing',$template_type,$order);
        if($show_print_button_pages){
            $show_print_button_arr = Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_invoice_show_print_button',$this->module_id);
            if(is_array($show_print_button_arr) && in_array('order_listing',$show_print_button_arr)){
                $generate_invoice_for=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus',$this->module_id);
                if((in_array('wc-'.$order->get_status(),$generate_invoice_for)))
                {
                    Wf_Woocommerce_Packing_List::generate_print_button_for_user($order,$order_id,'print_packinglist',esc_html__('Print Packingslip','wt_woocommerce_invoice_addon'));
                }
            }
        }
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

    public function enable_pro_customizer($base_id,$template_type){

        if($base_id === $this->module_id){
            //initializing necessary modules, the argument must be current module name/folder
            if(!is_null($this->customizer) && true === apply_filters('wt_pklist_switch_to_classic_customizer_'.$this->module_base, true, $this->module_base))
            {
                $this->customizer->init($this->module_base);
            }
        }
    }

    public function add_additional_tab($tab_items,$template_type,$base_id){
        if($base_id === $this->module_id){
            if(isset($tab_items['customize_pro'])){
                unset($tab_items['customize_pro']);
            }

            $new_element = array('advanced' => __('Advanced','wt_woocommerce_invoice_addon'));
            $tab_items = Wt_woocommerce_invoice_addon_Admin::wt_add_array_element_to_position($tab_items,$new_element,'general');
        }
        return $tab_items;
    }

    public function add_additional_tab_content($template_type,$base_id){

        if($base_id === $this->module_id){
            wp_enqueue_script('wc-enhanced-select');
            wp_enqueue_style('woocommerce_admin_styles',WC()->plugin_url().'/assets/css/admin.css');
            wp_enqueue_media();
            
            $target_id = "advanced";
            $view_file=plugin_dir_path( __FILE__ ).'views/advanced.php';
            include $view_file;
        }
        
    }

    public function add_remove_fields_from_settings($settings,$target_id,$template_type,$base_id){
        if($base_id === $this->module_id){
            if("general" === $target_id){
                include plugin_dir_path( __FILE__ ).'views/general.php';
            }
        }
        return $settings;
    }

    public function add_pro_template($template_arr,$template_type){
        
        $module_id = Wf_Woocommerce_Packing_List::get_module_id($template_type);
        if($module_id === $this->module_id){
            include plugin_dir_path( __FILE__ ).'data/data.templates.php';
            return $template_arr;
        }
        return $template_arr;
    }

    public function get_customizable_items($settings,$base_id)
    {
        if($base_id === $this->module_id)
        {
            //these fields are the classname in template Eg: `company_logo` will point to `wfte_company_logo`
            return array(
                'doc_title'         => __('Document title','wt_woocommerce_invoice_addon'),
                'company_logo'      => __('Company Logo','wt_woocommerce_invoice_addon'),           
                'order_number'      => __('Order Number','wt_woocommerce_invoice_addon'),
                'order_date'        => __('Order Date','wt_woocommerce_invoice_addon'),
                'from_address'      => __('From Address','wt_woocommerce_invoice_addon'),
                'billing_address'   => __('Billing Address','wt_woocommerce_invoice_addon'),
                'shipping_address'  => __('Shipping Address','wt_woocommerce_invoice_addon'),
                'email'             => __('Email Field','wt_woocommerce_invoice_addon'),
                'tel'               => __('Tel Field','wt_woocommerce_invoice_addon'),
                'vat_number'        => __('VAT Field','wt_woocommerce_invoice_addon'),
                'ssn_number'        => __('SSN Field','wt_woocommerce_invoice_addon'),
                'customer_note'     => __('Customer Note','wt_woocommerce_invoice_addon'),
                'shipping_method'   => __('Shipping Method','wt_woocommerce_invoice_addon'),
                'tracking_number'   => __('Tracking Number','wt_woocommerce_invoice_addon'),
                'box_name'          => __('Box name','wt_woocommerce_invoice_addon'),
                'product_table'     => __('Product Table','wt_woocommerce_invoice_addon'),
                'package_no'        => __('Package Number','wt_woocommerce_invoice_addon'),
                'total_no_of_items' => __('No of Items','wt_woocommerce_invoice_addon'),
                'footer'            => __('Footer','wt_woocommerce_invoice_addon'),
                'return_policy'     => __('Return Policy','wt_woocommerce_invoice_addon'),
                'barcode'           => __('Bar Code','wt_woocommerce_invoice_addon'),
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
                'barcode',
                'package_no',
                'footer',
                'return_policy',
            );
        }
        return $settings;
    }

    public function default_settings($settings,$base_id)
    {
        if($base_id === $this->module_id)
        {      
            // general tab
            $settings['woocommerce_wf_generate_for_orderstatus']    = array();
            // $settings['wt_pklist_separate_email']   = 'No';
            $settings['wf_woocommerce_invoice_show_print_button']   = array();
            $settings['wf_woocommerce_product_category_wise_splitting'] = "No";
            $settings['sort_products_by']   = "";
            $settings['bundled_product_display_option'] = 'main-sub'; 
            // $settings['woocommerce_wf_attach_'.$this->module_base] = array(); // show print button for statuses
            
            // advanced tab
            $settings['wf_'.$this->module_base.'_contactno_email'] = array();
            $settings['wf_'.$this->module_base.'_product_meta_fields']  = array();
            $settings['wt_'.$this->module_base.'_product_attribute_fields'] = array();
            $settings['woocommerce_wf_packinglist_footer']  = "";
        }
        return $settings;
    }

    public function single_checkbox_fields($settings,$base_id,$tab_name){
        if($base_id === $this->module_id){
            $settings['wt_packinglist_general']['wf_woocommerce_product_category_wise_splitting'] = "No";
            $settings['wt_packinglist_general']['woocommerce_wf_packinglist_variation_data'] = "No";
            // $settings['wt_packinglist_general']['wt_pklist_separate_email'] =  "No";
        }
        return $settings;
    }

    public function multi_checkbox_fields($settings,$base_id,$tab_name){
        if($base_id === $this->module_id){
            $settings['wt_packinglist_general']['woocommerce_wf_generate_for_orderstatus']  = array();
            $settings['wt_packinglist_general']['wf_woocommerce_invoice_show_print_button'] = array();
            $settings['wt_packinglist_advanced']['wf_'.$this->module_base.'_contactno_email'] = array();
            $settings['wt_packinglist_advanced']['wf_'.$this->module_base.'_product_meta_fields'] = array();
            $settings['wt_packinglist_advanced']['wt_'.$this->module_base.'_product_attribute_fields'] = array();
        }
        return $settings;
    }

    public function alter_validation_rule($arr, $base_id)
    {
        if($base_id === $this->module_id)
        {
            $arr['woocommerce_wf_generate_for_orderstatus'] = array('type'=>'text_arr');
            $arr['wf_woocommerce_invoice_show_print_button'] = array('type'=>'text_arr');
            $arr['wf_'.$this->module_base.'_contactno_email']   = array('type'=>'text_arr');
            $arr['wf_'.$this->module_base.'_product_meta_fields'] = array('type'=>'text_arr');
            $arr['wt_'.$this->module_base.'_product_attribute_fields']  = array('type'=>'text_arr');
            $arr['woocommerce_wf_packinglist_footer']   = array('type'=>'textarea');

            //$arr['woocommerce_wf_attach_'.$this->module_base]   = array('type'=>'text_arr'); // show print button
        }
        return $arr;
    }
    
    public function convert_to_design_view_html($find_replace,$html,$template_type)
    {   
        $is_pro_customizer = apply_filters('wt_pklist_pro_customizer_'.$template_type,false,$template_type);
        if($template_type === $this->module_base && $is_pro_customizer)
        {
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_logo($find_replace,$template_type,$html);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_from_address($find_replace,$template_type,$html);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_billing_address($find_replace,$template_type,$html);     
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_address($find_replace,$template_type,$html);  
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_default_order_fields($find_replace,$template_type,$html);   
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_product_table($find_replace,$template_type,$html);     
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_other_data($find_replace,$template_type,$html);
        }
        return $find_replace;
    }

    public function bundled_prod_css($pro_css,$template_type,$order){
        if($template_type === $this->module_base){
            $category_wise_split=Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_product_category_wise_splitting', $this->module_id);
            $bundle_display_option=Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_bundle_display_option($template_type, $order);
            if($bundle_display_option && "No" === $category_wise_split)
            {
                $pro_css.='tr.wfte_product_row_bundle_child .product_td{ padding-left:20px !important; }';
            }
        }
        return $pro_css;
    }
    /**
     *  Items needed to be converted to HTML for print
     */
    public function generate_template_html($find_replace,$html,$template_type,$order,$box_packing=null,$order_package=null)
    {
        $is_pro_customizer = apply_filters('wt_pklist_pro_customizer_'.$template_type,false,$template_type);
        if($template_type === $this->module_base && $is_pro_customizer)
        {
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_logo($find_replace,$template_type,$html,$order);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_from_address($find_replace,$template_type,$html,$order);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_billing_address($find_replace, $template_type, $html,$order);      
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_address($find_replace,$template_type, $html,$order); 
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_default_order_fields($find_replace,$template_type, $html,$order);                 
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::package_doc_items($find_replace,$template_type,$order,$box_packing,$order_package);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_product_table($find_replace,$template_type,$html,$order,$box_packing,$order_package);      
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_extra_charge_fields($find_replace,$template_type,$html,$order);   
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_other_data($find_replace,$template_type,$html,$order);     
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_package_weight_and_count_info($find_replace,$template_type,$order,$box_packing,$order_package);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_order_data($find_replace,$template_type,$html,$order);     
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_extra_fields($find_replace,$template_type,$html,$order);
        }
        return $find_replace;
    }

    /**
     *  Add Packinglist email to the WooCommerce Email list
     *  @since 1.0.0
     *  @param array $email_classes email classes array
     *  @return array - new class list
     */
    public function add_email_class($email_classes)
    {
        include_once plugin_dir_path(__FILE__)."classes/class-".$this->module_base."-email.php";
        $email_classes['Wf_Woocommerce_Packing_List_Packinglist_Email'] = new Wf_Woocommerce_Packing_List_Packinglist_Email();
        return $email_classes;
    }

    /**
     *  Add Packinglist email option to order actions select box on edit order page
     *  @since 1.0.0
     *  @param array $actions order actions array to display
     *  @return array - updated actions
     */
    public function add_email_order_action($actions)
    {
        $actions['wf_pklist_send_'.$this->module_base.'_email']=__('Send Packing Slip email', 'wt_woocommerce_invoice_addon');
        return $actions;
    }

    /**
     *  Send separate email
     *  @since 4.0.8
     *  @param \WC_Order $order
     */
    public function send_separate_email($order)
    {       
        $message = sprintf(__( 'Packing Slip email send by %s.', 'wt_woocommerce_invoice_addon'), wp_get_current_user()->display_name );
        $order->add_order_note($message);

        $wc_version = WC()->version;
        $order_id   = $wc_version<'2.7.0' ? $order->id : $order->get_id();

        WC()->mailer()->emails['Wf_Woocommerce_Packing_List_Packinglist_Email']->trigger($order_id, $order);
    }
    /**
    *   Add email attachment
    *   @since 1.0.0
    */
    public function add_email_attachments($attachments, $order, $order_id, $email_class_id)
    {   
        $template_type = $this->module_base;
        /* check order email types */       
        $attach_to_mail_for = array('new_order', 'customer_completed_order', 'customer_invoice', 'customer_on_hold_order', 'customer_processing_order');
        /* check order email types for renewal order */
        $attach_to_mail_for = array_merge($attach_to_mail_for,array('new_renewal_order','customer_renewal_invoice','customer_completed_renewal_order','customer_on_hold_renewal_order','customer_processing_renewal_order'));
        $attach_to_mail_for = apply_filters('wf_pklist_alter_'.$this->module_base.'_attachment_mail_type', $attach_to_mail_for, $order_id, $email_class_id, $order);
        /* To avoid the duplication when using the filter */
        $attach_to_mail_for = array_unique($attach_to_mail_for);
        
        $is_attach = false;
        if(in_array($email_class_id, $attach_to_mail_for)) 
        {
            /* check order statuses to generate picklist */
            $generate_picklist_for = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus', $this->module_id);
            if(in_array('wc-'.$order->get_status(), $generate_picklist_for))
            {
                $is_attach = true;
            }

            // new order email
            if(in_array('wfte_new_order',$generate_picklist_for) && "new_order" === $email_class_id){
                $is_attach = true;
                $is_attach = apply_filters('wt_pklist_enable_new_order_email_attachment',$is_attach,$order,$template_type);
            }
        }

        /* separate email */
        if('wt_pklist_'.$this->module_base.'_email' === $email_class_id)
        {
            $is_attach=true;
        }

        if($is_attach) 
        {                    
            if(!is_null($this->customizer))
            { 
                $order_ids  = array($order_id);
                $pdf_name   = $this->customizer->generate_pdf_name($this->module_base, $order_ids);

                /* check the PDF was already generated in this hook */
                $attachment_file = Wf_Woocommerce_Packing_List_Customizer_Ipc::is_pdf_generated(self::$attachment_files, $pdf_name);

                if(!$attachment_file)
                {
                    $this->customizer->template_for_pdf = true;
                    $html   = $this->generate_order_template($order_ids, $pdf_name);
                    $attachment_file = $this->customizer->generate_template_pdf($html, $this->module_base, $pdf_name, 'attach');
                    self::$attachment_files[] = $attachment_file;
                }               
                $attachments[] = $attachment_file;
            }
        }
        return $attachments;
    }

    /* 
    * Print_window for packinglist
    * @param $orders : order ids
    */    
    public function print_it($order_ids,$action) 
    {
        $is_pro_customizer  = apply_filters('wt_pklist_pro_customizer_'.$this->module_base,false,$this->module_base);
        if($is_pro_customizer)
        {
            if("print_packinglist" === $action || "download_packinglist" === $action || "preview_packinglist" === $action)
            {   
                if(!is_array($order_ids))
                {
                    return;
                }    
                if(!is_null($this->customizer))
                {
                    $pdf_name   = $this->customizer->generate_pdf_name($this->module_title, $order_ids);
                    if("preview_packinglist" === $action || "download_packinglist" === $action){
                        $this->customizer->template_for_pdf = true;
                        if("preview_packinglist" === $action){
                            $html=$this->customizer->get_preview_pdf_html($this->module_base);
                            $html=$this->generate_order_template($order_ids, $pdf_name, $html,$action);
                            $this->customizer->generate_template_pdf($html,$this->module_base,$pdf_name,'preview');
                        }else{
                            $html=$this->generate_order_template($order_ids, $pdf_name);
                            $this->customizer->generate_template_pdf($html,$this->module_base,$pdf_name,'download');
                        }
                    }else{
                        $html=$this->generate_order_template($order_ids, $pdf_name,"",$action);
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

    public function generate_order_template($orders,$page_title,$html="",$action="")
    {
        $template_type = $this->module_base;
        if("" === $html)
    	{
    		//taking active template html
    		$html=$this->customizer->get_template_html($template_type);
    	}
        $style_blocks   = $this->customizer->get_style_blocks($html);
        $html           = $this->customizer->remove_style_blocks($html,$style_blocks);
        $out            = '';
        if("" !== $html)
        {
            if (!class_exists('Wf_Woocommerce_Packing_List_Box_packing')) {
                include_once WT_PKLIST_INVOICE_ADDON_PLUGIN_PATH.'includes/class-wf-woocommerce-packing-list-box_packing.php';
            }
            $box_packing    = new Wf_Woocommerce_Packing_List_Box_packing();
            $out_arr        = array();
            foreach ($orders as $order_id)
            {
                $order          = ( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);
                $order_packages = null;
                $order_packages = $box_packing->create_order_package($order, $template_type);
                $number_of_order_package = count($order_packages);
                if(!empty($order_packages)) 
                {
                    $order_pack_inc = 0;
                    foreach ($order_packages as $order_package_id => $order_package)
                    {
                        $order_pack_inc++;
                        $order      = ( WC()->version < '2.7.0' ) ? new WC_Order($order_id) : new wf_order($order_id);
                        $out_arr[]  = $this->customizer->generate_template_html($html,$template_type,$order,$box_packing,$order_package);                      
                    }
                    if("preview_packinglist" !== $action){
                        $document_created = Wf_Woocommerce_Packing_List_Admin::created_document_count($order_id,$template_type);    
                    } 
                }else
                {
                    wp_die(__("Unable to print Packing slip. Please check the items in the order.",'wt_woocommerce_invoice_addon'), "", array());
                }
            }
            $out = implode('<p class="pagebreak"></p>',$out_arr).'<p class="no-page-break"></p>';

            $out = $this->customizer->append_style_blocks($out,$style_blocks);
            //adding header and footer
            $out = $this->customizer->append_header_and_footer_html($out,$template_type,$page_title);
        }
        return $out;
    }
    
    public function show_details_after_refund($show,$template_type){
        if($template_type === $this->module_base){
            return true;
        }
        return $show;
    }

    public function enable_pdf_preview($status, $template_type)
	{
		if($template_type === $this->module_base)
		{
			$status=true;	
		}
		return $status;
	}
}
new Wf_Woocommerce_Packing_List_Packinglist_Pro();