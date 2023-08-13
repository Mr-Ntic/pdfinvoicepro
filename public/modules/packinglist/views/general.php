<?php
if (!defined('ABSPATH')) {
    exit;
}

// remove the basic plugin fields of packingslip from general settings
if(isset($settings['packingslip_general_general'])){
    unset($settings['packingslip_general_general']);
}

$new_order_email_label = sprintf('%1$s <code>(%2$s)</code>',__("New order","wt_woocommerce_invoice_addon"),get_option( 'admin_email' ));
$order_statuses = wc_get_order_statuses();
$order_statuses_new = array(
    'wfte_new_order' => $new_order_email_label,
    'wfte_seperate_email' => __("Custom email","wt_woocommerce_invoice_addon")
);
$order_statuses = array_merge($order_statuses_new,$order_statuses);



$email_settings_path=admin_url('admin.php?page=wc-settings&tab=email&section=wf_woocommerce_packing_list_'.$this->module_base.'_email');

// add pro plugin fields of the packingslip in the general settings
$settings["packingslip_general_general"] = array(
    'wt_sub_head_pkslip_gen_general' => array(
        'type'  =>  'wt_sub_head',
        'class' =>  'wt_pklist_field_group_hd_sub',
        'heading_number' => 1,
        'label' =>  __("General",'wt_woocommerce_invoice_addon'),
    ),

    'woocommerce_wf_generate_for_orderstatus' => array(
        'type' => 'wt_select2_checkbox',
        'label' => __("Email packingslip automatically to","wt_woocommerce_invoice_addon"),
        'name' => 'woocommerce_wf_generate_for_orderstatus',
        'id' => 'woocommerce_wf_generate_for_orderstatus_st',
        'value' => $order_statuses,
        'checkbox_fields' => $order_statuses,
        'class' => 'woocommerce_wf_generate_for_orderstatus',
        'col' => 3,
        'placeholder' => __("Choose order status","wt_woocommerce_invoice_addon"),
        'help_text' => sprintf(__('Packing slip as PDF will be sent to the selected recipients. To configure email recipients, choose %1$s `custom email` %2$s and click on %1$s update settings. %2$s You can then configure the email notification from %3$s here. %4$s',"wt_woocommerce_invoice_addon"),'<b>','</b>','<a href="'.esc_url($email_settings_path).'" target="_blank">','</a>'),
        'alignment' => 'vertical_with_label',
        'ref_id' => 'woocommerce_wf_generate_for_orderstatus',
        'tooltip' => true,
    ),

    'wf_woocommerce_invoice_show_print_button' => array(
        'type' => 'wt_multi_checkbox',
        'label' => __("Show print packingslip button for customers","wt_woocommerce_invoice_addon"),
        'id' => '',
        'class' => 'wf_woocommerce_invoice_show_print_button',
        'name' => 'wf_woocommerce_invoice_show_print_button',
        'value' => '',
        'checkbox_fields' => array(
            'order_listing' => __('My account - Order lists page','wt_woocommerce_invoice_addon'),
            'order_details' => __('My account - Order details page', 'wt_woocommerce_invoice_addon'),
        ),
        'col' => 3,
        'alignment' => 'vertical_with_label',
        'tooltip' => true,
    ),

    'wt_inv_gen_hr_line_1' => array(
        'type' => 'wt_hr_line',
        'class' => 'wf_field_hr',
    )
);

$settings['packingslip_general_product_display'] = array(

    'wt_sub_head_pkslip_gen_prod_display' => array(
        'type'  =>  'wt_sub_head',
        'class' =>  'wt_pklist_field_group_hd_sub',
        'heading_number' => 2,
        'label' =>  __("Product display",'wt_woocommerce_invoice_addon'),
    ),

    'wf_woocommerce_product_category_wise_splitting' => array(
        'type' => 'wt_single_checkbox',
        'label'=>__("Group products by 'Category'",'wt_woocommerce_invoice_addon'),
        'name'=>"wf_woocommerce_product_category_wise_splitting",
        'value' => 'Yes',
        'checkbox_fields' => array('Yes'=> __("Enable to display and group product by category","wt_woocommerce_invoice_addon")),
        'class' => 'wf_woocommerce_product_category_wise_splitting',
        'id' => 'wf_woocommerce_product_category_wise_splitting',
        'tooltip' => true,
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

    'bundled_product_display_option' => array(
        'id' => 'bundled_product_display_option',
        'class' => 'bundled_product_display_option',
        'type'=>"wt_select_dropdown",
        'label'=>__("Bundled product display options",'wt_woocommerce_invoice_addon'),
        'name'=>"bundled_product_display_option",
        'help_text'=>sprintf(__('Choose how to display bundled products in the packing slip. Applicable only if you are using %s Woocommerce Product Bundles %s / %s YITH WooCommerce Product Bundle add-on %s. It may not work along with %sGroup by Category%s option.','wt_woocommerce_invoice_addon'), '<b>', '</b>', '<b>', '</b>', '<b>', '</b>' ),
        'select_dropdown_fields'=>array(
            'main-sub'=>__('Main product with bundle items', 'wt_woocommerce_invoice_addon'),
            'main'=>__('Main product only', 'wt_woocommerce_invoice_addon'),
            'sub'=>__('Bundle items only', 'wt_woocommerce_invoice_addon'),
        ),
        'help_text_conditional'=>array(
            array(
                'help_text'=>'<img src="'.WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'assets/images/bundle-both-items.png"/>',
                'condition'=>array(
                    array('field'=>'bundled_product_display_option', 'value'=>'main-sub')
                )
            ),
            array(
                'help_text'=>'<img src="'.WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'assets/images/bundle-parent-only.png"/>',
                'condition'=>array(
                    array('field'=>'bundled_product_display_option', 'value'=>'main')
                )
            ),
            array(
                'help_text'=>'<img src="'.WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'assets/images/bundle-child-only.png"/>',
                'condition'=>array(
                    array('field'=>'bundled_product_display_option', 'value'=>'sub')
                )
            ),
        ),
    ),
);
?>