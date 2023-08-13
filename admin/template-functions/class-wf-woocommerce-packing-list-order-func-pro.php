<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
if(!class_exists('Wf_Woocommerce_Packing_List_Order_Func_Pro')){

class Wf_Woocommerce_Packing_List_Order_Func_Pro{

    public static function get_tax_incl_text($template_type, $order, $text_for='total_price')
    {
        $incl_tax_text=__('incl. tax', 'wt_woocommerce_invoice_addon');
        return apply_filters('wf_pklist_alter_tax_inclusive_text', $incl_tax_text, $template_type, $order, $text_for);
    }
    /**
     * To get the unit price depends on the discount type and tax type
     *
     * @param [object] $order_item
     * @param [object] $order
     * @param [boolean] $p_incl_tax
     * @param [string] $discount_type
     * @return int|float
     */
    public static function get_unit_price($order_item,$order,$p_incl_tax,$discount_type){
        if($discount_type === "after_discount"){
            $total_price = $order->get_line_total($order_item, $p_incl_tax, true);
            $unit_price = $total_price / max( 1, abs( $order_item['qty'] ) );
            return $unit_price;
        }else{
            $total_price = $order->get_line_subtotal($order_item, $p_incl_tax, true);
            $unit_price = $total_price / max( 1, abs( $order_item['qty'] ) );
            return $unit_price;
        }
    }

    /**
     * To get the total price value depends on the discount and tax type
     *
     * @param [object] $order_item
     * @param [object] $order
     * @param [boolean] $p_incl_tax
     * @param [string] $discount_type
     * @return void
     */
    public static function get_total_price($order_item,$order,$p_incl_tax,$discount_type,$template_type="",$package_item=array()){
        $show_after_refund = apply_filters('wt_pklist_show_details_after_refund_'.$template_type,false,$template_type);
        $item_qty_refunded = 0;
        if($show_after_refund){
            $item_qty_refunded = $order->get_qty_refunded_for_item( $order_item->get_id() );
        }
        $item_taxes 		= $order_item->get_taxes();
        $item_tax_subtotal	= (isset($item_taxes['subtotal']) ? $item_taxes['subtotal'] : array());
        
        if($discount_type === "after_discount"){
            $total_price = (float)$order->get_line_total($order_item, $p_incl_tax, true);
        }else{
            $total_price = (float)$order->get_line_subtotal($order_item, $p_incl_tax, true);
        }
        if(true === $p_incl_tax){
            $line_discount     = (float)$order->get_line_subtotal($order_item, true, true) - (float)$order->get_line_total($order_item, true, true);
        }else{
            $line_discount     = (float)$order->get_line_subtotal($order_item, false, true) - (float)$order->get_line_total($order_item, false, true);
        }
        
        $unit_coupon_amount = (float)$line_discount/abs($order_item['qty']);
        if($show_after_refund){
            $refunded_total_amount = (float)$order->get_total_refunded_for_item( $order_item->get_id() );
            $refuned_total_tax = 0;
            foreach($item_tax_subtotal as $tax_id => $tax_val)
            {
                $refuned_total_tax += (float)$order->get_tax_refunded_for_item($order_item->get_id(),$tax_id);
            }
            $total_price = (true===$p_incl_tax) ? $total_price - ($refunded_total_amount+$refuned_total_tax) : $total_price - $refunded_total_amount;
            if(abs($item_qty_refunded) > 0 && "before_discount" === $discount_type){
                $total_price = $total_price - $unit_coupon_amount;
            }
        }

        if(isset($package_item['quantity'])){
            $unit_price = $total_price / max( 1, (abs( $order_item['qty'] ) - abs($item_qty_refunded)) );
            $total_price = (float)$package_item['quantity'] * (float)$unit_price;
        }
        return $total_price;
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
}
new Wf_Woocommerce_Packing_List_Order_Func_Pro();
}