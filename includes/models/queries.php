<?php


/** @var wpdb $wpdb */
global $wpdb;

class queries
{

    public static function readBracketData()
    {
        global $wpdb;
        $table = bracketpress()->getTable('team');
        $results = $wpdb->get_results("select * from $table order by region, seed");

        $main_array = array();
        foreach($results as $result)
        {
            $team_data =array();
            $team_data['ID'] = $result->ID;
            $team_data['name'] = $result->name;
            $team_data['seed'] = $result->seed;
            $team_data['region'] = $result->region;
            $team_data['conference'] = $result->conference;

            array_push($main_array, $team_data);
        }
        return $main_array;

    }

    /**
     * This function will count the number of teams in the database and return it
     * @static
     * @return integer
     */

    public static function getTeamCount()
    {
        global $wpdb;

        $table = bracketpress()->getTable('team');
        $results = $wpdb->get_var("select count(*) from $table");
        return $results;
    }

    public static function getLocationCount()
    {
        global $wpdb;
        $table = bracketpress()->getTable('location');
        $results = $wpdb->get_var("select count(*) from $table");
        return $results;
    }

    public static function updateBracketData()
    {
        $y = 1;
        global $wpdb;
        $table = bracketpress()->getTable('team');
        for($i=0; $i<NUMBER_OF_TEAMS;$i++)
        {
            $data[$i] = array(
                //'ID' =>(($_POST['region_'.$y]*100)+$_POST['seed_'.$y]),
                'seed'=> $_POST['seed_'.$y],
                'name'=> $_POST['team_'.$y],
                'region'=> $_POST['region_'.$y],
                'conference' => $_POST['conference_'.$y]);
            $where[$i] = array('ID'=>( ($_POST['region_'.$y]*100)+$_POST['seed_'.$y] ) );
            $wpdb->update($table, $data[$i],$where[$i],null,null);
            $y++;
        }

    }

    public static function updateLocationData()
    {

        $y = 1;
        global $wpdb;
        $table = bracketpress()->getTable('location');
        for($i=0; $i<NUMBER_OF_LOCATIONS;$i++)
        {

            $date = date("Y-m-d", strtotime(str_replace('-', '/', $_POST['gamedate_'.$y])));
            $data[$i] = array(
                'match_ident' => $_POST['match_ident_'.$y],
                'venue'=> $_POST['venue_'.$y],
                'game_date'=> $date,
                'game_time'=> $_POST['gametime_'.$y]);
            $where[$i] = array('ID'=>( $_POST['location_id_'.$y] ));
            $wpdb->update($table, $data[$i],$where[$i],null,null);
            $y++;
        }
    }

    public static function readLocationData()
    {
        global $wpdb;
        $table = bracketpress()->getTable('location');
        $results = $wpdb->get_results("select * from $table");

        $main_array = array();
        foreach($results as $result)
        {
            $date = date("m-d-Y", strtotime(str_replace('-', '/', $result->game_date)));

            $location_data =array();
            $location_data['ID'] = $result->ID;
            $location_data['match_ident'] = $result->match_ident;
            $location_data['venue'] = $result->venue;
            $location_data['game_date'] = $date;
            $location_data['game_time'] = $result->game_time;

            array_push($main_array, $location_data);
        }

        return $main_array;
    }

    public static function insertLocationData()
    {

        global $wpdb;

        $number_of_locations = self::getLocationCount();
        $location_input_spot = $number_of_locations +1;
        $table = bracketpress()->getTable('location');

        for($i=$location_input_spot; $i<=NUMBER_OF_LOCATIONS;$i++)
        {
            $date = date("Y-m-d", strtotime(str_replace('-', '/', $_POST['gamedate_'.$i])));
            $data[$i] = array(

              //  'ID'=> $_POST['location_id_'.$location_input_spot],
               // 'match_ident' =>$_POST['match_ident_'.$location_input_spot],
                'venue' => $_POST['venue_'.$i],
                'game_date' => $date,
                'game_time' => $_POST['gametime_'.$i]
            );

            if($data[$i]['venue'] !== '' || $data[$i]['game_date'] !== '' || $data[$i]['game_time'] !== '' ) //this keeps blank input fields from being inserted into the database
            {
                $wpdb->insert($table,$data[$i]);

            }

            //TODO the else condition could be a message to let the user know what they did wrong

        }
    }

    /**
     * @static
     * This function updates information from the Bracket Data Page forms.
     * It uses the post data from the user to get this information
     */
    public static function insertBracketData()
    {
        //this function will collect all the data from the post array in BracketPress Data
        //and insert those values into the database.
        //This function will update or create depending on whether that row exists
        global $wpdb;
        $number_of_teams = self::getTeamCount();
        $team_input_spot = $number_of_teams +1;
        //echo $number_of_teams.'</br>';
        //echo $team_input_spot.'</br>';
        $table = bracketpress()->getTable('team');
        for($i=$team_input_spot; $i<=NUMBER_OF_TEAMS;$i++)
        {
            $data[$i] = array(
                'ID'=>( ($_POST['region_'.$team_input_spot]*100)+$_POST['seed_'.$team_input_spot] ),
                'name' =>$_POST['team_'.$team_input_spot],
                'seed' =>$_POST['seed_'.$team_input_spot],
                'region' => $_POST['region_'.$team_input_spot],
                'conference' => $_POST['conference_' . $team_input_spot]);

            if($data[$i]['ID'] !== 0) //this keeps blank input fields from being inserted into the database
                $wpdb->insert($table,$data[$i]);

        }

    }

}