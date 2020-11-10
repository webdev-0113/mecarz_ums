<?

class DB {
var $db_connect_id;
    function DB() {
            global $config;
            $this->db_connect_id = mysql_connect("{$config['sqlhost']}", "{$config['sqluser']}", "{$config['sqlpass']}");
            mysql_select_db($config['sqldb'],$this->db_connect_id);
            $this->error();
    }

    function query($query,$fileerror="",$lineerror="") {
            $query1 = @mysql_query($query);
            //global $config;
            //$config[sqls][]=substr($query, 0, 255);;
            //$config[sqlsct]++;
            $this->error($query,$fileerror,$lineerror);
            return $query1;
    }
    function free_result($result) {
            @mysql_free_result($result);
    }     
    function num_rows($query) {
            $query1 = mysql_num_rows($query);
            $this->error($query);
            return $query1;
    }
    function fetch_array($query) {
            $query1 = @mysql_fetch_array($query);
            $this->error($query);
            return $query1;
    }
    function fetch_row($query) {
            $query1 = @mysql_fetch_row($query);
            //$this->error($query);
            return $query1;
    }        
    function insert_id() {
                        $insert_id = mysql_insert_id();
            $this->error();
            return $insert_id;
    } 
    function error($query="",$fileerror="",$lineerror="") {
            $details = mysql_error();
            if ($details != null) {
                         $details=mysql_errno() . ": " . mysql_error() . "\n";                         
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