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
trait Wf_Woocommerce_Packing_List_Customizer_Product_table_Ipc_Pro
{

	/**
	* @since 1.0.0 
	* - Generating product table
	* - Tax column introduced 
	*	
	*/
	public static function set_product_table($find_replace,$template_type,$html,$order=null,$box_packing=null,$order_package=null)
	{
		$match 					= array();
		$default_columns		= array('image','sku','product','quantity','price','total_price');
		$columns_list_arr 		= array();
		$column_list_options_value = array();
		//extra column properties like text-align etc are inherited from table head column. We will extract that data to below array
	    $column_list_options 	= array();
	    $module_id 				= Wf_Woocommerce_Packing_List::get_module_id($template_type);
		$tax_type = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_taxstatus');
		$tax_type = in_array('in_tax',$tax_type) ? 'incl_tax' : 'excl_tax';
	    $product_table_values_attributes = array(
	    	'img-width' => '30px',
	    	'img-height' => '',
	    	'img-background' => '',
	    	'p-meta' => array(), // product meta
	    	'oi-meta' => array(), // order item meta
			'p-attr' => array(), // product attributes
	    	'total-tax-display-option' => 'amount', // amount, rate, amount-rate
	    	'ind-tax-display-option' => 'amount', // amount, rate, amount-rate, separate column
	    	'tax-type' => $tax_type, // incl_tax , excl_tax - applicable for all pricing fields except the individual tax columns and total tax column
			'discount-type' => 'before_discount', //before_discount, after_discount - applicable for all pricing field including tax columns
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

						//extracting extra column options, like column text align class etc
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

						if("tax" === $col_key || "-tax" === $col_key) //column key is tax then check, tax column options are enabled
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
									$tax_id_prefix 	= ("" === $individual_tax_column_display_option || "separate-column" === $individual_tax_column_display_option ? 'amount' : $individual_tax_column_display_option).'_'.$tax_id_prefix;

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
								$columns_list_arr[$col_key] 			= $th_single_html.'</th>';
								$column_list_options[$col_key]			= $extra_table_col_opt;
								$individual_tax_column_display_option 	= "amount";
								$ind_tax_dis_opt_match 					= array();
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
			$tbody_tag_match = array();
			$tbody_tag 		= '';
			if(preg_match('/<tbody(.*?)>/s',$product_tb_html,$tbody_tag_match))
			{
				Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::$reference_arr['tbody_placholder'] = $tbody_tag_match[0];
				if(!is_null($box_packing))
				{
					$find_replace[$tbody_tag_match[0]] 	= $tbody_tag_match[0].self::generate_package_product_table_product_row_html($column_list_options,$template_type,$order,$box_packing,$order_package,$column_list_options_value);
				}else
				{
					$find_replace[$tbody_tag_match[0]] 	= $tbody_tag_match[0].self::generate_product_table_product_row_html($column_list_options,$template_type,$order,null,null,$column_list_options_value);
				}
			}
		}
		$find_replace['[wfte_product_table_start]'] = '';
		$find_replace['[wfte_product_table_end]']	= '';
		return $find_replace;
	}

	private static function prepare_tax_item_column_html($tax_id, $tax_label, $th_single_html, &$columns_list_arr, &$column_list_options, $extra_table_col_opt, $template_type, $tax_item, $individual_tax_column_config, $order)
	{
		$tax_label 	= apply_filters('wf_pklist_alter_individual_tax_column_head', $tax_label, $template_type, $tax_item, $individual_tax_column_config, $order);

		$col_html 	= str_replace('[wfte_product_table_tax_item_column_label]', $tax_label, $th_single_html);

		//adding column data to arrays
		$columns_list_arr[$tax_id] 		= $col_html.'</th>';
		$column_list_options[$tax_id]	= $extra_table_col_opt;
	}


	/**
	* 	Extract table column style classes.
	*	@since 1.0.0
	*/
	public static function extract_table_col_options($th_single_html)
	{
		$matchs 		= array();
		$is_have_class	= preg_match('/class="(.*?)"/',$th_single_html,$matchs);
		$option_classes = array('wfte_text_left','wfte_text_right','wfte_text_center');
		$out 			= array();
		if($is_have_class)
		{
			$class_arr 	= explode(" ",$matchs[1]);
			foreach($class_arr as $class)
			{
				if(in_array($class,$option_classes))
				{
					$out[] = $class;
				}
			}
		}
		return implode(" ",$out);
	}

	/**
	*  	Set other charges fields in product table
	*	@since 	1.0.0
	*
	*/
	public static function set_extra_charge_fields($find_replace,$template_type,$html,$order=null)
	{
		//module settings are saved under module id
		$module_id 	= Wf_Woocommerce_Packing_List::get_module_id($template_type);
		$show_after_refund = apply_filters('wt_pklist_show_details_after_refund_'.$template_type,false,$template_type);

		if(!is_null($order))
        {
        	$the_options 	= Wf_Woocommerce_Packing_List::get_settings($module_id);
			$order_items	= $order->get_items();
			$wc_version 	= WC()->version;
			$order_id 		= $wc_version<'2.7.0' ? $order->id : $order->get_id();
			$user_currency  = Wt_Pklist_Common_Ipc::get_order_meta($order_id, 'currency', true);
			$tax_type 		= Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_taxstatus');
			$incl_tax 		= in_array('in_tax', $tax_type);
			$summary_tax_type = in_array('in_tax',$tax_type) ? 'incl_tax' : 'excl_tax';

			$predefined_placeholders_summary_table = array(
				'wfte_product_table_subtotal',
				'wfte_product_table_shipping',
				'wfte_product_table_cart_discount',
				'wfte_product_table_order_discount',
				'wfte_product_table_tax_item',
				'wfte_product_table_total_tax',
				'wfte_product_table_fee',
				'wfte_product_table_total_quantity',
				'wfte_product_table_total_weight',
				'wfte_product_table_payment_method',
				'wfte_product_table_coupon',
				'wfte_product_table_payment_total'
			);
			$predefined_placeholders_summary_table = apply_filters('wt_pklist_add_predefined_placeholders_in_summary_table',$predefined_placeholders_summary_table,$template_type,$order);

			$summary_table_row_attr = array(
				'tax-type' => $summary_tax_type,
				'discount-type' => 'after_discount',
				'shipping-type' => 'amount_label',
				'ind-tax-type' => 'tot-combined-tax',
				'tot-tax-type' => 'tot-combined-tax',
			);
			$summary_table_row_attr = apply_filters('wt_pklist_add_row_attributes_in_summary_table',$summary_table_row_attr,$template_type,$order);

			$summary_table_row_arr = array();
			foreach($predefined_placeholders_summary_table as $st_row){
				$summary_table_row_arr[$st_row] = $summary_table_row_attr;
				// default type for the subtotal and total
				if('wfte_product_table_subtotal' === $st_row){
					$summary_table_row_arr[$st_row]['discount-type'] = 'before_discount';
				}elseif('wfte_product_table_payment_total' === $st_row){
					$summary_table_row_arr[$st_row]['tax-type'] = 'incl_tax';
				}
			}

			// ADC Rendering the summary table rows
			if(class_exists('Wf_Woocommerce_Packing_List_Template_Render_Adc')){
				$summary_table_row_arr = Wf_Woocommerce_Packing_List_Template_Render_Adc::process_summary_table_row($summary_table_row_arr,$summary_table_row_attr,$html);
			}

			$order_details = Wf_Woocommerce_Packing_List_Template_Render::process_all_values($order,$template_type);

			// Process the summary table rows
			if(!empty($summary_table_row_arr)){
				foreach($summary_table_row_arr as $this_st_row_key => $this_st_row_val){
					if("wfte_product_table_subtotal" === $this_st_row_key || "subtotal" === Wf_Woocommerce_Packing_List_Template_Render::get_row_key($this_st_row_key)){
						$incl_tax_text 	= '';
						if("incl_tax" === $this_st_row_val['tax-type'])
						{
							$incl_tax_text 	= self::get_tax_incl_text($template_type, $order, 'product_price');
							$incl_tax_text	= ($incl_tax_text!="" ? ' ('.$incl_tax_text.')' : $incl_tax_text);
							// $sub_total += $total_tax;
						}else{
							$incl_tax = false;
						}
						$sub_total = Wf_Woocommerce_Packing_List_Template_Render::process_summary_table_subtotal_row($this_st_row_key,$this_st_row_val,$order_items,$order,$template_type,$order_details);
						$sub_total = apply_filters('wf_pklist_alter_subtotal', $sub_total, $template_type, $order, $incl_tax);	    
						$sub_total_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$sub_total).$incl_tax_text;
						$find_replace['['.$this_st_row_key.']'] = apply_filters('wf_pklist_alter_subtotal_formated', $sub_total_formated, $template_type, $sub_total, $order, $incl_tax);
					}

					// summary table - shipping row
					if("wfte_product_table_shipping" === $this_st_row_key || "shipping" === Wf_Woocommerce_Packing_List_Order_Func_Pro::get_row_key($this_st_row_key)){
						if("yes" === get_option('woocommerce_calc_shipping'))
						{
							$shippingdetails=$order->get_items('shipping');
							if(!empty($shippingdetails))
							{
								$tax_display 	= get_option( 'woocommerce_tax_display_cart' );
								$shipping = '';
								if( 0 < abs( (float) $order->get_shipping_total() ) && "label" !== $this_st_row_val['shipping-type']){
									if("incl_tax" === $this_st_row_val['tax-type'])
									{
										$tot_shipping_amount = $order_details['shipping']['it'];
										$shipping = apply_filters('wt_pklist_change_price_format',$user_currency,$order,$tot_shipping_amount);
										if ( (float) $order->get_shipping_tax() > 0 && ! $order->get_prices_include_tax()){
											$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>', $order, $tax_display );
										}
									}else{
										if('label' !== $this_st_row_val['shipping-type']){
											$shipping = apply_filters('wt_pklist_change_price_format',$user_currency,$order,$order_details['shipping']['et']);
											if ( (float) $order->get_shipping_tax() > 0 && $order->get_prices_include_tax()){
												$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>', $order, $tax_display );
											}
										}
									}
									if("amount" !== $this_st_row_val['shipping-type']){
										$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_shipped_via', '&nbsp;<small class="shipped_via">' . sprintf( __( 'via %s', 'woocommerce' ), $order->get_shipping_method() ) . '</small>', $order );
									}
								}elseif( $order->get_shipping_method() && "amount" !== $this_st_row_val['shipping-type']){
									$shipping = $order->get_shipping_method();
								}elseif("amount" !== $this_st_row_val['shipping-type']){
									$shipping = __( 'Free!', 'woocommerce' );
								}
								$shipping = apply_filters('wf_pklist_alter_shipping_method', $shipping, $template_type, $order, 'product_table');
								$find_replace['['.$this_st_row_key.']'] = __($shipping, 'wt_woocommerce_invoice_addon');
							}
						}
					}

					// summary table - cart discount row
					if('wfte_product_table_cart_discount' === $this_st_row_key || "cart_discount" === Wf_Woocommerce_Packing_List_Order_Func_Pro::get_row_key($this_st_row_key)){
						$cart_discount_tax = ('incl_tax' === $this_st_row_val['tax-type']) ? true : false;
						$cart_discount = Wf_Woocommerce_Packing_List_Order_Func::wt_get_discount_amount('cart',$cart_discount_tax,$order,$template_type);
						if(0 !== $cart_discount) 
						{
							$find_replace['['.$this_st_row_key.']'] = $cart_discount;
						}
					}
					// summary table - order discount row
					if('wfte_product_table_order_discount' === $this_st_row_key || "order_discount" === Wf_Woocommerce_Packing_List_Order_Func_Pro::get_row_key($this_st_row_key)){
						$order_discount = ($wc_version<'2.7.0' ? $order->order_discount : Wt_Pklist_Common_IPC::get_order_meta($order_id,'_order_discount',true));
						if ($order_discount>0)
						{
							$find_replace['['.$this_st_row_key.']'] = '-'.Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$order_discount);
						}
					}

					// summary table - Fee row
					$summary_table_fee_tax = 0;
					if('wfte_product_table_fee' === $this_st_row_key || "fee" === Wf_Woocommerce_Packing_List_Order_Func_Pro::get_row_key($this_st_row_key)){
						$fee_tax = ('incl_tax' === $this_st_row_val['tax-type']) ? true : false;
						$fee_incl_text = (true === $fee_tax) ? $incl_tax_text : '';
						$fee_details 		= $order->get_items('fee');
						$fee_details_html	= '';
						$fee_total_amount 	= 0;
						if(!empty($fee_details))
						{
							foreach($fee_details as $fee => $fee_detail){
								$fee_detail_html = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$fee_detail['amount']).' via '.$fee_detail['name'];
								$fee_detail_html = apply_filters('wf_pklist_alter_fee',$fee_detail_html,$template_type,$fee_detail,$user_currency,$order);
								$fee_details_html.= ($fee_detail_html!="" ? $fee_detail_html.'<br/>' : '');
							}
							
							if($fee_tax)
							{
								$fee_total_amount = $order_details['fee']['it'];
							}else{
								$fee_total_amount = $order_details['fee']['et'];
							}

							$fee_total_amount_formated 	= Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$fee_total_amount);
							$fee_total_amount_formated 	= apply_filters('wf_pklist_alter_total_fee',$fee_total_amount_formated,$template_type,$fee_total_amount,$user_currency,$order).$fee_incl_text;

							if("creditnote" !== $template_type){
								$find_replace['['.$this_st_row_key.']'] 	= $fee_details_html.($fee_total_amount_formated!="" ? '<br />'.$fee_total_amount_formated : '');
							}else{
								$find_replace['['.$this_st_row_key.']'] 	= $fee_total_amount_formated;
							}

						}else{
							// $hide_elements =
							$find_replace['['.$this_st_row_key.']'] 	= "";
						}
					}

					$refunded_tax_details = Wf_Woocommerce_Packing_List_Template_Render::wt_get_total_tax_refunded($order);
					// summary table - Total tax row
					$tax_items = $order->get_tax_totals();
					if('wfte_product_table_total_tax' === $this_st_row_key || "total_tax" === Wf_Woocommerce_Packing_List_Order_Func_Pro::get_row_key($this_st_row_key)){
						if(is_array($tax_items) && count($tax_items)>0)
						{
							$tax_total = 0;
							if("tot-combined-tax" === $this_st_row_val['tot-tax-type']){
								$tax_total = $order->get_total_tax();
								if($show_after_refund){
									$tax_total -= $refunded_tax_details['total_tax']['total_tax'];
								}
							}elseif("tot-ship-tax" === $this_st_row_val['tot-tax-type']){
								$tax_total = $order->get_shipping_tax();
								if($show_after_refund){
									$tax_total -= $refunded_tax_details['total_tax']['shipping_tax'];
								}
							}elseif("tot-pro-tax" === $this_st_row_val['tot-tax-type']){
								if ( !empty($order->get_taxes()) ) {
									foreach ( $order->get_taxes() as $key => $tax ) {
										$tax_total += $tax->get_tax_total();
									}
								}
								if($show_after_refund){
									$tax_total -= $refunded_tax_details['total_tax']['amount_tax'];
								}
							}
							$tax_total = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$tax_total);
							$tax_total = apply_filters('wf_pklist_alter_total_tax_row',$tax_total,$template_type,$order,$tax_items);
							$tax_total = apply_filters('wf_pklist_alter_total_tax_row_adc',$tax_total,$template_type,$order,$tax_items);
							$find_replace['['.$this_st_row_key.']'] = $tax_total;
						}
						
					}

					// summary table - Tax items row
					if('wfte_product_table_tax_item' === $this_st_row_key || "tax_item" === Wf_Woocommerce_Packing_List_Order_Func_Pro::get_row_key($this_st_row_key)){
						$tax_items = $order->get_tax_totals();
						$shipping_tax_items = $order->get_items( array('tax') );
						$tax_items_match 	= array();
						$tax_items_row_html = ''; //row html
						$tax_items_html 	= '';
						$tax_items_total 	= 0;

						if(preg_match('/<[^>]*data-row-type\s*=\s*"[^"]*\bwfte_product_table_tax_item\b[^"]*"[^>]*>(.*?)<\/tr>/s', $html, $tax_items_match)){
							$tax_items_row_html=isset($tax_items_match[0]) ? $tax_items_match[0] : '';
						}elseif(preg_match('/<[^>]*data-row-type\s*=\s*"[^"]*\bwfte_tax_items\b[^"]*"[^>]*>(.*?)<\/tr>/s', $html, $tax_items_match))
						{
							$tax_items_row_html=isset($tax_items_match[0]) ? $tax_items_match[0] : '';
						}

						foreach($shipping_tax_items as $tax_item_id => $tax_item){
							$tax_rate_id = $tax_item->get_rate_id();
							$tax_amount = (float)$tax_item->get_tax_total();
							$tax_ship_amount = (float)$tax_item->get_shipping_tax_total();
							if("tot-combined-tax" === $this_st_row_val['ind-tax-type']){
								$tax_amount = ($tax_amount + $tax_ship_amount);
								if($show_after_refund){
									$tax_amount -= (float)$refunded_tax_details['tax_items'][$tax_rate_id]['total_tax'];
								}
							}elseif("tot-ship-tax" === $this_st_row_val['ind-tax-type']){
								$tax_amount = $tax_ship_amount;
								if($show_after_refund){
									$tax_amount -= (float)$refunded_tax_details['tax_items'][$tax_rate_id]['shipping_tax'];
								}
							}elseif("tot-pro-tax" === $this_st_row_val['ind-tax-type']){
								if($show_after_refund){
									$tax_amount -= (float)$refunded_tax_details['tax_items'][$tax_rate_id]['amount_tax'];
								}
							}

							$tax_amount 	= Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$tax_amount);
							$tax_label 		= apply_filters('wf_pklist_alter_taxitem_label', esc_html($tax_item->get_label()), $template_type, $order, $tax_item);
							$tax_items_html.= str_replace(array('[wfte_product_table_tax_item_label]','[wfte_product_table_tax_item]'), array($tax_label, $tax_amount), $tax_items_row_html);
						}
						
						if("" !== $tax_items_row_html && isset($tax_items_match[0])) //tax items placeholder exists
						{ 
							$find_replace[$tax_items_match[0]] = $tax_items_html; //replace tax items
						}
					}

					// summary table - Grand total row
					if('wfte_product_table_payment_total' === $this_st_row_key || "payment_total" === Wf_Woocommerce_Packing_List_Order_Func_Pro::get_row_key($this_st_row_key)){
						$total_price_final = (float)$order->get_total();
						$tax_data = '';
						if('incl_tax' === $this_st_row_val['tax-type']){
							$incl_tax_text 	= self::get_tax_incl_text($template_type, $order, 'total_price');
							$tax_data = ((in_array('in_tax', $tax_type) && !empty($order->get_total_tax())) ? ' ('.$incl_tax_text." ".Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$order->get_total_tax()).')' : '');
							/**
							*	@since 1.0.0 New filter to customize tax info
							*/
							if("" !== $tax_data)
							{
								$tax_data = apply_filters('wf_pklist_alter_tax_info_text', $tax_data, $tax_type, $tax_items_total, $user_currency, $template_type, $order);
							}	
						}else{
							$total_price_final = (float) $order->get_total() - (float)$order->get_total_tax();
						}

						$total_price 		= (float)$total_price_final; //taking value for future use
						$refund_amount = 0;
						$refund_data_arr = $order->get_refunds();
						if(!empty($refund_data_arr))
						{
							foreach($refund_data_arr as $refund_data)
							{	
								$refund_id 			= ($wc_version< '2.7.0' ? $refund_data->id : $refund_data->get_id());
								$cr_refund_amount 	= (float) Wt_Pklist_Common_Ipc::get_order_meta($refund_id,'total',true);
								$total_price_final 	+= $cr_refund_amount;
								$refund_amount 		-= $cr_refund_amount;
							}
						}

						if(!empty($refund_amount) && 0 !== $refund_amount) /* having refund */
						{
							$total_price_final 	= apply_filters('wf_pklist_alter_total_price', $total_price_final, $template_type, $order);
							
							$total_price_final_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$total_price_final);

							/* price before refund */
							$total_price_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$total_price);
							$refund_formated 	= '<br /> ('.__('Refund','wt_woocommerce_invoice_addon').' -'.Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$refund_amount).')';
							$refund_formated 	= apply_filters('wf_pklist_alter_refund_html', $refund_formated, $template_type, $refund_amount, $order);
							$show_refund_amount = apply_filters('wt_pklist_show_total_with_refund_amount',false,$order,$template_type);
							if(true === $show_refund_amount){
								$total_price_html 	= '<strike>'.$total_price_formated.'</strike> '.$total_price_final_formated.$tax_data.$refund_formated;
							}else if($show_after_refund){
								$total_price_html 	= $total_price_final_formated.$tax_data;
							}else{
								$total_price_html = $total_price_formated.$tax_data;
							}

							if("creditnote"  === $template_type){
								$total_price_html = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$refund_amount);
								$total_price_html = apply_filters('wf_pklist_alter_price_creditnote',$total_price_html,$template_type,$order);
							}
						}else{
							$total_price_final 	= apply_filters('wf_pklist_alter_total_price',$total_price_final,$template_type,$order);
							$total_price_formated 	= Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$total_price_final);
							$total_price_html 	= $total_price_formated.$tax_data;
						}

						/* total price in words */
						$find_replace 	= self::set_total_in_words($total_price_final, $find_replace, $template_type, $html, $order);
						$find_replace['[wfte_product_table_payment_total]']	= $total_price_html;
					}

					// coupon details
					if('wfte_product_table_coupon' === $this_st_row_key || "coupon" === Wf_Woocommerce_Packing_List_Order_Func_Pro::get_row_key($this_st_row_key)){
						$coupon_details 	= $order->get_items('coupon');
						$coupon_info_arr 	= array();
						$coupon_info_html 	= '';
						$find_replace['['.$this_st_row_key.']'] = '';
						if(!empty($coupon_details))
						{
							foreach($coupon_details as $coupon_id=>$coupon_detail)
							{
								$discount 	= ($wc_version<'3.2.0' ? $coupon_detail['discount_amount'] : $coupon_detail->get_discount());
								$discount_tax 	= ($wc_version<'3.0.0' ? $coupon_detail['discount_amount_tax'] : $coupon_detail->get_discount_tax());
								$coupon_name	= ($wc_version<'3.0.0' ? $coupon_detail['name'] : $coupon_detail->get_name());
								$discount_total	= (float)$discount+(float)$discount_tax;
								$coupon_info_arr[$coupon_name] = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$discount_total);
							}
							$coupon_code_arr 	= array_keys($coupon_info_arr);
							$coupon_info_html 	= implode(", ",$coupon_code_arr);
							$find_replace['['.$this_st_row_key.']'] = $coupon_info_html;
						}
					}

					if("wfte_product_table_total_weight" === $this_st_row_key || "total_weight" === Wf_Woocommerce_Packing_List_Template_Render::get_row_key($this_st_row_key)){
						$total_weight = $order_details['total_weight'].get_option('woocommerce_weight_unit');
						$total_weight = apply_filters('wf_pklist_alter_total_weight', $total_weight, $template_type, $order);	    
						$find_replace['['.$this_st_row_key.']'] = $total_weight;
					}

					if("wfte_product_table_total_qty" === $this_st_row_key || "total_qty" === Wf_Woocommerce_Packing_List_Template_Render::get_row_key($this_st_row_key)){
						$total_qty = $order_details['total_qty'];
						$total_qty = apply_filters('wf_pklist_alter_total_quantity', $total_qty, $template_type, $order);	    
						$find_replace['['.$this_st_row_key.']'] = $total_qty;
					}

					if('wfte_product_table_payment_method' === $this_st_row_key || 'payment_method' === Wf_Woocommerce_Packing_List_Order_Func_Pro::get_row_key($this_st_row_key)){
						$paymethod_title 	= ($wc_version< '2.7.0' ? $order->payment_method_title : $order->get_payment_method_title());
						$paymethod_title 	= __($paymethod_title, 'wt_woocommerce_invoice_addon');
						$find_replace['['.$this_st_row_key.']'] = $paymethod_title;
					}
				}
			}

			$find_replace = apply_filters('wt_pklist_get_value_for_summary_table_row',$find_replace,$summary_table_row_arr,$template_type,$order);

			//custom order meta row ========
			$custom_order_meta_datas 	= array();
			if(self::get_summary_table_custom_order_meta_placeholders($html, $custom_order_meta_datas))
			{
				foreach($custom_order_meta_datas as $custom_order_meta_item)
				{
					$order_meta_value 	= Wt_Pklist_Common_Ipc::get_order_meta($order_id, $custom_order_meta_item[1], true);
					$find_replace[$custom_order_meta_item[0]] = self::process_meta_value($order_meta_value);
				}
			}

		}else
		{
			/**
			 *  for customizer 
			 */

			//custom order meta row ========
			$custom_order_meta_datas 	= array();
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


	private static function get_summary_table_custom_order_meta_placeholders($html, &$custom_order_meta_datas)
	{
		return preg_match_all('/\[wfte_payment_summary_table_custom_order_meta_([a-zA-Z0-9-_\s]*)\]/m', $html, $custom_order_meta_datas, PREG_SET_ORDER, 0);
	}


	/**
	*	@since 1.0.0 
	* 	- Render product table column data for package type documents
	* 	- Added Product attribute option in product name column
	*/
	public static function generate_package_product_table_product_column_html($wc_version,$the_options,$order,$template_type,$_product,$item,$columns_list_arr,$column_list_options_value)
	{
		$html 					= '';
		$show_after_refund = apply_filters('wt_pklist_show_details_after_refund_'.$template_type,false,$template_type);
		$product_row_columns 	= array(); //for html generation
        $product_id 			= ($wc_version < '2.7.0' ? $_product->id : $_product->get_id());       
        $variation_id 			= (int) ('' !== $item['variation_id'] ? $item['variation_id'] : 0);
        $parent_id 				= wp_get_post_parent_id($variation_id);
        $order_item_id 			= $item['order_item_id'];
        $dimension_unit 		= get_option('woocommerce_dimension_unit');
        $weight_unit 			= get_option('woocommerce_weight_unit');
        $order_id 				= $wc_version<'2.7.0' ? $order->id : $order->get_id();
		$user_currency 			= Wt_Pklist_Common_Ipc::get_order_meta($order_id,'currency',true);
		$order_item 			= new WC_Order_Item_Product( $order_item_id );
		$col_val_source_details = array(
			'wc_version' => $wc_version,
			'user_currency' => $user_currency,
			'the_options' => $the_options,
			'template_type' => $template_type,
			'product_id' => $product_id,
			'_product' => $_product,
			'order' => $order,
			'order_id' => $order_id,
			'item' => $item,
			'column_list_options_value' => $column_list_options_value,
			'variation_id' => $variation_id,
			'parent_id' => $parent_id,
			'order_item_id' => $order_item_id,
			'order_item' => $order_item,
			'document_type' => 'package',
		);
        foreach($columns_list_arr as $columns_key => $columns_value)
        {
        	$columns_key_real	= $columns_key; /* backup */
			
			if(0 === strpos($columns_key, 'default_column_')) /* if the current column added by customer, and its a default column */
			{
				$columns_key 	= str_replace('default_column_', '', $columns_key);
			}
			$columns_key = Wf_Woocommerce_Packing_List_Template_Render::get_column_key($columns_key);
			$col_val_source_details['columns_key'] = $columns_key;
			if("serial_no" === $columns_key || "-serial_no" === $columns_key )
			{
				$column_data 	= $item["serial_no"];
			}
            elseif("image" === $columns_key || "-image" === $columns_key)
            {
            	$column_data=Wf_Woocommerce_Packing_List_Template_Render::generate_product_image_column_data($product_id,$variation_id,$parent_id,$column_list_options_value['image']);
            }
            elseif("sku" === $columns_key || "-sku" === $columns_key)
            {
            	$column_data 	= $_product->get_sku();
            }
            elseif("product" === $columns_key || "-product" === $columns_key)
            {
				$product_name 	= (isset($item['name']) ? $item['name'] : '');
            	$product_name 	= apply_filters('wf_pklist_alter_package_product_name',$product_name,$template_type,$_product,$item,$order);
				$product_name	= ("" !== trim($product_name)) ? $product_name.'<br>' : $product_name;
            	/* variation data */
            	$variation 	= '';
            	if(isset($the_options['woocommerce_wf_packinglist_variation_data']) && "Yes" === $the_options['woocommerce_wf_packinglist_variation_data'])
            	{
	            	$variation 		= $item['variation_data'];
			        $item_meta 		= $item['extra_meta_details'];
			        $variation_data = apply_filters('wf_pklist_add_package_product_variation',$item_meta,$template_type,$_product,$item,$order);
			        if(!empty($variation_data) && !is_array($variation_data))
			        {
			            $variation .= '<br>'.$variation_data;
			        }
			        if(!empty($variation))
			        {
			        	$variation 	= '<small style="word-break: break-word;">'.$variation.'</small>';
			        }			        
		    	}

		        /*additional product meta*/
		        $addional_product_meta 	= '';
				if(class_exists('Wf_Woocommerce_Packing_List_Template_Render_Adc')){
					$addional_product_meta = Wf_Woocommerce_Packing_List_Template_Render_Adc::process_p_meta_product_name_column_package_doc($addional_product_meta,$col_val_source_details);
				}else{
					if(isset($the_options['wf_'.$template_type.'_product_meta_fields']) && is_array($the_options['wf_'.$template_type.'_product_meta_fields']) && count($the_options['wf_'.$template_type.'_product_meta_fields'])>0) 
        			{
						$p_meta_fields = $the_options['wf_'.$template_type.'_product_meta_fields'];
						$addional_product_meta = Wf_Woocommerce_Packing_List_Template_Render::process_p_meta_product_name_column_package_doc($addional_product_meta,$col_val_source_details);
					}
				}

				// render theme complete - extra product option - product meta
				if(class_exists('Wf_Woocommerce_Packing_List_Template_Load_Epo_Tm')){
					$addional_product_meta = Wf_Woocommerce_Packing_List_Template_Load_Epo_Tm::process_tmcart_meta_below_product_name_package_doc($addional_product_meta,$col_val_source_details);
				}
		        $addional_product_meta=apply_filters('wf_pklist_add_package_product_meta', $addional_product_meta, $template_type, $_product, $item, $order);

		        /**
		        *	Product attribute
		       	*/
		        $product_attr 	= '';
				// render product meta beneath the product name
				if(class_exists('Wf_Woocommerce_Packing_List_Template_Render_Adc')){
					$product_attr = Wf_Woocommerce_Packing_List_Template_Render_Adc::process_p_attr_product_name_column_package_doc($product_attr,$col_val_source_details);
				}else{
					$product_attr = Wf_Woocommerce_Packing_List_Template_Render::process_p_attr_product_name_column_package_doc($product_attr,$col_val_source_details);
				}
				$product_attr 	= apply_filters('wf_pklist_alter_package_product_attr_data', $product_attr, $template_type, $_product, $item, $order);
		        $product_column_data_arr=array(
		        	'product_name'	=> $product_name,
		        	'variation'		=> $variation,
		        	'product_meta'	=> $addional_product_meta,
		        	'product_attr'	=> $product_attr,
		        );

		        /**
		        * To alter the data items in product column
		        */
		        $product_column_data_arr 	= apply_filters('wf_pklist_alter_package_product_column_data_arr', $product_column_data_arr, $template_type, $_product, $item, $order);
		        $column_data = '';
		        if(is_array($product_column_data_arr))
		        {
		        	$product_column_data_arr 	= array_filter(array_values($product_column_data_arr));
		        	$column_data 				= implode('', $product_column_data_arr);
		        }else
		        {
		        	$column_data 				= $product_column_data_arr;
		        }

            }
            elseif("quantity" === $columns_key || "-quantity" === $columns_key)
            {
				$qty_col_val = (float)$item['quantity'];
            	$column_data 	= apply_filters('wf_pklist_alter_package_item_quantiy',$qty_col_val,$template_type,$_product,$item,$order);
            }
			elseif("price" === $columns_key || "-price" === $columns_key)
			{
				// $item_price 	= Wf_Woocommerce_Packing_List_Admin::wf_convert_to_user_currency($item['price'],$user_currency,$order);
				$item_price 	= Wf_Woocommerce_Packing_List_Template_Render::render_unit_price($col_val_source_details);
				$item_price 	= apply_filters('wf_pklist_alter_item_price',$item_price,$template_type,$_product,$item,$order);
				$item_price_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$item_price);
				$column_data  	= apply_filters('wf_pklist_alter_item_price_formated',$item_price_formated,$template_type,$item_price,$_product,$item,$order);         	
			}
            elseif("total_weight" === $columns_key || "-total_weight" === $columns_key)
            {
				$qty_col_val = (float)$item['quantity'];
            	$item_weight 	= ("" !== $item['weight']) ? $item['weight']*$qty_col_val.' '.$weight_unit : __('n/a','wt_woocommerce_invoice_addon');
            	$column_data 	= apply_filters('wf_pklist_alter_package_item_total_weight', $item_weight, $template_type, $_product, $item, $order);         	
            }
            elseif("total_price" === $columns_key || "-total_price" === $columns_key)
            {
				$product_total = Wf_Woocommerce_Packing_List_Template_Render::render_total_price($col_val_source_details);
				$total_price 	= apply_filters('wf_pklist_alter_package_item_total',$product_total,$template_type,$_product,$item,$order);          	
            	$product_total_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$total_price);
            	$column_data 	= apply_filters('wf_pklist_alter_package_item_total_formated', $product_total_formated, $template_type, $product_total, $_product, $item, $order);

            }elseif(false !== strpos($columns_key,'individual_tax_'))
			{
				$column_data = Wf_Woocommerce_Packing_List_Template_Render::render_tax_items_column($col_val_source_details);
			}elseif("tax" === $columns_key || "-tax" === $columns_key)
			{
				$column_data = Wf_Woocommerce_Packing_List_Template_Render::render_total_tax($col_val_source_details);
			}elseif("category" === $columns_key || "-category" === $columns_key)
			{	
				if(!empty($parent_id))
				{
					$product_id = $parent_id;
				}
	        	$column_data 	= self::set_category_col($product_id, $template_type, $order);
			}
			else //custom column by user
			{
				$column_data 	= '';
				if(!self::set_custom_meta_table_col_data($column_data, $columns_key_real, $product_id, $order_item_id,$item))
				{
				    $column_data = apply_filters('wf_pklist_package_product_table_additional_column_val', $column_data, $template_type, $columns_key_real, $_product, $item, $order);
				}
			}
            $product_row_columns[$columns_key_real] = $column_data;
        }
        $product_row_columns 	= apply_filters('wf_pklist_alter_package_product_table_columns', $product_row_columns, $template_type, $_product, $item, $order);
        $html 	= self::build_product_row_html($product_row_columns, $columns_list_arr, $order_item_id);
        return $html;
	}

	/**
	* @since 1.0.0 
	* - Render product table row HTML for package type documents
	* - Added group by order for Picklist, Compatibility for variable subscription product
	* - Order item sorting added
	*/
	public static function generate_package_product_table_product_row_html($columns_list_arr,$template_type,$order=null,$box_packing=null,$order_package=null,$column_list_options_value=array())
	{
		$html 	= '';
		if(!is_null($order))
        {
        	//module settings are saved under module id
			$module_id 		= Wf_Woocommerce_Packing_List::get_module_id($template_type);
			$the_options 	= Wf_Woocommerce_Packing_List::get_settings($module_id);

			/**
			*	This filter will: Want to check variation data too when grouping products
			*/
			$compare_with_variation_data = false;
			if("picklist" === $template_type)
			{
				$compare_with_variation_data 	= apply_filters('wt_pklist_compare_variation_data_to_group_in_picklist', $compare_with_variation_data);	
			}

        	/**
			*	Sort order items
			*/
			if(isset($the_options['sort_products_by']) && "" !== $the_options['sort_products_by'])
			{
				$sort_config_arr 	= explode("_", $the_options['sort_products_by']);
				$sort_by 			= $sort_config_arr[0];
				$sort_order 		= (isset($sort_config_arr[1]) ? $sort_config_arr[1] : "");
				$order_package 		= self::sort_items($order_package, $template_type, $order, "package", $sort_by, $sort_order);
			}else
			{
				/* the sort function also handles bundle product compatibilty, here no sorting enabled so it will handle bundle products only */
				$order_package 		= self::sort_items($order_package, $template_type, $order, "package");
			}		
			
        	$order_package 	= apply_filters('wf_pklist_alter_package_order_items', $order_package, $template_type, $order);
			$wc_version 	= WC()->version;
        	$package_type 	= Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_package_type');
            $category_wise_split =Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_product_category_wise_splitting',$module_id);
            
            /* only for picklist   */
            $order_wise_split = Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_product_order_wise_splitting',$module_id);
            if("single_packing" === $package_type && ("Yes" === $category_wise_split || "Yes" === $order_wise_split))
           	{
           		/* if both are enabled we need to decide which is outer */
           		$is_category_under_order 	= 1;
           		
           		if("Yes" === $order_wise_split && "Yes" === $category_wise_split)
	            {
	            	$is_category_under_order = apply_filters('wf_pklist_alter_groupby_is_category_under_order', $is_category_under_order, $template_type);
	            }

           		$item_arr 	= array();
	            foreach ($order_package as $id => $item)
	            {
					$item_obj	= new WT_WC_Product($item['id']);
					if(!$item_obj->obj){continue;}     
	                if("" !== $item['variation_id'])
	                {
	                   $parent_id 	= wp_get_post_parent_id($item['variation_id']);
	                   $item['id'] 	= $parent_id; 
	                }

	                // $item_obj 					= $_product;
					$item_obj->qty 				= $item['quantity'];
                    $item_obj->weight 		 	= $item['weight'];
                    $item_obj->price 			= $item['price'];
                    $item_obj->variation_data 	= $item['variation_data'];
                    $item_obj->variation_id 	= $item['variation_id'];
                    $item_obj->item_id 			= $item['id'];
                    $item_obj->name 			= $item['name'];
                    $item_obj->sku 				= $item['sku'];
                    $item_obj->order_item_id 	= $item['order_item_id'];
                    $item_obj->item 			= $item;

	                if("Yes" === $category_wise_split)
	                {
	                	$terms 			= get_the_terms($item['id'], 'product_cat');
		                $term_name_arr 	= array();

		                if($terms)
		                {
		                	$term_name_arr 	= self::get_term_data($item['id'], $term_name_arr, $template_type, $order);

		                }else /* compatibility for variable subscription products */
						{
							if(isset($item['extra_meta_details']) && isset($item['extra_meta_details']['_product_id'])) //extra meta details available
							{
								if(is_array($item['extra_meta_details']['_product_id']))
								{
									foreach($item['extra_meta_details']['_product_id'] as $p_id)
									{
										$term_name_arr 	= self::get_term_data($p_id, $term_name_arr, $template_type, $order);
									}
								}else
								{
									$p_id=(int) $item['extra_meta_details']['_product_id'];
									if($p_id>0)
									{
										$term_name_arr 	= self::get_term_data($p_id, $term_name_arr, $template_type, $order);
									}
								}
							}
						}

						//adding empty value if no term found
						$term_name_arr 	= (0 === count($term_name_arr) ? array('--') : $term_name_arr);
	                	$term_name 		= implode(", ",$term_name_arr);

	                	if("Yes" === $order_wise_split)
	                	{
	                		$order_text = self::order_text_for_product_table_grouping_row($item, $template_type);
	                		if(1 === $is_category_under_order)
	                		{
	                			if(!isset($item_arr[$order_text]))
								{
									$item_arr[$order_text] 	= array();
								}
								if(!isset($item_arr[$order_text][$term_name]))
								{
									$item_arr[$order_text][$term_name] = array();
								}

	                			$item_arr[$order_text][$term_name][] = $item_obj;
	                		}else
	                		{
	                			if(!isset($item_arr[$term_name]))
								{
									$item_arr[$term_name] = array();
								}
								if(!isset($item_arr[$term_name][$order_text]))
								{
									$item_arr[$term_name][$order_text] = array();
								}
								
	                			$item_arr[$term_name][$order_text][] = $item_obj;
	                		}
	                	}else
	                	{

	                		if(!isset($item_arr[$term_name]))
							{
								$item_arr[$term_name]=array();
							}

	                		//avoiding duplicate row of products (Picklist)
	                		if("picklist" === $template_type) //not need a checking, but for perfomance and security
	                		{
	                			$variation_id 	= (isset($item['extra_meta_details']['_variation_id']) && !is_array($item['extra_meta_details']['_variation_id']) && "" !== trim($item['extra_meta_details']['_variation_id']) ? $item['extra_meta_details']['_variation_id'] : $item['variation_id']);
	                			
	                			$product_id 	= ("" !== $variation_id ? $variation_id : $item['id']);

	                			if(isset($item_arr[$term_name][$product_id])) //already added then increment the quantity
	                			{
	                				$cr_item 	= $item_arr[$term_name][$product_id];
	                				$increase_quantity = true;
	                				
	                				if($compare_with_variation_data) /* compare with variation data too */
	                				{
	                					$cr_item_variation_data = $cr_item->item['variation_data'];
	                					$item_variation_data 	= $item_obj->item['variation_data'];
	                					
	                					if($cr_item_variation_data !== $item_variation_data) /* variation data doesn't matches so add as different item */
	                					{
	                						$increase_quantity 	= false;
	                						$product_id 		= $product_id.'_'.$item_obj->item['order_item_id'];
	                					}
	                				}

	                				if($increase_quantity)
	                				{
	                					$new_quantity 	= ((int) $cr_item->qty) + ((int) $item_obj->qty);
		                				$cr_item->qty 	= $new_quantity;
		                				$cr_item->item['quantity'] = $new_quantity;
		                				$item_obj 		= $cr_item;
	                				}
	                			} 
	                			$item_arr[$term_name][$product_id] = $item_obj;		                			
	                		}else
	                		{
                        		$item_arr[$term_name][] = $item_obj;
                        	}
                    	}
	                }else
	                {
	                	$order_text = self::order_text_for_product_table_grouping_row($item,$template_type);
	                	$item_arr[$order_text][] = $item_obj;
	                }
	            }

	            $item_arr = apply_filters('wf_pklist_alter_package_grouped_order_items', $item_arr, array('order'=>$order_wise_split, 'category'=>$category_wise_split), $order_package, $template_type, $order);	            

	            $total_column 	= self::get_total_table_columms_enabled($columns_list_arr);
	            $serial_no 		= 1;

	            if("Yes" === $order_wise_split && "Yes" === $category_wise_split)
	            {
	            	foreach($item_arr as $key=>$val_arr)
            		{
		            	$html 	.= self::get_product_table_grouping_row($is_category_under_order, 1, $key, $total_column, $template_type);
            			foreach($val_arr as $val_key=>$val)
            			{
            				$html .= self::get_product_table_grouping_row($is_category_under_order, 2, $val_key, $total_column, $template_type);
			            	foreach($val as $cat_ind=>$cat_data) 
			            	{
			            		// get the product; if this variation or product has been deleted, this will return null...
					    		$_product 	= $cat_data->obj;
					    		$item 		= $cat_data->item;
					    		
					    		if($_product)
					    		{	
					    			$item["serial_no"] = $serial_no;
					    			$html 	.= self::generate_package_product_table_product_column_html($wc_version,$the_options,$order,$template_type,$_product,$item,$columns_list_arr,$column_list_options_value);
					    			$serial_no++;
					    		}
			            	}
            			}
            		}
	            }else
	            {
	            	foreach($item_arr as $val_key=>$val)
        			{
        				$is_group_by_cat 	= ("Yes" === $category_wise_split ? 1 : 0);
        				$html.=self::get_product_table_grouping_row($is_group_by_cat, 2, $val_key, $total_column, $template_type);
		            	
		            	foreach($val as $cat_ind=>$cat_data) 
		            	{
		            		// get the product; if this variation or product has been deleted, this will return null...
				    		$_product 	= $cat_data->obj;
				    		$item 		= $cat_data->item;
				    		
				    		if($_product)
				    		{	
				    			$item["serial_no"] = $serial_no;
				    			$html 	.=self::generate_package_product_table_product_column_html($wc_version,$the_options,$order,$template_type,$_product,$item,$columns_list_arr,$column_list_options_value);
				    			$serial_no++;
				    		}
		            	}
        			}
	            }
           	}else
           	{
           		$serial_no = 1;
           		if("single_packing" === $package_type && "picklist" === $template_type) /* remove the duplicates and increase the quantity. not need a template type checking, but for perfomance and security */
           		{
           			$item_arr 	= array();
           			foreach ($order_package as $id => $item)
					{	            		
						$product_id = ("" !== $item['variation_id'] ? $item['variation_id'] : $item['id']);
						
						if(isset($item_arr[$product_id])) //already added then increment the quantity
						{
							$cr_item = $item_arr[$product_id];
							$item_arr[$product_id]['quantity'] = ((int) $cr_item['quantity']) + ((int) $item['quantity']);
						}else
						{
							$item_arr[$product_id] = $item;
						}
	            	}
	            	$order_package 	= $item_arr;
           		}

           		foreach($order_package as $id => $item)
	            {	            	
	            	$_product = wc_get_product($item['id']);                
	                if("" !== $item['variation_id'])
	                {
	                   $parent_id 	= wp_get_post_parent_id($item['variation_id']);
	                   $item['id']	= $parent_id; 
	                }
	                if($_product)
				    {
				    	$item["serial_no"] = $serial_no;
	            		$html .= self::generate_package_product_table_product_column_html($wc_version,$the_options,$order,$template_type,$_product,$item,$columns_list_arr,$column_list_options_value);
	            		$serial_no++;
	            	}
	            }
           	}
           	$html = apply_filters('wf_pklist_package_product_tbody_html', $html, $columns_list_arr, $template_type, $order, $box_packing, $order_package);
        }else
        {
			$html = self::dummy_product_row($columns_list_arr,$column_list_options_value);
        }
        return $html;
	}

	/**
	*	@since 1.0.0
	* 	- Prepare grouping row for package product table Eg: Order wise(Only for picklist), Category wise
	*	- Added new filter to alter grouping row content
	*/
	public static function get_product_table_grouping_row($is_category_under_order, $loop, $key, $total_column, $template_type)
	{
		$row_type 	= 'category';
		if((1 === $is_category_under_order && 1 === $loop) || (1 !== $is_category_under_order && 2 === $loop))
		{
			$row_type = 'order';
		}
		$key = apply_filters('wf_pklist_alter_grouping_row_data', $key, $row_type, $template_type);
		
		if("category" === $row_type)
		{
			$category_tr_html 	= '<tr class="wfte_product_table_category_row"><td colspan="'.esc_attr($total_column).'">'.wp_kses_post($key).'</td></tr>';
			return apply_filters('wf_pklist_alter_category_row_html', $category_tr_html, $key, $total_column, $template_type);
		}else
		{
			$order_tr_html 	= '<tr class="wfte_product_table_order_row"><td colspan="'.esc_attr($total_column).'">'.wp_kses_post($key).'</td></tr>';
    		return apply_filters('wf_pklist_alter_order_row_html', $order_tr_html, $key, $total_column, $template_type);
		}
	}

	/**
	* 
	* Render image column for product table
	* @since 1.0.0
	* - Default image option added, CSS class option added
	* - Added filter to alter image URL
	*/
	public static function generate_product_image_column_data($product_id, $variation_id, $parent_id)
	{
		$img_url = plugin_dir_url(plugin_dir_path(__FILE__)).'assets/images/thumbnail-preview.png';
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

        return '<img src="'.esc_url($img_url).'" style="max-width:30px; max-height:30px; border-radius:25%;" class="wfte_product_image_thumb"/>';
	}

	/**
	*	@since 1.0.0 
	* 	- Prepare order grouping row text for package product table
	*	- Added new filter(wf_pklist_alter_order_grouping_row_text_glue) to alter order info text glue.
	*/
	public static function order_text_for_product_table_grouping_row($item, $template_type)
	{
		$order_text = __('Unknown','wt_woocommerce_invoice_addon');
		if(isset($item['order']) && !is_null($item['order']) && is_object($item['order']) && is_a($item['order'],'WC_Order'))
		{
			$order_info_arr 	= array();
			$order_info_arr[]	= __('Order number','wt_woocommerce_invoice_addon').': '.self::get_order_number($item['order'],$template_type);
			
			if(Wf_Woocommerce_Packing_List_Public::module_exists('invoice'))
			{
				$order_info_arr[] = __('Invoice number','wt_woocommerce_invoice_addon').': '.Wf_Woocommerce_Packing_List_Invoice::generate_invoice_number($item['order'],false); //do not force generate
			}
			$order_info_arr = apply_filters('wf_pklist_alter_order_grouping_row_text', $order_info_arr, $item['order'], $template_type);
			
			$order_info_glue = " ";
			$order_info_glue = apply_filters('wf_pklist_alter_order_grouping_row_text_glue', $order_info_glue, $item['order'], $template_type);
			
			$order_text = implode($order_info_glue, $order_info_arr);
		}
		return $order_text;
	}

	public static function generate_product_table_head_html($columns_list_arr,$template_type)
	{
		$is_rtl_for_pdf 		= false;
		$is_rtl_for_pdf 		= apply_filters('wf_pklist_is_rtl_for_pdf',$is_rtl_for_pdf,$template_type);
		$first_visible_td_key 	= '';
		$last_visible_td_key	='';
		foreach ($columns_list_arr as $columns_key=>$columns_value)
		{
			$is_hidden	= ("-" === $columns_key[0] ? 1 : 0); //column not enabled
			
			if(strpos($columns_key,'-individual_tax_') !== false){
				$is_hidden = 1;
			}

			if(strip_tags($columns_value) === $columns_value) //column entry without th HTML so we need to add
			{
				$coumn_key_real = (1 === $is_hidden ? substr($columns_key,1) : $columns_key);
				$columns_value  = '<th class="wfte_product_table_head_'.$coumn_key_real.' wfte_product_table_head_bg wfte_table_head_color" col-type="'.$columns_key.'">'.$columns_value.'</th>';
			}
			
			if(1 === $is_hidden)
			{
				$columns_value_updated 	= self::addClass('', $columns_value, Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::TO_HIDE_CSS);
				if($columns_value_updated === $columns_value) //no class attribute in some cases
				{
					$columns_value_updated = str_replace('<th>','<th class="'.Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::TO_HIDE_CSS.'">',$columns_value);
				}
			}else
			{
				$columns_value_updated = self::removeClass('',$columns_value,Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::TO_HIDE_CSS);

				if("" === $first_visible_td_key)
				{
					$first_visible_td_key = $columns_key;
				}
				$last_visible_td_key = $columns_key;
			}
			//remove last column CSS class
			$columns_value_updated 			= str_replace('wfte_right_column','',$columns_value_updated);
			$columns_list_arr[$columns_key]	= $columns_value_updated;
		}

		//add end th CSS class
		$end_td_key=(false === $is_rtl_for_pdf ? $last_visible_td_key : $first_visible_td_key);
		if("" !== $end_td_key)
		{
			$columns_class_added 	= self::addClass('', $columns_list_arr[$end_td_key], 'wfte_right_column');
			if($columns_class_added === $columns_list_arr[$end_td_key]) //no class attribute in some cases, so add it
			{
				$columns_class_added = str_replace('<th>','<th class="wfte_right_column">',$columns_list_arr[$end_td_key]);
			}
			$columns_list_arr[$end_td_key] = $columns_class_added;
		}
		$columns_list_val_arr = array_values($columns_list_arr);
		return implode('',$columns_list_val_arr);
	}

	public static function build_product_row_html($product_row_columns, $columns_list_arr, $order_item_id)
	{
		$html = '';
		if(is_array($product_row_columns))
        {
        	/**
        	* Added CSS class for Bundled products
        	*/
        	$is_bundle 	= false;
        	self::is_bundle_type($is_bundle, $order_item_id);
        	$tr_class 	= "";

        	if(false !== $is_bundle)
        	{
        		$tr_class .= (1 === $is_bundle ? ' wfte_product_row_bundle_parent' : ' wfte_product_row_bundle_child');
        	}

        	$html .= '<tr class="'.$tr_class.'">';
        	foreach($product_row_columns as $columns_key=>$columns_value) 
        	{
				$hide_it	= ("-" === $columns_key[0] ? Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::TO_HIDE_CSS : ""); //column not enabled
				if(strpos($columns_key,'-individual_tax_') !== false){
					$hide_it = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::TO_HIDE_CSS;
				}
        		$extra_col_options = $columns_list_arr[$columns_key];
        		$td_class = $columns_key.'_td';
        		$html 	.= '<td class="'.$hide_it.' '.$td_class.' '.$extra_col_options.'">';
        		$html 	.= $columns_value;
        		$html 	.= '</td>';
        	}
        	$html.='</tr>';
        }
        return $html;
	}

	/**
	*	@since 1.0.0
	*	The current order item is bundle type. Currently compatible with YITH and WC
	*/
	public static function is_bundle_type(&$is_bundle, $order_item_id)
	{
		/**	
		*	Woocommerce Product Bundles
		*		_bundled_by -  child
		*		_bundled_items -  parent
		*		_bundle_cart_key -  bundle item id
		*/

		/**	
		*	YITH WooCommerce Product Bundles 
		*		_cartstamp - Parent
		*		_bundled_by - Child
		*		_yith_bundle_cart_key -  bundle item id(Parent) - (Pro only)
		*/


		$bundle_info_arr=array(
			'_bundled_by'		=>'', //for child
			'_bundle_cart_key'	=>'', //for parent
		);

		$meta_data_arr 	= wc_get_order_item_meta($order_item_id, '', false);

		/* check, it is a product bundle main product */
		if(isset($meta_data_arr['_bundled_items']) || isset($meta_data_arr['_cartstamp']))
		{
			$is_bundle 	= 1; //parent
			if(isset($meta_data_arr['_bundle_cart_key'])) //WC
			{
				$bundle_info_arr['_bundle_cart_key']	= $meta_data_arr['_bundle_cart_key'];

			}elseif(isset($meta_data_arr['_yith_bundle_cart_key'])) //Yith pro
			{
				$bundle_info_arr['_bundle_cart_key']	= $meta_data_arr['_yith_bundle_cart_key'];
			}
			$bundle_info_arr=self::_bundle_product_process_assoc_array($bundle_info_arr, '_bundle_cart_key');

		}elseif(isset($meta_data_arr['_bundled_by']))
		{
			$is_bundle 	= 2; //child
			$bundle_info_arr['_bundled_by']	= (isset($meta_data_arr['_bundled_by']) ? $meta_data_arr['_bundled_by'] : '');
			$bundle_info_arr = self::_bundle_product_process_assoc_array($bundle_info_arr, '_bundled_by');			
		}
		
		return $bundle_info_arr;
	}

	/** 
	*	@since 1.0.0
	*	In some cases the bundle meta data is saved as array 
	*/
	private static function _bundle_product_process_assoc_array($bundle_info_arr, $key)
	{
		if(isset($bundle_info_arr[$key]) && is_array($bundle_info_arr[$key]))
		{
			if(isset($bundle_info_arr[$key][0]) && is_string($bundle_info_arr[$key][0]))
			{
				$bundle_info_arr[$key]=$bundle_info_arr[$key][0];
			}else
			{
				$bundle_info_arr[$key]='';
			}
		}
		return $bundle_info_arr;
	}

	/**
	*	Sort order items, By Name SKU etc
	*	@since 1.0.0
	*/
	public static function sort_items($items, $template_type, $order, $doc_type="non-package", $sort_by="", $sort_order="asc")
	{
		$out 			= array();
		$sort_key_arr 	= array(); //this is for package documents only
		$module_id 		= Wf_Woocommerce_Packing_List::get_module_id($template_type);	
		$bundle_display_option = Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_bundle_display_option($template_type, $order);

		/* we have to disable bundle display option when category wise display is enabled */
		$category_wise_split = Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_product_category_wise_splitting', $module_id);
		$previous_sort_key 	= '';
		$bundle_child_arr 	= array();

		foreach($items as $k=>$item)
		{
			$sort_key=$k;
			$sort_key_a = $k;
			if("" !== $sort_by)
			{
				if("non-package" === $doc_type) //invoice, proforma etc
				{
					$product = $item->get_product();
					if($product instanceof WC_Product)
					{
		                $sort_key_suffix = '_'.$k;

						if("name" === $sort_by)
						{
							$sort_key = $product->get_name().$sort_key_suffix;
							$sort_key_a = $product->get_name();
						}
						elseif("sku" === $sort_by)
						{
							$sort_key = $product->get_sku().$sort_key_suffix;
							$sort_key_a = $product->get_sku();
						}
					}
				}else
				{			
					$sort_key_suffix = '_'.$item['order_item_id'];

					if("name" === $sort_by)
					{
						$sort_key = $item['name'].$sort_key_suffix;
						$sort_key_a = $item['name'];
					}
					elseif("sku" === $sort_by)
					{
						$sort_key = $item['sku'].$sort_key_suffix;
						$sort_key_a = $item['sku'];
					}

					/**
					* To get item  quantity on Picklist when packaging is Pack items individually
					*/
					if(isset($sort_key_arr[$sort_key]))
					{
						$sort_key_arr[$sort_key] = $sort_key_arr[$sort_key]+1;
						$sort_key .= '-'.$sort_key_arr[$sort_key];					
					}else
					{
						$sort_key_arr[$sort_key] = 1;
					}			
				}
			}		
			
			/* filter to alter sort key */
			$sort_key = apply_filters('wt_pklist_alter_product_sort_by', $sort_key, $item, $template_type, $sort_by, $order);
			$remove_current_item 	= false;
			$bundle_info_arr 		= array();

			if("Yes" !== $category_wise_split) /* Check category wise grouping not enabled */
			{
				$is_bundle 		= false;
				$order_item_id 	= ("non-package" === $doc_type ? $k : (isset($item['order_item_id']) ? $item['order_item_id'] : 0));
				$bundle_info_arr= self::is_bundle_type($is_bundle, $order_item_id);				

				if(1 === $is_bundle) //parent
				{
					if("sub" === $bundle_display_option)
					{
						$remove_current_item = true;
					}

				}elseif(2 === $is_bundle) //child
				{
					if("main" === $bundle_display_option)
					{
						$remove_current_item = true;

					}elseif("main-sub" === $bundle_display_option)
					{
						/* check this as a bundle child, only if there is a bundle_by attribute, Otherwise treat as a normal order item */
						if(isset($bundle_info_arr['_bundled_by']) && "" !== $bundle_info_arr['_bundled_by'])
						{
							/* check this is the first child of the bundle parent. (Not items already added) */
							if(!isset($bundle_child_arr[$bundle_info_arr['_bundled_by']]))
							{
								if("" !== $previous_sort_key && isset($out[$previous_sort_key])) //previous item exists(Parent item) in the loop, Otherwise treat as a normal order item
								{
									if("" === $out[$previous_sort_key][2]['_bundle_cart_key']) /* bundle cart key is empty then fill it with bundle child `_bundled_by`. for Yith basic users. */
									{
										$out[$previous_sort_key][2]['_bundle_cart_key'] = $bundle_info_arr['_bundled_by'];
									}
									
									$remove_current_item = true; //remove the item from main sorting array and add it to separate array
									
									/* add child item to separate children array under its parent's _bundle_cart_id */
									$bundle_child_arr[$bundle_info_arr['_bundled_by']] = array($sort_key=>array($item, $k, $bundle_info_arr,$sort_key_a));

								}
							}else
							{
								$remove_current_item = true; //remove the item from main sorting array and add it to separate array
								$bundle_child_arr[$bundle_info_arr['_bundled_by']][$sort_key] = array($item, $k, $bundle_info_arr,$sort_key_a);
							}
						}				
					}
				}			
			}

			if(!$remove_current_item)
			{		
				$out[$sort_key] = array($item, $k, $bundle_info_arr,$sort_key_a);
			}
			$previous_sort_key = $sort_key;
		}

		$sort_order = apply_filters('wt_pklist_alter_product_sort_order', $sort_order, $template_type, $order);

		$main_align = array();
		foreach ($out as $mkey => $mrow)
		{
		    $main_align[$mkey] = $mrow[3];
		}

		if("asc" === $sort_order)
		{
			array_multisort($main_align, SORT_ASC, $out);
		}else
		{
			array_multisort($main_align, SORT_DESC, $out);
		}

		$items = array();
		foreach($out as $v)
		{
			$items[$v[1]] = $v[0];
			
			if("Yes" !== $category_wise_split) /* Check category wise grouping not enabled */
			{
				if(isset($v[2]) && isset($v[2]['_bundle_cart_key']) 
					&& "" !== $v[2]['_bundle_cart_key'] 
					&& isset($bundle_child_arr[$v[2]['_bundle_cart_key']])) /*  confirming its a bundle parent  */
				{
					
					$current_bundle_child_arr = $bundle_child_arr[$v[2]['_bundle_cart_key']];
					$sub_align = array();
					foreach ($current_bundle_child_arr as $sub_key => $sub_row)
					{
					    $sub_align[$sub_key] = $sub_row[3];
					}
					/* sort child items */
					if("asc" === $sort_order)
					{
						array_multisort($sub_align, SORT_ASC, $current_bundle_child_arr);
					}else
					{
						array_multisort($sub_align, SORT_DESC, $current_bundle_child_arr);
					}
					foreach ($current_bundle_child_arr as $child_arr)
					{
						$items[$child_arr[1]]=$child_arr[0]; //add child items to main array
					}
				}
			}
		}
		return $items;
	}

	/**
	* 	Render product table row HTML for non package type documents
	* 	@since 1.0.0 
	* 	- Group by category option added
	* 	- Order item sorting added
	*/
	public static function generate_product_table_product_row_html($columns_list_arr, $template_type, $order=null,$refund_order = null, $refund_id = null,$column_list_options_value=array())
	{
		$html = '';
		$show_after_refund = apply_filters('wt_pklist_show_details_after_refund_'.$template_type,false,$template_type);
		//module settings are saved under module id
		$module_id = Wf_Woocommerce_Packing_List::get_module_id($template_type);
		$free_line_items_enable = Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_invoice_free_line_items',$module_id);

		if(!is_null($order))
        {
			$wc_version 	= WC()->version;
			$order_id 		= $wc_version<'2.7.0' ? $order->id : $order->get_id();
			$user_currency 	= Wt_Pklist_Common_Ipc::get_order_meta($order_id,'currency', true);
			$incl_tax_text 	= '';
			$incl_tax 		= false;
			$tax_type 		= Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_taxstatus');

			if(in_array('in_tax', $tax_type)) /* including tax */
			{
				$incl_tax_text 	= self::get_tax_incl_text($template_type, $order, 'product_price');
				$incl_tax_text 	= ($incl_tax_text!="" ? ' ('.$incl_tax_text.')' : $incl_tax_text);
				$incl_tax 		= true;
			}

			$the_options 	= Wf_Woocommerce_Packing_List::get_settings($module_id);
			$order_items 	= $order->get_items();
			$full_refunded	= Wt_Pklist_Common_Ipc::is_fully_refunded($order);
			
			if(!is_null($refund_order) && false === $full_refunded){
				$order_items = $refund_order->get_items();
			}

			/**
			*	Sort order items
			*/
			if(isset($the_options['sort_products_by']) && "" !== $the_options['sort_products_by'])
			{
				$sort_config_arr 	= explode("_", $the_options['sort_products_by']);
				$sort_by 			= $sort_config_arr[0];
				$sort_order 		= (isset($sort_config_arr[1]) ? $sort_config_arr[1] : "");
				$order_items 		= self::sort_items($order_items, $template_type, $order, "non-package", $sort_by, $sort_order);
			}else
			{
				/* the sort function also handles bundle product compatibilty, here no sorting enabled so it will handle bundle products only */
				$order_items 	= self::sort_items($order_items, $template_type, $order, "non-package");
			}
			

			$order_items = apply_filters('wf_pklist_alter_order_items', $order_items, $template_type, $order);			
			if($wc_version<'2.7.0')
			{
	            $order_prices_include_tax 	= $order->prices_include_tax;
	            $order_display_cart_ex_tax 	= $order->display_cart_ex_tax;
	        }else{
	            $order_prices_include_tax 	= $order->get_prices_include_tax();
	            $order_display_cart_ex_tax 	= Wt_Pklist_Common_Ipc::get_order_meta($order_id, '_display_cart_ex_tax', true);
	        }	        

	        /**
	        *	Check grouping enabled
	        */
	        $category_wise_split = Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_product_category_wise_splitting',$module_id);
	        $item_arr 		= array();
	        $total_column 	= self::get_total_table_columms_enabled($columns_list_arr);
	        
	        if("Yes" === $category_wise_split)
	        {
	        	foreach ($order_items as $order_item_id=>$order_item) 
				{
					$product_id 	= $order_item->get_product_id();
	        		$term_name_arr	= array();
	        		$term_name_arr 	= self::get_term_data($product_id, $term_name_arr, $template_type, $order); 
	        		
	        		//adding empty value if no term found
					$term_name_arr 	= (0 === count($term_name_arr) ? array('--') : $term_name_arr);
                	$term_name 		= implode(", ",$term_name_arr);
                	if(!isset($item_arr[$term_name]))
                	{
                		$item_arr[$term_name] = array();
                	}
                	$item_arr[$term_name][$order_item_id] = $order_item;
	        	}
	        }else /* prepare same array structure as in the grouping */
	        {
	        	$item_arr[] = $order_items;
	        }

			$tax_items_arr 	= $order->get_items('tax');
			$tax_data_arr 	= array();

			foreach ($tax_items_arr as $tax_item)
			{
				$tax_data_arr[$tax_item->get_rate_id()] = $tax_item->get_rate_percent();
			}
			
			// $order = Wf_Woocommerce_Packing_List_Admin::check_full_refunded_property($order);
	        foreach($item_arr as $item_key=>$items)
	        {
        		if("Yes" === $category_wise_split)
	        	{
        			$html .= self::get_product_table_grouping_row(1, 2, $item_key, $total_column, $template_type);
        		}

        		/* Get the refunded items id to show only the refunded items in credit note */
        		$refunded_line_items 	= array();
        		$order_refunds 			= $order->get_refunds();

        		if(!empty($order_refunds)){
        			foreach( $order_refunds as $refund ){
						$refunded_items = $refund->get_items();
						foreach($refunded_items as $refund_item){
							$refunded_line_items[] = $refund_item->get_meta('_refunded_item_id');
						}
					}
        		}

        		$serial_no = 1;
				foreach ($items as $order_item_id=>$order_item) 
				{
					$refunded_item_id = $order_item_id;

					if("creditnote" === $template_type){
						$order_item_id 	= wc_get_order_item_meta($refunded_item_id, '_refunded_item_id', true);
						if($order_item_id){
							$order_item = new WC_Order_Item_Product($order_item_id);
						}
						$order_item->this_refund_item_id = $refunded_item_id;
					}
					
					/* Free line items display in invoice */
					$product_total_free_order = ($wc_version< '2.7.0' ? $order->get_item_meta($order_item_id,'_line_total',true) : $order->get_line_total($order_item, $incl_tax, true));
					
					if(0 === \intval($order->get_total())){
						if("No" === $free_line_items_enable){
							if ((0.0 === (float) $order_item['line_total']) && (0.0 === (float)$product_total_free_order) ) {
		                    	continue;
		                	}
						}
					}else{
						if("No" === $free_line_items_enable){
							if ((0.0 === (float) $order_item['line_total']) || (0.0 === (float)$product_total_free_order) ) {
		                    	continue;
		                	}
						}
					}

				    /* get the product; if this variation or product has been deleted, this will return null... */
				    $_product = $order_item->get_product();
				    if($_product)
				    {
				        $product_row_columns = array(); //for html generation
				        $product_id 		= ($wc_version< '2.7.0' ? $_product->id : $_product->get_id());
				        $variation_id 		= ($order_item['variation_id']!='' ? $order_item['variation_id']*1 : 0);
				        $parent_id 			= wp_get_post_parent_id($variation_id);
				        $item_taxes 		= $order_item->get_taxes();
				        $item_tax_subtotal	= (isset($item_taxes['subtotal']) ? $item_taxes['subtotal'] : array());

						$col_val_source_details = array(
							'wc_version' => $wc_version,
							'user_currency' => $user_currency,
							'the_options' => $the_options,
							'template_type' => $template_type,
							'product_id' => $product_id,
							'_product' => $_product,
							'order' => $order,
							'order_id' => $order_id,
							'order_item' => $order_item,
							'order_item_id' => $order_item_id,
							'column_list_options_value' => $column_list_options_value,
							'variation_id' => $variation_id,
							'parent_id' => $parent_id,
						);

				        foreach($columns_list_arr as $columns_key => $columns_value)
				        {
				        	$columns_key_real = $columns_key; /* backup */
				        	
				        	if(0 === strpos($columns_key, 'default_column_')) /* if the current column added by customer, and its a default column */
				        	{
				        		$columns_key = str_replace('default_column_', '', $columns_key);
				        	}
							$columns_key = Wf_Woocommerce_Packing_List_Template_Render::get_column_key($columns_key);
							$col_val_source_details['columns_key'] = $columns_key;

				        	if("serial_no" === $columns_key || "-serial_no" === $columns_key){
				        		$column_data = $serial_no;
				        	}
				            elseif("image" === $columns_key || "-image" === $columns_key)
				            {
				            	$column_data = Wf_Woocommerce_Packing_List_Template_Render::generate_product_image_column_data($product_id,$variation_id,$parent_id,$column_list_options_value['image']);
				            }
				            elseif("sku" === $columns_key || "-sku" === $columns_key)
				            {
				            	$column_data = $_product->get_sku();
				            }
				            elseif("product" === $columns_key || "-product" === $columns_key)
				            {
				            	$product_name 	= (isset($order_item['name']) ? $order_item['name'] : '');
				            	$product_name 	= apply_filters('wf_pklist_alter_product_name', $product_name, $template_type, $_product, $order_item, $order);
								$product_name	= ("" !== trim($product_name)) ? $product_name.'<br>' : $product_name;
				            	/*variation data*/
				            	$variation 	= '';
				            	if(isset($the_options['woocommerce_wf_packinglist_variation_data']) && "Yes" === $the_options['woocommerce_wf_packinglist_variation_data'])
				            	{ 
				            		// get variation data, meta data
					            	$variation = Wf_Woocommerce_Packing_List_Customizer::get_order_line_item_variation_data($order_item, $order_item_id, $_product, $order, $template_type);
							        
							        $item_meta = function_exists('wc_get_order_item_meta') ? wc_get_order_item_meta($order_item_id, '', false) : $order->get_item_meta($order_item_id);

							        $variation_data = apply_filters('wf_pklist_add_product_variation', $item_meta, $template_type, $_product, $order_item, $order);

							        if(!empty($variation_data) && !is_array($variation_data))
							        {
							        	$variation .= '<br>'.$variation_data;
							        }
							        if(!empty($variation))
							        {	        
							        	$variation = '<small style="word-break: break-word;">'.$variation.'</small>';
							        }
						    	}


						        /*additional product meta*/
						        $addional_product_meta 	= '';
						        $meta_data_formated_arr	= array();

								// render product meta beneath the product name
								if(class_exists('Wf_Woocommerce_Packing_List_Template_Render_Adc')){
									$meta_data_formated_arr = Wf_Woocommerce_Packing_List_Template_Render_Adc::process_p_meta_product_name_column($meta_data_formated_arr,$col_val_source_details);
								}else{
									$meta_data_formated_arr = Wf_Woocommerce_Packing_List_Template_Render::process_p_meta_product_name_column($meta_data_formated_arr,$col_val_source_details);
								}
						        
								// render theme complete - extra product option - product meta
						        if(class_exists('Wf_Woocommerce_Packing_List_Template_Load_Epo_Tm')){
									$meta_data_formated_arr = Wf_Woocommerce_Packing_List_Template_Load_Epo_Tm::process_tmcart_meta_below_product_name($meta_data_formated_arr,$col_val_source_details);
								}

						        /**
						    	* - The string glue to combine meta data items
						    	*/
								$string_glue = '<br>';
						    	$string_glue = apply_filters('wt_pklist_product_meta_string_glue', $string_glue, $order, $template_type);
						    	$addional_product_meta = implode($string_glue, $meta_data_formated_arr);
						        $addional_product_meta = apply_filters('wf_pklist_add_product_meta', $addional_product_meta,$template_type,$_product,$order_item,$order);

						        /**
						        *	Product attribute
						       	*/
						        $product_attr = '';	
						        $product_attr_formated_arr = array();
								
								// render product meta beneath the product name
								if(class_exists('Wf_Woocommerce_Packing_List_Template_Render_Adc')){
									$product_attr_formated_arr = Wf_Woocommerce_Packing_List_Template_Render_Adc::process_p_attr_product_name_column($product_attr_formated_arr,$col_val_source_details);
								}else{
									$product_attr_formated_arr = Wf_Woocommerce_Packing_List_Template_Render::process_p_attr_product_name_column($product_attr_formated_arr,$col_val_source_details);
								}

						        /**
						    	* The string glue to combine product attr items
						    	*/
								$string_glue 	= '<br>';
						    	$string_glue 	= apply_filters('wt_pklist_product_attr_string_glue', $string_glue, $order, $template_type);
						    	$product_attr 	= implode($string_glue, $product_attr_formated_arr);
						        $product_attr 	= apply_filters('wf_pklist_alter_product_attr_data', $product_attr, $template_type, $_product, $order_item, $order);
						        $product_column_data_arr = array(
						        	'product_name' 	=> $product_name,
						        	'variation'		=> $variation,
						        	'product_meta'	=> $addional_product_meta,
						        	'product_attr' 	=> $product_attr,
						        );

						        /**
						        * To alter the data items in product column
						        */
						        $product_column_data_arr = apply_filters('wf_pklist_alter_product_column_data_arr', $product_column_data_arr, $template_type, $_product, $order_item, $order);
						        $column_data = '';

						        if(is_array($product_column_data_arr))
						        {
						        	$product_column_data_arr = array_filter(array_values($product_column_data_arr));
						        	$column_data = implode('<br>', $product_column_data_arr);
						        }else
						        {
						        	$column_data = $product_column_data_arr;
						        }
				            }
				            elseif("quantity" === $columns_key || "-quantity" === $columns_key)
				            {
								$qty_col_val = (float)$order_item['qty'];
								if($show_after_refund){
									$qty_col_val -= abs((float)$order->get_qty_refunded_for_item($order_item->get_id()));
								}
				            	$column_data = apply_filters('wf_pklist_alter_item_quantiy',$qty_col_val,$template_type,$_product,$order_item,$order);
				            }
				            elseif("price" === $columns_key || "-price" === $columns_key)
				            {
								$item_price 	= Wf_Woocommerce_Packing_List_Template_Render::render_unit_price($col_val_source_details);
	                    		$item_price 	= apply_filters('wf_pklist_alter_item_price',$item_price,$template_type,$_product,$order_item,$order);
	                    		$item_price_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$item_price);
	                    		$column_data  	= apply_filters('wf_pklist_alter_item_price_formated',$item_price_formated,$template_type,$item_price,$_product,$order_item,$order);         	
				            }
				            elseif(false !== strpos($columns_key,'individual_tax_'))
				            {
								$column_data = Wf_Woocommerce_Packing_List_Template_Render::render_tax_items_column($col_val_source_details);
				            }
				            elseif("tax" === $columns_key || "-tax" === $columns_key)
				            {
								$column_data = Wf_Woocommerce_Packing_List_Template_Render::render_total_tax($col_val_source_details);
				            }
				            elseif("total_price" === $columns_key || "-total_price" === $columns_key)
				            {
								$product_total = Wf_Woocommerce_Packing_List_Template_Render::render_total_price($col_val_source_details);
		                        $product_total = apply_filters('wf_pklist_alter_item_total', $product_total, $template_type, $_product, $order_item, $order, $incl_tax);
		                        $product_total_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$product_total);
		                        $column_data = apply_filters('wf_pklist_alter_item_total_formated', $product_total_formated, $template_type, $product_total, $_product, $order_item, $order, $incl_tax);

				            }elseif("category" === $columns_key || "-category" === $columns_key)
				            {	
				            	if(!empty($parent_id))
								{
									$product_id = $parent_id;
								}
	        					$column_data = self::set_category_col($product_id, $template_type, $order);
				            }
							elseif("total_weight" === $columns_key || "-total_weight" === $columns_key)
							{
								$qty_col_val = (float)$order_item['qty'];
								if($show_after_refund){
									$qty_col_val -= abs((float)$order->get_qty_refunded_for_item($order_item->get_id()));
								}
								$product_weight = (float)get_post_meta( $product_id, '_weight', true );
								$total_weight = (float) $product_weight * $qty_col_val .''.get_option('woocommerce_weight_unit');
								$column_data 	= apply_filters('wf_pklist_alter_item_total_weight', $total_weight, $template_type, $_product, $order_item, $order);         	
							}
				            else //custom column by user
				            {
				            	$column_data = '';
				            	if(!self::set_custom_meta_table_col_data($column_data, $columns_key_real, $product_id, $order_item_id,$order_item))
				            	{
				            		$column_data = apply_filters('wf_pklist_product_table_additional_column_val', $column_data, $template_type, $columns_key_real, $_product, $order_item, $order);
				            	}
				            }
				            $product_row_columns[$columns_key_real] = $column_data;
				        }
				        $product_row_columns = apply_filters('wf_pklist_alter_product_table_columns',$product_row_columns,$template_type,$_product,$order_item,$order);
				        $html .= self::build_product_row_html($product_row_columns, $columns_list_arr, $order_item_id);
				    }else{
				    	$product_row_columns 	= array(); //for html generation
				    	$item_taxes				= $order_item->get_taxes();
				        $item_tax_subtotal 		= (isset($item_taxes['subtotal']) ? $item_taxes['subtotal'] : array());
				        $product_id 			= 0;
				        $variation_id 			= ($order_item['variation_id']!='' ? $order_item['variation_id']*1 : 0);
				        $parent_id 				= wp_get_post_parent_id($variation_id);

						$col_val_source_details = array(
							'wc_version' => $wc_version,
							'user_currency' => $user_currency,
							'the_options' => $the_options,
							'template_type' => $template_type,
							'product_id' => $product_id,
							'_product' => null,
							'order' => $order,
							'order_id' => $order_id,
							'order_item' => $order_item,
							'order_item_id' => $order_item_id,
							'column_list_options_value' => $column_list_options_value,
							'variation_id' => $variation_id,
							'parent_id' => $parent_id,
						);
						foreach($columns_list_arr as $columns_key=>$columns_value)
						{
						    $columns_key_real = $columns_key; /* backup */
						    if(0 === strpos($columns_key, 'default_column_')) /* if the current column added by customer, and its a default column */
						    {
						        $columns_key=str_replace('default_column_', '', $columns_key);
						    }

						    if("serial_no" === $columns_key || "-serial_no" === $columns_key){
				        		$column_data = $serial_no;
				        	}
						    elseif("image" === $columns_key || "-image" === $columns_key)
						    {
						        $img_url 		= plugin_dir_url(plugin_dir_path(__FILE__)).'assets/images/thumbnail-preview.png';
						        $column_data 	= '<img src="'.esc_url($img_url).'" style="max-width:30px; max-height:30px; border-radius:25%;" class="wfte_product_image_thumb"/>';
						    }
						    elseif("product" === $columns_key || "-product" === $columns_key)
						    {
						        $product_name 	= (isset($order_item['name']) ? $order_item['name'] : '');
						        $product_name 	= apply_filters('wf_pklist_alter_item_name_deleted_product',$product_name,$template_type,$order_item,$order);
						        $column_data 	= $product_name;
						    }
						    elseif("quantity" === $columns_key || "-quantity" === $columns_key)
						    {	
								$qty_col_val = (float)$order_item['qty'];
								if($show_after_refund){
									$qty_col_val -= abs((float)$order->get_qty_refunded_for_item($order_item->get_id()));
								}
						        $column_data 	= apply_filters('wf_pklist_alter_item_quantiy_deleted_product',$qty_col_val,$template_type,$order_item,$order);
						    }
						    elseif("price" === $columns_key || "-price" === $columns_key)
						    {
								$item_price 	= Wf_Woocommerce_Packing_List_Template_Render::render_unit_price($col_val_source_details);
						        $item_price 	= apply_filters('wf_pklist_alter_item_price_deleted_product',$item_price,$template_type,$order_item,$order);
						        $item_price_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$item_price); 
						        $column_data 	= $item_price_formated;          
						    }
						    elseif(strpos($columns_key,'individual_tax_')!==false)
						    {
						        $column_data = Wf_Woocommerce_Packing_List_Template_Render::render_tax_items_column($col_val_source_details);
						    }
						    elseif("tax" === $columns_key || "-tax" === $columns_key)
						    {
						        $column_data = Wf_Woocommerce_Packing_List_Template_Render::render_total_tax($col_val_source_details);
						    }
						    elseif("total_price" === $columns_key || "-total_price" === $columns_key)
						    {
						        $product_total = Wf_Woocommerce_Packing_List_Template_Render::render_total_price($col_val_source_details);
					         	$product_total = apply_filters('wf_pklist_alter_item_total_deleted_product', $product_total, $template_type, $order_item, $order, $incl_tax);
						        $product_total_formated = Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$product_total);
						        $column_data = apply_filters('wf_pklist_alter_item_total_formated_deleted_product', $product_total_formated, $template_type, $product_total, $order_item, $order, $incl_tax);
						    }else{
						        $column_data = '';
								if(!self::set_custom_meta_table_col_data($column_data, $columns_key_real, $product_id, $order_item_id,$order_item))
				            	{
				            		$column_data = apply_filters('wf_pklist_product_table_additional_column_val', $column_data, $template_type, $columns_key_real, $_product, $order_item, $order);
				            	}
						    }
						    $product_row_columns[$columns_key_real] = $column_data;
						}
						$html .= self::build_product_row_html($product_row_columns, $columns_list_arr, $order_item_id);
				    }
			    $serial_no++;
				}
			}
			$html = apply_filters('wf_pklist_product_tbody_html', $html, $columns_list_arr, $template_type, $order);

		}else //dummy value for preview section (No order data available)
		{
			$html = self::dummy_product_row($columns_list_arr,$column_list_options_value);
		}
		return $html;
	}

	public static function set_category_col($product_id, $template_type, $order)
	{
		$term_name_arr = array();
		$term_name_arr = self::get_term_data($product_id, $term_name_arr, $template_type, $order); 
		return implode(", ", $term_name_arr);
	}

	public static function set_custom_meta_table_col_data(&$column_data, $columns_key, $product_id, $order_item_id,$order_item)
	{
		if(0 === strpos($columns_key, 'custom_product_meta_'))
		{
			$product_meta_key 	= str_replace('custom_product_meta_', '', $columns_key);
			$product_meta_value = get_post_meta($product_id, $product_meta_key, true);
			$variation_id 		= (int)($order_item['variation_id']!='' ? $order_item['variation_id']*1 : 0);
	        $parent_id 			= wp_get_post_parent_id($variation_id);
			
			if("" === $product_meta_value && $variation_id>0)
            {
            	$product_meta_value = get_post_meta($parent_id,$product_meta_key,true);
            }
            if(is_array($product_meta_value))
            {
                $product_meta_value = (self::wf_is_multi($product_meta_value) ? '' : implode(', ',$product_meta_value));
            }
			$column_data = self::process_meta_value($product_meta_value);
			return true;

		}elseif(0 === strpos($columns_key, 'custom_order_item_meta_'))
		{
			$order_item_meta_key 	= str_replace('custom_order_item_meta_', '', $columns_key);
			$order_item_meta_value 	= wc_get_order_item_meta($order_item_id, $order_item_meta_key, true);
			$column_data 			= self::process_meta_value($order_item_meta_value);
			return true;		
		}
		return false;
	}

	/**
    * @since 1.0.0
    * Get total count of enabled table columns
    */
    public static function get_total_table_columms_enabled($columns_list_arr)
    {
    	$total = 0;
    	foreach ($columns_list_arr as $key => $value) 
    	{
    		if("-" !== substr($key, 0, 1))
    		{
    			$total++;
    		}
    	}
    	return $total;
    }

    /**
	* @since 1.0.0 
	* Grouping terms
	*/
	public static function get_term_data($id, $term_name_arr, $template_type, $order)
	{
		$terms = get_the_terms($id,'product_cat');
		if($terms)
		{
			foreach($terms as $term)
			{
				$term_name_arr[] = $term->name;
			}
		}
		$term_name_arr = apply_filters('wf_pklist_alter_grouping_term_names', $term_name_arr, $id, $template_type, $order);
		return $term_name_arr;
	}

	public static function get_bundle_display_option($template_type, $order)
    {
    	$module_id = Wf_Woocommerce_Packing_List::get_module_id($template_type);
    	$bundle_display_option = Wf_Woocommerce_Packing_List::get_option('bundled_product_display_option', $module_id);		
		return apply_filters('wf_pklist_alter_bundle_display_option', $bundle_display_option, $template_type, $order);
    }
}