<?php
if (!defined('ABSPATH')) {
    exit;
}
$arr = (isset($arr) && is_array($arr)) ? $arr : array();
$new_arr=array(
	"woocommerce_wf_enable_payment_link_in_invoice" => __("Adds a payment link besides the payment method in the invoice. Ensure to choose a template from the ‘Customize’ tab that supports the payment link.","wt_woocommerce_invoice_addon"),
	"sort_products_by" => __("Sort products in ascending/descending order based on Name or SKU","wt_woocommerce_invoice_addon"),
	'wf_'.$this->module_base.'_contactno_email' => __("Include custom order meta by clicking on the Add/ Edit order meta field. Key in the field name and meta key to include an order meta. Previously added tab lists the order meta fields added in the past.","wt_woocommerce_invoice_addon"),
	'wf_'.$this->module_base.'_product_meta_fields' => __("Include custom product meta by clicking on the Add/ Edit order meta field. Key in the field name and meta key to include a product meta. Previously added tab lists the product meta fields added in the past.","wt_woocommerce_invoice_addon"),
	'wt_'.$this->module_base.'_product_attribute_fields' => __("Include custom product attributes by clicking on the Add/ Edit order meta field. Key in the field name and meta key to include a product attribute. Previously added tab lists the product attributes added in the past.","wt_woocommerce_invoice_addon"),
	"woocommerce_wf_packinglist_logo" => __("Overrides the common logo set in the General settings and allows inserting a unique logo in the invoice.","wt_woocommerce_invoice_addon"),
	"woocommerce_wf_packinglist_footer" => __("Overrides the common footer content specified in the General settings and allows inserting content that is specific only to invoices.","wt_woocommerce_invoice_addon")
);
$arr = array_merge($new_arr,$arr);