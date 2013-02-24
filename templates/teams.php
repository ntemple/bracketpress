<h1>Manage Teams</h1>

<?php  if (!class_exists( 'BracketPressPro' )) { ?>
<div class="updated">
<p>
    Don't want to mess with data entry? Rather have a beer than update scoring during the tournament?
    The <a href="http://www.bracketpress.com/downloads/bracketpress-pro-data-feed/" target="store">BracketPress Pro Data Plugin</a> automatically gives you sample data,
    (from 2012), PLUS updates your teams with 2013 on Selection Data.
</p>
<p>
    Every time a game is played, the Pro
    Plugin updates your master bracket, re-calculates scoring, and optionally notifies your users of their
    scores - <i>plus much more!</i>. Note: All features may not be available until after March 10th.
</p>
<p>
<center>
<table width=90%>
<tr><td><li>Automatically populate teams and seeds on Selection Sunday</li></td><td><li>2012 Data Pre-loaded for Testing</li></td><td><li>Premium Support Included w/ PRO</li></td></tr>
<tr><td><li>Automatic Updates of Game Winners and Scores</li></td><td><li>Automatic Re-calculations of Scoring</li></td><td><li>Exclusive Pro Member Forum</li></td></tr>
<tr><td><li>Optional User Notifications of Updates</li></td><td><li>Access to Add-Ons Before the Public</li></td><td><li>Develop Chat Invitations</li></td></tr>
</table>
</center>

</p>
<p>
   <a href="http://www.bracketpress.com/downloads/bracketpress-pro-data-feed/" target="store">Go Pro! Now</a>
</p>
</div>
<p>
    Or, enter the team names, below:
</p>
<?php } else {
?>
<p>BracketPress Pro is installed and is managing your team names. Please note changes below will be overwritten during the tournament. </p>
<?php } ?>

<form id="bracket_fillout_form" name="bracket_fillout_form" method="post">
    <table cellpadding="10px">

    <?php

        $team_info = queries::readBracketData();
        $number_of_teams = queries::getTeamCount();

        function bracketpress_team_for($team_info, $id, $region, $seed) {

            foreach ($team_info as $team) {
                if ($team['ID'] == $id) return $team;
            }
            // Can't find it, done.
            return array('ID' => $id, 'region' => $region, 'seed' => $seed, 'name' => '', 'conference' => '');
        }

        for ($i = 0; $i < NUMBER_OF_TEAMS; $i++) {
            $seed = ($i % 16) + 1;
            $region = (int)($i / 16) + 1;
            $id = $region * 100 + $seed;
            $team = bracketpress_team_for($team_info, $id, $region, $seed);

            $selected_region = array('', '', '', '', '');
            $selected_region[$team['region']] = "selected='selected'";

            $y = $i + 1;
            ?>
            <tr>
                <td>
                    <label for="team_<?php echo $y; ?>">Team <?php echo $y; ?></label>
                    <input type="text" id="team_<?php echo $y; ?>" name="team_<?php echo $y; ?>"
                           value="<?php echo $team['name'] ?>"/>
                </td>
                <td>
                    <label for="region_<?php echo $y; ?>">Region</label>
                    <select id="region_<?php echo $y; ?>" name="region_<?php echo $y; ?>">
                        <?php
                        echo "<option value=''>Choose a region</option>";
                        echo "<option value='1' {$selected_region[1]}>South</option>";
                        echo "<option value='2' {$selected_region[2]}>West</option>";
                        echo "<option value='3' {$selected_region[3]}>East</option>";
                        echo "<option value='4' {$selected_region[4]}>Midwest</option>";
                        ?>
                    </select>
                </td>
                <td>
                    <label for="seed_<?php echo $y; ?>">Seed</label>
                    <select id="seed_<?php echo $y; ?>" name="seed_<?php echo $y; ?>">
                        <option value="<?php echo $team['seed']; ?>"><?php echo $team['seed'];?></option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                        <option value="13">13</option>
                        <option value="14">14</option>
                        <option value="15">15</option>
                        <option value="16">16</option>
                    </select>
                </td>
                <td>
                    <label for="conference_<?php echo $y; ?>">Conference</label>
                    <input type="text" id="conference_<?php echo $y; ?>" name="conference_<?php echo $y; ?>" value="<?php echo $team['conference'] ?>"/>
                </td>

            </tr>
            <?php
        }
        ?>
    </table>
    <input type='hidden' id='form_submitter' name='form_submitter'/>
    <input type="submit" value="Submit" id="submit_the_form"/>
</form>
