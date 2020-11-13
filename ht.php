<?php


$starttime123 = utime();

function utime ()
{
    $time = explode( " ", microtime() );
    $usec = ( double )$time[0];
    $sec = ( double )$time[1];
    return $sec + $usec;
}


$path = "includes/";


include $path . "db.php";

include $path . "licence.php";
include $path . "config.php";

if (_INSTALL!=1){
    header("Location: install/"); /* Redirect browser */
    exit;
}
$db = new DB;
include $path . "session.php";


session_start();

$a=date("dj");
$b=md5($a);
$b=substr($b,0,3);
$b=md5($b);
$b=substr($b,0,3);
$_SESSION['session_uid']=$b;
if ($_REQUEST['p']=="image") {
    $x=100;
    $y=20;

    $im = imagecreate($x, $y);
    $bg = imagecolorallocate($im, 200,200,200);
    imagefill($im, 0,0, $bg);

    $red=imagecolorallocate($im, 0, 0, 255);

    imagestring($im, 6, 58, 3, $b, $red);

    header("Content-type: image/png");
    imagepng ($im);
    exit(0);
}

// ini_set ('error_reporting', E_ALL);
//print_r($_REQUEST);

include $path . "tpl.php";
include $path . "function.php";
include $path . "image.class.php";
include $path . "global.class.php";
include $path . "admin.class.php";
include $path . "visit.class.php";
include $path . "email.class.php";
include $path . "banner_visit.class.php";


$config['tpl'] = $config['tplvisit'];

$tpl = new TPL;
$Image_Class = new Image;
$Global_Class = new GlobalClass;
$VisitClass = new VisitClass;
$Email_class = new EmailClass;
$banner_class = new Banner_Visit_Class;
//$dealer = new DealerClass;

$settings_profile = $Global_Class->getprofile( "1","settings","id" );
//switch($_REQUEST['p']){
//
//       case "showbanner":
//          echo $banner_class->showbanner();
//          exit(0);
//       break;
//       case "redirect":
//          $banner_class->redirect($_REQUEST['idb']);
//          exit(0);
//       break;
//
//}
//list($config['config_auto_bannerscode1'],$config['config_auto_bannerscode2'],$config['config_auto_bannerscode3'],$config['config_auto_bannerscode4'])=$banner_class->banner();
foreach ($settings_profile as $kk1=>$vv1){
    if ($vv1!=''){
        $config[$kk1]=$vv1;
    }
}
$config['config_auto_price_before']=$config['price_before'];

$_SESSION['cardatase'] = "cars";


if ($_REQUEST['reset']==2){
    $_SESSION['rent']=false;
    $_SESSION['template'] = "";
}
/*
if ($_REQUEST['p']=='rent'){
   $_SESSION['rent']=true;
   $_SESSION['cardatase'] = "rentcars";
   $_SESSION['template'] = "rent";
   unset($_SESSION['arrival_date']);
   unset($_SESSION['departure_date']);
}
if ($_SESSION['rent']) {
   $lang['tpl_auto_load_calendar_js'] = $tpl->replace( array(), "calendar1.js", "js" );
}
*/
if ($_SESSION['cardatase'] =='') {
    $_SESSION['cardatase'] ="cars";
}

$count = 0;
if ($_REQUEST['language_session'] == '' AND $_COOKIE['language_session']<>'') {
    $_SESSION['language_session'] = $_REQUEST['language_session']=$_COOKIE['language_session'];

}
if ($_REQUEST['language_session'] != '') {
    $_SESSION['language_session'] = $_REQUEST['language_session'];
    $language_set = $_SESSION['language_session'];
    if ($_SESSION['language_session']==0) {
        $language_set = '';
    }
    setcookie('language_session',$_SESSION['language_session'],time()+3600*24*365);
}
if ($_SESSION['language_session']>0) {
    $language_set = $_SESSION['language_session'];
}
$array_lang[]=0;
if ($settings_profile['language1']!=-1){
    $count++;
    if ($settings_profile['languageadmin']==$settings_profile['language1']) {
        //$language_set=1;
    }
    $multiplelanguage[$count] = ucfirst(substr($settings_profile['language1'],0,-4));
    $array_lang[]=$count;

}
if ($settings_profile['language2']!=-1){
    $count++;
    if ($settings_profile['languageadmin']==$settings_profile['language2']) {
        //$language_set=2;
    }
    $multiplelanguage[$count] = ucfirst(substr($settings_profile['language2'],0,-4));
    $array_lang[]=$count;
}
if ($settings_profile['language3']!=-1){
    $count++;
    if ($settings_profile['languageadmin']==$settings_profile['language3']) {
        //$language_set=3;
    }
    $multiplelanguage[$count] = ucfirst(substr($settings_profile['language3'],0,-4));
    $array_lang[]=$count;
}
/*
if ($_REQUEST['language_session'] != '') {
 $_SESSION['language_session'] = $_REQUEST['language_session'];
 $language_set = $_SESSION['language_session'];
 if ($_SESSION['language_session']==0) {
      //$language_set = '';
 }
 $array_lang[]=$count;
}
if (!in_array($_SESSION['language_session'],$array_lang)){
   $_SESSION['language_session']=0;
   $language_set='';
}
*/
//$results = get_browser();

if ($count>0) {
    $found=0;
    foreach ($multiplelanguage as $key=>$val){
        $class_ = ($_SESSION['language_session'] == $key ) ? " class=\"selected\"": " class=\"noselected\"";
        $found = ($_SESSION['language_session'] == $key ) ? 1: $found;
        $var_lang['key']=$key;
        $var_lang['val']=strtolower($val);
        $var_lang['class']=strtolower($class_);
        if (1) {
            $var_lang['onclicklang']="setCookie('language_session',$key,365);window.location=window.location;return false;";
        }
        $settings_profile['languagedropdown1'] .= $config["config_separator"].$tpl->replace($var_lang,"urllanguge.html");

    }
    if ($found==0) $_SESSION['language_session']='';
    $class_ = ($_SESSION['language_session'] == '' ) ? " class=\"selected\"": " class=\"noselected\"";
    $var_lang['key']=0;
    $var_lang['val']=strtolower(substr($settings_profile['language'],0,-4));
    $var_lang['class']=strtolower($class_);
    if (2) {
        $var_lang['onclicklang']="setCookie('language_session',0,365);window.location=window.location;return false;";
    }
    $settings_profile['languagedropdown'] = $tpl->replace($var_lang,"urllanguge.html");

    $settings_profile['languagedropdown'] .= $settings_profile['languagedropdown1'];

}
if (file_exists($config['path'].'language/'.$settings_profile["language{$language_set}"])) {
    @include $config['path'].'language/'.$settings_profile["language{$language_set}"];
}
include $path . "update.php";
if ($settings_profile['nrpageuser']>0) {
    $config['nrresult'] = $settings_profile['nrpageuser'];
}
if ($settings_profile['picture_width']>0) {
    $IMG_WIDTH_BIG = $settings_profile['picture_width'];
}
if ($settings_profile['picture_height']>0) {
    $IMG_HEIGHT_BIG = $settings_profile['picture_height'];
}
if ($settings_profile['thumbnail_width']>0) {
    $IMG_WIDTH = $settings_profile['thumbnail_width'];
}
if ($settings_profile['thumbnail_height']>0) {
    $IMG_HEIGHT = $settings_profile['thumbnail_height'];
}
$lang["tpl_auto_css"] = $config['tpl_path_visit'] . "style.css";
$settings_profile['time']=dateformat($config["config_date_format"],strtotime ("now"));
$lang["tpl_auto_separator_sign"]=$config["config_separator"];

$settings_profile['customlinks'] = $VisitClass->customlinks("other_menu_list.html");
$news_profile['customlinks'] = $settings_profile['customlinks'];

if ($settings_profile['logo']=="") $settings_profile['logo']="../images/spacer.gif";
if ($settings_profile['thumbnail']=="") $settings_profile['thumbnail']="../images/spacer.gif";
if ($settings_profile['picture']=="") $settings_profile['picture']="../images/spacer.gif";

$settings_profile['logo'] = $config['url_path_temp'] . $settings_profile['logo'];

$settings_profile['signup'] = "onclick=\"OpenWindow(this.href,'name', '{$config['width_popup']}', '{$config['height_popup']}','yes'); return false\"";


$p=$_REQUEST['p'];

if ( $HTTP_COOKIE_VARS['username_dealer_cookie'] == "" ) {
    $settings_profile['dealerlogin'] = $tpl->replace($settings_profile,"dealerlogin.html");
}else{
    $settings_profile['dealerlogin'] = "({$HTTP_COOKIE_VARS['username_dealer_cookie']}) <a href=\"logout.html\">{$lang['tpl_auto_Logout']}</a>";
}

if ($HTTP_COOKIE_VARS['username_dealer_cookie'] != "") {
    $tabel_cars = "carsdealer";
}else{
    $tabel_cars = "cars";
}

$arraytemp=explode(',',$_COOKIE['mycars']);
if (!is_array($arraytemp)) $arraytemp=array();
foreach ($arraytemp as $key=>$val){
    if (trim($val)==''){
        unset($arraytemp[$key]);
    }
}
$settings_profile['wishcount']=count($arraytemp);

$javascript_profile = $Global_Class->getprofile( "1","javascript","id" );
foreach ($javascript_profile as $key=>$val){
    $config["javascriptprofiles"][$key]=unserialize(stripslashes($val));
}


$sql = "SELECT {$config['table_prefix']}make.* FROM `{$config['table_prefix']}make` where 1 ";
$result = $db->db_connect_id->query( $sql );
$num_rows = mysqli_num_rows( $result );
$contor=0;
if ( $num_rows > 0 ) {
    while ( $var_features = mysqli_fetch_assoc( $result ) ) {
        $name=makeurl($var_features['name']);
        echo <<<END
\tRewriteRule ^([0-9]{1,2})-$name.html /index.php?language_session=$1&make=$var_features['id']&submit1=1&p=search
\tRewriteRule ^$name.html /index.php?language_session=0&make=$var_features['id']&submit1=1&p=search

END;


    } // while
}

$sql = "SELECT {$config['table_prefix']}country.* FROM `{$config['table_prefix']}country` where 1 ";
$result = $db->db_connect_id->query( $sql );
$num_rows = mysqli_num_rows( $result );
$contor=0;
if ( $num_rows > 0 ) {
    while ( $var_features = mysqli_fetch_assoc( $result ) ) {
        $name=makeurl($var_features['name']);
        echo <<<END
\tRewriteRule ^([0-9]{1,2})-$name.html /index.php?language_session=$1&country=$var_features['id']&submit1=1&p=search
\tRewriteRule ^$name.html /index.php?language_session=0&country=$var_features['id']&submit1=1&p=search

END;


    } // while
}




?>