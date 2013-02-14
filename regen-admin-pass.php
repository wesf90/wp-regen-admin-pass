<?php
/* 
	Plugin Name: Regenerate Admin Password
  Plugin URI: http://
  Description: Regenerates a random password at a configurable time interval and emails it to you. This will ensure that your account is very secure and brute forcing an attack is virtually impossible. Do not get caught without it!
  Author: Wes Foster
  Version: 1.0 
  Author URI: http://github.com/wesf90
*/
/************************************************
* The main plugin file for this plugin
* Version: 1.0
*	Author: Wes Foster
* URL: http://github.com/wesf90
* Feel free to browse around and help build on Github!
* All I ask is that you don't steal, that's bad joojoo.
************************************************/

// Define the plugin path, and load the WF Plugin Class
define( 'WF_PLUGIN_PATH', plugin_dir_path(__FILE__) );
require( WF_PLUGIN_PATH . 'classes/wf_WordpressPlugin.class.php' );

// Create the plugin's own little world
class WF_RegenAdminPass extends WF_WordpressPlugin {
	/**
	 * Initializes the parent class and sets some defaults for this plugin [1.0]
	 * Author: Wes Foster
	**/
	function __construct() {
		parent::__construct(array(
			'filepath'				=> __FILE__,											// This is used for automatically running install/uninstall hooks
			'plugin_title'		=> 'Regenerate Admin Pass',				// The plugin name
			'plugin_version'	=> '1.0',													// The plugin version
			'option_prefix'		=> 'wf-regen_',										// This prefixes all the plugin's options
			'default_options' => array(													// This is all the default options for the plugin
															'pass_length'      => 9,
															'use_spec_chars'   => 1,
															'use_exspec_chars' => 0,
															'interval_days'    => 30,
															'admin_id' 		     => 1,
															'admin_email'      => get_option('admin_email'),
															'disable'          => 1
													 )
		));

		// Run the plugin
		$this->init_plugin();
	}


	///// VARS
	public $new_password;


	/**
	 * Initializes this plugin [1.0]
	 * Author: Wes Foster
	**/
	public function init_plugin()
	{
		// Add admin pages
		add_action('admin_menu', array($this, 'add_admin_settings_page') );

		// Add deactivation of crons on uninstall
		register_uninstall_hook( __FILE__, array($this, 'uninstall_plugin') );

		// Setup the cron job, if the plugin isn't disabled
		if ( $this->get_option('disable') == 0 )
		{
			// Creates the interval schedule to use
			add_filter( 'cron_schedules', array($this, 'add_cron_schedules') );

			// Adds the action to the cron job
			add_action( 'cron_hook_regen_admin_pass', array($this, 'cron_function_update_user_to_change_pass') );

			/*debug*
			add_action( 'cron_hook_regen_admin_pass_test', array($this, 'cron_function_update_user_to_change_pass') );
			/**/

			// This adds a check to each page load to see if we should generate a new password.
			add_action( 'init', array($this, 'init_generate_and_mail_pass') );

			// Create the cron-job timer
			$this->schedule_the_cron_job();
		}
	}


	/**
	 * The function to call when this plugin is uninstalled
	 * Autho: Wes Foster
	**/
	function uninstall_plugin()
	{
		wp_clear_scheduled_hook('cron_wf_regen_admin_pass');
		$this->remove_scheduled_cron();
	}


	///////////////////////////
	// ADMIN PANEL FUNCTIONS
	/**
	 * Add the menu item in the admin panel [1.0]
	 * Author: Wes Foster
	**/
	function add_admin_settings_page() {
	    add_options_page( 'Regenerate Admin Password Settings', 'Regen Admin Pass', 'administrator', 'regen-admin-pass', array($this, 'display_settings_page') );  
	}

	/**
	 * Includes the file that is used for the "Regen Admin Pass" page content [1.0]
	 * Author: Wes Foster
	**/
	function display_settings_page() {
		include(WF_PLUGIN_PATH . 'views/admin/settings_page.php');
	}



	///////////////////////////
	// CRON FUNCTIONS
	/**
	 * Creates a custom schedule interval for use in schedule_the_cron_job()  [1.0]
	 * Author: Wes Foster
	**/
	function add_cron_schedules( $schedules )
	{
		$schedules['cron_schedule_admin_regen_pass'] = array(
			'interval' => 86400 * (int)$this->get_option('interval_days'),
			'display'  => __('Every so many days, based on admin pass plugin')
		);

		/*debug*
		$schedules['cron_schedule_admin_regen_pass_test'] = array(
			'interval' => 60 * (int)$this->get_option('interval_days'),
			'display'  => __('Every so many MINS DEBUG, based on admin pass plugin')
		);
		/**/

		return $schedules;
	}


	/**
	 * Schedules the cron job inside of WP [1.0]. This creates the action hook so we can attach a function to it above
	 * Author: Wes Foster
	**/
	function schedule_the_cron_job()
	{
		// $this->remove_scheduled_cron();
		if ( !wp_next_scheduled( 'cron_hook_regen_admin_pass' ) ) {
			wp_schedule_event( time(), 'cron_schedule_admin_regen_pass', 'cron_hook_regen_admin_pass' );
		}

		/*debug*
		if ( !wp_next_scheduled( 'cron_hook_regen_admin_pass_test' ) ) {
			wp_schedule_event( time(), 'cron_schedule_admin_regen_pass_test', 'cron_hook_regen_admin_pass_test' );
		}
		/**/
	}


	/**
	 * Removed the scheduled cron job
	 * Author: Wes Foster
	**/
	function remove_scheduled_cron()
	{
		$timestamp = wp_next_scheduled( 'cron_hook_regen_admin_pass' );
		wp_unschedule_event($timestamp, 'cron_hook_regen_admin_pass' );

		/*debug*
		$timestamp = wp_next_scheduled( 'cron_hook_regen_admin_pass_test' );
		wp_unschedule_event($timestamp, 'cron_hook_regen_admin_pass_test' );
		/**/
	}


	/**
	 * This function is ran on each page load. If the user needs a new password, then we generate and mail it. Otherwise, nothing happens. [1.0]
	 * Author: Wes Foster
	**/
	function init_generate_and_mail_pass()
	{
		if ( $this->time_to_change_the_password() && $this->get_option('disable') == 0 )
		{
			$this->new_password = $this->generate_password();
			/*debug*/
			wp_set_password( $this->new_password, $this->get_option('admin_id') );
			/**/

		  $this->update_user_meta_password_was_changed();
		  wp_mail( $this->get_option('admin_email'), get_bloginfo('name') . ' Password Automatically Regenerated', "For your safety, your password has been automatically regenerated according to your preferences for the Regenerate Admin Password plugin.\n\nYour new password is: " . $this->new_password . "\n\nStay Safe!\n Compliments of Regen Admin Pass");

		  /*debug*
		  @mail( 'wesfed@gmail.com', get_bloginfo('name') . ' Password Automatically Regenerated', "For your safety, your password has been automatically regenerated according to your preferences for the Regenerate Admin Password plugin.\n\nYour new password is: " . $this->new_password . "\n\nStay Safe!\n Compliments of Regen Admin Pass");
			/**/
		}
	}


	/**
	 * Update the user to change the password. This is triggered by the cron job
	**/
	function cron_function_update_user_to_change_pass(){
		$this->update_user_meta_to_change_pass();
	}


	/**
	 * Updates the user's meta so that we can change the password on the next INIT action call [1.0]
	 * Author: Wes Foster
	**/
	function time_to_change_the_password() {
		return get_user_meta( $this->get_option('admin_id'), '_cron_change_password', true);
	}


	/**
	 * Updates the user's meta so that we can change the password on the next INIT action call. [1.0]
	 * Author: Wes Foster
	**/
	function update_user_meta_to_change_pass() {
		update_user_meta( $this->get_option('admin_id'), '_cron_change_password', 1);
		return true;
	}


	/**
	 * Updates the user's meta once we have changed the password [1.0]
	 * Author: Wes Foster
	**/
	function update_user_meta_password_was_changed() {
		update_user_meta( $this->get_option('admin_id'), '_cron_change_password', 0);
	}


	///////////////////////////
	// MISC FUNCTIONS
	/**
	 * Generate Password [1.0]
	 * Author: Wes Foster
	**/
	function generate_password()
	{
		$length              = $this->get_option('pass_length');
		$special_chars       = $this->get_option('use_spec_chars');
		$extra_special_chars = $this->get_option('use_exspec_chars');

		// Determine the range of the length
		if ( strstr($length, '..') ){
			preg_match('/^(\d+)\.{2,}(\d+)$/', $length, $match);
			list( $length, $min, $max ) = $match;
			$length = round( rand($min, $max) );
		}

		return wp_generate_password( $length, $special_chars, $extra_special_chars );
	}

}


// Run this class in the real world.
new WF_RegenAdminPass;
?>