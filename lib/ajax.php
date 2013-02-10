<?php
/**
 * @package BracketPress
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/wp-load.php");

bracketpress_do_ajax();

function bracketpress_query($sql) {
    global $wpdb;
    bracketpress_do_ajax_trace($sql);
    return $wpdb->query($sql);
}

function bracketpress_do_ajax() {
    /** @var wpdb $wpdb*/
    global $wpdb;

    $table_match = bracketpress()->getTable('match');

    $user_id = get_current_user_id();
    if (! ($user_id > 0))  return; // Early out if we aren't logged in

    $post_id = isset($_GET['bracket']) ? (int)$_GET['bracket'] : 0;
    if ($post_id < 1) return;

    $match_ident = isset($_GET['mid']) ? $_GET['mid'] : null; // winner
    if (! $match_ident) return;

    // refactoring: match ident was a string (matchX) not is just an integer
    $match_id = str_replace('match', '', $match_ident) + 0;
    if (! $match_id) return;

    $task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';
    switch ($task) {

        case 'save_selection':

            $winner_id = isset($_GET['winner']) ? (int) $_GET['winner'] : NULL; // winner
            if ($winner_id) {
                // Make sure we have a record
                $sql = $wpdb->prepare("SELECT match_id FROM $table_match WHERE user_id=%d AND match_id=%d and post_id=%d", $user_id, $match_id, $post_id);

                $count = $wpdb->get_results($sql);
                // bracketpress_do_ajax_trace(print_r($count, true));

                if (count($count) == 0) {
                    $sql = $wpdb->prepare("INSERT INTO $table_match (match_id, user_id, post_id, winner_id) VALUES(%d, %d, %d, %d)", $match_id, $user_id, $post_id, $winner_id);
                } else {
                    $sql = $wpdb->prepare("UPDATE $table_match set winner_id=%d WHERE user_id=%d AND match_id=%d and post_id=%d", $winner_id,  $user_id, $match_id, $post_id);
                    // We may be updating only one part of the record
                }
                $wpdb->query($sql);
            }
            break;
    }

}
