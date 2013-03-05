<?php

// Administrative Functions
class BracketPressAdmin {

    /** @var BracketPressAdmin */
    static $instance;


    /**
     * Main bracketpress admin
     *
     * Ensures that only one instance exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @return BracketPressAdmin
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new BracketPressAdmin();
            if (is_admin()) {
                self::$instance->setup_admin();
                self::$instance->setup_settings();
            }
        }
        return self::$instance;
    }
    private function __construct() { } // Do nothing, but don't allow instantiation outside the class

    private function add_actions($actions, $priority = 10) {
        //@todo make indirect to allow for plugins to override these functions

        foreach ($actions as $instance_action => $hook) {
            add_action( $hook, array( $this, $instance_action ), $priority );
        }
    }

    private function setup_settings() {
        require_once(bracketpress()->includes_dir . 'admin/settings.php');

        $settings = array(
            array(
                'name' => 'date_brackets_close',
                'type' => 'date',
                'size' => '10',
                'default' => '3/20/2013',
                'label' => 'Day Brackets Close',
                'description' =>  'Which day does the bracket lock? (server date is ' . strftime('%D'). ')'
            ),
            array(
                'name' => 'time_brackets_close',
                'type' => 'time',
                'size' => '10',
                'default' => '23:00',
                'label' => 'Time Brackets Close',
                'description' =>  'What time does the bracket lock in 24 hour format? (server time is ' . strftime('%H:%I'). ')',
            ),
            array(
                'name' => 'points_first_round',
                'type' => 'integer',
                'size' => 8,
                'default' => 1,
                'label' => 'First Round Points',
                'description' => 'How many points are awarded for a correct answer in the 1st round?',
            ),
            array(
                'name' => 'points_second_round',
                'type' => 'integer',
                'size' => 8,
                'default' => 2,
                'label' => 'Second Round Points',
                'description' => 'How many points are awarded for a correct answer in the second round?',
            ),
            array(
                'name' => 'points_third_round',
                'type' => 'integer',
                'size' => 8,
                'default' => 4,
                'label' => 'Third Round Points',
                'description' => 'How many points are awarded for a correct answer in the Sweet 16 round?',
            ),
            array(
                'name' => 'points_fourth_round',
                'type' => 'integer',
                'size' => 8,
                'default' => 8,
                'label' => 'Fourth Round Points',
                'description' => 'How many points are awarded for a correct answer in the Elite Eight round?',
            ),
            array(
                'name' => 'points_fifth_round',
                'type' => 'integer',
                'size' => 8,
                'default' => 16,
                'label' => 'Fifth Round Points',
                'description' => 'How many points are awarded for a correct answer in the Final Four round?',
            ),

            array(
                'name' => 'points_sixth_round',
                'type' => 'integer',
                'size' => 8,
                'default' => 32,
                'label' => 'Sixth Round Points',
                'description' => 'How many points are awarded for a correct answer in the Championship round?',
            ),

            array(
                'name' => 'master_id',
                'type' => 'integer',
                'size' => 8,
                'default' => '1',
                'label' => 'Scoring Bracket',
                'description' => 'Enter the id of the bracket to score against.',
            ),
            array(
                'name' => 'template',
                'type' => 'template_list',
                'size' => 8,
                'default' => '0',
                'label' => 'Template',
                'description' => 'What template would you like to use to display the "Bracket" custom post type? (use drop down)'
            ),
            array(
                'name' => 'edit_title',
                'type' => 'list',
                'size' => 8,
                'default' => '1',
                'label' => 'Allow Title Change',
                'description' => 'Allow users to edit the Title and Excerpt for their bracket',
                'options' => array('0:Do Not Allow Frontend Editing', "1:Allow Frontend Editing")
            ),
            array(
                'name' => 'show_bracketpress_logo',
                'type' => 'list',
                'size' => 8,
                'default' => 'no',
                'label' => 'Show BracketPress Logo',
                'description' => 'Support the plugin! show the BracketPress Logo with a link to the site',
                'options' => array('yes:Show logo and link', "no:Do not show logo")
            ),

        );

        $this->general_settings = new BracketPressSettingsPage(
            'BracketPress', 'bracketpress', $settings
        );

    }

    private function setup_admin() {

        $actions = array(
            'add_pages'         => 'bracketpress_admin_menu',
        );

        $this->add_actions($actions);
    }

    function add_pages() {
        add_submenu_page ( 'edit.php?post_type=brackets', 'BracketPress > Team Data', 'Team Data', 'manage_options', 'bracketpress_teams', array($this, 'forms_page'));
        add_submenu_page ( 'edit.php?post_type=brackets', 'BracketPress > Add Ons', 'Add Ons',     'manage_options', 'bracketpress_addons', array($this, 'addons_page'));
    }

    // Admin Pages

    function forms_page() {

        if( isset($_POST['form_submitter']) ){
            queries::insertBracketData();
            queries::updateBracketData();
        }
        include(bracketpress()->plugin_dir . 'templates/teams.php');
    }

    function location_page()
    {

        if( isset($_POST['location_form_submitter']) )
        {
            queries::insertLocationData();
            queries::updateLocationData();
        }

        include(bracketpress()->plugin_dir . 'templates/locations.php');
    }

    function addons_page() {
        include(bracketpress()->plugin_dir . 'templates/addons.php');
    }

}





