<?php
function precent($variable,$total){
    $precent=round(($variable*100)/$total,2);
    $precent = sprintf("%01.2f", $precent);
    return ($precent);
}


function savesql($str)
{
    if (get_magic_quotes_gpc())
    {
        $str=stripslashes($str);
    }
    return (!is_callable('mysql_real_escape_string'))?mysql_escape_string($str):mysql_real_escape_string($str);
}

function time_diff($time)
{ // calculate elapsed time (in seconds!)
    global $lang;
    $diff = time()-$time;
    $daysDiff = floor($diff/60/60/24);
    $diff -= $daysDiff*60*60*24;
    $hrsDiff = floor($diff/60/60);
    $diff -= $hrsDiff*60*60;
    $minsDiff = floor($diff/60);
    $diff -= $minsDiff*60;
    $secsDiff = $diff;
    if ($secsDiff>0){
        $returnout=$secsDiff.$lang['tpl_auto_sec']." ";
    }
    if ($minsDiff>0){
        $returnout=$minsDiff.$lang['tpl_auto_mins']." ";
    }
    if ($hrsDiff>0){
        $returnout=$hrsDiff.$lang['tpl_auto_hours']." ";
    }
    if ($daysDiff>0){
        $returnout=$daysDiff.$lang['tpl_auto_days']." ";
    }
    return ($returnout);
}
function addlogging( $row )
{
    global $mn, $db, $lang, $_POST,$config, $Global_Class,$_COOKIE;
    $cookie=$_COOKIE['username_cookie'];
    if ($cookie!='thebestrealestate' and $row['admin']!='thebestrealestate'){

        $sql="SHOW FIELDS FROM `{$config['table_prefix']}logging` ";
        $result = $db->db_connect_id->query($sql);
        $array_not = array("ctime");

        $sql = "insert into `{$config['table_prefix']}logging` values (";
        while ($tablefield_array_r = mysqli_fetch_array($result)){
            if (!in_array($tablefield_array_r['Field'],$array_not)) {
                $sql .= " '".addslashes($row[$tablefield_array_r['Field']])."', " ;
            }

        }

        $sql .= "now() )";
        $result = $db->db_connect_id->query( $sql );
    }
}

function GetTimes( $arrival,$departure ,$type=0){
    global $db, $Global_Class, $tpl, $language_set;
    global $config, $_REQUEST, $lang, $IMG_HEIGHT,$IMG_WIDTH,$settings_profile,$_SESSION;

    global $db,$_SESSION;

    $start_perioad = strtotime($arrival);
    $end_perioad = strtotime("-0 day",strtotime($departure));

    $start_perioad1=$start_perioad;
    $end_perioad1=$end_perioad;

    $start_perioad=$start_perioad1;
    $end_perioad=$end_perioad1;
    $time=$start_perioad;

    if ($type<=0){
        $i=0;



        while ($time<=$end_perioad){

            $date=date("Y-m-d",$time);
            $return['months'][$i]=$date;
            $i++;

            $time=strtotime("+$i month ",$start_perioad);

        }
        $time=strtotime("-1 month ",$time);
    }

    if ($type<=1){
        $i=0;
        $start_perioad=$time;
        $end_perioad=$end_perioad1;
        $time=$start_perioad;

        while ($time<=$end_perioad){

            $date=date("Y-m-d",$time);
            $return['weeks'][$i]=$date;
            $i++;

            $time=strtotime("+$i week ",$start_perioad);

        }

        $time=strtotime("-1 week ",$time);
    }

    $i=0;
    $start_perioad=$time;
    $end_perioad=$end_perioad1;
    $time=$start_perioad;

    while ($time<=$end_perioad){

        $date=date("Y-m-d",$time);
        $return['days'][$i]=$date;
        $i++;

        $time=strtotime("+$i day ",$start_perioad);

    }
    return $return;
}

function PrepareForStore($variable){
    if (is_array($variable)){
        foreach ($variable as $key=>$val){
            if (!get_magic_quotes_gpc()) {
                $variable[$key] = trim(addslashes($val));
            }
        }
    }else{
        if (!get_magic_quotes_gpc()) {
            $variable = trim(addslashes($variable));
        }
    }
    return ($variable);
}
function PrepareForWrite ($variable) {
    if (is_array($variable)){
        foreach ($variable as $key=>$val){
            if (get_magic_quotes_gpc()){
                $variable[$key] = trim(stripslashes($val));
            }
        }
    }
    else{
        if (get_magic_quotes_gpc()) {
            $variable = trim(stripslashes($variable));
        }
    }
    return ($variable);
};
function sort_order($order,$order_now,$sort_now){
    if ($order_now == $order){
        $order = ( ($sort_now == "desc") || ($sort_now == "") ) ? "asc":"desc";
    }else{
        $order = $sort_now;
    }
    $order = ( ($order == "desc") || ($order == "") ) ? "asc":"desc";
    return $order;
}
function yes_or_no($val,$yes_no=1){
    global $lang;
    $temp ="selected$val";
    $$temp = "selected";

    $out = "<option $selected1 value='1'>{$lang['yes']}</option>\n";
    $out .= "<option $selected0 value='0'>{$lang['no']}</option>\n";
    if ($yes_no and $config['show_only_yes_no_for_active']){
        $out .= "<option $selected2 value='2'>{$lang['sold']}</option>\n";
        $out .= "<option $selected3 value='3'>{$lang['BRANDNEW']}</option>\n";
        $out .= "<option $selected4 value='4'>{$lang['EXDEMO']}</option>\n";
    }
    return $out;
}

function nr_afis($val){
    global $config;
    $out = number_format($val, $config["config_price3_format"], $config["config_price1_format"], $config["config_price2_format"] );
    return $out;
}
function dateformat($format,$val){
    global $config;
    $out=@date($format,$val);;
    $out=str_replace(
        array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"),
        $config["months_name"],
        $out);
    return $out;
}
function makeurlold($url){

    $user['url'] = strip_tags($url);
    $user['url'] = preg_replace("-", "_", $user['url']);
    $user['url'] = preg_replace(" |\/", "-", $user['url']);
    $user['url'] = preg_replace("!#[0-9]{0,4};", "", $user['url']);
    $user['url'] = str_replace("!","", $user['url']);
    $user['url'] = str_replace("_","", $user['url']);
    $user['url'] = str_replace("#","", $user['url']);
    $user['url'] = str_replace("%","", $user['url']);
    $user['url'] = str_replace("/","-", $user['url']);
    $user['url'] = preg_replace(":|,|\.|\(|\)|`|&", "", $user['url']);
    $user['url'] = preg_replace("-+", "-", $user['url']);
    $user['url'] = preg_replace("^-", "", $user['url']);
    return $user['url'];
}
function makeurl($url){
    $user['url'] = strip_tags($url);
    $user['url'] = preg_replace("-", "_", $user['url']);
    $user['url'] = preg_replace(" |\/", "-", $user['url']);
    $user['url'] = preg_replace("!#[0-9]{0,4};", "", $user['url']);
    $user['url'] = str_replace(array("\r\n","\n","\t"),"", $user['url']);
    $user['url'] = str_replace("!","", $user['url']);
    $user['url'] = str_replace("_","", $user['url']);
    $user['url'] = str_replace("#","", $user['url']);
    $user['url'] = str_replace("|","", $user['url']);
    $user['url'] = str_replace(array("%","'",'"',"`","\\","\\'"),"", $user['url']);
    $user['url'] = str_replace(array("@","+"),"-", $user['url']);
    $user['url'] = str_replace(array("***","**","*","[","]","&amp;"),"-", $user['url']);
    $user['url'] = str_replace('**',"-", $user['url']);
    $user['url'] = preg_replace(":|,|\.|\(|\)|`|&", "", $user['url']);
    $user['url'] = preg_replace("-+", "-", $user['url']);
    $user['url'] = preg_replace("^-", "", $user['url']);

    return ascii_encode(strtolower($user['url']));
}

function makeurl2($url){
    $user['url'] = strip_tags($url);
    //$user['url'] = preg_replace("-", "_", $user['url']);
    $user['url'] = preg_replace(" |\/", "-", $user['url']);
    $user['url'] = preg_replace("!#[0-9]{0,4};", "", $user['url']);
    $user['url'] = str_replace(array("\r\n","\n","\t"),"", $user['url']);
    $user['url'] = str_replace("!","", $user['url']);
    $user['url'] = str_replace("_","", $user['url']);
    $user['url'] = str_replace("#","", $user['url']);
    $user['url'] = str_replace("|","", $user['url']);
    $user['url'] = str_replace(array("%","'",'"',"`","\\","\\'"),"", $user['url']);
    $user['url'] = str_replace(array("@","+"),"-", $user['url']);
    $user['url'] = str_replace(array("***","**","*","[","]","&amp;"),"-", $user['url']);
    $user['url'] = str_replace('**',"-", $user['url']);
    $user['url'] = preg_replace(":|,|\.|\(|\)|`|&", "", $user['url']);
    $user['url'] = preg_replace("-+", "-", $user['url']);
    $user['url'] = preg_replace("^-", "", $user['url']);

    return ascii_encode(strtolower($user['url']));
}

function ascii_encode($string,$aaa=47)  {
    $encoded="";

    for ($i=0; $i < strlen($string); $i++)  {
        $chars=substr($string,$i,1);

        if (ord($chars)>=48 and ord($chars)<=57 ){
            $encoded.=$chars;
        }elseif (ord($chars)>=65 and ord($chars)<=90 ){
            $encoded.=$chars;
        }elseif (ord($chars)>=97 and ord($chars)<=122 ){
            $encoded.=$chars;
        }elseif ( in_array(ord($chars),array(45,47,45,95,$aaa)) ){
            $encoded.=$chars;
        }

    }

    return $encoded;
}

function fortemplates($text){


    $text = str_replace("}}","&#".ord('}')."&#".ord('}'), $text);
    $text = str_replace("{{","&#".ord('{')."&#".ord('{'), $text);
    return $text;
}
function fortemplatestosave($text){


    $text = str_replace("&#".ord('}')."&#".ord('}'),"}}", $text);
    $text = str_replace("&#".ord('{')."&#".ord('{'),"{{", $text);
    return $text;
}

function writetofile($filename,$somecontent){
    global $lang;
    if ($filename==''){
        $out = $lang['tpl_auto_no_filename'];
    }

    // Let's make sure the file exists and is writable first.
    {

        // In our example we're opening $filename in append mode.
        // The file pointer is at the bottom of the file hence
        // that's where $somecontent will go when we fwrite() it.
        if (!$handle = fopen($filename, 'w')) {
            $out .= $lang['tpl_auto_Cannot_open_file']." ($filename)";
        }

        // Write $somecontent to our opened file.
        if (!fwrite($handle, $somecontent)) {
            $out .= " ($filename)";
        }

        $out .= $lang['tpl_auto_Success_wrote_to_file']." ($filename)\n";

        fclose($handle);
        return $out;
    }

}
function checkoverapping($roomid="carid"){
    global $output_add,$o,$language_set,$db,$config,$tpl,$HTTP_POST_VARS,$_REQUEST,$Global_Class,$lang,$sql_default_global,$default_tabel,$valoare_1;

    $tablefield_array_r['Field']="date_start";

    $temp = explode("-",$_POST["input_".$tablefield_array_r['Field']]);
    $var['tpl_input_day']=$temp[0];
    $var['tpl_input_month']=$temp[1];
    $var['tpl_input_year'] = $temp[2];



    if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
        $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
    }
    $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']}";
    $variable[$tablefield_array_r['Field']]=date ("Y-m-d",strtotime($variable[$tablefield_array_r['Field']]));
    $time1=strtotime($variable[$tablefield_array_r['Field']]);

    $tablefield_array_r['Field']="date_ends";


    $temp = explode("-",$_POST["input_".$tablefield_array_r['Field']]);
    $var['tpl_input_day']=$temp[0];
    $var['tpl_input_month']=$temp[1];
    $var['tpl_input_year'] = $temp[2];

    if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
        $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
    }
    $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']}";
    $variable[$tablefield_array_r['Field']]=date ("Y-m-d",strtotime($variable[$tablefield_array_r['Field']]));
    $time2=strtotime($variable[$tablefield_array_r['Field']]);
    if ($time1>$time2){
        $output_add[1]=$lang['error_From_Date_must_be_before_To_Date'];
        if ($output_add[1] != "") {
            $output_add[0] == false;
        }
    }
    if ($o=="edit1"){
        $sqlid=" and id!='{$_REQUEST['id']}' ";
    }
    $roomspriceprofile = $Global_Class->getprofilefirst($default_tabel,  " and `carid`='$valoare_1' and date_start>='{$variable['date_start']}' and date_start<='{$variable['date_ends']}' $sqlid");
    $roomspriceprofile1 = $Global_Class->getprofilefirst($default_tabel, " and `carid`='$valoare_1' and date_ends>='{$variable['date_start']}' and date_ends<='{$variable['date_ends']}' $sqlid");
    if ($roomspriceprofile or $roomspriceprofile1){
        $output_add[1]=$lang['error_OVERAPPING_PERIODS'];
        if ($output_add[1] != "") {
            $output_add[0] == false;
        }
    }


}

function get_user_ip()
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

function checkbanned_ips(){
    global $language_set,$db,$config,$tpl,$_POST,$_REQUEST,$Global_Class,$lang,$sql_default_global,$default_tabel,$valoare_1,$lang;

    $ip=get_user_ip();


    $sql = "SELECT count(*) FROM `{$config['table_prefix']}logging` WHERE `admin`='$ip' and `ctime` LIKE CONCAT(SUBSTRING(NOW(),1,13),'%')";
    $result = $db->db_connect_id->query( $sql );

    $row=mysqli_fetch_array($result);
    @mysqli_free_result($result);

    if ( $row[0] >=$config['banned_ip_after_try_to_loggin'] and $config['banned_ip_after_try_to_loggin']>0)
    {
        die($lang["tpl_auto_You_have_been_banned_for_1hour"]);
        exit(0);
    }

}

function banned_ips(){
    global $language_set,$db,$config,$tpl,$_POST,$_REQUEST,$Global_Class,$lang,$sql_default_global,$default_tabel,$valoare_1,$lang;

    $ip=get_user_ip();



    preg_match('/([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})/', $ip, $user_ip_parts);
    $ips_profile = $Global_Class->getprofilefirst(  "ips", " and active=1 and "." ( ip = '" . $user_ip_parts[1] .".". $user_ip_parts[2] .".". $user_ip_parts[3] .".". $user_ip_parts[4] . "' or ip =
                '" . $user_ip_parts[1] .".". $user_ip_parts[2] .".". $user_ip_parts[3] . ".*' or ip =  '" . $user_ip_parts[1] .".". $user_ip_parts[2] . ".*.*' or ip =  '" . $user_ip_parts[1] . ".*.*.*')"." and date_start<=NOW() and NOW()<=date_ends " );

    if ( $ips_profile )
    {
        die($lang["tpl_auto_You_have_been_banned"]);
        exit(0);
    }

}

function dayshours($weeks,$hours){
    global $lang;

    $timenew=$weeks*7*24;

    $timediff=$timenew-$hours;

    $days=floor($timediff/24);

    $hourremain=$timediff-($days*24);

    $return=str_replace("{days}",$days,$lang['tpl_auto_expire_in_days_and_hours']);
    $return=str_replace("{hours}",$hourremain,$return);

    return $return;
}

function convert_time($mysql_timestamp){
    // YYYYMMDDHHMMSS
    if (ereg("([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})",$mysql_timestamp,$res)){
        $info["year"]=$res[1];
        $info["month"]=$res[2];
        $info["day"]=$res[3];
        $info["hour"]=$res[4];
        $info["min"]=$res[5];
        $info["sec"]=$res[6];
        if (!checkdate($res[2],$res[3],$res[1])) {
            return(false);
        }
        return(mktime($info["hour"],$info["min"],$info["sec"],
            $info["month"],$info["day"],$info["year"]));
    }elseif (ereg("([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})",$mysql_timestamp,$res)){
        $info["year"]=$res[1];
        $info["month"]=$res[2];
        $info["day"]=$res[3];
        $info["hour"]=$res[4];
        $info["min"]=$res[5];
        $info["sec"]=$res[6];
        if (!checkdate($res[2],$res[3],$res[1])) {
            return(false);
        }
        return(mktime($info["hour"],$info["min"],$info["sec"],
            $info["month"],$info["day"],$info["year"]));
    }else{
        return(false);
    };
}

function updatejavascript($up_){
    global $config,$db,$VisitClass,$Global_Class,$array_lang;


    foreach ($config['admin_section']['cars']['dropdown_fields'] as $key=>$val) {
        $sql = "SELECT * from {$config['table_prefix']}$val where 1";
        $result = $db->db_connect_id->query($sql);
        $num_rows = mysqli_num_rows($result);
        $javascript_profile[$val."javascript"]=array();
        if ($num_rows > 0){
            while ($user = mysqli_fetch_assoc($result)){
                $javascript_profile[$val."javascript"][$user['id']]=$user;
            }
            @mysqli_free_result($result);
        }
    }
    $sql1__="";
    if ($up_){
        $javascript_profile['makemodeljavascript']=array();
        foreach ($array_lang as $language_set=>$nname){
            if ($language_set==0) $language_set1="";
            else $language_set1=$language_set;
            $somecontent = $Global_Class->getjavascriptarray("make","name{$language_set1}","id","name{$language_set1}","model","name{$language_set1}","id","name{$language_set1}","makeid");
            writetofile($config['path'].'temp/makemodel'.$language_set1.'.txt',$somecontent);
        }
    }
    $sql = "UPDATE `{$config['table_prefix']}javascript` SET
        categoryjavascript='".addslashes(serialize($javascript_profile["categoryjavascript"]))."',
        makejavascript='".addslashes(serialize($javascript_profile["makejavascript"]))."',
        modeljavascript='".addslashes(serialize($javascript_profile["modeljavascript"]))."',
        bodytypejavascript='".addslashes(serialize($javascript_profile["bodytypejavascript"]))."',
        transmissionjavascript='".addslashes(serialize($javascript_profile["transmissionjavascript"]))."',
        intcolorjavascript='".addslashes(serialize($javascript_profile["intcolorjavascript"]))."',
        extcolorjavascript='".addslashes(serialize($javascript_profile["extcolorjavascript"]))."'
        where id=1 limit 1";
    //print_r($javascript_profile["cityjavascript"]);
    $result = $db->db_connect_id->query( $sql );
}

?>