<?php

class BracketPressInstaller {

    var $new_db_version;

    function update_tables($current_version = '0.0.0') {
        // http://codex.wordpress.org/Creating_Tables_with_Plugins
        global $wpdb;

        $prefix = $wpdb->prefix . "bracketpress";

        $sql = $this->parse_sql($prefix, 'bp');

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * @param string $prefix
     * @param string $old_prefix
     * @return string
     * @throws Exception
     */

    function parse_sql($prefix = 'wp_bracketpress', $old_prefix = 'bp') {

        $magic_header = '<?php die(); // dbschema ?>';
        $lines = file(dirname(__FILE__) . '/install.sql.php');
        $header = trim(array_shift($lines)); // drop die statement
        if ($header != $magic_header) throw new Exception('database schema not found');

        $this->new_db_version = trim(array_shift($lines)); // get version

        $sql = '';

        $creating = false;

        foreach ($lines as $line) {

            if (strpos($line, 'CREATE') === 0) {
                $creating = true;
                $line = str_replace($old_prefix, $prefix, $line);
            }
            if (strpos($line, ';') > 0) {
                $line = ");\n"; // remove "ENGINE"
            }


            if ($creating) {
                $line = str_replace('`', '', $line);
                $line = str_replace('PRIMARY KEY (', 'PRIMARY KEY  (', $line);
                $sql .= $line;
            }

            if (strpos($line, ';') > 0) {
                $creating = false;
            }

        }
        return $sql;
    }
}
