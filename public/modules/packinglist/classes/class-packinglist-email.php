<?php
if (!defined('ABSPATH')) exit;

class Wf_Woocommerce_Packing_List_Packinglist_Email extends WC_Email {

	public function __construct() 
	{
		$this->id             = 'wt_pklist_packinglist_email';
		$this->title          = __('Packing Slip', 'wt_woocommerce_invoice_addon');
		$this->description = sprintf( __( 'Send Packing Slip as PDF attachment to warehouse when an order is placed. Use these available placeholders: %s', 'wt_woocommerce_invoice_addon' ), '<code>{site_title}, {order_date}, {order_number}</code>' );
		$this->heading        = $this->get_default_heading();
		$this->subject        = $this->get_default_subject();

		$this->template_html  = 'emails/packinglist.php';
		$this->template_plain = 'emails/plain/packinglist.php';

		$this->placeholders   = array(
			'{site_title}'   => $this->get_blogname(),
			'{order_date}'   => '',
			'{order_number}' => '',
		);
		
		$this->template_base = WF_PKLIST_PACKINGLIST_PRO_EMAIL_TEMPLATE_PATH;

		$this->send_seperate_mail_on_order_status_changes();

		// Call parent constructor.
		parent::__construct();

		// Other settings.
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );

		$this->enabled='yes';
	}

	public function send_seperate_mail_on_order_status_changes(){
		$is_send_on_checkout_process = false;
		$is_send_on_checkout_process = apply_filters('wt_pklist_send_seperate_mail_only_on_checkout_process',$is_send_on_checkout_process,'packinglist');
		if(!$is_send_on_checkout_process){
			// Triggers for this email.
			add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_pending_to_completed_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_failed_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_failed_to_completed_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_cancelled_to_processing_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_cancelled_to_completed_notification', array( $this, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_cancelled_to_on-hold_notification', array( $this, 'trigger' ), 10, 2 );
		}
	}
	
	/**
	 * Trigger sending of this email.
	 *
	 * @param int      $order_id The order ID.
	 * @param WC_Order $order Order object.
	 */
	public function trigger( $order_id, $order = false )
	{
		$this->setup_locale();

		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object                         = $order;
			$this->recipient                      = $this->process_recipient_list();
			$this->placeholders['{order_date}']   = wc_format_datetime( $this->object->get_date_created() );
			$this->placeholders['{order_number}'] = $this->object->get_order_number();
		}

		if ( $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	private function process_recipient_list()
	{
		return $this->recipient;
	}

	/**
	 * Get email subject.
	 */
	public function get_default_subject() 
	{
		$default_subject = 	__('Packing Slip for order #{order_number} on {site_title}', 'wt_woocommerce_invoice_addon');
		$subject = $this->get_option('subject',$default_subject);
		$subject = (trim($subject) == "") ? $default_subject : $subject;
		return $subject;
	}

	/**
	 * Get email heading.
	 */
	public function get_default_heading()
	{	
		$default_heading = __('Packing Slip for order #{order_number}', 'wt_woocommerce_invoice_addon');
		$heading = $this->get_option('heading',$default_heading);
		$heading = (trim($heading) == "") ? $default_heading : $heading;
		return $heading;
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_subject() {
        return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $this->subject ), $this->object );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_heading() {
        return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->heading ), $this->object );
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {       
        return wc_get_template_html(
			$this->template_html, array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => false,
				'email'         => $this,
			),
			'',
			$this->template_base
		);
    }

    /**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
        return wc_get_template_html(
			$this->template_plain, array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => true,
				'plain_text'    => true,
				'email'         => $this,
			),
			'',
			$this->template_base
		);
    }

    /**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			/*'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'woocommerce' ),
				'default' => 'no',
			), */
			'recipient'  => array(
				'title'       => __( 'Recipient(s)', 'wt_woocommerce_invoice_addon' ),
				'type'        => 'text',
				/* translators: %s: WP admin email */
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'wt_woocommerce_invoice_addon' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>'),
				'placeholder' => '',
				'default'     => '',
				'desc'    => true,
				'desc_tip' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'wt_woocommerce_invoice_addon' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>'),
			),
			'subject'      => array(
				'title'       => __( 'Subject', 'wt_woocommerce_invoice_addon' ),
				'type'        => 'text',
				'desc_tip'    => true,
				/* translators: %s: list of placeholders */
				'description' => sprintf( __( 'Available placeholders: %s', 'wt_woocommerce_invoice_addon' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'placeholder' =>$this->get_default_subject(),
				'default'     => '',
			),
			'heading'      => array(
				'title'       => __( 'Email heading', 'wt_woocommerce_invoice_addon' ),
				'type'        => 'text',
				'desc_tip'    => true,
				/* translators: %s: list of placeholders */
				'description' => sprintf( __( 'Available placeholders: %s', 'wt_woocommerce_invoice_addon' ), '<code>{site_title}, {order_date}, {order_number}</code>' ),
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'email_type'   => array(
				'title'       => __( 'Email type', 'wt_woocommerce_invoice_addon' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'wt_woocommerce_invoice_addon' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}
}