<?php
$starttime123 = utime();

function utime ()
{
        $time = explode( " ", microtime() );
        $usec = ( double )$time[0];
        $sec = ( double )$time[1];
        return $sec + $usec;
} 

ob_start();

// ini_set ('error_reporting', E_ALL);

$path = "../includes/";
require $path . "licence.php";
require $path . "config.php";
if (_INSTALL!=1){
    header("Location: ../install/"); /* Redirect browser */
    exit(0);
}
require $path . "db.php";
$db = new DB;
require $path . "session.php";

$condtemplates=0;

//session_start();
/*
if ($_REQUEST['language_session']!=''){
	setcookie('LANG_COOKIE',$_REQUEST['language_session']);
	header("Location: index.php");
	exit(0);
}
*/
if ($_REQUEST['language_session']==''){
$_REQUEST['language_session']=$_COOKIE['LANG_COOKIE'];
}

require $path . "tpl.php";
require $path . "function.php";
require $path . "image.class.php";
require $path . "global.class.php";
require $path . "admin.class.php";
require $path . "visit.class.php";
require $path . "email.class.php";
require $config['wywiwyg_path']. "fckeditor.php" ;
require $path . "visitstats.class.php";
$admin = new Admin;
$p = $_REQUEST['p'];
if ( $p == "dates" )
{
        require $path . "dates.class.php";
        $Dates_Class = new Dates_Class;
}
$config['tpl'] .= "admin/";


$tpl = new TPL;
$Image_Class = new Image;
$Global_Class = new GlobalClass;
$Email_class = new EmailClass;
$VisitClass = new VisitClass;
$visit_class = new Visit_Class;

$settings_profile = $Global_Class -> getprofile( "1","settings","id" );

foreach ($settings_profile as $kk1=>$vv1){
	if ($vv1!=''){
	$config[$kk1]=$vv1;
	}
}
$config['config_auto_price_before']=$config['price_before'];

if ($config[adprofiles]){
$config['delay_How_many_days_this_object_will_be_active']=-1;
}

$count=0;

//print_r($_SESSION);
$array_lang[]=0;
if ($settings_profile[language1]!=-1){
    $count++;
    if ($_SESSION[language_session]=='' and $settings_profile[languageadmin]==$settings_profile[language1]) {
        $language_set=1;
    }
    $multiplelanguage[$count] = ucfirst(substr($settings_profile[language1],0,-4));
    $array_lang[]=$count;

}
if ($settings_profile[language2]!=-1){
    $count++;
    if ($_SESSION[language_session]=='' and $settings_profile[languageadmin]==$settings_profile[language2]) {
        $language_set=2;
    }
    $multiplelanguage[$count] = ucfirst(substr($settings_profile[language2],0,-4));
    $array_lang[]=$count;
}
if ($settings_profile[language3]!=-1){
    $count++;
    if ($_SESSION[language_session]=='' and $settings_profile[languageadmin]==$settings_profile[language3]) {
        $language_set=3;
    }
    $multiplelanguage[$count] = ucfirst(substr($settings_profile[language3],0,-4));
    $array_lang[]=$count;
}
if (!is_array($multiplelanguage)) {
   $multiplelanguage = array();
}
if ($_REQUEST[language_session] != '') {
 $_SESSION[language_session] = $_REQUEST[language_session];
 $_SESSION[language_sessionset]=1;
 $language_set = $_SESSION[language_session];
 if ($_SESSION[language_session]==0) {
      $language_set = '';
 }
}elseif ($_SESSION[language_sessionset]==''){
 $_SESSION[language_session]=$language_set;
 $_SESSION[language_sessionset]=1;
}
if ($_SESSION[language_sessionset]==1) {
  $language_set = $_SESSION[language_session];
}
if ($language_set==0) $language_set="";
if ($count>0) {
  $found=0;
  $var_lang[path]="p=".$_REQUEST['p']."&amp;";
  foreach ($multiplelanguage as $key=>$val){
            $class_ = ($_SESSION[language_session] == $key ) ? " class=\"selected\"": " class=\"noselected\"";
            $found = ($_SESSION[language_session] == $key ) ? 1: $found;
            $var_lang['key']=$key;
            $var_lang['val']=strtolower($val);
            $var_lang['class']=strtolower($class_);
            $settings_profile[languagedropdown1] .= $config["config_separator"].$tpl->replace($var_lang,"urllanguge.html");
  }
  if ($found==0) $_SESSION[language_session]='';
  $class_ = ($_SESSION[language_session] == '' ) ? " class=\"selected\"": " class=\"noselected\"";
  $var_lang['key']=0;
  $var_lang['val']=strtolower(substr($settings_profile[language],0,-4));
  $var_lang['class']=strtolower($class_);
  $settings_profile[languagedropdown] = $tpl->replace($var_lang,"urllanguge.html");

  $settings_profile[languagedropdown] .= $settings_profile[languagedropdown1];
}
/*
if (file_exists($config['path'].'language/'.$settings_profile[languageadmin]) ) {
    require $config['path'].'language/'.$settings_profile[languageadmin];
}
*/

if (file_exists($config['path'].'language/'.$settings_profile["language{$language_set}"])) {
    require $config['path'].'language/'.$settings_profile["language{$language_set}"];
}
require $path . "configsettings.php";
require $path . "update.php"; 
banned_ips();
checkbanned_ips();

if ($settings_profile[nrpageadmin]>0) {
    $config['nrresult'] = $settings_profile[nrpageadmin];
}
if ($settings_profile[picture_width]>0) {
    $IMG_WIDTH_BIG = $settings_profile[picture_width];
}
if ($settings_profile[picture_height]>0) {
    $IMG_HEIGHT_BIG = $settings_profile[picture_height];
}
if ($settings_profile[thumbnail_width]>0) {
    $IMG_WIDTH = $settings_profile[thumbnail_width];
}
if ($settings_profile[thumbnail_height]>0) {
    $IMG_HEIGHT = $settings_profile[thumbnail_height];
}

if ($settings_profile[logo]=="") $settings_profile[logo]="../images/spacer.gif";
if ($settings_profile[thumbnail]=="") $settings_profile[thumbnail]="../images/spacer.gif";
if ($settings_profile[picture]=="") $settings_profile[picture]="../images/spacer.gif";


$lang["tpl_auto_css"] = $config['tpl_path_admin'] . "style.css";

$redirect = $_REQUEST[redirect];
if ( $redirect == "" ) $redirect = $_SERVER['QUERY_STRING'];

$username = $_POST['username'];
$var = $settings_profile;
$var["url_path_tpl_admin"] = $config['url_path_tpl_admin'] ;
$var[languagedropdown] = $settings_profile[languagedropdown];
$javascript_profile = $Global_Class -> getprofile( "1","javascript","id" );
foreach ($javascript_profile as $key=>$val){
        $config["javascriptprofiles"][$key]=unserialize(stripslashes($val));
}        

$lang['tpl_auto_load_calendar_js'] = $tpl -> replace( array(), "calendar1.js", "js" );
switch($_REQUEST[p]){

        case "banner":
                $banner_settings_profile = $Global_Class -> getprofile( "1","bannersettings","id" );
                switch($_REQUEST[p1]){
                        case "banner":
                                ////$lang['tpl_auto_load_calendar_js'] = $tpl -> replace( array(), "calendar1.js", "js" );
                                require $path . "banner.class.php";
                                $banner_class = new Banner_Class;
                                break;
                        case "bannerdates":
                                require $path . "banner.class.php";
                                $banner_class = new Banner_Class;
                                echo $banner_class->bannerdates();
                                exit;
                                break;
                        case "bannerclicks":
                                require $path . "banner.class.php";
                                $banner_class = new Banner_Class;
                                echo $banner_class->bannerclicks();
                                exit;
                                break;
                        case "showbanner":
                                require $path . "banner.class.php";
                                $banner_class = new Banner_Class;
                                echo $banner_class->showbanner();
                                exit;
                                break;
                        case "bannerstats":
                                //$lang['tpl_auto_load_calendar_js'] = $tpl -> replace( array(), "calendar1.js", "js" );
                                require $path . "bannerstats.class.php";
                                $bannerstats_class = new BannerStats_Class;
                                break;
                        case "bannermonths":
                                require $path . "banner.class.php";
                                $banner_class = new Banner_Class;
                                echo $banner_class->bannermonths();
                                exit;
                                break;

                } // switch
        break;

}
switch($_REQUEST[p]){

       case "showbanner":
           require $path . "banner.class.php";
           $banner_class = new Banner_Class;
          echo $banner_class->showbanner();
          exit;
       break;
       case "redirect":
           require $path . "banner.class.php";
           $banner_class = new Banner_Class;
          $banner_class->redirect($_REQUEST[idb]);
          exit;
       break;


}
switch($_REQUEST[p]){
        case "renew":
                switch($_REQUEST[p1]){
                        default:
                                require $path . "renew.class.php";
                                $renewclass = new renewclass_Class;
                                break;
                } // switch
        break;
}        
$outputtoscreen .= $tpl -> replace( $var, "admin_header.html" ); //read header

switch ( $p )
{
case "image":
	$x=50;
	$y=17;
	
	//$a=$_REQUEST['uid'];//rand(0,9999999);
	srand((double)microtime() * 1000000);
	
	$a =rand(0, 999999);
	$b=md5($a);
	$b=substr($b,0,3);
	$b=md5($b);
	$b=substr($b,0,3);
	$_SESSION['session_uid']=$b;
	
	$im = imagecreate($x, $y);
	$bg = imagecolorallocate($im, 200,200,200);
	imagefill($im, 0,0, $bg);
	
	$red=imagecolorallocate($im, 1, 1, 1);
	
	imagestring($im, 6, 15, 0, $b, $red);
	
	header("Content-type: image/png");
	imagepng ($im); 
	exit(0);
break;
}

if ( $_COOKIE['username_cookie'] == "" )
{
        switch ( $p )
        {
                case "login":
                        $outputtoscreen .= $admin -> login( $username, "login1", $redirect, "" );
                        break;
                case "forgot":
                        $outputtoscreen .= $admin -> forgot( "forgot1", $redirect, "" );
                        break;
                case "forgot1":
                        $email = $_POST['email'];
						if (!eregi( "^[a-z0-9]+([_.-][a-z0-9]+)*([_])*@([a-z0-9]+([.-][a-z0-9]+)*)+\\.[a-z]{2,4}$", $email ) )
                        {
                                $outputdefault .= $admin -> forgot( "forgot1", $redirect, $lang["erroremail"] );
                        }else{                        
                        $email_var = $Global_Class -> getprofile( $_POST['email'], "admin", "email" );
                        if ( !$email_var or $email=='')
                        {
                                $outputtoscreen .= $admin -> forgot( "forgot1", $redirect, $lang["erroremail"] );
                        }else{
                              srand((double)microtime() * 1000000);
                              $unic_id=@md5(rand(0, 999999));
                              $password = substr( @md5(rand(0, 999999)) ,0, 8) ;
                              $email_var[password] = $password;
                              $settings_template = $Global_Class -> getprofile( "1","template","id" );
                              if ($email_var[active]==2) {
                                            if ($email_var[unic_id]=="") {
                                             $email_var[unic_id] = $unic_id;
                                             $sql_unic_id = ", `unic_id` = '$unic_id'";
                                            }
                                            $email_var['link'] = $config['url_path']."index.php?p=confirm&amp;id=".$email_var[unic_id];
                                            $settings_template[signup_subject] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $settings_template[signup_subject] );
                                            $settings_template[signup_body] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $settings_template[signup_body] );
                                            $sendresult = $Email_class -> emailsend(  $email_var[email], $email_var[username],$settings_template[email], $settings_template[from], $settings_template[signup_subject], $settings_template[signup_body] );
                                            $sendresult = $Email_class -> emailsend(  $settings_template[email], $settings_template[from] , $settings_template[email], $settings_template[from], $settings_template[signup_subject], $settings_template[signup_body] );
                              }
                              $settings_template[signup_subject] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $lang['newpassword_subject'] );
                              $settings_template[signup_body] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $lang['newpassword_body'] );

                              $sql="UPDATE `{$config[table_prefix]}admin` SET password='".md5($password)."'{$sql_unic_id} where `email` = '$email' limit 1";
                              $result = $db -> query($sql,__FILE__,__LINE__);

                              $sendresult = $Email_class -> emailsend(  $email_var[email], $email_var[username],$settings_template[email], $settings_template[from], $settings_template[signup_subject], $settings_template[signup_body] );
                              $sendresult = $Email_class -> emailsend(  $settings_template[email], $settings_template[from] , $settings_template[email], $settings_template[from], $settings_template[signup_subject], $settings_template[signup_body] );

                              $outputtoscreen .= $admin -> login( $username, "login1", $redirect, $lang['tpl_auto_You_will_receive'] );
                        }
                        }
                        break;
                case "login1":
                        $password = $_POST['password'];
                        $username = $_POST['username'];
                        $code = $_POST['code'];
                        
                        if ( $username == "" )
                        {
                                $outputtoscreen .= $admin -> login( $username, "login1", $redirect, $lang["error1"] );
                        } elseif ( $password == "" )
                        {
                                $outputtoscreen .= $admin -> login( $username, "login1", $redirect, $lang["error2"] );
                        }
                        elseif ( $_SESSION['session_uid']!='' and $code == "" and (extension_loaded ("gd"))  and $config[useimagesecurity] )
                        {
                                $outputtoscreen .= $admin -> login( $username, "login1", $redirect, $var["error"]=$lang["errorcode"] );
                                //unset($_SESSION[number_unic]);
                                $_SESSION['session_uid']="";
                        }elseif ( $_SESSION['session_uid']!='' and  $code != $_SESSION['session_uid'] and (extension_loaded ("gd"))  and $config[useimagesecurity] )
                        {
                                $outputtoscreen .= $admin -> login( $username, "login1", $redirect, $var["error"]=$lang["errorcode1"] );
                                //unset($_SESSION[number_unic]);
                                $_SESSION['session_uid']="";
                        }
                        elseif ( $userprofile = $admin -> verifyadmin( $username, $password  ) )
                        {
                                $userprofile = $admin -> getadminprofile( $username );
                                if (0){
                                   $userprofile['right'] = 1;
                                }else{
                                	$row = array(
									"admin"=>$userprofile['id'],
									"action"=>$lang['logging']['user_login']." ".$username,
									);
					
									addlogging( $row );                                   	
                                }
                                $admin -> loginadmin( $username, $userprofile['right'], $userprofile['id'] );
                                //unset($_SESSION[number_unic]);
                                if ($redirect=="p=forgot") $redirect="";
                                $redirect='';
                                header( "Location: index.php?$redirect" );
                                exit(0);
                        }
                        else
                        {
                                if ( $admin -> existadmin( $username ) )
                                {
                                        $profile_ = $admin -> getadminprofile( $username );

                                        if ($profile_[active]==0){
                                        $var = array ( "error" => $lang["error10"]
                                                );
                                        }
                                        elseif ($profile_[active]==2){
                                        $var = array ( "error" => $lang["error11"]
                                                );
                                        }elseif ($profile_[active]==1)
                                        $var = array ( "error" => $lang["error4"]
                                                );
                                        elseif ($profile_[active]==3)
                                        $var = array ( "error" => $lang["error12"]
                                                );
                                }
                                else
                                {
                                        $var = array ( "error" => $lang["error3"]
                                                );
                                }
                                $outputtoscreen .= $admin -> login( $username, "login1", $redirect, $var["error"] );
                        }
						$row = array(
							"admin"=>get_user_ip(),
							"action"=>$lang['logging']['user_try_login'].": ".$username." (".$var["error"].") ",
						);
		
						addlogging( $row );                        
                        break;
                default:
                        if ($redirect=='') $redirect="p=summary&page=0";
                        $outputtoscreen .= $admin -> login( $username, "login1", $redirect, "" );
        }
}
else
{
        $var['username'] = $_COOKIE['username_cookie'];
        $right_cookie = $admin -> getadminright( $_COOKIE['right_cookie'] );
        if ( !is_array( $right_cookie ) ) $right_cookie = array();	    
        $nr = 0;
        //$var['menu']=" :: ";



        foreach( $right_cookie as $key => $val )
        {
                if ( !in_array( $key, array( "id", "name","view_all_cars","view_all_booking" ) ) )
                {
                        if ( $val == 1 )
                        {
                                $key_ = preg_replace( "/_(\w+)/e", "", $key );
                                if ( $menu_added[$key_] != 1 and $key_!="" and !in_array( $key_, array( "discounts","prices","notavailable","bookings" ) ) )
                                {
                                        $menu_added[$key_] = 1;
                                        $pp = $p;

                                        if ( $pp == $key_ )
                                        {
                                                $out_start = "[ <b>";
                                                $out_end = "</b> ]";
                                        }
                                        else
                                        {
                                                $out_start = "";
                                                $out_end = "";
                                        }

                                        $array_menu[$nr] = "
                                        <tr><td><img src=\"../images/sign.gif\" border=0><a href=\"index.php?p=$key_&amp;page=0\" onmouseover=\"show_('$key_')\" onmouseout=\"hide_('$key_')\">$out_start{{tpl_auto_$key_}}$out_end </a></td></tr>";
                                        /*
                                        if (!in_array($key_,array("template","settings","sendemail") ) ) {
                                        $array_menu[$nr] .= "<li><a href=index.php?p=$key_&amp;o=add>{{add_start}}{{tpl_auto_Add}}{{add_finish}}</a></li>
                                                 <li><a href=index.php?p=$key_&amp;o=view>{{view_start}}{{tpl_auto_View_Edit_Delete}}{{view_finish}}</a></li>
                                                 <li><a href=index.php?p=$key_&amp;o=search>{{search_start}}{{tpl_auto_Search}}{{search_finish}}</a></li>";
                                        }
                                        */
                                        $array_menu[$nr] .= "\n\r
                                        ";
                                        $array_menu123[$nr] = "<tr><td>
                                        <a href=\"index.php?p=$key_\" onmouseover=\"show_('$key_')\" onmouseout=\"hide_('$key_')\"><img src=\"../images/sign.gif\" border=0>".""."$out_start {{tpl_auto_$key_}}$out_end</a> </td></tr> \r\n";
                                        $array_menu1[$nr] = "<TD valign=top width=\"25%\"><table><tr><TD valign=top> <img src=\"../images/sign.gif\" border=0><a href=\"index.php?p=$key_&amp;page=0\">".""."<b>$out_start {{tpl_auto_$key_}}$out_end</b></a> </td> </tr>\r\n";
                                        $array_menu_order[$nr] = $lang ["tpl_auto_$key_"];
                                        $array_menu_expl[$nr] = "<div id=$key_ class=styleadmin style=\"visibility: hidden;\"> {$lang['auto_meniu_expl'][$key_]}</DIV>\n";
                                        $array_menu_expl1[$nr] = "<tr><Td valign=top> {$lang['auto_meniu_expl'][$key_]}\n </td></tr></table></TD>";
                                        $array_order_menu[$key_]=$nr;

                                        $nr++;
                                        $aaatemp.=' "'.$key_.'",';
                                }
                        }
                }
        }
        if (!is_array($array_menu_order)) $array_menu_order = array();
        asort($array_menu_order);
        $nr=1;
        $adminprofile = $Global_Class -> getprofile( $_COOKIE['id_cookie'],"admin","id" );
         if ($p == ''  or $p == 'summary') {
             if (1){
					 if (!$right_cookie['view_all_cars']) {
                           $sql_default_global = " and {$config[table_prefix]}cars.admin = '".$_COOKIE['id_cookie']."' ";
                     }
                     $userstat['val']=$val."&f=inactivestock";
                     $var[inactivecars]=$Global_Class->getnumber("cars"," and active=0 $sql_default_global");;                         							 

                     $userstat['val']=$val."&f=activestock";
                     $var[activecars]=$Global_Class->getnumber("cars"," and active>=1 $sql_default_global");;                         
                     
                     $userstat['val']=$val."&f=picturesstock";
                     
                     $listin_array_id_gallery = $Global_Class -> getarrayid('gallery','carsid',$sqlini=' group by carsid');
          			 if (!is_array($listin_array_id_gallery)) $listin_array_id_gallery=array();

                     $var[picturesstock]=$Global_Class->getnumber("cars"," AND ( FIND_IN_SET( id, '".implode(",",$listin_array_id_gallery)."' ) > 0 ) $sql_default_global");  
                     
                     $var[inpicturesstock]=$Global_Class->getnumber("cars"," AND ( FIND_IN_SET( id, '".implode(",",$listin_array_id_gallery)."' ) = 0 ) $sql_default_global");                                   
                     
             }
         }
                 
         
         if ($settings_profile['adprofiles']==1 and $adminprofile[daystoexpire]>=0 and $adminprofile[active]!=3 and !$right_cookie['view_all_cars']){
         $var['home1'] =$tpl -> replace( $adminprofile, "home1.html" );
         }


	                                  
         if ($settings_profile['adprofiles']==1 and $adminprofile[active]==3){
         $var['deactive'] =$tpl -> replace( $adminprofile, "home2.html" );
         $config[autoactivatedisabled]=true;
         }
         if (!$right_cookie['view_all_cars']){
         $var['home_adprofiles'] =$tpl -> replace( $adminprofile, "home_adprofiles.html" );
         }         
         $var['home_page'] =$tpl -> replace( $var, "home.html" );
         
         $var['home_page'] .= '<TABLE class=menu>';

         $var['menu'] .= "<tr><td>----------</td></tr>";
         $condmenu=false;

         foreach ($config["config_order_menuadmin"] as $val) {
             if ($val=="|" and $condmenu==true){
              $var['menu'] .= "<tr><td>----------</td></tr>";
              $condmenu=false;
             }else{
               if ($array_order_menu[$val]!='' or $array_order_menu[$val]===0)
               {
                      $condmenu=true;
                      $key = $array_order_menu[$val];
                      if ($nr == 1 OR $nr % 4 == 1) $var['home_page'] .= "<tr class=menu2>\r\n";
                      $var['menu'] .= $array_menu[$key];
                      $var['menuexpl'] .= $array_menu_expl[$key];
                      $var['home_page'] .= $array_menu1[$key];
                      $var['home_page'] .= $array_menu_expl1[$key];
                      if ($nr != 0 AND $nr % 4 == 0) $var['home_page'] .= "</tr>\r\n";
                      $nr++;
                      if ($p=='summary'){
                      if (in_array($val,$config["config_statsinadmin"] )){
                         $contor1++;
                         if ($contor1%2) $userstat['class_temp']="class_temp1";
                         else $userstat['class_temp']="class_temp2";
                         $userstat[name]=$lang['tpl_auto_'.$val];
                         $userstat['val']=$val;
                         $userstat[number]=$Global_Class->getnumber($val);;
                         $statsoutput .= $tpl -> replace( $userstat, "logs2.html" );
                      }
                      }
               }
             }
         }
         if ($condmenu)
         $var['menu'] .= "<tr><td>----------</td></tr>";
         if ($nr != 0 AND $nr % 4 != 1) $var['home_page'] .= "</tr>\r\n";
         $var['home_page'] .= '</TABLE>';

        foreach( $config['admin_menu_top1'] as $key => $val )
        {
           $menu_show=false;
           foreach ($config["admin_menu_top2"][$val] as $key1=>$val1) {
                 $val1_ = preg_replace( "/_(\w+)/e", "", $val1 );

                 if ($right_cookie[$val1_] or $right_cookie[$val1_.'_view']){
                   $menu_show=true;
                 }
           }

           $vartemp['val'] = $lang['admin_menu_top1_'.$val];
           if (!is_array($config["admin_menu_top2"][$val])){
                 $config["admin_menu_top2"][$val]=array();
           }
           $vartemp['explain'] = strip_tags($lang['auto_meniu_expl'][$val]);
           if ($_REQUEST['p']==$val or @in_array($_REQUEST['p'],$config["admin_menu_top2"][$val])){
            $vartemp['class'] = "ClassBold";
            $defaultoption=$val;
           }else{
            $vartemp['class'] = "ClassNormal";
            $var['Class'.$_REQUEST['p']] = "ClassNormal";
           }
           if (in_array($_REQUEST['p'],$config["admin_menu_top3"])){
            $defaultoption=$config["admin_menu_top3_default"];
            if ($val==$defaultoption) {
             $vartemp['class'] = "ClassBold";
            }
           }
           //echo "\$lang['admin_menu_top1_{$val}']=\"{$val}\";\n";
           $vartemp['key'] = $val;
           if ($menu_show){
           $var['repeat'] .= $tpl -> replace( $vartemp, "admin_menu_repeat.html" );
           }
        }
        if (!is_array($config["admin_menu_top2"][$defaultoption])) $config["admin_menu_top2"][$defaultoption]=array();
		
        foreach( $config["admin_menu_top2"][$defaultoption] as $key => $val )
        {
          $val1_ = preg_replace( "/_(\w+)/e", "", $val );
          if ($right_cookie[$val1_] or $right_cookie[$val1_.'_view'] or ( in_array($_REQUEST['p'],$config["admin_menu_top3"]) and $right_cookie[$val1_.'view'])){

           $vartemp['val'] = $lang['tpl_auto_'.$val];
           $vartemp['explain'] = strip_tags($lang['auto_meniu_expl'][$val]);
           if ($_REQUEST['p']==$val){
            $vartemp['class'] = "ClassBold";
           }else{
            $vartemp['class'] = "ClassNormal";
           }
           if ($_REQUEST['p']=='banner'){
	           if ($_REQUEST['p1']==$val){
	            $vartemp['class'] = "ClassBold";
	           }else{
	            $vartemp['class'] = "ClassNormal";
	           }           
           }
            if ($val==$defaultoption and in_array($_REQUEST['p'],$config["admin_menu_top3"])) {
             $vartemp['class'] = "ClassBold";
            }
           //echo "\$lang['admin_menu_top1_{$val}']=\"{$val}\";\n";
           $vartemp['key'] = $val;
           if ($val=='banner'){
           	$vartemp['key'] = "banner&p1=banner";
           	$right_cookie[bannerstats]=1;
           }elseif ($val=='bannerstats'){
           	$vartemp['key'] = "banner&p1=bannerstats";
           	$right_cookie[bannerstats]=1;
           }
           
           $var['repeat2'] .= $tpl -> replace( $vartemp, "admin_menu_repeat.html" );

          }
        }
        $var['Class'.$_REQUEST['p']] = "ClassBold";
         //if ($nr != 0 AND $nr % 4 != 1)
         //$var['home_page'] .= "</tr>\r\n";

         //$var['home_page'] .= '</TABLE>';
         if ($p != '' ) {
             if ($right_cookie['summary'] == 0 and $p=='summary'){
              $p="";
              $_REQUEST['p']='';              
             }else{
              $var['home_page'] = '';
             }
         }
        /*
                                $var['home_page'] = '<TABLE class=menu>';
                                foreach ($array_menu_order as $key=>$val){
                                        if ( $nr==1  OR $nr % 4 == 1 ) $var['home_page'] .= "<tr class=menu2>\r\n";
                                        $var['menu'] .=        $array_menu[$key];
                                        $var['menuexpl'] .= $array_menu_expl[$key];
                                        $var['home_page'] .=        $array_menu1[$key];
                                        $var['home_page'] .= $array_menu_expl1[$key];
                                        if ( $nr!=0  AND $nr % 4 == 0 ) $var['home_page'] .= "</tr>\r\n";
                                        $nr++;
                                }
                                if ( $nr!=0  AND $nr % 4 != 1 ) $var['home_page'] .= "</tr>\r\n";
                                $var['home_page'] .= '</TABLE>';
                                if ($p!='') {
                                        $var['home_page'] = '';
                                }
        */
        $var['titlesite']=$settings_profile['titlesite'];
        $outputtoscreen .= $tpl -> replace( $var, "admin_menu.html" ); //read header
        switch ( $p )
        {
                case "change":
                        if ( $adminprofile = $Global_Class -> getprofile( $_COOKIE['id_cookie'],"admin","id" ) )
                        {

                                $varchar_fields=$config['admin_section']['changeprofile']['varchar_fields'];
                                $text_fields=$config['admin_section']['changeprofile']['text_fields'];
                                $file_fields=$config['admin_section']['changeprofile']['file_fields'];
                                $dropdown_fields=$config['admin_section']['changeprofile']['dropdown_fields'];
                                $dropdownval=$config['admin_section']['changeprofile']['dropdownval'];
                                $radio_fields=$config['admin_section']['changeprofile']['radio_fields'];
                                $radioval=$config['admin_section']['changeprofile']['radioval'];
                                $checkbox_fields=$config['admin_section']['changeprofile']['checkbox_fields'];
                                $password_fields=$config['admin_section']['changeprofile']['password'];

                                $outputtoscreen .= $Global_Class -> edit( $_COOKIE['id_cookie'], "admin", "change1", "change1", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, "id", "", $adminprofile );
                        }
                        else
                        {
                                $outputtoscreen .= $admin -> login( $_COOKIE['username'], "login1", $lang["error2"] );
                        }
                        break;
                case "change1":
                        $file = $config['admin_section']['changeprofile']['file']; // for pictures
                        $file_size = $config['admin_section']['changeprofile']['file_size'] ;


                        $copy_from = $config['admin_section']['changeprofile']['copy_from'];
                        $copy_from_val = $config['admin_section']['changeprofile']['copy_from_val'];
                        $require_array = $config['admin_section']['changeprofile']['require_array'];

                        $user_profile = $Global_Class -> getprofile( $_COOKIE['id_cookie'], "admin", "id" );

                        $name_profile = $Global_Class -> getprofile( $_POST['input_username'], "admin", "username" );

                        if ( $name_profile && ( $name_profile['id'] != $_COOKIE['id_cookie'] ) )
                        {
                                $outputtoscreen_add[1] .= $lang['error_change1']['username_exist'];
                        }
                        $email_profile = $Global_Class -> getprofile( $_POST['input_email'], "admin", "email" );

                        if ( $email_profile && ( $email_profile['id'] != $_COOKIE['id_cookie'] ) )
                        {
                                $outputtoscreen_add[1] .= $lang['error_change1']['email_exist'];
                        }
                        if ( ( $_POST['input_password'] != $_POST['input_password1'] ) )
                        {
                                $outputtoscreen_add[1] .= $lang['error_change1']['password_not_equal'];
                        }
                        if ( ( strlen( $_POST['input_username'] ) < 4 ) || ( strlen( $_POST['input_username'] ) > 20 ) )
                        {
                                $outputtoscreen_add[1] .= $lang['error_change1']['username_short'];
                        } elseif ( ( strlen( $_POST['input_password'] ) < 4 ) || ( strlen( $_POST['input_password1'] ) > 20 ) )
                        {
                                $outputtoscreen_add[1] .= $lang['error_change1']['password_short'];
                        }
                        if ( $outputtoscreen_add[1] == "" )
                        {
                                $copy_from_id = $config['admin_section']['changeprofile']['copy_from_id'];
                                        foreach ($config['admin_section']['changeprofile']['copy_from_id'] as $key1=>$val1){
                                         $copy_from_id_value[$val1] = $user_profile[$val1];
                                        }
                                                                $default_id=array("id");
                                                                $file=$config['admin_section']['changeprofile']['file'];
                                                                $file_size=$config['admin_section']['changeprofile']['file_size'];
                                                                $relation=$config['admin_section']['changeprofile']['relation'];
                                                                $relation_table=$config['admin_section']['changeprofile']['relation_table'];
                                                                $password=$config['admin_section']['changeprofile']['password'];
                                                                $email_fields=$config['admin_section']['changeprofile']['email_fields'];
                                                                $id_="id";

                                $outputtoscreen_add = $Global_Class -> edit1( $_COOKIE['id_cookie'], "admin", "change1", "", $default_id, $file,$file_size,$relation,$relation_table,$copy_from,$copy_from_val,$require_array,$password,$copy_from_id,$copy_from_id_value, $email_fields, $id_ ,"","");


                        }
                        if ( $outputtoscreen_add[0] == false )
                        {

                                $varchar_fields=$config['admin_section']['changeprofile']['varchar_fields'];
                                $text_fields=$config['admin_section']['changeprofile']['text_fields'];
                                $file_fields=$config['admin_section']['changeprofile']['file_fields'];
                                $dropdown_fields=$config['admin_section']['changeprofile']['dropdown_fields'];
                                $dropdownval=$config['admin_section']['changeprofile']['dropdownval'];
                                $radio_fields=$config['admin_section']['changeprofile']['radio_fields'];
                                $radioval=$config['admin_section']['changeprofile']['radioval'];
                                $checkbox_fields=$config['admin_section']['changeprofile']['checkbox_fields'];
                                $password_fields=$config['admin_section']['changeprofile']['password'];

                                $outputtoscreen .= $Global_Class -> edit( $_COOKIE['id_cookie'], "admin", "change1", "change1", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, "id", "", $user_profile,  $outputtoscreen_add[1] );


                        }
                        else
                        {
                                $outputtoscreen .= $outputtoscreen_add;
                                $admin -> loginadmin( $_POST['input_username'], $_COOKIE['right_cookie'], $_COOKIE['id_cookie'] );
                                header( "Location: index.php?p=change2" );
                                exit(0);
                        }
                        break;
                case "change2":
                        $var = array ( "tpl_msg" => $lang["tpl_Profile_updated"]
                                );
                        $outputtoscreen .= $tpl -> replace( $var, "admin_profileupdated.html" );
                        break;
//finish cars
                case "cars":

                        $default_tabel = "cars";
                        $default_id = array("id");
                        $o = $_REQUEST['o'];
						
                        $id_ = "id";
                        if (intval($_REQUEST['id'])>0){
                        $user_profile = $Global_Class -> getprofile(  $_REQUEST['id'], $default_tabel, $id_ );
                        //print_R($user_profile);
                        }

                        $varchar_fields = $config['admin_section'][$default_tabel]['varchar_fields'];
                        $multiplefields = $config['admin_section'][$default_tabel]['multiplefields'];

                        $text_fields = $config['admin_section'][$default_tabel]['text_fields'];
                        $multiplefields_text = $config['admin_section'][$default_tabel]['multiplefields_text'];
                        $text_fields_wysiwyg = $config['admin_section'][$default_tabel]['text_fields_wysiwyg'];
                        $file_fields = array();
                        $dropdown_fields = $config['admin_section'][$default_tabel]['dropdown_fields'];
                        foreach ($varchar_fields as $key1=>$val1){
                               if ($config['varchar_special_maxlength'][$default_tabel][$val1]!=''){
                                  $config['varchar_special_maxlength'][$val1]=$config['varchar_special_maxlength'][$default_tabel][$val1];
                               }
                               if ($config['varchar_special_maxlength_goodchars'][$default_tabel][$val1]!=''){
                                  $config['varchar_special_maxlength_goodchars'][$val1]=$config['varchar_special_maxlength_goodchars'][$default_tabel][$val1];
                               }
                        }
                        $count1=8;
                        foreach ($config['admin_section'][$default_tabel]['dropdown_fields_fromlanguage'] as $key1=>$val1){
                          $dropdown_fields[]=$val1;
                          $temp="valoare_".$count1;
                          $$temp=-1;
                          if ($o=="add") {
                                  $$temp = -1;
                          }elseif ($o=="edit" or $o=='see') {

                                  $$temp = $user_profile[$val1];
                          }else{

                                  $$temp = $_POST['input_'.$val1];
                          }
                          $dropdownval[$val1] = $Global_Class -> getdropdown_array( $$temp, $lang[$val1] );
                          $count1++;
                        }
                        if (!$right_cookie['view_all_cars']) {
                           $sql_default_global = " and {$config[table_prefix]}cars.admin = '".$_COOKIE['id_cookie']."' ";
                        }else{
                           $dropdown_fields[]="admin";
                        }
                        if ($adminprofile[active]!=3){
							
                        $dropdown_fields[]="active";
                        
						}
                        if ($o=="add" or $o=="add1" ) {
                                $admin_profile = $Global_Class -> getprofile(  $_COOKIE['id_cookie'], "admin", "id" );
                                $nocars = $Global_Class -> getnumrows($_COOKIE['id_cookie'], "cars", "admin");
                                if ($admin_profile[nocars]>0 AND $nocars>=$admin_profile[nocars]) {
                                    $_REQUEST['o']="view";
                                    $outputtoscreen .= $lang['tpl_auto_You_don_t_have_the_right_to_add_more_Cars'];
                                    //$output_add[0]=false;
                                }
                        }
                        if ($o=="add") {

                                $valoare_1 = -1;
                                $valoare_6 = -1;
                                $valoare_7 = -1;
                                $valoare_8 = -1;
                                if ($config['delay_How_many_days_this_object_will_be_active']>0){
                                  $dropdown_fields[]="delay";
                                }
                        }elseif ($o=="edit" or $o=='see') {
                                $valoare_1 = $user_profile['admin'];
                                $valoare_6 = $user_profile['active'];
                                $valoare_7 = $user_profile['model'];
                                $valoare_8 = $user_profile['delay'];
                        }else{
                                $valoare_1 = $_POST['input_admin'];
                                $valoare_6 = $_POST['input_active'];
                                $valoare_7 = $_POST['input_model'];
                                $valoare_8 = $_POST['input_delay'];
                        }
                        
                        $copy_from_id = array("date_add", "date_modify", "noview");

						if ($adminprofile[active]==3){
							$copy_from_id[active]=$user_profile['active'];
						}
                        $copy_from_id_value["date_modify"] = date("Y-m-d");
                                                
                        if ($o=="edit" or $o=="edit1"){
                         //exit;
                         $copy_from_id[]="date_delay";
						 //print_r($config);
                         if ( ($user_profile['daystoexpire']<=$config['how_many_days_before_expire_canbe_reactivated'] or $user_profile['date_delay']=='0000-00-00') and ($adminprofile[active]!=3) ){
                           if ($config['delay_How_many_days_this_object_will_be_active']>0)	{
                             $dropdown_fields[]="delay";
                           }
                           $copy_from_id_value["date_delay"] = date("Y-m-d");
                         }else{
                           if ($o=="edit" and $config['delay_How_many_days_this_object_will_be_active']>0) {
                           $lang['tabel_cars']['active'].=eregi_replace("{{days}}",$user_profile['daystoexpire'],$lang['tpl_auto_expire_in_days']);
                           }
                           $copy_from_id[]="delay";
                           $copy_from_id_value["date_delay"] = $user_profile["date_delay"];
                           $copy_from_id_value["delay"] = $user_profile["delay"];
                         }
                        }
                                                
                        if ($o=="add1") {
                            $copy_from_id_value["date_add"] = date("Y-m-d");
                        }elseif ($o=="edit1") {
                            $copy_from_id_value["date_add"] = $user_profile["date_add"];
                            $copy_from_id_value['noview']=$user_profile["noview"];
                        }
                        
                        if ($o=="add1") {
                            $copy_from_id[]="date_delay";
                            if ($config['delay_How_many_days_this_object_will_be_active']>0){
                            $dropdown_fields[]="delay";
                        	}
                            $copy_from_id_value["date_add"] = date("Y-m-d");
                            $copy_from_id_value["date_delay"] = date("Y-m-d");

                            srand((double)microtime() * 1000000);
                            $unic_id = @md5(rand(0, 999999));
                            $copy_from_id_value["unicid"] = $unic_id;
                        }elseif ($o=="edit1") {
                            $copy_from_id_value["date_add"] = $user_profile["date_add"];
                            $copy_from_id_value['noview']=$user_profile["noview"];
                            $copy_from_id[]="daysactive";
                            $copy_from_id_value['daysactive']=$user_profile["daysactive"];
                            if ($user_profile["unicid"]=='') $user_profile["unicid"]=$unicid;
                            $copy_from_id_value['unicid']=$user_profile["unicid"];

                        }      
                        if ($adminprofile[active]!=3){             
                        $field_activate = "active"; //for activat
                        }
                        
                        $field_activate = "active";
                        
                        $config[config_second_multiple_show]=1;
                        $config[config_sold_multiple_show]=1;


                        $dropdownval["admin"] = $Global_Class -> getdropdown( $valoare_1, "admin", "username", "id", "username" );


                        $relation = array( "admin" );//,"city", "category", "make", "model"
                        $relation_table["admin"][0] = "admin";
                        $relation_table["admin"][1] = "id";
                        $relation_table["admin"][2] = "username";

                        $count1=20;
                        foreach ($config['admin_section'][$default_tabel]['dropdown_fields'] as $key1=>$val1){
                          $temp="valoare_".$count1;
                          $$temp=-1;
                          if ($o=="add") {
                                  $$temp = -1;
                          }elseif ($o=="edit" or $o=='see') {

                                  $$temp = $user_profile[$val1];
                          }else{

                                  $$temp = $HTTP_POST_VARS['input_'.$val1];
                          }
                          if (in_array($val1,$config['admin_section'][$default_tabel]['onchange'])){
                                        $temp1="val".$val1;
                                  $$temp1=$$temp;
                                if ($$temp1==-1){
                                     $user_profile = $Global_Class -> getprofilefirst( $val1, " order by name limit 1" );
                                     $$temp1=$user_profile[id];
                                }
                          }
						  if ($o=="edit" or $o=="add" or $o=="edit1" or $o=="add1" or $o=="see") {
	                          if (in_array($val1,$config['admin_section'][$default_tabel]['onchange_sub'])){
	                                $temp1="val".$val1;
	                                $temp2="val".$config['admin_section'][$default_tabel]['onchange_sub_rel'][$val1];
	                                $$temp1=$$temp;
	                                $dropdownval[$val1] = $Global_Class -> getdropdown( $$temp1, $val1, "name", "id", "name" ,0," and ".$config['admin_section'][$default_tabel]['onchange_sub_id'][$val1]."='{$$temp2}' " );
	                                $dropdownval_onchange[$config['admin_section'][$default_tabel]['onchange_sub_rel'][$val1]]=" onChange=\"changeinput_".$val1."(document.formarticle.input_".$val1.".selectedIndex,document.formarticle.input_".$config['admin_section'][$default_tabel]['onchange_sub_rel'][$val1].");\" ";
	                                
	                                //$dropdownval_onchange['state']=" onChange=\"changeinput_city(document.formarticle.input_city.selectedIndex,document.formarticle.input_state);\" ";
									/*
							        if (file_exists($config['path'].'temp/makemodel'.$language_set.'.txt') and filesize($config['path'].'temp/makemodel'.$language_set.'.txt')>0){
							         	 $language_set1=($language_set=='')?0:$language_set;
							         	 $javascript_special[$val1] = @implode('',@file($config['path'].'temp/makemodel'.$language_set1.'.txt'));
							         	//$config["javascriptprofiles"]['makemodeljavascript'][$language_set1] ;
							         }else{
							         	 
							         }
							         */
							         
							         $javascript_special[$val1] = $Global_Class -> getjavascriptarray($config['admin_section'][$default_tabel]['onchange_sub_rel'][$val1],"name{$language_set}","id","name{$language_set}",$val1,"name{$language_set}","id","name{$language_set}",$config['admin_section'][$default_tabel]['onchange_sub_id'][$val1]);
							                 
							                              
	                          } else{
	
	                                $dropdownval[$val1] = $Global_Class -> getdropdown( $$temp, $val1, "name", "id", "name" );
	
	                          }
						  }

						   $dropdownval_onchange['state']=" onChange=\"changeinput_city(document.formarticle.input_city.selectedIndex,document.formarticle.input_state);\" ";
                          $count1++;
                          $relation[]=$val1;
                          $relation_table["$val1"][0] = "$val1";
                          $relation_table["$val1"][1] = "id";
                          $relation_table["$val1"][2] = "name{$language_set}";

                        }

                        $dropdownval["admin"] = $Global_Class -> getdropdown( $valoare_1, "admin", "username", "id", "username" );

                        $dropdownval["active"] = yes_or_no($valoare_6);
                        $dropdownval["delay"] = $Global_Class -> getdropdown_array( $valoare_8, array($config['delay_How_many_days_this_object_will_be_active']) );



                        
                        if (!is_array($config['admin_section']['cars']['radio_fields'])) {
                        	$config['admin_section']['cars']['radio_fields']=array();
                        	$radio_fields = array("displaymodel");
                        }else{
                        	$radio_fields=$config['admin_section']['cars']['radio_fields'];
                        }
                        if (!is_array($config['admin_section']['cars']['radioval'])) {
                        	$config['admin_section']['cars']['radioval']=array();
                        	$radioval["displaymodel"] = "0|<img src=\"../images/displaymodel0.gif\" border=0>|#1|<img src=\"../images/displaymodel1.gif\" border=0>";
                        }else{
                        	$radioval=$config['admin_section']['cars']['radioval'];
                        }

                        $checkbox_fields = array();

                        $password_fields = array();

                        $file = array(); // for pictures
                        $file_size = array(); // for pictures size


                        $copy_from = array(); // for big pictures
                        $copy_from_val = array(); // for big picutres size
                        $require_array = $config['admin_section']['cars']['require_array']; //require array
                        $password = array(); // for md5 fields


                        if (!$right_cookie['view_all_cars']) {
                             $copy_from_id[]="admin";
                             $copy_from_id_value["admin"]=$_COOKIE['id_cookie'];
                        }

                        $email_fields = array();
                        $date_fields = array();

                        $fields_not_show = $config['admin_section']['cars']['fields_search_cars'];
                        $tablefield_array_options = $config['admin_section']['cars']['tablefield_array_options'];
                        $tablefield_array_options_val=$config['admin_section']['cars']['tablefield_array_options_val'];
                        $field_name ="model"; //for delete
                        $search_fields = $config['admin_section']['cars']['fields_view_cars'];
                        if ($o=="add1" or $o=="edit1") {
	                        if ($_REQUEST['input_specialprice']=='' and $_REQUEST['input_price']==''){
	                        	$output_add[1].=$lang['error_cars']['price']."<br />";	
	                        }                        
	                        if (strlen($_REQUEST['input_shortdescription'])<100){
	                        	$output_add[1].=$lang['error_shortdescription']."<br />";	
	                        }
	                        
	                        if (strlen($_REQUEST['input_description'])<150){
	                        	$output_add[1].=$lang['error_description']."<br />";	
	                        }
	                        
	                        if (strlen($_REQUEST['input_sitetitle'])<100){
	                        	$output_add[1].=$lang['error_sitetitle']."<br />";	
	                        }
	                        
	                        if (strlen($_REQUEST['input_metadescription'])<100){
	                        	$output_add[1].=$lang['error_metadescription']."<br />";	
	                        }

                        }
                        $outputtoscreen .= $Global_Class -> choose_option();
                        break;
//finish cars



//carsfeatures
                case "carsfeatures":

                        $default_tabel = "carsfeatures";
                        $default_id = array("id");
                        $o = $_REQUEST['o'];
                        $oid=$_REQUEST['oid'];
                        $id_ = "id";
                        if (intval($_REQUEST['id'])>0){
                        $user_profile = $Global_Class -> getprofile(  $_REQUEST['id'], $default_tabel, $id_ );
                        }                        
                        $config['config_auto_oid']=$oid;
                        if ($oid!=""){
                              session_register("option_oid1");
                              $_SESSION['option_oid1']=$oid;
                        }

                        if ($_SESSION['option_oid1']!="") {
                                $user_profile_parent = $Global_Class -> getprofile(  $_SESSION['option_oid1'], "cars", "id" );
                                $model_profile = $Global_Class -> getprofile(  $user_profile_parent[model], "model", $id_ );
                                $session_activate_name=$_SESSION['option_oid1']." ".$model_profile["name{$language_set}"];
                                $session_parent="cars";
                        }

                        $sql_default_global = " and {$config[table_prefix]}carsfeatures.carsid = '".$_SESSION['option_oid1']."' ";

                        $varchar_fields = array( );
                        $text_fields = array();
                        $file_fields = array();

                        $right_cookie[$p.'_view']=1;
                        $right_cookie[$p.'_edit']=1;
                        $right_cookie[$p.'_delete']=1;
                        $right_cookie[$p.'_add']=1;

                        if ($o=="add1") {
                           $Global_Class -> insertcheckbox($_SESSION['option_oid1'],'features','name','id','name','carsfeatures','carsid','featuresid');
                           $_REQUEST['o']="add";
                           $outputtoscreen_add[0]=true;
                           $HTTP_POST_VARS[error]=$lang['msg1'];

                        }else{
                           $_REQUEST[o]="add";
                        }

                        $lang["tpl_auto_View_Edit_Delete"] = "";
                        $lang["tpl_auto_Search"] = "";


                        $dropdown_fields = array();
                            //$dropdownval[featuresid] = $Global_Class -> getdropdown( $valoare_1, "features", "name", "id", "name" );



                        $radio_fields = array();
                        $radioval = array();
                        $checkbox_fields = array();

                        $password_fields = array();

                        $file = array(); // for pictures
                        $file_size = array(); // for pictures size

                        $relation = array( "featuresid" );
                        $relation_table["featuresid"][0] = "features";
                        $relation_table["featuresid"][1] = "id";
                        $relation_table["featuresid"][2] = "name{$language_set}";

                        $copy_from = array(); // for big pictures
                        $copy_from_val = array(); // for big picutres size
                        $require_array = array(); //require array
                        $password = array(); // for md5 fields
                        $copy_from_id = array("carsid");
                        $copy_from_id_value[carsid] = $_SESSION['option_oid1'];

                        $email_fields = array();
                        $date_fields = array();

                        $fields_not_show = array();

                        $field_name ="name{$language_set}"; //for delete
                        $search_fields = array();
                        $config['config2_multiple_options'][0] = 1;
                        $config['config2_multiple_options'][1] = $Global_Class -> getcheckbox($_SESSION['option_oid1'],"features","name{$language_set}","id","name{$language_set}","carsfeatures","carsid","featuresid");
                        $outputtoscreen .= $Global_Class -> choose_option();
                        if ($o=="add1" or $o=="edit1") {
                           $sql1="update `{$config[table_prefix]}cars` set date_modify=NOW() where id='{$_SESSION['option_oid1']}' limit 1";
                           $result1 = $db -> query($sql1);
                        }
                        break;
//finish carsfeatures

//gallery
                case "gallery":

                        $default_tabel = "gallery";
                        $default_id = array("id");
                        $o = $_REQUEST['o'];
                        $oid=$_REQUEST['oid'];
                        $id_ = "id";
                        if (intval($_REQUEST['id'])>0){
                        $user_profile = $Global_Class -> getprofile(  $_REQUEST['id'], $default_tabel, $id_ );
                        }                       
                        $config['config_auto_oid']=$oid;
                        if ($oid!=""){
                                  session_register("option_oid1");
                                  $_SESSION['option_oid1']=$oid;
                        }
                        if ($_SESSION['option_oid1']!="") {
                                $user_profile_parent = $Global_Class -> getprofile(  $_SESSION['option_oid1'], "cars", "id" );
                                $model_profile = $Global_Class -> getprofile(  $user_profile_parent[model], "model", $id_ );
                                $session_activate_name=$_SESSION['option_oid1']." ".$model_profile["name{$language_set}"];
                                $session_parent="cars";
                        }
                        if ($o=="add") {
                                $admin_profile = $Global_Class -> getprofile(  $HTTP_COOKIE_VARS['id_cookie'], "admin", "id" );
                                $nopictures = $Global_Class -> getnumrows( $HTTP_SESSION_VARS['option_oid1'], "gallery", "carsid");
                                if ($admin_profile[nopictures]!=0 and $nopictures>=$admin_profile[nopictures]) {
                                    $_REQUEST['o']="view";
                                }
                                if($admin_profile[nopictures]>0  and $config['auto_multiple'][$default_tabel]>$admin_profile[nopictures]-$nopictures){
                                 $config['auto_multiple'][$default_tabel]=$admin_profile[nopictures]-$nopictures;
                                }
						        if($admin_profile[nopictures]==0 ){
						        	$config['auto_multiple'][gallery]=$admin_profile[nopictures]=0;
						        }                                
                        }
                        $sql_default_global = " and {$config[table_prefix]}gallery.carsid = '".$_SESSION['option_oid1']."' ";
						if (!is_array($config['admin_section']['gallery']['varchar_fields'])) {
                        	$config['admin_section']['gallery']['varchar_fields']=array();
                        	$varchar_fields = array( "description", "order" );
						}else{
							$varchar_fields = $config['admin_section']['gallery']['varchar_fields'];
						}
                        $multiplefields = array( "description" );
                        $text_fields = array();
                        $file_fields = array();

                        $right_cookie[$p.'_view']=1;
                        $right_cookie[$p.'_edit']=1;
                        $right_cookie[$p.'_delete']=1;
                        $right_cookie[$p.'_add']=1;

                        $dropdown_fields = array();
                        $dropdownval = array();


                        $radio_fields = array();
                        $radioval = array();
                        $checkbox_fields = array();

                        $password_fields = array();

                        $file = array("picture","thumbnail"); // for pictures all
                        $file_fields = array("picture"); // for only show when add
                        $file_size["thumbnail"][0]=$IMG_HEIGHT;
                        $file_size["thumbnail"][1]=$IMG_WIDTH;
                        $file_size["picture"][0]=$IMG_HEIGHT_BIG;
                        $file_size["picture"][1]=$IMG_WIDTH_BIG;
                        $copy_from=array("thumbnail");
                        $copy_from_val["thumbnail"]="picture";

                        $filearray=array("thumbnail","image");

                        $relation = array();
                        $relation_table = array();

                        $require_array = array("picture"); //require array
                        $password = array(); // for md5 fields
                        $copy_from_id = array("carsid");
                        $copy_from_id_value[carsid] = $_SESSION['option_oid1'];

                        $email_fields = array();
                        $date_fields = array();

                        $fields_not_show = array("id","description{$language_set}");

                        $field_name ="id"; //for delete
                        $search_fields = array("id","description{$language_set}","order","thumbnail");
                        
                        if ($o=="add1" or $o=="edit1") {
	                        
	                        if (strlen($_REQUEST['input_description'])<15){
	                        	$output_add[1].=$lang['error_descriptiongallery']."<br />";	
	                        }
	                        if (is_uploaded_file($_FILES['multiple1_input_picture']['tmp_name'])){
		                        if (strlen($_REQUEST['multiple1_input_description'])<15){
		                        	$output_add[1].=$lang['error_descriptiongallery']."<br />";	
		                        }
	                        }
	                        if (is_uploaded_file($_FILES['multiple2_input_picture']['tmp_name'])){
		                        if (strlen($_REQUEST['multiple2_input_description'])<15){
		                        	$output_add[1].=$lang['error_descriptiongallery']."<br />";	
		                        }
	                        }
                        }

                                               
                        $outputtoscreen .= $Global_Class -> choose_option();
                        if ($o=="add1" or $o=="edit1") {
                           $sql1="update `{$config[table_prefix]}cars` set date_modify=NOW() where id='{$_SESSION['option_oid1']}' limit 1";
                           $result1 = $db -> query($sql1);
                        }
                        break;
//finish gallery


//messages
                case "messages":

                        $default_tabel = "messages";
                        $default_id = array("id");
                        $o = $_REQUEST['o'];
                        $oid=$_REQUEST['oid'];
                        $id_ = "id";
                        if (intval($_REQUEST['id'])>0){
                        $user_profile = $Global_Class -> getprofile(  $_REQUEST['id'], $default_tabel, $id_ );
                        }
                        $config['config_auto_oid']=$oid;
                        if ($oid!=""){
                                  session_register("option_oid1");
                                  $_SESSION['option_oid1']=$oid;
                        }
                        if ($_SESSION['option_oid1']!="") {
                                $user_profile_parent = $Global_Class -> getprofile(  $_SESSION['option_oid1'], "cars", "id" );
                                $model_profile = $Global_Class -> getprofile(  $user_profile_parent[model], "model", $id_ );
                                $session_activate_name=$_SESSION['option_oid1']." ".$model_profile["name{$language_set}"];
                                $session_parent="cars";
                        }

                        $sql_default_global = " and {$config[table_prefix]}messages.carsid = '".$_SESSION['option_oid1']."' ";

                        $varchar_fields = array( "name", "email", "phone" );
                        $multiplefields = array(  );
                        $text_fields = array("message");
                        $file_fields = array();

                        $right_cookie[$p.'_view']=1;
                        $right_cookie[$p.'_edit']=1;
                        $right_cookie[$p.'_delete']=1;
                        $right_cookie[$p.'_add']=1;

                        $dropdown_fields = array();
                        $dropdownval = array();


                        $radio_fields = array();
                        $radioval = array();
                        $checkbox_fields = array();

                        $password_fields = array();

                        $file = array(); // for pictures all
                        $file_fields = array(); // for only show when add

                        $copy_from=array();
                        $copy_from_val=array();

                        $filearray=array();

                        $relation = array();
                        $relation_table = array();

                        $require_array = array(); //require array
                        $password = array(); // for md5 fields
                        $copy_from_id = array("carsid");
                        $copy_from_id_value[carsid] = $_SESSION['option_oid1'];

                        $email_fields = array();
                        $date_fields = array("date_add");

                        $fields_not_show = array("id","name","email","phone","date_add");

                        $field_name ="id"; //for delete
                        $search_fields = array("id","name","email","phone","date_add");
                        $outputtoscreen .= $Global_Class -> choose_option();
                        break;
//finish messages


//sponsored
                case "sponsored":

                        $default_tabel = "sponsored";
                        $default_id = array("id");
                        $o = $_REQUEST['o'];
                        $id_ = "id";
                        if (intval($_REQUEST['id'])>0){
                        $user_profile = $Global_Class -> getprofile(  $_REQUEST['id'], $default_tabel, $id_ );
                        }

                        if ($o=="add") {
                                $valoare_1 = -1;
                        }elseif ($o=="edit" or $o=='see') {
                                $valoare_1 = $user_profile['carid'];
                        }else{
                                $valoare_1 = $_POST['input_carid'];
                        }

                        $varchar_fields = array(  );
                        $text_fields = array();
                        $file_fields = array();
                        $dropdown_fields = array("carid");

                        $dropdownval["carid"] = $Global_Class -> getdropdownspon( $valoare_1, "cars", "model", "id", "model" ,0);
                        if ($o=="edit1" or $o=="add1"){
                           checkoverapping('carid');
                        }
                        $radio_fields = array();
                        $radioval = array();
                        $checkbox_fields = array();

                        $password_fields = array();

                        $file = array(); // for pictures
                        $file_size = array(); // for pictures size

                        $relation = array();
                        $relation_table = array();
                        $relation = array( "carid" );
                        $relation_table["carid"][0] = "cars";
                        $relation_table["carid"][1] = "id";
                        $relation_table["carid"][2] = "model";
                        //$relation_table["carid"][3] = 1;

                        $copy_from = array(); // for big pictures
                        $copy_from_val = array(); // for big picutres size
                        $require_array = array(); //require array
                        $password = array(); // for md5 fields
                        $copy_from_id = array("");
                        $copy_from_id_value = array();
                        $email_fields = array();
                        $date_fields = array("date_start","date_ends");

                        $fields_not_show = array("id","carid","date_start","date_ends");

                        $field_name ="id"; //for delete
                        $search_fields = array("id","carid","date_start","date_ends");
                        $outputtoscreen .= $Global_Class -> choose_option();
                        break;
//finish sponsored


//send email
                case "sendemail":

                        if (  $right_cookie['sendemail'] == 0   )
                        {
                                $outputtoscreen .= $lang["error_permission"];
                        }else {
                                switch($_REQUEST[o]){
                                        case "sendemail1":

                                                $outputtoscreen .= $Global_Class -> sendemail1();
                                        break;
                                        default:
                                        $outputtoscreen .= $Global_Class -> sendemail();
                                        break;
                                }
                        }
                break;
//ends
//send email
                case "sendemailadmin":

                        if (  $right_cookie['sendemailadmin'] == 0   )
                        {
                                $outputtoscreen .= $lang["error_permission"];
                        }else {
                                switch($_REQUEST[o]){
                                        case "sendemailadmin1":

                                                $outputtoscreen .= $Global_Class -> sendemailadmin1();
                                        break;
                                        default:
                                        $outputtoscreen .= $Global_Class -> sendemailadmin();
                                        break;
                                }
                        }
                break;
//ends


                case "renew":

                    $permission_denied =false;
                    $right_cookie['renew_view']=1;
                    $right_cookie['renew_edit']=1;
                    $right_cookie['renew_delete']=1;
                    $right_cookie['renew_add']=1;
                    $o = $_REQUEST['o'];
                    $oo = eregi_replace( "0|1|2|3", "", $o );
                    $oo = eregi_replace( "activate|deactivate|sold", "edit", $oo );
                    $oo_bold=$oo;


                    $p1=$_REQUEST[p1];


                    switch ($p1){
                              case "ok":
                               		$adprofiles= $Global_Class -> getprofile( $_REQUEST[ad], "adprofiles", "id" );
			

                                    $var[name].=$adprofiles["titleok".$language_set];
                                 	$var[mesg] .= $adprofiles["textok".$language_set];
                                 	$outputtoscreen .= $tpl -> replace( $var, "mesg.html" );
                              break;
                              case "notok":
                               		$adprofiles= $Global_Class -> getprofile( $_REQUEST[ad], "adprofiles", "id" );			

                                    $var_header[name].=$adprofiles["titlenotok".$language_set];
                                 	$var[mesg] .= $adprofiles["textnotok".$language_set];
                                 	$outputtoscreen .= $tpl -> replace( $var, "mesg.html" );
                              break;                     	
                              case "renew1":
                                   if ($permission_denied) {
                                   break;
                                   }
                                    $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;<a href=\"index.php?p=renew\">{$lang['tpl_auto_Renew_account']}</a>&nbsp;&nbsp;›&nbsp;&nbsp;{$lang['tpl_auto_Payment_options']}";
                                 $outputtoscreen .= $renewclass->renewclass1();
                              break;
                              case "renew2":
                                   if ($permission_denied) {
                                   break;
                                   }
                                 $outputtoscreen .= $renewclass->renewclass2();
                              break;
                              default:
                                   if ($permission_denied) {
                                   break;
                                   }
                                   $o=$_REQUEST[o];
                                   if ($o=="" or $o=="delete") {
                                       $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;{$lang['tpl_auto_Renew_account']}";
                                   }else{
                                       $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;<a href=\"index.php?p=renew\">{$lang['tpl_auto_Renew_account']}</a>";
                                   }

                                   $outputtoscreen .= $renewclass->renewclass($error);
                              break;

                     }
                  break;                

                case "summary":
                        if ( $right_cookie['summary'] == 0 )
                        {
                                $outputtoscreen .= $homepage_html;
                                break;
                        }else{
                        $var[logs]=logs($var[onlineusers]);
                        $var[statistics]=$statsoutput;
                        $var[top]=top();
                        $outputtoscreen .= $tpl -> replace( $var, "logs.html" );
                        break;
                        }
                case "logout":
                        $admin -> logoutadmin();
                        
                        $row = array(
									"admin"=>$_COOKIE['id_cookie'],
									"action"=>$lang['logging']['user_logout'],
									);
					
						addlogging( $row ); 
						                        
                        header( "Location: index.php" );
                        exit(0);
                        break;
//start default


                case "backup":
                        if ( $right_cookie['backup'] == 0 )
                        {
                                $outputtoscreen .= $lang["error_permission"];
                                break;
                        }else{

                                switch($_REQUEST[o]){
                                                case "backup1":
                                                                                  $pathsearch=$config['mysqldump_pathtosearch'];
                                                                                  $open_basedir=@ini_get('open_basedir');
                                                                                  $pathsearch=array_merge($pathsearch,explode(':',$open_basedir));
                                                                                  $mysql_exe = 'unknown';
                                                                                  $mysqldump_exe = 'unknown';
                                                                                  foreach($pathsearch as $path){
                                                                                          $path = str_replace('\\','/',$path); // convert backslashes
                                                                                          $path = str_replace('//','/',$path); // convert double slashes to singles
                                                                                          $path = (substr($path,-1)!='/') ? $path . '/' : $path; // add a '/' to the end if missing
                                                                                    if ($mysql_exe == 'unknown') {
                                                                                      if (@file_exists($path.'mysql'))     $mysql_exe = $path.'mysql';
                                                                                      if (@file_exists($path.'mysql.exe')) $mysql_exe = $path.'mysql.exe';
                                                                                    }
                                                                                    if ($mysqldump_exe == 'unknown') {
                                                                                      if (@file_exists($path.'mysqldump'))     $mysqldump_exe = $path.'mysqldump';
                                                                                      if (@file_exists($path.'mysqldump.exe')) $mysqldump_exe = $path.'mysqldump.exe';
                                                                                    }

                                                                                    if ($mysql_exe != 'unknown' && $mysqldump_exe != 'unknown') break;
                                                                                  }

                                                                                  if (!$mysqldump_exe or $mysqldump_exe== 'unknown'){
                                                                                  	$mysqldump_exe="mysqldump";
                                                                                  }
                                                                                  if (!$mysqldump_exe or $mysqldump_exe== 'unknown'){
                                                                                          $var['error'] = $lang["tpl_auto_WARNING_mysqldump_binary_not_found"];

                                                                                          $var[p]="backup";
                                                                    $var[o]="backup1";
                                                                     $outputtoscreen.=$tpl->replace($var, "backup.html");
                                                                                  }else{
                                                                                         $filename=date('Y-m-j_h-i-s');
                                                                                         $outreturn =  $filename = $mysqldump_exe." -Q -u ".$config['sqluser']." -p".$config['sqlpass'] ." -h ".$config['sqlhost']." ".$config['sqldb']."> ".$config['path']."temp/backup_".$filename.".sql";
                                                                                         $outreturn .=  $handle =exec ($filename,$array);
                                                                                         $outreturn .=  $array1=@implode('',$array);
                                                                                         $outreturn .=  "\n\n";

                                                                                         
                                                                                         
          if (!defined('PMA_USR_OS')) {
            if (!empty($_SERVER['HTTP_USER_AGENT'])) {
                $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
            } else if (!isset($HTTP_USER_AGENT)) {
                $HTTP_USER_AGENT = '';
            }
            // 1. Platform
            if (strstr($HTTP_USER_AGENT, 'Win')) {
                define('PMA_USR_OS', 'Win');
            } else if (strstr($HTTP_USER_AGENT, 'Mac')) {
                define('PMA_USR_OS', 'Mac');
            } else if (strstr($HTTP_USER_AGENT, 'Linux')) {
                define('PMA_USR_OS', 'Linux');
            } else if (strstr($HTTP_USER_AGENT, 'Unix')) {
                define('PMA_USR_OS', 'Unix');
            } else if (strstr($HTTP_USER_AGENT, 'OS/2')) {
                define('PMA_USR_OS', 'OS/2');
            } else {
                define('PMA_USR_OS', 'Other');
            }
            // 2. browser and version
            // (must check everything else before Mozilla)
            if (preg_match('@Opera(/| )([0-9].[0-9]{1,2})@', $HTTP_USER_AGENT, $log_version)) {
                define('PMA_USR_BROWSER_VER', $log_version[2]);
                define('PMA_USR_BROWSER_AGENT', 'OPERA');
            } else if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $HTTP_USER_AGENT, $log_version)) {
                define('PMA_USR_BROWSER_VER', $log_version[1]);
                define('PMA_USR_BROWSER_AGENT', 'IE');
            } else if (preg_match('@OmniWeb/([0-9].[0-9]{1,2})@', $HTTP_USER_AGENT, $log_version)) {
                define('PMA_USR_BROWSER_VER', $log_version[1]);
                define('PMA_USR_BROWSER_AGENT', 'OMNIWEB');
                // } else if (ereg('Konqueror/([0-9].[0-9]{1,2})', $HTTP_USER_AGENT, $log_version)) {
                // Konqueror 2.2.2 says Konqueror/2.2.2
                // Konqueror 3.0.3 says Konqueror/3
            } else if (preg_match('@(Konqueror/)(.*)(;)@', $HTTP_USER_AGENT, $log_version)) {
                define('PMA_USR_BROWSER_VER', $log_version[2]);
                define('PMA_USR_BROWSER_AGENT', 'KONQUEROR');
            } else if (preg_match('@Mozilla/([0-9].[0-9]{1,2})@', $HTTP_USER_AGENT, $log_version) && preg_match('@Safari/([0-9]*)@', $HTTP_USER_AGENT, $log_version2)) {
                define('PMA_USR_BROWSER_VER', $log_version[1] . '.' . $log_version2[1]);
                define('PMA_USR_BROWSER_AGENT', 'SAFARI');
            } else if (preg_match('@Mozilla/([0-9].[0-9]{1,2})@', $HTTP_USER_AGENT, $log_version)) {
                define('PMA_USR_BROWSER_VER', $log_version[1]);
                define('PMA_USR_BROWSER_AGENT', 'MOZILLA');
            } else {
                define('PMA_USR_BROWSER_VER', 0);
                define('PMA_USR_BROWSER_AGENT', 'OTHER');
            }
        }



        $mime_type = (PMA_USR_BROWSER_AGENT == 'IE' || PMA_USR_BROWSER_AGENT == 'OPERA')
        ? 'application/octetstream'
        : 'application/octet-stream';
        $date = date("M_j_Y_G_i");
        header('Content-Type: ' . $mime_type);
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        // lem9 & loic1: IE need specific headers
        if (PMA_USR_BROWSER_AGENT == 'IE') {
            header('Content-Disposition: inline; filename=backup_'.$filename.'.sql');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        } else {
            header('Content-Disposition: attachment; filename=backup_'.$filename.'.sql');
            header('Pragma: no-cache');
        }
                                                                                                 

                                                                                         sleep(2);
                                                                                         @readfile($config['path']."temp/backup_".$filename.".sql");
                                                                                         @unlink($config['path']."temp/backup_".$filename.".sql");
                                                                                         exit(0);
                                                                                  }
                                        break;
                                        default:
                                                                $var[p]="backup";
                                                                    $var[o]="backup1";
                                                                     $outputtoscreen.=$tpl->replace($var, "backup.html");
                                        break;
                                }


                        break;
                        }


                case "tpl":
                        if ( $right_cookie['tpl'] == 0 )
                        {
                                $outputtoscreen .= $lang["error_permission"];
                                break;
                        }else{

                                switch($_REQUEST[o]){
                                                case "tpl1":
                                                                $var[p]="tpl";
                                                                    $var[o]="tpl2";
                                                                                        $var[id]=$_REQUEST['file'];
                                                                                        $var['cols'] = 1.5*$config['cols'];
                                                                                        $var['rows'] = 7*$config['rows'];
                                                                                        $condtemplates=0;
                                                                                        $var['what_add']='';
                                                                                        $var['error']='';
                                                                                        $var['tpl_name']=$lang['tpl_auto_Templates_files'].$_REQUEST['file'];
                                                                                        $var['tpl_input_name']='content';
                                                                                        $var['tpl_input_name_val']=fortemplates(htmlspecialchars(implode('',file($config['tplvisit'].$var[id]))));
                                                                                        $var['options']=$tpl->replace($var, "global_add_text.html","",$condtemplates);

                                                        $outputtoscreen.=$tpl->replace($var, "global_edit.html","",$condtemplates);
                                        break;
                                                case "tpl2":
                                                                $var[p]="tpl";
                                                                    $var[o]="tpl2";
                                                                                        $var[id]=$_REQUEST['id'];

                                                                                        $var['file']=$lang['tpl_auto_Templates_files'].$_REQUEST['id'];

                                                                                        $somecontent=fortemplatestosave(stripslashes($_REQUEST['content']));

                                                                                        $var['error'].=writetofile($config['tplvisit'].$var[id],$somecontent)."<br />";

                                        default:
                                                                $var[p]="tpl";
                                                                    $var[o]="tpl1";
                                                                                        $var['error'].=$lang['tpl_auto_select_the_File_to_Modify'];
                                                                                        if ($handle = opendir($config['tplvisit'])) {
                                                                                           /* This is the correct way to loop over the directory. */
                                                                                           $var['options'] .="<ul>";
                                                                                           while (false !== ($file = readdir($handle))) {
                                                                                                            if ($file != "." && $file != "..") {
                                                                                                 $var['options'].= "<li><a href=\"index.php?p=tpl&amp;o=tpl1&amp;file=$file\">$file</a></li>\n";
                                                                                                            }
                                                                                           }
                                                                                           $var['options'] .="</ul>";
                                                                                           closedir($handle);
                                                                                        }

                                                                     $outputtoscreen.=$tpl->replace($var, "tpl.html");
                                        break;
                                }


                        break;
                        }


                case "language":
                        if ( $right_cookie['language'] == 0 )
                        {
                                $outputtoscreen .= $lang["error_permission"];
                                break;
                        }else{

                                switch($_REQUEST[o]){
                                                case "language1":
                                                                $var[p]="language";
                                                                    $var[o]="language2";
                                                                                        $var[id]=$_REQUEST['file'];
                                                                                        $var['cols'] = 1.5*$config['cols'];
                                                                                        $var['rows'] = 7*$config['rows'];
                                                                                        $condtemplates=0;
                                                                                        $var['what_add']='';
                                                                                        $var['error']='';
                                                                                        $var['tpl_name']=$lang['tpl_auto_Templates_files'].$_REQUEST['file'];
                                                                                        $var['tpl_input_name']='content';
                                                                                        $var['tpl_input_name_val']=fortemplates(htmlspecialchars(implode('',file($config['path'].'language/'.$var[id]))));
                                                                                        $var['options']=$tpl->replace($var, "global_add_text.html","",$condtemplates);

                                                        $outputtoscreen.=$tpl->replace($var, "global_edit.html","",$condtemplates);
                                        break;
                                                case "language2":
                                                                $var[p]="language";
                                                                    $var[o]="language2";
                                                                                        $var[id]=$_REQUEST['id'];

                                                                                        $var['file']=$lang['tpl_auto_Templates_files'].$_REQUEST['id'];

                                                                                        $somecontent=fortemplatestosave(stripslashes($_REQUEST['content']));

                                                                                        $var['error'].=writetofile($config['path'].'language/'.$var[id],$somecontent)."<br />";

                                        default:
                                                                $var[p]="language";
                                                                    $var[o]="language1";
                                                                    $var['tpl_auto_tpl']=$lang['tpl_auto_Language'];
                                                                                        $var['error'].=$lang['tpl_auto_select_the_File_to_Modify'];
                                                                                        if ($handle = opendir($config['path'].'language/')) {
                                                                                           /* This is the correct way to loop over the directory. */
                                                                                           $var['options'] .="<ul>";
                                                                                           while (false !== ($file = readdir($handle))) {
                                                                                                            if ($file != "." && $file != "..") {
                                                                                                 $var['options'].= "<li><a href=\"index.php?p=language&amp;o=language1&amp;file=$file\" class=''>$file</a></li>\n";
                                                                                                            }
                                                                                           }
                                                                                           $var['options'] .="</ul>";
                                                                                           closedir($handle);
                                                                                        }

                                                                     $outputtoscreen.=$tpl->replace($var, "tpl.html");
                                        break;
                                }


                        break;
                        }

//banner start
                case "banner":

                        $permission_denied =false;
                        $o = $_REQUEST['o'];
                        $oo = eregi_replace( "0|1|2|3", "", $o );
                        $oo = eregi_replace( "activate|deactivate|sold", "edit", $oo );
                        $oo_bold=$oo;

                        if ( $o == "search" or $o=="see") $oo = "view";
                        if ( ( ( $right_cookie[$p.'_view'] == 0 ) && ( $oo == "" ) ) || ( ( $right_cookie[$p.'_' . $oo] == 0 ) && ( $oo != "" ) ) )
                        {
                         $outputbanners .= $lang["error_permission"];
                         $permission_denied = true;
                        }



                     $p1=$_REQUEST[p1];
                     if ($p1=="") {
                     $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;{$lang['tpl_auto_Banner_Functions']}";
                     }else{
                     $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;<a href=\"index.php?p=banner\">{$lang['tpl_auto_Banner_Functions']}</a>";
                     }
                     switch ($p1){
                              case "banner":
                                   if ($permission_denied) {
                                   break;
                                   }
                                   $o=$_REQUEST[o];
                                   if ($o=="" or $o=="delete") {
                                       $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;{$lang['tpl_auto_Banner']}";
                                   }else{
                                       $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;<a href=\"index.php?p=banner&p1=banner\">{$lang['tpl_auto_Banner']}</a>";
                                   }
                                   switch ($o){
                                            case "delete":
                                                  $outputbanners .= $banner_class->deletebanner();
                                            break;
                                            case "add":
                                                  $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;{$lang['tpl_auto_Add_new_Banner']}";
                                                  $outputbanners .= $banner_class->addbanner();
                                            break;
                                            case "add1":
                                                  $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;{$lang['tpl_auto_Add_new_Banner']}";
                                                  $outputbanners .= $banner_class->addbanner1();
                                            break;
                                            case "edit":
                                                  $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;{$lang['tpl_auto_Edit_Banner']}";
                                                  $outputbanners .= $banner_class->editbanner();
                                            break;
                                            case "edit1":
                                                  $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;{$lang['tpl_auto_Edit_Banner']}";
                                                  $outputbanners .= $banner_class->editbanner1();
                                            break;
                                            default:
                                                  $outputbanners .= $banner_class->banner($error);
                                            break;
                                   }
                              break;



                              case "bannerstats":
                                   if ($permission_denied) {
                                   break;
                                   }
                                   $o=$_REQUEST[o];
                                   if ($o=="" or $o=="delete") {
                                       $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;{$lang['tpl_auto_Banner_stats']}";
                                   }else{
                                       $var_header[location_top].="&nbsp;&nbsp;›&nbsp;&nbsp;<a href=\"index.php?p=banner&p1=bannerstats\">{$lang['tpl_auto_Banner_stats']}</a>";
                                   }
                                   switch ($o){
                                            default:
                                                  $outputbanners .= $bannerstats_class->banner($error);
                                            break;
                                   }
                              break;

                              default:
                                    $outputbanners .= $tpl -> replace( $var_header, "banner.html" ); //read header
                              break;
                     }
                     $var_header[banner]=$outputbanners;
                     $outputtoscreen .= $tpl -> replace( $var_header, "global_banner.html" );
                  break;

//banner ends  
//importsettings start
                case "importsettings":
                        if ( $right_cookie['importsettings'] == 0 )
                        {
                                $outputtoscreen .= $lang["error_permission"];
                                break;
                        }else{
	                     $o1=$_REQUEST[o];
	                     switch ($o1){
	                              case "importsettings1":	
									if (!is_array($_REQUEST['importsettings'])) $_REQUEST['importsettings']=array();
									if (!is_array($_REQUEST['importsettingsdefaultvalue'])) $_REQUEST['importsettingsdefaultvalue']=array();
			                        $sql="TRUNCATE TABLE `{$config['table_prefix']}importsettings` ";
			                        $result = $db -> query($sql);								
									
									foreach ($_REQUEST['importsettings'] as $key=>$val){
										if ($val==-1) $val=999999;
										$sql="INSERT INTO `{$config['table_prefix']}importsettings` ( `id` , `field` , `relation` , `defaultvalue` )
	VALUES (
	'', '{$key}', '{$val}', '{$_REQUEST[importsettingsdefaultvalue][$key]}'
	) ";
			                        	$result = $db -> query($sql);									
									}
			                        $var = array ( "tpl_msg" => $lang['tpl_auto_your_preferences_saved']
			                                );
			                        $outputtoscreen .= $tpl -> replace( $var, "admin_profileupdated.html" );	                              
	                              break;
	                              default:							
						
									$notusedarray=array();
			                        foreach ($config['admin_section']['cars']['multiplefields'] as $mul_key=>$mul_val) {
			                                 $count_mul = 0;
			                                 $ct=1;
			                                 foreach ($multiplelanguage as $multiple_key=>$multiple_val) {
			                                      if ($count_mul==0) {
			                                       $count_mul = 1;
			                                      }
			                                      $ct++;
			                                      $lang['tabel_cars']["$mul_val".$multiple_key] = "<font class=\"languageadmin\">[ ".$multiple_val."] </font> ".$lang['tabel_cars']["$mul_val"];
			                                 }
			                                 if ($count_mul==1) {
			                                       $lang['tabel_cars']["$mul_val"] = "<font class=\"languageadmin\">[ ".ucfirst(substr($settings_profile[language],0,-4))."] </font> ".$lang['tabel_cars']["$mul_val"];
			                                 }
			                                 for($i=$ct+1;$i<=4;$i++){
			                                  	if ($i==1) $ii="";
			                                  	else $ii=$i-1;
					                        	$notusedarray[]=$mul_val.$ii;
					                         }		
			                        }
			                       
			                        foreach ($config['admin_section']['cars']['multiplefields_text'] as $mul_key=>$mul_val) {
			                                 $count_mul = 0;
			                                  $ct=1;
			                                 foreach ($multiplelanguage as $multiple_key=>$multiple_val) {
			                                      if ($count_mul==0) {
			                                       $count_mul = 1;
			                                      }
			                                      $ct++;
			                                      $lang['tabel_cars']["$mul_val".$multiple_key] = "<font class=\"languageadmin\">[ ".$multiple_val."] </font> ".$lang['tabel_cars']["$mul_val"];
			                                 }
			                                 if ($count_mul==1) {
			                                       $lang['tabel_cars']["$mul_val"] = "<font class=\"languageadmin\">[ ".ucfirst(substr($settings_profile[language],0,-4))."] </font> ".$lang['tabel_cars']["$mul_val"];
			                                 }
			                                 for($i=$ct+1;$i<=4;$i++){
			                                  	if ($i==1) $ii="";
			                                  	else $ii=$i-1;
					                        	$notusedarray[]=$mul_val.$ii;
					                         }		                                 
			                        }		 
			                        //print_r($notusedarray);
	                        		$tablefield_array_rall=array();
	                        		$tablefield_array_rallname=array();
			                        $sql="SHOW FIELDS FROM `{$config['table_prefix']}cars` ";
			                        $result = $db -> query($sql);
			                        while ($tablefield_array_r = mysql_fetch_array($result)){
			                        	if ($tablefield_array_r['Field']!='id' and !in_array($tablefield_array_r['Field'],$notusedarray) and !in_array($tablefield_array_r['Field'],$config['admin_section']['cars']['notimportfields']) ){
			                               $tablefield_array_rall[]="cars__".$tablefield_array_r['Field'];
			                               $tablefield_array_rallname["cars__".$tablefield_array_r['Field']]=$lang["tpl_auto_cars"]." ".$lang['tabel_cars'][$tablefield_array_r['Field']];
			                        	}
			                        }                         
	
									$sql = "SELECT {$config[table_prefix]}features.* FROM `{$config[table_prefix]}features` where 1 order by {$config[table_prefix]}features.name{$language_set}";
								    $result = $db -> query( $sql );
								    $num_rows = mysql_num_rows( $result );
								    $contor=0;
								    if ( $num_rows > 0 ) {
								      while ( $var_features = mysql_fetch_assoc( $result ) ) {
								           $tablefield_array_rall[]="features__".$var_features[id];
								           $tablefield_array_rallname["features__".$var_features[id]]=$lang["tpl_auto_features"]." ".$var_features["name$language_set"];
								      } // while
								      @mysql_free_result($result);
								    }
							    
								    for($i=1;$i<=$config['number_pictures_import'];$i++){
								           $tablefield_array_rall[]="gallery__".$i;
								           $tablefield_array_rallname["gallery__".$i]=$lang["tpl_auto_gallery"]." #".$i;
								    }
	
			                        $sql="SELECT * FROM `{$config['table_prefix']}importsettings` order by `relation` ";
			                        $result = $db -> query($sql);
			                        $num_rows = mysql_num_rows( $result );
			                        $arrayallresults=$arrayallresultskey=array();
			                        $ct=0;		                        
			                        if ( $num_rows > 0 ) {
			                         while ($userprofiles = mysql_fetch_array($result)){
			                         	$ct++;
			                         	$arrayallresults[$ct]=$userprofiles;
			                         	$arrayallresultskey[$ct]=$userprofiles[field];
			                         }
			                         @mysql_free_result($result);
	                        		}
		                        		
	                        		foreach ($tablefield_array_rall as $key=>$val){
	                        			if (!in_array($val,$arrayallresultskey)){
	                        				$ct++;
				                         	$arrayallresults[$ct]=array('field'=>$val,'relation'=>$ct,'defaultvalue'=>'',);
				                         	$arrayallresultskey[$ct]=$val;                        				
	                        			}
	                        		}
	                        		
				                       
			                        $var['class_temp']="class_temp2";  		                        
				                    $var['tpl_name']=$lang['tpl_auto_importsettings_field_name'];
				                    $var['tpl_input_name_val']="<b>".$lang['tpl_auto_importsettings_fields_location']."</b>";
	                  			                        
	
			                        $out1.=$tpl->replace($var,"global_add_see.html");
				                                                		
									foreach ($arrayallresults as $key=>$valarray){
										if ($valarray[relation]==999999) $valarray[relation]=-1;
				                        if ($key%2) $var['class_temp']="class_temp1";
				                        else $var['class_temp']="class_temp2";  
	
					                    $var['tpl_name']=$tablefield_array_rallname[$valarray[field]];
					                    $var['tpl_input_name']="importsettings[".$valarray[field]."]";
					                    $var['tpl_input_name_val']=$valarray[relation];
					                    if (eregi("features__",$valarray[field])){
					                    $var['tpl_input_name_explain']=$lang['tpl_auto_features_importexpl'];	
					                    }elseif (eregi("gallery__",$valarray[field])){
					                    $var['tpl_input_name_explain']=$lang['tpl_auto_gallery_importexpl'];	
					                    }else{
					                    $var['tpl_input_name_explain']=$lang['tpl_auto_'.$valarray[field]];
					                    }
	
					                    $var['tpl_input_name1']="importsettingsdefaultvalue[".$valarray[field]."]";
					                    $var['tpl_input_name1_val']=$valarray[defaultvalue];				                                        
				                        $var['goodchars'] = $config['default_digits']."-";
				                        $var['size'] = $config['default_fieldssize_in_admin'];
				                        $var['maxlength'] = $config['default_fieldssize_in_admin'];
				                        $out1.=$tpl->replace($var,"global_add_varcharspecial1.html");
				                        
									}
									$var[p]='importsettings';
									$var[o]='importsettings1';
									$var['what_add']=$lang['tpl_auto_importsettings'];
									$var['options']=$out1;
	                                $outputtoscreen.=$tpl->replace($var, "global_edit.html");
	                        		
			                        @mysql_free_result($result);
	                        		break;
	                     }
	                     break;		                        		
                        }
//importsettings ends

//fixtools start
                case "fixtools":
                        if ( $right_cookie['fixtools'] == 0 )
                        {
                             $outputtoscreen .= $lang["error_permission"];
                             break; 
                        }else{
                        	
		                     $o1=$_REQUEST[o];
		                     switch ($o1){
		                              case "fixtoolsremake":	
									   require $path . "image1.class.php";
				                       $Image_Class1 = new Image1;		                              
		                               include_once("remake_thumbnail.php");                        	
				                       $outputtoscreenfix = $lang['tpl_auto_remake_thumbnail_error'];

				                      break;
			                          case "fixtoolsdelete":
		                                	    include_once("delete_all_images.php");
				                        $outputtoscreenfix = $lang['tpl_auto_Delete_all_images_not_used_error']." ( $imagesct {$lang['tpl_auto_images']})";
				                      break;			                      
		                              default:
				                        $var = array ( "error" => $lang['tpl_auto_Please_waitIt_might_take_a_minute']
				                                );		                              
				                        $outputtoscreenfix .= $tpl -> replace( $var, "admin_fixtools.html" );
		                        	  break;
		                     }	  
		                     
		                     $var_header[banner]=$outputtoscreenfix;
		                     $outputtoscreen .= $tpl -> replace( $var_header, "global_banner.html" );		                                                break; 
                        }                
//fixtools ends  
//import start
                case "import":
                        if ( $right_cookie['import'] == 0 )
                        {
                                $outputtoscreen .= $lang["error_permission"];
                                break;
                        }else{
                        $var[p]="import";
                        $var[o]="import1";
                        if ($_REQUEST[o]!='import1'){
                        	$aa=explode("__",$_COOKIE['autocars_import']);
                        	//print_R($aa);
                        	//exit;
						    if (isset($_COOKIE['autocars_import'])){
						    	$var['terminatedby']=$aa[0];
						    }else{
						    	$var['terminatedby']=";";
						    }
						
						    if (isset($_COOKIE['autocars_import'])){
						    	$var['ignorefirst']=($aa[1])?"checked":"";
						    }else{
						    	$var['ignorefirst']="checked";
						    }     

						    if (isset($_COOKIE['autocars_import'])){
						    	$var['enclosedby']=stripslashes($aa[2]);
						    }else{
						    	$var['enclosedby']='"';
						    }	
						    if ($var['enclosedby']=='"') $var['enclosedby']=htmlspecialchars($var['enclosedby']);
                        	$outputtoscreen .= $tpl -> replace( $var, "import.html","",1 );
                        }else{
						$_REQUEST['input_enclosedby']=stripslashes($_REQUEST['input_enclosedby']);
						if ($_REQUEST['input_enclosedby']=='&quot;') $_REQUEST['input_enclosedby']='"';
                        $file_name = $HTTP_POST_FILES ['input_importfile']["name"];
                        $file = $HTTP_POST_FILES ['input_importfile']["tmp_name"];
						require $path . "image1.class.php";
                        $Image_Class1 = new Image1;
                        $count=0;
                        $count1=0;

                        $sql="SELECT * FROM `{$config['table_prefix']}importsettings` order by `relation` ";
                        $result = $db -> query($sql);
                        $num_rows = mysql_num_rows( $result );
                        $arrayallresults=$arrayallresultskey=array();
                        $ct=0;		                        
                        if ( $num_rows > 0 ) {
                         while ($userprofiles = mysql_fetch_array($result)){
                         	$userprofiles[relation]=($userprofiles[relation]==999999)?-1:$userprofiles[relation]-1;
                         	$config['import_relation'][$userprofiles[field]]=$userprofiles[relation];
                         	$config['import_relation_defaultvalue'][$userprofiles[field]]=$userprofiles[defaultvalue];
                         	if (eregi("features__",$userprofiles[field])){
                         	$config['import_relationfeatures'][$userprofiles[field]]=$userprofiles[relation];
                         	$config['import_relationfeatures_defaultvalue'][$userprofiles[field]]=$userprofiles[defaultvalue];
                         	}
                         	if (eregi("gallery__",$userprofiles[field])){
                         	$config['import_relationgallery'][$userprofiles[field]]=$userprofiles[relation];
                         	$config['import_relationgallery_defaultvalue'][$userprofiles[field]]=$userprofiles[defaultvalue];
                         	}                         	
                         }
                         @mysql_free_result($result);
                		}
	                        		                        
                        if ($_REQUEST[repeat]==0){
                         copy($file,$config['path']."temp/import.csv");
                         $lastid=-1;
                         setcookie ( "autocars_import", $_REQUEST['input_terminatedby']."__".$_REQUEST['input_ignorefirst']."__".$_REQUEST['input_enclosedby'], time() + 3600*24*360 );
                        }else{
                         $file =$config['path']."temp/import.csv";
                         $lastidaa=file_get_contents($config['path']."temp/lastimport.txt");
                         $aaa=explode("|",$lastidaa);
                         $lastid=$aaa[0];
                         $count=$aaa[1];
                         $count1=$aaa[2];
                         $_REQUEST['input_terminatedby']=$aaa[3];
                         $_REQUEST['input_enclosedby']=$aaa[4];
                        }

                        //$file=file($file);

                        $sizefile=filesize($file);
                        $file = fopen ($file,"r");
                        $coot=0;
                        //foreach ($file as $key=>$val){
                        $key=-1;

                        $sql="SHOW FIELDS FROM `{$config['table_prefix']}cars` ";
                        $result = $db -> query($sql);
                        while ($tablefield_array_r = mysql_fetch_array($result)){
                               $tablefield_array_rall[]=$tablefield_array_r;
                        }
                        //$filename = 'test.txt';
                        //$handle = @fopen($filename, 'a');

                        while ($arraye = fgetcsv ($file, $sizefile, $_REQUEST['input_terminatedby'],$_REQUEST['input_enclosedby'])) {
                         $key++;
                         //print_R($arraye);
						 $endtime123 = utime();
						 $run = $endtime123 - $starttime123;
                         if ($run>20){//$coot>20
                           $handle123 = fopen($config['path']."temp/lastimport.txt", 'w');
                           $key--;
                           //$count++;
                           fwrite($handle123, $key."|".$count."|".$count1."|".$_REQUEST['input_terminatedby']."|".$_REQUEST['input_enclosedby']);
                           fclose($handle123);
                           //file_put_contents("lastimport.txt",$coot."|".$count."|".$count1);
                           $_REQUEST[repeat]++;
                           $var[error] = $count.$lang['tpl_auto_Ends_Import_imported'];
                           $var[error] .= $count1.$lang['tpl_auto_Ends_Import_updated'];
                           echo '<HTML> <HEAD>
<META HTTP-EQUIV="Refresh" CONTENT="5; URL=index.php?p=import&o=import1&repeat='.$_REQUEST[repeat].'">
</HEAD> <BODY>  <H1><a href="index.php?p=import&o=import1&repeat=1">Please wait 5 seconds<BR/>This is the redirect number <font color=red>'.$_REQUEST[repeat].'</font><br/><br/>'.$var[error].'<br/><br/>
<Br/></a></H1> </BODY> </HTML>
';//If the browser not redirect, click here to redirect
                           //header("Location: index.php?p=import&o=import1&repeat=1");
                           die(0);
                           exit(0);
                         }
                         if ((!$_REQUEST['input_ignorefirst'] or $key>0) and $key>$lastid){
                     	
						   foreach ($arraye as $ak=>$av){
						   	$arraye[$ak]=trim($av);
						   	//$arraye[$ak]=str_replace(array("\r\n","\n"),array("\015\012","\012"),$arraye[$ak]);
						   }
                            //$arraye=explode($_REQUEST['input_terminatedby'],$val);
                            //$arraye
                           // print_r($arraye);
                           //$arraye[$config['import_relation']['stock']];

                           $user_profile = $Global_Class -> getprofile(  $arraye[$config['import_relation']['cars__stock']], 'cars', 'stock' );

                           $sql="SHOW FIELDS FROM `{$config['table_prefix']}cars` ";
                           $result = $db -> query($sql);

                           $sql_input="";
                           $sql_input_val="";
                           foreach  ($tablefield_array_rall as $tablefield_array_r){
                                     if ($tablefield_array_r['Field']!='id'){
                                     //echo "\$config['import_relation']['".$tablefield_array_r['Field']."']=1;\n";

                         			 if ($config['import_relation']['cars__'.$tablefield_array_r['Field']]==-1){
                         			 	$arraye[$config['import_relation']['cars__'.$tablefield_array_r['Field']]]=$config['import_relation_defaultvalue']['cars__'.$tablefield_array_r['Field']];
                         			 }
                                     $valoare=addslashes($arraye[$config['import_relation']['cars__'.$tablefield_array_r['Field']]]);
                                     if ($tablefield_array_r['Field']=='date_add'){
                                         if (!$user_profile){
                                           $valoare=date("Y-m-d");
                                         }else{
                                           $valoare=$user_profile['date_add'];
                                         }
                                     }
                                     if ($tablefield_array_r['Field']=='date_modify'){
                                           $valoare=date("Y-m-d");
                                     }
                                     if ($tablefield_array_r['Field']=='delay'){
                                           $valoare=$config['delay_How_many_days_this_object_will_be_active'];
                                     }                                     
                                     if ($tablefield_array_r['Field']=='date_delay'){
                                           $valoare=date("Y-m-d");
                                     }                                       

                                     if (in_array($tablefield_array_r['Field'],$config['admin_section']['cars']['dropdown_fields'] )){
                                     	   $arraye[$config['import_relation']['cars__'.$tablefield_array_r['Field']]]=strval($arraye[$config['import_relation']['cars__'.$tablefield_array_r['Field']]]);
                                           $category_profile = $Global_Class -> getprofile(  $arraye[$config['import_relation']['cars__'.$tablefield_array_r['Field']]], $tablefield_array_r['Field'], 'name' );
                                           /*echo "<pre>";
                                           print_r($category_profile);
                                           echo "</pre>";
                                           */
                                           if ($category_profile){
                                            $valoare = $category_profile[id];
                                           }else{
                                           	 if ($tablefield_array_r['Field']=='model'){
                                           	 	$sqlmodel=" `makeid`, ";
                                           	 	$sqlmodel1=" '$valoaremake', ";
                                           	 }else{
                                           	 	$sqlmodel1=$sqlmodel="";
                                           	 }
                                             $sql1 = "INSERT INTO `{$config['table_prefix']}".$tablefield_array_r['Field']."` "
                                                      ." ( `id` ,{$sqlmodel} `name` )"
                                                      ." VALUES ( "
                                                      ." '',{$sqlmodel1} '".$arraye[$config['import_relation']['cars__'.$tablefield_array_r['Field']]]."' );";
                                             $result1 = $db -> query($sql1);
                                             $valoare=mysql_insert_id();
                                           }
                                           $valoaresave='valoare'.$tablefield_array_r['Field'];
                                           $$valoaresave=$valoare;
                                     }




                                     if (!$user_profile){
                                     $sql_input.=" , `".$tablefield_array_r['Field']."` ";
                                     $sql_input_val.=" , '".$valoare."' ";
                                     }else{
                                     $sql_input.=" , `".$tablefield_array_r['Field']."` = ";
                                     $sql_input.=" '".$valoare."' ";
                                     }
                                     @mysql_free_result($result1);
                                     }

                           }
                           if (!$user_profile){
                           $sql = "INSERT INTO `{$config['table_prefix']}cars` "
                                    ." ( `id` $sql_input )"
                                    ." VALUES ( '' "
                                    ." $sql_input_val );";
                           $result = $db -> query($sql);
                           $user_profile[id]=mysql_insert_id();
                           $count++;
                           }else{
                           $sql = "UPDATE `{$config['table_prefix']}cars` SET `id`='{$user_profile[id]}' "
                                    ." $sql_input where `id`='{$user_profile[id]}' limit 1";
                           $result = $db -> query($sql);
                           $count1++;
                           }
                           @mysql_free_result($result);
                           
					      $sql = "SELECT * FROM `{$config[table_prefix]}gallery` where carsid='{$user_profile[id]}' ";
					      $result2 = $db -> query($sql);
					      $num_rows_gallery2 = mysql_num_rows($result2);
					      @mysql_free_result($result);
					
					
					      $countimage = 0;
                          					
					      if ($num_rows_gallery2 == 0){ 

					      	  $countimage=1;
	                  
			                   foreach  ($config['import_relationgallery'] as $kkkf=>$vvvf){
			                   		if ($vvvf==-1) $arraye[$vvvf]=$config['import_relationgallery_defaultvalue'][$kkkf];
			                   		if ($countimage<100 and  $arraye[$vvvf]!='') {
			                        	$imagine = $Image_Class1->resizer_main($arraye[$config['import_relationgallery'][$kkkf]],$IMG_HEIGHT,$IMG_WIDTH,$user_profile[id]);
			                        	$imagine1 = $Image_Class1->resizer_main($arraye[$config['import_relationgallery'][$kkkf]],$IMG_HEIGHT_BIG,$IMG_WIDTH_BIG,$user_profile[id]);
				                        if ($imagine and $imagine1){
			                        	$sql = "insert into `{$config[table_prefix]}gallery` VALUES ('','{$user_profile[id]}','$imagine1','$imagine','','','','','$countimage');";
				                        $result1 = $db -> query($sql,__FILE__,__LINE__);	                        	
				                        }
				                        
			                        	$countimage++;
			                   		}
		                      }
					      	           
					         	
					      }
					         
		                  
	                                                 
                          $sql = "delete from `{$config[table_prefix]}carsfeatures` where `carsid`='{$user_profile[id]}'";
                          $result_ = $db -> query($sql,__FILE__,__LINE__);
		                  foreach  ($config['import_relationfeatures'] as $kkkf=>$vvvf){
		                  	    if ($vvvf==-1) $arraye[$vvvf]=$config['import_relationfeatures_defaultvalue'][$kkkf];
		                        if ($arraye[$vvvf]>0) {
		                        	   $kkkf1=str_replace("features__","",$kkkf);
		                               $sql = "insert into `{$config[table_prefix]}carsfeatures` VALUES ('','{$user_profile[id]}','$kkkf1');";
		                               $result1 = $db -> query($sql,__FILE__,__LINE__);
		
		                        }
		                  }
                                             
                          //$coot++;
                          @mysql_free_result($result);                           
$somecontent = $key."\n";
//@fwrite($handle, $somecontent);

                         }
                        }

                        //fclose($handle);
                        $var[error] = $count.$lang['tpl_auto_Ends_Import_imported'];
                        $var[error] .= $count1.$lang['tpl_auto_Ends_Import_updated'];
                        //$outputtoscreen .= $tpl -> replace( $var, "import.html" );
						$var_header[banner]=$var[error];
		                $outputtoscreen .= $tpl -> replace( $var_header, "global_banner.html" );	                        
                        updatejavascript($up_);
                        }
                        break;
                        }
//import ends
			
//export start
                case "export":
                        if ( $right_cookie['export'] == 0 )
                        {
                                $outputtoscreen .= $lang["error_permission"];
                                break;
                        }else{
                        $var[p]="export";
                        $var[o]="export1";
                        if ($_REQUEST[o]=='export1' and $_REQUEST['input_terminatedby']==''){
                        	$var['error']=$lang['tpl_auto_Export_error'];
                        	$_REQUEST[o]='';
                        }
                        if ($_REQUEST[o]!='export1'){
					        foreach ($config['admin_section']['cars']['dropdown_fields'] as $key1=>$val1){
					
					           $var[$val1] = $Global_Class -> getdropdown( $_REQUEST['input_'.$val1], "$val1", "name{$language_set}", "id", "name{$language_set}",0 );
					
					
					          if ($val1=='model'){
					                  $var["model"] = $Global_Class -> getdropdown( $_REQUEST['input_model'], "model", "name{$language_set}", "id", "name{$language_set}",0, " and makeid='{$_REQUEST[input_make]}' " );
					          } else{

					
					          }
					        }
                      		$var[admin] = $Global_Class -> getdropdown( $_REQUEST['input_admin'], "admin", "username", "id", "username",0 );
                      		$aa=explode("__||__",$_COOKIE['autolisting_export']);
                      		if (isset($_COOKIE['autolisting_export'])){
						    	$var['terminatedby']=stripslashes($aa[0]);
						    	$var['filename']=$aa[1];
						    	$var['enclosedby']=$aa[2];
						    	$var['putfieldsnames']=($aa[3]==1)?' checked':"";
						    }else{
						    	$var['terminatedby']=";";
						    	$var['filename']="";
						    	$var['enclosedby']='"';
						    	$var['putfieldsnames']=' checked';
						    }					    
						    if ($var['enclosedby']=='' or $var['enclosedby']=='\"') $var['enclosedby']='"';
						    if ($var['enclosedby']=='"') $var['enclosedby']=htmlspecialchars($var['enclosedby']);
                        	$outputtoscreen .= $tpl -> replace( $var, "export.html" );
                        	break;
                        }else{
                        	$_REQUEST[input_enclosedby]=stripslashes($_REQUEST[input_enclosedby]);
                        	$_REQUEST[input_terminatedby]=stripslashes($_REQUEST[input_terminatedby]);
                        	if ($_REQUEST['input_enclosedby']=='&quot;') $_REQUEST['input_enclosedby']='"';
                        	setcookie ( "autolisting_export", $_REQUEST['input_terminatedby']."__||__".$_REQUEST[input_filename]."__||__".$_REQUEST[input_enclosedby]."__||__".$_REQUEST[input_putfieldsnames], time() + 3600*24*360 );
                        
//start first name


							if ($_REQUEST[input_putfieldsnames]){
	                    		$tablefield_array_rall=array();
	                    		$tablefield_array_rallname=array();								
		                        $sql="SHOW FIELDS FROM `{$config['table_prefix']}cars` ";
		                        $result = $db -> query($sql);
		                        while ($tablefield_array_r = mysql_fetch_array($result)){
		                               $tablefield_array_rallname["cars__".$tablefield_array_r['Field']]=$lang["tpl_auto_cars"]." ".$lang['tabel_cars'][$tablefield_array_r['Field']];
		                        }                         

								$sql = "SELECT {$config[table_prefix]}features.* FROM `{$config[table_prefix]}features` where 1 order by {$config[table_prefix]}features.name{$language_set}";
							    $result = $db -> query( $sql );
							    $num_rows = mysql_num_rows( $result );
							    $contor=0;
							    if ( $num_rows > 0 ) {
							      while ( $var_features = mysql_fetch_assoc( $result ) ) {
							           $tablefield_array_rallname["features__".$var_features[id]]=$lang["tpl_auto_features"]." ".$var_features["name$language_set"];
							      } // while
							      @mysql_free_result($result);
							    }
						    
							    for($i=1;$i<=$config['number_pictures_import'];$i++){
							           $tablefield_array_rallname["gallery__".$i]=$lang["tpl_auto_gallery"]." #".$i;
							    }

							}
//ends
                        	
	                        $sql="SELECT * FROM `{$config['table_prefix']}importsettings` order by `relation` ";
	                        $result = $db -> query($sql);
	                        $num_rows = mysql_num_rows( $result );
	                        $arrayallresults=$arrayallresultskey=array();
	                        $ct=0;	
	                        $max__=0;	                        
	                        if ( $num_rows > 0 ) {
	                         while ($userprofiles = mysql_fetch_array($result)){
	
	                         	$userprofiles[relation]=($userprofiles[relation]==999999)?-1:$userprofiles[relation]-1;
	                         	if ($userprofiles[relation]>$max__) $max__=$userprofiles[relation];
	                         	
	                         	$config['import_relation'][$userprofiles[field]]=$userprofiles[relation];
	                         	$config['import_relation_defaultvalue'][$userprofiles[field]]=$userprofiles[defaultvalue];
	                         	
	                         	if (eregi("features__",$userprofiles[field])){
	                         	$config['import_relationfeatures'][$userprofiles[field]]=$userprofiles[relation];
	                         	$config['import_relationfeatures_defaultvalue'][$userprofiles[field]]=$userprofiles[defaultvalue];
	                         	}
	                         	if (eregi("gallery__",$userprofiles[field])){
	                         	$config['import_relationgallery'][$userprofiles[field]]=$userprofiles[relation];
	                         	$config['import_relationgallery_defaultvalue'][$userprofiles[field]]=$userprofiles[defaultvalue];
	                         	}                         	
	                         }
	                         @mysql_free_result($result);
	                		}
	                		
	         				if ($_REQUEST[input_admin] != "" and $_REQUEST[input_admin] != "..."){
	             				$sql_cond .= " and admin = '{$_REQUEST[input_admin]}' ";
	             			} 
	         				if ($_REQUEST[input_category] != "" and $_REQUEST[input_category] != "..."){
	             				$sql_cond .= " and category = '{$_REQUEST[input_category]}' ";
	             			} 
	         				if ($_REQUEST[input_make] != "" and $_REQUEST[input_make] != "..."){
	             				$sql_cond .= " and make = '{$_REQUEST[input_make]}' ";
	             			}  
	             			
	             			$arrayexport=array();        
	
	
					        $sql = "SELECT * FROM `{$config[table_prefix]}cars` WHERE 1 $sql_cond";
					        $result = $db -> query($sql);
					        $num_rows = mysql_num_rows($result);
					        $contor = 0;
			        
					        if ($num_rows > 0){
					             while ($user = mysql_fetch_assoc($result)){    
	
						             		foreach  ($user as $kkcar=>$vvcars){
		                                     if ($kkcar!='id'){
		                                     //echo "\$config['import_relation']['".$tablefield_array_r['Field']."']=1;\n";
		
		                         			 if ($config['import_relation']['cars__'.$kkcar]!=-1 and $config['import_relation']['cars__'.$kkcar]>-1){
		                         			 	$value=addslashes($vvcars);

			                                    if (in_array($kkcar,$config['admin_section']['cars']['dropdown_fields'] )){
			                                        $category_profile = $Global_Class -> getprofile(  $vvcars, $kkcar, 'id' );
			                                        $value = $category_profile[name];
			                                    }
		                         			 	if ($config['import_relation_defaultvalue']['cars__'.$kkcar]!='') {
		                         			 		$value=$config['import_relation_defaultvalue']['cars__'.$kkcar];
		                         			 	}			                                    
			                                    $arrayexport[$config['import_relation']['cars__'.$kkcar]]=$value;	
			                                    $arrayexporttitle[$config['import_relation']['cars__'.$kkcar]]='cars__'.$kkcar;	
		                         			 }
		                         			 
		 
		                                     }
		
		                           }

					                 $sql = "SELECT * FROM `{$config[table_prefix]}carsfeatures` WHERE carsid='{$user[id]}' ";
							         $resultfea = $db -> query($sql);
							         $num_rowsfea = mysql_num_rows($resultfea);
							         $userfeanew=array();
							         if ($num_rowsfea > 0){
							             while ($userfea = mysql_fetch_assoc($resultfea)){             			             		
							                	$userfeanew[$userfea[featuresid]]=1;
							             } // while
							         }
							         @mysql_free_result($resultfea);
							         //print_r($userfeanew);
							         //exit;
					                  foreach  ($config['import_relationfeatures'] as $kkkf=>$vvvf){
					                  	    if ($vvvf!=-1) {
					                  	    	$kkkf11=str_replace("features__","",$kkkf);
					                  	    	$value=$config[explort_features_true];
					                  	    	$cond1=0;
					                  	    	if ($config['import_relationfeatures_defaultvalue'][$kkkf]!='') {
					                  	    		$value=$config['import_relationfeatures_defaultvalue'][$kkkf];
					                  	    		$cond1=1;
					                  	    	}
					                  	    	if ($userfeanew[$kkkf11] or $cond1) {
					                        	   $arrayexport[$vvvf]=$value;				
					                        	}else{
					                        	   $arrayexport[$vvvf]=$config[explort_features_false];
					                        	}
					                        	$arrayexporttitle[$vvvf]=$kkkf;
					                  	    }				                        
					                  }	
					                  
					                 $sql = "SELECT * FROM `{$config[table_prefix]}gallery` WHERE carsid='{$user[id]}' order by `order`";
							         $resultfea = $db -> query($sql);
							         $num_rowsfea = mysql_num_rows($resultfea);
							         $userfeagal=array();
							         $ctgal=0;
							         if ($num_rowsfea > 0){
							             while ($userfea = mysql_fetch_assoc($resultfea)){             			             		
							             		$ctgal++;
							                	$userfeagal['gallery__'.$ctgal]=$config['url_path']."temp/".$userfea['picture'];
							             } // while
							             @mysql_free_result($resultfea);
							         }
							         
					                  foreach  ($config['import_relationgallery'] as $kkkf=>$vvvf){
					                  	    if ($vvvf!=-1) {
					                  	    	$value=$userfeagal[$kkkf];
					                  	    	if ($config['import_relationgallery_defaultvalue'][$kkkf]!='') {
					                  	    		$value=$config['import_relationgallery_defaultvalue'][$kkkf];
					                  	    	}
					                  	    	$arrayexport[$vvvf]=$value;	
					                  	    	$arrayexporttitle[$vvvf]=$kkkf;
					                  	    }				                        
					                  }	
					                  for($i=1;$i<=$max__;$i++){
					                  	$valoarexp=trim($arrayexport[$i-1]);
					                  	//$valoarexp=str_replace(array("\r\n","\n"),array("\\r\\n","\\n"),$valoarexp);
					                  	
					                  	$sendback .= $_REQUEST[input_enclosedby].$valoarexp.$_REQUEST[input_enclosedby]."".$_REQUEST['input_terminatedby'];
					                  }
									  $sendback = substr($sendback, 0, -1); //chop last ,
									  $sendback .= "\n";					                  
					             } // while
					             @mysql_free_result($result);
					             
					        }
							 $filename=($_REQUEST[input_filename]=='')?"export_".date('Y-m-j_h-i-s').".csv":$_REQUEST[input_filename];
							 
					         if ($_REQUEST[input_putfieldsnames]){
					         		  $sendback1="";
					                  for($i=1;$i<=$max__;$i++){
					                  	$valoarexp=trim($tablefield_array_rallname[$arrayexporttitle[$i-1]]);
					                  	//$valoarexp=str_replace(array("\r\n","\n"),array("\\r\\n","\\n"),$valoarexp);
					                  	
					                  	$sendback1 .= $_REQUEST[input_enclosedby].$valoarexp.$_REQUEST[input_enclosedby]."".$_REQUEST['input_terminatedby'];
					                  }
									  $sendback1 = substr($sendback1, 0, -1); //chop last ,
									  $sendback1 .= "\n";	
									  $sendback=$sendback1.$sendback;
					         }							 
							 
							 header("Content-type: application/ofx");
							 header("Content-Disposition: attachment; filename=".$filename);                     
							 echo $sendback;
							 exit(0);
                      		 break;
                        }
                        }
//export ends	
//start stats
				case "stats":
					$father_template = $Global_Class->getprofile( 1 , 'statssettings' ,"id" ) ;
					$settingsupdate_template = $Global_Class->getprofile( 1 , 'statssettingsupdate' ,"id" ) ;
					
					foreach ($father_template as $key=>$val){
						$config[]=$val;
					}
					$config['nrresult']=$father_template['rowsperpage'];
					$config['ignore_domain']=$father_template['ignoredomains'];
					$config['ignoresubdomenins']=$father_template['ignoresubdomenins'];
					$config['ignore_domainnew']=explode(",",$config['ignore_domain']);
					
					if ($config['ignore_domain']!=''){
						foreach ($config['ignore_domainnew'] as $key=>$val){
							$val=str_replace(array("http://www.","http://"),"",$val);
							$val=eregi_replace("^www\.","",$val);
							$config['sql_ignore_domain']	.= " and `fromdomain`!='$val' ";
						}
					}
					
					if ($config['ignoresubdomenins']){
						$config['sql_ignoresubdomenins'] = " and fromdomain NOT LIKE '%{$config['cookie_domain']}' ";
					}		
					
					$p1=$_REQUEST[p1];
			        switch ( $p1 )
			        {		        	
						case "visits":
						    echo $var_header[visits] = $visit_class->visits();
						    exit(0);
						break;
						case "referrers":
						    echo $var_header[referrers] = $visit_class->referrers();			    
						    exit(0);
						break;
						case "searches":
						    echo $var_header[searches] = $visit_class->searches();
						    exit(0);
						break;
						case "system":
						    echo $var_header[system] = $visit_class-> sistem();
						    exit(0);
						break;												
						default:
							if (substr($settingsupdate_template[lastupdate],0,8)!=date("Ymd")){					
									$sql = "UPDATE `{$config['table_settingsupdate']}` SET 	`lastupdate`=NOW()+0 WHERE `id`=1 LIMIT 1";   
									$result = $db -> query( $sql );  
									$sqlupdateall=1;
							}else{
									$sqlupdateall=0;
							}
						    $sql_ = "select max( id ) as maxx from `{$config['table_visits']}` where date_format(ctime,'%Y%m%d')<'".date("Ymd")."'  ";
						    $result_ = $db -> query( $sql_ );
						    list($row['maxx']) = $db -> fetch_row($result_);	
						    $db -> free_result($result_);		
						    $config['maxx_id']=$row['maxx'];
						    			    
						    $var_header[visits] = $visit_class->visits($sqlupdateall);
						    $var_header[referrers] = $visit_class->referrers($sqlupdateall);
						    $var_header[searches] = $visit_class->searches($sqlupdateall);
						    $var_header[system] = $visit_class->sistem($sqlupdateall);
						    			    
						    //$var_header["signsort_".$_COOKIE['accstats_orderby']]= "<font class=\"sign\">&uarr;</font>";
						    //$var_header["signsort1_".$_COOKIE['accstats_orderby1']]= "<font class=\"sign\">&uarr;</font>";
						    
						    //print_r($settingsupdate_template);
						    if ($config[maxx_id]!=''){
						    $sql = "DELETE FROM `{$config['table_visits']}` where `id`<='{$config[maxx_id]}'";    
						    $result = $db -> query( $sql );  
						    }
						    
							$sql = "UPDATE `{$config['table_settingsupdate']}` SET `data`='".addslashes(serialize($settingsupdate_template['data']))."',
							`datasearches`='".addslashes(serialize($settingsupdate_template['datasearches']))."',
							`datareferrers`='".addslashes(serialize($settingsupdate_template['datareferrers']))."',
							`sistem`='".addslashes(serialize($settingsupdate_template['sistem']))."',
							`lastupdate`=NOW()+0
							WHERE `id`=1 LIMIT 1"; 
							
						    $result = $db -> query( $sql );  			    
						    
						    $outputstats .= $tpl -> replace( $var_header, "admin_visit.html" );
						break;
			
			        }
					if ($_REQUEST[p]==''){
				     $var_header['sistem'] = $tpl -> replace( $var_header, "sistem.html" );
					}
			        $output_header = $tpl -> replace( $var_header, "stats_header.html" ); //read header
			        //$output .= $tpl -> replace( $var, "admin_jos.html" );
			        
			        $output_footer .= $tpl -> replace( $var, "stats_footer.html" ); //read header				
			        
			        $outputtoscreen.=$output_header.$outputstats.$output_footer;
				break;
//ends stats				
                default:
                        if ($_REQUEST['p']!='') {
                        $default_tabel = $_REQUEST['p'];
                        if (!in_array($_REQUEST['p'],$config["config_options_in_admin"])){
                          $default_tabel=$config["config_options_in_admin"][0];
                          break;
                        }
                        if ($right_cookie['view_all_cars']) {
	                        if ($_REQUEST[o1]=='reset'){
	                        	$sql="UPDATE `{$config[table_prefix]}admin` SET `date_delay`=NOW(),`daystoexpire` = `delay`,`daysactive`=0,`active`=1,`emailrenewsent`=0 where `id` = '{$_REQUEST[id]}' limit 1";
	                            $result = $db -> query($sql,__FILE__,__LINE__);
	                        }
                        }
                        if ($default_tabel=='') $default_tabel='cars';

                        $default_id = array("id");
                        $o = $_REQUEST['o'];
                        $id_ = "id";
                        if (!in_array($p,$config['admin_not_delete_need'] ) ) {
                        }else{
                        	if (!in_array($p,$config['admin_special_menu'] ) ) {
		                         if ($o==''){
		                          $o='see';
		                          $_REQUEST['id']=1;
		                         }
                        	}
                        }
                        
                        if (in_array($p,$config['admin_special_menu'] ) ) {
                        	
                        }
                        if (in_array($p,$config['admin_not_delete_need'])){
                                if ($right_cookie[$p]) {
                                        $right_cookie[$p.'_view']=1;
                                        $right_cookie[$p.'_edit']=1;
                                        $right_cookie[$p.'_delete']=1;
                                        $right_cookie[$p.'_add']=1;
                                }
                                if ($_REQUEST['o']=="add" or $_REQUEST['o']=="add1" OR $_REQUEST['o']=="delete"  or $_REQUEST['o']=="delete1" OR $_REQUEST['o']=="search") {
                                    $_REQUEST['o']="view";
                                }
                                if ($Global_Class -> getnumrows("1", $p, "id") < 1) {
                                    if ($_REQUEST['o']!="add1") {
                                       $_REQUEST['o']="add" ;
                                    }
                                }
                        }
                        if (intval($_REQUEST['id'])>0){
                        $user_profile = $Global_Class -> getprofile(  $_REQUEST['id'], $default_tabel, $id_ );
                        }
						if ($user_profile and in_array($p,$config['admin_special_menu'] ) ) {
                        	if (!is_array($lang['tabel_'.$p][$_REQUEST['id']])) $lang['tabel_'.$p]['1']=array();
                        	foreach ($lang['tabel_'.$p][$_REQUEST['id']] as $ktemp1=>$vtemp1){
                        		$lang['tabel_'.$p][$ktemp1]=$vtemp1;
                        	}
                        	
                        }
                        $varchar_fields = $config['admin_section'][$default_tabel]['varchar_fields'];
                        $multiplefields = $config['admin_section'][$default_tabel]['multiplefields'];

                        $text_fields = $config['admin_section'][$default_tabel]['text_fields'];
                        $multiplefields_text = $config['admin_section'][$default_tabel]['multiplefields_text'];
                        $text_fields_wysiwyg = $config['admin_section'][$default_tabel]['text_fields_wysiwyg'];
                        $file_fields = $config['admin_section'][$default_tabel]['file_fields'];
                        $dropdown_fields = $config['admin_section'][$default_tabel]['dropdown_fields'];
                        foreach ($varchar_fields as $key1=>$val1){
                               if ($config['varchar_special_maxlength'][$default_tabel][$val1]!=''){
                                  $config['varchar_special_maxlength'][$val1]=$config['varchar_special_maxlength'][$default_tabel][$val1];
                               }
                               if ($config['varchar_special_maxlength_goodchars'][$default_tabel][$val1]!=''){
                                  $config['varchar_special_maxlength_goodchars'][$val1]=$config['varchar_special_maxlength_goodchars'][$default_tabel][$val1];
                               }
                        }
                        $count1=1;
                        if ($config['admin_section'][$default_tabel]['unic_name']){
                         $inivaluetemp=$_POST["input_".$varchar_fields[0]];
                         if ($o=="add1" OR $o=="edit1") {
                         if ($o=="add1"){
                          $value_toexplode_array=array();
                          if (eregi(",",$_POST["input_".$varchar_fields[0]]) and count($varchar_fields)==1){
                                $value_toexplode=explode(",",$_POST["input_".$varchar_fields[0]]);
                                foreach ($value_toexplode as $keytemp=>$valtemp){
                                 $valtemp = trim($valtemp);
                                 if ($valtemp!='' and !in_array($valtemp,$value_toexplode_array)){
                                  $value_toexplode_array[]=$valtemp;
                                 }
                                }
                                $inivaluetemp=$_POST["input_".$varchar_fields[0]]=implode(",",$value_toexplode_array);
                          }else{
                               $value_toexplode_array=array($_POST["input_".$varchar_fields[0]]);
                          }
                         }else{
                               $value_toexplode_array=array($_POST["input_".$varchar_fields[0]]);
                         }
                         foreach ($value_toexplode_array as $keyexplode=>$valexplode){
                            if (count($varchar_fields)==1){
                             $_POST["input_".$varchar_fields[0]]= $valexplode;
                            }

                                    if ($config['admin_section'][$default_tabel]['unic_name_field_relation_exist']){
                                     $sqltemp = " and `".$config['admin_section'][$default_tabel]['unic_name_field_relation']."`='".$_POST['input_'.$config['admin_section'][$default_tabel]['unic_name_field_relation']]."'";
                                    }else{
                                     $sqltemp = "";
                                    }
                                    $second_profile = $Global_Class -> getprofile( $_POST['input_'.$config['admin_section'][$default_tabel]['unic_name_field']], $default_tabel, $config['admin_section'][$default_tabel]['unic_name_field'],$sqltemp );
                                    if ($o=="edit1") {
                                        $cond1 = ($second_profile['id'] != $_REQUEST['id']);
                                        $cond2=($_POST['input_'.$config['admin_section'][$default_tabel]['unic_name_field_relation']]==$second_profile[$config['admin_section'][$default_tabel]['unic_name_field_relation']]);
                                    }else{
                                        $cond1=1;
                                        $cond2=($_POST['input_'.$config['admin_section'][$default_tabel]['unic_name_field_relation']]==$second_profile[$config['admin_section'][$default_tabel]['unic_name_field_relation']]);
                                    }
                                    if ( $second_profile && ( $cond1 ) &&  $cond2)
                                    {
                                            $outputtoscreen_add[1] .= $lang['error_change1']['name_exist'].$config['use_point_after_error'].$_POST['input_'.$config['admin_section'][$default_tabel]['unic_name_field']].$config['use_br_after_error'];
                                    }
                                    if ($outputtoscreen_add[1]!=""){
                                            $outputtoscreen_add[0]==false;
                                    }
                            }
                         }
                         $_POST["input_".$varchar_fields[0]] = $inivaluetemp;
                        }
                        if ($config['admin_section'][$default_tabel]['secondunic_name_field']!=''){

                                    $second_profile = $Global_Class -> getprofile( $_POST['input_'.$config['admin_section'][$default_tabel]['secondunic_name_field']], $default_tabel, $config['admin_section'][$default_tabel]['secondunic_name_field'],$sqltemp );
                                    if ($o=="edit1") {
                                        $cond1 = ($second_profile['id'] != $_REQUEST['id']);
                                        $cond2=($_POST['input_'.$config['admin_section'][$default_tabel]['secondunic_name_field']]==$second_profile[$config['admin_section'][$default_tabel]['secondunic_name_field']]);
                                    }else{
                                        $cond1=1;
                                        $cond2=($_POST['input_'.$config['admin_section'][$default_tabel]['secondunic_name_field']]==$second_profile[$config['admin_section'][$default_tabel]['secondunic_name_field']]);
                                    }
                                    if ( $second_profile && ( $cond1 ) &&  $cond2)
                                    {
                                        $outputtoscreen_add[1] .= $lang['error_change1']['name_exist'].$config['use_point_after_error'].$_POST['input_'.$config['admin_section'][$default_tabel]['secondunic_name_field']].$config['use_br_after_error'];
                                    }
                                    if ($outputtoscreen_add[1]!=""){
                                        $outputtoscreen_add[0]==false;
                                    }



                        }
                        foreach ($config['admin_section'][$default_tabel]['dropdown_fields_fromlanguage'] as $key1=>$val1){
                          $dropdown_fields[]=$val1;
                          $temp="valoare_".$count1;

                          $$temp=-1;
                          if ($o=="add") {
                                  $$temp = -1;
                          }elseif ($o=="edit" or $o=='see') {

                                  $$temp = $user_profile[$val1];
                          }else{

                                  $$temp = $_POST['input_'.$val1];
                          }
                          $valoaretemp[$val1]=$$temp;
                          if ($val1==$config['admin_section'][$default_tabel]['field_activate']){
                           $dropdownval[$val1] = yes_or_no($$temp);
                          }else{
                           $dropdownval[$val1] = $Global_Class -> getdropdown_array( $$temp, $lang[$val1] );
                          }
                          $count1++;
                        }
                                                if (!is_array($config['admin_section'][$default_tabel]['dropdown_fields_language']))$config['admin_section'][$default_tabel]['dropdown_fields_language']=array();
                                                if (!is_array($config['admin_section'][$default_tabel]['dropdown_fields_language_notused']))$config['admin_section'][$default_tabel]['dropdown_fields_language_notused']=array();

                        foreach ($config['admin_section'][$default_tabel]['dropdown_fields_language'] as $key1=>$val1){
                          $dropdown_fields[]=$val1;
                          $temp="valoare_".$count1;

                          $$temp=-1;
                          if ($o=="add") {
                                  $$temp = -1;
                          }elseif ($o=="edit" or $o=='see') {

                                  $$temp = $user_profile[$val1];
                          }else{

                                  $$temp = $_POST['input_'.$val1];
                          }
                          $valoaretemp[$val1]=$$temp;
                          $valtemplang=(in_array($val1,$config['admin_section'][$default_tabel]['dropdown_fields_language_notused']))?0:1;
                          $dropdownval[$val1] = $Global_Class -> getdropdownlanguage( $$temp,$valtemplang );
                          $count1++;
                        }
                        
 						if ($default_tabel=='paymenthistory'){
                      	 	if (!$right_cookie['view_all_cars']) {
                            $sql_default_global = " and {$config['table_prefix']}{$default_tabel}.adminid = '".$_COOKIE['id_cookie']."' ";
                         	}
                      	}                        

                        if ($config['admin_section'][$default_tabel]['admin_dropdown']) {
                         if (!$right_cookie['view_all_'.$config['admin_section'][$default_tabel]['admin_dropdown_field']]) {
                            $sql_default_global = " and {$config['table_prefix']}{$default_tabel}.".$config['admin_section'][$default_tabel]['relation_to_admin']." = '".$_COOKIE['id_cookie']."' ";
                         }else{
                            $dropdown_fields[]=$config['admin_section'][$default_tabel]['relation_to_admin'];
                         }
                        }
                        /*
                        foreach ($config['admin_section'][$default_tabel]['dropdown_fields'] as $key1=>$val1){

                          if ($o=="add") {
                                  if ($config['admin_section'][$default_tabel]['exist_limit_number']) {
                                     $admin_profile = $Global_Class -> getprofile(  $_COOKIE['id_cookie'], $config['admin_section'][$default_tabel]['admin_limit_number_table'], "id" );
                                     $nolimit = $Global_Class -> getnumrows($_COOKIE['id_cookie'], $default_tabel, $config['admin_section'][$default_tabel]['relation_to_admin']);
                                     if ($admin_profile[$config['admin_section'][$default_tabel]['admin_limit_number_field']]>0 AND $nolimit>=$admin_profile[$config['admin_section'][$default_tabel]['admin_limit_number_field']]) {
                                        $_REQUEST['o']="view";
                                     }
                                  }
                                  $valoare_1 = -1;
                          }elseif ($o=="edit" or $o=='see') {
                                  $valoare_1 = $user_profile[$val1];
                          }else{
                                  $valoare_1 = $_POST['input_'.$val1];
                          }

                          $dropdownval[$val1] = $Global_Class -> getdropdown( $valoare_1, $config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][0],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][1],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][2],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][3] );

                        }
                        */
                        
                        foreach ($config['admin_section'][$default_tabel]['dropdown_fields'] as $key1=>$val1){

                          if ($o=="add") {
                                  if ($config['admin_section'][$default_tabel]['exist_limit_number']) {
                                     $admin_profile = $Global_Class -> getprofile(  $_COOKIE['id_cookie'], $config['admin_section'][$default_tabel]['admin_limit_number_table'], "id" );
                                     $nolimit = $Global_Class -> getnumrows($_COOKIE['id_cookie'], $default_tabel, $config['admin_section'][$default_tabel]['relation_to_admin']);
                                     if ($admin_profile[$config['admin_section'][$default_tabel]['admin_limit_number_field']]>0 AND $nolimit>=$admin_profile[$config['admin_section'][$default_tabel]['admin_limit_number_field']]) {
                                        $_REQUEST['o']="view";
                                     }
                                  }
                                  $valoare_1 = -1;
                          }elseif ($o=="edit" or $o=='see') {
                                  $valoare_1 = $user_profile[$val1];
                          }else{
                                  $valoare_1 = $_POST['input_'.$val1];
                          }



//start
                          if (!is_array($config['admin_section'][$default_tabel]['onchange'])) $config['admin_section'][$default_tabel]['onchange']=array();
                          if (!is_array($config['admin_section'][$default_tabel]['onchange_sub'])) $config['admin_section'][$default_tabel]['onchange_sub']=array();
                          if (in_array($val1,$config['admin_section'][$default_tabel]['onchange'])){
                                $temp1="val".$val1;
                                $$temp1=$valoare_1;
                                if ($valoare_1==-1){
                                     $user_profile123 = $Global_Class -> getprofilefirst( $config['admin_section'][$default_tabel]['onchange_rel'][$val1], " order by name{$language_set} limit 1" );
                                     $$temp1=$user_profile123[id];
                                }
                          }

                          if (in_array($val1,$config['admin_section'][$default_tabel]['onchange_sub'])){
                                $temp1="val".$val1;
                                $temp2="val".$config['admin_section'][$default_tabel]['onchange_sub_id'][$val1];
                                if ($val1=='city'){
                                 if ($o=="edit" or $o=='see') {
                                  $$temp=$user_profile['city'];

                                 }elseif ($o=="add"){
                                  $$temp=-1;
                                 }
                                }
                                if ($val1=='provinceid'){
                                 if ($o=="edit" or $o=='see') {
                                  $$temp=$user_profile['provinceid'];

                                 }elseif ($o=="add"){
                                  $$temp=-1;
                                 }
                                }
                                $$temp1=$$temp;
                                $dropdownval[$val1] = $Global_Class -> getdropdown( $$temp1, $config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][0],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][1],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][2],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][3] ,0," and ".$config['admin_section'][$default_tabel]['onchange_sub_id'][$val1]."='{$$temp2}' " );
                                $dropdownval_onchange[$config['admin_section'][$default_tabel]['onchange_sub_id'][$val1]]=" onChange=\"changeinput_".$val1."(document.formarticle.input_".$val1.".selectedIndex,document.formarticle.input_".$config['admin_section'][$default_tabel]['onchange_sub_id'][$val1].");\" ";

                                 //$javascript_special[$val1] = $Global_Class -> getjavascriptarray($config['admin_section'][$default_tabel]['onchange_sub_rel'][$val1],"name{$language_set}","id","name{$language_set}",$config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][0],"name{$language_set}","id","name{$language_set}",$config['admin_section'][$default_tabel]['onchange_sub_id'][$val1]);
                                 $javascript_special[$val1] = $Global_Class -> getjavascriptarray("country","name{$language_set}","id","name{$language_set}","state","name{$language_set}","id","name{$language_set}","countryid");
                                 //exit;
                                 //echo $val1;
                                 //echo  $javascript_special[$val1];
                                 //echo "<BR>--<BR>";
                                 
                          } else{

                               $dropdownval[$val1] = $Global_Class -> getdropdown( $valoare_1, $config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][0],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][1],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][2],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][$val1][3] );
                          }
//end

                        }                        

                        foreach ($config['admin_section'][$default_tabel]['copy_from_id_add'] as $key1=>$val1){

                          if ($o=="add1") {
                              $copy_from_id_value[$key1] = date($val1);
                          }

                        }

                        foreach ($config['admin_section'][$default_tabel]['copy_from_id_edit'] as $key1=>$val1){

                          if ($o=="edit1") {
                             $copy_from_id_value[$val1] = $user_profile[$val1];
                          }

                        }

                        $field_activate = $config['admin_section'][$default_tabel]['field_activate']; //for activat
                        $config[config_second_multiple_show]=$config['admin_section'][$default_tabel]['config_second_multiple_show'];
                        $config[config_sold_multiple_show]=$config['admin_section'][$default_tabel]['config_sold_multiple_show'];

                        if ($config['admin_section'][$default_tabel]['exist_onChange']){
                          if ($valoaretemp[$config['admin_section'][$default_tabel]['exist_onChange_field']]==-1){
                               $user_profile = $Global_Class -> getprofilefirst( $config['admin_section'][$default_tabel]['exist_onChange_relation'][0], $config['admin_section'][$default_tabel]['exist_onChange_relation'][1] );
                               $valoaretemp[$config['admin_section'][$default_tabel]['exist_onChange_field']]=$user_profile[$id_];
                          }
                          $dropdownval[$valoaretemp[$config['admin_section'][$default_tabel]['exist_onChange_field']]] = $Global_Class -> getdropdown( $valoaretemp[$config['admin_section'][$default_tabel]['exist_onChange_field']], $config['admin_section'][$default_tabel]['dropdown_fields_relation'][0],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][1],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][2],$config['admin_section'][$default_tabel]['dropdown_fields_relation'][3]  ,0," and ".$config['admin_section'][$default_tabel]['exist_onChange_relation_field']."='".$valoaretemp[$config['admin_section'][$default_tabel]['exist_onChange_field']]."' ");
                          $dropdownval_onchange[$config['admin_section'][$default_tabel]['exist_onChange_secondfield']]=$config['admin_section'][$default_tabel]['dropdownval_onchange'];
                          $javascript_special[$config['admin_section'][$default_tabel]['exist_onChange_field']] = $Global_Class -> getjavascriptarray($config['admin_section'][$default_tabel]['javascript_special'][0],$config['admin_section'][$default_tabel]['javascript_special'][1],$config['admin_section'][$default_tabel]['javascript_special'][3],$config['admin_section'][$default_tabel]['javascript_special'][4],$config['admin_section'][$default_tabel]['javascript_special'][5],$config['admin_section'][$default_tabel]['javascript_special'][6],$config['admin_section'][$default_tabel]['javascript_special'][7],$config['admin_section'][$default_tabel]['javascript_special'][8]);
                        }

                        $radio_fields = $config['admin_section'][$default_tabel]['radio_fields'];
                        $radioval = $config['admin_section'][$default_tabel]['radioval'];

                        $checkbox_fields = $config['admin_section'][$default_tabel]['checkbox_fields'];
                        if ($default_tabel=='rights'){
                                $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
                                $result = $db -> query($sql,__FILE__,__LINE__);
                                while ($tablefield_array_r = mysql_fetch_array($result)){
                                        $key = $tablefield_array_r['Field'];
                                        if ( !in_array( $key, array( "id", "name" ) ) ){
                                              $checkbox_fields[]=$key;
                                        }

                                }
                        }

                        $password_fields = $config['admin_section'][$default_tabel]['password_fields'];

                        $file = $config['admin_section'][$default_tabel]['file']; // for pictures
                        $file_size = $config['admin_section'][$default_tabel]['file_size'];  // for pictures size

                        $relation = $config['admin_section'][$default_tabel]['relation'];

                        foreach ($config['admin_section'][$default_tabel]['relation'] as $key1=>$val1){
                         $relation_table[$val1] = $config['admin_section'][$default_tabel]['relation_table'][$val1];
                        }

                        $copy_from = $config['admin_section'][$default_tabel]['copy_from']; // for big pictures
                        $copy_from_val = $config['admin_section'][$default_tabel]['copy_from_val']; // for big picutres size
                        $require_array = $config['admin_section'][$default_tabel]['require_array']; //require array
                        $password = $config['admin_section'][$default_tabel]['password']; // for md5 fields
                        $copy_from_id = $config['admin_section'][$default_tabel]['copy_from_id'];

                        foreach ($config['admin_section'][$default_tabel]['copy_from_id_default'] as $key1=>$val1){
                         $copy_from_id_value[$key1] = date($val1);
                        }

                        if ($config['admin_section'][$default_tabel]['admin_dropdown']) {
                         if (!$right_cookie['view_all_'.$config['admin_section'][$default_tabel]['admin_dropdown_field']]) {
                             $copy_from_id[]=$config['admin_section'][$default_tabel]['relation_to_admin'];
                             $copy_from_id_value[$config['admin_section'][$default_tabel]['relation_to_admin']]=$_COOKIE['id_cookie'];
                         }
                        }
                        
                        if ($default_tabel=='admin'){
                        	$copy_from_id[]='date_delay';
                        	$copy_from_id_value['date_delay']=date("Y-m-d");
                        }
                                                
                        if (count($config['admin_section'][$default_tabel]['password_fields'])>0){
                                                 if ($o=='add1' or $o=='edit1'){

                                                         if ( ( ( strlen( $_POST['input_'.$config['admin_section'][$default_tabel]['password_fields'][0]] ) < 4 ) || ( strlen( $_POST['input_'.$config['admin_section'][$default_tabel]['password_fields'][0]] ) > 20 ) ) && ($_POST['input_'.$config['admin_section'][$default_tabel]['password_fields'][0]]!='' || $_POST['input_'.$config['admin_section'][$default_tabel]['password_fields'][1]]!='' || $o=='add1' ) )
                                {
                                        $outputtoscreen_add[1] .= $lang['error_change1']['password_short'];
                                }
                                                 }

                         if ( ( $_POST['input_'.$config['admin_section'][$default_tabel]['password_fields'][0]] != $_POST['input_'.$config['admin_section'][$default_tabel]['password_fields'][1]] ) )
                         {
                           $outputtoscreen_add[1] .= $lang['error_change1']['password_not_equal'];
                         }
                         if ($outputtoscreen_add[1]!=""){
                           $outputtoscreen_add[0]==false;
                         }
                        }

                        $email_fields = $config['admin_section'][$default_tabel]['email_fields'];
                        $date_fields = $config['admin_section'][$default_tabel]['date_fields'];

                        $fields_not_show = $config['admin_section'][$default_tabel]['fields_search'];
                        if ($config['admin_section'][$default_tabel]['exist_relationtolevel2']){
                         $tablefield_array_options = $config['admin_section'][$default_tabel]['tablefield_array_options'];
                         foreach ($config['admin_section'][$default_tabel]['tablefield_array_options'] as $key1=>$val1){
                          $tablefield_array_options_val[$key1] = $val1;
                         }
                        }

                        $field_name =$config['admin_section'][$default_tabel]['field_name_for_delete']; //for delete
                        $search_fields = $config['admin_section'][$default_tabel]['fields_view'];

                                                if ($o=="search") {
                                                        foreach ($date_fields as $key=>$val){
                                           $_POST["input_".$val."_day"] = ( $_POST["input_".$val."_day"]<=9 && $_POST["input_".$val."_day"]!='') ? "0".$_POST["input_".$val."_day"]:$_POST["input_".$val."_day"];
                                           $var['tpl_input_day']=( $_POST["input_".$val."_day"] !='') ? "-".$_POST["input_".$val."_day"] : "";
                                           $_POST["input_".$val."_month"] = ($_POST["input_".$val."_month"]<=9 && $_POST["input_".$val."_month"]!='') ? "0".$_POST["input_".$val."_month"] : $_POST["input_".$val."_month"];
                                           if ($_POST["input_".$val."_day"]!='' and $_POST["input_".$val."_month"]==''){
                                                   $_POST["input_".$val."_month"]="__";
                                           }
                                           $var['tpl_input_month']=( $_POST["input_".$val."_month"] !='') ? "-".$_POST["input_".$val."_month"]:"";
                                           $var['tpl_input_year'] = ( $_POST["input_".$val."_year"] !='')? $_POST["input_".$val."_year"]:"";
                                           $_REQUEST["input_".$val]="{$var['tpl_input_year']}{$var['tpl_input_month']}{$var['tpl_input_day']}";
                                                        }
                                                }


                        if (intval($_REQUEST['id'])>0){
                        $user_profile = $Global_Class -> getprofile(  $_REQUEST['id'], $default_tabel, $id_ );
                        }
                        $outputtoscreen .= $Global_Class -> choose_option();
                        if (in_array($default_tabel,$config['admin_section']['cars']['dropdown_fields'] )){
                            if ($o=="add1" or $o=="edit1" or $o=="delete" ) {
                               $up_=0;
                               if ($default_tabel=='make' or $default_tabel=='model')	{
                               	$up_=1;
                               }
                               updatejavascript($up_);
                            }
                        }                        
                        }
                        break;
//finish default


        }
        $outputtoscreen .= $tpl -> replace( $var, "admin_jos.html","",$condtemplates );
}
$var = array();
$outputtoscreen .= $tpl -> replace( $var, "admin_footer.html","",$condtemplates ); //read header

echo $outputtoscreen;
/*
echo "<BR><textarea ROWS=10 COLS=100>$msg_global";
echo $config[sqlsct];
echo "\n";
print_r($config[sqls]);
echo "</TEXTAREA><br>\n";
*/
ob_end_flush();

$endtime123 = utime();
$run = $endtime123 - $starttime123;

//echo "<BR><center>Page loaded in " . substr( $run, 0, 5 ) . " seconds.</center>";

?>