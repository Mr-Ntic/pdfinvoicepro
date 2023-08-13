<?php
if (!defined('ABSPATH')) {
    exit;
}
$arr=array(
	'woocommerce_wf_packinglist_companyname'=>__('Key in your company name.','wt_woocommerce_invoice_addon'),
	'woocommerce_wf_packinglist_return_policy'=>__('The norms and rules regarding the return, exchange and associated refund-related procedures of your business can be stated in the expandable text area provided. You can choose to display it or not in your shipping documents from the concerned documents customize tab.','wt_woocommerce_invoice_addon'),
	'wf_invoice_additional_checkout_data_fields'=>__('Lets you add additional text input fields on the checkout page which would be stored along with order details among the custom fields.
	Defaulted to VAT and SSN, you may also include additional custom metadata by specifying concerned field name and meta key in which it would be stored in database. Provisions to set the field mandatory or not is also allowed.','wt_woocommerce_invoice_addon'),
	'woocommerce_wf_packinglist_preview'=>__('Enable to have a preview of the document before the print is initiated','wt_woocommerce_invoice_addon'),
	'woocommerce_wf_tracking_number'=>__('Include a tracking number in your documents by specifying an appropriate tracking number meta(usually from a third-party plugin) in the field provided. ','wt_woocommerce_invoice_addon'),
	'woocommerce_wf_state_code_disable'=>__('When enabled, replaces state code with state name in the from, return, billing and shipping addresses','wt_woocommerce_invoice_addon'),
	'woocommerce_wf_packinglist_package_type'=>__('Choose appropriate option from the drop down either to have all items or individual items of an order in the packing slip/shipping label.','wt_woocommerce_invoice_addon'),
	'active_pdf_library' => __("The default library to be used PDF generation","wt_woocommerce_invoice_addon"),
);
?>