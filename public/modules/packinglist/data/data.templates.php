<?php
$template_arr_pro=array(
	array(
		'id'=>'template_pro_1',
		'title'=>__('PRO - 1', 'wt_woocommerce_invoice_addon'),
		'preview_img'=>'template1.png',
		'version' => 'pro',
		'pro_template_path' => plugin_dir_path(__FILE__),
		'pro_template_url' => WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'public/modules/packinglist/data/',
	),
	array(
		'id'=>'template_pro_2',
		'title'=>__('PRO - 2', 'wt_woocommerce_invoice_addon'),
		'preview_img'=>'template2.png',
		'version' => 'pro',
		'pro_template_path' => plugin_dir_path(__FILE__),
		'pro_template_url' => WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'public/modules/packinglist/data/',
	),
	array(
		'id'=>'template_pro_3',
		'title'=>__('PRO - 3', 'wt_woocommerce_invoice_addon'),
		'preview_img'=>'template3.png',
		'version' => 'pro',
		'pro_template_path' => plugin_dir_path(__FILE__),
		'pro_template_url' => WT_PKLIST_INVOICE_ADDON_PLUGIN_URL.'public/modules/packinglist/data/',
	),
);

$template_arr = array_merge($template_arr_pro, $template_arr);