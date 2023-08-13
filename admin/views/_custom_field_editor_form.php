<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wt_pklist_custom_field_form" style="display:none;">
	<div class="wt_pklist_checkout_field_tab">
		<div class="wt_pklist_custom_field_tab_head active_tab" data-target="add_new" data-add-title="<?php _e('Add new', 'wt_woocommerce_invoice_addon');?>" data-edit-title="<?php _e('Edit','wt_woocommerce_invoice_addon');?>">
			<span class="wt_pklist_custom_field_tab_head_title"> <?php _e('Add new', 'wt_woocommerce_invoice_addon');?></span>
			<div class="wt_pklist_custom_field_tab_head_patch"></div>
		</div>
		<div class="wt_pklist_custom_field_tab_head" data-target="list_view">
			<?php _e('Previously added', 'wt_woocommerce_invoice_addon');?>
			<div class="wt_pklist_custom_field_tab_head_patch"></div>		
		</div>
		<div class="wt_pklist_custom_field_tab_head_add_new" title="<?php _e('Add new', 'wt_woocommerce_invoice_addon');?>">
			<span class="dashicons dashicons-plus"></span>
		</div>
	</div>
	<div class="wt_pklist_custom_field_tab_content active_tab" data-id="add_new">
    	<div class='wt_pklist_custom_field_tab_form_row wt_pklist_custom_field_form_notice'>
    		<?php _e('You can edit an existing item by using its key.', 'wt_woocommerce_invoice_addon');?>
    	</div>
    	<div class='wt_pklist_custom_field_tab_form_row'>
    		<table>
    			<tr>
    				<td style="width:30%;"><?php _e('Field Name', 'wt_woocommerce_invoice_addon'); ?><i style="color:red;">*</i></td>
    				<td style="width:50%;"><input type='text' name='wt_pklist_new_custom_field_title' data-required="1" style='width:100%'/></td>
    			</tr>
    			<tr>
    				<td style="width:30%;"><?php _e('Meta Key', 'wt_woocommerce_invoice_addon'); ?></td>
    				<td style="width:50%;"><input type='text' name='wt_pklist_new_custom_field_key' data-required="1" style='width:100%'/></td>
    			</tr>
    		</table>
		</div>
		<div class='wt_pklist_custom_field_tab_form_row' style="height:25px;">

		</div>
	</div>
	<div class="wt_pklist_custom_field_tab_content" data-id="list_view" style="height:155px; overflow:auto;">
		
	</div>
</div>