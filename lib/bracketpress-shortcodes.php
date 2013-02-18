<?php

/**
 * Display the bracket.
 *
 * Since shortcodes run late in the cycle, it pulls the pre-generated content
 * from the class, runs it yet-again through the shortcode system, and
 * displays it
 *
 */
function bracketpress_shortcode_display() {
    print do_shortcode(bracketpress()->getContent());
    return;
}
add_shortcode( 'bracketpress_display_bracket', 'bracketpress_shortcode_display' );


function bracketpress_shortcode_doscoring() {
    print bracketpress()->score();
}
add_shortcode( 'bracketpress_doscoring', 'bracketpress_shortcode_doscoring' );

function bracketpress_shortcode_edit($args) {

    $user_id = get_current_user_id();

    if (! $user_id) {
        print "You are not logged in, so can't edit your bracket. Please create an account to continue.";
        return;
    }

    $author_query = array('posts_per_page' => '-1','author' => $user_id, 'post_type' => 'brackets');
    $author_posts = new WP_Query($author_query);
    $posts = $author_posts->get_posts();

    ob_start();

    if (count($posts) == 0) {
        print "Sorry, you don't have any brackets. Please sign out then re-sign in, or contact your administrator for help.";
        return;
    } else if (count($posts) == 1) {
        // Shortcodes run too late in the process, so we cannot redirect without warnings / errors.
        // Do nothing
        // print "The normal case, you only have one post connected to your id. We can redirect you to it so you can edit the post, or let you click here.";
    } else {
        // List of brackets. Do nothing.
        // print "Uh oh! More than one post connected to your Id. What to do, what to do? Show you all, or just use the first one and ignore the rest? Or check admin settings?";
    }

    print "My Brackets\n<table width='60%'>\n";
    foreach ($posts as $post) {
        $link = bracketpress()->get_bracket_permalink($post->ID);
        $link_edit = bracketpress()->get_bracket_permalink($post->ID, true);
        print "<tr><td>{$post->post_title}</td><td width='20%'><a href='$link'>View</a>&nbsp;</td><td width='20%'><a href='$link_edit'>Edit</a></td></tr>\n";
//        print "<tr><td colspan=3><pre>" . print_r($post, true) . "</pre></td></tr>\n";
    }

    print "</table>\n";
    $output = ob_get_clean();
    echo do_shortcode($output);

    return;
}
add_shortcode( 'bracketpress_edit', 'bracketpress_shortcode_edit' );



//Displays the excerpt of all brackets.  Since we don't have anyting being placed into the
//excerpt yet.  This shortcode shouldn't be used.
add_shortcode( 'bracketpress_all_brackets', 'bracketpress_shortcode_all_brackets' );

function bracketpress_shortcode_all_brackets() {
//    print "<pre>\n";
    $args = array(
        'post_type' => 'brackets',
        'posts_per_page' => 5,//get_option( 'posts_per_page' ), // you can assign 15
        'paged'	=> get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
        'meta_key' => 'score',
        'orderby'  => 'meta_value_num'
    );

    $wp_query = new WP_Query($args);

    if ($wp_query->have_posts()) {


        $posts = $wp_query->get_posts();
        print "<table>\n";
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
            <tr>
              <td><a href='{$link}'>{$post->post_title}</a></td>
              <td>{$author->display_name}</td>
              <td>{$author->first_name}</td>
              <td>{$author->last_name[0]}</td>
              <td>{$score}</td>
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
//    print "</pre>\n";
}

