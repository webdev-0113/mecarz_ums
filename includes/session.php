<?

function sessao_open($aSavaPath, $aSessionName)
{
    global $aTime;
    sessao_gc( $aTime );
    return True;
}
function sessao_close()
{
    return True;
}
function sessao_read( $aKey )
{
    global $db,$HTTP_COOKIE_VARS,$_SERVER,$config;
    $query = "SELECT DataValue FROM {$config['table_prefix']}sessions WHERE ip='$aKey' or ip='".user_ipses()."' or SessionID='$aKey' ";
    $busca =$db->db_connect_id->query($query);
    if(mysqli_num_rows($busca) == 1)
    {
        $r = mysqli_fetch_array($busca);
        @mysqli_free_result($busca);
        return $r['DataValue'];
    } ELSE {
        if ( $HTTP_COOKIE_VARS['username_cookie'] == "" ) $cookie="-";
        else  $cookie=$HTTP_COOKIE_VARS['username_cookie'];
        $url=urlencode($_SERVER["REQUEST_URI"]);
        $ip = user_ipses();

        $query = "INSERT INTO {$config['table_prefix']}sessions (SessionID, LastUpdated, DataValue,`username`,`location`,`ip`)
                      VALUES ('$aKey', NOW(), '','$cookie','$url','$ip')";
        mysqli_query($db->db_connect_id, $query);

        return "";
    }
}
function sessao_write( $aKey, $aVal )
{
    global $db,$HTTP_COOKIE_VARS,$_SERVER,$config;
    $aVal = addslashes( $aVal );
    if ( $HTTP_COOKIE_VARS['username_cookie'] == "" ) $cookie="-";
    else  $cookie=$HTTP_COOKIE_VARS['username_cookie'];
    $url=urlencode($_SERVER["REQUEST_URI"]);
    $ip = user_ipses();

    $query = "UPDATE {$config['table_prefix']}sessions SET DataValue = '$aVal', LastUpdated = NOW(),`username`='$cookie',`location`='$url',`ip`='{$ip}' WHERE SessionID = '$aKey'  or ip='".user_ipses()."'";
    mysqli_query($db->db_connect_id, $query);

    return True;
}
function sessao_destroy( $aKey )
{
    global $db,$config;
    $query = "DELETE FROM {$config['table_prefix']}sessions WHERE SessionID = '$aKey'";
    mysqli_query($db->db_connect_id, $query);
    $db->close();
    return True;
}
function sessao_gc( $aMaxLifeTime )
{
    global $db,$config;
    $aMaxLifeTime=$config['aMaxLifeTime'];
    $query = "DELETE FROM {$config['table_prefix']}sessions WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(LastUpdated) > '$aMaxLifeTime'";
    mysqli_query($db->db_connect_id, $query);
    return True;
}
function user_ipses()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
        $ip = getenv("REMOTE_ADDR");
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
        $ip = $_SERVER['REMOTE_ADDR'];
    else
        $ip = "unknown";
    return($ip);
}

session_set_save_handler("sessao_open", "sessao_close", "sessao_read", "sessao_write", "sessao_destroy", "sessao_gc");

function logs($onlineusers)
{
    global $db, $Global_Class, $tpl,$lang;
    global $config, $_REQUEST, $language_set;

    $exp = $config['aMaxLifeTime'];
    $expired = time() - $exp;
    $sql = "select * from {$config['table_prefix']}sessions WHERE UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(LastUpdated) < '$exp' order by LastUpdated desc";

    $result = $db->db_connect_id->query( $sql );
    $num_rows = mysqli_num_rows( $result );
    $onlineusers=$num_rows;
    $contor=1;
    $out = '';
    if ( $num_rows > 0 ) {
        while ( $user = mysqli_fetch_assoc( $result ) ) {
            if ($user[username]=='-') $user[username]=$lang['tpl_auto_guess'];
            $user['location']=urldecode($user['location']);
            if ($contor%2) $user['class_temp']="class_temp1";
            else $user['class_temp']="class_temp2";

            $out .= $tpl->replace( $user, "logs1.html" );
            $contor++;
        } // while
        @mysqli_free_result($result);
    }

    return $out;
}
function top()
{
    global $db, $Global_Class, $tpl,$lang;
    global $config, $_REQUEST, $language_set, $VisitClass;

    $exp = $config['aMaxLifeTime'];
    $expired = time() - $exp;

    $config['admin_number_intop']=($config['admin_number_intop']<=0)?5:$config['admin_number_intop'];
    $sql = "select * from {$config['table_prefix']}cars WHERE 1 order by noview desc limit {$config['admin_number_intop']}";

    $result = $db->db_connect_id->query( $sql );
    $num_rows = mysqli_num_rows( $result );
    $onlineusers=$num_rows;
    $contor=1;
    $out = '';
    if ( $num_rows > 0 ) {
        while ( $user = mysqli_fetch_assoc( $result ) ) {

            $user = $VisitClass->prepareuser($user);

            if ($contor%2) $user['class_temp']="class_temp1";
            else $user['class_temp']="class_temp2";

            $out .= $tpl->replace( $user, "logs3.html" );
            $contor++;
        } // while
        @mysqli_free_result($result);
    }

    return $out;
}
?>