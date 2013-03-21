<?php
/**
 * @package BracketPress
 * @subpackage Display
 *
 * This file is included from the core bracket_edit function and is designed to be a
 * template the $selections and $post variables are all in the
 * bracketpress class before including this file.
 *
 * see bracketpress.php: bracketpress::bracket_edit
 *
 * Note: the output has many duplicated id's. The CSS (and javascript) is keying off id's
 * where it needs to be using classes.  Since it works, we're leaving it alone for now
 * a major piece of contribution would be:
 *
 * - updating the LESS file so it outputs the correct CSS. It's currently out of date.
 * - combining bracket.css and bracket_readonly.css where possible; preferably one
 *   is an override to the other.
 * - refactoring so that classes are used and all id's are unique
 *
 * Javascript Debugging: When BRACKETPRES_DEBUG is set to true, the
 * javascript is loaded inline.
 *
 * Otherwise, it's enqueued and loaded externally.
 *
 */

/**
 * Enqueue Front-End Scripts
 * we need bracketprs, jquery and jquery-ui
 */
function bracketpress_master_enqueue() {
    wp_register_style('bp_bracket', BRACKETPRESS_CSS_URL . 'bracket.css');
    wp_register_style('bp_jquery-ui', BRACKETPRESS_CSS_URL . 'jquery-ui-theme.css');

    wp_enqueue_style('bp_bracket');
    wp_enqueue_style('bp_jquery-ui');

    wp_register_script('bp_master', BRACKETPRESS_URL . 'templates/' . 'bracket_edit.js');

    wp_enqueue_script('jquery');

    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_script('jquery-ui-selectable');
    wp_enqueue_script('jquery-ui-widget');

    wp_enqueue_script('bp_master');
}
add_action('wp_enqueue_scripts', 'bracketpress_master_enqueue');

/**
 * Our javascript variables
 */

function bracketpress_master_header() {
    $matchlist = new BracketPressMatchList(bracketpress()->post->ID);
    ?>
<script type="text/javascript">
    var bracketpress_ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
    var post_id = <?php print bracketpress()->post->ID ?>;
    var sels = eval('(' + '<?php  print json_encode($matchlist->winners); ?>' + ')');
</script>
<?php
}
add_action('wp_head', 'bracketpress_master_header');

/**
 * Show the 16 seeded teams for the bracket
 *
 * @param $region
 * @param $match
 */
function bracketpress_partial_master_seed($region, $match) {

    $base = ($region - 1) * 15;
    $matchlist = new BracketPressMatchList(bracketpress()->post->ID);

    $k = 0;

    print '<div id="round1" xid="round1_' . $region . '" class="round">' . "\n";

    for ($x = 0; $x < BracketPressMatchList::$bracket_size; $x++, $k+=80) {
        $match_id = $base + $x + 1;
        $match = $matchlist->getMatch($match_id);

        $team1 = $match->getTeam1();
        $team2 = $match->getTeam2();

        $team1_name = bracketpress_display_name($team1->name);
        $team2_name = bracketpress_display_name($team2->name);


        $out = <<<EOT
        <div id="match$match_id" class="match" style="top: {$k}px">
          <p id="team_{$team1->ID}" class="slot slot1"><span class="seed">{$team1->seed}</span> {$team1_name}</p>
          <p id="team_{$team2->ID}" class="slot slot2"><span class="seed">{$team2->seed}</span> {$team2_name}</p>
        </div>
EOT;
      print $out;
    }

    print "\n</div>\n";
}

/**
 * Show a single matchup
 *
 * @param $region
 * @param $match_id
 * @param $m
 * @param $count
 */

function bracketpress_master_show_match($region, $match_id, $m, $count) {

    $c1 = $count;
    $c2 = $count +1;

    $out = <<<EOT

            <div id="match$match_id" class="match m$m">
                <p rel="match$c1" class="slot slot1">
                    <select name="get_team_selector_{$region}_match$c1" id="get_team_selector_{$region}_match$c1">
                        <option value="0">~Select~</option>
                    </select>
                </p>
                <p rel="match$c2" class="slot slot2">
                    <select name="get_team_selector_{$region}_match$c2" id="get_team_selector_{$region}_match$c2">
                        <option value="0">~Select~</option>
                    </select>
                </p>
            </div>
EOT;

    print $out;
}

/**
 * Display a full bracket
 *
 * The 8 is the # of teams, set out so we can turn it into
 * a variable later. Could be much optimized.
 *
 * @param $region
 * @param $region_name
 * @param $match
 */

function bracketpress_partial_show_region($region, $region_name, $match) {
    ?>

<div id="reg-<?php print $region ?>">
    <div id="bracket">
        <?php   bracketpress_partial_master_seed($region, $match); ?>
        <div id="round2" class="round round2">
            <?php
            bracketpress_master_show_match($region_name, $match + 8 + 0,  1, $match + 0);
            bracketpress_master_show_match($region_name, $match + 8 + 1,  2, $match + 2);
            bracketpress_master_show_match($region_name, $match + 8 + 2,  3, $match + 4);
            bracketpress_master_show_match($region_name, $match + 8 + 3,  4, $match + 6);
            ?>
        </div>
        <div id="round3" class="round round3">
            <?php
            bracketpress_master_show_match($region_name, $match + 8 + 4, 1, $match + 8);
            bracketpress_master_show_match($region_name, $match + 8 + 5, 2, $match + 10);
            ?>
        </div>
        <div id="round4" class="round round4">
            <?php
            bracketpress_master_show_match($region_name, $match + 8 + 6, 1, $match + 12);
            ?>
        </div>
        <?php $final_match_id = $match + 8 + 6 ?>
        <div id="round5" class="round round5">
            <div class="m1" id="match<?php print $final_match_id ?>-winner">
                <p rel="<?php print $final_match_id ?>">
                    <select  name="get_team_selector_<?php print $region_name ?>_match<?php print $match + 8 + 6 ?>" id="get_team_selector_<?php print $region_name ?>_match<?php print $match + 8 + 6 ?>" style="position:relative;top:-13px;">
                        <option value="0">~Select~</option>
                    </select>
                </p>
            </div>
        </div>
    </div>
</div>
<?php
}
?>

<?php print $message ?><br>
<form method="post">
    <input type="submit" name="cmd_bracketpress_randomize" value="Randomize Bracket" style="float: right">
    <?php if (bracketpress()->get_option('edit_title')) { ?>
    <input type="hidden" name="bracket" value="<?php print bracketpress()->post->ID ?>">
    Title:<br>
    <input type="text" name="post_title" value="<?php echo stripslashes(bracketpress()->post->post_title) ?>"><br>
    Description:<br>
    <textarea name="post_excerpt" rows="4" cols="80"><?php echo stripslashes(bracketpress()->post->post_excerpt) ?></textarea>
    <br>
<?php  } ?>
    Final Game Combined Score:
    <br>
    <input type="text" name="combined_score" size="5" value="<?php echo stripslashes(bracketpress()->post->combined_score) ?>">
    (used for scoring tie breakers)
    <br>
    <input type="submit" name="cmd_bracketpress_save" value="Save">
</form>


<a href="<?php print bracketpress()->get_bracket_permalink(bracketpress()->post->ID, false)?>" style="float: right">View Bracket</a>

<div class="bracket standings light-blue">

<div id="content-wrapper">

<div id="table">
    <table class="gridtable">
        <tr>
            <th>&nbsp;</th>
        </tr>
    </table>
</div>



<div id="tabs" style="position:relative;">
    <div style="position:absolute;left:0;top:64px;width:99%;height:643px;display:none;background:#fff;opacity:0.4;z-index:999;" id="loader-div"><img src="<?php	echo  BRACKETPRESS_IMAGES;?>ajax-loader.gif" style="position:absolute;left:50%;top:50%;" /></div>
    <ul>
        <li><a href="#reg-1">SOUTH</a></li>
        <li><a href="#reg-2">WEST</a></li>
        <li><a href="#reg-3">EAST</a></li>
        <li><a href="#reg-4">MIDWEST</a></li>
        <li><a href="#reg-5">FINALS</a></li>
    </ul>

    <?php
    bracketpress_partial_show_region(1, 'south',   1);
    bracketpress_partial_show_region(2, 'west',    16);
    bracketpress_partial_show_region(3, 'east',    31);
    bracketpress_partial_show_region(4, 'midwest', 46);
    ?>

    <div id="reg-5">
        <h2>Final Four</h2>
        <div id="bracket" style="left: 200; top:50">
            <div id="round1" class="round">
                <div id="match61seed" class="match" style="top: 0px">
                    <p id="match61_team1" class="slot slot1"></p>
                    <p id="match61_team2" class="slot slot2"></p>
                </div>
                <div id="match62seed" class="match" style="top: 80px">
                    <p id="match62_team1" class="slot slot1"></p>
                    <p id="match62_team2" class="slot slot2"></p>
                </div>
            </div>
            <div id="round2" class="round">
                <div id="match61" class="match m1">
                    <p rel="match61" class="slot slot1">
                        <select name="get_team_selector_match61" id="get_team_selector_match61">
                            <option value="0">~Select~</option>
                        </select>
                    </p>
                    <p rel="match62" class="slot slot2">
                        <select name="get_team_selector_match62" id="get_team_selector_match62">
                            <option value="0">~Select~</option>
                        </select>
                    </p>
                </div>
            </div>
            <div id="round5" class="round round5" style="top:-220px; left:320px">
                <div class="m1" id="match63-winner">
                    <p rel="match63">
                        <select  name="get_team_selector_match63" id="get_team_selector_match63" style="position:relative;top:-13px;">
                            <option value="0">~Select~</option>
                        </select>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>


</div>

</div>
