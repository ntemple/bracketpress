<?php
/**
 * @package BracketPress
 */


function bracketpress_do_ajax() {
    /** @var wpdb $wpdb*/
    global $wpdb;

    // file_put_contents('/tmp/log.txt', print_r($_REQUEST, true), LOCK_EX | FILE_APPEND);

    $table_match = bracketpress()->getTable('match');

    $user_id = get_current_user_id();
    if (! ($user_id > 0))  return; // Early out if we aren't logged in

    $post_id = isset($_POST['bracket']) ? (int)$_POST['bracket'] : 0;
    if ($post_id < 1) return;

    $match_ident = isset($_POST['mid']) ? $_POST['mid'] : null; // winner
    if (! $match_ident) return;

    // refactoring: match ident was a string (matchX) not is just an integer
    $match_id = str_replace('match', '', $match_ident) + 0;
    if (! $match_id) return;

    $task = isset($_REQUEST['task']) ? $_REQUEST['task'] : '';
    switch ($task) {

        case 'save_selection':

            $winner_id = isset($_POST['winner']) ? (int) $_POST['winner'] : NULL; // winner
            if (! $winner_id) $winner_id = 'NULL';

            // Make sure we have a record
            $sql = $wpdb->prepare("SELECT match_id FROM $table_match WHERE user_id=%d AND match_id=%d and post_id=%d", $user_id, $match_id, $post_id);

            $count = $wpdb->get_results($sql);

            if (count($count) == 0) {
                $sql = $wpdb->prepare("INSERT INTO $table_match (match_id, user_id, post_id, winner_id) VALUES(%d, %d, %d, %d)", $match_id, $user_id, $post_id, $winner_id);
                do_action('bracketpress_selection_new', array('match_id' => $match_id, 'user_id' => $user_id, 'winner_id' => $winner_id, 'post_id' => $post_id));
            } else {
                $sql = $wpdb->prepare("UPDATE $table_match set winner_id=%d WHERE user_id=%d AND match_id=%d and post_id=%d", $winner_id,  $user_id, $match_id, $post_id);
                do_action('bracketpress_selection_change', array('match_id' => $match_id, 'user_id' => $user_id, 'winner_id' => $winner_id, 'post_id' => $post_id));
            }
            $wpdb->query($sql);

        break;
    }

    print "{ 'status': 'ok'}\n";
}
