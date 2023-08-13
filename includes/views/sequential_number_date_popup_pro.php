<?php
if (!defined('ABSPATH')) {
    exit;
}
$date_frmt_tooltip=__('Click to append with existing data','wt_woocommerce_invoice_addon');
?>
<style type="text/css">
.wf_inv_num_frmt_hlp_btn{ cursor:pointer; }
.wf_inv_num_frmt_hlp table thead th{ font-weight:bold; text-align:left; }
.wf_inv_num_frmt_hlp table tbody td{ text-align:left; }
.wf_inv_num_frmt_hlp .wf_pklist_popup_body{min-width:300px; padding:20px;}
.wf_inv_num_frmt_append_btn{ cursor:pointer; }
</style>
<!-- Invoice number Prefix/Suffix help popup -->
<div class="wf_inv_num_frmt_hlp wf_pklist_popup">
	<div class="wf_pklist_popup_hd">
		<span style="line-height:40px;" class="dashicons dashicons-calendar-alt"></span> <?php _e('Date formats','wt_woocommerce_invoice_addon');?>
		<div class="wf_pklist_popup_close">X</div>
	</div>
	<div class="wf_pklist_popup_body">
		
		<p style="text-align:left; max-width:400px; margin-top:0px;">
			<?php _e("By default the arguments will consider the document date(date on which the document invoice, shipping label etc is generated) as the input. ",'wt_woocommerce_invoice_addon'); ?>
			<br />
			<br />
			<input type="checkbox" name="wf_inv_num_frmt_data_val" id="wf_inv_num_frmt_order_date" value="order_date"> <label for="wf_inv_num_frmt_order_date"><?php _e('Use order date as input instead.','wt_woocommerce_invoice_addon');?></label>
			<span class="wf_form_help" style="margin-top:2px;"><?php _e('Enable this if you want to use the order date as input for the below arguments.', 'wt_woocommerce_invoice_addon');?></span> 
		</p>

		<p style="text-align:left; margin-bottom:2px;">
			<?php _e('Select from any of the formats below:','wt_woocommerce_invoice_addon');?>
		</p>
		<table class="wp-list-table widefat striped">
			<thead>
				<tr>
					<th><?php _e('Format','wt_woocommerce_invoice_addon');?></th><th><?php _e('Output','wt_woocommerce_invoice_addon');?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[F]</a></td>
					<td><?php echo date('F'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[dS]</a></td>
					<td><?php echo date('dS'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[M]</a></td>
					<td><?php echo date('M'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[m]</a></td>
					<td><?php echo date('m'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[d]</a></td>
					<td><?php echo date('d'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[D]</a></td>
					<td><?php echo date('D'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[y]</a></td>
					<td><?php echo date('y'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[Y]</a></td>
					<td><?php echo date('Y'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[d/m/y]</a></td>
					<td><?php echo date('d/m/y'); ?></td>
				</tr>
				<tr>
					<td><a class="wf_inv_num_frmt_append_btn" title="<?php echo $date_frmt_tooltip; ?>">[d-m-Y]</a></td>
					<td><?php echo date('d-m-Y'); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>