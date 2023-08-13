=== WooCommerce PDF Invoices, Packing Slips and Credit Notes (Pro) ===
Contributors: Webtoffee
Version: 1.0.3
Tags: woocommerce invoice, woocommerce invoice generator, woocommerce send invoice, woocommerce invoice email, woocommerce receipt plugin, woocommerce vat invoice, woocommerce pdf invoices, woocommerce custom invoice, Packinglist, Invoice printing, Credit note, Wordpress
Requires at least: 3.0.1
Requires PHP: 5.6
Tested up to: 6.2
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce PDF Invoices, Packing Slips and Credit Notes (Pro)

== Description ==

== Screenshots ==

== Installation ==

1. Upload `wt-woocommerce-invoice-addon` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Changelog ==

= 1.0.3 2023-07-07 =
* [Tweak] - Added customer note, VAT, and SSN as separate placeholders in the templates
* [Tweak] - Added Save and Activate button in the Invoice, Packing slip, and Credit note customizer
* [Enhancement] - Optimized loading time when bulk printing the documents
* [Enhancement] - Icons to identify whether an invoice or packing slip is already printed or downloaded
* [Enhancement] - Added compatibility with WooCommerce High-Performance Order Storage (HPOS) Table feature
* [Enhancement] - Moved the default order meta fields such as email, phone number, customer note, VAT, and SSN from the Settings page to the Customizer
* [Enhancement] - Added compatibility with Customizer for WooCommerce PDF Invoices add-on (Pro) by WebToffee
* [Compatibility] - Tested OK with WooCommerce v7.8.2

= 1.0.2 2023-03-06 =
* [Fix] - CRITICAL Uncaught Error: Access to an undeclared static property of $return_dummy_invoice_number variable
* [Fix] - The order meta option in the packing slip - advanced tab could not be saved
* [Fix] - Unable to hide the tax items column in product table
* [Tweak] - Notify the user when using the basic template in the pro-add-on or when using the premium template in the basic plugin
* [Compatibility] - Compatibility with WooCommerce upto v7.4.1

= 1.0.1 =
* [Fix] - Added the placeholder for the payment received a stamp for the premium invoice templates
* [Fix] - SKU Based sorting was not working properly when SKUs are numerical order
* [Tweak] - Added a filter to use the billing address as the shipping address or hide the shipping address field when the shipping address is empty
* [Enhancement] - Added an option to create the credit notes for the chosen orders statuses only if the respective order has any refund
* [Enhancement] - Added an option to create credit notes manually from the order details page if the order has any refund
* [Compatibility] - Added the compatibility of the print node add-on from v1.0.4
* [Compatibility] - with WC v7.3.0

= 1.0.0 =
* Initial version

== Upgrade Notice ==

= 1.0.3 =
* [Tweak] - Added customer note, VAT, and SSN as separate placeholders in the templates
* [Tweak] - Added Save and Activate button in the Invoice, Packing slip, and Credit note customizer
* [Enhancement] - Optimized loading time when bulk printing the documents
* [Enhancement] - Icons to identify whether an invoice or packing slip is already printed or downloaded
* [Enhancement] - Added compatibility with WooCommerce High-Performance Order Storage (HPOS) Table feature
* [Enhancement] - Moved the default order meta fields such as email, phone number, customer note, VAT, and SSN from the Settings page to the Customizer
* [Enhancement] - Added compatibility with Customizer for WooCommerce PDF Invoices add-on (Pro) by WebToffee
* [Compatibility] - Tested OK with WooCommerce v7.8.2