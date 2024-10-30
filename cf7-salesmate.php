<?php
/*
* Root Plugin file
*
* @package   Cf7_Salesmate
* @author    Rapidops Inc.
* @license   GPL-2.0+
* @link      -
*
* @wordpress-plugin
* Plugin Name:  Salesmate Integration for Contact Form 7
* Plugin URI: http://www.rapidops.com/cf7-saleamate
* Description: Salesmate Integration for Contact Form 7 is a minimal plugin that creates a Salesamate Contacts, Company and Deal submitted for you're done.
* Version: 1.7
* Author: Salesmate io.
* Author URI: https://www.salesmate.io/
* Text Domain: cf7-salesmate
* License: GPL - 2.0+
* License URI:   http://www.gnu.org/licenses/gpl-2.0.txt
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*Error activation hooks for error log system*/
function salesmate_cf7_activate() {
	global $wpdb;
  $table_name = $wpdb->prefix.'salesmatelogs';
  $charset_collate = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE $table_name (
	 id mediumint(9) NOT NULL AUTO_INCREMENT,
	 form_id varchar(100),
	 fromodule varchar(255),
	 errlogs varchar(255),
	 trieddata varchar(2000),
	 logat datetime,
	 PRIMARY KEY  (id)
 );";
	 require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
	 dbDelta( $sql );
	 if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
		wp_die( __( 'Contact Form 7 must be installed and activated for the CF7 salesmate plugin to work', 'textdomain' ) );
	}
}
register_activation_hook( __FILE__, 'salesmate_cf7_activate');

define( 'CF7_SALESMATE_PLUGIN_SLUG', 'cf7_salesmate');
define( 'CF7_SALESMATE_PLUGIN_BASENAME', plugin_basename( plugin_dir_path( __FILE__ ) . CF7_SALESMATE_PLUGIN_SLUG . '.php' ) );
// echo CF7_SALESMATE_PLUGIN_BASENAME;
require_once plugin_dir_path( __FILE__ ) . 'class-cf7-salesmate.php';
add_action('plugins_loaded', array( 'Cf7_Salesmate', 'get_instance'), 99999999);
