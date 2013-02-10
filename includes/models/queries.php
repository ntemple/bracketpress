<?php


/** @var wpdb $wpdb */
global $wpdb;

class queries
{

    public static function readBracketData()
    {
        global $wpdb;
        $table = bracketpress()->getTable('team');
        return $wpdb->get_results("select * from $table order by ID", ARRAY_A);
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

    /**
     * @static
     * This function updates information from the Bracket Data Page forms.
     * It uses the post data from the user to get this information
     *
     * this function will collect all the data from the post array in BracketPress Data
     *  and insert those values into the database.
     *  This function will update or create depending on whether that row exists
     */
    public static function insertBracketData()
    {
        global $wpdb;
        $table = bracketpress()->getTable('team');

        $d = stripslashes_deep($_POST);


        for ($tid = 1; $tid <= NUMBER_OF_TEAMS; $tid++) {
            if (isset($d['team_' . $tid]) && $d['team_' . $tid]) {
                $record = array(
                    'name' => stripslashes($d['team_'.$tid]),
                    'seed' =>$d['seed_'.$tid],
                    'region' => $d['region_'.$tid],
                    'conference' => stripslashes($d['conference_' . $tid])
                );
                $record['ID'] = $record['region'] * 100 + $record['seed'];
                if ($record['ID']) {
                    $sql = $wpdb->prepare("REPLACE INTO $table (`ID`, `name`, `seed`, `region`, `conference`) values (%d,%s,%d,%d,%s)", $record['ID'], $record['name'], $record['seed'], $record['region'], $record['conference']);
                    $wpdb->query($sql);
                }
            }
        }
        return;
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
        // Insert also handles updates.
        return;
    }

    // =========== Location
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

}