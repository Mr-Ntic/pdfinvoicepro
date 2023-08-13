<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.webtoffee.com/
 * @since      1.0.0
 *
 * @package    Wt_woocommerce_invoice_addon
 * @subpackage Wt_woocommerce_invoice_addon/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wt_woocommerce_invoice_addon
 * @subpackage Wt_woocommerce_invoice_addon/includes
 * @author     Webtoffee <info@webtoffee.com>
 */
class Wt_woocommerce_invoice_addon_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );       
        if(is_multisite()) 
        {	
        	if(is_network_admin()){
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
                foreach($blog_ids as $blog_id ) 
                {   
                    self::install_tables_multi_site($blog_id);
                }
            }else{
                $current_blog_id = get_current_blog_id();
                self::install_tables_multi_site($current_blog_id);
            }
        }
        else 
        {
            self::install_tables();
        }
	}

	public static function install_tables_multi_site($blog_id){
		switch_to_blog( $blog_id );
        self::install_tables();
        restore_current_blog();
	}

	public static function install_tables()
	{
		global $wpdb;

		//creating table for saving template data================
        $search_query = "SHOW TABLES LIKE %s";
        $charset_collate = $wpdb->get_charset_collate();
        $tb=Wf_Woocommerce_Packing_List::$template_data_tb;
        $like = '%' . $wpdb->prefix.$tb.'%';
        $table_name = $wpdb->prefix.$tb;


        if(!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) 
        {
            $sql_settings = "CREATE TABLE IF NOT EXISTS `$table_name` (
			  `id_wfpklist_template_data` int(11) NOT NULL AUTO_INCREMENT,
			  `template_name` varchar(200) NOT NULL,
			  `template_html` text NOT NULL,
			  `template_from` varchar(200) NOT NULL,
			  `is_dc_compatible` int(11) NOT NULL DEFAULT '0',
			  `is_active` int(11) NOT NULL DEFAULT '0',
			  `template_type` varchar(200) NOT NULL,
			  `created_at` int(11) NOT NULL DEFAULT '0',
			  `updated_at` int(11) NOT NULL DEFAULT '0',
			  PRIMARY KEY(`id_wfpklist_template_data`)
			) DEFAULT CHARSET=utf8;";
            dbDelta($sql_settings);

        }else
        {
	        $search_query = "SHOW COLUMNS FROM `$table_name` LIKE 'is_dc_compatible'";
	        if(!$wpdb->get_results($search_query,ARRAY_N)) 
	        {
	        	$wpdb->query("ALTER TABLE `$table_name` ADD `is_dc_compatible` int(11) NOT NULL DEFAULT '0' AFTER `template_from`");
	        }
        }    
	}
}
?>