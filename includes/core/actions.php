<?php

/**
 * BracketPress Actions
 *
 * @package BracketPress
 * @subpackage Core
 *
 * This file contains the actions that are used through-out. They are
 * consolidated here to make searching for them easier, and to help developers
 * understand at a glance the order in which things occur.
 * 
 * Based on bbPress
 *
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Attach WordPress
 *
 * uses its own internal actions to help aid in third-party plugin
 * development, and to limit the amount of potential future code changes when
 * updates to WordPress core occur.
 *
 * These actions exist to create the concept of 'plugin dependencies'. They
 * provide a safe way for plugins to execute code *only* when bbPress is
 * installed and activated, without needing to do complicated guesswork.
 *
 * For more information on how this works, see the 'Plugin Dependency' section
 * near the bottom of this file.
 *
 *           v--WordPress Actions        v--bbPress Sub-actions
 */
bracketpress_create_sub_action( 'plugins_loaded',           'bracketpress_loaded',                   10    );
bracketpress_create_sub_action( 'init',                     'bracketpress_init',                     0     ); // Early for bracketpress_register
bracketpress_create_sub_action( 'parse_query',              'bracketpress_parse_query',              2     ); // Early for overrides
bracketpress_create_sub_action( 'parse_query',              'bracketpress_route',                    12    ); // late for routing
bracketpress_create_sub_action( 'widgets_init',             'bracketpress_widgets_init',             10    );
bracketpress_create_sub_action( 'generate_rewrite_rules',   'bracketpress_generate_rewrite_rules',   10    );
bracketpress_create_sub_action( 'wp_enqueue_scripts',       'bracketpress_enqueue_scripts',          10    );
bracketpress_create_sub_action( 'wp_head',                  'bracketpress_head',                     10    );
bracketpress_create_sub_action( 'wp_footer',                'bracketpress_footer',                   10    );
bracketpress_create_sub_action( 'template_redirect',        'bracketpress_template_redirect',        10    );
bracketpress_create_sub_action( 'login_form_login',         'bracketpress_login_form_login',         10    );
bracketpress_create_sub_action( 'user_register',            'bracketpress_user_register',            10    );
bracketpress_create_sub_action( 'admin_init',               'bracketpress_admin_init',            10    );
bracketpress_create_sub_action( 'admin_menu',               'bracketpress_admin_menu',            10    );
bracketpress_create_sub_action( 'admin_notices',            'bracketpress_admin_notices',            10    );


//@todo make anonymous functions when 5.3 is available on all platforms
function bracketpress_create_sub_action($hook, $action, $priority = 10, $params = 0) {

    $callback = "function $action() { \$args = func_get_args(); do_action('$action', \$args); }";
    eval($callback);

    add_action($hook, $action, $priority);
}


