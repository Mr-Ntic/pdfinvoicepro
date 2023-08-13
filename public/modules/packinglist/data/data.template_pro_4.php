<div class="wfte_rtl_main wfte_invoice-main">
  <div class="wfte_invoice-header wfte_invoice-header_color clearfix">
      <div class="wfte_invoice-header_top clearfix">
        <div class="wfte_col-1 float_left clearfix">  
          <div class="wfte_doc_title wfte_template_element" data-hover-id="doc_title">__[Packing slip]__</div>
        </div>
      </div>
      <div class="wfte_invoice-header_top clearfix">
        <div class="wfte_company_logo float_left wfte_template_element" data-hover-id="company_logo">
            <div class="wfte_company_logo_img_box">
                <img src="[wfte_company_logo_url]" class="wfte_company_logo_img">
            </div>
            <div class="wfte_company_name wfte_hidden">[wfte_company_name]</div>
            <div class="wfte_company_logo_extra_details">__[]__</div>
        </div>

        <div class="wfte_barcode float_right wfte_text_right wfte_template_element" data-hover-id="barcode">
            <img src="[wfte_barcode_url]" class="wfte_img_barcode">
        </div>

        <div class="float_right" style="width:28%;">
          <div class="wfte_invoice_data">
            <div class="wfte_order_number wfte_template_element" data-hover-id="order_number">
              <span class="wfte_order_number_label">__[Order No:]__</span> 
              <span class="wfte_order_number_val">[wfte_order_number]</span>
            </div>
            <div class="wfte_order_date wfte_template_element" data-order_date-format="m-d-Y" data-hover-id="order_date">
              <span class="wfte_order_date_label">__[Date:]__</span> 
              <span class="wfte_order_date_val">[wfte_order_date]</span>
            </div>
          </div>
          <div class="wfte_package_no wfte_hidden clearfix wfte_text_left wfte_template_element" data-hover-id="package_no">
            [wfte_package_no]
          </div>
          <div class="wfte_total_no_of_items wfte_template_element clearfix wfte_text_left" data-hover-id="total_no_of_items">
              <span class="wfte_total_no_of_items_label">__[No of items:]__ </span> 
              <span class="wfte_total_no_of_items_val">[wfte_total_no_of_items]</span>
          </div>
        </div>

      </div>
      <div class="wfte_addrss_field_main clearfix">
         <div class="wfte_addrss_fields wfte_from_address float_left wfte_text_left wfte_template_element" data-hover-id="from_address">
           <div class="wfte_address-field-header wfte_from_address_label">__[From Address:]__</div>
           <div class="wfte_from_address_val">[wfte_from_address]</div>
         </div>
         <div class="wfte_addrss_fields wfte_billing_address float_left wfte_text_left wfte_template_element" data-hover-id="billing_address">
           <div class="wfte_address-field-header wfte_billing_address_label">__[Billing Address:]__</div>
           [wfte_billing_address]
         </div>
         <div class="wfte_addrss_fields wfte_shipping_address float_left wfte_text_left wfte_template_element" data-hover-id="shipping_address">
           <div class="wfte_address-field-header wfte_shipping_address_label">__[Shipping Address:]__</div>
           [wfte_shipping_address]
         </div>
         <div class="wfte_addrss_fields wfte_text_left float_left">
            <div class="wfte_invoice_data">  
                <div class="wfte_vat_number wfte_template_element" data-hover-id="vat_number">
                  <span class="wfte_vat_number_label">__[VAT:]__</span>
                  <span class="wfte_vat_number_val">[wfte_vat_number]</span>
                </div>
                <div class="wfte_ssn_number wfte_template_element" data-hover-id="ssn_number">
                  <span class="wfte_ssn_number_label">__[SSN:]__</span>
                  <span class="wfte_ssn_number_val">[wfte_ssn_number]</span>
                </div>
                <div class="wfte_email wfte_template_element" data-hover-id="email">
                  <span class="wfte_email_label">__[Email:]__</span>
                  <span class="wfte_email_val">[wfte_email]</span>
                </div>
                <div class="wfte_tel wfte_template_element" data-hover-id="tel">
                  <span class="wfte_tel_label">__[Tel:]__</span>
                  <span class="wfte_tel_val">[wfte_tel]</span>
                </div>
                <div class="wfte_shipping_method wfte_template_element" data-hover-id="shipping_method">
                  <span class="wfte_shipping_method_label">__[Shipping Method:]__</span>
                  <span class="wfte_shipping_method_val">[wfte_shipping_method]</span>
                </div>
                <div class="wfte_tracking_number wfte_template_element" data-hover-id="tracking_number">
                  <span class="wfte_tracking_number_label">__[Tracking number:]__</span>
                  <span class="wfte_tracking_number_val">[wfte_tracking_number]</span>
                </div>
                <div class="wfte_customer_note wfte_template_element wfte_hidden" data-hover-id="customer_note">
                    <span class="wfte_customer_note_label">__[Customer note:]__</span>
                    <span>[wfte_customer_note]</span>
                </div>
            </div>
            <div class="wfte_order_item_meta">[wfte_order_item_meta]</div>
            [wfte_extra_fields]
         </div>
      </div>
  </div>
  <div class="wfte_invoice-body clearfix">
  [wfte_product_table_start]
    <table class="wfte_product_table wfte_template_element" data-hover-id="product_table">
        <thead class="wfte_product_table_head">
          <tr>
            <th class="wfte_product_table_head_serial_no wfte_product_table_head_bg wfte_table_head_color" col-type="-serial_no">__[S.No]__</th>
            <th class="wfte_product_table_head_image wfte_table_head_color wfte_product_table_head_bg" col-type="image">__[Image]__</th>
            <th class="wfte_product_table_head_sku wfte_table_head_color wfte_product_table_head_bg" col-type="sku">__[SKU]__</th>
            <th class="wfte_product_table_head_product wfte_table_head_color wfte_product_table_head_bg" col-type="product">__[Product]__</th>
            <th class="wfte_product_table_head_quantity wfte_table_head_color wfte_product_table_head_bg" col-type="quantity">__[Quantity]__</th>
            <th class="wfte_product_table_head_total_weight wfte_table_head_color wfte_product_table_head_bg" col-type="total_weight">__[Total Weight]__</th>
            <th class="wfte_product_table_head_total_price wfte_table_head_color wfte_product_table_head_bg" col-type="total_price">__[Total Price]__</th>     
          </tr>
        </thead>
        <tbody class="wfte_product_table_body">
        </tbody>
    </table>   
  [wfte_product_table_end]

    <div class="wfte_return_policy wfte_text_left clearfix wfte_template_element" data-hover-id="return_policy">
      [wfte_return_policy]
    </div>
    <div class="wfte_footer wfte_text_left clearfix wfte_template_element" data-hover-id="footer">
      [wfte_footer]
    </div>
  </div>
</div>
<style type="text/css">
.clearfix::after {
    display: block;
    clear: both;
  content: "";}
.wfte_invoice-main{ color:#73879C; font-family:"Helvetica Neue", Roboto, Arial, "Droid Sans", sans-serif; font-size:12px; font-weight:400; line-height:14px; box-sizing:border-box; width:100%; margin:0px; padding:0px;}
.wfte_invoice-main *{ box-sizing:border-box;}
.wfte_col-1{ width:100%; display:block;}
.wfte_doc_title{ color:#23a8f9; font-size:30px; font-weight:bold; height:auto; width:100%; line-height:22px;}
.wfte_invoice-header{ background:#445aa8; padding:15px 20px; width:100%; }
.wfte_invoice-header_color{ color:#ffffff;}
.wfte_invoice-header_top{ padding:15px 0px; padding-bottom:25px; border-bottom:solid 1px rgba(255, 255, 255, 0.2); width:100%; height:auto;}
.wfte_company_logo{width:40%; }
.wfte_company_logo_img{ width:150px; max-width:100%; }
.wfte_barcode{ width:25%; }
.wfte_barcode .wfte_img_barcode{width: 100%;}
.wfte_company_name{ font-size:18px; font-weight:bold; }
.wfte_company_logo_extra_details{ font-size:12px; }
.wfte_invoice_number{ font-size:12px; font-weight:bold; }
.wfte_order_number{ font-size:12px; font-weight:bold; }
.wfte_invoice_date{ font-size:12px;}
.wfte_order_date{ font-size:12px;}
.wfte_invoice_data{width:100%;}
.wfte_addrss_field_main{width:100%; font-size:12px; padding-top:25px; }
.wfte_addrss_fields{ width:24%; padding:0px .5%;}
.wfte_address-field-header{ font-weight:bold; }
.wfte_invoice-body{background:#ffffff; color:#23272c; padding:15px 20px; width:100%;}
.wfte_product_table{ width:100%; border-collapse:collapse; margin:0px; }
.wfte_product_table .wfte_right_column{ width:15%; }
.wfte_payment_summary_table .wfte_left_column{ width:60%; }

.wfte_product_table_head{color:#ffffff;}
.wfte_product_table_head th{ color:#ffffff; height:36px; padding:0px 5px; font-size:.75rem; text-align:center; line-height:10px; font-family:"Helvetica Neue", Roboto, Arial, "Droid Sans", sans-serif;}
.wfte_product_table_body td{ text-align:center; padding:2px 5px; font-size:12px; line-height:10px; border:solid 1px #dadada;}
.wfte_product_table_head_bg{ background-color:#212529; }
.wfte_product_table_head_bg th{border:solid 1px #212529;}

.wfte_product_table_payment_total{ font-size:12px; font-weight:bold; height:36px; padding:0px;line-height:10px;}
td.wfte_product_table_payment_total_label{ text-align:right; background-color:#212529; height:36px; padding:0px 5px; line-height:10px; color:#ffffff;}
.wfte_product_table_payment_total_val{background-color:#212529; color:#ffffff;}
.wfte_signature{ width:100%; height:auto; min-height:60px; padding:15px 0px;}
.wfte_image_signature_box{ display:inline-block; }
.wfte_return_policy{width:100%; height:auto; border-top:solid 1px #dfd5d5; padding:5px 0px; margin-top:15px; }
.wfte_footer{width:100%; height:auto; border-top:solid 1px #dfd5d5; padding:5px 0px; margin-top:5px;}

.wfte_invoice_data td, .wfte_extra_fields td{font-size:12px; padding:0px; font-family:"Helvetica Neue", Roboto, Arial, "Droid Sans", sans-serif;}

.float_left{ float:left; }
.float_right{ float:right; }
</style>