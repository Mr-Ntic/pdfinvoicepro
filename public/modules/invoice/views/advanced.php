<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wf-tab-content" data-id="<?php echo esc_attr($target_id);?>">
	<form method="post" class="wf_settings_form">
        <input type="hidden" value="invoice" class="wf_settings_base" />
        <input type="hidden" value="wf_save_settings" class="wf_settings_action" />
        <input type="hidden" value="wt_invoice_advanced" name="wt_tab_name" class="wt_tab_name" />
        <?php
        // Set nonce:
        if (function_exists('wp_nonce_field'))
        {
            wp_nonce_field('wf-update-invoice-'.WF_PKLIST_POST_TYPE);
        }
        ?>
        <table class="wf-form-table">
            <tbody>
            	<?php
                    $order_meta_doc_url = 'https://www.webtoffee.com/adding-additional-fields-pdf-invoices-woocommerce/#add-order-meta';
                    $product_meta_doc_url = 'https://www.webtoffee.com/adding-additional-fields-pdf-invoices-woocommerce/#add-product-meta';
                    $product_attr_doc_url = 'https://www.webtoffee.com/adding-additional-fields-pdf-invoices-woocommerce/#add-product-attribute';

            		$settings_arr['invoice_advanced_general'] = array(
                        'order_meta_fields_pro' => array(
                            'type'          => "order_meta_fields_pro",
                            'label'         => __("Order meta fields",'wt_woocommerce_invoice_addon'),
                            'name'          => 'wf_'.$this->module_base.'_contactno_email',
                            'module_base'   => $this->module_base,
                            'help_text'     => __("Select/add order meta to display additional information related to the order on the invoice. The selected order meta will be displayed beneath the respective product in the invoice.","wt_woocommerce_invoice_addon"),
                            'tooltip'       => true,
                        ),
                        'product_meta_fields_pro' => array(
                            'type'          => "product_meta_fields_pro",
                            'label'         => __("Product meta fields",'wt_woocommerce_invoice_addon'),
                            'name'          => 'wf_'.$this->module_base.'_product_meta_fields',
                            'module_base'   => $this->module_base,
                            'help_text'     => __("Select/add product meta to display additional information related to the order on the invoice. The selected product meta will be displayed beneath the respective product in the invoice.","wt_woocommerce_invoice_addon"),
                            'tooltip'       => true,
                        ),
                        'product_attribute_pro' => array(
                            'type'          => "product_attribute_pro",
                            'label'         => __("Product attributes", 'wt_woocommerce_invoice_addon'),
                            'name'          => 'wt_'.$this->module_base.'_product_attribute_fields',
                            'module_base'   => $this->module_base,
                            'help_text'     => __("Select/add product attributes to display additional information related to the products in the invoice. The selected product attributes will be displayed beneath the respective product in the invoice.","wt_woocommerce_invoice_addon"),
                            'tooltip'       => true,
                        ),
            			'woocommerce_wf_packinglist_logo' => array(
                            'type'          => "wt_uploader",
                            'label'         => __("Custom logo for invoice",'wt_woocommerce_invoice_addon'),
                            'name'          => "woocommerce_wf_packinglist_logo",
                            'id'            => "woocommerce_wf_packinglist_logo",
                            'help_text'     => sprintf('%1$s <b>%2$s</b> %3$s <b>%4$s</b> %5$s <b>%6$s</b>. %7$s.',
                                            __("If left blank, the logo from the","wt_woocommerce_invoice_addon"),
                                            __("General settings","wt_woocommerce_invoice_addon"),
                                            __("will be used by default. To include the uploaded image to the invoice, ensure to select","wt_woocommerce_invoice_addon"),
                                            __("Company logo","wt_woocommerce_invoice_addon"),
                                            __("from","wt_woocommerce_invoice_addon"),
                                            __("Invoice > Customize > Company Logo / Name"),
                                            __("Recommended size is 150×50px","wt_woocommerce_invoice_addon")
                                        ),
                            'tooltip'       => true,
                        ),
                        'woocommerce_wf_packinglist_invoice_signature' => array(
                            'type'          => "wt_uploader",
                            'label'         => __("Upload signature",'wt_woocommerce_invoice_addon'),
                            'name'          => "woocommerce_wf_packinglist_invoice_signature",
                            'id'            => "woocommerce_wf_packinglist_invoice_signature",
                            'tooltip'       => true,
                        ),
                        'woocommerce_wf_packinglist_footer' => array(
                            'type'          =>  'wt_textarea',
                            'label'         =>  __("Custom footer for invoice",'wt_woocommerce_invoice_addon'),
                            'name'          =>	'woocommerce_wf_packinglist_footer',
                            'id'            => 'woocommerce_wf_packinglist_footer',
                            'class'         => 'woocommerce_wf_packinglist_footer',
                            'ref_id'        =>  'woocommerce_wf_packinglist_footer',
                            'help_text'     => __("Footer content to be included in the invoice.",'wt_woocommerce_invoice_addon'),
                            'tooltip'       => true,
                        )
            		);
            		
                    if(class_exists('WT_Form_Field_Builder_PRO_Documents')){
                        $Form_builder = new WT_Form_Field_Builder_PRO_Documents();
                    }else{
                        $Form_builder = new WT_Form_Field_Builder();
                    }
                    
                    $settings_arr = Wf_Woocommerce_Packing_List::add_fields_to_settings($settings_arr,$target_id,$this->module_base,$this->module_id);
                    foreach($settings_arr as $settings){
                        $Form_builder->generate_form_fields($settings, $this->module_id);
                    }
            	?>
            </tbody>
        </table>
        <?php
            include plugin_dir_path( WT_PKLIST_INVOICE_ADDON_FILENAME )."admin/views/_custom_field_editor_form.php"; 
            include plugin_dir_path( WT_PKLIST_INVOICE_ADDON_FILENAME )."admin/views/admin-settings-save-button.php";
        ?>
    </form>
</div>