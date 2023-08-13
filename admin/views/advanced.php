<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wf-tab-content" data-id="<?php echo $target_id;?>">
    <form method="post" class="wf_settings_form wf_general_settings_form">
        <input type="hidden" value="main" class="wf_settings_base" />
        <input type="hidden" value="wf_save_settings" class="wf_settings_action" />
        <input type="hidden" value="wt_main_advanced" class="wt_tab_name" name="wt_tab_name">
        <?php
        if (function_exists('wp_nonce_field'))
        {
            wp_nonce_field(WF_PKLIST_PLUGIN_NAME);
        }
        ?>
        <table class="wf-form-table">
            <tbody>
                <?php 
                    $settings_arr['advanced_option'] = array(
                        'wf_invoice_additional_checkout_data_fields' => array(
                            'type' => 'wf_invoice_additional_checkout_data_fields',
                            'label' => __('Add additional fields on checkout page','wt_woocommerce_invoice_addon'),
                            'name'  =>  'wf_invoice_additional_checkout_data_fields',
                            'id'    =>  'wf_invoice_additional_checkout_data_fields',
                            'class' =>  'wf_invoice_additional_checkout_data_fields',
                            'tooltip' => true,
                            'placeholder' => __("No fields added yet","wt_woocommerce_invoice_addon"),
                        ),
                        'woocommerce_wf_tracking_number' => array(
                            'type' => 'wt_text',
                            'label' => __("Tracking number meta","wt_woocommerce_invoice_addon"),
                            'id' => 'woocommerce_wf_tracking_number',
                            'name' => 'woocommerce_wf_tracking_number',
                            'class' => 'woocommerce_wf_tracking_number',
                            'help_text' => __("Enter the tracking number meta field to add tracking number information","wt_woocommerce_invoice_addon"),
                            'tooltip' => true,
                        ),
                       'woocommerce_wf_state_code_disable' => array(
                            'type' => 'wt_single_checkbox',
                            'label' => __("Display state name","wt_woocommerce_invoice_addon"),
                            'id' => 'woocommerce_wf_state_code_disable',
                            'name' => 'woocommerce_wf_state_code_disable',
                            'value' => "yes",
                            'checkbox_fields' => array('yes'=> __("Enable to show state name in addresses","wt_woocommerce_invoice_addon")),
                            'class' => "woocommerce_wf_state_code_disable",
                            'col' => 3,
                            'tooltip' => true,
                        ),

                        'woocommerce_wf_packinglist_preview' => array(
                            'type' => 'wt_single_checkbox',
                            'label' => __("Preview before printing","wt_woocommerce_invoice_addon"),
                            'id' => 'woocommerce_wf_packinglist_preview',
                            'name' => 'woocommerce_wf_packinglist_preview',
                            'value' => "enabled",
                            'checkbox_fields' => array('enabled'=> __("Preview documents before printing","wt_woocommerce_invoice_addon")),
                            'class' => "woocommerce_wf_packinglist_preview",
                            'col' => 3,
                            'tooltip' => true,
                        ),

                        'woocommerce_wf_add_rtl_support' => array(
                            'type' => 'wt_single_checkbox',
                            'label' => __("Enable RTL support","wt_woocommerce_invoice_addon"),
                            'id' => 'woocommerce_wf_add_rtl_support',
                            'name' => 'woocommerce_wf_add_rtl_support',
                            'value' => "Yes",
                            'checkbox_fields' => array('Yes'=> __("RTL support for documents","wt_woocommerce_invoice_addon")),
                            'class' => "woocommerce_wf_add_rtl_support",
                            'col' => 3,
                            'help_text' => sprintf('%1$s <a href="https://wordpress.org/plugins/mpdf-addon-for-pdf-invoices/">%2$s</a>.',
                                __("For better RTL integration in PDF documents, please use our","wt_woocommerce_invoice_addon"),
                                __("mPDF add-on","wt_woocommerce_invoice_addon")),
                        ),
                    );
                    if(is_array($pdf_libs) && count($pdf_libs)>1)
                    {
                        $pdf_libs_form_arr=array();
                        foreach ($pdf_libs as $key => $value)
                        {
                            $pdf_libs_form_arr[$key]=(isset($value['title']) ? $value['title'] : $key);
                        }
                        $settings_arr['advanced_option']['active_pdf_library']=array(
                            'type'  =>  "wt_radio",
                            'label' =>  __("PDF library",'wt_woocommerce_invoice_addon'),
                            'name'  =>  "active_pdf_library",
                            'radio_fields'  =>  $pdf_libs_form_arr,
                            'tooltip' => true,
                        );
                    }

                    $is_tax_enabled=wc_tax_enabled();
                    $tax_not_enabled_info='';
                    $incl_tax_img_src = $wf_admin_img_path.'/incl_tax.png';
                    $excl_tax_img_src = $wf_admin_img_path.'/excl_tax.png';
                    $incl_tax_img = '<br><br><img src="'.esc_url($incl_tax_img_src).'" alt="include tax img" width="100%" style="background: #f0f0f1;padding: 10px;">'; 
                    $excl_tax_img = '<br><br><img src="'.esc_url($excl_tax_img_src).'" alt="exclude tax img" width="100%" style="background: #f0f0f1;padding: 10px;">'; 
                    if(!$is_tax_enabled)
                    {
                        $tax_not_enabled_info.='<br>';
                        $tax_not_enabled_info.=sprintf(__('%sNote:%s You have not enabled tax option in WooCommerce. If you need to apply tax for new orders you need to enable it %s here. %s', 'wt_woocommerce_invoice_addon'), '<b>', '</b>', '<a href="'.admin_url('admin.php?page=wc-settings').'" target="_blank">', '</a>');
                        $incl_tax_img = ""; 
                        $excl_tax_img = "";
                    }
                    $settings_arr['advanced_option']['woocommerce_wf_generate_for_taxstatus'] = array(
                        'type'  => 'wt_radio',
                        'label' => __('Display price in the product table', 'wt_woocommerce_invoice_addon'),
                        'name'  => 'woocommerce_wf_generate_for_taxstatus',
                        'id'    => 'woocommerce_wf_generate_for_taxstatus',
                        'class' => 'woocommerce_wf_generate_for_taxstatus',
                        'attr'=>($is_tax_enabled ? '' : "disabled"),
                        'after_form_field'=>($is_tax_enabled ? '<a href="'.admin_url('admin.php?page=wc-settings&tab=tax').'" class="" target="_blank" style="text-align:center;">'.__('WooCommerce tax settings', 'wt_woocommerce_invoice_addon').'<span class="dashicons dashicons-external"></span></a>' : ""),
                            'radio_fields'  =>  array(
                                'ex_tax'=>__('Exclude tax','wt_woocommerce_invoice_addon'),
                                'in_tax'=>__('Include tax','wt_woocommerce_invoice_addon'),
                            ),
                        'help_text_conditional'=>array(
                            array(
                                'help_text'=>__('All price columns displayed will be inclusive of tax.', 'wt_woocommerce_invoice_addon').$tax_not_enabled_info.$incl_tax_img,
                                'condition'=>array(
                                    array('field'=>'woocommerce_wf_generate_for_taxstatus', 'value'=>'in_tax')
                                )
                            ),
                            array(
                                'help_text'=>__('All price columns displayed will be exclusive of tax.', 'wt_woocommerce_invoice_addon').$tax_not_enabled_info.$excl_tax_img,
                                'condition'=>array(
                                    array('field'=>'woocommerce_wf_generate_for_taxstatus', 'value'=>'ex_tax')
                                )
                            )
                        ),
                    );

                    $packaging_doc_types = array();
                    if(class_exists('Wf_Woocommerce_Packing_List_Pro_Common_Func')){
                        $packaging_doc_types['packingslip'] = __("Packing slip","wt_woocommerce_invoice_addon");
                    }
                    if(class_exists('Wf_Woocommerce_Packing_List_Pro_Common_Func_SDD')){
                        $packaging_doc_types['shippinglabel'] = __("Shipping label","wt_woocommerce_invoice_addon");
                        $packaging_doc_types['deliverynote'] = __("Delivery note","wt_woocommerce_invoice_addon");
                    }
                    $pack_help = "";
                    if(!empty($packaging_doc_types)){
                        $pack_help = sprintf(__('Note: This option is applicable on %1s only','wt_woocommerce_invoice_addon'),implode(', ', $packaging_doc_types));
                    }
                    $settings_arr['advanced_option']['woocommerce_wf_packinglist_package_type'] = array(
                         'type'=>'wt_select_dropdown',
                            'label'=>__("Packaging type", 'wt_woocommerce_invoice_addon'),
                            'name'=>"woocommerce_wf_packinglist_package_type",
                            'select_dropdown_fields'=>array(
                               'pack_items_individually'=>__('Pack items individually','wt_woocommerce_invoice_addon'),
                                'box_packing'=>__('Box packing','wt_woocommerce_invoice_addon'),
                                'single_packing'=>__('Single package per order','wt_woocommerce_invoice_addon')
                            ),
                            'class' => 'woocommerce_wf_packinglist_package_type',
                            'id' => 'woocommerce_wf_packinglist_package_type',
                            'tooltip' => true,
                            'help_text' => sprintf('<b>%1$s</b> - %2$s <br><b>%3$s</b> - %4$s <br><b>%5$s</b> - %6$s <br><b>%7$s</b>',
                                __("Single package(per order)","wt_woocommerce_invoice_addon"),
                                __("All the items belonging to an order are packed together into a single package. Every order will have a respective package","wt_woocommerce_invoice_addon"),
                                __("Box packing(per order)","wt_woocommerce_invoice_addon"),
                                __("All the items belonging to an order are packed into the respective boxes as per the configuration. Every order may have one or more boxes based on the configuration","wt_woocommerce_invoice_addon"),
                                __("Pack items individually","wt_woocommerce_invoice_addon"),
                                __("Every item from the order/s are packed individually. e.g if an order has 2 quantities of product A and 1 quantity of product B, there will be three packages consisting one item each from the order","wt_woocommerce_invoice_addon"),
                                $pack_help
                            ),
                            'form_toggler'=>array(
                                'type'=>'parent',
                                'target'=>'wf_box_packing_table',
                            )
                    );
                    $settings_arr = Wf_Woocommerce_Packing_List::add_fields_to_settings($settings_arr);
                    if(class_exists('WT_Form_Field_Builder_PRO_Documents')){
                        $Form_builder = new WT_Form_Field_Builder_PRO_Documents();
                    }else{
                        $Form_builder = new WT_Form_Field_Builder();
                    }
                    foreach($settings_arr as $settings){
                        $Form_builder->generate_form_fields($settings);
                    }
                ?>
            </tbody>
        </table>

        <!-- box dimensions -->
        <div wf_frm_tgl-id="wf_box_packing_table" wf_frm_tgl-val="box_packing">
            <?php
            $wf_packlist_boxes=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_boxes');
            $weight_unit = get_option('woocommerce_weight_unit');
            $dimension_unit = get_option('woocommerce_dimension_unit');
            ?>
            <input type='hidden' id='dimension_unit' value='<?php echo esc_attr($dimension_unit); ?>'>
            <input type='hidden' id='weight_unit' value='<?php echo esc_attr($weight_unit); ?>'>
            <strong><?php _e('Box Sizes','wt_woocommerce_invoice_addon'); ?></strong><br><br>
            <table class="woocommerce_wf_packinglist_boxes widefat">
                <thead>
                    <tr>
                        <th class="check-column" style="padding: 0px; vertical-align: middle;"><input type="checkbox" /></th>
                        <th><?php _e('Name','wt_woocommerce_invoice_addon'); ?></th>
                        <th><?php _e('Length','wt_woocommerce_invoice_addon'); ?></th>
                        <th><?php _e('Width','wt_woocommerce_invoice_addon'); ?></th>
                        <th><?php _e('Height','wt_woocommerce_invoice_addon'); ?></th>
                        <th><?php _e('Box Weight','wt_woocommerce_invoice_addon'); ?></th>
                        <th><?php _e('Max Weight','wt_woocommerce_invoice_addon'); ?></th>
                        <th><?php _e('Enabled','wt_woocommerce_invoice_addon'); ?></th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th colspan="8">
                            <div style="float:left; margin:0px 15px;">
                                <a href="#" class="button plus insert"><?php _e('Add Box','wt_woocommerce_invoice_addon'); ?></a>
                                <a href="#" class="button minus remove"><?php _e('Remove selected box(es)','wt_woocommerce_invoice_addon'); ?></a>
                            </div>
                            <small class="description"><?php _e('Items will be packed into these boxes depending on its dimensions and volume, those that do not fit will be packed individually.','wt_woocommerce_invoice_addon'); ?></small>
                        </th>
                    </tr>
                </tfoot>
                <tbody id="rates">
                <?php
                if ($wf_packlist_boxes) 
                {
                    foreach ($wf_packlist_boxes as $key => $box) 
                    {
                        if (!is_numeric($key))
                        continue;
                    ?>
                        <tr>
                            <td class="check-column" style="padding: 10px; vertical-align: middle;"><input type="checkbox" /></td>
                            <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][name]" value="<?php echo esc_attr($box['name']); ?>" /></td>
                            <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][length]" value="<?php echo esc_attr($box['length']); ?>" /><?php echo $dimension_unit; ?></td>
                            <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][width]" value="<?php echo esc_attr($box['width']); ?>" /><?php echo $dimension_unit; ?></td>
                            <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][height]" value="<?php echo esc_attr($box['height']); ?>" /><?php echo $dimension_unit; ?></td>
                            <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][box_weight]" value="<?php echo esc_attr($box['box_weight']); ?>" /><?php echo $weight_unit; ?></td>
                            <td><input type="text" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][max_weight]" value="<?php echo esc_attr($box['max_weight']); ?>" /><?php echo $weight_unit; ?></td>
                            <td><input type="checkbox" name="woocommerce_wf_packinglist_boxes[<?php echo $key; ?>][enabled]" <?php checked($box['enabled'], true); ?> /></td>
                        </tr>
                    <?php
                    }
                }
                ?>
                </tbody>
            </table>    
        </div>
        <!-- box dimensions -->
        <?php 
            include "admin-settings-save-button.php";
        ?>
    </form>
</div>