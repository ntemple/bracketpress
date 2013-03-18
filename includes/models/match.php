<?php
/**
 * Logic for handling teams and matches
 */

/** @var wpdb */
global $wpdb;

/**
 * Model class for handling the matches from the database
 */
class BracketPressMatchList {

    // Beginning to abstract the logic so we can support
    // different combiniations of team sizes and brackets.
    static $bracket_size = 8;
    static $num_brackets = 4;
    static $num_matches = 63;

    /* Expensive operations. Cache in memory */
    static $teams = array();
    static $winners_list = array();
    static $matches_list = array();

    var $winners;
    var $matches;
    var $post_id;

    static $bracketpress_matches_order = array(
        array(1, 16),
        array(8, 9),
        array(5, 12),
        array(4, 13),
        array(6, 11),
        array(3, 14),
        array(7, 10),
        array(2, 15)
    ) ;

    function __construct($post_id, $clear = false) {
        global $wpdb;

        if (!self::$teams) {
          self::getTeamList();
        }

        $this->post_id = $post_id;

        if ($clear || ! isset(self::$winners_list[$post_id])) {
            $table_match = bracketpress()->getTable('match');
            $sql = $wpdb->prepare("SELECT match_id, concat('match', match_id) as match_ident, winner_id, points_awarded FROM $table_match WHERE post_id=%d order by match_id", $post_id);
            self::$winners_list[$post_id] = $wpdb->get_results($sql);

        }
        $this->winners = self::$winners_list[$post_id];
        do_action('bracketpress_load_post', array('post_id' => $post_id, 'winners' => $this->winners));

        if ($clear || ! isset(self::$matches_list[$post_id])) {
            $matches = array();
            for ($i = 1; $i <= self::$num_matches; $i++) {
                $match = self::find_match($this->winners, $i);
                $matches[$i] = new BracketPressMatch($i, $post_id,  $match);
            }
            self::$matches_list[$post_id] = $matches;
        }

        $this->matches = self::$matches_list[$post_id];
    }

    function randomize() {
        global $wpdb;

        $table_match = bracketpress()->getTable('match');
        $post = get_post($this->post_id);


        // for each game
        for ($i = 1; $i < 64; $i++) {

            $match = $this->getMatch($i);
            if (! $match->winner_id) {

                $teams = array($match->getTeam1Id(), $match->getTeam2Id() );
                shuffle($teams);
                $winner = array_pop($teams);
                $match->winner_id = $winner;
                $sql = $wpdb->prepare("insert ignore into $table_match (post_id, match_id, winner_id, user_id) values (%d, %d, %d, %d)", $this->post_id, $i, $winner, $post->post_author);
                $wpdb->query($sql);
            }
        }

    }

    function getWinners() {
        return $this->winners;
    }

    /**
     * @param $match_id
     * @return BracketPressMatch
     */
    function getMatch($match_id) {
        return $this->matches[$match_id];
    }


    static function find_match($matches, $needle) {
        foreach ($matches as $match) {
            if ($match->match_id == $needle)
                return $match;
        }
        return false;
    }

    static function getTeamList() {
        global $wpdb;

        $table_team = bracketpress()->getTable('team');

        if (! self::$teams) {
            self::$teams = array();

            $sql ="SELECT  * from $table_team order by region, seed";
            $rows = $wpdb->get_results($sql);

            foreach ($rows as $row) {
                self::$teams[$row->ID] = $row;
            }
        }

        return self::$teams;
    }

    /**
     * Get all the teams for a specific region
     *
     * @param string $region_id
     * @return mixed
     */
    static function getTeams($region_id = '')
    {
        global $wpdb;

        $table_team = bracketpress()->getTable('team');

        $query="select * from $table_team where region=$region_id order by seed asc";
        $teams = $wpdb->get_results( $query );
        return $teams;
    }


    /**
     * Given a game number, calculate the next match number.
     *
     * @static
     * @param $id
     * @return array ($game_id, $slot)
     */

    static function getNextMatch($id) {

        $region = (int) ( ($id-1) / 15) + 1;
        $base = ($region -1) * 15;
        $game_number = ($id - $base);

        // most common case
        $game_id = ($game_number / 2) + self::$bracket_size + $base;
        if (ceil($game_id) == $game_id) {
            $slot = 1;
        }  else {
            $slot = 0;
        }
        $next_game = array( ceil($game_id), $slot);

        // Final Four
        switch ($id) {
            case 15: $next_game = array(62, 0); break;
            case 30: $next_game = array(61, 1); break;
            case 45: $next_game = array(62, 1); break;
            case 60: $next_game = array(61, 0); break;

            case 61: $next_game = array(63, 0); break;
            case 62: $next_game = array(63, 1); break;
        }

        // And beyond
        if ($id > 62)  $next_game = null;

        return $next_game;
    }

    static function getRound($id) {

        if ($id == 63) return 6; // Final game
        if ($id == 62) return 5;
        if ($id == 61) return 5; // Final Four

        $region = (int) ( ($id-1) / 15) + 1;
        $base = ($region -1) * 15;
        $game_number = ($id - $base);

        switch ($game_number) {
            case 15: return 4;
            case 14:
            case 13: return 3;
            case 12:
            case 11:
            case 10:
            case 9:  return 2;
            default: return 1;
        }
    }

    /**
     * Given the current match, should calculate the
     * previous match that got us here.
     *
     * returns the previous two games.
     *
     * @static
     * @param $id
     * @return mixed
     */
    static function getPreviousMatch($id) {

        $previous = null;

        if ($id > 63) throw new Exception("Games out of bound, must be less than 63: $id");
        if ($id < 1)  throw new Exception("Games out of bound, must be greater than 0: $id");

        $region = (int) ( ($id-1) / 15) + 1;
        $base = ($region -1) * 15;

        $game_number = ($id - $base);

        $games = array();
        $games[9]   = array(1, 2);
        $games[10]  = array(3, 4);
        $games[11]  = array(5, 6);
        $games[12]  = array(7, 8);
        $games[13]  = array(9, 10);
        $games[14]  = array(11, 12);
        $games[15]  = array(13, 14);

        if (isset($games[$game_number])) {
                $previous = array(
                   $games[$game_number][0] + $base,
                   $games[$game_number][1] + $base,
            );
        }

        // Special case tournament bracket
        if ($id > 60)  {
            if ($id == 61) $previous = array(60, 30);
            if ($id == 62) $previous = array(15, 45);
            if ($id == 63) $previous = array(61, 62);
        }

        return $previous;
    }

    static function getSeedForMatch($id) {

        $region = (int) ( ($id-1) / 15) + 1;
        $base = ($region -1) * 15;

        $game_number = (int) ($id - $base);

        if ($game_number < 1) throw new Exception("Only the first round is seeded, game $id has no seed");
        if ($game_number > 8) throw new Exception("Only the first round is seeded, game $id has no seed");

        $seed = self::$bracketpress_matches_order[$game_number -1];
        $seed[0] = $region * 100 + $seed[0];
        $seed[1] = $region * 100 + $seed[1];

        return $seed;

    }
}



class BracketPressMatch {

    var $match_id;
    var $winner_id;
    var $post_id;

    function getMatchId() {
        return $this->match_id;
    }

    function getMatchIdent() {
        return 'match' . $this->match_id;
    }

    function getWinnerId() {
        return $this->winner_id;
    }

    function getTeam1Id() {
        $previous_matches = BracketPressMatchList::getPreviousMatch($this->match_id);
        if ($previous_matches) {
            $previous_match = $previous_matches[0];
            $list = new BracketPressMatchList($this->post_id);
            $match = $list->getMatch($previous_match);
            return $match->getWinnerId();
        } else {
            // We have No previous match, get the seed
            $seeds = BracketPressMatchList::getSeedForMatch($this->match_id);
            return $seeds[0];
        }
    }

    function getTeam2Id() {
        $previous_matches = BracketPressMatchList::getPreviousMatch($this->match_id);
        if ($previous_matches) {
            $previous_match = $previous_matches[1];
            $list = new BracketPressMatchList($this->post_id);
            $match = $list->getMatch($previous_match);
            return $match->getWinnerId();
        } else {
            // We have No previous match, get the seed
            $seeds = BracketPressMatchList::getSeedForMatch($this->match_id);
            return $seeds[1];
        }
    }

    function getWinner() {
        $teams = BracketPressMatchList::getTeamList();
        return $teams[$this->getWinnerId()];
    }

    function getTeam1() {
        $teams = BracketPressMatchList::getTeamList();
        return $teams[$this->getTeam1Id()];
    }

    function getTeam2() {
        $teams = BracketPressMatchList::getTeamList();
        return $teams[$this->getTeam2Id()];
    }


    /**
     * @param $match_id
     * @param $post_id
     * @param null $match
     * Note: team_id's are constructed in such a way that the region and seed are encoded
     * in the id's.  team_id = 100*$region + seed
     * @throws Exception
     */

    function __construct($match_id, $post_id, $match = null) {
        $this->post_id = $post_id;  // Is there a better way to do this?
        $this->match_id = $match_id;
        $this->match_ident = 'match' . $match_id;

        if ($match) {
            if ($match_id != $match->match_id) {
                throw new Exception("Match $match_id does not meet expected {$match->match_id}");
            }
            $this->winner_id = $match->winner_id;
            $this->points_awarded = $match->points_awarded;
        }
    }


}
