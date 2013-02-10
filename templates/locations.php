
<link rel="stylesheet" href="<?php echo BRACKETPRESS_CSS_URL ?>jquery-ui.css" type="text/css">
<script src="<?php echo BRACKETPRESS_CSS_URL ?>jquery-1.8.3.js"></script>
<script src="<?php echo BRACKETPRESS_CSS_URL ?>jquery-ui.js"></script>
<h1>Manage Locations</h1>
</br>
<?php

    $location_info = queries::readLocationData();
    $number_of_locations = queries::getLocationCount();

?>

    <form id="location_fillout_form" name="location_fillout_form" method="post">

    <?php
    $y=1;
    foreach($location_info as $location):
        $selected_location = array('', '', '', '', '');
        $selected_location[$location['game_time']] = "selected='selected'";
        ?>
        <input type="hidden" name="location_id_<?php echo $y; ?>" value="<?php echo $location['ID']; ?>" />
        <label for="venue_<?php echo $y; ?>">Venue</label>
        <input type="text" id="venue_<?php echo $y; ?>" name="venue_<?php echo $y; ?>" value="<?php echo $location['venue'] ?>" />

        <label for="gamedate_<?php echo $y; ?>">Game Date</label>
        <input type="text" id="gamedate_<?php echo $y; ?>" name="gamedate_<?php echo $y; ?>" value="<?php echo $location['game_date']; ?>" />

        <label for="gametime_<?php echo $y; ?>">Game Time</label>
        <select id="gametime_<?php echo $y; ?>" name="gametime_<?php echo $y; ?>">
            <?php
                 echo ("<option value=''>Choose a time</option>");
                 echo ("<option value='12:00:00' {$selected_location['12:00:00']}>12PM</option>");
                 echo ("<option value='15:00:00' {$selected_location['15:00:00']}>3PM</option>");
                 echo ("<option value='18:00:00' {$selected_location['18:00:00']}>6PM</option>");
                 echo ("<option value='20:00:00' {$selected_location['20:00:00']}>8PM</option>");
            ?>
        </select>


        </br>
        </br>
        <script>
            $('#gamedate_<?php echo $y; ?>').datepicker({ dateFormat: "mm-dd-yy" });
        </script>
        <?php
        $y++;

    endforeach;
    ?>
        <?php for($i=$number_of_locations; $i<NUMBER_OF_LOCATIONS;$i++):?>

<!--        <input type="text" name="location_id_insert--><?php //echo $y; ?><!--" value="--><?php //echo $location['ID']; ?><!--" />-->
        <label for="venue_<?php echo $y; ?>">Venue</label>
        <input type="text" id="venue_<?php echo $y; ?>" name="venue_<?php echo $y; ?>" value="" />

        <label for="gamedate_<?php echo $y; ?>">Game Date</label>
        <input type="text" id="gamedate_<?php echo $y; ?>" name="gamedate_<?php echo $y; ?>" value="" />

        <label for="gametime_<?php echo $y; ?>">Game Time</label>
        <select id="gametime_<?php echo $y; ?>" name="gametime_<?php echo $y; ?>">
            <?php
                _e("<option value=''>Choose a time</option>");
                _e("<option value='12:00:00' {$selected_location['12:00:00']}>12PM</option>");
                _e("<option value='15:00:00' {$selected_location['15:00:00']}>3PM</option>");
                _e("<option value='18:00:00' {$selected_location['18:00:00']}>6PM</option>");
                _e("<option value='20:00:00' {$selected_location['20:00:00']}>8PM</option>");
            ?>
        </select>

        </br>
        </br>
        </br>
        <script>
            $('#gamedate_<?php echo $y; ?>').datepicker({ dateFormat: "mm-dd-yy" });

        </script>
        <?php
        $y++;
    endfor;

        ?>
        <input type="hidden" id="location_form_submitter" name="location_form_submitter" />
        <input type="submit" value="Submit" id="submit_the_location_form"/>
</form>
<script>

</script>