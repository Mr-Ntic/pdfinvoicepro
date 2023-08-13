<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

if(!class_exists('Wf_Woocommerce_Packing_List_Customizer_Ipc'))
{

include_once plugin_dir_path(__FILE__).'classes/class-customizer-pro.php';
class Wf_Woocommerce_Packing_List_Customizer_Ipc{
    
    public static $extra_field_slug_prefix = 'extra_field_';
    public $rtl_css_added=false;
    public $template_for_pdf=false;
    public $custom_css='';
    public function __construct() {
        add_action('wf_pklist_load_customizer_js_pro',array($this,'load_customizer_js_pro'),10,3);
    }

    public function load_customizer_js_pro($customizer_module_id,$js_params,$template_type){
        $is_pro_customizer = apply_filters('wt_pklist_pro_customizer_'.$template_type,false,$template_type);
        $doc_arr = array('invoice','packinglist','creditnote');
        if($is_pro_customizer && in_array($template_type,$doc_arr)){
            wp_enqueue_script($customizer_module_id.'-code_editor-js',WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'admin/modules/customizer/libraries/code_editor/lib/codemirror.js',array('jquery'),WT_PKLIST_INVOICE_ADDON_VERSION);
            wp_enqueue_script($customizer_module_id.'-code_editor-mode-htmlmixed',WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'admin/modules/customizer/libraries/code_editor/mode/htmlmixed/htmlmixed.js',array('jquery'),WT_PKLIST_INVOICE_ADDON_VERSION);
            wp_enqueue_script($customizer_module_id.'-code_editor-mode-css',WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'admin/modules/customizer/libraries/code_editor/mode/css/css.js',array('jquery'),WT_PKLIST_INVOICE_ADDON_VERSION);
            
            wp_enqueue_style($customizer_module_id.'-code_editor-css', WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'admin/modules/customizer/libraries/code_editor/lib/codemirror.css', array(),WT_PKLIST_INVOICE_ADDON_VERSION,'all');

            wp_enqueue_script($customizer_module_id,WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'admin/modules/customizer/assets/js/customize.js',array('jquery'),WT_PKLIST_INVOICE_ADDON_VERSION);
            $js_params['enable_code_view'] = true;
            $js_params['msgs']['error'] = __('Error','wt_woocommerce_invoice_addon');
            wp_localize_script($customizer_module_id,$customizer_module_id,$js_params);
        }
    }

    /**
    *   This checking is useful when attaching same document in different email in same time.
    *   @since 1.0.0
    */
    public static function is_pdf_generated($generated_list, $current_pdf_name)
    {
        $pdf_path=false;
        foreach ($generated_list as $generated_pdf_path)
        {
            if(basename($generated_pdf_path)==$current_pdf_name.'.pdf')
            {
                $pdf_path=$generated_pdf_path;
                break;
            }
        }
        return $pdf_path;
    }

    /**
     *  Prepare template placholder for custom meta added by user
     * 
     */
    public static function prepare_custom_meta_placeholder($meta_key)
    {
        return '[wfte_'.self::$extra_field_slug_prefix.$meta_key.']';
    }

    private static function set_extra_fields_for_customize(&$find_replace, $template_type, $html)
    {
        $extra_fields=self::get_all_user_created_fields();

        foreach($extra_fields as $extra_field_key => $value)
        {
            $placeholder=self::prepare_custom_meta_placeholder($extra_field_key);
            $find_replace[$placeholder]=$extra_field_key;
        }
    } 

    /**
     *  Get all user created meta fields, including custom checkout fields
     * 
     */
    public static function get_all_user_created_fields()
    {
        $user_created_fields=self::get_user_created_meta_fields();

        //additional checkout fields
        $additional_checkout=Wf_Woocommerce_Packing_List::get_option('wf_additional_checkout_data_fields');
        
        /* if it is a numeric array convert it to associative.[Bug fix 4.0.1]    */
        $additional_checkout=Wf_Woocommerce_Packing_List_Pro_Common_Func::process_checkout_fields($additional_checkout);

        return array_merge($additional_checkout, $user_created_fields);
    }

    public static function get_user_created_meta_fields()
    {
        $user_created_fields=Wf_Woocommerce_Packing_List::get_option('wf_additional_data_fields'); //this is plugin main setting so no need to specify module id
        return is_array($user_created_fields) ? $user_created_fields : array();
    }

    public static function get_template_html_attr_vl($html,$attr,$default='')
    {
        $match_arr=array();
        $out=$default;
        if(preg_match('/'.$attr.'="(.*?)"/s',$html,$match_arr))
        {
            $out=$match_arr[1];
            $out=($out=='' ? $default : $out);
        }
        return $out;
    }

    public function dummy_data_for_customize($find_replace, $template_type, $html){
        $wf_admin_img_path=WF_PKLIST_PLUGIN_URL . 'admin/images';
        $module_id=Wf_Woocommerce_Packing_List::get_module_id($template_type);
        // self::set_extra_fields_for_customize($find_replace, $template_type, $html);

        $find_replace['[wfte_invoice_number]']=123456;
        $find_replace['[wfte_order_number]']=123456;
        $find_replace['[wfte_customer_note]']=__('Mauris dignissim neque ut sapien vulputate, eu semper tellus porttitor. Cras porta lectus id augue interdum egestas.','wt_woocommerce_invoice_addon');

        $order_date_format=self::get_template_html_attr_vl($html,'data-order_date-format','m/d/Y');
        $find_replace['[wfte_order_date]']=date($order_date_format);

        $invoice_date_format=self::get_template_html_attr_vl($html,'data-invoice_date-format','m/d/Y');
        $find_replace['[wfte_invoice_date]']=date($invoice_date_format);

        $dispatch_date_format=self::get_template_html_attr_vl($html,'data-dispatch_date-format','m/d/Y');
        $find_replace['[wfte_dispatch_date]']=date($dispatch_date_format);

        $creditnote_date_format=self::get_template_html_attr_vl($html,'data-creditnote_date-format','m/d/Y');
        $find_replace['[wfte_creditnote_date]']=date($creditnote_date_format);

        //Dummy billing addresss
        $find_replace['[wfte_billing_address]']='Billing address name <br>20 Maple Avenue <br>San Pedro, California, 90731 <br>United States (US) <br>';

        /* for template with sub placeholders - Billing address */
        $find_replace['[wfte_billing_address_name]']='Mark';
        $find_replace['[wfte_billing_address_company]']='Webtoffee';
        $find_replace['[wfte_billing_address_address_1]']='20 Maple Avenue';
        $find_replace['[wfte_billing_address_address_2]']='';
        $find_replace['[wfte_billing_address_city]']='San Pedro';
        $find_replace['[wfte_billing_address_state]']='California';
        $find_replace['[wfte_billing_address_postcode]']='90731';
        $find_replace['[wfte_billing_address_country]']='United States (US)';

        //Dummy shipping addresss
        $find_replace['[wfte_shipping_address]']='Shipping address name <br>20 Maple Avenue <br>San Pedro, California, 90731 <br>United States (US) <br>';

        /* for template with sub placeholders - Shipping address */
        $find_replace['[wfte_shipping_address_name]']='Mark';
        $find_replace['[wfte_shipping_address_company]']='Webtoffee';
        $find_replace['[wfte_shipping_address_address_1]']='20 Maple Avenue';
        $find_replace['[wfte_shipping_address_address_2]']='';
        $find_replace['[wfte_shipping_address_city]']='San Pedro';
        $find_replace['[wfte_shipping_address_state]']='California';
        $find_replace['[wfte_shipping_address_postcode]']='90731';
        $find_replace['[wfte_shipping_address_country]']='United States (US)';
        $find_replace['[wfte_shipping_address_phone]']='Phone:+1 123 456';

        //Dummy shipping addresss
        $find_replace['[wfte_return_address]']='Return address name <br>20 Maple Avenue <br>San Pedro, California, 90731 <br>United States (US) <br>';

        /* for template with sub placeholders - Return address */
        $find_replace['[wfte_return_address_name]']='Mark';
        $find_replace['[wfte_return_address_company]']='Webtoffee';
        $find_replace['[wfte_return_address_address_1]']='20 Maple Avenue';
        $find_replace['[wfte_return_address_address_2]']='';
        $find_replace['[wfte_return_address_city]']='San Pedro';
        $find_replace['[wfte_return_address_state]']='California';
        $find_replace['[wfte_return_address_postcode]']='90731';
        $find_replace['[wfte_return_address_country]']='United States (US)';

        $find_replace['[wfte_vat_number]']='123456';
        $find_replace['[wfte_eu_vat_number]']='123456';
        $find_replace['[wfte_vat]']='123456';
        $find_replace['[wfte_ssn_number]']='SSN123456';
        $find_replace['[wfte_email]']='info@example.com';
        $find_replace['[wfte_tel]']='+1 123 456';
        $find_replace['[wfte_shipping_method]']='DHL';
        $find_replace['[wfte_tracking_number]']='123456';
        $find_replace['[wfte_order_item_meta]']='';
        $find_replace['[wfte_extra_fields]']='';
        $find_replace['[wfte_product_table_tax_item_column_label]']='<span style="color:#aaa; font-style:italic;">'.__('Tax items', 'wt_woocommerce_invoice_addon').'</span>';
        $find_replace['[wfte_product_table_subtotal]']='$100.00';
        $find_replace['[wfte_product_table_shipping]']='$0.00';
        $find_replace['[wfte_product_table_cart_discount]']='$0.00';
        $find_replace['[wfte_product_table_order_discount]']='$0.00';
        $find_replace['[wfte_product_table_total_tax]']='$0.00';
        $find_replace['[wfte_product_table_fee]']='$0.00';
        $find_replace['[wfte_product_table_payment_method]']='PayPal';
        $find_replace['[wfte_product_table_payment_total]']='$100.00';
        $find_replace['[wfte_product_table_coupon]']='{ABCD100}';
        $find_replace['[wfte_product_table_tax_item]']='$1.00';
        $find_replace['[wfte_product_table_tax_item_label]']=__('Tax items', 'wt_woocommerce_invoice_addon');

        $find_replace['[wfte_barcode_url]']='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEYAAAAeAQMAAACrPfpdAAAABlBMVEX///8AAABVwtN+AAAAAXRSTlMAQObYZgAAABdJREFUGJVj+MzDfPg8P/NnG4ZRFgEWAHrncvdCJcw9AAAAAElFTkSuQmCC';

        $find_replace['[wfte_return_policy]']=__('Mauris dignissim neque ut sapien vulputate, eu semper tellus porttitor. Cras porta lectus id augue interdum egestas. Suspendisse potenti. Phasellus mollis porttitor enim sit amet fringilla. Nulla sed ligula venenatis, rutrum lectus vel','wt_woocommerce_invoice_addon');

        $footer_content = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_footer',$module_id);
        if(trim($footer_content) == ""){
            $footer_content = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_packinglist_footer');
            if($footer_content == ""){
                $footer_content = __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc nec vehicula purus. Mauris tempor nec ipsum ac tempus. Aenean vehicula porttitor tortor, et interdum tellus fermentum at. Fusce pellentesque justo rhoncus','wt_woocommerce_invoice_addon');
            }
        }
        $find_replace['[wfte_footer]'] = $footer_content;
        $find_replace['[wfte_special_notes]']= __('Special notes: consectetur adipiscing elit. Nunc nec vehicula purus.','wf_woocommerce_packing_list');
        $find_replace['[wfte_transport_terms]']=__('Transport Terms: Nunc nec vehicula purus. Mauris tempor nec ipsum ac tempus.','wt_woocommerce_invoice_addon');
        $find_replace['[wfte_sale_terms]']=__('Sale terms: et interdum tellus fermentum at. Fusce pellentesque justo rhoncus','wt_woocommerce_invoice_addon');

        //on package type documents
        $find_replace['[wfte_box_name]']=__('Small','wt_woocommerce_invoice_addon');
        $find_replace['[wfte_qr_code]']='';
        $find_replace['[wfte_total_in_words]']=self::convert_number_to_words(100);
        $find_replace['[wfte_printed_on]']=Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_printed_on_date($html);

        $find_replace['[wfte_payment_link]'] = '#';
        $find_replace['[wfte_package_no]'] = __('Package 1 of 1','wt_woocommerce_invoice_addon');
        $find_replace['[wfte_total_no_of_items]'] = 1;
        $find_replace=apply_filters('wf_pklist_alter_dummy_data_for_customize_pro',$find_replace,$template_type,$html);

        $tax_items_match=array();
        if(preg_match('/<[^>]*data-row-type\s*=\s*"[^"]*\bwfte_tax_items\b[^"]*"[^>]*>(.*?)<\/tr>/s',$html,$tax_items_match))
        {
            $find_replace[$tax_items_match[0]]='';
        }
        return $find_replace;
    }  

    /**
    *   Convert number to words
    *   @author hunkriyaz <Github>
    *   @since 1.0.0
    *
    */
    public static function convert_number_to_words($number)
    {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );
        if (!is_numeric($number)) {
            return false;
        }
        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            /* 
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            ); */
            return false;
        }
        if ($number < 0) {
            return $negative . self::convert_number_to_words(abs($number));
        }
        $string = $fraction = null;
        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }
        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . self::convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = self::convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= self::convert_number_to_words($remainder);
                }
                break;
        }
        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }
        return $string;
    }

    private function convert_translation_strings($match)
    {
        return is_array($match) && isset($match[1]) && trim($match[1])!="" ? __($match[1],'wt_woocommerce_invoice_addon') : '';
    }

    public function toggle_rtl($html)
    {
        $rtl_support=Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_add_rtl_support');
        if("Yes" === $rtl_support)
        {   
            if(!Wf_Woocommerce_Packing_List_Admin::is_enable_rtl_support()) /* checks the current language need RTL support */
            {
                return $html;
            }

            $html=str_replace('wfte_rtl_main', 'wfte_rtl_main wfte_rtl_template_main', $html);
            if(true === $this->template_for_pdf)
            {
                /* some PDF libraries does not needed to reverse the HTML column. */
                $is_reverse_column=true;
                $is_reverse_column=apply_filters('wf_pklist_enable_product_table_columns_reverse', $is_reverse_column);

                if($is_reverse_column)
                {
                    add_filter('wf_pklist_reverse_product_table_columns', function($columns_list_arr,$template_type){
                        return array_reverse($columns_list_arr,true);
                    },10,2);
                }

                //this for checking where to add last table column CSS class, In case of `RTL PDF table` the last column CSS class must add to first column
                add_filter('wf_pklist_is_rtl_for_pdf', '__return_true', 1); /* priority 1 so external addons can override */

                //reverse product summary columns
                if($is_reverse_column)
                {
                    $html=$this->reverse_product_summary_columns($html);
                }
                $this->custom_css.='
                .wfte_invoice_data{ padding-top:1px !important;}
                .wfte_invoice_data div{ text-align:left !important; padding-left:10px !important;}';
            }
            
            $this->custom_css.='
            body, html{direction:rtl; unicode-bidi:bidi-override; }
            .wfte_rtl_main .float_left{ float:right; }
            .wfte_rtl_main .float_right{ float:left; }
            .wfte_rtl_main .float_left{ float:right; }
            .wfte_rtl_main .wfte_text_right{ text-align:left !important; }  
            .wfte_rtl_main .wfte_text_left{ text-align:right !important; }
            .wfte_invoice_data div span:nth-child(1){  float:right !important;} 
            .wfte_order_data div span:nth-child(1){ float:right !important;} 
            .wfte_list_view div span:nth-child(1){ float:right !important;} 
            .wfte_extra_fields span:nth-child(1){ float:right !important;}';
        }
        return $html;
    }

    public function generate_template_html_for_creditnote($html,$template_type,$order,$refund_order,$refund_id,$box_packing=null,$order_package=null)
    {
        //convert translation html 
        $html=preg_replace_callback('/__\[(.*?)\]__/s', array($this, 'convert_translation_strings'),$html);
        
        //customizer functions
        // include_once plugin_dir_path(__FILE__)."classes/class-customizer.php";
        if(false === $this->rtl_css_added)
        {
            $html=$this->toggle_rtl($html); //this method uses function in above included file
            $this->rtl_css_added=true;
        }

        $module_id=Wf_Woocommerce_Packing_List::get_module_id($template_type);
        
        $category_wise_split=Wf_Woocommerce_Packing_List::get_option('wf_woocommerce_product_category_wise_splitting', $module_id);
        $bundle_display_option=Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::get_bundle_display_option($template_type, $order);

        /**
        *   @since 1.0.0
        *   Custom CSS for bundle products 
        */
        if($bundle_display_option && "No" === $category_wise_split)
        {
            $this->custom_css.='tr.wfte_product_row_bundle_child .product_td{ padding-left:20px !important; }';
        }
        
        

        $find_replace=array();
        $find_replace=Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_logo($find_replace,$template_type,$order);
        $find_replace=Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::set_shipping_from_address($find_replace, $template_type, $html, $order);

        /* this filter will add other datas */
        $find_replace=apply_filters('wf_module_generate_template_html_for_creditnote',$find_replace,$html,$template_type,$order,$refund_order,$refund_id,$box_packing,$order_package);
        
        /**
        *   @since 1.0.0
        *   Add values to placeholders that are not available in the doc type module
        */
        $find_replace=Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::add_missing_placeholders($find_replace, $template_type, $html, $order);



        $html=apply_filters('wt_pklist_alter_order_template_html', $html, $template_type, $order, $box_packing, $order_package, $this->template_for_pdf);

        $html = Wf_Woocommerce_Packing_List_Admin::hide_empty_shipping_address($html,$template_type,$order);
        //*******the main hook to alter everything in the template *******//
        $find_replace=apply_filters('wf_pklist_alter_find_replace',$find_replace,$template_type,$order,$box_packing,$order_package, $html);

        $html=Wf_Woocommerce_Packing_List_Customizer_Ipc_PRO::hide_empty_elements_for_creditnote($find_replace,$html,$template_type);
        
        $html=$this->replace_placeholders($find_replace, $html, $template_type);

        return apply_filters('wt_pklist_alter_final_order_template_html', $html, $template_type, $order, $box_packing, $order_package, $this->template_for_pdf);
    }

    public function replace_placeholders($find_replace,$html,$template_type)
    {
        $find=array_keys($find_replace);
        $replace=array_values($find_replace);
        $html=str_replace($find,$replace,$html);
        return $html;
    }
    
    /**
	*
	* DomPDF will not revrese the table columns in RTL so we need to do it manually
	* @param $html template HTML
	* @return $html formatted template HTML
	*/
	protected function reverse_product_summary_columns($html)
	{
		$table_html_arr=array();
		$table_html=Wf_Woocommerce_Packing_List_CustomizerLib::getElmByClass('wfte_payment_summary_table',$html);
		if($table_html)
		{
			$table_arr=array();
			if(preg_match('/'.$table_html[0].'(.*?)<\/table>/s',$html,$table_arr))
			{ 
				$tbody_arr=array();
				if(preg_match('/<tbody(.*?)>/s',$table_arr[0],$tbody_arr)) //tbody exists
				{
					$table_html_arr[]=$tbody_arr[0];
				}
				$tr_arr=array();
				if(preg_match_all('/<tr(.*?)>(.*?)<\/tr>/s',$table_arr[0],$tr_arr)) //tr exists
				{ 
					foreach ($tr_arr[0] as $tr_k=>$tr_html) 
					{
						$td_arr=array();
						preg_match_all('/<td(.*?)>(.*?)<\/td>/s',$tr_html,$td_arr);
						$td_html_arr=array_reverse($td_arr[0]);
						$table_html_arr[]='<tr'.$tr_arr[1][$tr_k].'>'.implode("\n",$td_html_arr).'</tr>';
					}
				}
				if(count($tbody_arr)>0) //tbody exists
				{
					$table_html_arr[]='</tbody>';
				}
				$formatted_table_html=implode("",$table_html_arr);
				$html=str_replace($table_arr[1],$formatted_table_html,$html);
			}
		}
		return $html;
	}
}
new Wf_Woocommerce_Packing_List_Customizer_Ipc();
}