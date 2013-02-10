<?php

class MyNewWidget extends WP_Widget {

function MyNewWidget() {
// Instantiate the parent object
parent::__construct( false, 'My New Widget Title' );
}

function widget( $args, $instance ) {
// Widget output
}

function update( $new_instance, $old_instance ) {
// Save widget options
}

function form( $instance ) {
// Output admin widget options form
}
}

function myplugin_register_widgets() {
register_widget( 'MyNewWidget' );
}

add_action( 'widgets_init', 'myplugin_register_widgets' );