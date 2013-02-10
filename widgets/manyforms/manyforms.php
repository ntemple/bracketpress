<?php
/*
Plugin Name: Many Forms
Plugin URI: http://wordpress.org/extend/plugins/bracketpress-login/
Description:  An easy form generator
Version: 1.0
Author: Charles Griffin
Author URI: http://nibbledabble.com
*/

class Manyforms extends WP_Widget {

    function __construct()
    {
        $params = array(
            'description' => 'Display messages to readers',
            'name'  => 'Many_Forms'

        );
        parent::__construct('Many_Forms', '', $params);
    }

    public function form($instance)
    {
        extract($instance);
        ?>
    <p>
        <label for="<?php echo $this->get_field_id('title'); ?>">Title: </label>
        <input
            class="widefat"
            id="<?php echo $this->get_field_id('title');?>"
            name="<?php echo $this->get_field_name('title');?>"
            value="<?php if( isset($title) ) echo esc_attr($title); ?>" />
    </p>
    <?php
    }

    public function widget()
    {

    }
}
add_action('widgets_init', 'form_make');

function form_make()
{
    register_widget('Many_Forms');
}



