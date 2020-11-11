<?

class DB {
    var $db_connect_id;
    function DB() {
        global $config;
        $this->db_connect_id = mysqli_connect("{$config['sqlhost']}", "{$config['sqluser']}", "{$config['sqlpass']}", "{$config['sqldb']}");
//        if ($this->db_connect_id->connect_error) {
//            die("Connection failed: " . $this->db_connect_id->connect_error);
//        }
//        die("Connected successfully");

        mysqli_select_db($this->db_connect_id, $config['sqldb']);
        $this->error();
    }

    function query($query, $fileerror="", $lineerror="") {
        $query1 = @mysqli_query($query);
        //global $config;
        //$config[sqls][]=substr($query, 0, 255);;
        //$config[sqlsct]++;
        print($query1);
        $this->error($query,$fileerror,$lineerror);
        return $query1;
    }
    function free_result($result) {
        @mysqli_free_result($result);
    }
    function num_rows($query) {
        $query1 = mysqli_num_rows($query);
        $this->error($query);
        return $query1;
    }
    function fetch_array($query) {
        $query1 = @mysqli_fetch_array($query);
        $this->error($query);
        return $query1;
    }
    function fetch_row($query) {
        $query1 = @mysqli_fetch_row($query);
        //$this->error($query);
        return $query1;
    }
    function insert_id() {
        $insert_id = mysqli_insert_id();
        $this->error();
        return $insert_id;
    }
    function error($query="", $fileerror="", $lineerror="") {
        $details = mysqli_error($this->db_connect_id);
        if ($details != null) {
            $details= mysqli_errno($this->db_connect_id) . ": " . mysqli_error($this->db_connect_id) . "\n";
            ob_start(); // buf2
            print_r($_SERVER);
            $buf2 = ob_get_contents();
            ob_end_clean();

            ///@mail("sales@the-best-cars.com", "mysql error script", $details."\n".$buf2."\n".$query."\n".$fileerror.": ".$lineerror,
            //"From: sales@the-best-cars.com", "-fsales@the-best-cars.com}");
            echo "ERROR...<br><font style=\"color:red; font-size:18px; \">Mysql error".$details."\n".$buf2."\n".$query."\n".$fileerror.": ".$lineerror;
            echo "</font>";
            exit(0);
        }
    }
    function Close() {
        mysql_close($this->db_connect_id);
    }
}

?>