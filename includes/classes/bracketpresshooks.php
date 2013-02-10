<?php

/**
 * This class is used to document (and test)
 * the various hooks available in bracketpress, and
 * shows examples of usage.
 *
 * The class itself is not used, but it's great for copy & paste code.
 */
// $bracketpressHooks = new BracketPressHooks();

class BracketPressHooks {

    function __construct() {
        // Test all our hooks


        // Registration
        add_action( 'bracketpress_register_form',   array($this, 'bracketpress_register_form'));
        add_action( 'bracketpress_before_register', array($this, 'bracketpress_before_register') , 10, 1);
        add_action( 'bracketpress_after_register',  array($this, 'bracketpress_after_register') , 10, 1);

    }

    function bracketpress_register_form() {
        print "bracketpress_register_form hook called()<br>\n.";
    }

    /**
     * Called before registration is performed.
     *
     * @param $userdata sanitized login and email, plus any errors
     */
    function bracketpress_before_register($userdata) {
        print "<pre>\n";
        print "before register";
        print_r($userdata);
        print"</pre>\n";
    }

    /**
     * @param $userdata array of the user information from the db
     */

    function bracketpress_after_register($userdata) {
        //get_user_by('login', $user);

        print "<pre>\n";
        print "after register\n";
        print_r($userdata);
        print"</pre>\n";
    }

}
