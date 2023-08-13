<?php

/**
 * Product table related function for customizer module
 *
 * @link       
 * @since 1.0.0     
 *
 * @package  Wt_woocommerce_invoice_addon  
 */

if (!defined('ABSPATH')) {
    exit;
}
trait Wf_Woocommerce_Packing_List_Customizer_Product_Table_Creditnote
{
    /**
    *   @since 1.0.0 
    *   - Generating product table
    *   - Tax column introduced 
    *   
    */
    public static function set_product_table_creditnote($find_replace,$template_type,$html,$order=null,$refund_id=null,$refund_order=null,$box_packing=null,$order_package=null)
    {
        $match 					= array();
		$default_columns		= array('image','sku','product','quantity','price','total_price');
		$columns_list_arr 		= array();
		$column_list_options_value = array();
		//extra column properties like text-align etc are inherited from table head column. We will extract that data to below array
	    $column_list_options 	= array();
	    $module_id 				= Wf_Woocommerce_Packing_List::get_module_id($template_type);
		$tax_type 		= Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_taxstatus');
		$tax_type = in_array('in_tax',$tax_type) ? 'incl_tax' : 'excl_tax';
	    $product_table_values_attributes = array(
	    	'img-width' => '',
	    	'img-height' => '',
	    	'img-background' => '',
	    	'p-meta' => array(), // product meta
	    	'oi-meta' => array(), // order item meta
			'p-attr' => array(), // product attributes
	    	// 'tax-display' => ,
	    	'total-tax-display-option' => 'amount', // amount, rate, amount-rate
	    	'ind-tax-display-option' => 'amount', // amount, rate, amount-rate, separate column
	    	'tax-type' => $tax_type, // incl_tax , excl_tax - applicable for all pricing fields except the individual tax columns and total tax column
			'discount-type' => 'after_discount', //before_discount, after_discount - applicable for all pricing field including tax columns
			'col-value-font-size' => '',
			'col-value-font-weight' => '',
			'col-value-font-color' => '',
	    );

	    /* checking product table markup exists  */
	    if(preg_match('#<table[^>]*class\s*=\s*["\']([^"\']*)wfte_product_table(.*?[^"\']*)["\'][^>]*>(.*?)</table>#s',$html,$match))
		{
			$product_tb_html 	= $match[0];
			$thead_match 		= array();
			$th_html 			= '';

			if(preg_match('/<thead(.*?)>(.*?)<\/thead>/s', $product_tb_html, $thead_match))
			{
				if(isset($thead_match[2]) && "" !== $thead_match[2])
				{
					$thead_tr_match 	= array();
					if(preg_match('/<tr(.*?)>(.*?)<\/tr>/s',$thead_match[2],$thead_tr_match))
					{
						if(isset($thead_tr_match[2]))
						{
							$th_html 	= $thead_tr_match[2];
						}
					}
				}				
			}

			if("" !== $th_html)
			{
				$th_html_arr 	= explode('</th>',$th_html);
				$th_html_arr	= array_filter($th_html_arr);
				$col_ind 		= 0;

				foreach($th_html_arr as $th_single_html)
				{
					$th_single_html 	= trim($th_single_html);
					if("" !== $th_single_html)
					{
						$matchs 		= array();
						$is_have_col_id	= preg_match('/col-type="(.*?)"/',$th_single_html,$matchs);
						$col_ind++;
						$col_key 		= ($is_have_col_id ? $matchs[1] : $col_ind); //column id exists
						
						/* To avoid the duplicate column key, when adding column through advanced customizer */
						$cn = 2;
						while(isset($columns_list_arr[$col_key])){
							$col_key = $col_key.'_'.$cn;
							$cn++;
						}

						$col_key_dc 	= "-" === $col_key[0] ? substr($col_key, 1) : $col_key;

						/* extracting extra column options, like column text align class etc */
						$extra_table_col_opt = self::extract_table_col_options($th_single_html);

						/* check following to add the product table values attributes */
						foreach($product_table_values_attributes as $prod_val_attr_key =>  $prod_val_attr){
							$column_list_options_value[$col_key_dc][$prod_val_attr_key] = $prod_val_attr;
						}

						if(class_exists('Wf_Woocommerce_Packing_List_Template_Render_Adc')){
							$column_list_options_value = Wf_Woocommerce_Packing_List_Template_Render_Adc::process_image_col_styles($column_list_options_value,$col_key_dc,$th_single_html);
							$column_list_options_value = Wf_Woocommerce_Packing_List_Template_Render_Adc::col_value_font_styles($column_list_options_value,$col_key_dc,$th_single_html);
							$column_list_options_value = Wf_Woocommerce_Packing_List_Template_Render_Adc::process_product_name_col_values($column_list_options_value,$col_key_dc,$th_single_html);
							$column_list_options_value = Wf_Woocommerce_Packing_List_Template_Render_Adc::process_price_col_values($column_list_options_value,$col_key_dc,$th_single_html);
						}
						/* column key is tax then get the tax display option from the corresponding thead attributes */
						if("tax" === $col_key || "-tax" === $col_key)
						{
			            	//adding column data to arrays
							$columns_list_arr[$col_key] 	= $th_single_html.'</th>';
							$column_list_options[$col_key]	= $extra_table_col_opt;

							$column_list_options_value[$col_key_dc]['total-tax-display-option'] = "amount";
							$tot_tax_dis_opt_match 			= array();
							$tot_tax_dis_opt 				= preg_match('/data-total-tax-display-option="(.*?)"/',$th_single_html,$tot_tax_dis_opt_match);
							if($tot_tax_dis_opt){
								$column_list_options_value[$col_key_dc]['total-tax-display-option'] = $tot_tax_dis_opt_match[1];
							}
						}
						elseif("tax_items" === $col_key || "-tax_items" === $col_key)
						{
							if(!is_null($order)) //do not show this column in customizer
        					{
								$show_individual_tax_column = true;

								if(true === $show_individual_tax_column || "Yes" === $show_individual_tax_column) 
								{ 	
									$individual_tax_column_display_option 	= "amount";
									$ind_tax_dis_opt_match 					= array();
									$ind_tax_dis_opt = preg_match('/data-ind-tax-display-option="(.*?)"/',$th_single_html,$ind_tax_dis_opt_match);
									if($ind_tax_dis_opt){
										$individual_tax_column_display_option = $ind_tax_dis_opt_match[1];
										$column_list_options_value[$col_key_dc]['ind-tax-display-option'] = $ind_tax_dis_opt_match[1];
									}

									if(false === $individual_tax_column_display_option) //option not present, then add a filter to control the value
									{
										$individual_tax_column_display_option=apply_filters('wf_pklist_alter_individual_tax_column_display_option', $individual_tax_column_display_option, $template_type, $order);
									}

									$show_individual_tax_rate_column_after_amount_column = true; //only applicable on separte column
									if("separate-column" === $individual_tax_column_display_option)
									{
										/**
										*	Show rate column after amount column. Default:true
										*	
										*/
										$show_individual_tax_rate_column_after_amount_column = apply_filters('wf_pklist_show_individual_tax_rate_column_after_amount_column', $show_individual_tax_rate_column_after_amount_column, $template_type, $order);
									}

									/**
									*	This variable is for filter.
									*/
									$individual_tax_column_config = array(
										'display_option'=>$individual_tax_column_display_option,
										'rate_column_after_amount_column'=>$show_individual_tax_rate_column_after_amount_column,
									);


									$tax_items 		= $order->get_items('tax');
									$tax_id_prefix 	= ($col_key[0]=='-' ? $col_key[0] : '').'individual_tax_';
									$tax_id_prefix_rate_only = 'rate_'.$tax_id_prefix;
									$tax_id_prefix 	= ("" === $individual_tax_column_display_option ? 'amount' : $individual_tax_column_display_option).'_'.$tax_id_prefix;

									foreach($tax_items as $tax_item)
									{
										$tax_id 	= $tax_item->get_rate_id();
										$tax_id_rate_only = $tax_id_prefix_rate_only.$tax_id;			
										$tax_id 	= $tax_id_prefix.$tax_id;
										
										$tax_label 	= $tax_item->get_label();
										$tax_rate_only_column_label = $tax_label.'(%)';
										
										if("rate" === $individual_tax_column_display_option)
										{
											$tax_label = $tax_rate_only_column_label;
										}

										/**
										*	Rate column before amount column
										*/
										if("separate-column" === $individual_tax_column_display_option && !$show_individual_tax_rate_column_after_amount_column)
										{
											self::prepare_tax_item_column_html($tax_id_rate_only, $tax_rate_only_column_label, $th_single_html, $columns_list_arr, $column_list_options, $extra_table_col_opt, $template_type, $tax_item, $individual_tax_column_config, $order);
										}

										self::prepare_tax_item_column_html($tax_id, $tax_label, $th_single_html, $columns_list_arr, $column_list_options, $extra_table_col_opt, $template_type, $tax_item, $individual_tax_column_config, $order);

										/**
										*	Rate column after amount column
										*/
										if("separate-column" === $individual_tax_column_display_option && $show_individual_tax_rate_column_after_amount_column)
										{
											self::prepare_tax_item_column_html($tax_id_rate_only, $tax_rate_only_column_label, $th_single_html, $columns_list_arr, $column_list_options, $extra_table_col_opt, $template_type, $tax_item, $individual_tax_column_config, $order);
										}
									}
								}
							}else
							{
								$columns_list_arr[$col_key] 		= $th_single_html.'</th>';
								$column_list_options[$col_key]	= $extra_table_col_opt;
								$individual_tax_column_display_option 	= "amount";
								$ind_tax_dis_opt_match 				= array();
								$ind_tax_dis_opt 	= preg_match('/data-ind-tax-display-option="(.*?)"/',$th_single_html,$ind_tax_dis_opt_match);
								if($ind_tax_dis_opt){
									$individual_tax_column_display_option = $ind_tax_dis_opt_match[1];
									$column_list_options_value[$col_key_dc]['ind-tax-display-option'] = $ind_tax_dis_opt_match[1];
								}
							}
						}
						else
						{
							//adding column data to arrays
							$columns_list_arr[$col_key] 	= $th_single_html.'</th>'; 
							$column_list_options[$col_key]	= $extra_table_col_opt;
						}
					}
				}
				
				if(!is_null($order))
	    		{
	    			//filter to alter table head
					$columns_list_arr=apply_filters('wf_pklist_alter_product_table_head',$columns_list_arr,$template_type,$order);
				}
				$columns_list_arr = (!is_array($columns_list_arr) ? array() : $columns_list_arr);

				//for table head
				$columns_list_arr = apply_filters('wf_pklist_reverse_product_table_columns',$columns_list_arr,$template_type);				

				/* update the column options according to $columns_list_arr */
				$column_list_option_modified = array();
				foreach($columns_list_arr as $column_key => $column_data)
				{
					if(isset($column_list_options[$column_key]))
					{
						$column_list_option_modified[$column_key] = $column_list_options[$column_key];
					}else
					{
						//extracting extra column options, like column text align class etc
						$extra_table_col_opt = self::extract_table_col_options($column_data);
						$column_list_option_modified[$column_key] = $extra_table_col_opt;
					}
				}
				$column_list_options = $column_list_option_modified;
								
				//replace for table head section
				$find_replace[$th_html] = self::generate_product_table_head_html($columns_list_arr,$template_type);
			
			}

            //product table body section
            $tbody_tag_match    = array();
            $tbody_tag          = '';
            if(preg_match('/<tbody(.*?)>/s',$product_tb_html,$tbody_tag_match))
            {
                Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::$reference_arr['tbody_placholder'] = $tbody_tag_match[0];
                if(!is_null($box_packing))
                {
                    $find_replace[$tbody_tag_match[0]] = $tbody_tag_match[0].self::generate_package_product_table_product_row_html($column_list_options,$template_type,$order,$box_packing,$order_package);
                }else
                {
                    $find_replace[$tbody_tag_match[0]] = $tbody_tag_match[0].self::generate_product_table_product_row_html($column_list_options,$template_type,$order,$refund_order,$refund_id,$column_list_options_value);
                }
            }
        }

        $find_replace['[wfte_product_table_start]'] = '';
        $find_replace['[wfte_product_table_end]']   = '';
        return $find_replace;
    }

    /**
    *   Set other charges fields in product table
    *   @since  1.0.0
    *   - Refund amount calculation issue fixed. Total in words integrated. Added filter to alter total
    *   - Added new filter to alter tax item amount `wf_pklist_alter_taxitem_amount`
    */
    public static function set_extra_charge_fields_creditnote($find_replace,$template_type,$html,$order=null,$refund_id=null,$refund_order=null)
    {
        //module settings are saved under module id
        $module_id = Wf_Woocommerce_Packing_List::get_module_id($template_type);
        $show_after_refund = apply_filters('wt_pklist_show_details_after_refund_'.$template_type,false,$template_type);
        if(!is_null($order))
        {
            $the_options    = Wf_Woocommerce_Packing_List::get_settings($module_id);
            $order_items    = $order->get_items();

            if(!is_null($refund_order)){
                $order_items = $refund_order->get_items();
            }

            $wc_version     = WC()->version;
            $order_id       = $wc_version<'2.7.0' ? $order->id : $order->get_id();
            $user_currency  = Wt_Pklist_Common_Ipc::get_order_meta($order_id, 'currency', true);
            $tax_type       = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_taxstatus');
            $incl_tax       = in_array('in_tax', $tax_type);
            $full_refunded  = Wt_Pklist_Common_Ipc::is_fully_refunded($order);
            if(true === $full_refunded){
                $refund_order   = $order;
                $refund_id      = $order_id;
            }
            //subtotal ==========================
            if(!isset($find_replace['[wfte_product_table_subtotal]'])) /* check already added */
            {
                $incl_tax_text  = '';
                $sub_total = 0;
                foreach($order_items as $order_item){
                    if($incl_tax){
                        $sub_total += $order->get_line_total($order_item, true, true);
                    }else{
                        $sub_total += $order->get_line_total($order_item, false, true);
                    }
                }

                if($incl_tax)
                {
                    $incl_tax_text  = self::get_tax_incl_text($template_type, $order, 'product_price');
                    $incl_tax_text  = ($incl_tax_text!="" ? ' ('.$incl_tax_text.')' : $incl_tax_text);
                }               

                $sub_total = apply_filters('wf_pklist_alter_subtotal', $sub_total, $template_type, $order, $incl_tax);
                if(abs($sub_total) > 0){
                    $sub_total_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,abs($sub_total)).$incl_tax_text;
                }else{
                    $sub_total_formated = "";
                }
                $find_replace['[wfte_product_table_subtotal]'] = apply_filters('wf_pklist_alter_subtotal_formated', $sub_total_formated, $template_type, $sub_total, $order, $incl_tax);
            }

            //shipping method ==========================
            if(!isset($find_replace['[wfte_product_table_shipping]']) || "" === $find_replace['[wfte_product_table_shipping]'] ) /* check already added */
            {
                if("Yes" === get_option('woocommerce_calc_shipping') || "yes" === get_option('woocommerce_calc_shipping'))
                {
                    $shippingdetails=$refund_order->get_items('shipping');
                    if (!empty($shippingdetails))
                    {   
                        $shipping = (float)$refund_order->get_shipping_total();
                        if($incl_tax)
                        {
                            $shipping += (float)$refund_order->get_shipping_tax();
                        }
                        
                        $shipping = (0 < abs($shipping)) ? Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,abs($shipping)) : "";
                        $shipping = apply_filters('wf_pklist_alter_shipping_row', $shipping, $template_type, $order, 'product_table');
                        $find_replace['[wfte_product_table_shipping]'] =__($shipping, 'wt_woocommerce_invoice_addon');
                    }
                }
            }

            $tax_items = $order->get_tax_totals();

            //tax items ==========================
            if(!isset($find_replace['[wfte_product_table_total_tax]'])) /* check already added */
            {
                if(in_array('ex_tax',$tax_type))
                {
                    //total tax ==========================
                    if(is_array($tax_items) && count($tax_items)>0)
                    {
                        $tax_total = $refund_order->get_total_tax();
                        $tax_total = apply_filters('wf_pklist_alter_total_tax_row',$tax_total,$template_type,$order,$tax_items);
                        $find_replace['[wfte_product_table_total_tax]'] = $tax_total;
                    }else
                    {
                        $find_replace['[wfte_product_table_total_tax]'] = '';
                    }
                }else
                {
                    $find_replace['[wfte_product_table_total_tax]'] = '';
                }
            }

            $tax_items_match    = array();
            $tax_items_row_html = ''; //row html
            $tax_items_html     = '';
            $tax_items_total    = 0;

            if(preg_match('/<[^>]*data-row-type\s*=\s*"[^"]*\bwfte_tax_items\b[^"]*"[^>]*>(.*?)<\/tr>/s', $html, $tax_items_match))
            {
                $tax_items_row_html=isset($tax_items_match[0]) ? $tax_items_match[0] : '';
            }

            if(is_array($tax_items) && count($tax_items)>0)
            {
                foreach($tax_items as $tax_item)
                {
                    if(in_array('ex_tax',$tax_type) && "" !== $tax_items_row_html)
                    {   
                        $tax_rate_id    = $tax_item->rate_id;
                        $tax_label      = apply_filters('wf_pklist_alter_taxitem_label', esc_html($tax_item->label), $template_type, $order, $tax_item);
                        
                        if(false === $full_refunded){
                            $tax_amount = 0;
                            foreach($refund_order->get_items() as $refunded_item_id => $refunded_item){
                                $refund_tax = $refunded_item->get_taxes();
                                $tax_amount += isset( $refund_tax['total'][ $tax_rate_id ] ) ? (float) $refund_tax['total'][ $tax_rate_id ] : 0;
                            }
                        }else{
                            $tax_amount = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$tax_item->amount);
                        }

                        $tax_amount = apply_filters('wf_pklist_alter_taxitem_amount', $tax_amount, $tax_item, $order, $template_type,$tax_rate_id);
                        
                        if("" === $tax_amount){
                            break;
                        }
                        $tax_items_html .= str_replace(array('[wfte_product_table_tax_item_label]','[wfte_product_table_tax_item]'), array($tax_label, $tax_amount), $tax_items_row_html);
                    }
                    else
                    {
                        $tax_items_total += (float)$tax_item->amount;
                    }
                }
            }
            

            if("" !== $tax_items_row_html && isset($tax_items_match[0])) //tax items placeholder exists
            { 
                $find_replace[$tax_items_match[0]] = $tax_items_html; //replace tax items
            }

            //fee details ==========================
            if(!isset($find_replace['[wfte_product_table_fee]'])) /* check already added */
            {
                $fee_details        = $refund_order->get_items('fee');
                $fee_details_html   = '';
                $fee_total_amount   = 0;
                $find_replace['[wfte_product_table_fee]'] = '';
                if(!empty($fee_details)){
                    $fee_ord_arr    = array();
                    foreach($fee_details as $fee => $fee_value){
                        $fee_order_id = $fee;
                        if(!in_array($fee_order_id,$fee_ord_arr)){
                            $fee_total_amount += (abs((float)wc_get_order_item_meta($fee_order_id,'_line_total',true)));
                            if($incl_tax)
                            {
                                $fee_total_amount += (abs((float)wc_get_order_item_meta($fee_order_id,'_line_tax',true)));
                            } 
                            $fee_ord_arr[] = $fee_order_id;
                        }
                    }
                    if(0 < $fee_total_amount){
                        $fee_total_amount_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$fee_total_amount);
                    }else{
                        $fee_total_amount_formated = "";
                    }
                    $fee_total_amount_formated = apply_filters('wf_pklist_alter_total_fee',$fee_total_amount_formated,$template_type,$fee_total_amount,$user_currency,$order).$incl_tax_text;
                    $find_replace['[wfte_product_table_fee]'] = $fee_total_amount_formated;
                }
            }

            //total amount ==========================
            if(!isset($find_replace['[wfte_product_table_payment_total]']) || !isset($find_replace['[wfte_total_in_words]'])) /* check already added */
            {
                $total_price_final  = ($wc_version<'2.7.0' ? $refund_order->order_total : Wt_Pklist_Common_Ipc::get_order_meta($refund_id,'total',true));
                $refund_amount      = abs((float)$total_price_final);
                $total_price_html   = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$refund_amount);
                $total_price_html   = apply_filters('wf_pklist_alter_price_creditnote',$total_price_html,$template_type,$order);
                $find_replace       = self::set_total_in_words($total_price_final, $find_replace, $template_type, $html, $order);
                $find_replace['[wfte_product_table_payment_total]'] = $total_price_html;
            }
        }else
        {
            /**
             *  for customizer 
             */

            //custom order meta row ========
            $custom_order_meta_datas = array();
            if(self::get_summary_table_custom_order_meta_placeholders($html, $custom_order_meta_datas))
            {
                foreach($custom_order_meta_datas as $custom_order_meta_item)
                {
                    $find_replace[$custom_order_meta_item[0]] = $custom_order_meta_item[1];
                }
            }
        }
        return $find_replace;
    }
}