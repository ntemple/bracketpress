<?php

add_action( 'admin_init', 'bracketpress_login_options_init' );
add_action( 'admin_menu', 'bracketpress_login_options_add_page' );

/**
 * Define Options
 */
global $bracketpress_login_options;

$bracketpress_login_options = (
	array( 
		array(
			'', 
			array(
				array(
					'name' 		=> 'bracketpresslogin_heading',
					'std' 		=> __('Login', 'bplogin'),
					'label' 	=> __('Logged out heading', 'bplogin'),
					'desc'		=> __('Heading for the widget when the user is logged out.', 'bplogin')
				),
				array(
					'name' 		=> 'bracketpresslogin_welcome_heading',
					'std' 		=> __('Welcome %username%', 'bplogin'),
					'label' 	=> __('Logged in heading', 'bplogin'),
					'desc'		=> __('Heading for the widget when the user is logged in.', 'bplogin')
				),
			)
		),
		array(
			__('Redirects', 'bplogin'),
			array(
				array(
					'name' 		=> 'bracketpresslogin_login_redirect',
					'std' 		=> '', 
					'label' 	=> __('Login redirect', 'bplogin'),
					'desc'		=> __('Url to redirect the user to after login. Leave blank to use the current page.', 'bplogin'),
					'placeholder' => 'http://'
				),
				array(
					'name' 		=> 'bracketpresslogin_logout_redirect',
					'std' 		=> '', 
					'label' 	=> __('Logout redirect', 'bplogin'),
					'desc'		=> __('Url to redirect the user to after logout. Leave blank to use the current page.', 'bplogin'),
					'placeholder' => 'http://'
				),
			)
		),
		array(
			__('Links', 'bplogin'),
			array(
				array(
					'name' 		=> 'bracketpresslogin_register_link',
					'std' 		=> '1', 
					'label' 	=> __('Show Register Link', 'bplogin'),
					'desc'		=> sprintf( __('The <a href="%s" target="_blank">\'Anyone can register\'</a> setting must be turned on for this option to work.', 'bplogin'), admin_url('options-general.php')),
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> 'bracketpresslogin_forgotton_link',
					'std' 		=> '1', 
					'label' 	=> __('Show Lost Password Link', 'bplogin'),
					'desc'		=> '',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> 'bracketpress_login_avatar',
					'std' 		=> '1', 
					'label' 	=> __('Show Logged in Avatar', 'bplogin'),
					'desc'		=> '',
					'type' 		=> 'checkbox'
				),
				array(
					'name' 		=> 'bracketpresslogin_logged_in_links',
					'std' 		=> "<a href=\"".get_bloginfo('wpurl')."/wp-admin/\">".__('Dashboard','bplogin')."</a>\n<a href=\"".get_bloginfo('wpurl')."/wp-admin/profile.php\">".__('Profile','bplogin')."</a>",
					'label' 	=> __('Logged in links', 'bplogin'),
					'desc'		=> sprintf( __('One link per line. Note: Logout link will always show regardless. Tip: Add <code>|true</code> after a link to only show it to admin users or alternatively use a <code>|user_capability</code> and the link will only be shown to users with that capability (see <a href=\'http://codex.wordpress.org/Roles_and_Capabilities\' target=\'_blank\'>Roles and Capabilities</a>).<br/> You can also type <code>%%USERNAME%%</code> and <code>%%USERID%%</code> which will be replaced by the user\'s info. Default: <br/>&lt;a href="%s/wp-admin/"&gt;Dashboard&lt;/a&gt;<br/>&lt;a href="%s/wp-admin/profile.php"&gt;Profile&lt;/a&gt;', 'bplogin'), get_bloginfo('wpurl'), get_bloginfo('wpurl') ),
					'type' 		=> 'textarea'
				),
			)
		)
	)
);
	
/**
 * Init plugin options to white list our options
 */
function bracketpress_login_options_init() {

	global $bracketpress_login_options;

	foreach($bracketpress_login_options as $section) {
		foreach($section[1] as $option) {
			if (isset($option['std'])) add_option($option['name'], $option['std']);
			register_setting( 'bracketpress-login', $option['name'] );
		}
	}

	
}

/**
 * Load up the menu page
 */
function bracketpress_login_options_add_page() {
	add_submenu_page ( 'edit.php?post_type=brackets', 'BracketPress > Login Settings', 'Login Widget', 'manage_options', 'bracketpress-login', 'bracketpress_login_options');
	//add_options_page(__('BracketPress Login','bplogin'), __('BracketPress Login','bplogin'), 'manage_options', 'bracketpress-login', 'bracketpress_login_options');
}

/**
 * Create the options page
 */
function bracketpress_login_options() {
	global $bracketpress_login_options;

	if ( ! isset( $_REQUEST['settings-updated'] ) ) $_REQUEST['settings-updated'] = false;

	?>
	<div class="wrap">
		<?php screen_icon(); echo "<h2>" .__( 'BracketPress Login Options','bplogin') . "</h2>"; ?>
		
		<form method="post" action="options.php">
		
			<?php settings_fields( 'bracketpress-login' ); ?>
	
			<?php
			foreach($bracketpress_login_options as $section) {
			
				if ($section[0]) echo '<h3 class="title">'.$section[0].'</h3>';
				
				echo '<table class="form-table">';
				
				foreach($section[1] as $option) {
					
					echo '<tr valign="top"><th scope="row">'.$option['label'].'</th><td>';
					
					if (!isset($option['type'])) $option['type'] = '';
					
					switch ($option['type']) {
						
						case "checkbox" :
						
							$value = get_option($option['name']);
							
							?><input id="<?php echo $option['name']; ?>" name="<?php echo $option['name']; ?>" type="checkbox" value="1" <?php checked( '1', $value ); ?> /><?php
						
						break;
						case "textarea" :
							
							$value = get_option($option['name']);
							
							?><textarea id="<?php echo $option['name']; ?>" class="large-text" cols="50" rows="10" name="<?php echo $option['name']; ?>" placeholder="<?php if (isset($option['placeholder'])) echo $option['placeholder']; ?>"><?php echo esc_textarea( $value ); ?></textarea><?php
						
						break;
						default :
							
							$value = get_option($option['name']);
							
							?><input id="<?php echo $option['name']; ?>" class="regular-text" type="text" name="<?php echo $option['name']; ?>" value="<?php esc_attr_e( $value ); ?>" placeholder="<?php if (isset($option['placeholder'])) echo $option['placeholder']; ?>" /><?php
						
						break;
						
					}
					
					if ($option['desc']) echo '<span class="description">'.$option['desc'].'</span>';
					
					echo '</td></tr>';
				}
				
				echo '</table>';
				
			}
			?>

			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'bplogin'); ?>" />
			</p>
		</form>
	</div>
	<?php
}