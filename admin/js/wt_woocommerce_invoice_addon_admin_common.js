(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function(){
		/* custom checkout field, Product meta, Product attribute etc  */
		var wt_pklist_custom_field_editor=
		{
			Set:function()
			{
				this.tabview();
				this.reg_delete();
				this.reg_edit();
				this.reg_addnew();
			},
			tabview:function()
			{
				$(document).on('click','.wt_pklist_custom_field_tab_head',function(){
					var trget_id=$(this).attr('data-target');
					$('.wf_popover-content').find('.wt_pklist_custom_field_tab_head').removeClass('active_tab');
					$(this).addClass('active_tab');
					$('.wf_popover-content').find('.wt_pklist_custom_field_tab_content').removeClass('active_tab');
					$('.wf_popover-content').find('.wt_pklist_custom_field_tab_content[data-id="'+trget_id+'"]').addClass('active_tab');
					if("add_new" === trget_id)
					{
						$(this).parents('.wf_popover').find('.wf_popover-footer').show();
					}
					if("list_view" === trget_id)
					{
						$(this).parents('.wf_popover').find('.wf_popover-footer').hide();
						wt_pklist_custom_field_editor.get_list();
					}
				});
			},
			reg_delete:function()
			{
				$(document).on('click','.wt_pklist_custom_field_delete',function(){
					var item_dv=$(this).parent('.wt_pklist_custom_field_item');
					if(1 === item_dv.attr('data-builtin'))
					{
						return false;
					}
					if(confirm(wf_pklist_params_ipc_admin_param.msgs.sure))
					{
						var sele_elm=$('[name="wt_pklist_custom_field_btn"]').data('select-elm');
						var meta_key=item_dv.attr('data-key');
						sele_elm.select2("destroy");
						sele_elm.find('option[value="'+meta_key+'"]').remove();
						sele_elm.select2();
						wt_pklist_custom_field_editor.get_list(meta_key);
					}
				});
			},
			reg_edit:function()
			{
				$(document).on('click','.wt_pklist_custom_field_edit',function(){
					var item_dv=$(this).parent('.wt_pklist_custom_field_item');
					var tb_hd_dv=$('.wf_popover-content').find('.wt_pklist_custom_field_tab_head[data-target="add_new"]');
					tb_hd_dv.trigger('click').find('.wt_pklist_custom_field_tab_head_title').html(tb_hd_dv.attr('data-edit-title'));
					$('.wf_popover-content').find('.wt_pklist_custom_field_tab_head_add_new').show();

					/* fillup the fields */
					var form_dv=$('.wf_popover-content').find('.wt_pklist_custom_field_tab_content[data-id="add_new"]');
					form_dv.find('[name="wt_pklist_new_custom_field_title"]').val(item_dv.find('.wt_pklist_custom_field_title').html().trim());
					form_dv.find('[name="wt_pklist_new_custom_field_key"]').val(item_dv.attr('data-key')).prop('readonly',true);
					form_dv.find('[name="wt_pklist_new_custom_field_title_placeholder"]').val(item_dv.find('.wt_pklist_custom_field_placeholder').html().trim());
					form_dv.find('[name="wt_pklist_cst_chkout_required"]').prop('checked',false);
					form_dv.find('[name="wt_pklist_cst_chkout_required"][value="'+item_dv.find('.wt_pklist_custom_field_is_required').text().trim()+'"]').prop('checked',true);
					/* form_dv.find('.wt_pklist_custom_field_form_notice').hide(); */
				});
			},
			reg_addnew:function()
			{
				$(document).on('click','.wt_pklist_custom_field_tab_head_add_new', function(){
					var tb_hd_dv=$('.wf_popover-content').find('.wt_pklist_custom_field_tab_head[data-target="add_new"]');
					tb_hd_dv.trigger('click').find('.wt_pklist_custom_field_tab_head_title').html(tb_hd_dv.attr('data-add-title'));
					$(this).hide();
					var form_dv=$('.wf_popover-content').find('.wt_pklist_custom_field_tab_content[data-id="add_new"]');
					form_dv.find('[name="wt_pklist_new_custom_field_key"]').prop('readonly',false);
					$('.wf_popover-content').trigger('reset');
					/* form_dv.find('.wt_pklist_custom_field_form_notice').show(); */
				});
			},
			get_list:function(delete_item)
			{
				var ajx_data='action=wt_pklist_custom_field_list_view&_wpnonce='+wf_pklist_params_ipc_admin_param.nonces.wf_packlist+'&wt_pklist_custom_field_type='+jQuery('[name="wt_pklist_custom_field_btn"]').data('field-type');

				if(delete_item)
				{
					ajx_data+='&wf_delete_custom_field='+delete_item;
				}

				$('.wf_popover-content').find('.wt_pklist_custom_field_tab_content[data-id="list_view"]').html(wf_pklist_params_ipc_admin_param.msgs.please_wait);
				jQuery.ajax({
					url:wf_pklist_params_ipc_admin_param.ajaxurl,
					data:ajx_data,
	            	type: 'POST',
					success:function(data)
					{ 
						$('.wt_pklist_custom_field_tab_content[data-id="list_view"]').html(data);
					},
					error:function()
					{
						$('.wt_pklist_custom_field_tab_content[data-id="list_view"]').html(wf_pklist_params_ipc_admin_param.msgs.error);
					}
				});
			}
		}
		var wf_popover={
			Set:function()
			{
				this.remove_duplicate_content_container();
				jQuery('[data-wf_popover="1"]').on('click',function(){
					var cr_elm=jQuery(this);
					if(1 === cr_elm.attr('data-popup-opened'))
					{
						var pp_elm=jQuery('.wf_popover');
						var pp_lft=pp_elm.offset().left-50;
						jQuery('[data-wf_popover="1"]').attr('data-popup-opened',0);
						pp_elm.stop(true,true).animate({'left':pp_lft,'opacity':0}, 300,function(){
							jQuery(this).css({'display':'none'});
						});
						return false;
					}else
					{
						jQuery('[data-wf_popover="1"]').attr('data-popup-opened', 0);
						cr_elm.attr('data-popup-opened',1);
					}
					if(0 === jQuery('.wf_popover').length)
					{
						var template='<div class="wf_popover"><h3 class="wf_popover-title"></h3><span class="wt_popover_close_top popover_close" title="'+wf_pklist_params_ipc_admin_param.msgs.close+'">X</span>'
						+'<form class="wf_popover-content"></form><div class="wf_popover-footer">'
						+'<button name="wt_pklist_custom_field_btn" type="button" id="wt_pklist_custom_field_btn" class="button button-primary">'+wf_pklist_params_ipc_admin_param.msgs.save+'</button>'
						+'<button name="popover_close" type="button" class="button button-secondary popover_close">'+wf_pklist_params_ipc_admin_param.msgs.close+'</button>'
						+'<span class="spinner" style="margin-top:5px"></span>'
						+'</div></div>';
						jQuery('body').append(template);
						wf_popover.regclosePop();
						wf_popover.sendData();
					}
					
					var pp_elm=jQuery('.wf_popover');
					var action_field='<input type="hidden" name="wt_pklist_settings_base" value="'+cr_elm.attr('data-module-base')+'"  />';
					var pp_html='';
					var pp_html_cntr=cr_elm.attr('data-content-container');
					if(typeof pp_html_cntr !== typeof undefined && pp_html_cntr !== false)
					{
						pp_html=jQuery(pp_html_cntr).html();
					}else
					{
						pp_html=cr_elm.attr('data-content');
					}
					pp_elm.css({'display':'block'}).find('.wf_popover-content').html(pp_html).append(action_field);
					pp_elm.find('.wf_popover-footer').show();
					var cr_elm_w=cr_elm.width();
					var cr_elm_h=cr_elm.height();
					var pp_elm_w=pp_elm.width();
					var pp_elm_h=pp_elm.height();
					var cr_elm_pos=cr_elm.offset();
					var cr_elm_pos_t=cr_elm_pos.top-((pp_elm_h-cr_elm_h)/2);
					var cr_elm_pos_l=cr_elm_pos.left+cr_elm_w;	    			
					pp_elm.find('.wf_popover-title').html(cr_elm.attr('data-title'));			
					pp_elm.css({'display':'block','opacity':0,'top':cr_elm_pos_t+5,'left':cr_elm_pos_l}).stop(true,true).animate({'left':cr_elm_pos_l+50,'opacity':1});
					jQuery('[name="wt_pklist_custom_field_btn"]').data({'select-elm' : cr_elm.parents('.wf_select_multi').find('select'), 'field-type':cr_elm.attr('data-field-type')});
				});
			},
			remove_duplicate_content_container:function()
			{
				jQuery('[data-wf_popover="1"]').each(function(){
					var cr_elm=jQuery(this);
					var pp_html_cntr=cr_elm.attr('data-content-container');
					var container_arr=new Array();
					if(typeof pp_html_cntr !== typeof undefined && pp_html_cntr !== false)
					{
						if(jQuery.inArray(pp_html_cntr, container_arr)==-1)
						{
							container_arr.push(pp_html_cntr);
							if(jQuery(pp_html_cntr).lenth>1)
							{
								jQuery(pp_html_cntr).not(':first-child').remove();
							}
						}			
					}
				});
			},
			sendData:function()
			{	    		
				jQuery('[name="wt_pklist_custom_field_btn"]').on('click',function(){
			        
			        var empty_fields=0;
			        jQuery('.wf_popover-content input[type="text"]').each(function(){
			        	if(1 === jQuery(this).attr('data-required') && "" === jQuery(this).val().trim())
			        	{
							empty_fields++;
			        	}
			        });
			        if(empty_fields>0){
			        	alert(wf_pklist_params_ipc_admin_param.msgs.enter_mandatory_fields);
			        	jQuery('.wf_popover-content input[type="text"]:eq(0)').focus();
			        	return false;
			        }

			        if(0 === empty_fields){
			        	var custom_meta_key = jQuery('.wf_popover-content input[name="wt_pklist_new_custom_field_key"]').val();
		    			if ("" !== custom_meta_key && (jQuery.isNumeric(custom_meta_key) || custom_meta_key.match(/^\d+$/))) {
		    				alert(wf_pklist_params_ipc_admin_param.msgs.enter_mandatory_non_numeric_fields);
		    				return false;
		    			}
			        }
			        var elm=jQuery(this);
			        var sele_elm=elm.data('select-elm');
			        jQuery('.wf_popover-footer .spinner').css({'visibility':'visible'});
			        jQuery('.wf_popover-footer .button').attr('disabled','disabled').css({'opacity':.5});
			        var data=jQuery('.wf_popover-content').serialize();

			        data+='&action=wf_pklist_advanced_fields&_wpnonce='+wf_pklist_params_ipc_admin_param.nonces.wf_packlist+'&wt_pklist_custom_field_btn&wt_pklist_custom_field_type='+elm.data('field-type');	
			        jQuery.ajax({
						url:wf_pklist_params_ipc_admin_param.ajaxurl,
						data:data,
						dataType:'json',
		            	type: 'POST',
						success:function(data)
						{
							jQuery('.wf_popover-footer .spinner').css({'visibility':'hidden'});
							jQuery('.wf_popover-footer .button').removeAttr('disabled').css({'opacity':1});
							if(true === data.success)
							{
								if('add' === data.action)
								{
									var newOption = new Option(data.val,data.key, true, true);
									sele_elm.append(newOption).trigger('change');
								}else
								{
									sele_elm.select2("destroy");
									sele_elm.find('option[value="'+data.key+'"]').text(data.val);
									sele_elm.select2();
								}
							}else
							{
								alert(data.msg);
								jQuery('.wf_popover-footer .spinner').css({'visibility':'hidden'});
								jQuery('.wf_popover-footer .button').removeAttr('disabled').css({'opacity':1});
							}
						},
						error:function()
						{
							jQuery('.wf_popover-footer .spinner').css({'visibility':'hidden'});
							jQuery('.wf_popover-footer .button').removeAttr('disabled').css({'opacity':1});
						}
					});
				});
			},
			regclosePop:function()
			{
				jQuery('.nav-tab ').on('click',function(){
					jQuery('.wf_popover').css({'display':'none'});
				});
				jQuery('.popover_close').on('click',function(){
					wf_popover.closePop();
				});
			},
			closePop:function()
			{
				var pp_elm=jQuery('.wf_popover');
				if(pp_elm.length>0)
				{
					var pp_lft=pp_elm.offset().left-50;
					jQuery('[data-wf_popover="1"]').attr('data-popup-opened',0);
					pp_elm.stop(true,true).animate({'left':pp_lft,'opacity':0},300,function(){
						jQuery(this).css({'display':'none'});
					});
				}
			}
		};
		wt_pklist_custom_field_editor.Set();
		wf_popover.Set();
	});
})( jQuery );