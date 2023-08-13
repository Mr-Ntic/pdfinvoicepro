<?php
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Wf_Woocommerce_Packing_List_Template_Load')){
class Wf_Woocommerce_Packing_List_Template_Load
{
    public static function load_template_element_attributes($template_type){
        $result = array(
            'tax_type'      => 'excl_type',
            'discount_type' => 'before_discount',
            'p_meta'        => '',
            'p_attr'        => '',
            'oi_meta'       => '',
        );

        if("" === trim($template_type)){
            return $result;
        }

        $l_module_id = Wf_Woocommerce_Packing_List::get_module_id($template_type);
        $tax_type_settings = Wf_Woocommerce_Packing_List::get_option('woocommerce_wf_generate_for_taxstatus');
        $result['tax_type'] = in_array('in_tax', $tax_type_settings) ? 'incl_tax' : 'excl_tax';

        $all_p_meta = Wf_Woocommerce_Packing_List::get_option('wf_product_meta_fields');
        $l_p_meta = Wf_Woocommerce_Packing_List::get_option('wf_'.$template_type.'_product_meta_fields',$l_module_id);
        if(!empty($all_p_meta) && !empty($l_p_meta)){
            if(is_array($l_p_meta)){
                $result['p_meta'] = implode('|',$l_p_meta);
            }
        }

        $all_p_attr = Wf_Woocommerce_Packing_List::get_option('wt_product_attribute_fields');
        $l_p_attr = Wf_Woocommerce_Packing_List::get_option('wt_'.$template_type.'_product_attribute_fields',$l_module_id);
        if(!empty($all_p_attr) && !empty($l_p_attr)){
            if(is_array($l_p_attr)){
                $result['p_attr'] = implode('|',$l_p_attr);
            }
        }

        $all_oi_meta = Wf_Woocommerce_Packing_List::get_option('wt_order_item_meta_fields');
        $l_oi_meta = Wf_Woocommerce_Packing_List::get_option('wt_'.$template_type.'_order_item_meta_fields',$l_module_id);
        if(!empty($all_oi_meta) && !empty($l_oi_meta)){
            if(is_array($l_oi_meta)){
                $result['oi_meta'] = implode('|',$l_oi_meta);
            }
        }

        return $result;
    }
}
}
?>