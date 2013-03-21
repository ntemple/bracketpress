<?php
/*
Plugin Name: BracketPress
Description: Run and score a tournament bracket pool.
Author: Scott Hack and Nick Temple
Author URI: http://www.bracketpress.com
Version: 1.4.1
*/

/*
BracketPress, Copyright (C)2013  Nick Temple, Scott Hack

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Based on bbPress singleton model
 */
if (@constant('BRACKETPRESS_DEBUG')) {
    error_reporting(E_ALL &~ E_NOTICE &~ E_STRICT);
    ini_set("display_errors", 1);
    if (!defined('WP_DEBUG')) {
        define('WP_DEBUG', true);
    }
}

// Security Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// prevent hard to find errors because of outdated PHP
if ( version_compare( PHP_VERSION, '5.2', '<' ) ) {
    // Thanks for this Yoast!
    if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';
        deactivate_plugins( __FILE__ );
        wp_die( __('BracketPress requires PHP 5.2 or higher, as does WordPress 3.2 and higher. The plugin has now disabled itself.', 'bracketpress' ) );
    } else {
        return;
    }
}

if ( !class_exists( 'BracketPress' ) ) :

final class BracketPress {

    /** @var BracketPress */
    static $instance;

    /** @var array */
    var $options;

    // Globals
    var $file;
    var $basename;
    var $plugin_dir;
    var $plugin_url;

    var $includes_dir;
    var $includes_url;

    var $themes_dir;
    var $themes_url;

    var $bracket_url;
    var $bracket_slug;

    var $edit_id;

    var $bracket_title;
    var $default_shortcode;




    // The curent post query
    var $post;
    /** @var BracketPressMatchList */
    var $matchlist;

    /** @var BracketPressMatchList */
    var $winnerlist;

    // Generated content to place in the post
    var $content;

    /** @var BracketPressAdmin */
    var $admin;

    /** @var array array of the current bracket*/
    var $selections;

    /**
     * Main BracketPress Instance
     *
     * Ensures that only one instance exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @return BracketPress The one true BracketPress
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new BracketPress();
            self::$instance->setup_globals();
            self::$instance->includes();
            self::$instance->setup_actions();

            if (is_admin()) {
                include(self::$instance->includes_dir . 'admin/admin.php');
                self::$instance->admin = BracketPressAdmin::instance();
                self::$instance->setup_admin_actions(); // For ajax
            }
        }
        return self::$instance;
    }

    private function __construct() { /* Do nothing here */ }

    /**
     * Get the full table name of a bracketpress table in the database.
     * match, bracket and teams are currently supported.
     *
     * @param $name
     * @return string
     */

    function getTable($name) {
        global $wpdb;
        return $wpdb->prefix . "bracketpress_$name";
    }

    /**
     * Get an option from the database
     *
     * @param $key
     * @param null $default
     * @return $option
     */
    function get_option($key, $default = null) {
        // print_r($this->options);

        if (isset($this->options[$key]))
            return $this->options[$key];
        else
            return $default;
    }

    /**
     * Set an option and save it.
     * @param $key
     * @param $value
     */
    function update_option($key, $value) {
        $this->options[$key] = $value;
        update_option('bracketpress_options', $this->options);
    }

    /**
     * Create any one-time globals, passing through filters
     * so you can move them around if necessary.
     */

    private function setup_globals() {

        // Options list from setings page stored locally as an
        // associative array
        // access with bracketpress()->get_option and
        // bracketpress()->update_option()

        $this->options = get_option('bracketpress_options');

        /** Paths *************************************************************/

        // Setup some base path and URL information
        $this->file       = __FILE__;
        $this->basename   = apply_filters( 'bracketpress_plugin_basename',  plugin_basename( $this->file ) );
        $this->plugin_dir = apply_filters( 'bracketpress_plugin_dir_path',  plugin_dir_path( $this->file ) );
        $this->plugin_url = apply_filters( 'bracketpress_plugin_dir_url',   plugin_dir_url ( $this->file ) );

        // Includes
        $this->includes_dir = apply_filters( 'bracketpress_includes_dir', trailingslashit( $this->plugin_dir . 'includes'  ) );
        $this->includes_url = apply_filters( 'bracketpress_includes_url', trailingslashit( $this->plugin_url . 'includes'  ) );

        // Templates
        $this->themes_dir   = apply_filters( 'bracketpress_themes_dir',   trailingslashit( $this->plugin_dir . 'templates' ) );
        $this->themes_url   = apply_filters( 'bracketpress_themes_url',   trailingslashit( $this->plugin_url . 'templates' ) );

        $this->bracket_slug = apply_filters( 'bracketpress_bracket_slug', 'brackets' );
        $this->bracket_url = apply_filters( 'bracketpress_bracket_slug',   trailingslashit( $this->plugin_url . $this->bracket_slug ) );

        $this->edit_id  = apply_filters( 'bracketpress_bracket_edit_id', 'edit' );

        $this->bracket_title = apply_filters( 'bracketpress_default_bracket_title', 'Bracket' );
        $this->default_shortcode = apply_filters( 'bracketpress_default_shortcode', '[bracketpress_display_bracket]' );
    }


    private function includes() {

        require($this->plugin_dir  . 'bracketpress-config.php');

        require_once( $this->plugin_dir  . 'includes/core/actions.php' );

        // Include required library files
        require_once( $this->plugin_dir  . 'lib/functions.php' );
        require_once( $this->plugin_dir  . 'lib/bracketpress-shortcodes.php');
        require_once( $this->plugin_dir  . 'lib/bracketpress-widgets.php');
        require_once( $this->plugin_dir  . 'lib/ajax.php' );
        require_once( $this->includes_dir  . 'models/queries.php');
        require_once( $this->includes_dir  . 'models/match.php');

    }

    /**
     * Add a class action to the system, usinh default
     * call stack
     *
     * @param $actions
     * @param int $priority
     */
    private function add_actions($actions, $priority = 10) {
        foreach ($actions as $instance_action => $hook) {
            add_action( $hook, array( $this, $instance_action ), $priority );
        }
    }

    private function setup_actions() {

        $actions = array(
            'register_posts'           => 'bracketpress_init',
            'route'                    => 'bracketpress_route',
            'generate_rewrite_rules'   => 'bracketpress_generate_rewrite_rules',
            'add_rewrite_tags'         => 'bracketpress_init'
        );

        add_action( 'wp_login', array( $this, 'on_signin' ), 10, 2 );

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        if ($this->get_option('show_bracketpress_logo') == 'yes') {
            add_filter('bracketpress_brandingbox2', array($this, 'show_logo'));
        }

        $this->add_actions($actions);
    }

    /**
     * Setup admin actions.
     * http://codex.wordpress.org/AJAX_in_Plugins
     * @todo hook into bracketpress action system
     */
    private function setup_admin_actions() {
        add_action('wp_ajax_bracketpress', array($this, 'handle_frontend_ajax'));
        add_action('wp_ajax_bracketpress', array($this, 'handle_nopriv_frontend_ajax'));
    }

    function handle_frontend_ajax() {
        ob_clean();
        bracketpress_do_ajax();
        die(); //?per codex. Is it right to die?
    }


    function activate() {
        require_once($this->plugin_dir . 'install/install.php');

        $installer = new BracketPressInstaller();
        $installer->update_tables();

        // Create a master bracket for this user
        // if it hasn't been set
        if (! $this->get_option('master_id')) {
            // We don't have a master bracket
            // Create one for the admin, if the admin doesn't have a bracket
            $this->create_bracket_post();
            // Create the Master Bracket
            $master_id = $this->create_bracket_post(null, 'MASTER BRACKET', true); // can be a second bracket
            // Note: if you don't want to display the bracket, then remove the shortcode
            $this->update_option('master_id', $master_id);
        }

        if (! $this->get_option('leaderboard_id')) {
            $post = array(
                'post_type'    => 'page',
                'post_title'   => 'BracketPress Leaderboard',
                'post_content' => '[bracketpress_all_brackets]',
                'post_status'  => 'pending'
            );
            $this->update_option('leaderboard_id', wp_insert_post($post));
        }

        if (! $this->get_option('edit_id')) {
            $post = array(
                'post_type'    => 'page',
                'post_title'   => 'My Brackets',
                'post_content' => '[bracketpress_edit]',
                'post_status'  => 'pending'
            );
            $this->update_option('edit_id', wp_insert_post($post));
        }

        flush_rewrite_rules();
    }

    function deactivate() {   }


    function register_posts() {

        $labels = array(
            'name'               => __( 'Brackets' ),
            'singular_name'      => __( 'Bracket' ),
            'add_new'            => __( 'Add New' ),
            'add_new_item'       => __( 'Add New Bracket' ),
            'edit_item'          => __( 'Edit Bracket' ),
            'new_item'           => __( 'New Bracket' ),
            'view_item'          => __( 'View Bracket' ),
            'search_items'       => __( 'Search Brackets' ),
            'not_found'          => __( 'No brackets found' ),
            'not_found_in_trash' => __( 'No brackets found in Trash' ),
            'parent_item_colon'  => '',
        );

        $args = array(
            'labels'               => $labels,
            'public'               => true,
            'rewrite'              => array( 'slug' => $this->bracket_slug ),
            'capability_type'      => 'post',
            'has_archive'          => true,
            'hierarchical'         => false,
            'menu_position'        => 5,
            //'register_meta_box_cb' => 'hmls_add_custom_metaboxes',
            //'taxonomies' => array('post_tag', 'category'),
            'supports'           => array(
                'title',
                'editor',
                'excerpt',
                'thumbnail',
                'custom-field',
                'tags',
                'comments',
                'author'
            ),
        );

        register_post_type( 'brackets', $args );
        add_filter('manage_brackets_posts_columns', array($this, 'change_cols'));
        add_action('manage_brackets_posts_custom_column', array($this, 'custom_columns'), 10, 2);
        add_filter('manage_edit-brackets_sortable_columns', array($this, 'sortable_columns'));
        add_filter('request', array($this, 'handle_custom_sorting'));
    }

    function change_cols($cols) {
        $cols = array(
            'cb' => '<input type="checkbox" />',
            'title' => 'Bracket Title',
            'author' => 'User',
            'score'  => 'Score',
            'date'   => 'Date'
        );
        return $cols;
    }

    function custom_columns($column, $post_id) {
        switch($column) {
            case 'score':
                $score = get_post_meta($post_id, 'score', true);
                if (! $score) {
                    print "Unscored";
                } else {
                    print $score;
                }
            break;
        }
    }

    function sortable_columns() {
        return array('score' => 'score', 'author' => 'author');
    }

    function handle_custom_sorting($vars) {

        if (isset($vars['orderby']) && 'score' ==  $vars['orderby']) {
            $vars = array_merge($vars, array(
                'meta_key' => 'score',
                'orderby'  => 'meta_value_num',
                )
            );
        }
        return $vars;
    }

    // Branding Box


    function show_logo($text) {
        return '<center><a href="http://www.bracketpress.com"><img src="http://www.bracketpress.com/wp-content/themes/bracketpress/images/logo.jpg" target="bracketpress" height="100"></a></center>';
    }




    /**
     * bracketpress rewrite rules as necessary
     */

    function add_rewrite_tags() {
        add_rewrite_tag( '%%' . $this->edit_id               . '%%', '([1]{1,})' ); // Edit Page tag
    }

    function generate_rewrite_rules( $wp_rewrite ) {
    }

    /**
     * When someone signs in, create their bracket.
     *
     * @param $username
     * @param $user
     */
    function on_signin($username, $user) {
        $this->create_bracket_post($user->ID);
    }

    /**
     * Create a default bracket post. The bracket matches are created later, when viewed.
     *
     * @param null $author_id (or current author)
     * @param null $title     (or default title)
     * @param bool $allow_multiple  Create an additional post if one exists? (defaults to false)
     * @return integer $post_id
     */

    function create_bracket_post($author_id = null, $title = null, $allow_multiple = false) {

        $user = null;

        if ($author_id) {
            $user = get_user_by('id', $author_id);
        } else {
            $user = wp_get_current_user();
        }

        // Don't do anything if we don't have an author
        if (! $user) return null;
        $author_id = $user->ID;


        // Check for multiple posts
        $post_id = $this->get_bracket_for_user($author_id);
        if ($post_id && ! $allow_multiple) {
            // we have one or posts by this user and we don't allow more.
            return $post_id;
        }



        // If we get here, we want to create the post

        // Override post title for SEO?
        $post_title = $title ? $title : $this->bracket_title . ' ' . $user->data->display_name;
        $post_title = apply_filters('bracketpress_bracket_title', $post_title);

        //@todo need a excerpt. Maybe created with a plugin?
        $post = array(
            'post_type'    => 'brackets',
            'post_title'   => $post_title,
            'post_content' => $this->default_shortcode,
            'post_status'  => 'publish',
            'post_author'  => $author_id,
        );
        return wp_insert_post($post);
    }

    /**
     * Return the *first* bracket for this user.
     * (usually the only bracket)
     *
     * @param $id
     * @return $post_id or null if none found
     */

    function get_bracket_for_user($id) {
        $query = new WP_Query(array('post_type' => 'brackets', "author" => $id) );

        // We're returning the id of the first post found
        if ($query->have_posts())   {
            $query->next_post();
            return $query->post->ID;
        }

        return null;
    }

    /**
     * Handle scoring.
     */

    function get_score($id = null) {
        if (!$id) $id = $this->post->ID;
        $score = get_post_meta($id, 'score', true);
        if ($score == '') $score = "Unscored";
        return $score;
    }

    function reset_score() {
        global $wpdb;

        $table_match = bracketpress()->getTable('match');
        $wpdb->query("update $table_match set points_awarded=NULL");


        $table_postmeta = $wpdb->prefix . 'postmeta';
        $wpdb->query("update $table_match set points_awarded=NULL");
        //@todo: constrain delete by post_type = brackets
        $wpdb->query("delete from $table_postmeta where meta_key='score'");

    }

    function score() {
        /** @var wpdb $wpdb */
        global $wpdb;

        // Could to most of this in one or two massive queries.
        // If you want to optimize, go right ahead.
        ob_start(); // Debug

        print_r($this->options);

        $table_match = bracketpress()->getTable('match');

        $master = $this->get_option('master_id');

        $sql = $wpdb->prepare("select * from $table_match where post_id=%d and winner_id > %d", $master, 0);

        $winners = $wpdb->get_results($sql);
        print "$sql\n";


        foreach ($winners as $winner) {
            $match = $this->getMatchDetails($winner->match_id);

            $sql =$wpdb->prepare("update $table_match set points_awarded=%d where match_id=%d and winner_id = %d", $match->points, $winner->match_id, $winner->winner_id);
            $wpdb->query($sql);
            print "$sql\n";

            $sql =$wpdb->prepare("update $table_match set points_awarded=%d where match_id=%d and winner_id <> %d", 0, $winner->match_id, $winner->winner_id);
            $wpdb->query($sql);
            print "$sql\n";
        }

        // Get all the bracket posts
        //$table = $wpdb->prefix . 'posts';
        $sql = "select post_id, sum(points_awarded) as score from $table_match group by post_id";
        print "$sql\n";
        $brackets = $wpdb->get_results($sql);
        print_r($brackets);
        foreach ($brackets as $bracket) {
            $old_score = get_post_meta($bracket->post_id, 'score');
            update_post_meta($bracket->post_id, 'score', $bracket->score);
            do_action('bracketpress_event_updatescore', array('post_id' => $bracket->post_id, 'old_score' => $old_score, 'score' => $bracket->score));
        }

       $debug = ob_get_clean();

       $log = "<pre>\n$debug</pre>";
       // print $log;


       return $log;
    }

    /**
     * Get the number of points awarded for this match.
     *
     * make dynamic based on settings, which means
     * we have to figure out which round match is in
     * based in it's id.
     *
     * @param $match_ident
     * @return stdClass
     */

    function getMatchDetails($match_id) {
        $match = new stdClass();

        $round = BracketPressMatchList::getRound($match_id);

        switch($round) {
            case 1: $match->points = $this->get_option('points_first_round'); break;
            case 2: $match->points = $this->get_option('points_second_round'); break;
            case 3: $match->points = $this->get_option('points_third_round'); break;
            case 4: $match->points = $this->get_option('points_fourth_round'); break;
            case 5: $match->points = $this->get_option('points_fifth_round'); break; // Final 4
            case 6: $match->points = $this->get_option('points_sixth_round'); break; // Final game
            default: throw new Exception("Match $match_id doesn't exist for round $round");
        }

        print "Round: $match_id: $round: {$match->points}\n";

        return $match;
    }


    /**
     * Set the page content for the display plugin
     *
     * @param $content
     * @return mixed
     */

    function setContent($content) {
        $this->content = $content;
        return $this->getContent();
    }

    function getContent() {
        return apply_filters( 'bracketpress_content', $this->content);
    }

    /**
     * Get the permalink to this match.
     *
     * @param $post_id
     * @param bool $edit true if you want the link to the edit page
     * @return string
     */

    function get_bracket_permalink($post_id, $edit = false) {
        $p =  get_post_permalink($post_id);
        if ($edit) {
            $p = add_query_arg($this->edit_id, 'true', $p);
        }
        return $p;
    }

    /**
     * BracketPress Core router
     *
     * The actual page is generated here, and stored in $content.
     * The shortcode then just displayes the page
     *
     * Main Controller routine
     *
     * @param $post_query WP_Query
     *
     */
    function route( $post_query) {

        static $in_route = false;

        // bail if this is a subrequest
        if ($in_route) return;

        // Bail if in admin
        if ( is_admin() )   return;

        // Bail if filters are suppressed on this query
        if ( true == $post_query->get( 'suppress_filters' ) )  return;

        // Bail if $posts_query is not the main loop
        if ( ! $post_query->is_main_query() )
            return;

        // We are only interested in "brackets"
        if ($post_query->get('post_type') !== 'brackets')
            return;

        $in_route = true;
        // Get query variables
        $posts = $post_query->get_posts();
        $in_route = false;

        if($post_query->post_count == 1) {

            $is_edit  = $post_query->get( 'edit' );
            $post = array_pop($posts);
            $post->combined_score = get_post_meta($post->ID, 'combined_score', true);
            $this->post = $post;

            if ($is_edit) {
                $this->bracket_edit($post);
            } else {
                $this->bracket_display($post);
            }

        } else {
            if (@constant('BRACKETPRESS_DEBUG')) {
                print "<pre>\n";
                print_r($posts);
                print "</pre>\n";
            }
            $this->setContent('LIST OF POSTS');
        }
    }

    function is_bracket_owner() {
        if ($this->post->post_author == get_current_user_id()) {
            return true;
        } else {
            return false;
        }
    }


    function get_bracket_close_time() {
        $close_datetime = $this->get_option('date_brackets_close') . ' ' . $this->get_option('time_brackets_close');
        return strtotime($close_datetime);
    }

    function is_bracket_closed() {
        $now = time();
        if ($now > $this->get_bracket_close_time())
            return true;
        else
            return false;
    }

    // Page Display routines

    /**
     *
     * Create the edit bracket page, and store the content
     * for display by the shortcode.
     *
     * @param $post
     */
    function bracket_edit($post) {
        $this->post = $post;
        $this->matchlist = new BracketPressMatchList($post->ID);

        if (!$this->is_bracket_owner()) {
            $this->bracket_display($post, '<div class="updated"><p>You must be signed in to edit this bracket.</p></div>');
            return;
        }

        $close = $this->get_bracket_close_time();
        $date = strftime("%Y-%m-%d %H:%M:%S", $close);

        $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );
        $date = date($date_format, $close) . " at " . date($time_format, $close);

        $datediff = human_time_diff(time(), $close);
        $message = "The bracket will close for editing in $datediff on $date.<br>";


        if ($post->ID != $this->get_option('master_id'))
            if ($this->is_bracket_closed()) {
                $this->bracket_display($post, "This bracket has been closed for editing. Good Luck!<br>");
                return;
            }

        if (isset($_POST['cmd_bracketpress_save'])) {

            $post_data = array(
                'ID' => $post->ID,
                'post_title' => $_POST['post_title'],
                'post_excerpt' => $_POST['post_excerpt'],
                'combined_score' => $_POST['combined_score'],
            );

            $post_data = apply_filters('bracketpress_update_bracket', $post_data );

            if ($post_data['post_title']) wp_update_post($post_data);
            if ($post_data['combined_score'])  update_post_meta($post_data['ID'], 'combined_score', $post_data['combined_score']);

            $this->post->post_title = $post_data['post_title'];
            $this->post->post_excerpt = $post_data['post_excerpt'];
            $this->post->combined_score = $post_data['combined_score'];
        }

        if (isset($_POST['cmd_bracketpress_randomize'])) {
            $this->matchlist->randomize();
            $this->matchlist = new BracketPressMatchList($post->ID, true); // Reload the bracket
            do_action('bracketpress_event_randomize');
        }

        $file = apply_filters( 'bracketpress_template_edit',   $this->themes_dir .  'bracket_edit.php' );
        ob_start();
        include($file);
        $output = ob_get_clean();
        $this->setContent($output);
    }

    /**
     * Create the display bracket page, and store the content
     * for display by the shortcode.
     *
     * The message (if any) is displayed at the top of the page
     * (in the default template)
     *
     * @param $post
     * @param string $message
     */

    function bracket_display($post, $message = '') {

        $this->post = $post;
        $this->matchlist = new BracketPressMatchList($post->ID);
        $master = $this->get_option('master_id');
        if ($master) {
            $this->winnerlist = new BracketPressMatchList($master);
        }

        $file = apply_filters( 'bracketpress_template_display',   $this->themes_dir .  'bracket_display.php' );

        ob_start();
        include($file);
        $output = ob_get_clean();
        $this->setContent($output);

    }

}


/**
 * The main function responsible for returning the one true BracketPress Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 */
function bracketpress() {
    return bracketpress::instance();
}

// "And now here's something we hope you'll really like!" Run the plugin.
bracketpress();

endif;



