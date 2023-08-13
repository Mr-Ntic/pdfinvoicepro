<?php
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Wf_Woocommerce_Packing_List_Template_Render')){
class Wf_Woocommerce_Packing_List_Template_Render
{
    private static function wf_is_multi($array)
    {
        $multi_check = array_filter($array,'is_array');
        if(count($multi_check)>0) return true;
        return false;
    }

    /**
     * To get the predefined column key from the additional column key
     *
     * @param [string] $column_key
     * @return string
     */
    public static function get_row_key($column_key){
        if(str_contains($column_key,'custom_st_row_')){
            $col_key = explode('custom_st_row_',$column_key);
            $col_key = explode("_wtpdf_",$col_key[1]);
            return $col_key[0];
        }
        return $column_key;
    }

    /**
     * To get the predefined column key from the additional column key
     *
     * @param [string] $column_key
     * @return string
     */
    public static function get_column_key($column_key){
        if(str_contains($column_key,'custom_pt_col_')){
            $col_key = explode('custom_pt_col_',$column_key);
            $col_key = explode("_wtpdf_",$col_key[1]);
            return $col_key[0];
        }
        return $column_key;
    }

    public static function generate_product_image_column_data($product_id, $variation_id, $parent_id,$img_style=array())
	{
		$img_url = plugin_dir_url(plugin_dir_path( dirname( __FILE__ ) )).'admin/modules/customizer/assets/images/thumbnail-preview.png';
		if($product_id>0)
		{
			$image_id 	= get_post_thumbnail_id($product_id);
	        $attachment = wp_get_attachment_image_src($image_id);

	        if(empty($attachment[0]) && $variation_id>0) //attachment is empty and variation is available
	        {		            
	            $var_image_id 	= get_post_thumbnail_id($variation_id);
	            $image_id 		= (("" === $var_image_id || 0 === $var_image_id) ? get_post_thumbnail_id($parent_id) : $var_image_id);
	            $attachment 	= wp_get_attachment_image_src($image_id);
	        }
	        $img_url = (!empty($attachment[0]) ? $attachment[0] : $img_url);
    	}
    	
    	$img_url = apply_filters('wt_pklist_alter_product_image_url', $img_url, $product_id, $variation_id, $parent_id);

		$style = "";
    	if(!empty($img_style) && is_array($img_style)){
			$img_sty = "";
			$style = "max-width:30px; max-height:30px;";
    		if(isset($img_style["img-width"]) && "" !== trim($img_style["img-width"])){
    			$img_sty .= "width:".$img_style["img-width"].";";
    		}
			if(isset($img_style["img-height"]) && "" !== trim($img_style["img-height"])){
    			$img_sty .= "height:".$img_style["img-height"].";";
    		}
			if("" !== $img_sty){
				$style = $img_sty;
			}

    	}else{
    		$style = "max-width:30px; max-height:30px;";
    	}
        return '<img src="'.esc_url($img_url).'" style="border-radius:25%;'.esc_attr($style).'" class="wfte_product_image_thumb"/>';
	}

    /**
     * To render the product meta details beneath the product name for accounting documents
     *
     * @param [array] $meta_data_formated_arr
     * @param [array] $p_meta_source_details
     * @return array
     */
    public static function process_p_meta_product_name_column($meta_data_formated_arr, $p_meta_source_details){
        extract($p_meta_source_details);
        if(isset($the_options['wf_'.$template_type.'_product_meta_fields']) && is_array($the_options['wf_'.$template_type.'_product_meta_fields']) && count($the_options['wf_'.$template_type.'_product_meta_fields'])>0) 
        {
            $selected_product_meta_arr = $the_options['wf_'.$template_type.'_product_meta_fields'];
            $product_meta_arr = Wf_Woocommerce_Packing_List::get_option('wf_product_meta_fields');
            foreach($selected_product_meta_arr as $value)
            {
                if(isset($product_meta_arr[$value])) //meta exists in the added list
                {   
                    $meta_data = get_post_meta($product_id, $value, true);
                    if("" === $meta_data && $variation_id > 0)
                    {
                        $meta_data = get_post_meta($parent_id,$value,true);
                    }
                    if(is_array($meta_data))
                    {
                        $output_data = (self::wf_is_multi($meta_data) ? '' : implode(', ',$meta_data));
                    }else
                    {
                        $output_data = $meta_data;
                    }

                    if("" !== $output_data)
                    {
                        $meta_info_arr = array(
                            'key' 	=> $value,
                            'title' => __($product_meta_arr[$value],'wt_woocommerce_invoice_addon'),
                            'value' => __($output_data, 'wt_woocommerce_invoice_addon')
                        );

                        $meta_info_arr = apply_filters('wf_pklist_alter_product_meta', $meta_info_arr, $template_type, $_product, $order_item, $order);
                        if(is_array($meta_info_arr) && isset($meta_info_arr['title']) && isset($meta_info_arr['value']) && "" !== $meta_info_arr['value'])
                        {
                            $meta_data_formated_arr[] = '<small><span class="wt_pklist_product_meta_item" data-meta-id="'.esc_attr($value).'"><label>'.$meta_info_arr['title'].'</label> : '.$meta_info_arr['value'].'</span></small>';
                        }
                    }
                }
            }
        }
        return $meta_data_formated_arr;
    }

    public static function process_p_meta_product_name_column_package_doc($addional_product_meta,$col_val_source_details){
        extract($col_val_source_details);
        if(isset($the_options['wf_'.$template_type.'_product_meta_fields']) && is_array($the_options['wf_'.$template_type.'_product_meta_fields']) && count($the_options['wf_'.$template_type.'_product_meta_fields'])>0) 
        {
            $selected_product_meta_arr 	= $the_options['wf_'.$template_type.'_product_meta_fields'];
            $product_meta_arr  			= Wf_Woocommerce_Packing_List::get_option('wf_product_meta_fields');
            foreach($selected_product_meta_arr as $value)
            {
                if(isset($product_meta_arr[$value])) //meta exists in the added list
                {
                    $meta_data 	= get_post_meta($product_id, $value, true);
                    if("" === $meta_data && $variation_id > 0)
                    {
                        $meta_data 	= get_post_meta($parent_id, $value, true);
                    }
                    if(is_array($meta_data))
                    {
                        $output_data = (self::wf_is_multi($meta_data) ? '' : implode(', ',$meta_data));
                    }else
                    {
                        $output_data = $meta_data;
                    }
                    if("" !== $output_data)
                    {
                        $meta_info_arr 	= array(
                            'key' 	=> $value, 
                            'title'	=> __($product_meta_arr[$value], 'wt_woocommerce_invoice_addon'), 
                            'value'	=> __($output_data,'wt_woocommerce_invoice_addon')
                        );
                        $meta_info_arr=apply_filters('wf_pklist_alter_package_product_meta', $meta_info_arr, $template_type, $_product, $item, $order);

                        if(is_array($meta_info_arr) && isset($meta_info_arr['title']) && isset($meta_info_arr['value']) && "" !== trim($meta_info_arr['value']))
                        {
                            $item_html 	= '<br><small>'.$meta_info_arr['title'].': '.$meta_info_arr['value'].'</small>';
                            
                            /**
                            * To alter the prepared meta item HTML
                            */
                            $item_html 	= apply_filters('wf_pklist_alter_package_product_meta_item_html', $item_html, $meta_info_arr, $template_type, $_product, $item, $order);
                            $addional_product_meta .= (is_string($item_html) ? $item_html : '');
                        }
                    }
                }
            }
        }
        return $addional_product_meta;
    }
    /**
     * To render the product attributes details beneath the product name
     *
     * @param [array] $product_attr_formated_arr
     * @param [array] $p_meta_source_details
     * @return array
     */
    public static function process_p_attr_product_name_column($product_attr_formated_arr,$p_meta_source_details){
        extract($p_meta_source_details);
        if(isset($the_options['wt_'.$template_type.'_product_attribute_fields']) && is_array($the_options['wt_'.$template_type.'_product_attribute_fields']) && count($the_options['wt_'.$template_type.'_product_attribute_fields'])>0) 
        {
            $selected_product_attr_arr 	= $the_options['wt_'.$template_type.'_product_attribute_fields'];
            $product_attr_arr 			= Wf_Woocommerce_Packing_List::get_option('wt_product_attribute_fields');

            foreach($selected_product_attr_arr as $attr)
            {
                $attr_data 		= $_product->get_attribute($attr);
                $label 			= wc_attribute_label($attr, $_product);
                $attr_info_arr	= array(
                    'key' 	=> $attr, 
                    'title'	=> $label, 
                    'value' => $attr_data
                );
                $attr_info_arr 	= apply_filters('wf_pklist_alter_product_attr_item', $attr_info_arr, $template_type, $_product, $order_item, $order); 
                
                if(is_array($attr_info_arr) && isset($attr_info_arr['title']) && isset($attr_info_arr['value']) && "" !== $attr_info_arr['value'])
                {
                    $item_html 	= '<span class="wt_pklist_product_attr_item" data-attr-id="'.esc_attr($attr).'"><label>'.$attr_info_arr['title'].'</label>: '.$attr_info_arr['value'].'</span>';
                    $item_html 	= apply_filters('wf_pklist_alter_product_attr_item_html', $item_html, $attr_info_arr, $template_type, $_product, $order_item, $order);
                    
                    if(is_string($item_html) && "" !== $item_html)
                    {
                        $product_attr_formated_arr[] = $item_html;
                    }
                }
            }
        }
        return $product_attr_formated_arr;
    }
    
    public static function process_p_attr_product_name_column_package_doc($product_attr,$col_val_source_details){
        extract($col_val_source_details);
        if(isset($the_options['wt_'.$template_type.'_product_attribute_fields']) && is_array($the_options['wt_'.$template_type.'_product_attribute_fields']) && count($the_options['wt_'.$template_type.'_product_attribute_fields'])>0) 
        {
            $selected_product_attr_arr 	= $the_options['wt_'.$template_type.'_product_attribute_fields'];
            $product_attr_arr 			= Wf_Woocommerce_Packing_List::get_option('wt_product_attribute_fields');
            foreach($selected_product_attr_arr as $attr)
            {
                $attr_data 		= $_product->get_attribute($attr);
                $label 			= wc_attribute_label($attr, $_product);
                $attr_info_arr 	= array(
                    'key' 	=> $attr, 
                    'title' => $label, 
                    'value' => $attr_data
                );
                $attr_info_arr 	= apply_filters('wf_pklist_alter_package_product_attr_item', $attr_info_arr, $template_type, $_product, $item, $order);
                
                if(is_array($attr_info_arr) && isset($attr_info_arr['title']) && isset($attr_info_arr['value']) && "" !== $attr_info_arr['value'])
                {
                    $item_html 	= '<small>'.$attr_info_arr['title'].': '.$attr_info_arr['value'].'</small><br>';
                    $item_html 	= apply_filters('wf_pklist_alter_package_product_attr_item_html', $item_html, $attr_info_arr, $template_type, $_product, $item, $order);
                    $product_attr .= (is_string($item_html) ? $item_html : '');
                }
            }
        }
        return $product_attr;
    }

    /**
     * To render the unit price column value depends on the settings
     *
     * @param [array] $col_val_source_details
     * @return string|int|float
     */
    public static function render_unit_price($col_val_source_details){
        extract($col_val_source_details);
        $p_incl_tax = false;
        $discount_type = "before_discount";
        if(isset($column_list_options_value[$columns_key]['tax-type'])){
            $p_tax_type_name = $column_list_options_value[$columns_key]['tax-type'];
            if($p_tax_type_name === "incl_tax"){
                $p_incl_tax = true;
            }
        }
        if(isset($column_list_options_value[$columns_key]['discount-type'])){
            $discount_type = $column_list_options_value[$columns_key]['discount-type'];
        }
        return Wf_Woocommerce_Packing_List_Order_Func_Pro::get_unit_price($order_item,$order,$p_incl_tax,$discount_type);
    }

    /**
     * To render the unit price column value depends on the settings
     *
     * @param [array] $col_val_source_details
     * @return string|int|float
     */
    public static function render_total_price($col_val_source_details){
        extract($col_val_source_details);
        $p_incl_tax = false;
        $discount_type = "before_discount";
        if(isset($column_list_options_value[$columns_key]['tax-type'])){
            $p_tax_type_name = $column_list_options_value[$columns_key]['tax-type'];
            if($p_tax_type_name === "incl_tax"){
                $p_incl_tax = true;
            }
        }
        if(isset($column_list_options_value[$columns_key]['discount-type'])){
            $discount_type = $column_list_options_value[$columns_key]['discount-type'];
        }
        
        if(isset($document_type) && isset($item)){
            return Wf_Woocommerce_Packing_List_Order_Func_Pro::get_total_price($order_item,$order,$p_incl_tax,$discount_type,$template_type,$item);
        }else{
            return Wf_Woocommerce_Packing_List_Order_Func_Pro::get_total_price($order_item,$order,$p_incl_tax,$discount_type,$template_type);
        }
    }

    /**
     * To render the total tax column value based on the display type and discount type
     *
     * @param [array] $col_val_source_details
     * @return string
     */
    public static function render_total_tax($col_val_source_details,$template_type=""){
        extract($col_val_source_details);
        $show_after_refund = apply_filters('wt_pklist_show_details_after_refund_'.$template_type,false,$template_type);
        $tax_items_arr 	= $order->get_items('tax');
        $tax_data_arr 	= array();

        foreach ($tax_items_arr as $tax_item)
        {
            $tax_data_arr[$tax_item->get_rate_id()] = $tax_item->get_rate_percent();
        }
        // for rate
        $tax_rate_display 	= '';
        $tax_rate 			= 0;

        // for amount
        $item_tax_formated = '';
        $item_tax = 0;
        if(isset($document_type) && "package" === $document_type){
            $order_item = new WC_Order_Item_Product( $order_item_id );
        }

        $item_qty_refunded = 0;
        if($show_after_refund){
            $item_qty_refunded = $order->get_qty_refunded_for_item( $order_item->get_id() );
        }

        $item_taxes 		= $order_item->get_taxes();
        $item_tax_subtotal	= (isset($item_taxes['subtotal']) ? $item_taxes['subtotal'] : array());

        $tax_display_option = "amount";
        $discount_type = "before_discount";

        if(isset($column_list_options_value[$columns_key]['discount-type']) && "after_discount" === $column_list_options_value[$columns_key]['discount-type']){
            $discount_type = "after_discount";
            $item_tax_subtotal	= (isset($item_taxes['total']) ? $item_taxes['total'] : array());
        }
        if(isset($column_list_options_value[$columns_key]['total-tax-display-option'])){
            $tax_display_option = "" !== trim($column_list_options_value[$columns_key]['total-tax-display-option']) ? $column_list_options_value[$columns_key]['total-tax-display-option']: $tax_display_option;
        }
        
        $refunded_tot_tax = 0;
        $tax_val_for_single_item = 0;
        $discount_amount = 0;
        if(!empty($item_tax_subtotal)){
            foreach($item_tax_subtotal as $tax_id => $tax_val)
            {
                $tax_rate += ((!empty($tax_val) && isset($tax_data_arr[$tax_id])) ? (float) $tax_data_arr[$tax_id] : 0);
                if($show_after_refund){
                    $tax_val_for_single_item += (float) $tax_val/(float)$order_item->get_quantity();
                    $refunded_tot_tax += abs((float)$order->get_tax_refunded_for_item($order_item_id,$tax_id));
                }
            }
        }

        if($refunded_tot_tax > 0){
            $discount_amount += (float) ($tax_val_for_single_item - $refunded_tot_tax);
        }

        if("rate" === $tax_display_option || "amount-rate" === $tax_display_option)
        {
            $tax_rate_display = "";
            if("amount-rate" === $tax_display_option && abs($tax_rate) > 0){
                $tax_rate_display = ' ('.$tax_rate.'%)';
            }elseif(abs($tax_rate) > 0){
                $tax_rate_display = $tax_rate.'%';
            }
        }

        if("amount" === $tax_display_option || "amount-rate" === $tax_display_option)
        {	
            if($discount_type === "after_discount"){
                $product_total = $order->get_line_total($order_item, false, true);
            }else{
                $product_total = (float) ($wc_version< '2.7.0' ? $order->get_item_meta($order_item_id,'_line_total',true) : $order->get_line_subtotal($order_item, false, true));
            }

            if(abs($tax_rate) > 0){
                $item_tax = $product_total * ($tax_rate/100);
            }else{
                $item_tax = $order_item['line_subtotal_tax'];
            }
            
            
            if($show_after_refund){
                $item_tax -= $refunded_tot_tax;
                if($discount_amount > 0){
                    $item_tax -= $discount_amount;
                }
            }
            
            if(isset($item['quantity']) && isset($document_type) && $document_type === "package"){
                $unit_price = $item_tax / max( 1, (abs( $order_item['qty'] ) - abs($item_qty_refunded)) );
                $item_tax = (float)$item['quantity'] * (float)$unit_price;
            }

            $item_tax = apply_filters('wf_pklist_alter_item_tax', $item_tax, $template_type, $_product, $order_item, $order);
            if(abs($item_tax) > 0){
                $item_tax_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$item_tax);	
            }else{
                $item_tax_formated = '-';
                $tax_rate_display = '';
            }
        }
        $item_tax_formated = apply_filters('wf_pklist_alter_item_tax_formated',$item_tax_formated,$template_type,$item_tax,$_product,$order_item,$order); 
        $tax_rate_display = apply_filters('wf_pklist_alter_total_tax_rate', $tax_rate_display, $tax_rate, $tax_data_arr, $template_type, $order_item, $order);
        return $item_tax_formated.$tax_rate_display;
    }

    public static function render_tax_items_column($col_val_source_details,$template_type=""){
        extract($col_val_source_details);
        $show_after_refund = apply_filters('wt_pklist_show_details_after_refund_'.$template_type,false,$template_type);
        $tax_items_arr 	= $order->get_items('tax');
        $tax_data_arr 	= array();

        foreach ($tax_items_arr as $tax_item)
        {
            $tax_data_arr[$tax_item->get_rate_id()] = $tax_item->get_rate_percent();
        }
        if(isset($document_type) && "package" === $document_type){
            $order_item = new WC_Order_Item_Product( $order_item_id );
        }

        $item_qty_refunded = 0;
        if($show_after_refund){
            $item_qty_refunded = $order->get_qty_refunded_for_item( $order_item->get_id() );
        }

        $item_taxes 		= $order_item->get_taxes();
        $item_tax_subtotal	= (isset($item_taxes['subtotal']) ? $item_taxes['subtotal'] : array());
        
        if(isset($column_list_options_value['tax_items']['discount-type']) && "after_discount" === $column_list_options_value['tax_items']['discount-type']){
            $item_tax_subtotal	= (isset($item_taxes['total']) ? $item_taxes['total'] : array());
        }

        $tax_info_arr 		= explode("individual_tax_",$columns_key);
        $tax_display_type 	= "amount";
        if(isset($column_list_options_value['tax_items']['ind-tax-display-option'])){
            $tax_display_type = $column_list_options_value['tax_items']['ind-tax-display-option'];
            if("separate-column" === $tax_display_type){
                $tax_display_type	= str_replace('_', '', $tax_info_arr[0]);
            }
        }

        $tax_id 			= end($tax_info_arr);
        if(array_key_exists($tax_id,$item_tax_subtotal)){
            $tax_rate = (isset($tax_data_arr[$tax_id]) ? (float) $tax_data_arr[$tax_id] : 0);
        }else{
            $tax_rate = 0;
        }

        $tax_val = '';
        if("amount" === $tax_display_type || "amount-rate" === $tax_display_type || "rate" === $tax_display_type)
        {
            $tax_val 	= (float)(isset($item_tax_subtotal[$tax_id]) ? $item_tax_subtotal[$tax_id] : 0);

            if($show_after_refund && abs($tax_val) > 0){
                $tax_val_for_single_item = (float) $tax_val/(float)$order_item->get_quantity();
                $discount_amount    = 0;
                $total_refund_tax   = abs((float)$order->get_tax_refunded_for_item($order_item_id,$tax_id));
                if($total_refund_tax > 0){
                    $discount_amount = (float) ($tax_val_for_single_item - $total_refund_tax);
                }
                $tax_val -= (float)$order->get_tax_refunded_for_item($order_item_id,$tax_id);
                if($discount_amount > 0){
                    $tax_val -= $discount_amount;
                }
            }
            
            // Packaging document - get the quantity from the line to compatible with the packaging type
            if(isset($item['quantity']) && isset($document_type) && $document_type === "package"){
                $unit_price = $tax_val / max( 1, (abs( $order_item['qty'] ) - abs($item_qty_refunded)) );
                $tax_val = (float)$item['quantity'] * (float)$unit_price;
            }

            if(abs($tax_val) > 0){
                $tax_exist = true;
                $tax_val 	= Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$tax_val);
            }else{
                $tax_val = '-';
            }
        }

        $tax_rate_display = '';
        if("rate" === $tax_display_type || "amount-rate" === $tax_display_type)
        {
            if("amount-rate" === $tax_display_type && abs($tax_rate) > 0 && isset($tax_exist)){
                $tax_rate_display = ' ('.$tax_rate.'%)';
            }elseif("rate" === $tax_display_type && abs($tax_rate) > 0 && isset($tax_exist)){
                $tax_rate_display = $tax_rate.'%';
            }

            if("rate" === $tax_display_type && isset($tax_exist)){
                $tax_val = "";
            }
        }

        $tax_val 	= apply_filters('wf_pklist_alter_item_individual_tax', $tax_val, $template_type, $tax_id, $order_item, $order);
        $tax_rate_display = apply_filters('wf_pklist_alter_individual_tax_rate', $tax_rate_display, $tax_rate, $template_type, $tax_id, $order_item, $order);
        return $tax_val.$tax_rate_display;
    }

    /**
     * To get the subtotal row in summary table
     *
     * @param [string] $this_st_row_key
     * @param [array] $this_st_row_val
     * @param [array] $order_items
     * @param [object] $order
     * @param [string] $template_type
     * @return int|float
     */
    public static function process_summary_table_subtotal_row($this_st_row_key,$this_st_row_val,$order_items,$order,$template_type,$order_details){
        if("incl_tax" === $this_st_row_val['tax-type']){
            if("before_discount" === $this_st_row_val['discount-type']){
                return $order_details['subtotal']['it_bd'];
            }
            return $order_details['subtotal']['it_ad'];
        }else{
            if("before_discount" === $this_st_row_val['discount-type']){
                return $order_details['subtotal']['et_bd'];
            }
            return $order_details['subtotal']['et_ad'];
        }
    }

    public static function process_all_values($order,$template_type){
        $show_after_refund = apply_filters('wt_pklist_show_details_after_refund_'.$template_type,false,$template_type);
        $order_items	= $order->get_items();
        $total_weight = 0;
        $total_qty = 0;
        $line_discount_it = 0;
        $line_discount_et = 0;
        $unit_coupon_amount_it = 0;
        $unit_coupon_amount_et = 0;
        // subtotal
        $sub_total = array(
            'et_bd' => 0,
            'et_ad' => 0,
            'it_bd' => 0,
            'it_ad' => 0,
        );
        foreach($order_items as $order_item){

            $line_discount_it     += (float)$order->get_line_subtotal($order_item, true, true) - (float)$order->get_line_total($order_item, true, true);
            $line_discount_et     += (float)$order->get_line_subtotal($order_item, false, true) - (float)$order->get_line_total($order_item, false, true);

            // subtotal
            $sub_total['et_bd'] += $order->get_line_subtotal($order_item, false, true);
            $sub_total['et_ad'] += $order->get_line_total($order_item, false, true);
            $sub_total['it_bd'] += $order->get_line_subtotal($order_item, true, true);
            $sub_total['it_ad'] += $order->get_line_total($order_item, true, true);

            $product_id = $order_item->get_product_id();
            $product_weight = (float)get_post_meta( $product_id, '_weight', true );
            $quantity = (float)$order_item->get_quantity();

            $unit_coupon_amount_it   += (float)$line_discount_it/abs($quantity);
            $unit_coupon_amount_et   += (float)$line_discount_et/abs($quantity);

            if($show_after_refund){
                $quantity -= abs((float)$order->get_qty_refunded_for_item($order_item->get_id()));
                $sub_total['et_bd'] = $sub_total['et_bd'] - $unit_coupon_amount_et;
                $sub_total['it_bd'] = $sub_total['it_bd'] - $unit_coupon_amount_it;
            }
            $total_qty += $quantity;
            $total_weight += (float) $product_weight * $quantity;
        }

        $refunded_total_amount_li = 0;
        $refunded_total_tax_li = 0;
        foreach($order_items as $order_item){
            $item_taxes 		= $order_item->get_taxes();
            $item_tax_subtotal	= (isset($item_taxes['subtotal']) ? $item_taxes['subtotal'] : array());
            $refunded_total_amount_li += (float)$order->get_total_refunded_for_item( $order_item->get_id() );
            foreach($item_tax_subtotal as $tax_id => $tax_val)
            {
                $refunded_total_tax_li += (float)$order->get_tax_refunded_for_item($order_item->get_id(),$tax_id);
            }
        }

        if($show_after_refund){
            $sub_total['et_bd'] -= (float)$refunded_total_amount_li;
            $sub_total['et_ad'] -= (float)$refunded_total_amount_li;
            $sub_total['it_bd'] -= ((float)$refunded_total_amount_li + (float)$refunded_total_tax_li);
            $sub_total['it_ad'] -= ((float)$refunded_total_amount_li + (float)$refunded_total_tax_li);
        }

        // shipping
        $shippingdetails = $order->get_items('shipping');
        $shipping['et'] = (float)$order->get_shipping_total();
        $shipping['it'] = ((float)$order->get_shipping_total() + (float)$order->get_shipping_tax());
        $refund_shipping_amount_tax = 0;

        foreach($shippingdetails as $sk => $sv){
            $sv_taxes 		= $sv->get_taxes();
            $sv_item_tax_subtotal	= (isset($sv_taxes['total']) ? $sv_taxes['total'] : array());
            foreach($sv_item_tax_subtotal as $tax_id => $tax_val)
            {
                $refund_shipping_amount_tax += (float)$order->get_tax_refunded_for_item($sk,$tax_id,'shipping');
            }
        }

        if($show_after_refund){
            $shipping['et'] = $shipping['et'] - (float)$order->get_total_shipping_refunded();
            $shipping['it'] = $shipping['it'] - ((float)$order->get_total_shipping_refunded() + (float)$refund_shipping_amount_tax);
        }

        // fee details
        $fee_details = $order->get_items('fee');
        $fee['et'] = 0;
        $fee['it'] = 0;
        $refunded_total_amount_fee = 0;
        $refunded_total_tax_fee = 0;
        if(!empty($fee_details)){
            $fee_ord_arr 	= array();
            foreach($fee_details as $fee_id => $fee_detail){
                if(!in_array($fee_id,$fee_ord_arr)){
                    $fee['et'] += $order->get_line_total($fee_detail, false, true);
                    $fee['it'] += $order->get_line_total($fee_detail, true, true);

                    $fee_item_taxes 		= $fee_detail->get_taxes();
                    $fee_item_tax_subtotal	= (isset($fee_item_taxes['total']) ? $fee_item_taxes['total'] : array());
                    $refunded_total_amount_fee += (float)$order->get_total_refunded_for_item( $fee_id,'fee');
                    foreach($fee_item_tax_subtotal as $tax_id => $tax_val)
                    {
                        $refunded_total_tax_fee += (float)$order->get_tax_refunded_for_item($fee_id,$tax_id,'fee');
                    }
                }
            }
        }

        if($show_after_refund){
            $fee['et'] -= (float)$refunded_total_amount_fee;
            $fee['it'] -= ((float)$refunded_total_amount_fee + (float)$refunded_total_tax_fee);
        }
        
        $output = array(
            'subtotal'  => $sub_total,
            'shipping'  => $shipping,
            'fee'       => $fee,
            'total_qty' => $total_qty,
            'total_weight' => $total_weight,
        );
        return $output;
    }

    public static function wt_get_total_tax_refunded($order) {
        $output = array(
            'total_tax'  => array(
                'amount_tax' => 0,
                'shipping_tax' => 0,
                'total_tax' => 0
            )
        );
        $order_tax_items = $order->get_items( array('tax') );
        foreach($order_tax_items as $ot){
            $ot_rate_id = $ot->get_rate_id();
            $output['tax_items'][$ot_rate_id] = array(
                'amount_tax' => 0,
                'shipping_tax' => 0,
                'total_tax' => 0
            );
        }
		foreach ( $order->get_refunds() as $refund ) {
			foreach ( $refund->get_items( 'tax' ) as $refunded_item ) {
                $tax_rate_id_refunded = absint( $refunded_item->get_rate_id() );
                if(!isset($output['tax_items'][$tax_rate_id_refunded])){
                    $output['tax_items'][$tax_rate_id_refunded] = array(
                        'amount_tax' => 0,
                        'shipping_tax' => 0,
                        'total_tax' => 0
                    );
                }
                // individual tax items
                $output['tax_items'][$tax_rate_id_refunded]['amount_tax'] += abs( $refunded_item->get_tax_total() );
                $output['tax_items'][$tax_rate_id_refunded]['shipping_tax'] += abs( $refunded_item->get_shipping_tax_total() );
                $output['tax_items'][$tax_rate_id_refunded]['total_tax'] += abs( $refunded_item->get_tax_total() ) + abs( $refunded_item->get_shipping_tax_total() );
				
                // total tax
                $output['total_tax']['amount_tax'] += abs( $refunded_item->get_tax_total() );
                $output['total_tax']['shipping_tax'] += abs( $refunded_item->get_shipping_tax_total() );
                $output['total_tax']['total_tax'] += abs( $refunded_item->get_tax_total() ) + abs( $refunded_item->get_shipping_tax_total() );
			}
		}
        return $output;
	}
}
}