<?php
/**
 * Pay Later Payment Gateway
 *
 * @link       
 * @since 1.0.0     
 *
 * @package  Wt_woocommerce_invoice_addon  
 */
if (!defined('ABSPATH')) {
    exit;
}
if((!class_exists('Wf_Woocommerce_Packing_List_Pay_Later_Payment')) && (class_exists('WC_Payment_Gateway')))
{
	class Wf_Woocommerce_Packing_List_Pay_Later_Payment extends WC_Payment_Gateway {
		
		private $plugin_name;
		private $version;
		public $instructions;

		public function __construct() {

			if( defined( 'WT_PKLIST_INVOICE_ADDON_VERSION' ) ) 
			{
				$this->version = WT_PKLIST_INVOICE_ADDON_VERSION;
			}else
			{
				$this->version = '1.0.3';
			}
			if(defined('WT_PKLIST_INVOICE_ADDON_PLUGIN_NAME'))
			{
				$this->plugin_name=WT_PKLIST_INVOICE_ADDON_PLUGIN_NAME;
			}else
			{
				$this->plugin_name='wt_woocommerce_invoice_addon';
			}

			$this->id = 'wf_pay_later';
			$this->icon               = apply_filters('woocommerce_offline_icon', '');
			$this->has_fields         = false;
			$this->method_title       = __( 'Pay Later', 'wt_woocommerce_invoice_addon' );
			$this->method_description = __( 'Allows a ‘Pay Later’ option at the checkout. Orders will be marked with the status ‘Pending payment’ on using this payment method.', 'wt_woocommerce_invoice_addon' );

			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();

			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );

			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		  
			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}

		/**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
	  
			$this->form_fields = apply_filters( 'wf_pay_later_payment_form_fields', array(
		  
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wt_woocommerce_invoice_addon' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Pay Later Payment', 'wt_woocommerce_invoice_addon' ),
					'default' => 'no'
				),
				
				'title' => array(
					'title'       => __( 'Title', 'wt_woocommerce_invoice_addon' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wt_woocommerce_invoice_addon' ),
					'default'     => __( 'Pay Later Payment', 'wt_woocommerce_invoice_addon' ),
					'desc_tip'    => true,
				),
				
				'description' => array(
					'title'       => __( 'Description', 'wt_woocommerce_invoice_addon' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'wt_woocommerce_invoice_addon' ),
					'default'     => __( 'Please remit payment to Store Name upon pickup or delivery.', 'wt_woocommerce_invoice_addon' ),
					'desc_tip'    => true,
				),
				
				'instructions' => array(
					'title'       => __( 'Instructions', 'wt_woocommerce_invoice_addon' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wt_woocommerce_invoice_addon' ),
					'default'     => '',
					'desc_tip'    => true,
				),
			) );
		}


		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}


		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
			$order_id = (WC()->version < '2.7.0') ? $order->id : $order->get_id();
			$payment_method = (WC()->version< '2.7.0' ? Wt_Pklist_Common_Ipc::get_order_meta( $order_id, '_payment_method', true ) : $order->get_payment_method());
			if ( $this->instructions && ! $sent_to_admin && $this->id === $payment_method && $order->has_status( 'on-hold' ) ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}


		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {

			$order = wc_get_order( $order_id );
			
			// Mark as on-hold (we're awaiting the payment)
			$order->update_status( 'on-hold', __( 'Pay later: ', 'wt_woocommerce_invoice_addon' ) );
			
			// Remove cart
			WC()->cart->empty_cart();
			
			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		}
	}
}
?>