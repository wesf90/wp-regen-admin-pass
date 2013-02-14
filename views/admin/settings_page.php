<?php
/************************************************
* This page shows and manages the admin page
* Version: 1.0
*	Author: Wes Foster
* URL: http://github.com/wesf90
* Feel free to browse around and help build on Github!
* All I ask is that you don't steal, that's bad joojoo.
************************************************/

// Get the form helper
require( WF_PLUGIN_PATH . 'helpers/wf_FormHelper.class.php' );

// Brand the footer
WF_RegenAdminPass::wp_brand_admin_footer();

// The form has been submitted
if ( $_POST )
{
	if ( wp_verify_nonce($_POST['wf-regen_form'], 'wf-regen_form_submit') )
	{
		// Validate the form input. Since the password length field can be a range, we need to ensure that the format is correct.
		$validated_settings = array(
			'pass_length'      => ( preg_match('/^\d+(\.{2,}\d+)?$/', $_POST['pass_length']) ) ? $_POST['pass_length'] : WF_FormHelper::add_error('You\'ve entered an incorrect value for the Password Length'),
			'use_spec_chars'   => (int)$_POST['use_spec_chars'],
			'use_exspec_chars' => (int)$_POST['use_exspec_chars'],
			'interval_days'    => (int)$_POST['interval_days'],
			'admin_id'			   => (int)$_POST['admin_id'],
			'admin_email'      => mysql_real_escape_string($_POST['admin_email']),
			'disable'			     => (int)$_POST['disable']
		);

		// Save the form submission unless we have errors
		if ( WF_FormHelper::no_errors() ){
			// Update all the validated options
			foreach ( $validated_settings as $setting => $value ) {
				WF_RegenAdminPass::update_option($setting, $value);
			}

			// Set the update message
			WF_FormHelper::set_update_message('Your setting have been saved!');
		}

		// Extract the vars to be used in the form below
		extract( $validated_settings );
	}
}
else
{
	// Get the field values for the form.
	// This is the same as writing "$option = WF_RegenAdminPass::get_option('option');" for each option
	extract( WF_RegenAdminPass::get_all_options_for_extract() );
}
?>



<div class="wrap">

	<div id="icon-options-general" class="icon32">&nbsp;</div>	
	<h2><?php _e('Regenerate Admin Password Settings'); ?></h2>

	<?php WF_FormHelper::print_update_message(); ?>
	<?php WF_FormHelper::print_errors(); ?>

	<form action="" method="post">
		<?php wp_nonce_field('wf-regen_form_submit','wf-regen_form'); ?>

		<h3><?php _e('General Options'); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="disable"><?php _e('Disable this plugin?'); ?></label></th>
				<td>
					<input type="hidden" name="disable" value="0">
					<input type="checkbox" name="disable" value="1" <?php WF_FormHelper::print_checkbox_is_checked($disable, '1'); ?>> Disabled
					<p class="description">Since deactivating this plugin will remove all your saved settings, you can easily disable this plugin here without losing your settings.</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="interval_days"><?php _e('Regeneration Cycle:'); ?></label></th>
				<td>
					<input type="text" name="interval_days" value="<?php echo $interval_days; ?>" size="10">
					<p class="description">(Enter a number of days between each password generation)</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="admin_id"><?php _e('Admin ID:'); ?></label></th>
				<td>
					<input type="text" name="admin_id" value="<?php echo $admin_id; ?>" size="20">
					<p class="description">The user id of the admin to be reset. If left blank, will default to 1. (<strong>Your ID is:</strong> <?php echo get_current_user_id(); ?>)</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="admin_email"><?php _e('Admin Email:'); ?></label></th>
				<td>
					<input type="text" name="admin_email" value="<?php echo $admin_email; ?>" size="20">
					<p class="description">The password will be sent here. If left blank, will default to <strong><?php echo get_settings('admin_email'); ?></strong></p>
				</td>
			</tr>
		</table>

		<h3><?php _e('Password Options') ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="pass_length"><?php _e('Password Length:'); ?></label></th>
				<td>
					<input type="text" name="pass_length" value="<?php echo $pass_length; ?>" size="10">
					<p class="description">A single number, or a number range. To create a range, separate the two numbers using two or more periods. eg. 5..7</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="use_spec_chars"><?php _e('Use Special Characters?'); ?></label></th>
				<td>
					<input type="hidden" name="use_spec_chars" value="0">
					<input type="checkbox" name="use_spec_chars" value="1" <?php WF_FormHelper::print_checkbox_is_checked($use_spec_chars, '1'); ?>> Yes
					<p class="description">Includes: <strong>!@#$%^&amp;*()</strong></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="use_exspec_chars"><?php _e('Use Extra Special Characters?'); ?></label></th>
				<td>
					<input type="hidden" name="use_exspec_chars" value="0">
					<input type="checkbox" name="use_exspec_chars" value="1" <?php WF_FormHelper::print_checkbox_is_checked($use_exspec_chars, '1'); ?>> Yes
					<p class="description">Includes: <strong>-_ []{}&lt;&gt;~`+=,.;:/?|'</strong></p>
				</td>
			</tr>
		</table>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"></p>

	</form>

</div>