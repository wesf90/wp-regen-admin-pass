<?php
/************************************************
* The FormHelper for the WF WP Plugin class
* Version: 1.0
*	Author: Wes Foster
* URL: http://github.com/wesf90
* Feel free to browse around and help build on Github!
* All I ask is that you don't steal, that's bad joojoo.
************************************************/

class WF_FormHelper {
	// Variables
	public $update_message;
	public $error_messages = array();


	/**
	 * Prints "checked='checked'" if the $name matches the $expected_value [1.0]
	 * Author: Wes Foster
	 * @params: mixed $name
	 *					mixed $expected_value
	**/
	public function print_checkbox_is_checked($name, $expected_value){
		echo ( $name == $expected_value ) ? ' checked="checked" ' : '';
	}


	/**
	 * Add an error message [1.0]
	 * Author: Wes Foster
	 * @params: string $message
	**/
	public function add_error($message) {
		$this->error_messages[] = $message;
		echo $message;
	}


	/**
	 * Checks if there are any errors in the array or not [1.0]
	 * Author: Wes Foster
	**/
	public function no_errors() {
		return ( empty($this->error_messages) );
	}


	/**
	 * Display the error message(s) [1.0]
	 * Author: Wes Foster
	**/
	public function print_errors() {
		$return_data = '';
		if ( !empty($this->error_messages) ){
			$return_data .= "<div id='form_error_notice' class='error settings-error'><p><strong>The following errors have prevented this form from saving:</strong></p><ul>";

			foreach ($this->error_messages as $error) {
				$return_data .= "<li>{$error}</li>";
			}

			$return_data .= "</ul></div";

			echo $return_data;
		}
	}


	/**
	 * Sets the update message [1.0]
	 * Author: Wes Foster
	 * @params: string $message
	**/
	public function set_update_message($message) {
		$this->update_message = $message;
	}


	/**
	 * Prints the update_message var with WP's notification styling around it [1.0]
	 * Author: Wes Foster
	**/
	public function print_update_message()
	{
		if ( $this->update_message ){
			echo "
				<div id='form_updated_notice' class='updated settings-error'><p><strong>{$this->update_message}</strong></p></div>
			";
		}
	}
}
?>