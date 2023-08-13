<?php
if (!defined('ABSPATH')) {
    exit;
}
$arr = (isset($arr) && is_array($arr)) ? $arr : array();
$new_arr=array(
	'wf_woocommerce_invoice_show_print_button' => __("Enabling this option includes a button to print the packing slip from the order lists page or order details page","wt_woocommerce_shippinglabel_addon"),
	'wf_woocommerce_product_category_wise_splitting'=>__("Groups products falling under same category in the packing slip, thus making packing easier","wt_woocommerce_invoice_addon"),
	'woocommerce_wf_packinglist_variation_data'=>__("Enable to include variation data in the product table of the packing slip.","wt_woocommerce_invoice_addon"),
	'wf_packinglist_contactno_email'=>__("Include custom order meta by clicking on the Add/ Edit order meta field. Key in the field name and meta key to include an order meta. Previously added tab lists the order meta fields added in the past.","wt_woocommerce_invoice_addon"),
	'wf_packinglist_product_meta_fields'=>__("Include custom product meta by clicking on the Add/ Edit order meta field. Key in the field name and meta key to include a product meta. Previously added tab lists the product meta fields added in the past.","wt_woocommerce_invoice_addon"),
	'wt_packinglist_product_attribute_fields'=>__("Include custom product attributes by clicking on the Add/ Edit order meta field. Key in the field name and meta key to include a product attribute. Previously added tab lists the product attributes added in the past.","wt_woocommerce_invoice_addon"),
	'woocommerce_wf_packinglist_footer'=>__("Overrides the common footer content specified in the General settings and allows inserting content that is specific only to packing slips.","wt_woocommerce_invoice_addon"),
);
$arr = array_merge($new_arr,$arr);