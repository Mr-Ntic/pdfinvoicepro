<?php
if (!defined('ABSPATH')) {
	exit;
}

class WT_Form_Field_Builder_PRO_Documents extends WT_Form_Field_Builder{

	/**
	 * @since 1.0.0
	 * 
	 * Function to display the plain text field using form builder
	 */
	public function wt_plaintext($args,$base_id){
		extract($this->verify_the_fields($args));
		return sprintf('<tr><td>%1$s</td><td>%2$s</td><td></td></tr>',wp_kses_post($label),wp_kses_post($value));
	}

	/**
	 * @since 1.0.0
	 * 
	 * Function to display the temporary files count field using form builder
	 */
	public function wt_temp_files_field($args,$base_id){
		extract($this->verify_the_fields($args));
		$total_temp_files=Wt_woocommerce_invoice_addon_Admin::get_total_temp_files();
		$html = '<td style="width:50%;">';
		$total_temp_files = 1;
		if($total_temp_files > 0){
			$html .=sprintf('<span style="line-height:38px;" class="temp_files_count">%1$s %2$s</span>',
							wp_kses_post($total_temp_files), __("Temp file(s) found.","wt_woocommerce_invoice_addon"));
			$html .=sprintf('<button type="button" class="button button-secondary wt_pklist_temp_files_btn" data-action="delete_all_temp" style="float:right;">%1$s</button><button type="button" class="button button-secondary wt_pklist_temp_files_btn" data-action="download_all_temp" style="float:right; margin-right:10px;">%2$s</button>',
				__("Delete all","wt_woocommerce_invoice_addon"),
				__("Download all","wt_woocommerce_invoice_addon"));
		}else{
			$html .=sprintf(__("No files found","wt_woocommerce_invoice_addon"));
		}
		$html .=sprintf('</td><td></td>');
		return $html;
	}

	/**
	 * @since 1.0.0
	 * 
	 * Function to display the additional checkout field in advanced - general tab using form builder
	 */
	public function wf_invoice_additional_checkout_data_fields($args,$base_id){
		extract($this->verify_the_fields($args,$base_id));
		$custom_field_doc_url = 'https://www.webtoffee.com/how-to-add-a-custom-field-to-checkout-page/';
		$user_selected_add_fields 	= array();
		$add_checkout_data_flds 	= Wf_Woocommerce_Packing_List::$default_additional_checkout_data_fields;
        $user_created_ck_fields 	= Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');

        /* if it is a numeric array convert it to associative.*/
        $user_created_ck_fields 	= Wf_Woocommerce_Packing_List_Pro_Common_Func::process_checkout_fields($user_created_ck_fields);
        $ck_fields = array_merge($add_checkout_data_flds,$user_created_ck_fields);
        $result = Wf_Woocommerce_Packing_List::get_option($name,$base_id);
        $user_selected_arr 	= ($result && is_array($result)) ? $result : array();
        $additional_checkout_field_options=Wf_Woocommerce_Packing_List::get_option('wt_additional_checkout_field_options');

		$html = '<td>';
		$html .= sprintf('<div class="wf_select_multi">
								<input type="hidden" name="wf_invoice_additional_checkout_data_fields_hidden" value="1" />
								<select class="wc-enhanced-select" data-placeholder="%2$s" name="%1$s[]" multiple="multiple">',
								esc_attr($name),
								esc_attr($placeholder));

		foreach($ck_fields as $ck_key => $ck_value){
			$add_data=isset($additional_checkout_field_options[$ck_key]) ? $additional_checkout_field_options[$ck_key] : array();
			$is_required=(int) (isset($add_data['is_required']) ? $add_data['is_required'] : 0);
			$is_required_label = (1 === $is_required) ? '('.__("required","wt_woocommerce_invoice_addon").')' : "";
			$selected = in_array($ck_key,$user_selected_arr) ? "selected" : "";

			/* we are giving option to edit title of builtin items */
        	$ck_value=(isset($add_data['title']) && trim($add_data['title'])!="" ? $add_data['title'] : $ck_value);

        	$html .= sprintf('<option value="%1$s" %2$s>%3$s (%1$s) %4$s</option>',
        		wp_kses_post($ck_key),
        		$selected,
        		wp_kses_post($ck_value),
        		wp_kses_post($is_required_label));
		}

		$html .= sprintf('</select><button type="button" class="button button-secondary" data-wf_popover="1" data-title="Checkout Field Inserter" data-module-base="main" data-content-container=".wt_pklist_checkout_inserter_form" data-field-type="checkout" style="margin-top:5px; margin-left:5px; float:right;">%1$s</button>',__("Add/Edit Custom Field","wt_woocommerce_invoice_addon"));
		$html .= '<div class="wt_pklist_checkout_inserter_form">';
		$html .= sprintf('<div class="wt_pklist_checkout_field_tab">
							<div class="wt_pklist_custom_field_tab_head active_tab" data-target="add_new" data-add-title="%1$s" data-edit-title="%2$s">
								<span class="wt_pklist_custom_field_tab_head_title">%1$s</span>
	                			<div class="wt_pklist_custom_field_tab_head_patch"></div>
							</div>
							<div class="wt_pklist_custom_field_tab_head" data-target="list_view">
	                			%3$s
	                			<div class="wt_pklist_custom_field_tab_head_patch"></div>		
	                		</div>
	                		<div class="wt_pklist_custom_field_tab_head_add_new" title="%1$s">
	                			<span class="dashicons dashicons-plus"></span>
	                		</div>
						</div>',
					__("Add new","wt_woocommerce_invoice_addon"),
					__("Edit", "wt_woocommerce_invoice_addon"),
					__("Previously added","wt_woocommerce_invoice_addon"));
		$html .='<div class="wt_pklist_custom_field_tab_content active_tab" data-id="add_new">';
		$html .= sprintf('<div class="wt_pklist_custom_field_tab_form_row wt_pklist_custom_field_form_notice">
		                		%1$s
                		</div>
	                	<div class="wt_pklist_custom_field_tab_form_row">
							<div class="wt_pklist_new_custom_field_title">
								<table>
									<tr>
										<td>%2$s<i style="color:red;">*</i>:</td>
										<td><input type="text" name="wt_pklist_new_custom_field_title" data-required="1"/></td>
									<tr>
								</table> 
							</div>
						</div>
						<div class="wt_pklist_custom_field_tab_form_row">
							<div class="wt_pklist_new_custom_field_key">
								<table>
									<tr>
										<td style="vertical-align:top;">%3$s<i style="color:red;">*</i>:</td>
										<td><input type="text" name="wt_pklist_new_custom_field_key" data-required="1" />
											<br>
											<i class="meta_key_help_text"> %4$s
											<br /> %5$s : my_meta_key, meta_one 
											<br /> %6$s : #My meta Key, Meta.1 </i>
										</td>
									</tr>
								</table>
							</div>
						</div>',
						__("You can edit existing Meta items by using Meta key of the item.","wt_woocommerce_invoice_addon"),
						__("Field Name","wt_woocommerce_invoice_addon"),
						__("Meta Key","wt_woocommerce_invoice_addon"),
						__("Please use only alphabets and underscore for meta key.","wt_woocommerce_invoice_addon"),
						__("Correct","wt_woocommerce_invoice_addon"),
						__('Incorrect','wt_woocommerce_invoice_addon'));
		$html .= sprintf('<div class="wt_pklist_custom_field_tab_form_row">
								<div class="wt_pklist_new_custom_field_title_placeholder">
									<table>
										<tr>
											<td>%1$s :</td>
											<td><input type="text" name="wt_pklist_new_custom_field_title_placeholder"/></td>
										</tr>
									</table>  
								</div>
								<div class="is_mandatory_div">
									<table>
										<tr>
											<td>%2$s</td>
											<td>
												<div class="is_mandatory_inner_div">
													<span>
														<input type="radio" name="wt_pklist_cst_chkout_required" value=1> %3$s
													</span>
													<span>
														<input type="radio" name="wt_pklist_cst_chkout_required" value="0" checked="checked"> %4$s
													</span>
												</div>
											</td>
										</tr>
									</table>
								</div>
							</div>',
							__("Placeholder","wt_woocommerce_invoice_addon"),
							__("Is mandatory field?","wt_woocommerce_invoice_addon"),
							__("Yes", "wt_woocommerce_invoice_addon"),
							__("No","wt_woocommerce_invoice_addon"));
		$html .='</div>';
		$html .='<div class="wt_pklist_custom_field_tab_content" data-id="list_view" style="height:282px; overflow:auto;"></div>';
		
		$html .= '</div>';
		$html .=sprintf('<br>
			                <span class="wf_form_help" style="display:inline;"> %1$s <br> %2$s <br> <a href="%3$s">%4$s</a>
			                </span>',
			                __("Select/add additional fields in the checkout page","wt_woocommerce_invoice_addon"),
			                __("e.g VAT, SSN etc","wt_woocommerce_invoice_addon"),
			                esc_url($custom_field_doc_url),
			                __("Learn how to add custom field at checkout","wt_woocommerce_invoice_addon"));    
		$html .='</div></td><td></td>';
		return $html;
	}


	/**
	 * @since 1.0.0
	 * 
	 * Function to display the order meta field using form builder
	 */
	public function order_meta_fields_pro($args,$base_id){
		extract($this->verify_the_fields($args,$base_id));
		$additional_fields = array();
		$default_add_fields = Wf_Woocommerce_Packing_List::$default_additional_data_fields; 
		$user_created=Wf_Woocommerce_Packing_List::get_option('wf_additional_data_fields');
		if(is_array($user_created))  //user created
        {
            $additional_fields=array_merge($default_add_fields,$user_created);
        }else
        {
            $additional_fields=$default_add_fields; //default
        }

        //additional checkout fields
        $additional_checkout=Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');
    	$additional_checkout=Wf_Woocommerce_Packing_List_Pro_Common_Func::process_checkout_fields($additional_checkout);
    	$additional_fields=array_merge($additional_fields, $additional_checkout);
    	$result = Wf_Woocommerce_Packing_List::get_option($name,$base_id);
        $user_selected_arr 	= ($result && is_array($result)) ? $result : array(); 

    	$vat_fields = array('vat','vat_number','eu_vat_number');
    	$temp = array();
    	foreach($user_selected_arr as $u_val){
    		if(in_array($u_val,$vat_fields)){
    			if(!in_array('vat',$temp)){
    				$temp[] = 'vat';
    			}
    		}else{
    			$temp[] = $u_val;
    		}
    	}
    	$user_selected_arr = $temp;
    	$d_temp = array();
    	foreach($additional_fields as $d_key => $d_val){
    		if(in_array($d_key,$vat_fields)){
    			if(!array_key_exists('vat',$d_temp)){
    				$d_temp[$d_key] = 'VAT';
    			}
    		}else{
    			$d_temp[$d_key] = $d_val;
    		}
    	}
    	$additional_fields = $d_temp;

		/**
		 * @since 1.0.3 - [Tweak] - Remove the default order fields from the settings page and moved them to the customizer
		 */
		$unset_keys = array('contact_number','email','ssn','vat','vat_number','eu_vat_number','cus_note','aelia_vat');
		foreach($unset_keys as $unset_key){
			if(isset($additional_fields[$unset_key])){
				unset($additional_fields[$unset_key]);
			}
		}

    	$html = '<td>';

		$html .= sprintf('<div class="wf_select_multi">
							<input type="hidden" name="%1$s_hidden" value="1" />
							<select class="wc-enhanced-select" name="%1$s[]" multiple="multiple">',esc_attr($name));
    	foreach($additional_fields as $add_field_key => $add_field_value){
    		$meta_key_display=Wf_Woocommerce_Packing_List::get_display_key($add_field_key);
    		$selected = in_array($add_field_key, $user_selected_arr) ? "selected" : "";

    		$html .= sprintf('<option value="%1$s" %2$s>%3$s</option>',
    				esc_attr($add_field_key),
		    		$selected,
		    		wp_kses_post($add_field_value.$meta_key_display));
    	}
		$html .=sprintf('</select><br><button type="button" class="button button-secondary" data-wf_popover="1" data-title="%1$s" data-module-base="%2$s" data-content-container=".wt_pklist_custom_field_form" data-field-type="order_meta" style="margin-top:5px; margin-left:5px; float: right;">%3$s</button>',
			__("Order meta","wt_woocommerce_invoice_addon"),
			$module_base,
			__("Add/Edit order meta field","wt_woocommerce_invoice_addon"));
		$html .= sprintf('<span class="wf_form_help"  style="display:inline;">%1$s</span>',wp_kses_post($help_text));
			
    	$html .='</div>
    	</td><td></td>';
    	return $html;
	}

	/**
	 * @since 1.0.0
	 * 
	 * Function to display the product meta field using form builder
	 */
	public function product_meta_fields_pro($args,$base_id){
		extract($this->verify_the_fields($args,$base_id));

		$result = Wf_Woocommerce_Packing_List::get_option($name,$base_id);
        $user_selected_arr 	= ($result && is_array($result)) ? $result : array(); 
		$wt_product_spec_fields=Wf_Woocommerce_Packing_List::get_option('wf_product_meta_fields');
		
		$html ='<td>';
		$html .= sprintf('<div class="wf_select_multi">
							<input type="hidden" name="%1$s_hidden" value="1" />
							<select class="wc-enhanced-select" name="%1$s[]" multiple="multiple">',esc_attr($name));
		
		if (is_array($wt_product_spec_fields))
        {
            foreach ($wt_product_spec_fields as $prod_meta_key => $prod_meta_val)
            {
            	$meta_key_display=Wf_Woocommerce_Packing_List::get_display_key($prod_meta_key);
            	$selected = in_array($prod_meta_key, $user_selected_arr) ? "selected" : "";
                $html .=sprintf('<option value="%1$s" %2$s>%3$s</option>',
                	esc_attr($prod_meta_key),
                	$selected,
                	wp_kses_post($prod_meta_val.$meta_key_display)
            	);
            }
        }

		$html .= sprintf('</select><br><button type="button" class="button button-secondary" data-wf_popover="1" data-title="%1$s" data-module-base="%2$s" data-content-container=".wt_pklist_custom_field_form" data-field-type="product_meta" style="margin-top:5px; margin-left:5px; float: right;">%3$s</button>',
			__("Product Meta","wt_woocommerce_invoice_addon"),
			esc_attr($module_base),
			__("Add/Edit Product Meta","wt_woocommerce_invoice_addon"));
		$html .= sprintf('<span class="wf_form_help"  style="display:inline;">%1$s</span>',wp_kses_post($help_text));
		$html .='</div></td><td></td>';
		
		return $html;
	}

	/**
	 * @since 1.0.0
	 * 
	 * Function to display the product attributes field using form builder
	 */
	public function product_attribute_pro($args,$base_id){
		extract($this->verify_the_fields($args,$base_id));

		$result = Wf_Woocommerce_Packing_List::get_option($name,$base_id);
        $user_selected_arr 	= ($result && is_array($result)) ? $result : array(); 
		$wt_product_spec_fields=Wf_Woocommerce_Packing_List::get_option('wt_product_attribute_fields');
		$html ='<td>';
		$html .= sprintf('<div class="wf_select_multi">
							<input type="hidden" name="%1$s_hidden" value="1" />
							<select class="wc-enhanced-select" name="%1$s[]" multiple="multiple">',esc_attr($name));
		
		if (is_array($wt_product_spec_fields))
        {
            foreach ($wt_product_spec_fields as $prod_meta_key => $prod_meta_val)
            {
            	$meta_key_display=Wf_Woocommerce_Packing_List::get_display_key($prod_meta_key);
            	$selected = in_array($prod_meta_key, $user_selected_arr) ? "selected" : "";
                $html .=sprintf('<option value="%1$s" %2$s>%3$s</option>',
                	esc_attr($prod_meta_key),
                	$selected,
                	wp_kses_post($prod_meta_val.$meta_key_display)
            	);
            }
        }

		$html .= sprintf('</select><br><button type="button" class="button button-secondary" data-wf_popover="1" data-title="%1$s" data-module-base="%2$s" data-content-container=".wt_pklist_custom_field_form" data-field-type="product_attribute" style="margin-top:5px; margin-left:5px; float: right;">%3$s</button>',
			__("Product Attributes","wt_woocommerce_invoice_addon"),
			esc_attr($module_base),
			__("Add/Edit Product Attribute","wt_woocommerce_invoice_addon"));
		$html .= sprintf('<span class="wf_form_help"  style="display:inline;">%1$s</span>',wp_kses_post($help_text));
		$html .='</div></td><td></td>';
		
		return $html;
	}

}