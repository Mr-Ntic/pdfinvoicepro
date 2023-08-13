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

class Wf_Woocommerce_Packing_List_Invoice_Pro 
{
    public $module_id='';
    public static $module_id_static='';
    public $module_base='invoice';
    public $is_enable_invoice;

    public function __construct()
    {
        $this->module_id=Wf_Woocommerce_Packing_List::get_module_id($this->module_base);
        self::$module_id_static=$this->module_id;
        add_filter('wt_pklist_add_additional_tab_item_into_module',array($this,'add_additional_tab'),10,3);
        add_action('wt_pklist_add_additional_tab_content_into_module', array($this, 'add_additional_tab_content'),10,2);
        add_filter('wt_pklist_add_fields_to_settings',array($this,'add_remove_fields_from_settings'),10,4);
        add_filter('wf_module_default_settings', array($this, 'default_settings'), 11, 2);
        add_filter('wf_module_single_checkbox_fields',array($this,'single_checkbox_fields'),11,3);
        add_filter('wf_module_multi_checkbox_fields', array($this, 'multi_checkbox_fields'), 10, 3);
        add_filter('wt_pklist_intl_alter_validation_rule', array($this,'alter_validation_rule'), 10, 2);

        add_filter('wt_pklist_add_pro_templates',array($this,'add_pro_template'),10,2);
        add_filter('wf_module_customizable_items',array($this,'get_customizable_items'),10,2);
        add_filter('wf_pklist_alter_customize_inputs',array($this,'alter_customize_inputs'),10,3);
        add_filter('wf_module_non_options_fields',array($this,'get_non_options_fields'),10,2);

        add_filter('wf_module_convert_to_design_view_html_for_'.$this->module_base,array($this,'convert_to_design_view_html'),10,3);
        add_filter('wf_module_generate_template_html_for_'.$this->module_base,array($this,'generate_template_html'),10,6);

        add_filter('wt_pklist_pro_customizer_'.$this->module_base,array($this,'switch_to_pro_customizer'),10,2);
        add_filter('wt_pklist_enable_code_editor',array($this,'enable_code_editor_customizer'),10,2);

        add_action('wt_run_payment_link_module',array($this,'run_payment_link_module'),10,2);
        add_action('wf_pklist_intl_after_setting_update', array($this, 'after_setting_update'), 10, 2);

        $this->is_enable_invoice=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_enable_invoice',$this->module_id);
        if("Yes" === $this->is_enable_invoice) /* `print_it` method also have the same checking */
        {
            // show document details
            add_filter('wt_print_docdata_metabox',array($this, 'add_docdata_metabox_pro'),10,3);
            add_filter('wt_print_actions',array($this,'add_print_buttons'),10,4);
        }

        add_filter('wt_pklist_alter_tooltip_data_'.$this->module_base, array($this, 'register_tooltips'), 10,2);
        add_filter('wt_pklist_bundled_product_css_'.$this->module_base, array($this,'bundled_prod_css'),10,3);

        /**
        * @since 1.0.1 Add to remote printing
        */
        add_filter('wt_pklist_add_to_remote_printing', array($this, 'add_to_remote_printing'), 10, 2);

        /**
        * @since 1.0.1 Do remote printing
        */
        add_filter('wt_pklist_do_remote_printing', array($this, 'do_remote_printing'), 10, 2);

        add_filter('wt_pklist_check_prompt_'.$this->module_base,array($this,'check_prompt_for_print_node_btn'),10,3);
    }
    
    /**
    *   @since 1.0.1
    *   Add to remote printing, this will enable remote printing settings
    */
    public function add_to_remote_printing($arr, $remote_print_vendor)
    {
        $arr[$this->module_base]=__('Invoice', 'wt_woocommerce_invoice_addon');
        return $arr;
    }

    /**
    *   @since 1.0.1
    *   Do remote printing.
    */
    public function do_remote_printing($module_base_arr, $order_id)
    {
        $this_pro = new Wf_Woocommerce_Packing_List_Invoice();
        return Wt_woocommerce_invoice_addon_Admin::do_remote_printing($module_base_arr, $order_id, $this_pro);
    }

    public function check_prompt_for_print_node_btn($is_show_prompt,$order,$template_type){
        if($this->module_base === $template_type){
            $is_show_prompt = 1;
            $order_id       = WC()->version<'2.7.0' ? $order->id : $order->get_id();
            $order_status   = ( WC()->version < '2.7.0' ) ? $order->status : $order->get_status();
            $invoice_number = Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order,false);
            $generate_invoice_for   = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_orderstatus',$this->module_id);
            $free_order_enable      = Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_invoice_free_orders',$this->module_id);
            
            if(in_array('wc-'.$order_status, $generate_invoice_for) || !empty($invoice_number))
            {
                $is_show_prompt=0;
            }else
            {
                if(empty($invoice_number))
                {
                    $is_show_prompt=1;
                }
            }

            if(empty($invoice_number))
            {
                if("No" === $free_order_enable){
                    if(0 === \intval($order->get_total())){
                        $is_show_prompt=2;
                    }
                }
            }
        }
        return $is_show_prompt;
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
        if("detail_page" === $button_location && isset($item_arr['invoice_details_actions']))
        {
            $item_arr=apply_filters('wt_pklist_after_'.$this->module_base.'_print_button_list', $item_arr, $order, $button_location, $this->module_base);
        }
        return $item_arr;
    }

    public function add_docdata_metabox_pro($data_arr, $order, $order_id)
    {
        
        $invoice_number=Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order, false);
        if("" !== $invoice_number)
        {
            $invoice_date=Wt_woocommerce_invoice_addon_Admin::get_sequential_date($order_id, 'wf_invoice_date', get_option( 'date_format' ), $order);
            $data_arr['wf_meta_box_invoice_date']=array(
                'label'=>__('Invoice Date', 'wt_woocommerce_invoice_addon'),
                'value'=>$invoice_date,
            );
        }
        return $data_arr;
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

    public function add_additional_tab($tab_items,$template_type,$base_id){
        if($base_id === $this->module_id){
            $new_element = array('advanced' => __('Advanced','wt_woocommerce_invoice_addon'));
            $tab_items = Wt_woocommerce_invoice_addon_Admin::wt_add_array_element_to_position($tab_items,$new_element,'general');
        }
        return $tab_items;
    }

    public function add_additional_tab_content($template_type,$base_id){

        if($base_id === $this->module_id){
            if(class_exists('Wf_Woocommerce_Packing_List_Pro_Common_Func')){
                wp_enqueue_script($this->module_id.'_seq_pro',WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'includes/js/sequential_number.js',array('jquery'),WT_PKLIST_INVOICE_ADDON_VERSION);
                include plugin_dir_path( WT_PKLIST_INVOICE_ADDON_FILENAME )."includes/views/sequential_number_date_popup_pro.php";
            }
            $target_id = "advanced";
            $view_file=plugin_dir_path( __FILE__ ).'views/advanced.php';
            include $view_file;
        }
        
    }

    public function add_remove_fields_from_settings($settings,$target_id,$template_type,$base_id){
        
        if($base_id === $this->module_id){

            // remove the free line option from other section (Basic version)
            if("general" === $target_id){
                if(isset($settings['invoice_general_others']['wf_woocommerce_invoice_free_line_items'])){
                    unset($settings['invoice_general_others']['wf_woocommerce_invoice_free_line_items']);
                }
                if(isset($settings['invoice_general_invoice_details'])){
                    unset($settings['invoice_general_invoice_details']);
                }
            }

            $payment_link_default_order_statuses = array(
                'wc-pending'    => _x( 'Pending payment', 'Order status', 'woocommerce' ),
                'wc-on-hold'    => _x( 'On hold', 'Order status', 'woocommerce' ),
                'wc-failed'     => _x( 'Failed', 'Order status', 'woocommerce' ),
            );

            // add the product display section after the general section in the invoice settings page
            $product_display_section = array(
                'invoice_general_product_display' => array(

                    'wt_sub_head_inv_gen_prod_display' => array(
                        'type'  =>  'wt_sub_head',
                        'class' =>  'wt_pklist_field_group_hd_sub',
                        'heading_number' => 2,
                        'label' =>  __("Product display",'wt_woocommerce_invoice_addon'),
                    ),

                    'wf_woocommerce_product_category_wise_splitting' => array(
                        'type'  => 'wt_single_checkbox',
                        'label' => __("Group products by 'Category'",'wt_woocommerce_invoice_addon'),
                        'name'  => "wf_woocommerce_product_category_wise_splitting",
                        'value' => 'Yes',
                        'checkbox_fields' => array('Yes'=> __("Enable to group products by category in the invoice","wt_woocommerce_invoice_addon")),
                        'class' => 'wf_woocommerce_product_category_wise_splitting',
                        'id'    => 'wf_woocommerce_product_category_wise_splitting',
                    ),

                    'sort_products_by' => array(
                        'type'  => 'wt_select_dropdown',
                        'label' => __("Sort products by", 'wt_woocommerce_invoice_addon'),
                        'name'  => "sort_products_by",
                        'select_dropdown_fields' => array(
                            ''          => __('None', 'wt_woocommerce_invoice_addon'),
                            'name_asc'  => __('Name ascending', 'wt_woocommerce_invoice_addon'),
                            'name_desc' => __('Name descending', 'wt_woocommerce_invoice_addon'),
                            'sku_asc'   => __('SKU ascending', 'wt_woocommerce_invoice_addon'),
                            'sku_desc'  => __('SKU descending', 'wt_woocommerce_invoice_addon'),
                        ),
                        'class'     => 'sort_products_by',
                        'id'        => 'sort_products_by',
                        'tooltip'   => true,
                    ),

                    'woocommerce_wf_packinglist_variation_data' => array(
                        'type'      => 'wt_single_checkbox',
                        'label'     => __("Show variation data below each product",'wt_woocommerce_invoice_addon'),
                        'name'      => "woocommerce_wf_packinglist_variation_data",
                        'value'     => 'Yes',
                        'checkbox_fields' => array('Yes'=> __("Enable to include of product variation data in the product table","wt_woocommerce_invoice_addon")),
                        'class'     => 'woocommerce_wf_packinglist_variation_data',
                        'id'        => 'woocommerce_wf_packinglist_variation_data',
                    ),

                    'bundled_product_display_option' => array(
                        'id'        => 'bundled_product_display_option',
                        'class'     => 'bundled_product_display_option',
                        'tooltip'   => true,
                        'type'      => "wt_select_dropdown",
                        'label'     => __("Bundled product display options",'wt_woocommerce_invoice_addon'),
                        'name'      => "bundled_product_display_option",
                        'help_text' => sprintf(__('Choose how to display bundled products in the invoice. Applicable only if you are using %s Woocommerce Product Bundles %s / %s YITH WooCommerce Product Bundle add-on %s. It may not work along with %sGroup by Category%s option.','wt_woocommerce_invoice_addon'), '<b>', '</b>', '<b>', '</b>', '<b>', '</b>' ),
                        'select_dropdown_fields'=>array(
                            'main-sub'  => __('Main product with bundle items', 'wt_woocommerce_invoice_addon'),
                            'main'      => __('Main product only', 'wt_woocommerce_invoice_addon'),
                            'sub'       => __('Bundle items only', 'wt_woocommerce_invoice_addon'),
                        ),
                        'help_text_conditional'=>array(
                            array(
                                'help_text' => '<img src="'.WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'assets/images/bundle-both-items.png"/>',
                                'condition' => array(
                                    array('field'=>'bundled_product_display_option', 'value'=>'main-sub')
                                )
                            ),
                            array(
                                'help_text' => '<img src="'.WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'assets/images/bundle-parent-only.png"/>',
                                'condition' => array(
                                    array('field'=>'bundled_product_display_option', 'value'=>'main')
                                )
                            ),
                            array(
                                'help_text' => '<img src="'.WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'assets/images/bundle-child-only.png"/>',
                                'condition' => array(
                                    array('field'=>'bundled_product_display_option', 'value'=>'sub')
                                )
                            ),
                        ),
                    ),

                    'wf_woocommerce_invoice_free_line_items' => array(
                        'type'      => 'wt_single_checkbox',
                        'label'     => __("Display free line items in the invoice","wt_woocommerce_invoice_addon"),
                        'id'        => 'wf_woocommerce_invoice_free_line_items',
                        'name'      => 'wf_woocommerce_invoice_free_line_items',
                        'value'     => "Yes",
                        'checkbox_fields' => array('Yes'=> __("Include free(priced as 0) line items in the invoice","wt_woocommerce_invoice_addon")),
                        'class'     => "wf_woocommerce_invoice_free_line_items",
                        'col'       => 3,
                    ), 

                    'wt_inv_gen_hr_line' => array(
                        'type'  => 'wt_hr_line',
                        'class' => 'wf_field_hr'
                    )
                )
            );
            
            $payment_link_section = array(
                'invoice_general_payment_link' => array(
                    'wt_sub_head_inv_gen_payment_link' => array(
                        'type'  =>  'wt_sub_head',
                        'class' =>  'wt_pklist_field_group_hd_sub',
                        'heading_number' => 2,
                        'label' =>  __("Payment link",'wt_woocommerce_invoice_addon'),
                    ),
                    'woocommerce_wf_enable_payment_link_in_invoice' => array(
                        'type'  => 'wt_single_checkbox',
                        'label' => __("Show payment link on invoice","wt_woocommerce_invoice_addon"),
                        'id'    => 'woocommerce_wf_enable_payment_link_in_invoice',
                        'name'  => 'woocommerce_wf_enable_payment_link_in_invoice',
                        'value' => 1,
                        'checkbox_fields' => array('1'=> __("Enable to add a payment link in the invoice","wt_woocommerce_invoice_addon")),
                        'class' => "woocommerce_wf_enable_payment_link_in_invoice",
                        'col'   => 3,
                        'form_toggler'=>array(
                            'type'  => 'parent',
                            'target'=> 'wf_paylink_for_order_status',
                        ),
                        'help_text' => __("Adds a payment link beside the payment method in the invoice. Ensure to choose a template from the ‘Customize’ tab that supports the payment link.","wt_woocommerce_invoice_addon"),
                        'tooltip'   => true,
                    ), 

                    'woocommerce_wf_payment_link_in_order_status' => array(
                        'type'  => 'wt_multi_checkbox',
                        'label' => __("Choose order status","wt_woocommerce_invoice_addon"),
                        'name'  => 'woocommerce_wf_payment_link_in_order_status',
                        'id'    => 'woocommerce_wf_payment_link_in_order_status_st',
                        'value' => $payment_link_default_order_statuses,
                        'checkbox_fields' => $payment_link_default_order_statuses,
                        'class' => 'woocommerce_wf_payment_link_in_order_status',
                        'col'   => 3,
                        'help_text' => __("Adds payment link for selected order statuses. Even if nothing is selected, 'On hold' status will be considered.","wt_woocommerce_invoice_addon"),
                        'alignment' => 'vertical_with_label',
                        'form_toggler'=>array(
                            'type'  => 'child',
                            'id'    => 'wf_paylink_for_order_status',
                            'val'   => '1',
                        ),
                        'tooltip' => true,
                    ),

                    'woocommerce_wf_show_pay_later_in_checkout' => array(
                        'type'  => 'wt_single_checkbox',
                        'label' => __("Show pay later on checkout","wt_woocommerce_invoice_addon"),
                        'id'    => 'woocommerce_wf_show_pay_later_in_checkout',
                        'name'  => 'woocommerce_wf_show_pay_later_in_checkout',
                        'value' => 1,
                        'checkbox_fields' => array('1'=> __("Enable to show pay later option at the checkout","wt_woocommerce_invoice_addon")),
                        'class' => "woocommerce_wf_show_pay_later_in_checkout",
                        'col'   => 3,
                        'form_toggler'=> array(
                            'type'  => 'parent',
                            'target'=> 'wf_paylink_form_fields',
                        )
                    ),

                    'woocommerce_wf_pay_later_title' => array(
                        'type'  => "wt_text",
                        'label' => __("Title", 'wt_woocommerce_invoice_addon'),
                        'name'  => 'woocommerce_wf_pay_later_title',
                        'form_toggler' => array(
                            'type'  => 'child',
                            'id'    => 'wf_paylink_form_fields',
                            'val'   => '1',
                        ),
                        'tooltip' => true,
                    ),
                    'woocommerce_wf_pay_later_description' => array(
                        'type'  => "wt_text",
                        'label' => __("Description", 'wt_woocommerce_invoice_addon'),
                        'name'  => 'woocommerce_wf_pay_later_description',
                        'form_toggler'  => array(
                            'type'  => 'child',
                            'id'    => 'wf_paylink_form_fields',
                            'val'   => '1',
                        ),
                        'tooltip' => true,
                    ),
                    'woocommerce_wf_pay_later_instuction' => array(
                        'type'  => "wt_text",
                        'label' => __("Instruction", 'wt_woocommerce_invoice_addon'),
                        'name'  => 'woocommerce_wf_pay_later_instuction',
                        'form_toggler'  => array(
                            'type'  => 'child',
                            'id'    => 'wf_paylink_form_fields',
                            'val'   => '1',
                        ),
                        'tooltip'   => true,
                    ), 

                    'wt_inv_gen_hr_line' => array(
                        'type'  => 'wt_hr_line',
                        'class' => 'wf_field_hr'
                    )
                )
            );

            if("general" === $target_id){
                // add the product display section after the general section in the invoice settings page
                $settings = Wt_woocommerce_invoice_addon_Admin::wt_add_array_element_to_position($settings,$payment_link_section,'invoice_general_invoice_number');
                $settings = Wt_woocommerce_invoice_addon_Admin::wt_add_array_element_to_position($settings,$product_display_section,'invoice_general_invoice_number');
            }
        }
        return $settings;
    }

    public function default_settings($settings,$base_id){
        if($base_id === $this->module_id){
            $settings['wf_woocommerce_product_category_wise_splitting'] = "No";
            $settings['sort_products_by'] = "";
            $settings['woocommerce_wf_packinglist_variation_data']='Yes';

            /* Possible values: 1.main-sub, 2.main, 3.sub */
            $settings['bundled_product_display_option'] = 'main-sub'; 
            $settings['woocommerce_wf_packinglist_footer'] = "";
            $settings['woocommerce_wf_packinglist_invoice_signature'] = '';
            $settings['wf_'.$this->module_base.'_product_meta_fields'] = array();
            $settings['wt_'.$this->module_base.'_product_attribute_fields'] = array();
            $settings['woocommerce_wf_enable_payment_link_in_invoice'] =  0;
            $settings['woocommerce_wf_show_pay_later_in_checkout']     =  0;
            $settings['woocommerce_wf_payment_link_in_order_status']   =  array();
            $settings['woocommerce_wf_pay_later_title']                =  __('Pay Later','wt_woocommerce_invoice_addon');
            $settings['woocommerce_wf_pay_later_description']          =  '';
            $settings['woocommerce_wf_pay_later_instuction']           =  '';

        }
        return $settings;
    }

    public function single_checkbox_fields($settings,$base_id,$tab_name){
        if($base_id === $this->module_id){
            $settings['wt_invoice_general']['wf_woocommerce_product_category_wise_splitting'] = "No";
            $settings['wt_invoice_general']['woocommerce_wf_packinglist_variation_data'] = "No";
            $settings['wt_invoice_general']['woocommerce_wf_enable_payment_link_in_invoice'] =  0;
            $settings['wt_invoice_general']['woocommerce_wf_show_pay_later_in_checkout']     =  0;
        }
        return $settings;
    }

    public function multi_checkbox_fields($settings, $base_id,$tab_name){
        if($base_id === $this->module_id){
            $settings['wt_invoice_general']['woocommerce_wf_payment_link_in_order_status'] = array('wc-on-hold');
            $settings['wt_invoice_advanced']['wf_'.$this->module_base.'_contactno_email'] = array();
            $settings['wt_invoice_advanced']['wf_'.$this->module_base.'_product_meta_fields'] = array();
            $settings['wt_invoice_advanced']['wt_'.$this->module_base.'_product_attribute_fields'] = array();
        }
        return $settings;
    }

    public function alter_validation_rule($arr, $base_id)
    {
        if($base_id === $this->module_id)
        {
            $arr['woocommerce_wf_packinglist_footer'] = array('type'=>'textarea');
            $arr['wf_'.$this->module_base.'_product_meta_fields'] = array('type'=>'text_arr');
            $arr['wt_'.$this->module_base.'_product_attribute_fields'] = array('type'=>'text_arr');

            $arr['woocommerce_wf_payment_link_in_order_status'] = array('type' =>  'text_arr');
            $arr['woocommerce_wf_pay_later_description']        = array('type' =>  'textarea');
            $arr['woocommerce_wf_pay_later_instuction']         = array('type' =>  'textarea');
        }
        return $arr;
    }

    public function add_pro_template($template_arr,$template_type){
        
        $module_id = Wf_Woocommerce_Packing_List::get_module_id($template_type);
        if($module_id === $this->module_id){
            include plugin_dir_path( __FILE__ ).'data/data.templates.php';
            return $template_arr;
        }
        return $template_arr;
    }

    public function get_customizable_items($settings,$base_id){
        
        if($base_id === $this->module_id)
        {
            $pro_features_disabled = array(
                                    "tracking_number_disabled",
                                    "product_table_subtotal_disabled",
                                    "product_table_shipping_disabled",
                                    "product_table_cart_discount_disabled",
                                    "product_table_order_discount_disabled",
                                    "product_table_total_tax_disabled",
                                    "product_table_fee_disabled",
                                    "product_table_coupon_disabled",
                                    "product_table_payment_method_disabled",
                                    "product_table_payment_total_disabled"
                                );
            foreach($pro_features_disabled as $pro_feature){
                if(isset($settings[$pro_feature])){
                    unset($settings[$pro_feature]);
                }
            }

            $pro_settings = array(
                'doc_title'                     => __('Document title','wt_woocommerce_invoice_addon'),
                'company_logo'                  => __('Company Logo','wt_woocommerce_invoice_addon'),
                'invoice_number'                => __('Invoice Number','wt_woocommerce_invoice_addon'),
                'order_number'                  => __('Order Number','wt_woocommerce_invoice_addon'),
                'invoice_date'                  => __('Invoice Date','wt_woocommerce_invoice_addon'),
                'order_date'                    => __('Order Date','wt_woocommerce_invoice_addon'),
                'received_seal'                 => __('Payment received stamp','wt_woocommerce_invoice_addon'),
                'from_address'                  => __('From Address','wt_woocommerce_invoice_addon'),
                'billing_address'               => __('Billing Address','wt_woocommerce_invoice_addon'),
                'shipping_address'              => __('Shipping Address','wt_woocommerce_invoice_addon'),
                'email'                         => __('Email Field','wt_woocommerce_invoice_addon'),
                'tel'                           => __('Tel Field','wt_woocommerce_invoice_addon'),
                'vat_number'                    => __('VAT Field','wt_woocommerce_invoice_addon'),
                'ssn_number'                    => __('SSN Field','wt_woocommerce_invoice_addon'),
                'shipping_method'               => __('Shipping Method','wt_woocommerce_invoice_addon'),
                'tracking_number'               => __('Tracking Number','wt_woocommerce_invoice_addon'),
                'product_table'                 => __('Product Table','wt_woocommerce_invoice_addon'),
                'product_table_subtotal'        => __('Subtotal','wt_woocommerce_invoice_addon'),
                'product_table_shipping'        => __('Shipping','wt_woocommerce_invoice_addon'),
                'product_table_cart_discount'   => __('Cart Discount','wt_woocommerce_invoice_addon'),
                'product_table_order_discount'  => __('Order Discount','wt_woocommerce_invoice_addon'),
                'product_table_total_tax'       => __('Total Tax','wt_woocommerce_invoice_addon'),
                'product_table_tax_item'        => __('Tax items','wt_woocommerce_invoice_addon'),
                'product_table_fee'             => __('Fee','wt_woocommerce_invoice_addon'),
                'product_table_coupon'          => __('Coupon info','wt_woocommerce_invoice_addon'),
                'product_table_payment_method'  => __('Payment Method','wt_woocommerce_invoice_addon'),
                'payment_link'                  => __('Payment Link','wt_woocommerce_invoice_addon'),
                'product_table_payment_total'   => __('Total','wt_woocommerce_invoice_addon'),
                'barcode'                       => __('Bar Code','wt_woocommerce_invoice_addon'),
                'qrcode'                        => __('QR Code','wt_woocommerce_invoice_addon'),
                'signature'                     => __('Signature','wt_woocommerce_invoice_addon'),
                'footer'                        => __('Footer','wt_woocommerce_invoice_addon'),
                'return_policy'                 => __('Return Policy','wt_woocommerce_invoice_addon'),
            );
            $show_qrcode_placeholder = apply_filters('wt_pklist_show_qrcode_placeholder_in_template',false,$this->module_base);
            if(!$show_qrcode_placeholder){
                unset($pro_settings['qrcode']);
            }
            $settings = array_unique(array_merge($pro_settings,$settings));
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
                        'label'         => __('Tax items value','wt_woocommerce_invoice_addon'),
                        'type'          => 'select',
                        'select_options'=> Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_customizer_presets('ind-tax'),
                        'css_prop'      => 'attr-data-ind-tax-display-option',
                        'trgt_elm'      => 'product_table_head_tax_items',
                        'event_class'   => 'wf_cst_change',
                        'width'         => '44%',
                    ),
                    array(
                        'label'         => __('Tax items text align','wt_woocommerce_invoice_addon'),
                        'type'          => 'select',
                        'select_options'=> Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_customizer_presets('text-align'),
                        'css_prop'      => 'text-align',
                        'trgt_elm'      => 'product_table_head_tax_items',
                        'event_class'   => 'wf_cst_change',
                        'width'         => '44%',
                        'float'         => 'right',
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
                    array(
                        'label'         => __('Total Tax value','wt_woocommerce_invoice_addon'),
                        'type'          => 'select',
                        'select_options'=> Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_customizer_presets('total-tax'),
                        'css_prop'      => 'attr-data-total-tax-display-option',
                        'trgt_elm'      => 'product_table_head_tax',
                        'event_class'   => 'wf_cst_change',
                        'width'         => '90%',
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

    /*
    * These are the fields that have no customizable options, Just on/off
    * 
    */
    public function get_non_options_fields($settings,$base_id)
    {
        if($base_id === $this->module_id)
        {   
            $template_type=$this->module_base;
            $show_qrcode_placeholder = apply_filters('wt_pklist_show_qrcode_placeholder_in_template',false,$template_type);
            if(!$show_qrcode_placeholder){
                return array(
                    'barcode',
                    'footer',
                    'return_policy',
                    'product_table_tax_item',
                );
            }else{
                return array(
                    'barcode',
                    'qrcode',
                    'footer',
                    'return_policy',
                    'product_table_tax_item',
                );
            }
        }
        return $settings;
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

    public function after_setting_update($the_options, $base_id){
        if($this->module_id === $base_id){
            $checkout_paylater  =   Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_show_pay_later_in_checkout',$this->module_id);
            if((1 === $checkout_paylater) || ("1" === $checkout_paylater)){
                $enable_paylater    =   "yes";
            }else{
                $enable_paylater    =   "no";
            }

            $paylater_title =   Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_pay_later_title',$this->module_id);
            $paylater_desc  =   Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_pay_later_description',$this->module_id);
            $paylater_inst  =   Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_pay_later_instuction',$this->module_id);
            $paylater_arr   =   array(
                'title'         =>  sanitize_text_field($paylater_title),
                'description'   =>  sanitize_textarea_field($paylater_desc), 
                'instructions'  =>  sanitize_textarea_field($paylater_inst),
                'enabled'       =>  $enable_paylater
            );
            $installed_payment_methods = WC()->payment_gateways->payment_gateways();

            if(array_key_exists("wf_pay_later",$installed_payment_methods)){
                if(get_option('woocommerce_gateway_order')){
                    $all_gateways = get_option('woocommerce_gateway_order');
                    if(!array_key_exists('wf_pay_later',$all_gateways)){
                        $paylater__serial_no            = count($all_gateways);
                        $all_gateways['wf_pay_later']   = $paylater__serial_no;
                        update_option('woocommerce_gateway_order',$all_gateways);
                    }
                }
            }
            update_option('woocommerce_wf_pay_later_settings',$paylater_arr);
        }
    }
    public function run_payment_link_module($plugin_name,$plugin_version){
        /**
         * The class responsible for pay later payment method to add the pay link in invoice
         * side of the site.
         */
        if((class_exists('Wf_Woocommerce_Packing_List_Pay_Later_Payment')) && (class_exists('WC_Payment_Gateway')))
        {
            add_filter('woocommerce_payment_gateways',array($this,'wf_paylater_add_to_gateways'),10,1);
            add_action('plugins_loaded',array($this,'save_paylater_settings_admin'),10,1);
            add_filter('woocommerce_valid_order_statuses_for_payment',array($this,'wf_allow_payment_for_order_status'),11,2);
            add_filter('woocommerce_available_payment_gateways',array($this,'hide_pay_later_payment_in_order_pay_page'),10,2);
            add_action( 'woocommerce_update_options_payment_gateways_wf_pay_later', array( $this, 'save_paylater_settings_admin' ),10);
        }
    }

    public function wf_paylater_add_to_gateways( $gateways ) {
        $gateways[] = 'Wf_Woocommerce_Packing_List_Pay_Later_Payment';
        return $gateways;
    }

    public function save_paylater_settings_admin(){
        if(get_option('woocommerce_wf_pay_later_settings')){
            $paylater_default_arr = array(
                'title'         => __('Pay Later','wt_woocommerce_invoice_addon'),
                'description'   => '',
                'instructions'  => '',
                'enabled'       => "no"
            );
            $paylater_details   = get_option('woocommerce_wf_pay_later_settings',$paylater_default_arr);
            if("yes" === $paylater_details['enabled']){
                $show_paylater = 1;
            }else{
                $show_paylater = 0;
            }
            
            Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_show_pay_later_in_checkout',$show_paylater,$this->module_id);
            Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_pay_later_title',sanitize_text_field($paylater_details['title']),$this->module_id);
            Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_pay_later_description',sanitize_textarea_field($paylater_details['description']),$this->module_id);
            Wf_Woocommerce_Packing_List::update_option('woocommerce_wf_pay_later_instuction',sanitize_textarea_field($paylater_details['instructions']),$this->module_id);
        }
    }

    public function wf_allow_payment_for_order_status($statuses, $order){
        $order_status = ( WC()->version < '2.7.0' ) ? $order->status : $order->get_status();
        if(!in_array('on-hold',$statuses) && ("on-hold" === $order_status)){
            $statuses[] = 'on-hold';
        }elseif(!in_array('failed',$statuses) && ("failed" === $order_status)){
            $statuses[] = 'failed';
        }
        return $statuses;
    }

    public function hide_pay_later_payment_in_order_pay_page( $available_gateways ) {
        // 1. On Order Pay page
        if( is_wc_endpoint_url( 'order-pay' ) ) {
            // Get an instance of the WC_Order Object
            $order = wc_get_order( get_query_var('order-pay') );

            // Loop through payment gateways 'pending', 'on-hold', 'processing'
            foreach( $available_gateways as $gateways_id => $gateways ){
                // Keep paypal only for "pending" order status
                if( "wf_pay_later" === $gateways_id && ($order->has_status('pending') || $order->has_status('on-hold') || $order->has_status('failed')) ) {
                    unset($available_gateways[$gateways_id]);
                }
            }
        }
        return $available_gateways;
    }

    public function convert_to_design_view_html($find_replace,$html,$template_type)
    {
        if($template_type === $this->module_base)
        {
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_from_address($find_replace,$template_type,$html);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_billing_address($find_replace, $template_type, $html);     
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_address($find_replace,$template_type, $html);  
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_default_order_fields($find_replace,$template_type,$html);   
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_product_table($find_replace,$template_type,$html);     
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_extra_charge_fields($find_replace,$template_type,$html);       
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_other_data($find_replace,$template_type,$html);
            $find_replace = $this->toggle_qrcode($find_replace,$html);
            $find_replace['[wfte_received_seal_extra_text]']='';
        }
        return $find_replace;
    }

    /**
     *  Items needed to be converted to HTML for print/download
     */
    public function generate_template_html($find_replace,$html,$template_type,$order,$box_packing=null,$order_package=null)
    {
        if($template_type === $this->module_base)
        {
            //Generate invoice number while printing invoice
            Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($order);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_logo($find_replace,$template_type,$html);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_from_address($find_replace,$template_type,$html);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_billing_address($find_replace, $template_type,$html,$order);      
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_address($find_replace,$template_type,$html,$order);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_default_order_fields($find_replace,$template_type,$html,$order);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_product_table($find_replace,$template_type,$html,$order);              
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_order_data($find_replace,$template_type,$html,$order);
            $find_replace = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::add_missing_placeholders($find_replace,$template_type,$html,$order);
            $find_replace = $this->toggle_qrcode($find_replace,$html);
            $find_replace=self::set_received_seal_extra_text($find_replace,$template_type,$html,$order);
        }
        return $find_replace;
    }

    private function toggle_qrcode($find_replace,$html)
	{
		$show_qrcode_placeholder = apply_filters('wt_pklist_show_qrcode_placeholder_in_template',false,$this->module_base);
		if(false === $show_qrcode_placeholder)
		{
			$find_replace['wfte_qrcode']='wfte_qrcode wfte_hidden';
			$find_replace['wfte_img_qrcode']='wfte_img_qrcode wfte_hidden';
		}else{
            if(false !== strpos($html, 'wfte_product_table_head_tax_items')){
                $find_replace['wfte_img_qrcode wfte_hidden']='wfte_img_qrcode';
            }
        }
		return $find_replace;
	}

    /**
    * Adding received seal extra text
    *   @since  1.0.0
    */
    private static function set_received_seal_extra_text($find_replace,$template_type,$html,$order)
    {
        if(false !== strpos($html,'[wfte_received_seal_extra_text]')) //if extra text placeholder exists then only do the process
        {
            $extra_text='';
            $find_replace['[wfte_received_seal_extra_text]']=apply_filters('wf_pklist_received_seal_extra_text',$extra_text,$template_type,$order);
        }
        return $find_replace;
    }
}
new Wf_Woocommerce_Packing_List_Invoice_Pro();