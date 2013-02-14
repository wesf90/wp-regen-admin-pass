<?php
/************************************************
* This is the main file for child plugin classes.
* Version: 1.0
*	Author: Wes Foster
* URL: http://github.com/wesf90
* Feel free to browse around and help build on Github!
* All I ask is that you don't steal, that's bad joojoo.
************************************************/

class WF_WordpressPlugin {
	// Variables
	var $wf_options = array();
	var $prefix = '';


	/**
	 * The initializer [1.0]
	 * Author: Wes Foster
	 * @params: array $options
	**/
	public function __construct($options)
	{
		$this->wf_options = $options;

		// Set internal vars for quicker access
		$this->prefix = $options['option_prefix'];

		// Install/Uninstall hook
		register_activation_hook( $options['filepath'], array(&$this, 'install_options') );
		register_uninstall_hook( $options['filepath'], array(&$this, 'uninstall_options') );
	}


	/**
	 * Brand the footer in the admin panel
	 * Autho: Wes Foster
	**/
	public function brand_admin_footer() {
		echo $this->wf_options['plugin_title'] . ' ' . $this->wf_options['plugin_version'] . ' was created by <a href="http://github.com/wesf90">Wes Foster</a>. Enjoy!';
	}

	
	/**
	 * The function that hooks into WP and brands the footer
	 * Autho: Wes Foster
	**/
	public function wp_brand_admin_footer(){
		add_filter('admin_footer_text', array(&$this, 'brand_admin_footer'));
	}
	


	/**
	 * Sets up default options and run other functions on plugin activation [1.0]
	 * Author: Wes Foster
	**/
	public function install_options() {
		foreach ($this->wf_options['default_options'] as $option => $value) {
			update_option( $this->prefix . $option, $value );
		}
	}


	/**
	 * Removed default options and runs other functions on plugin deactivation [1.0]
	 * Author: Wes Foster
	**/
	public function uninstall_options() {
		foreach ($this->wf_options['default_options'] as $option => $value) {
			delete_option( $this->prefix . $option );
		}
	}


	/**
	 * This modifies WP's default get_option function and prefixes the option name [1.0]
	 * Author: Wes Foster
	**/
	public function get_option($option, $default = '') {
		return get_option($this->prefix . $option, $default);
	}


	public function update_option($option, $value) {
		return update_option($this->prefix . $option, $value);
	}


	public function get_all_options_for_extract()
	{
		$return_data = array();

		foreach ( $this->wf_options['default_options'] as $option => $value ){
			$return_data[$option] = $this->get_option($option, $value);
		}

		return $return_data;
	}
	//--

}
?>