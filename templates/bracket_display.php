<?php
/**
 * This file is included from the core bracket_edit function and is designed to be a
 * template the selections, selection_json and $post variables are all set before
 * including this file.
 *
 * see bracketpress.php: bracketpress::bracket_display
 *
 * @var array $selections
 * @var string $message
 * @var array $post
 */

/**
 * Add our CSS
 */

function bracketpress_display_enqueue_css() {
    wp_register_style('bracket_readonly', BRACKETPRESS_CSS_URL . 'bracket_readonly.css');
    wp_enqueue_style('bracket_readonly');
}
add_action('wp_enqueue_scripts', 'bracketpress_display_enqueue_css');


/**
 * This is the actual output code for each bracket.
 *
 * @param $this_match_id
 * @param $m
 * @param $team1
 * @param $team2
 */

function bracketpress_partial_display_bracket($this_match_id, $m, $team1, $team2, $final = false, $match = null) {

    // Find out if we won or lost the previous match
    $class1 = '';
    $class2 = '';

    // Final match CSS
    $id1 = $id2 = '';
    $combined_score = '';
    $winner1 = $winner2 = '';

    $prev_match_ids = BracketPressMatchList::getPreviousMatch($this_match_id);
    $matchlist = bracketpress()->matchlist;

    $show_seed = true;

    if ($prev_match_ids) {

        $show_seed = false;

        $prev_match = array();
        $prev_match[0] = $matchlist->getMatch($prev_match_ids[0]);
        $prev_match[1] = $matchlist->getMatch($prev_match_ids[1]);

        $prev_winner = array(null, null);
        $winnerlist = bracketpress()->winnerlist;
        if ($winnerlist) {
            $prev_winner[0] = $winnerlist->getMatch($prev_match_ids[0]);
            $prev_winner[1] = $winnerlist->getMatch($prev_match_ids[1]);
        }

        $x1 = print_r($prev_match[0], true);
        $x2 = print_r($prev_match[1], true);


        $x1 = $prev_match[0]->points_awarded;
        $x2 = $prev_match[1]->points_awarded;

        if ($prev_match[0]->points_awarded == '0') {
            $class1 = 'lost';
            $winner1 = $prev_winner[0]->getWinner()->name;

        }
        if ($prev_match[0]->points_awarded > 0) $class1 = 'won';

        if ($prev_match[1]->points_awarded == '0') {
            $class2 = 'lost';
            $winner2 = $prev_winner[1]->getWinner()->name;
        }
        if ($prev_match[1]->points_awarded > 0) $class2 = 'won';
    }

    // Special id css display tags to make the final bracket work visually
    if ($final) {

        if ($match->winner_id) {
            if ($match->winner_id == $team1->ID) $class1 .= ' final_pick';
            if ($match->winner_id == $team2->ID) $class2 .= ' final_pick';
        }
        $id1 = "id='slot127'";
        $id2 = "id='slot128'";
    }
    ?>
<div id="match<?php print $this_match_id ?>" class="match m<?php print $m ?>">
    <p class="slot slot1 team_<?php echo $team1->ID ?>" <?php echo $id1 ?>>
            <span class="seed <?php echo $class1 ?>">
                <?php if ($winner1) { ?>
                    <span class="org_win1"><?php echo $winner1 ?></span>
                <?php } ?>
                <?php if ($team1) { ?>
                    <span class="team_ids"> <?php if ($show_seed) echo $team1->seed; ?></span> <?php print bracketpress_display_name($team1->name) ?>
                <?php } ?>
                <em class="score"><?php // echo $this_match_id ?></em>
            </span>

    </p>
    <p class="slot slot2 team_<?php echo $team2->ID ?>" <?php echo $id2 ?>>
            <span class="seed <?php echo $class2 ?>">
                <?php if ($winner2) { ?>
                    <span class="org_win2"><?php echo $winner2 ?></span>
                <?php } ?>
                <?php if ($team2) { ?>
                    <span class="team_ids"> <?php if ($show_seed) echo $team2->seed; ?></span> <?php print bracketpress_display_name($team2->name) ?>
                <?php } ?>
                <em class="score"><?php  ?></em>
            </span>
    </p>
</div>
<?php
}

/**
 * Display the seed brackets for a region.
 * For this, we need the team list (with the seeds)
 *
 * @param $region
 */
function bracketpress_partial_display_seed($region) {

    $base = ($region - 1) * 15;
    $matchlist = new BracketPressMatchList(bracketpress()->post->ID);

    for ($x = 0; $x < BracketPressMatchList::$bracket_size; $x++) {
        $match_id = $base + $x + 1;
        $match = $matchlist->getMatch($match_id);

        $team1 = $match->getTeam1();
        $team2 = $match->getTeam2();

        $m = $x+1;

        bracketpress_partial_display_bracket($match_id, $m, $team1, $team2, false, $match);
    }
}


/**
 * Displays a round for one region
 *
 * @param $round current round, which tells us how many matches will be in this round
 * @param $region the region this match is for
 *
 * @internal param \List $selections of user selections
 * @internal param \the $loop_start start index in the array to use. (now ignored)
 *
 */
function bracketpress_partial_display_round($round, $region) {

    $matchlist = new BracketPressMatchList(bracketpress()->post->ID);

    $offset = $increment = 0;
    if ($round == 2) {  $increment = 8;   $offset = 0;  }
    if ($round == 3) {  $increment = 4;   $offset = 0 + 8;  }
    if ($round == 4) {  $increment = 2;   $offset = 0 + 8 + 4;   }
    if ($round == 5) {  $increment = 1;   $offset = 0 + 8 + 4 + 2;  } // Inaccurate

    $base = ($region - 1) * 15; // We have 15 matches per region
    $start = $base + $offset + 1;

    $end = $start + $increment;

    $match_count = 1;
    for ($x = $start; $x < $end; $x += 2) {

        /*
         * $x is sitting at the previous match
         */

        list($this_match_id, $slot) = BracketPressMatchList::getNextMatch($x);
        $this_match = $matchlist->getMatch($this_match_id);

        $team1 = $this_match->getTeam1();
        $team2 = $this_match->getTeam2();

        bracketpress_partial_display_bracket($this_match_id, $match_count++, $team1, $team2);
    }
}

/**
 * Display a round for each region
 *
 * @param $num
 * @param $name
 */
function bracketpress_display_rounds($num, $name) {
    ?>
<div id="round<?php print $num ?>" class="round">
    <h3>Round <?php print $name ?> (2013 NCAA Men's Basketball Tournament)</h3>
    <div class="region region1">
        <h4 class="region1">MIDWEST</h4>
        <?php bracketpress_partial_display_round($num, BRACKETPRESS_REGION_MIDWEST); ?>
    </div>
    <div class="region region2">
        <h4 class="region2">WEST</h4>
        <?php bracketpress_partial_display_round($num, BRACKETPRESS_REGION_WEST); ?>
    </div>
    <div class="region region3">
        <h4 class="region3"> SOUTH </h4>
        <?php bracketpress_partial_display_round($num, BRACKETPRESS_REGION_SOUTH); ?>
    </div>
    <div class="region region4">
        <h4 class="region4">EAST</h4>
        <?php bracketpress_partial_display_round($num, BRACKETPRESS_REGION_EAST); ?>
    </div>
</div>
<?php
}

/* === Begin Page === */
?>

<?php if ($message) print $message; // Flash message?>
<?php if (bracketpress()->post->post_excerpt) {
   print "<p>".bracketpress()->post->post_excerpt . "</p>";
}
?>
<font size="+1">Current Bracket Score: <?php print bracketpress()->get_score(); ?></font>
<?php   if (bracketpress()->is_bracket_owner()) {  ?>
<a href="<?php print bracketpress()->get_bracket_permalink(bracketpress()->post->ID, true)?>" style="float: right;">Edit Bracket</a>
<?php } ?>
<br>
Final Game Combined Score Estimate: <?php print  stripslashes(bracketpress()->post->combined_score); ?>
<?php // print "<pre>" . print_r(bracketpress()->post, true) . "</pre>"; ?>
<div class="bracket standings light-blue">
<div id="content-wrapper">
<div id="table">

    <!-- Table Dates -->
    <table class="gridtable">
        <tr>
            <th class="round_1 current"> 1st ROUND</th>
            <th class="round_2 "> 2nd ROUND</th>
            <th class="round_3"> SWEET 16</th>
            <th class="round_4"> ELITE EIGHT</th>
            <th class="round5"> FINAL FOUR</th>
            <th class="round_6"> CHAMPION</th>
            <th class="round_5"> FINAL FOUR</th>
            <th class="round_4"> ELITE EIGHT</th>
            <th class="round_3"> SWEET 16</th>
            <th class="round_2"> 2nd ROUND</th>
            <th class="round_1 current"> 1st ROUND</th>
        </tr>
    </table>
</div>

<div id="bracket">
    <!-- Bracket -->
    <div id="round1" class="round">
        <h3>
            Round One (2013 NCAA Men's Basketball Tournament)
        </h3>

        <div class="region region1">
            <h4 class="region1 first_region">MIDWEST</h4>
            <?php bracketpress_partial_display_seed(BRACKETPRESS_REGION_MIDWEST) ?>
        </div>
        <div class="region region2">
            <h4 class="region2 first_region">WEST</h4>
            <?php bracketpress_partial_display_seed(BRACKETPRESS_REGION_WEST) ?>
        </div>
        <div class="region region3">
            <h4 class="region3 first_region">SOUTH</h4>
            <?php bracketpress_partial_display_seed(BRACKETPRESS_REGION_SOUTH) ?>
        </div>
        <div class="region region4">
            <h4 class="region4 first_region">EAST</h4>
            <?php bracketpress_partial_display_seed(BRACKETPRESS_REGION_EAST) ?>
        </div>


    </div>

<?php
    bracketpress_display_rounds(1, 'One');
    bracketpress_display_rounds(2, 'Two');
    bracketpress_display_rounds(3, 'Three');
    bracketpress_display_rounds(4, 'Four');
?>


    <div id="round5" class="round">
        <h3>Round Five (2013 NCAA Men's Basketball Tournament)</h3>

        <div class="region">
        <?php

            /**
             * This is the final four, and unforunately, the game order depends on the ordering
             * of the matches.
             */

            $matchlist = bracketpress()->matchlist;
            for($x = 1; $x <3; $x++) {
                $match_id = 60 + $x;
                $match = $matchlist->getMatch($match_id);

                $team1 = $match->getTeam1();
                $team2 = $match->getTeam2();

                bracketpress_partial_display_bracket($match_id, $x, $team1, $team2, false, $match);
            }
        ?>
        </div>
    </div>
    <div id="round6" class="round">
        <h3> Round Six (2013 NCAA Men's Basketball Tournament) </h3>

        <div class="region">
        <?php

            $match = $matchlist->getMatch(63);

            $team1 = $match->getTeam1();
            $team2 = $match->getTeam2();

            bracketpress_partial_display_bracket(63, 1, $team1, $team2, $final = true, $match);
        ?>
        </div>
    </div>


    <?php

    function winnerbox() {
        ?>
        <div id="winnerbox" class="round_bx" >
            <center>
                <?php
                $matchlist = bracketpress()->matchlist;
                $finals = $matchlist->getMatch(63);
                $winner = $class = '';

                if ($finals->points_awarded !== NULL) {
                    // Set a class only if we've scored
                    $winnerlist = bracketpress()->winnerlist;
                    if ($winnerlist) {

                        if ($winner->ID === $finals->getWinner()->ID) {
                            $class = "won";
                        } else {
                            $class = "lost";
                            $winner = $winnerlist->getMatch(63)->getWinner();
                        }
                    }
                }
                ?>

                Champion<br>
                <?php // print print_r($finals, true); ?>
                <span class="<?php echo $class ?>"><u> <?php print ($finals->getWinner()->name); ?> </u></span><br>
                <?php if ($winner) print "( {$winner->name} )"; ?>
                <br> Combined Score: <?php print  stripslashes(bracketpress()->post->combined_score); ?>
            </center>
        </div>
        <?php
    }

    ?>

    <?php winnerbox(); ?>

    <div id="brandingbox1" class="round_bx" ><?php print apply_filters('bracketpress_brandingbox1', ''); ?></div>
    <div id="brandingbox2" class="round_bx" ><?php print apply_filters('bracketpress_brandingbox2', ''); ?></div>
    <div id="brandingbox3" class="round_bx" ><?php print apply_filters('bracketpress_brandingbox3', ''); ?></div>

</div>
    
    

