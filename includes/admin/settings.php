<?php

/**
 * Create a wordpress OptionsPage from
 * an array of fields.
 */

class BracketPressSettingsPage {

    var $prefix;
    var $params;

    var $PLUGINS_HANDLE;
    var $OPTIONS_HANDLE;
    var $SECTION_HANDLE;

    var $name = 'BracketPress';
    var $plugin_name = 'bracketpress';
    var $page_name;

    var $updated = false;


    function __construct($name, $plugin_name, $params, $page_name = 'Settings & Scoring') {

        $this->name = $name;
        $this->params = $params;
        $this->plugin_name = $plugin_name;
        $this->page_name = $page_name;

        $prefix = strtolower($this->plugin_name);
        $this->prefix = $prefix;


        $this->PLUGINS_HANDLE = $prefix . "_plugins";
        $this->OPTIONS_HANDLE = $prefix . "_options";
        $this->SECTION_HANDLE = $prefix . "_section";

        $this->updated = get_transient($this->OPTIONS_HANDLE . '_u');

        add_action('bracketpress_admin_init', array($this, 'admin_init'));
        add_action('bracketpress_admin_menu', array($this, 'admin_add_page'));
        add_action('bracketpress_admin_notices', array($this, 'admin_notice'));
    }

    function admin_add_page() {
//        add_submenu_page ( 'edit.php?post_type=brackets', "BracketPress > {$this->name}", $this->name, 'manage_options', $this->plugin_name, array($this, 'settings_page'));
        add_submenu_page ( 'edit.php?post_type=brackets', "BracketPress > {$this->name}", $this->page_name, 'manage_options', $this->plugin_name, array($this, 'settings_page'));
    }

    function admin_init() {

        // One settings group for now. Eventually allow split into pages
        add_settings_section($this->SECTION_HANDLE, "{$this->name} Settings", array($this, 'main_section_text'), $this->PLUGINS_HANDLE);

        // We only have one setting
        register_setting ( $this->OPTIONS_HANDLE, $this->OPTIONS_HANDLE, array($this, 'validate') );

        foreach ($this->params as $param) {

            add_settings_field(
                $this->prefix . '_setting_' . $param['name'],
                $param['label'],
                array($this, 'displaySetting'),
                $this->PLUGINS_HANDLE,
                $this->SECTION_HANDLE,
                $param
            );

        }
    }

    /**
     * @param $param create an input widget
     */

    function displaySetting($param) {
        $options = get_option($this->OPTIONS_HANDLE);
        $name =  $param['name'];
        $value = $options[$name];

        if (! $value && isset($param['default'])) $value = $param['default'];

        // Convert complex types into basic types
        switch($param['type']) {
            case 'template_list':
                $template_options = array('0:Let WordPress Choose');
                $templates = get_page_templates();
                foreach($templates as $template_name => $template_filename ) {
                    $template_options[] = "$template_filename:$template_name";
                }
                $param['options'] = $template_options;
                $param['type'] = 'list';
                break;
            case 'date': //@todo make date picker work
/*
<link rel="stylesheet" href="<?php echo BRACKETPRESS_CSS_URL ?>jquery-ui.css" type="text/css" />
<script src="<?php echo BRACKETPRESS_CSS_URL ?>jquery-1.8.3.js"></script>
<script src="<?php echo BRACKETPRESS_CSS_URL ?>jquery-ui.js"></script>

                $id = "{$this->prefix}_$name";
                print "<script type='text/javascript'>$('#$id').datepicker();</script>\n";
                $param['type'] = 'text';
*/
                break;
        }

        // Handle the basic types
        switch ($param['type']) {

            case 'list':
                echo "<select id='{$this->prefix}_$name' name='{$this->prefix}_options[$name]'>\n";
                foreach ($param['options'] as $option) {
                    list ($v, $o) = explode(':', $option);
                    $selected = '';
                    if ($v == $value) {
                        $selected = "selected='selected'";
                    }
                    print "<option value='$v' $selected>$o</option>\n";
                }
                echo "</select>\n";
                break;
            case 'checkbox':
                $checked = '';
                if ($value == 'TRUE') {
                    $checked = "checked='checked'";
                }
                echo "<input id='{$this->prefix}_$name' name='{$this->prefix}_options[$name]' type='checkbox' value='TRUE' $checked />";
                break;
            case 'text':
            default:
                echo "<input id='{$this->prefix}_$name' name='{$this->prefix}_options[$name]' size='{$param[size]}' xtype='{$param[type]}' value='$value' />";
        }

        if (isset($param['description'])) print "\n<br> " . $param['description'];

    }

    function admin_notice() {
        if ($this->updated) {
            delete_transient($this->OPTIONS_HANDLE . '_u');
            echo '<div class="updated"><p>';
            // printf(__("{$this->name} Settings Updated | <a href='%1$s'>Undo</a>"), '?undo=0');
            echo "{$this->name} Settings Updated.";
            echo '</p></div>';
        }
    }

    function undo() {
        /*
                    add_action('admin_init', 'example_nag_ignore');
                    function example_nag_ignore() {
                        global $current_user;
                        $user_id = $current_user->ID;
                        //  If user clicks to ignore the notice, add that to their user meta
                        if ( isset($_GET['example_nag_ignore']) && '0' == $_GET['example_nag_ignore'] ) {
                            add_user_meta($user_id, 'example_ignore_notice', 'true', true);
                        }
                    }
        */

    }


    function main_section_text() {
        echo "<p>{$this->name} Options</p>";
    }

    function validate($input) {
        $options = get_option($this->OPTIONS_HANDLE, array());
        update_option($this->OPTIONS_HANDLE . '_old', $options); // @todo allow for undo
        set_transient($this->OPTIONS_HANDLE . '_u', 1, 60);

        foreach ($this->params as $param) {
            $name = $param['name'];
            if (isset($input[$name])) $options[$name] = $input[$name];
        }

        return $options;
    }

    function settings_page() {
        $msg = '';
        if (isset($_POST['cmd_score'])) {
            $out = bracketpress()->score();
            $msg = 'Scoring Completed';
        }
        if (isset($_POST['cmd_reset'])) {
            $out = bracketpress()->reset_score();
            $msg = 'Scoring Cleared. Re-Run Scoring to Update.';
        }
        ?>
    <style type="text/css">
        .form-table th {text-align: right}
    </style>
    <div class="wrap">
        <?php screen_icon(); ?>
        <h2><?php print $this->name ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields($this->OPTIONS_HANDLE); ?>
            <?php do_settings_sections($this->PLUGINS_HANDLE); ?>
            <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
        </form>

        <h3>Scoring</h3>
        <form method="post">
            <?php print $msg ?><br>
            <input type="submit" name="cmd_score" value="Process Scoring">
            <input type="submit" name="cmd_reset" value="Reset Scoring">
        </form>

    </div>
    <?php
    }
}
