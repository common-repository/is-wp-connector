<?php
/*
Adds option page to plugin in order to input API
*/

add_action('admin_init', 'infusion_api_init' );
add_action('admin_menu', 'infusion_api_add_page');

// Add menu page
function infusion_api_add_page() {
	add_options_page('InfusionSoft-WordPress Connector Options', 'InfusionSoft', 'manage_options', 'infusion_api', 'infusion_api_do_page');

}

// Draw the menu page itself
function infusion_api_do_page() {
	?>
	<div class="wrap">
		<h2>InfusionSoft-WordPress Connector Options</h2>

		<?php
			if ( isset ( $_GET['tab'] ) ) infusion_api_settings_tabs($_GET['tab']); else infusion_api_settings_tabs('homepage');
		?>
		<div id="tab-area">
			<?php
			if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab']; 
					else $tab = 'homepage'; 

			echo '<table class="form-table">';

			switch ( $tab ) {

				case 'homepage':
					?>
					<tr valign="top"><th scope="row">InfusionSoft-to-WordPress</th>
						<td>This tab contains information your InfusionSoft application will need in order to send commands to your WordPress installation. You can also use this tab to configure outgoing email messages from WordPress that are triggered by commands from InfusionSoft.</td>
					</tr>
					<tr valign="top"><th scope="row">WordPress-to-InfusionSoft</th>
						<td>This tab allows this WordPress installation to communicate with your InfusionSoft application. Please read how to <a href="http://ug.infusionsoft.com/article/AA-00442">set up your InfusionSoft API</a> if you have questions.</td>
					</tr>
					<?php break;

				case 'infusion':
					?><form method="post" action="options.php">
						<?php settings_fields('infusion_api_options'); ?>
						<?php $options = get_option('infusion_api'); ?>
					
						<tr valign="top"><th scope="row">WordPress API Key</th>
							<td><input type="text" name="infusion_api[wordpress_key]" value="<?php echo $options['wordpress_key']; ?>" />
								<label class="description" for="infusion_api_options[wordpress_key]"><?php _e( 'Alphanumeric input only', 'infusion_api' ); ?></label></td>
						</tr>
						<tr valign="top"><th scope="row">Error Notification Email</th>
							<td><input type="text" name="infusion_api[email_error]" value="<?php echo $options['email_error']; ?>" />
								<label class="description" for="infusion_api_options[email_error]"><?php _e( 'If blank, errors will be sent to the blog administrator email', 'infusion_api' ); ?></label></td>
						</tr>
						<tr valign="top"><th scope="row">New User Notification Email</th>
							<td><textarea name="infusion_api[new_email]" rows="10" cols="50"><?php echo(stripslashes($options['new_email'])); ?></textarea><br />
								<label class="description" for="infusion_api_options[new_email]"><?php _e( 'If blank, users will be sent a default new user email.<br />The email is plain text, but you may use any of the following variables in the email:<br />%username% (e.g., John Smith)<br />%accountname% (WordPress internal user name)<br />%email% (e.g., johnsmith@sample.com)<br />%password% (e.g., ABCabc123)', 'infusion_api' ); ?></label></td>
						</tr>
						<tr valign="top"><th scope="row">Existing User Notification Email</th>
							<td><textarea name="infusion_api[existing_email]" rows="10" cols="50"><?php echo(stripslashes($options['existing_email'])); ?></textarea><br />
								<label class="description" for="infusion_api_options[existing_email]"><?php _e( 'This email goes out when existing users have a new permission added via the API. If blank, users will not be sent an email notification.<br />The email is plain text, but you may use any of the following variables in the email:<br />%username% (e.g., John Smith)<br />%accountname% (WordPress internal user name)<br />%email% (e.g., johnsmith@sample.com)<br /><b>Passwords are not supported in the existing user email.</b>', 'infusion_api' ); ?></label></td>
						</tr>
						<input type="hidden" name="tab" value="infusion" />
					<?php break;

				case 'wordpress':
					?><form method="post" action="options.php">
						<?php settings_fields('infusion_api_options'); ?>
						<?php $options = get_option('infusion_api'); ?>
						
						<tr valign="top"><th scope="row">InfusionSoft API Key</th>
							<td><input type="text" name="infusion_api[infusion_key]" value="<?php echo $options['infusion_key']; ?>" />
								<label class="description" for="infusion_api_options[infusion_key]"><?php _e( 'Your InfusionSoft API key (generated within InfusionSoft)', 'infusion_api' ); ?></label></td>
						</tr>
						<tr valign="top"><th scope="row">Application Name</th>
							<td><input type="text" name="infusion_api[app_name]" value="<?php echo $options['app_name']; ?>" />
								<label class="description" for="infusion_api_options[app_name]"><?php _e( '"appname" in https://<b>appname</b>.infusionsoft.com/', 'infusion_api' ); ?></label></td>
						</tr>
						<tr valign="top"><th scope="row">"Never Signed In" tag ID</th>
							<td><input type="text" name="infusion_api[never_tag_id]" value="<?php echo $options['never_tag_id']; ?>" />
								<label class="description" for="infusion_api_options[never_tag_id]"><?php _e( 'Tag ID to be added if someone has never signed in', 'infusion_api' ); ?></label></td>
						</tr>
						<input type="hidden" name="tab" value="wordpress" />
					<?php break;
			}

			echo '</table>';
			if( $tab != 'homepage' ) {
				?><p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
				</form>
			<?php }
			?>
		</div>
	</div>
	<?php	
}

// Init plugin options to white list our options
function infusion_api_init() {
	register_setting( 'infusion_api_options', 'infusion_api', 'infusion_api_validate' );
}

function infusion_api_settings_tabs( $current = 'homepage' ) {
	$tabs = array( 
		'homepage' => 'Overview', 
		'infusion' => 'InfusionSoft-to-WordPress', 
		'wordpress' => 'WordPress-to-InfusionSoft'
	);
	echo '<h2 class="nav-tab-wrapper">';
	foreach( $tabs as $tab => $name ){
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab$class' href='?page=infusion_api&tab=$tab'>$name</a>";
	}
	echo '</h2>';
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function infusion_api_validate($input) {

	$options = get_option('infusion_api');

	$tab = $_POST['tab'];

	$input['wordpress_key'] = ( $tab == 'infusion' ) ? wp_filter_nohtml_kses($input['wordpress_key']) : $options['wordpress_key'];
	$input['email_error'] = ( $tab == 'infusion' ) ? sanitize_email($input['email_error']) : $options['email_error'];
	$input['new_email'] = ( $tab == 'infusion' ) ? wp_filter_nohtml_kses($input['new_email']) : $options['new_email'];
	$input['existing_email'] = ( $tab == 'infusion' ) ? wp_filter_nohtml_kses($input['existing_email']) : $options['existing_email'];
	$input['infusion_key'] = ( $tab == 'wordpress' ) ? wp_filter_nohtml_kses($input['infusion_key']) : $options['infusion_key'];
	$input['app_name'] = ( $tab == 'wordpress' ) ? wp_filter_nohtml_kses($input['app_name']) : $options['app_name'];
	$input['never_tag_id'] = ( $tab == 'wordpress' ) ? wp_filter_nohtml_kses($input['never_tag_id']) : $options['never_tag_id'];

	return $input;
}