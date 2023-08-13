<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
    <form method="post" class="wf_settings_form">
        <input type="hidden" value="creditnote" class="wf_settings_base" />
        <input type="hidden" value="wf_save_settings" class="wf_settings_action" />
        <input type="hidden" value="wt_creditnote_general" name="wt_tab_name" class="wt_tab_name" />
        <p><?php _e('Configure the general settings required for the credit note.','wt_woocommerce_invoice_addon');?></p>
        <?php
            // Set nonce:
            if (function_exists('wp_nonce_field'))
            {
                wp_nonce_field('wf-update-creditnote-'.WF_PKLIST_POST_TYPE);
            }
        ?>
        <table class="wf-form-table">
            <?php
                $order_statuses = wc_get_order_statuses();
                $order_meta_doc_url = 'https://www.webtoffee.com/adding-additional-fields-pdf-invoices-woocommerce/#add-order-meta';
                $product_meta_doc_url = 'https://www.webtoffee.com/adding-additional-fields-pdf-invoices-woocommerce/#add-product-meta';
                $product_attr_doc_url = 'https://www.webtoffee.com/adding-additional-fields-pdf-invoices-woocommerce/#add-product-attribute';

                $settings_arr['creditnote_general_general'] = array(
                    'wt_sub_head_crnt_gen_general' => array(
                        'type'  =>  'wt_sub_head',
                        'class' =>  'wt_pklist_field_group_hd_sub',
                        'heading_number' => 1,
                        'label' =>  __("General",'wt_woocommerce_invoice_addon'),
                    ),

                    'woocommerce_wf_generate_for_orderstatus' => array(
                        'type' => 'wt_select2_checkbox',
                        'label' => __("Create credit notes automatically","wt_woocommerce_invoice_addon"),
                        'name' => 'woocommerce_wf_generate_for_orderstatus',
                        'id' => 'woocommerce_wf_generate_for_orderstatus_st',
                        'value' => $order_statuses,
                        'checkbox_fields' => $order_statuses,
                        'class' => 'woocommerce_wf_generate_for_orderstatus',
                        'col' => 3,
                        'placeholder' => __("Choose order status","wt_woocommerce_invoice_addon"),
                        'help_text' => __("Creates credit notes for selected order statuses, if the order has any refunds","wt_woocommerce_invoice_addon"),
                        'alignment' => 'vertical_with_label',
                        'ref_id' => 'woocommerce_wf_generate_for_orderstatus',
                    ),

                    'woocommerce_wf_add_creditnote_in_mail' => array(
                        'type' => 'wt_single_checkbox',
                        'label'=>__("Attach credit note in ‘Refund’ email",'wt_woocommerce_invoice_addon'),
                        'name'=>"woocommerce_wf_add_creditnote_in_mail",
                        'value' => 'Yes',
                        'checkbox_fields' => array('Yes'=> __("Enable to attach credit note as PDF with WooCommerce refund email for the orders of above chosen statuses","wt_woocommerce_invoice_addon")),
                        'class' => 'woocommerce_wf_add_creditnote_in_mail',
                        'id' => 'woocommerce_wf_add_creditnote_in_mail',
                    ),

                    'wt_inv_gen_hr_line_1' => array(
                        'type' => 'wt_hr_line',
                        'class' => 'wf_field_hr',
                    )
                );

                $settings_arr['creditnote_general_product_display'] = array(
                    'wt_sub_head_pkslip_crnt_prod_display' => array(
                        'type'  =>  'wt_sub_head',
                        'class' =>  'wt_pklist_field_group_hd_sub',
                        'heading_number' => 2,
                        'label' =>  __("Product display",'wt_woocommerce_invoice_addon'),
                    ),

                    'sort_products_by' => array(
                        'type'=>'wt_select_dropdown',
                        'label'=>__("Sort products by", 'wt_woocommerce_invoice_addon'),
                        'name'=>"sort_products_by",
                        'select_dropdown_fields'=>array(
                            ''=>__('None', 'wt_woocommerce_invoice_addon'),
                            'name_asc'=>__('Name ascending', 'wt_woocommerce_invoice_addon'),
                            'name_desc'=>__('Name descending', 'wt_woocommerce_invoice_addon'),
                            'sku_asc'=>__('SKU ascending', 'wt_woocommerce_invoice_addon'),
                            'sku_desc'=>__('SKU descending', 'wt_woocommerce_invoice_addon'),
                        ),
                        'class' => 'sort_products_by',
                        'id' => 'sort_products_by',
                        'help_text'=>__('Sort products in ascending/descending order based on Name or SKU','wt_woocommerce_invoice_addon')
                    ),

                    'woocommerce_wf_packinglist_variation_data' => array(
                        'type' => 'wt_single_checkbox',
                        'label'=>__("Show variation data below each product",'wt_woocommerce_invoice_addon'),
                        'name'=>"woocommerce_wf_packinglist_variation_data",
                        'value' => 'Yes',
                        'checkbox_fields' => array('Yes'=> __("Enable to include variation data beneath the product name","wt_woocommerce_invoice_addon")),
                        'class' => 'woocommerce_wf_packinglist_variation_data',
                        'id' => 'woocommerce_wf_packinglist_variation_data',
                        'tooltip' => true,
                    ),

                    'wt_inv_gen_hr_line_1' => array(
                        'type' => 'wt_hr_line',
                        'class' => 'wf_field_hr',
                    )
                );
                $settings_arr['creditnote_general_creditnote_number'] = array(
                    'wt_sub_head_crnt_gen_crnt_no' => array(
                        'type' => 'wt_sub_head',
                        'class' => 'wt_pklist_field_group_hd_sub',
                        'label' => __("Credit note number",'wt_woocommerce_invoice_addon'),
                        'heading_number' => 2,
                        'ref_id' => 'wt_sub_head_4'
                    ),

                    'woocommerce_wf_invoice_as_ordernumber' => array(
                        'type' => 'wt_radio',
                        'label' => __("Set credit note number using","wt_woocommerce_invoice_addon"),
                        'id' => '',
                        'class' => 'invoice_preview_assert',
                        'name' => 'woocommerce_wf_invoice_as_ordernumber',
                        'value' => '',
                        'radio_fields' => array(
                                'Yes'=>__('Woocommerce order number','wt_woocommerce_invoice_addon'),
                                'No'=>__('Custom number series','wt_woocommerce_invoice_addon')
                            ),
                        'form_toggler'=>array(
                            'type'=>'parent',
                            'target'=>'wwpl_custom_inv_no',
                        ),
                        'col' => 3,
                        'end_col_call_back' => 'invoice_number_preview',
                        'module_base'=>$this->module_base,
                        'tooltip' => true,
                        'alignment' => 'horizontal_with_label',
                        'ref_id' => 'woocommerce_wf_orderdate_as_invoicedate',
                    ),

                    'woocommerce_wf_invoice_start_number' => array(
                        'type' => 'wt_invoice_start_number_text_input',
                        'label' => __('Credit note start number','wt_woocommerce_invoice_addon'),
                        'name' => 'woocommerce_wf_invoice_start_number',
                        'tr_id' => 'woocommerce_wf_invoice_start_number_tr',
                        'tooltip' => true,
                        'form_toggler'=>array(
                            'type'=>'child',
                            'val'=>'No',
                            'id'=>'wwpl_custom_inv_no',
                            'lvl' => 2,
                        )
                    ),

                    'woocommerce_wf_invoice_number_format' => array(
                        'type'=>'wt_select_dropdown',
                        'label'=>__("Credit note number format", 'wt_woocommerce_invoice_addon'),
                        'name'=>"woocommerce_wf_invoice_number_format",
                        'select_dropdown_fields'=>array(
                            '[number]'=>__('[number]', 'wt_woocommerce_invoice_addon'),
                            '[number][suffix]'=>__('[number][suffix]', 'wt_woocommerce_invoice_addon'),
                            '[prefix][number]'=>__('[prefix][number]', 'wt_woocommerce_invoice_addon'),
                            '[prefix][number][suffix]'=>__('[prefix][number][suffix]', 'wt_woocommerce_invoice_addon'),
                        ),
                        'class' => 'invoice_preview_assert',
                        'id' => 'woocommerce_wf_invoice_number_format',
                        'help_text' => __("Allows setting a combination of the prefix, number, and/or suffix as your creditnote number.","wt_woocommerce_invoice_addon"),
                    ),

                    'woocommerce_wf_invoice_number_prefix' => array(
                        'type' => 'wt_text',
                        'label'=>__("Prefix",'wt_woocommerce_invoice_addon'),
                        'name'=>"woocommerce_wf_invoice_number_prefix",
                        'help_text'=>sprintf(__("Use any of the %s date formats %s or alphanumeric characters.", 'wt_woocommerce_invoice_addon'), '<a class="wf_inv_num_frmt_hlp_btn" data-wf-trget="woocommerce_wf_invoice_number_prefix">', '</a>'),
                        'class' => 'invoice_preview_assert',
                    ),
                    
                    'woocommerce_wf_invoice_number_postfix' => array(
                        'type' => 'wt_text',
                        'label'=>__("Suffix",'wt_woocommerce_invoice_addon'),
                        'name'=>"woocommerce_wf_invoice_number_postfix",
                        'help_text'=>sprintf(__("Use any of the %s date formats %s or alphanumeric characters.", 'wt_woocommerce_invoice_addon'), '<a class="wf_inv_num_frmt_hlp_btn" data-wf-trget="woocommerce_wf_invoice_number_postfix">', '</a>'),
                        'class' => 'invoice_preview_assert',
                    ),
                    
                    'woocommerce_wf_invoice_padding_number' => array(
                        'type'=>'wt_number',
                        'label'=>__("Credit note number length",'wt_woocommerce_invoice_addon'),
                        'name'=>"woocommerce_wf_invoice_padding_number",
                        'attr'=>'min="0"',
                        'help_text'=>__("Indicates the total length of the invoice number, excluding the length of prefix and suffix if added. If the length of the generated invoice number is less than the provided, it will be padded with ‘0’. This setting can be experimented through the preview section. <br> E.g if you specify 7 as invoice length and your invoice number is 8009, it will be represented as 0008009 in the respective documents.", "wt_woocommerce_invoice_addon"),
                        'class' => 'invoice_preview_assert',
                        'id' => 'woocommerce_wf_invoice_padding_number',
                    ),

                    'wt_inv_gen_hr_line_3' => array(
                        'type' => 'wt_hr_line',
                        'class' => 'wf_field_hr',
                        'ref_id' => 'wt_hr_line_4',
                    )
                );
                $settings_arr['creditnote_general_advanced'] = array(

                    'wt_sub_head_pkslip_crnt_advanced_option' => array(
                        'type'  =>  'wt_sub_head',
                        'class' =>  'wt_pklist_field_group_hd_sub',
                        'heading_number' => 3,
                        'label' =>  __("Advanced",'wt_woocommerce_invoice_addon'),
                    ),

                    'order_meta_fields_pro' => array(
                        'type'=>"order_meta_fields_pro",
                        'label'=>__("Order meta fields",'wt_woocommerce_invoice_addon'),
                        'name'=>'wf_'.$this->module_base.'_contactno_email',
                        'module_base'=>$this->module_base,
                        'help_text'=>__("Select/add order meta to display additional information related to the order on the credit note.","wt_woocommerce_invoice_addon"),
                        'tooltip' => true,
                    ),

                    'product_meta_fields_pro' => array(
                        'type'=>"product_meta_fields_pro",
                        'label'=>__("Product meta fields",'wt_woocommerce_invoice_addon'),
                        'name'=>'wf_'.$this->module_base.'_product_meta_fields',
                        'module_base'=>$this->module_base,
                        'help_text'=>__("Select /add product meta to display additional information related to the products on the credit note. The selected product meta will be displayed beneath the respective product in the credit note.","wt_woocommerce_invoice_addon"),
                        'tooltip' => true,
                    ),

                    'product_attribute_pro' => array(
                        'type'=>"product_attribute_pro",
                        'label'=>__("Product attributes", 'wt_woocommerce_invoice_addon'),
                        'name'=>'wt_'.$this->module_base.'_product_attribute_fields',
                        'module_base'=>$this->module_base,
                        'help_text'=>__("Select/add product attributes to display additional information related to the product on the creditnote. The selected product attributes will be displayed beneath the respective product in the credit note.","wt_woocommerce_invoice_addon"),
                        'tooltip' => true,
                    )
                );

                $settings_arr = Wf_Woocommerce_Packing_List::add_fields_to_settings($settings_arr,$target_id,$this->module_base,$this->module_id);
                if(class_exists('WT_Form_Field_Builder_PRO_Documents')){
                    $Form_builder = new WT_Form_Field_Builder_PRO_Documents();
                }else{
                    $Form_builder = new WT_Form_Field_Builder();
                }

                $h_no = 1;
                foreach($settings_arr as $settings){
                    foreach($settings as $k => $this_setting){
                        if(isset($this_setting['type']) && "wt_sub_head" === $this_setting['type']){
                            $settings[$k]['heading_number'] = $h_no;
                            $h_no++;
                        }
                    }
                    $Form_builder->generate_form_fields($settings, $this->module_id);
                }
            ?>
        </table>

        <?php
        if(class_exists('Wf_Woocommerce_Packing_List_Pro_Common_Func')){
            include plugin_dir_path( WT_PKLIST_INVOICE_ADDON_FILENAME )."includes/views/sequential_number_date_popup_pro.php";
        } 
        include plugin_dir_path( WT_PKLIST_INVOICE_ADDON_FILENAME )."admin/views/_custom_field_editor_form.php"; 
        include plugin_dir_path( WF_PKLIST_PLUGIN_FILENAME )."admin/views/admin-settings-save-button.php"; 
       
        //settings form fields for module
        do_action('wf_pklist_document_settings_form');
        ?>  
    </form>
</div>
<?php do_action('wf_pklist_document_out_settings_form');?> 