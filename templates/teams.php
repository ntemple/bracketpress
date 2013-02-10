<h1>Manage Teams</h1>
</br>
    <?php

    $team_info = queries::readBracketData();
    $number_of_teams = queries::getTeamCount();

    ?>

<form id="bracket_fillout_form" name="bracket_fillout_form" method="post">

    <?php
      $y=1;
      foreach($team_info as $team):

      $selected_region = array('', '', '', '', '');
      $selected_region[$team['region']] = "selected='selected'";

    ?>

    <label for="team_<?php echo $y; ?>">Team <?php echo $y; ?></label>
    <input type="text" id="team_<?php echo $y; ?>" name="team_<?php echo $y; ?>" value="<?php echo $team['name'] ?>" />

    <label for="region_<?php echo $y; ?>">Region</label>
    <select id="region_<?php echo $y; ?>" name="region_<?php echo $y; ?>">
    <?php
        echo "<option value=''>Choose a region</option>";
        echo "<option value='1' {$selected_region[1]}>South</option>";
        echo "<option value='2' {$selected_region[2]}>East</option>";
        echo "<option value='3' {$selected_region[3]}>West</option>";
        echo "<option value='4' {$selected_region[4]}>Midwest</option>";
    ?>
    </select>

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

      <label for="conference_<?php echo $y; ?>">Conference</label>
      <input type="text" id="conference_<?php echo $y; ?>" name="conference_<?php echo $y; ?>" value="<?php echo $team['conference'] ?>" />

    </br>
    </br>
    </br>

    <?php
    $y++;
endforeach;
    ?>
    <?php for($i=$number_of_teams; $i<NUMBER_OF_TEAMS;$i++):?>

    <label for="team_<?php echo $y; ?>">Team <?php echo $y; ?></label>
    <input type="text" id="team_<?php echo $y; ?>" name="team_<?php echo $y; ?>" value="" />
    <label for="region_<?php echo $y; ?>">Region</label>
    <select id="region_<?php echo $y; ?>" name="region_<?php echo $y; ?>">
        <option value='' selected='selected'>Please Choose a Region</option>";
        <option value="1">South</option>
        <option value="2">East</option>
        <option value="3">West</option>
        <option value="4">MidWest</option>

    </select>

    <label for="seed_<?php echo $y; ?>">Seed</label>
    <select id="seed_<?php echo $y; ?>" name="seed_<?php echo $y; ?>">
        <option value="">Please Choose a Seed</option>
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

    <label for="conference_<?php echo $y; ?>">Conference</label>
    <input type="text" id="conference_<?php echo $y; ?>" name="conference_<?php echo $y; ?>" />

    </br>
    </br>
    </br>
    <?php
    $y++;
endfor;

    ?>

    <input type='hidden' id='form_submitter' name='form_submitter' />
    <input type="submit" value="Submit" id="submit_the_form"/>
</form>
