<?php
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Wf_Woocommerce_Packing_List_Template_Load_Epo_Tm')){
class Wf_Woocommerce_Packing_List_Template_Load_Epo_Tm
{
    /**
     * Compatible with Extra product option (theme complete)
     *
     * @param [array] $meta_data_formated_arr
     * @param [array] $p_meta_source_details
     * @return array
     */
    public static function process_tmcart_meta_below_product_name($meta_data_formated_arr,$p_meta_source_details){
        extract($p_meta_source_details);
        $enable_tmcart_data = "Yes";
        $tmcart_item_id 	= $order_item->get_id();
        $epo_tc_meta_data	= wc_get_order_item_meta($tmcart_item_id, '_tmcartepo_data',true);
        $enable_tmcart_data = apply_filters('wf_pklist_alter_tmcart_data_enable',$enable_tmcart_data,$order_item,$order,$template_type);

        if("Yes" === $enable_tmcart_data && !empty($epo_tc_meta_data)){
            foreach ($epo_tc_meta_data as $key => $epo) 
            {
                if ($epo && is_array($epo)) 
                {
                    $tmcart_option_name 	= wp_kses_post($epo['name']);
                    $tmcart_option_value 	= wp_kses_post($epo['value']);
                    $tmcart_option_qty 		= wp_kses_post($epo['quantity']);
                    $tmcart_option_price 	= Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$epo['price']);

                    $meta_data_formated_arr[] ='<small style="line-height:18px;">'.'<span class="wfte_epo_'.$tmcart_option_name.'">'.$tmcart_option_name .':'.' <span style="white-space: pre-wrap;">'. $tmcart_option_value.'</span><br></span>'. '<span class="wfte_epo_cost"> COST: ' . $tmcart_option_price .'<br></span> <span class="wfte_epo_qty">Qty: '. $tmcart_option_qty .'</span></small>';
                }
            }
        }
        return $meta_data_formated_arr;
    }

    public static function process_tmcart_meta_below_product_name_package_doc($addional_product_meta,$col_val_source_details){
        extract($col_val_source_details);
        $enable_tmcart_data	= "Yes";
        $epo_tc_meta_data	= wc_get_order_item_meta($order_item_id, '_tmcartepo_data',true);
        $enable_tmcart_data = apply_filters('wf_pklist_alter_package_tmcart_data_enable',$enable_tmcart_data,$item,$order,$template_type);

        if("Yes" === $enable_tmcart_data && !empty($epo_tc_meta_data)){
            foreach ($epo_tc_meta_data as $key => $epo) 
            {
                if ($epo && is_array($epo)) 
                {
                    $tmcart_option_name 	= wp_kses_post($epo['name']);
                    $tmcart_option_value 	= wp_kses_post($epo['value']);
                    $tmcart_option_qty 		= wp_kses_post($epo['quantity']);
                    $tmcart_option_price 	= Wf_Woocommerce_Packing_List_Admin::wf_display_price($user_currency,$order,$epo['price']);

                    $meta_data_formated_arr[] 	='<small style="line-height:18px;">'.'<span class="wfte_epo_'.$tmcart_option_name.'">'.$tmcart_option_name .':'.' <span style="white-space: pre-wrap;">'. $tmcart_option_value.'</span><br></span>'. '<span class="wfte_epo_cost"> COST: ' . $tmcart_option_price .'<br></span> <span class="wfte_epo_qty">Qty: '. $tmcart_option_qty .'</span></small>';
                }
            }
            $string_glue 	= '<br>';
            $string_glue 	= apply_filters('wt_pklist_package_epo_product_meta_string_glue', $string_glue, $order, $template_type);
            $addional_product_meta .= implode($string_glue, $meta_data_formated_arr);
        }
        return $addional_product_meta;
    }
}
}