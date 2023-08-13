(function( $ ) {
	//'use strict';
	function wf_toggle_invoice_number_fields()
	{
		var vl=$('#woocommerce_wf_invoice_number_format').val();
		var prefix_tr=$('[name="woocommerce_wf_invoice_number_prefix"]').parents('tr');
		var postfix_tr=$('[name="woocommerce_wf_invoice_number_postfix"]').parents('tr');
		prefix_tr.hide().find('th label').css({'padding-left':'0px'});
		postfix_tr.hide().find('th label').css({'padding-left':'0px'});
		$('.form-table th label').css({'float':'left','width':'100%'});
		var num_reg=/\[number\]/gm;
		var pre_reg=/\[prefix\]/gm;
		var pos_reg=/\[suffix\]/gm;
		if(vl.search(pre_reg)>=0)
		{  
			prefix_tr.show().find('th label').animate({'padding-left':'15px'});
		}
		if(vl.search(pos_reg)>=0)
		{
			postfix_tr.show().find('th label').animate({'padding-left':'15px'});
		}
	}
	$('[name="woocommerce_wf_invoice_as_ordernumber"]').on('change',function(){
		if("Yes" === $(this).val()){
			$('#preview_invoice_number_text').show();
			$("#preview_invoice_number_text_custom").hide();
		}else{
			$("#preview_invoice_number_text_custom").show();
			$('#preview_invoice_number_text').hide();
		}
	});
	$('#woocommerce_wf_invoice_number_format').change(function(){
		wf_toggle_invoice_number_fields();
	});
	wf_toggle_invoice_number_fields();

	$("#invoice_start_number").on('input change',function(){
		$("#sample_current_invoice_number").val($(this).val());
	});

	$('.invoice_preview_assert').on('input change',function(){
		wf_do_invoice_number_preview();
	});

	
	wf_do_invoice_number_preview();
	function wf_do_invoice_number_preview(){
		var invoice_no = $("#sample_invoice_number").val();
		var number_ref=$('[name="woocommerce_wf_invoice_as_ordernumber"]:checked').val();
		var invoice_start_no = $('#sample_current_invoice_number').val();
		var number_format=$('#woocommerce_wf_invoice_number_format').val();
		var prefix_val =$('[name="woocommerce_wf_invoice_number_prefix"]').val();
		var postfix_val =$('[name="woocommerce_wf_invoice_number_postfix"]').val();
		var number_len = $('[name="woocommerce_wf_invoice_padding_number"]').val();

		if("No" === number_ref){
			invoice_no = invoice_start_no;
		}
		/* length change calculation */
		var padded_invoice_number = "";
		var invoice_no_length = invoice_no.length;
		var padding_count = number_len - invoice_no_length;
		if (padding_count > 0) {
            for (var i = 0; i < padding_count; i++)
            {
                padded_invoice_number += '0';
            }
        }
		invoice_no = padded_invoice_number+invoice_no;

		if("[prefix][number][suffix]" === number_format){
			invoice_no = prefix_val+invoice_no+postfix_val;
		}else if("[prefix][number]" === number_format){
			invoice_no = prefix_val+invoice_no;
		}else if("[number][suffix]" === number_format){
			invoice_no = invoice_no+postfix_val;
		}
		invoice_no = replace_date_val_invoice_number(invoice_no);

		/* final preview */
		$("#preview_invoice_number").text(invoice_no);
	}

	function replace_date_val_invoice_number(invoice_no){
		invoice_no = invoice_no.replace(" data-val='order_date'","");
		const monthNames = ["January", "February", "March", "April", "May", "June",
		  "July", "August", "September", "October", "November", "December"
		];
		const monthShortNamescaps = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
		  "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
		];
		const daysShortNamescaps = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri",
		  "Sat"];
		var d = new Date();
		var full_year = d.getFullYear();
	    var short_year = full_year.toString().substr(-2);
		invoice_no = invoice_no.replace('[F]',monthNames[d.getMonth()]).replace('[dS]',d.getDate()+'th').replace('[M]',monthShortNamescaps[d.getMonth()]).replace('[m]',("0" + (d.getMonth()+1)).slice(-2)).replace('[d]',("0" + d.getDate()).slice(-2)).replace('[y]',short_year).replace('[Y]',full_year).replace('[D]',daysShortNamescaps[d.getDay()]).replace('[d/m/y]',("0" + d.getDate()).slice(-2)+'/'+("0" + (d.getMonth()+1)).slice(-2)+'/'+short_year).replace('[d-m-Y]',("0" + d.getDate()).slice(-2)+'-'+("0" + (d.getMonth()+1)).slice(-2)+'-'+full_year);
		return invoice_no;
	}

	$('.wf_inv_num_frmt_hlp_btn').on('click', function(){
		var trgt_field=$(this).attr('data-wf-trget');
		$('.wf_inv_num_frmt_hlp').attr('data-wf-trget',trgt_field);
		wf_popup.showPopup($('.wf_inv_num_frmt_hlp'));
	});

	$('.wf_inv_num_frmt_append_btn').on('click', function(){
		var trgt_elm_name=$(this).parents('.wf_inv_num_frmt_hlp').attr('data-wf-trget');
		var trgt_elm=$('[name="'+trgt_elm_name+'"]');
		var exst_vl=trgt_elm.val();
		var cr_vl=$(this).text();
		if($('[name="wf_inv_num_frmt_data_val"]:checked').length>0)
		{
			var data_val=$('[name="wf_inv_num_frmt_data_val"]:checked').val();
			const regex = /\[(.*?)\]/gm;
			cr_vl=cr_vl.replace(regex, "[$1 data-val='"+data_val+"']");
		}
		trgt_elm.val(exst_vl+cr_vl);
		wf_popup.hidePopup();
		wf_do_invoice_number_preview();
	});

})( jQuery );