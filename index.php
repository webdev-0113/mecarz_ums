<?php
ob_start();

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
if (in_array("p", $_REQUEST) && $_REQUEST['p']=="image") {
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
$dealer = new DealerClass;

$settings_profile = $Global_Class->getprofile( "1","settings","id" );
switch($_REQUEST['p']){

    case "showbanner":
        echo $banner_class->showbanner();
        exit(0);
        break;
    case "redirect":
        $banner_class->redirect($_REQUEST['idb']);
        exit(0);
        break;

}
list($config['config_auto_bannerscode1'],$config['config_auto_bannerscode2'],$config['config_auto_bannerscode3'],$config['config_auto_bannerscode4'])=$banner_class->banner();
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
//print_R($_GET);
if ($_GET['language_session'] == '' AND $_COOKIE['language_session']<>'') {
    $_SESSION['language_session'] = $_REQUEST['language_session']=$_COOKIE['language_session'];

}else
    if ($_COOKIE['linksave']==$_SERVER['REQUEST_URI']){
        $language_set	= $_COOKIE['language_session'];
        $_SESSION['language_session']=$language_set;
        if ($language_set==0) {
            $language_set = '';
        }
    }elseif ($_GET['language_session'] != '') {
        $_SESSION['language_session'] = $_GET['language_session'];
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
        $link=$_SERVER['REQUEST_URI'];
        if (1) {
            $var_lang['onclicklang']="setCookie('language_session',$key,365);setCookie('linksave','$link',365);window.location=window.location;return false;";
        }
        $settings_profile['languagedropdown1'] .= $config["config_separator"].$tpl->replace($var_lang,"urllanguge.html");

    }
    if ($found==0) $_SESSION['language_session']='';
    $class_ = ($_SESSION['language_session'] == '' ) ? " class=\"selected\"": " class=\"noselected\"";
    $var_lang['key']=0;
    $var_lang['val']=strtolower(substr($settings_profile[language],0,-4));
    $var_lang['class']=strtolower($class_);
    $link=$_SERVER['REQUEST_URI'];
    if (2) {
        $var_lang['onclicklang']="setCookie('language_session',0,365);setCookie('linksave','$link',365);window.location=window.location;return false;";
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
if (file_exists("seo1.php")){
    @include_once("seo1.php");
}
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


switch ( $_REQUEST['pp'] )
{


    case "admin":


        if ($_REQUEST['country']>0){
            $sqlcat=" AND country='".addslashes($_REQUEST['country'])."' ";
        }
        if ($_REQUEST['city']>0){
            $sqlcat.=" AND city='".addslashes($_REQUEST['city'])."' ";
        }
        if ($_REQUEST['state']>0){
            $sqlcat.=" AND state='".addslashes($_REQUEST['state'])."' ";
        }

        $listin_array_id_gallery = $Global_Class->getarrayid('cars','admin',$sqlcat.' AND `active`>0 GROUP BY `admin`');

        if (!is_array($listin_array_id_gallery)) $listin_array_id_gallery=array();
        $sql_cond11 = " AND ( FIND_IN_SET( id, '".implode(",",$listin_array_id_gallery)."' ) > 0 ) ";



        $out = $Global_Class->getdropdown( $_SESSION['admin'], "admin", "name", "id", "name",0," AND `showdropdown`='1' ".$sql_cond11 );

        //$out=explode("</option>",$out);
        //$out[0]=preg_replace("/<option value=/i","<option selected value=",$out[0]);
        //$out=implode("</option>",$out);

        echo <<<END
<select name="admin" style="width:165px">
<option value=''>{$lang['tpl_auto_All']}</option>
{$out}
</select>
END;
        exit(0);
        break;

    case "state1":
        $sqlcat="and `countryid` like '{$_REQUEST['first']}'";
        $out = $Global_Class->getdropdown( '', "state", "name{$language_set}", "id", "name{$language_set}",0, $sqlcat );
        //$out = $Global_Class->getdropdown( '', "province", "name", "province", "province",0, $sqlcat );
        //$out=explode("</option>",$out);
        //$out[0]=preg_replace("/<option value=/i","<option selected value=",$out[0]);
        //$out=implode("</option>",$out);

        echo <<<END
<select name="state" class="dropdown" style="width:165px" onchange="funcget1cc('city',document.frm.state.value,document.frm.state.value,'cityid',document.getElementById('cityid'),'{$lang['tpl_auto_Loading']}');return false;">
<option value=''>...</option>
{$out}
</select>
END;
        exit(0);
        break;

    case "state":
        $sqlcat="and `countryid` like '{$_REQUEST['first']}'";
        $out = $Global_Class->getdropdown( '', "state", "name{$language_set}", "id", "name{$language_set}",0, $sqlcat );
        //$out = $Global_Class->getdropdown( '', "province", "name", "province", "province",0, $sqlcat );
        //$out=explode("</option>",$out);
        //$out[0]=preg_replace("/<option value=/i","<option selected value=",$out[0]);
        //$out=implode("</option>",$out);

        echo <<<END
<select name="state" class="dropdown" style="width:165px" onchange="funcget1cc('city',document.formarticle.state.value,document.formarticle.state.value,'city',document.getElementById('city'),'{$lang['tpl_auto_Loading']}');return false;">
<option value=''>...</option>
{$out}
</select>
END;
        exit(0);
        break;

    case "city":
        $sqlcat=" and `stateid` like '{$_REQUEST['first']}' ";
        $out = $Global_Class->getdropdown( '', "city", "name{$language_set}", "id", "name{$language_set}",0, $sqlcat  );
        //$out = $Global_Class->getdropdown( '', "listing", "city", "city", "city",0,$sqlcat  );

        //$out=explode("</option>",$out);
        //$out[0]=preg_replace("/<option value=/i","<option selected value=",$out[0]);
        //$out=implode("</option>",$out);

        echo <<<END
<select name="city" size="1"  style="width:165px;" onchange="funcget1('admin',document.formarticle.admin.value,document.formarticle.country.value,document.formarticle.state.value,document.formarticle.city.value,'admin',document.getElementById('admin'),'{$lang['tpl_auto_Loading']}');return false;">
<option value=''>...</option>
{$out}
</select>
END;
        exit(0);
        break;

}

//$_SESSION[session_uid]='321';        
switch ($p){
    case "image":
        $x=150;
        $y=20;

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

        $red=imagecolorallocate($im, 255, 255, 255);

        imagestring($im, 6, 58, 3, $b, $red);

        header("Content-type: image/png");
        imagepng ($im);
        exit(0);
        break;
    case "sitemapxml":
        set_time_limit(0);

        $VisitClass->resetarray(0);

        $output = $VisitClass->sitemapxml();

        header ("content-type: text/xml");
        //$output .= $VisitClass->frontend($page,$output_);
        echo $output;
        exit(0);

        break;
    case "mycars":

        if ($_REQUEST['o']=='add'){
            $arraytemp=array();
            //if (!is_array($_COOKIE['myproperties'])) $_COOKIE['myproperties']=array();
            //
            $arraytemp=explode(',',$_COOKIE['mycars']);

            $arraytemp[]=$_REQUEST['id'];
            //unset($arraytemp[array_search('',$arraytemp)]);
            $arraytemp=array_unique($arraytemp);
            $tempval =implode(",",$arraytemp);

            setcookie ("mycars", $tempval, time() + 3600*24*365,'/');

            header("Location: index.php?p=mycars"); /* Redirect browser */
            exit(0);
        }
        if ($_REQUEST['o']=='delete'){

            $arraytemp=explode(',',$_COOKIE['mycars']);
            if (!is_array($arraytemp)) $arraytemp=array();
            unset($arraytemp[array_search($_REQUEST['id'],$arraytemp)]);
            unset($arraytemp[array_search('',$arraytemp)]);
            $arraytemp=array_unique($arraytemp);
            $tempval =implode(",",$arraytemp);
            setcookie ("mycars", '', time() - 3600*24*365,'/');
            setcookie ("mycars", $tempval, time() + 3600*24*365,'/');

            header("Location: index.php?p=mycars"); /* Redirect browser */
            exit(0);
        }

        $VisitClass->resetarray(0);
        $page = $lang["tpl_auto_Wishlist"];
        $myproper = 1;
        $outputtoscreen_car = $VisitClass->cars_list($pageoutfin,$nr_car_found,$myproper);

        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);
        break;
    case "compare":
        $listing_profile = $Global_Class->getprofile( $_REQUEST['compareid'],"cars","id" );
        $listing_profile1 = $Global_Class->getprofile( $_REQUEST['compareid1'],"cars","id" );

        if ($listing_profile and $listing_profile1) {

            $news_profile['title'] = $lang['tpl_auto_Compare_properties'].$listing_profile['make']." ".$listing_profile['model'].$lang['tpl_auto_and'].$listing_profile1['make']." ".$listing_profile1['model'];

            $news_profile['cars1'] = $VisitClass->cars_details($listing_profile, $num_rows_gallery,2);
            $news_profile['cars2'] = $VisitClass->cars_details($listing_profile1, $num_rows_gallery,2);

            $outputtoscreen_listing = $tpl->replace( $news_profile, "cars_compare.html" );

            //$VisitClass->resetarray();
            $news_profile['message']=$outputtoscreen_listing;

            echo $outputtoscreen = $tpl->replace($news_profile,"send_email_thanks.html");
            exit(0) ;
        }
        break;
//30
//de aici old                
    case "sitemap":

        $VisitClass->resetarray(0);
        $page = $lang["tpl_auto_sitemap"];

        $profile['allcategory'] = $VisitClass->category_content();

        $profile['allisting'] = $VisitClass->sitemap();

        $outputtoscreen_ .= $tpl->replace( $profile, "sitemap.html" );

        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_);

        break;
    case "allsponsored":

        $VisitClass->resetarray(0);
        $page = $lang["tpl_auto_sponsored"];
        $outputtoscreen_car = $VisitClass->allsponsored();

        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);

        break;
    case "allfeatures":
        $news_profile['page'] = $lang["tpl_auto_Features"];

        $sql = "SELECT {$config['table_prefix']}features.* FROM `{$config['table_prefix']}features` where 1 order by {$config['table_prefix']}features.name";
        $result = $db->query( $sql );
        $num_rows = mysqli_num_rows( $result );
        $contor=0;
        if ( $num_rows > 0 ) {
            while ( $var_features = mysqli_fetch_assoc( $result ) ) {
                if ($contor%2==1) {
                    $var=1;
                }else{
                    $var="";
                }
                $user['features'.$var] .= "<img src=\"images/close.gif\" border=\"0\">&nbsp;".$var_features["name".$language_set]."<br>";
                $contor++;
            } // while
        }

        @mysqli_free_result($result);
        $outputtoscreen_listing = $tpl->replace($user,"allfeatures.html");

        $VisitClass->resetarray(0);
        $page = $lang["tpl_auto_features"];
        $outputtoscreen_car = $outputtoscreen_listing;

        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);

        break;
    case "news":
        if ($config['user_same_format_for_all_customlinks']){
            $VisitClass->resetarray(0);
            $page = $lang["tpl_auto_news"];
            $outputtoscreen_car = $VisitClass->news_content();

            $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);
        }else{
            $news_profile['customlinks'] = $VisitClass->customlinks("customlinks_repeat.html");
            $news_profile['page'] = $lang["tpl_auto_news"];
            $news_profile['customlinks_repeat'] .= $VisitClass->news_content();
            $outputtoscreen .= $tpl->replace($news_profile,"customlinks.html");
        }
        break;
        break;
    case "customlinks":
        $customlinks_profile = $Global_Class->getprofile( $_REQUEST['id'],"customlinks","id" );
        if ($customlinks_profile ){
            $car_profileini = $customlinks_profile;
            $customlinks_profile['content']=$customlinks_profile["content"."$language_set"];
            $customlinks_profile['name']=$customlinks_profile["name"."$language_set"];
            $customlinks_profile['name1']=makeurl($customlinks_profile['name']);

            if ($config['user_same_format_for_all_customlinks']){
                $VisitClass->resetarray(0);
                $page = $customlinks_profile['name'];
                $outputtoscreen_car = $customlinks_profile['content'];

                $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);
            }else{
                $news_profile['customlinks'] = $VisitClass->customlinks("customlinks_repeat.html");
                $news_profile['page'] = $customlinks_profile['name'];
                $news_profile['customlinks_repeat'] .= $customlinks_profile['content'];

                $outputtoscreen .= $tpl->replace($news_profile,"customlinks.html");
            }
        }else{
            header('HTTP/1.0 404 Not Found');
            //$outputtoscreen_car=$lang['tpl_auto_no_listing_found'];
            $customlinks_profile = $Global_Class->getprofile( 8,"customlinks","id" );
            $outputtoscreen_car=$customlinks_profile["content"."$language_set"];
            $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car,$nr_car_found,$pageoutfin);
        }
        break;
        break;
    case "faq":
        if ($config['user_same_format_for_all_customlinks']){
            $VisitClass->resetarray(0);
            $page = $lang["tpl_auto_faq"];
            $outputtoscreen_car = $VisitClass->faq_content();

            $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);
        }else{
            $news_profile['customlinks'] = $VisitClass->customlinks("customlinks_repeat.html");
            $news_profile['page'] = $lang["tpl_auto_faq"];

            $news_profile['customlinks_repeat']  .= $VisitClass->faq_content();
            $outputtoscreen .= $tpl->replace($news_profile,"customlinks.html");
        }
        break;
        break;
    case "details":

        $car_profile = $Global_Class->getprofile( $_REQUEST['id'],"$tabel_cars","id" );
        $car_profileini = $car_profile;
        if ($car_profile) {
            $VisitClass->resetarray(0);
            if ($config['show_category_indetailpage']){
                $car_profile1 = $VisitClass->prepareuser($car_profile);
                $page = $car_profile1['category']." ".$car_profile1['model']." ".$car_profile1['make']." ".$car_profile1['year'];
            }else{
                $page = $car_profile['model'];
            }
            //$outputtoscreen_car = $VisitClass->cars_list($pageoutfin,$nr_car_found);
            $outputtoscreen_car .= $VisitClass->cars_details($car_profile, $num_rows_gallery,0);

            $user_profile['number']=$user_profile['nrpozetotal'];
            if ($num_rows_gallery>0){
                $gallery_profile['model']=$car_profile['model'];
                $gallery_profile['number']=$num_rows_gallery;
                if ($settings_profile['picture_width']>10){
                    $gallery_profile['width_div']=$config['picturewidth']+80;
                    $gallery_profile['height_div']=$config['pictureheight']+125;
                }else{
                    $gallery_profile['width_div']=$IMG_WIDTH_BIG+80;
                    $gallery_profile['height_div']=$IMG_HEIGHT_BIG+125;
                }
                $outputtoscreen_car .= $tpl->replace($gallery_profile,"cars_gallery_div.html");
            }

            $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car,$nr_car_found,$pageoutfin);
        }else{
            header('HTTP/1.0 404 Not Found');
            //$outputtoscreen_car=$lang['tpl_auto_no_listing_found'];
            $customlinks_profile = $Global_Class->getprofile( 8,"customlinks","id" );
            $outputtoscreen_car=$customlinks_profile["content"."$language_set"];
            $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car,$nr_car_found,$pageoutfin);
        }
        break;
    case "vehicle_information":
        $car_profile = $Global_Class->getprofile( $_REQUEST['id'],"$tabel_cars","id" );
        $car_profileini = $car_profile;
        if ($car_profile) {
            $category_profile = $Global_Class->getprofile( $user['category'], "category", 'id' );


            if (!is_array($config["config_search_field"])) $config["config_search_field"]=array();
            $array_list = $config["config_search_field"];
            foreach($array_list as $key=>$val){
                $news_profile[$val]=$_SESSION[$val];
            }


            $array_list=$config["config_search_field"];


            $outputtoscreen1 = $VisitClass->cars_details($car_profile, $num_rows_gallery,1);

            $news_profile['titlesite'] = $car_profile['name'];


            $outputtoscreen .= $outputtoscreen1;

            $user_profile['number']=$user_profile['nrpozetotal'];
            if ($num_rows_gallery>0){
                $gallery_profile['model']=$car_profile['model'];
                $gallery_profile['number']=$num_rows_gallery;
                $gallery_profile['width_div']=$settings_profile['picture_width']+50;
                $gallery_profile['height_div']=$settings_profile['picture_height']+50;
                $outputtoscreen .= $tpl->replace($gallery_profile,"cars_gallery_div.html");
            }
            $news_profile['vehicle_information'] = $outputtoscreen;
            echo $outputtoscreen = $tpl->replace($news_profile,"vehicle_information_sheet.html");
            exit;
            $footer="";
        }
        break;
    case "contactus":
        $page=$lang['tpl_auto_contactus'];
        $outputtoscreen_car = $tpl->replace($email,"contact.html");
        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car,$nr_car_found,$pageoutfin);
        break;
    case "contactus1":

        $page=$lang['tpl_auto_contactus'];


        foreach ($_POST as $key=>$val){
            if (!in_array($key,$config['igonorefiledcontact'])){
                $email[$key]=$val;
                $bodymail.="$key: $val<br>\n";
            }
        }

        $settings_template = $Global_Class->getprofile( "1","template","id" );


        $settings_template['contact_subject'] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $lang['contactus_subject'] );
        $settings_template['contact_body'] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $lang['contactus_body'] ).$bodymail;
        if ($_SESSION['session_uid']!=$_REQUEST['code'] or $_REQUEST['code']=='' ){
            $email['error']=$lang['tpl_auto_The_Image_Text_is_not_correct1'];
            $outputtoscreen_car = $tpl->replace($email,"contact.html");
        }else{
            if ($email_var['email']=='') $email_var['email']=$settings_template['email'];
            $sendresult = $Email_class->emailsend( $settings_template['email'], $settings_template['from'], $email_var['email'], $email_var['name'], $settings_template['contact_subject'], $settings_template['contact_body'] );
            $email_var['message']=$lang['tpl_auto_thanks'];
            $outputtoscreen_car = $tpl->replace($email_var,"contact_thanks.html");
        }

        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car,$nr_car_found,$pageoutfin);

        break;
    case "contact":
        $admin_profile = $Global_Class->getprofile( $_REQUEST['admin'],"admin","id" );
        if ($admin_profile) {
            $car_profile = $Global_Class->getprofile( $_REQUEST['id'],"$tabel_cars","id" );
            $category_profile = $Global_Class->getprofile( $car_profile['category'], "category", 'id' );
            $email=$settings_profile;
            $email['admin'] = $_REQUEST['admin'];
            $email['id'] = $_REQUEST['id'];
            $email['name_model_id'] = $category_profile['name']." - ".$car_profile['model']." (#".$_REQUEST['id'].")";
            $outputtoscreen = $tpl->replace($email,"send_email.html");
            echo $outputtoscreen;
            exit;
        }
        break;
    case "contact1":
        $admin_profile = $Global_Class->getprofile( $_REQUEST['admin'],"admin","id" );
        if ($admin_profile) {
            $car_profile = $Global_Class->getprofile( $_REQUEST['id'],"$tabel_cars","id" );
            $category_profile = $Global_Class->getprofile( $car_profile['category'], "category", 'id' );
            $email_var = $VisitClass->prepareuser($car_profile);
            $email_var['guest_name'] = $_REQUEST['name'];
            $email_var['guest_phone'] = $_REQUEST['phone'];
            $email_var['guest_email'] = $_REQUEST['email'];
            $email_var['guest_message'] = $_REQUEST['message'];
            $email_var['model']=$car_profile['model'];
            $email_var['id']=$_REQUEST['id'];
            $email_var['name']=$admin_profile['name'];
            $email_var['description']='';
            $settings_template = $Global_Class->getprofile( "1","template","id" );

            $settings_template['contact_subject'] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $settings_template["contact_subject".$language_set] );
            $settings_template['contact_body'] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $settings_template["contact_body".$language_set] );
            if ($_SESSION['session_uid']!=$_REQUEST['code'] or $_REQUEST['code']=='' ){
                $car_profile = $Global_Class->getprofile( $_REQUEST['id'],"$tabel_cars","id" );
                $category_profile = $Global_Class->getprofile( $car_profile['category'], "category", 'id' );
                $email=$settings_profile;
                $email['admin'] = $_REQUEST['admin'];
                $email['id'] = $_REQUEST['id'];
                $email['name_model_id'] = $category_profile['name']." - ".$car_profile['model']." (#".$_REQUEST['id'].")";
                $email['error']=$lang['tpl_auto_The_Image_Text_is_not_correct1'];
                $outputtoscreen = $tpl->replace($email,"send_email.html");

            }else{
                $sendresult = $Email_class->emailsend( $admin_profile['email'], $admin_profile['name'], $email_var['guest_email'], $email_var['guest_name'], $settings_template['contact_subject'], $settings_template['contact_body'] );
                $email_var['message']=$lang['tpl_auto_thanks'];
                foreach($email_var as $key=>$val){
                    $email_var[$key]=addslashes($email_var[$key]);
                }
                $sql="INSERT INTO `{$config['table_prefix']}messages` ( `id` , `carsid`,  `name` ,  `email` , `phone`,`message`,`date_add` )
	VALUES
	( '', '{$_REQUEST['id']}', '{$email_var['guest_name']}', '{$email_var['guest_email']}', '{$email_var['guest_phone']}','{$email_var['guest_message']}','".date("Y-m-d")."' );";
                $result = $db->query($sql);

                $outputtoscreen = $tpl->replace($email_var,"send_email_thanks.html");
            }


            echo $outputtoscreen;
            exit;
        }
        break;
    case "signup":
        $settings_profile['adprofiles'] = $VisitClass->adprofiles_content($nrmax);
        $settings_profile['nrmax'] = $nrmax;
        $outputtoscreen_car = $tpl->replace($settings_profile,"signup.html");
        $page = $lang["tpl_auto_signup"];
        //print_r($_SESSION);
        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);
        break;
    case "signup1":
        $user_profile = $Global_Class->getprofile( $HTTP_POST_VARS['input_username'], "admin", "username" );
        $email_profile = $Global_Class->getprofile( $HTTP_POST_VARS['input_email'], "admin", "email" );
        if ( $user_profile )
        {
            $outputtoscreen_add[1] .= $lang['error_change1']['username_exist'];
        }
        if ( $email_profile )
        {
            $outputtoscreen_add[1] .= $lang['error_change1']['email_exist'];
        }
        if ( ( $HTTP_POST_VARS['input_password'] != $HTTP_POST_VARS['input_password1'] ) )
        {
            $outputtoscreen_add[1] .= $lang['error_change1']['password_not_equal'];
        }
        if ( ( strlen( $HTTP_POST_VARS['input_username'] ) < 4 ) || ( strlen( $HTTP_POST_VARS['input_username'] ) > 20 ) )
        {
            $outputtoscreen_add[1] .= $lang['error_change1']['username_short'];
        } elseif ( ( strlen( $HTTP_POST_VARS['input_password'] ) < 4 ) || ( strlen( $HTTP_POST_VARS['input_password1'] ) > 20 ) )
        {
            $outputtoscreen_add[1] .= $lang['error_change1']['password_short'];
        }
        $email_var['password1'] = $_REQUEST['input_password1'];
        $email_var['password'] = $_REQUEST['input_password'];
        $email_var['email'] = $_REQUEST['input_email'];
        $email_var['username'] = $_REQUEST['input_username'];
        $email_var['name'] = $_REQUEST['input_name'];
        $email_var['phone'] = $_REQUEST['input_phone'];
        $email_var['country'] = $_REQUEST['input_country'];
        $email_var['state'] = $_REQUEST['input_state'];
        $email_var['city'] = $_REQUEST['input_city'];
        $email_var['fax'] = $_REQUEST['input_fax'];
        $email_var['address'] = $_REQUEST['input_address'];
        $email_var['zip'] = $_REQUEST['input_zip'];
        $email_var['nocontactemail'] = $_REQUEST['input_nocontactemail'];


        $email_var['adprofiles'] = $_REQUEST['adprofiles'];

        $user_profile = $Global_Class->getprofile( $_REQUEST['adprofiles'], "adprofiles", "id" );

        if ($user_profile) {
            $email_var['nocars'] = $user_profile['nocars'];
            $email_var['nopictures'] = $user_profile['nopictures'];
            $email_var['delay'] = $user_profile['days'];

        }else{
            $email_var['nocars'] = $settings_profile['nocars'];
            $email_var['nopictures'] = $settings_profile['nopictures'];
            $email_var['delay'] = $settings_profile['delay_How_many_days_this_object_will_be_active'];
        }
        if ($_SESSION['session_uid']!=$_REQUEST['code'] or $_REQUEST['code']=='' ){
            $outputtoscreen_add[1] =" ".$lang['tpl_auto_The_Image_Text_is_not_correct1'];
        }

        //print_r($_SESSION);
        if(  $outputtoscreen_add[1] =="") {

            srand((double)microtime() * 1000000);
            $unic_id = @md5(rand(0, 999999));
            $email_var['link'] = $config['url_path']."index.php?p=confirm&id=$unic_id";

            $settings_template = $Global_Class->getprofile( "1","template","id" );

            $settings_template['signup_subject'] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $settings_template["signup_subject".$language_set] );
            $settings_template['signup_body'] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $settings_template["signup_body".$language_set] );
            $email_var['password']=md5($email_var['password']);
            $sql="INSERT INTO `{$config['table_prefix']}admin` ( `id` , `right` , `username` , `password` , `password1` , `email` , `noemail` ,`nocontactemail`, `name` , `phone` ,`fax`, `address`,  `country` , `state` , `city` , `zip`, `logo` , `description` , `nocars` , `nopictures` ,  `adprofiles`, `unic_id`,`active`, `delay` , `date_delay` )
VALUES
( '', '{$settings_profile[rights_signupuser]}', '{$email_var['username']}' , '{$email_var['password']}' , '{$email_var['password']}', '{$email_var['email']}', '1','{$email_var['nocontactemail']}', '{$email_var['name']}', '{$email_var['phone']}','{$email_var['fax']}','{$email_var['address']}', '{$email_var['country']}', '{$email_var['state']}', '{$email_var['city']}','{$email_var['zip']}', '', '', '{$email_var['nocars']}', '{$email_var['nopictures']}', '{$email_var['adprofiles']}','$unic_id','1' , '720', CURDATE() );";
            $result = $db->query($sql);

            $sendresult = $Email_class->emailsend(  $email_var['email'], $email_var['username'],$settings_template['email'], $settings_template['from'], $settings_template['signup_subject'], $settings_template['signup_body'] );
            $sendresult = $Email_class->emailsend(  $settings_template['email'], $settings_template['from'] , $settings_template['email'], $settings_template['from'], $settings_template['signup_subject'], $settings_template['signup_body'] );
            $email_var['message']=$lang['tpl_auto_thanks_signup'];
            $email_var['message'] .= $tpl->replace(array(),"google.html");
            $outputtoscreen_car = $tpl->replace($email_var,"send_email_thanks.html");

        }else{
            $settings_profile['error']= $outputtoscreen_add[1];
            $settings_profile['adprofiles'] = $VisitClass->adprofiles_content($nrmax);
            $settings_profile['nrmax'] = $nrmax;
            $outputtoscreen_car = $tpl->replace($settings_profile,"signup.html");

        }
        $page = $lang["tpl_auto_signup"];
        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);
        break;

    case "confirm":
        $id=$_REQUEST['id'];
        if ($id=="")
            break;
        $user_profile = $Global_Class->getprofile( $id, "admin", "unic_id" );
        if ( !$user_profile )
        {
            break;
        }
        $adprofiles= $Global_Class->getprofile( $user_profile['adprofiles'], "adprofiles", "id" );
        if ($settings_profile['adprofiles']==0) {
            $active = 1;
        }else{
            $active = 3;
        }
        if ($user_profile['active']==0) {
            $active = 0;
        }
        if ($adprofiles && $adprofiles['price']==0){
            $active = 1;
            $settings_profile['adprofiles']=0;
            $sqlupdate=" ,delay='$adprofiles[days]',date_delay=NOW(),emailrenewsent=0,`daystoexpire` = $adprofiles[days]-(TO_DAYS(NOW()) - TO_DAYS(NOW())) ";
        }
        $sql="UPDATE `{$config['table_prefix']}admin` SET `active`='$active'{$sqlupdate} where `unic_id`='$id' limit 1;";
        $result = $db->query($sql);

        $news_profile['page'] = $lang['tpl_auto_Confirm_email_address'];

        if (!is_array($config["config_search_field"])) $config["config_search_field"]=array();
        $array_list=$config["config_search_field"];
        if ($_REQUEST['submit1']!="" or $_REQUEST['submit']!="") {
            foreach($array_list as $key=>$val){
                $news_profile[$val]=$_REQUEST[$val];
                $_SESSION[$val]=$_REQUEST[$val];
            }
        }


        $news_profile["category"] = $Global_Class->getdropdown( $_REQUEST['category'], "category", "name{$language_set}", "id", "name{$language_set}",1,"",$_SESSION['cardatase'] );
        $news_profile["make"] = $Global_Class->getdropdown( $_REQUEST['make'], "make", "name{$language_set}", "id", "name{$language_set}",1,"",$_SESSION['cardatase'] );
        $news_profile["model"] = $Global_Class->getdropdown( $_REQUEST['model'], "model", "name{$language_set}", "id", "name{$language_set}",0, " and makeid='{$_SESSION['make']}' ",$_SESSION['cardatase'] );
        $news_profile["fueltype"] = $Global_Class->getdropdown_array( $_REQUEST['fueltype'], $lang['fuelltype'],1,"",$_SESSION['cardatase'] );
        $news_profile["orderby"] = $Global_Class->getdropdown_array_car( $_REQUEST['orderby'], $config["config_orderby"] );
        $news_profile["method"] = $Global_Class->getdropdown_array1( $_REQUEST['method'], $config["config_method"] );
        $settings_profile['message'] = $lang['tpl_auto_thanks_confirm'];
        if ($settings_profile['adprofiles']==0) {
            $settings_profile['message'] .= $lang['tpl_auto_thanks_confirm1'];
        }else{
            $settings_profile['message'] .= $lang['tpl_auto_thanks_confirm1'];
            /*
            $settings_profile['message'] .= $lang['tpl_auto_thanks_confirm2'];
            $payment_profile = $Global_Class->getprofile(1 , "payment", "id" );
            $adprofiles_profile = $Global_Class->getprofile($user_profile['adprofiles'] , "adprofiles", "id" );
            switch ($payment_profile['id']) {
             default:
                  $payment_profile['item_number']=$user_profile['id'];
                  $payment_profile['amount'] =$adprofiles_profile['price'];
                  $payment_profile['return'] =$config['url_path']."ipn.php";
                  $settings_profile['message'] .= $tpl->replace($payment_profile,"paypal.html");
             break;
            }
            */
        }
        $outputtoscreen_car = $tpl->replace($settings_profile,"emailadmin_thanks.html");


        $news_profile["nr_car_found"] = $nr_car_found;

        $news_profile['pageoutfin']=$pageoutfin;
        $news_profile['signupmembers'] = $news_profile['signupmembers'];
        $news_profile['sponsored'] = $VisitClass->cars_sponsored();

        if (trim($news_profile['sponsored'])!=''){
            $news_profile["sponsored_show"] = $tpl->replace($news_profile,"sponsored_show.html");
        }
        $news_profile['arrival_date'] = $_SESSION['arrival_date'];
        $news_profile['departure_date'] = $_SESSION['departure_date'];

        $news_profile["simple_search"] = $tpl->replace($news_profile,"simple_search.html");
        $news_profile["newsletter_form"] = $tpl->replace($news_profile,"newsletter_form.html");
        $news_profile['output_car_details'] = $outputtoscreen_car;
        $outputtoscreen .= $tpl->replace($news_profile,"cars.html");

        break;


    case "confirmpayment":
        $news_profile['page'] = $lang['tpl_auto_Confirm_email_address'];

        if (!is_array($config["config_search_field"])) $config["config_search_field"]=array();
        $array_list=$config["config_search_field"];
        if ($_REQUEST['submit1']!="" or $_REQUEST['submit']!="") {
            foreach($array_list as $key=>$val){
                $news_profile[$val]=$_REQUEST[$val];
                $_SESSION[$val]=$_REQUEST[$val];
            }
        }

        $id=$_REQUEST['id'];
        if ($id=="")
            break;
        $user_profile = $Global_Class->getprofile( $id, "admin", "unic_id" );
        if ( !$user_profile )
        {
            break;
        }

        $news_profile["category"] = $Global_Class->getdropdown( $_REQUEST['category'], "category", "name{$language_set}", "id", "name{$language_set}",1,"",$_SESSION['cardatase'] );
        $news_profile["make"] = $Global_Class->getdropdown( $_REQUEST['make'], "make", "name{$language_set}", "id", "name{$language_set}",1,"",$_SESSION['cardatase'] );
        $news_profile["model"] = $Global_Class->getdropdown( $_REQUEST['model'], "model", "name{$language_set}", "id", "name{$language_set}",0, " and makeid='{$_SESSION['make']}' ",$_SESSION['cardatase'] );
        $news_profile["fueltype"] = $Global_Class->getdropdown_array( $_REQUEST['fueltype'], $lang['fuelltype'],1,"",$_SESSION['cardatase'] );
        $news_profile["orderby"] = $Global_Class->getdropdown_array_car( $_REQUEST['orderby'], $config["config_orderby"] );
        $news_profile["method"] = $Global_Class->getdropdown_array1( $_REQUEST['method'], $config["config_method"] );
        $settings_profile['message'] = $lang['tpl_auto_thanks_confirm'];
        if ($user_profile['active']==1) {
            $settings_profile['message'] .= $lang['tpl_auto_thanks_confirm1'];
        }else{
            if ($user_profile['active']==2) {


                if ($settings_profile['adprofiles']==0) {
                    $active = 1;
                }else{
                    $active = 3;
                }
                $sql="UPDATE `{$config['table_prefix']}admin` SET `active`='$active' where `unic_id`='$id' limit 1;";
                $result = $db->query($sql);


            }

            $settings_profile['message'] .= $lang['tpl_auto_thanks_confirm2'];

            $payment_profile = $Global_Class->getprofile(1 , "payment", "id" );
            $adprofiles_profile = $Global_Class->getprofile($user_profile['adprofiles'] , "adprofiles", "id" );
            switch ($payment_profile['id']) {
                default:
                    $payment_profile['amount'] =$adprofiles_profile['price'];
                    $payment_profile['return'] =$adprofiles_profile['price'];
                    $settings_profile['message'] .= $tpl->replace($payment_profile,"paypal.html");
                    break;
            }

        }
        $outputtoscreen_car = $tpl->replace($settings_profile,"emailadmin_thanks.html");


        $news_profile["nr_car_found"] = $nr_car_found;

        $news_profile['pageoutfin']=$pageoutfin;
        $news_profile['signupmembers'] = $news_profile['signupmembers'];
        $news_profile['sponsored'] = $VisitClass->cars_sponsored();
        if (trim($news_profile['sponsored'])!=''){
            $news_profile["sponsored_show"] = $tpl->replace($news_profile,"sponsored_show.html");
        }
        $news_profile['arrival_date'] = $_SESSION['arrival_date'];
        $news_profile['departure_date'] = $_SESSION['departure_date'];

        $news_profile["simple_search"] = $tpl->replace($news_profile,"simple_search.html");
        $news_profile["newsletter_form"] = $tpl->replace($news_profile,"newsletter_form.html");
        $news_profile['output_car_details'] = $outputtoscreen_car;
        $outputtoscreen .= $tpl->replace($news_profile,"cars.html");

        break;

    case "subscribe":



        $email_profile = $Global_Class->getprofile( $_REQUEST['input_email'], "members", "email" );

        if ($_REQUEST[input_subscribe]==1){
            if ( $email_profile )
            {
                $outputtoscreen_add[1] .= $lang['error_members']['email_exist'];
            }
            if ( $_REQUEST['input_name']=='' )
            {
                $outputtoscreen_add[1] .= $lang['error_members']['name'];
            }
        }else{
            if ( !$email_profile )
            {
                $outputtoscreen_add[1] .= $lang['error_members']['email_notexist'];
            }
        }
        if ( $_REQUEST['input_email']=='' )
        {
            $outputtoscreen_add[1] .= $lang['error_members']['email'];
        }

        $email_var['name'] = $_REQUEST['input_name'];
        $email_var['email'] = $_REQUEST['input_email'];

        if(  $outputtoscreen_add[1] =="") {
            if ($_REQUEST[input_subscribe]==1){
                srand((double)microtime() * 1000000);
                $unic_id = @md5(rand(0, 999999));
                $email_var['link'] = $config['url_path']."index.php?p=confirmsubcribe&id=$unic_id";

                $settings_template = $Global_Class->getprofile( "1","template","id" );

                $settings_template['signupmembers_subject'] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $settings_template["signupmembers_subject".$language_set] );
                $settings_template['signupmembers_body'] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $settings_template["signupmembers_body".$language_set] );
                $email_var['password']=md5($email_var['password']);
                $sql="INSERT INTO `{$config['table_prefix']}members` ( `id` , `name` ,  `email` , `unic_id`,`active`,`date_add` )
VALUES
( '', '{$email_var['name']}', '{$email_var['email']}', '$unic_id','2','".date("Y-m-d")."' );";
                $result = $db->query($sql);

                $sendresult = $Email_class->emailsend( $email_var['email'], $email_var['name'], $settings_template['email'], $settings_template['from'], $settings_template['signupmembers_subject'], $settings_template['signupmembers_body'] );
                $sendresult = $Email_class->emailsend(  $settings_template['email'], $settings_template['from'], $settings_template['email'], $settings_template['from'], $settings_template['signupmembers_subject'], $settings_template['signupmembers_body'] );
                $email_var['message']=$lang['tpl_auto_thanks_signupmembers'];
                $outputtoscreen_subscribe = $tpl->replace($email_var,"emailadmin_thanks.html");
            }else{
                $outok = $Global_Class->delete_id($email_profile[id],"members",array(),'id');
                if ($outok){
                    $email_var['message']=$lang['tpl_auto_thanks_unsubcribemembers'];
                    $outputtoscreen_subscribe = $tpl->replace($email_var,"emailadmin_thanks.html");
                }
            }
        }else{
            $settings_profile['error']= $outputtoscreen_add[1];
            $outputtoscreen_subscribe = $tpl->replace($settings_profile,"newsletter_form.html");

        }
        $news_profile['page'] = $lang["tpl_auto_Newsletter"];
        $page = $lang["tpl_auto_Newsletter"];
        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_subscribe,$nr_listing_found,$pageoutfin);

        break;

    case "confirmsubcribe":
        $id=$_REQUEST['id'];
        if ($id=="")
            break;
        $user_profile = $Global_Class->getprofile( $id, "members", "unic_id" );
        if ( !$user_profile )
        {
            break;
        }
        $sql="UPDATE `{$config['table_prefix']}members` SET `active`=1 where `unic_id`='$id' limit 1;";
        $result = $db->query($sql);
        echo "
                                        <script type=\"text/javascript\" language=\"javascript\">
                                        <!--
                                        alert('{$lang['tpl_auto_thanks_confirmmembers']}');
                                        window.location='index.php';
                                        // -->
                                        </script>
                        ";
        exit;
        break;
    case "logout":
        $dealer->logoutadmin();
        header( "Location: index.php" );
        exit();
        break;
    case "login":
        $password = $HTTP_POST_VARS['input_password'];
        $username = $HTTP_POST_VARS['input_username'];
        if ( $username == "" )
        {
            $settings_profile['error']=$lang["error1"];
            $header=$tpl->replace($settings_profile,"header.html");
        } elseif ( $password == "" )
        {
            $settings_profile['error']=$lang["error2"];
            $header=$tpl->replace($settings_profile,"header.html");

        } elseif ( $userprofile = $dealer->verifyadmin( $username, $password ) )
        {
            $userprofile = $dealer->getadminprofile( $username );
            $dealer->loginadmin( $username,  $userprofile['id'] );
            header( "Location: index.php" );
            exit();
        }
        else
        {
            if ( $dealer->existadmin( $username ) )
            {
                $profile_ = $dealer->getadminprofile( $username );

                if ($profile_['active']==0){
                    $var = array ( "error" => $lang["error10"]
                    );
                }
                elseif ($profile_['active']==2){
                    $var = array ( "error" => $lang["error11"]
                    );
                }if ($profile_['active']==1)
                $var = array ( "error" => $lang["error4"]
                );
            }
            else
            {
                $var = array ( "error" => $lang["error3"]
                );
            }
            $settings_profile['error']=$var[error];
        }
    //$header=$tpl->replace($settings_profile,"header.html");
    //$outputtoscreen = $header;
    /*
    case "simplesearchid":
          $VisitClass->resetarray();
          $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car,$nr_car_found,$pageoutfin);
          exit(0);
    break;
    case "advsearchid":
          $VisitClass->resetarray();
          $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car,$nr_car_found,$pageoutfin);
          exit(0);
    break;
    */
    case "advsearch":
        $VisitClass->resetarray();
        $page = $lang["tpl_auto_cars"];
        $outputtoscreen_car = $VisitClass->cars_list($pageoutfin,$nr_car_found);
        $_SESSION['adv_search']=true;
        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car,$nr_car_found,$pageoutfin);
        break;
    case "search":
        $VisitClass->resetarray();
        $page = $lang["tpl_auto_cars"];
        $outputtoscreen_car = $VisitClass->cars_list($pageoutfin,$nr_car_found);
        $_SESSION['adv_search']=false;
        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car,$nr_car_found,$pageoutfin);
        break;
    case "view":
        $VisitClass->resetarray();
        $page = $lang["tpl_auto_category"];
        $outputtoscreen_car = $VisitClass->category_content();

        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);
        break;
    case "payment":
        $car_profile = $Global_Class->getprofile( $_REQUEST['id'],"$tabel_cars","id" );
        if ($car_profile) {
            $settings_profile['price']=$car_profile['price'];
            if($car_profile['specialprice']>0) {
                $settings_profile['price'] = $car_profile['specialprice'];
            }
            $settings_profile['down_payment']=$config['down_payment'];
        }
        $outputtoscreen = $tpl->replace($settings_profile,"payment.html");
        echo $outputtoscreen;
        exit;
        break;
//15 feb 2005
    case "send":
        $car_profile = $Global_Class->getprofile( $_REQUEST['id'],"$tabel_cars","id" );
        if ($car_profile) {

            $category_profile = $Global_Class->getprofile( $car_profile['category'], "category", 'id' );
            $car_profile = $VisitClass->prepareuser($car_profile);
            $email=$settings_profile;
            $email['admin'] = $_REQUEST['admin'];
            $email['id'] = $_REQUEST['id'];
            $email['name_model_id'] = $category_profile["name{$language_set}"]." - ".$car_profile['model']." (#".$_REQUEST['id'].")";
            $outputtoscreen = $tpl->replace($email,"send_email_to_a_friend.html");
            echo $outputtoscreen;
            exit;
        }
        break;
    case "send1":
        $car_profile = $Global_Class->getprofile( $_REQUEST['id'],"$tabel_cars","id" );
        if ($car_profile) {
            //$car_profile = $Global_Class->getprofile( $_REQUEST['id'],"$tabel_cars","id" );
            $category_profile = $Global_Class->getprofile( $car_profile['category'], "category", 'id' );
            $email_var = $VisitClass->prepareuser($car_profile);
            $email_var['guest_name'] = $_REQUEST['name'];
            $email_var['guest_email'] = $_REQUEST['email'];
            $email_var['guest_message'] = $_REQUEST['message'];

            $email_var['friendname'] = $_REQUEST['friendname'];
            $email_var['friendemail'] = $_REQUEST['friendemail'];

            $email_var['model']=$car_profile['model'];
            $email_var['id']=$_REQUEST['id'];
            $email_var['name']=$admin_profile['name'];
            $email_var['url']=$config['url_path']."index.php?p=details&id=".$email_var['id'];
            $settings_template = $Global_Class->getprofile( "1","template","id" );

            $settings_template['contact_subject'] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $settings_template["friend_subject".$language_set] );
            $settings_template['contact_body'] = preg_replace( "/\{(\w+)\}/e", "\$email_var[\\1]", $settings_template["friend_body".$language_set] );
            if ($_SESSION['session_uid']!=$_REQUEST['code'] or $_REQUEST['code']=='' ){

                $car_profile = $Global_Class->getprofile( $_REQUEST['id'],"$tabel_cars","id" );
                $category_profile = $Global_Class->getprofile( $car_profile['category'], "category", 'id' );
                $email=$settings_profile;
                $email['admin'] = $_REQUEST['admin'];
                $email['id'] = $_REQUEST['id'];
                $email['name_model_id'] = $category_profile['name']." - ".$car_profile['model']." (#".$_REQUEST['id'].")";
                $email['error']=$lang['tpl_auto_The_Image_Text_is_not_correct1'];
                $outputtoscreen = $tpl->replace($email,"send_email_to_a_friend.html");
            }else{
                $sendresult = $Email_class->emailsend( $email_var['friendemail'], $email_var['friendname'], $email_var['guest_email'], $email_var['guest_name'], $settings_template['contact_subject'], $settings_template['contact_body'] );
                $email_var['message']=$lang['tpl_auto_thanksfriend'];
                $outputtoscreen = $tpl->replace($email_var,"send_email_thanks.html");
            }

            echo $outputtoscreen;
            exit;
        }
        break;
    case "up":

        $VisitClass->resetarray(0);

        $user = $Global_Class->getprofile( $_REQUEST['id'], "cars", 'unicid' );
        $_REQUEST['days']=$config['delay_How_many_days_this_object_will_be_active'];
        if ($user){
            $admin_profile = $Global_Class->getprofile( $user['admin'], "admin", 'id' );
            $user['name']=$admin_profile['name'];

            $user = $VisitClass->prepareuser($user);

            $user['days']=$_REQUEST['days'];

            $outputtoscreen_car.=preg_replace( "/\{(\w+)\}/e", "\$user[\\1]", $lang['youradwasupdated'] );

            srand((double)microtime() * 1000000);
            $unic_id = @md5(rand(0, 999999));

            $sql = "UPDATE `{$config['table_prefix']}cars` SET `date_delay` = INTERVAL {$_REQUEST['days']} DAY+`date_delay`, unicid='{$unic_id}',daystoexpire=0,daysactive=0 where id='{$user['id']}' limit 1;";
            $result = $db->query( $sql );

        }else{
            $outputtoscreen_car.=$lang['tpl_auto_Wrong_ID'];
        }

        $page = $outputtoscreen_car;

        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);

        break;
    default:
        $VisitClass->resetarray(1);
        $homepage_profile = $Global_Class->getprofile( "1","homepage","id" );
        $page = $homepage_profile["name{$language_set}"];
        $outputtoscreen_car = $homepage_profile["description{$language_set}"];
        $_SESSION['adv_search']=false;

        $outputtoscreen .= $VisitClass->frontend($page,$outputtoscreen_car);


        break;
}


if ($_REQUEST['p']=='search' OR $_REQUEST['p']=='advsearch'){



    if ($lang['tpl_auto_searchtile']==''){
        $lang['tpl_auto_searchtile']="List of <make> <model> <year> in <country> <city>";
    }

    if ($lang['tpl_auto_descriptionsearchtile']==''){
        $lang['tpl_auto_descriptionsearchtile']="List of <make> new/used cars for sale in <country> at the top used autos website mecarz.com. We have used & new <make> in <country> autos added on daily basis at your best source for used cars MECarz.com";
    }

    $config['config_auto_searchtile']=$lang['tpl_auto_searchtile'];
    $config['config_auto_descriptionsearchtile']=$lang['tpl_auto_descriptionsearchtile'];

    $category_profile = $Global_Class->getprofile( $_REQUEST['make'], "make", 'id' );
    $config['config_auto_searchtile']=str_replace("<make>",$category_profile['name'.$language_set],$config['config_auto_searchtile']);
    $config['config_auto_descriptionsearchtile']=str_replace("<make>",$category_profile['name'.$language_set],$config['config_auto_descriptionsearchtile']);

    $category_profile = $Global_Class->getprofile( $_REQUEST['model'], "model", 'id' );
    $config['config_auto_searchtile']=str_replace("<model>",$category_profile['name'.$language_set],$config['config_auto_searchtile']);
    $config['config_auto_descriptionsearchtile']=str_replace("<model>",$category_profile['name'.$language_set],$config['config_auto_descriptionsearchtile']);

    $config['config_auto_searchtile']=str_replace("<year>",$_REQUEST['year'],$config['config_auto_searchtile']);
    $config['config_auto_descriptionsearchtile']=str_replace("<year>",$_REQUEST['year'],$config['config_auto_descriptionsearchtile']);

    $category_profile = $Global_Class->getprofile( $_REQUEST[country], "country", 'id' );
    $config['config_auto_searchtile']=str_replace("<country>",$category_profile['name'.$language_set],$config['config_auto_searchtile']);
    $config['config_auto_descriptionsearchtile']=str_replace("<country>",$category_profile['name'.$language_set],$config['config_auto_descriptionsearchtile']);

    $category_profile = $Global_Class->getprofile( $_REQUEST[city], "city", 'id' );
    $config['config_auto_searchtile']=str_replace("<city>",$category_profile['name'.$language_set],$config['config_auto_searchtile']);
    $config['config_auto_descriptionsearchtile']=str_replace("<city>",$category_profile['name'.$language_set],$config['config_auto_descriptionsearchtile']);

    $car_profileini["sitetitle{$language_set}"]=$config['config_auto_searchtile'];
    $car_profileini["metadescription{$language_set}"]=$config['config_auto_descriptionsearchtile'];
}
if ($car_profileini["sitetitle{$language_set}"]!=''){
    $settings_profile["titlesite{$language_set}"] = $car_profileini["sitetitle{$language_set}"];
}
if ($car_profileini["metadescription{$language_set}"]!=''){
    $settings_profile["description{$language_set}"] = $car_profileini["metadescription{$language_set}"];
}
if ($car_profileini["metakeywords{$language_set}"]!=''){
    $settings_profile["keywords{$language_set}"] = $car_profileini["metakeywords{$language_set}"];
}
if ($_REQUEST['page']>0){
    $settings_profile["titlesite{$language_set}"].=' page '.$_REQUEST['page'];
    $settings_profile["description{$language_set}"].=' page '.$_REQUEST['page'];
}
$settings_profile['titlesite'] = $settings_profile["titlesite{$language_set}"];
$settings_profile['description'] = $settings_profile["description{$language_set}"];
$settings_profile['keywords'] = $settings_profile["keywords{$language_set}"];
$settings_profile['header'] = $settings_profile["header{$language_set}"];
$settings_profile['footer'] = $settings_profile["footer{$language_set}"];

//$settings_profile['rentform']=$tpl->replace($settings_profile,"rentform.html");

if (file_exists("seo.php")){
    @include_once("seo.php");
}

if ($language_set==1){
    $config['config_auto_homepageurl']='http://www.mecarz.com/1-index.html';
}else{
    $config['config_auto_homepageurl']='http://www.mecarz.com/';
}
$settings_profile['output'] = $outputtoscreen;
$outputtoscreen=$tpl->replace($settings_profile,"index.html");

echo $outputtoscreen;
//echo "<BR><textarea ROWS=10 COLS=100>$msg_global</TEXTAREA><br>";
//echo $config[sqlsct];
//echo "\n";
//print_r($config[sqls]);

$endtime123 = utime();
$run = $endtime123 - $starttime123;

//echo "<BR><center>Page loaded in " . substr( $run, 0, 5 ) . " seconds.</center>";

ob_end_flush();

?>