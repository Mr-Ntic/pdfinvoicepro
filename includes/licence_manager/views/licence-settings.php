<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<style type="text/css">
.wt_pklist_licence_container{ padding-bottom:20px; }
.wt_pklist_licence_form_table td{ padding-bottom:20px; width:200px; }
.wt_pklist_licence_form_table input[type="text"], .wt_pklist_licence_form_table select{ width:100%; display:block; border:solid 1px #ccd0d4;}
.wt_pklist_licence_form_table label{ width:100%; display:block; font-weight:bold; }
.wt_pklist_licence_table{ margin-bottom:20px; }
.wt_pklist_licence_form_table{ width:auto; }
</style>
<div class="wt-pklist-tab-content wt_pklist_licence_container" data-id="<?php echo $target_id;?>">
	<h3><span><?php _e('Activate new Licence', 'wt_woocommerce_invoice_addon');?></span></h3>
	<form method="post" id="wt_pklist_licence_manager_form">
		<?php
        // Set nonce:
        if (function_exists('wp_nonce_field'))
        {
            wp_nonce_field(WF_PKLIST_POST_TYPE);
        }
        ?>
        <input type="hidden" name="wt_pklist_licence_manager_action" value="activate">
        <input type="hidden" name="action" value="wt_pklist_licence_manager_ajax">
        <table class="wp-list-table widefat fixed striped wt_pklist_licence_form_table">
        	<tr>
				<td style="width:350px;">
					<label><?php _e('Product:', 'wt_woocommerce_invoice_addon');?></label>
					<select name="wt_pklist_licence_product">
						<?php
						if(is_array($products))
						{
							foreach ($products as $product_slug=>$product)
							{
								?>
								<option value="<?php echo $product_slug;?>">
									<?php echo $product['product_display_name'];?>
								</option>
								<?php
							}
						}
						?>
					</select>
				</td>
				<td>
					<label><?php _e('Licence Key:', 'wt_woocommerce_invoice_addon');?></label>
					<input type="text" name="wt_pklist_licence_key">
				</td>
				<td style="display:none;">
					<label><?php _e('Email:', 'wt_woocommerce_invoice_addon');?></label>
					<input type="text" name="wt_pklist_licence_email">
				</td>
				<td style="width:100px;">
					<label>&nbsp;</label>
					<button class="button button-primary wt_pklist_licence_activate_btn"><?php _e('Activate', 'wt_woocommerce_invoice_addon');?></button>
				</td>
			</tr>
        </table>
	</form>
	<h3><span><?php _e('Licence details', 'wt_woocommerce_invoice_addon');?></span></h3>
	<div class="wt_pklist_licence_list_container">
		
	</div>
</div>