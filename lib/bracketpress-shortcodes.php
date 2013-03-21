<?php
/**
 * Bracketpress Shortcodes
 */

/**
 * Display the bracket.
 *
 * [bracketpress_shortcode_display]
 *
 * Since shortcodes run late in the cycle, it pulls the pre-generated content
 * from the class, runs it yet-again through the shortcode system, and
 * displays it
 *
 */
function bracketpress_shortcode_display($atts) {

    extract( shortcode_atts( array(
        'before_widget' => '',
        'after_widget' => '',
        'title' => '',
        'before_title' => '',
        'after_title' => '',
    ), $atts ) );

    return bracketpress()->getContent();
}
add_shortcode( 'bracketpress_display_bracket', 'bracketpress_shortcode_display' );

/**
 * For logged in users, lists the brackets they own
 * and links to view and edit.
 *
 * [bracketpress_edit]
 *
 * @todo create new shortcode that does the same using CSS.
 *
 * @param $args
 * @return string
 */

function bracketpress_shortcode_edit($atts) {

    extract( shortcode_atts( array(
        'before_widget' => '',
        'after_widget' => '',
        'title' => '',
        'before_title' => '',
        'after_title' => '',
    ), $atts ) );


    $user_id = get_current_user_id();

    if (! $user_id) {
        return  "You are not logged in, so can't edit your bracket. Please create an account to continue.";
    }

    $author_query = array('posts_per_page' => '-1','author' => $user_id, 'post_type' => 'brackets');
    $author_posts = new WP_Query($author_query);
    $posts = $author_posts->get_posts();

    ob_start();

    if (count($posts) == 0) {
        return "Sorry, you don't have any brackets. Please sign out then re-sign in, or contact your administrator for help.";
    } else if (count($posts) == 1) {
        // Shortcodes run too late in the process, so we cannot redirect without warnings / errors.
        // Do nothing
        // print "The normal case, you only have one post connected to your id. We can redirect you to it so you can edit the post, or let you click here.";
    } else {
        // List of brackets. Do nothing.
        // print "Uh oh! More than one post connected to your Id. What to do, what to do? Show you all, or just use the first one and ignore the rest? Or check admin settings?";
    }

    print "<table width='60%'>\n";
    foreach ($posts as $post) {
        $link = bracketpress()->get_bracket_permalink($post->ID);
        $link_edit = bracketpress()->get_bracket_permalink($post->ID, true);
        print "<tr><td>{$post->post_title}</td><td width='20%'><a href='$link'>View</a>&nbsp;</td><td width='20%'><a href='$link_edit'>Edit</a></td></tr>\n";
    }

    print "</table>\n";
    $output = ob_get_clean();
    return $output;
;
}
add_shortcode( 'bracketpress_edit', 'bracketpress_shortcode_edit' );


/**
 * [bracketpress_all_brackets] or
 * [bracketpress_all_bracckets orderby='score' posts_per_page="10"]
 *
 * Note: If scoring has not been run, or a bracket has no score
 * then it will NOT show up if the orderby score clause is used.
 *
 * this is a limitation of wordpress where posts without the requested
 * meta information are not returned.
 *
 * @return string
 */

//Displays the excerpt of all brackets.  Since we don't have anyting being placed into the
//excerpt yet.  This shortcode shouldn't be used.


function bracketpress_shortcode_all_brackets($atts) {

    extract( shortcode_atts( array(
        'orderby' => 'default',
        'posts_per_page' => 10,
        'before_widget' => '',
        'after_widget' => '',
        'title' => '',
        'before_title' => '',
        'after_title' => '',
    ), $atts ) );

    $args = array(
        'post_type' => 'brackets',
        'posts_per_page' => $posts_per_page,
        'paged'	=> get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
    );

    if ($orderby == 'score') {
        $args['meta_key'] = 'score';
        $args['orderby'] = 'meta_value_num';
    }



    $wp_query = new WP_Query($args);
    ob_start();

    if ($wp_query->have_posts()) {


        $posts = $wp_query->get_posts();
        print "<table width='100%'>\n";
        foreach ($posts as $post) {
            $author_q =  get_user_by('id', $post->post_author);
            $author =  $author_q->data;
            $author_meta_q  = get_user_meta($post->post_author);
            foreach ($author_meta_q as $key => $value) {
                $author->$key = $author_meta_q[$key][0];
            }
            $link = bracketpress()->get_bracket_permalink($post->ID);
            $score = get_post_meta($post->ID, 'score', true);
            if (!$score ) {
                $score = 'Unscored';
            }
            print "
            <tr class='brackets'>
              <td width='30%' class='bracket_title'><a href='{$link}'>{$post->post_title}</a></td>
              <td width='25%' class='bracket_user'>{$author->display_name}</td>
              <td width='25%' class='bracket_fname'>{$author->first_name}</td>
              <td width='10%' class='bracket_lname'>{$author->last_name[0]}</td>
              <td width='10%' class='bracket_score'>{$score}</td>
            </tr>";

        }
        print "</table>\n";


/*
        while ($wp_query->have_posts()) {


            print_r($post);
            $title = $post->title;
            print $title;
        }
*/

        $big = 999999999; // need an unlikely integer
        echo '<div class="pagination">';
        echo paginate_links( array(
            'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format' => '?paged=%#%',
            'prev_next'    => True,
            'current' => max( 1, get_query_var('paged') ),
            'total' => $wp_query->max_num_pages
        ) );
        echo '</div>';

    } else {
        print "No brackets were found.\n";
    }

    return ob_get_clean();
}
add_shortcode( 'bracketpress_all_brackets', 'bracketpress_shortcode_all_brackets' );
