<?php

/**
 * Display Registration widget.
 *
 * Displays Registration Form.
 *
 * @param array $args Widget arguments.
 */
function bracketpress_registration_widget($args) {
    global $pagenow;

    if ($_GET['action'] != 'register') { // we don't want to display this form in the Registration Page

        $args = shortcode_atts( array(
            'before_widget' => '',
            'before_title'  => '',
            'title' =>  __('Registration'),
            'after_widget' => '',
            'after_title'  => '',
        ), $args );

        $options = get_option('bracketpress_widget_registration');
        $title = empty($options['title']) ? $args['title'] : apply_filters('widget_title', $options['title']);

        print $args['before_widget'] . "\n";
        print $args['before_title'] . $title . $args['after_title'] . "\n";
        the_bracketpress_registration_form($args);
        print $args['after_widget'] . "\n";
    }
}

function the_bracketpress_registration_form($args = null) {
    $register_button = __('Register');
    if (isset($args['register_button'])) $register_button = $args['register_button'];
    ?>
<form class="registerform" name="registerform" id="registerform"
      action="<?php echo site_url('wp-login.php?action=register', 'login_post') ?>" method="post">
    <input type="hidden" name="bracketpress_register" value="true" />
    <p>
        <label><?php _e('Username') ?>:</label><br/>
        <input tabindex="1" type="text" name="user_login" id="user_login" class="input"
               value="<?php echo esc_attr(stripslashes($user_login)); ?>" size="20" tabindex="10"/>
        <br/>

        <label for="user_email" id="user_email_label"><?php _e('E-mail') ?>:</label><br/>
        <input tabindex="2" type="text" name="user_email" id="user_email" class="input"
               value="<?php echo esc_attr(stripslashes($user_email)); ?>" size="25" tabindex="20"/>
        <br/>
    </p>

    <?php do_action('bracketpress_register_form'); ?>
    <p id="reg_passmail">
        <?php _e('A password will be e-mailed to you.') ?>
    </p>

    <p class="submit"><input tabindex="4" type="submit" name="wp-submit" id="wp-submit"
                             value="<?php echo($register_button) ?>" tabindex="100"/></p>
</form>
<?php
}

/**
 * HERE FOR REFERENCE wp3.5
 *
 * Handles registering a new user.
 *
 * @param string $user_login User's username for logging in
 * @param string $user_email User's email address to send password and add
 * @return int|WP_Error Either user's ID or error on failure.
 */
function bracketpress_NOT_USED_register_new_user( $user_login, $user_email ) {
    $errors = new WP_Error();

    $sanitized_user_login = sanitize_user( $user_login );
    $user_email = apply_filters( 'user_registration_email', $user_email );

    // Check the username
    if ( $sanitized_user_login == '' ) {
        $errors->add( 'empty_username', __( '<strong>ERROR</strong>: Please enter a username.' ) );
    } elseif ( ! validate_username( $user_login ) ) {
        $errors->add( 'invalid_username', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
        $sanitized_user_login = '';
    } elseif ( username_exists( $sanitized_user_login ) ) {
        $errors->add( 'username_exists', __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.' ) );
    }

    // Check the e-mail address
    if ( $user_email == '' ) {
        $errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please type your e-mail address.' ) );
    } elseif ( ! is_email( $user_email ) ) {
        $errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.' ) );
        $user_email = '';
    } elseif ( email_exists( $user_email ) ) {
        $errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.' ) );
    }

    do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

    $errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

    if ( $errors->get_error_code() )
        return $errors;

    $user_pass = wp_generate_password( 12, false);
    $user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
    if ( ! $user_id ) {
        $errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !' ), get_option( 'admin_email' ) ) );
        return $errors;
    }

    update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

    wp_new_user_notification( $user_id, $user_pass );

    return $user_id;
}


/**
 * Display and process registration widget options form.
 *
 */
function bracketpress_registration_widget_control() {
    $options = $newoptions = get_option('bracketpress_widget_registration');
    if (isset($_POST["registration-submit"])) {
        $newoptions['title'] = strip_tags(stripslashes($_POST["registration-title"]));
    }
    if ($options != $newoptions) {
        $options = $newoptions;
        update_option('bracketpress_widget_registration', $options);
    }
    $title = esc_attr($options['title']);
    ?>
<p><label for="registration-title"><?php _e('Title:'); ?> <input class="widefat" id="registration-title"
                                                                 name="registration-title" type="text"
                                                                 value="<?php echo $title; ?>"/></label></p>
<input type="hidden" id="registration-submit" name="registration-submit" value="1"/>
<?php
}

function bracketpress_registration_widget_init() {


//    register_sidebar_widget("BracketPress Registration", "registration_widget");
//    register_widget_control("BracketPress Registration", "registration_widget_control");

    wp_register_sidebar_widget('bracketpress_register', 'BracketPress Registration', 'bracketpress_registration_widget');
    wp_register_widget_control('bracketpress_register', 'BracketPress Registration', 'bracketpress_registration_widget_control');
}

add_action("plugins_loaded", "bracketpress_registration_widget_init");

function bracketpress_register_shortcode() {
    bracketpress_registration_widget(array());
}
add_shortcode('bracketpress_register', 'bracketpress_register_shortcode');

function bracketpress_action_before_register($user_login, $user_email, $errors) {

    if (isset($_POST['bracketpress_register'])) {
      $userdata['user_login'] = $user_login;
      $userdata['user_email'] = $user_email;
      $userdata['errors'] = $errors;
    }
    do_action('bracketpress_before_register', $userdata);
}
add_action('register_post', 'bracketpress_action_before_register', 10, 3);

function bracketpress_action_after_register($user_id) {
    if (isset($_POST['bracketpress_register'])) {
      $user_info = get_userdata($user_id);
      do_action('bracketpress_after_register', (array) $user_info->data);
    }
}
add_action('user_register', 'bracketpress_action_after_register', 10, 1);







