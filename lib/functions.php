<?php
/**
 * General purpose functions
 *
 * @package BracketPress
 * @subpackage Functions
 */

/**
 * create a bracket post
 * @deprecated
 *
 * @return int
 */
function bracketpress_create_post() {
    return bracketpress()->create_bracket_post();
}

/**
 * Display the excerpt
 * @param $excerpt
 *
 * @todo If we decide to use a template for the excerpt view, then we need to update this with file and correct path. SMH 1-5-2013
 */

function bracketpress_the_excerpt($excerpt) {
//

    /*
        // If we're not viewing the bracket custom post type, we can skip our override.
        if ( get_post_type() != 'brackets' )
            return $excerpt;

        $file =  BRACKETPRESS_PATH_TEMPLATES . '/excerpt.php' ;
        ob_start();
        include($file);
        $output = ob_get_clean();
        echo $output;

        return;
    */
}
//add_filter( 'the_excerpt', 'bracketpress_the_excerpt' );


function bracketpress_login() {
    $args = array(
        'echo' => true,
        'redirect' => site_url( $_SERVER['REQUEST_URI'] ),
        'form_id' => 'loginform',
        'label_username' => __( 'Username' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in' => __( 'Log In' ),
        'id_username' => 'user_login',
        'id_password' => 'user_pass',
        'id_remember' => 'rememberme',
        'id_submit' => 'wp-submit',
        'remember' => true,
        'value_username' => NULL,
        'value_remember' => false );

    wp_login_form( $args );

    //Only displays if Settings -> Anyone can register is checked.
    wp_register();

    return;
}

/**
 * Added by SMH 1-5-2013
 * completed with settings NLT 1-26-2013
 * See: http://codex.wordpress.org/Plugin_API/Filter_Reference/single_template
 *
 * @param $path
 * @return string
 *
 */
function bracketpress_use_my_template($path) {

    $template = bracketpress()->get_option('template');

    if ($template) {
        if( is_singular( 'brackets' ) ) {  // NLT
            $path = trailingslashit(TEMPLATEPATH) . $template;
        }

        if( is_archive( 'brackets' ) ) {
            //May want to change this to a special template that shows scores.
            $path = trailingslashit(TEMPLATEPATH) . $template;
        }
    }
    return $path;
}
add_filter( 'template_include', 'bracketpress_use_my_template' );

/**
 * Format a team name so it will fit in the bracket
 *
 * @param $str
 * @return mixed
 */
function bracketpress_display_name($str, $size = 16) {
    $name = apply_filters( 'bracketpress_display_name', $str);
    if ($name == $str) {
        $name = str_replace(' ', '&nbsp;', mb_substr($str, 0, $size));
    }
    return $name;
}

// Where are the match queries?
// They were moved out to install/includes/models/match