var wt_pklist_licence=(function( $ ) {
	'use strict';
	var wt_pklist_licence=
	{
		status_checked:false,
		Set:function()
		{
			this.list_data();
			this.activation();
		},
		check_status:function()
		{
			if($('.wt-pklist-tab-content[data-id="wt-licence"]').is(':visible'))
			{
				wt_pklist_licence.do_status_check();
			}
			$('.wf-tab-head .nav-tab[href="#wt-licence"]').on('click',function(){
				if(false === wt_pklist_licence.status_checked)
				{
					wt_pklist_licence.do_status_check();
				}
			});
		},
		do_status_check:function()
		{
			wt_pklist_licence.status_checked=true;
			if(0 === $('.wt_pklist_licence_table tbody .licence_tr').length)
			{
				return false;
			}

			$('.wt_pklist_licence_table .status_td, .wt_pklist_licence_table .action_td').html('...');
			
			$.ajax({
				url:wt_pklist_licence_params.ajax_url,
				data:{'action': 'wt_pklist_licence_manager_ajax', 'wt_pklist_licence_manager_action': 'check_status', '_wpnonce':wt_pklist_licence_params.nonce},
				type:'post',
				dataType:"json",
				success:function(data)
				{
					wt_pklist_licence.list_data();
				},
				error:function()
				{
					wt_pklist_licence.list_data();
				}
			});
		},
		update_status_tab_icon:function()
		{			
			if($('.wt_pklist_licence_table .status_td').length>0)
			{
				var status=true;
			}else
			{
				var status=false;
			}

			if(status)
			{
				$('[name="wt_pklist_licence_product"] option').each(function(){
					var vl=$(this).val();
					var licence_tr=$('.wt_pklist_licence_table .licence_tr[data-product="'+vl+'"]');
					if(0 === licence_tr.length)
					{
						status=false;
					}
				});
			}

			if(status)
			{
				$('.wt_pklist_licence_table .status_td').each(function(){
					var st=$(this).attr('data-status');
					if("inactive" === st || "" === st)
					{
						status=false;
					}
				});
			}

			var tab_icon_elm=$('.wf-tab-head .nav-tab[href="#wt-licence"] .dashicons')
			if(status)
			{
				tab_icon_elm.replaceWith(wt_pklist_licence_params.tab_icons['active']);
			}else
			{
				tab_icon_elm.replaceWith(wt_pklist_licence_params.tab_icons['inactive']);	
			}
		},
		list_data:function()
		{
			$.ajax({
				url:wt_pklist_licence_params.ajax_url,
				data:{'action': 'wt_pklist_licence_manager_ajax', 'wt_pklist_licence_manager_action': 'licence_list', '_wpnonce':wt_pklist_licence_params.nonce},
				type:'post',
				dataType:"json",
				success:function(data)
				{
					if(true === data.status)
					{
						$('.wt_pklist_licence_list_container').html(data.html);
						wt_pklist_licence.update_status_tab_icon();
						wt_pklist_licence.deactivation();
						if(false === wt_pklist_licence.status_checked)
						{
							wt_pklist_licence.check_status();
						}
					}else
					{
						wf_notify_msg.error(wt_pklist_licence_params.msgs.unable_to_fetch);
					}
				},
				error:function()
				{
					wf_notify_msg.error(wt_pklist_licence_params.msgs.unable_to_fetch);
				}
			});
		},
		deactivation:function()
		{
			$('.wt_pklist_licence_deactivate_btn').on('click',function(){
				if(confirm(wt_pklist_licence_params.msgs.sure))
				{
					wt_pklist_licence.do_deactivate($(this));
				}
			});
		},
		do_deactivate:function(btn)
		{
			var btn_txt_back=btn.html();
			btn.html(wt_pklist_licence_params.msgs.please_wait).prop('disabled', true);
			var product=btn.attr('data-product');
			var action=btn.attr('data-action');
			$.ajax({
				url:wt_pklist_licence_params.ajax_url,
				data:{'action': 'wt_pklist_licence_manager_ajax', 'wt_pklist_licence_manager_action': action, '_wpnonce':wt_pklist_licence_params.nonce, 'wt_pklist_licence_product':product},
				type:'post',
				dataType:"json",
				success:function(data)
				{
					if(true === data.status)
					{	
						wf_notify_msg.success(data.msg);
						if(btn.parents('tbody').find('tr').length>1)
						{
							btn.parents('tr').remove();
						}else
						{
							wt_pklist_licence.list_data();
						}
					}else
					{
						btn.html(btn_txt_back).prop('disabled', false);
						wf_notify_msg.error(wt_pklist_licence_params.msgs.error);
					}
				},
				error:function()
				{
					btn.html(btn_txt_back).prop('disabled', false);
					wf_notify_msg.error(wt_pklist_licence_params.msgs.error);
				}
			});
		},
		activation:function()
		{
			$('#wt_pklist_licence_manager_form').submit(function(e){
				e.preventDefault();
				var this_form=$(this);
				var licence_key=this_form.find('[name="wt_pklist_licence_key"]').val().trim();
				var licence_product=this_form.find('[name="wt_pklist_licence_product"]').val().trim();
				if("" === licence_product)
				{
					wf_notify_msg.error(wt_pklist_licence_params.msgs.product_mandatory);
					return false;
				}
				if("" === licence_key)
				{
					wf_notify_msg.error(wt_pklist_licence_params.msgs.key_mandatory);
					return false;
				}
				var btn=this_form.find('.wt_pklist_licence_activate_btn');
				var btn_txt_back=btn.html();
				btn.html(wt_pklist_licence_params.msgs.please_wait).prop('disabled', true);
				$.ajax({
					url:wt_pklist_licence_params.ajax_url,
					data:this_form.serialize(),
					type:'post',
					dataType:"json",
					success:function(data)
					{
						btn.html(btn_txt_back).prop('disabled', false);
						if(true === data.status)
						{
							this_form[0].reset();
							wf_notify_msg.success(data.msg);
							wt_pklist_licence.list_data();
						}else
						{
							wf_notify_msg.error(data.msg);
						}
					},
					error:function()
					{
						btn.html(btn_txt_back).prop('disabled', false);
						wf_notify_msg.error(wt_pklist_licence_params.msgs.error);
					}
				});
			});
		}
	}
	return wt_pklist_licence;
	
})( jQuery );

jQuery(function() {			
	wt_pklist_licence.Set();
});