<?php
// ini_set ('error_reporting', E_ALL);
set_time_limit(0);

$path = "includes/";
require $path . "db.php";

require $path . "licence.php";
require $path . "config.php";
if (_INSTALL!=1){
    header("Location: install/"); /* Redirect browser */
    exit;
}
$db = new DB;
require $path . "session.php";

session_start();

require $path . "tpl.php";
require $path . "function.php";
require $path . "image.class.php";
require $path . "global.class.php";
require $path . "admin.class.php";
require $path . "visit.class.php";
require $path . "email.class.php";


$config['tpl'] .= "visit/";

$tpl = new TPL;
$Image_Class = new Image;
$Global_Class = new GlobalClass;
$VisitClass = new VisitClass;
$Email_class = new EmailClass;

$settings_profile = $Global_Class->getprofile( "1","settings","id" );

$count = 0;
if ($_REQUEST['language_session'] != '') {
 $_SESSION['language_session'] = $_REQUEST['language_session'];
 $language_set = $_SESSION['language_session'];
 if ($_SESSION['language_session']==0) {
      $language_set = '';
 }
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

if ($settings_profile[language4]!=-1  and $settings_profile[language4]!=''){
    $count++;
    $multiplelanguage[$count] = ucfirst(substr($settings_profile[language4],0,-4));
    $array_lang[]=$count;
}

if ($settings_profile[language5]!=-1  and $settings_profile[language5]!=''){
    $count++;
    $multiplelanguage[$count] = ucfirst(substr($settings_profile[language5],0,-4));
    $array_lang[]=$count;
}

if ($settings_profile[language6]!=-1  and $settings_profile[language6]!=''){
    $count++;
    $multiplelanguage[$count] = ucfirst(substr($settings_profile[language6],0,-4));
    $array_lang[]=$count;
}

if ($count>0) {
  $found=0;
  foreach ($multiplelanguage as $key=>$val){
            $class_ = ($_SESSION['language_session'] == $key ) ? " class=\"selected\"": " class=\"noselected\"";
            $found = ($_SESSION['language_session'] == $key ) ? 1: $found;
            $var_lang['key']=$key;
            $var_lang['val']=strtolower($val);
            $var_lang['class']=strtolower($class_);
            $settings_profile['languagedropdown1'] .= $config["config_separator"].$tpl->replace($var_lang,"urllanguge.html");

  }
  if ($found==0) $_SESSION['language_session']='';
  $class_ = ($_SESSION['language_session'] == '' ) ? " class=\"selected\"": " class=\"noselected\"";
  $var_lang['key']=0;
  $var_lang['val']=strtolower(substr($settings_profile[language],0,-4));
  $var_lang['class']=strtolower($class_);
  $settings_profile['languagedropdown'] = $tpl->replace($var_lang,"urllanguge.html");

  $settings_profile['languagedropdown'] .= $settings_profile['languagedropdown1'];

}
if (file_exists($config['path'].'language/'.$settings_profile["language{$language_set}"])) {
    require $config['path'].'language/'.$settings_profile["language{$language_set}"];
}

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

if ($settings_profile['logo']=="") $settings_profile['logo']="../images/spacer.gif";
if ($settings_profile['thumbnail']=="") $settings_profile['thumbnail']="../images/spacer.gif";
if ($settings_profile['picture']=="") $settings_profile['picture']="../images/spacer.gif";


$p=$_REQUEST['p'];
$arraytabels=array("country",'state',"city","year","category", "make", "model","bodytype","transmission","intcolor", "extcolor",'typeofvehicle','doors','classifyemissions','gearbox','climaticcontrol');
foreach ($arraytabels as $keyy){
$config[$keyy.'profile']=array();
$sql = "SELECT * FROM `{$config['table_prefix']}{$keyy}` WHERE 1";
$result = $db->query( $sql );
$num_rows = mysqli_num_rows( $result );

$contor=0;
if ( $num_rows > 0 ) {
    while ( $user = mysqli_fetch_assoc( $result ) ) {
    	$config[$keyy.'profile'][$user['id']]=$user;
    }
}
}


		        
switch ($p){

        default:

		
		        $variable = array (
		                      "nrresult"=>$settings_profile['nrpageuser'],
		                      "page"=>$_REQUEST['page'],
		                      "agent"=>$_REQUEST['agent']
		        );
		        $consearch=false;
		        if ($_SESSION['orderby']==""){
		            $orderby="date_add";
		        }else{
		            $orderby=$_SESSION['orderby'];
		            $consearch=true;
		        }
		        if ($_SESSION['method']==""){
		            $method="desc";
		        }else{
		            $method=$_SESSION['method'];
		            $consearch=true;
		        }
		
/*

		        */
$_REQUEST['country']=intval($_REQUEST['country']);
if ($_REQUEST['country']>0){
	$sqlc=" and country='{$_REQUEST['country']}' ";
}else{
	$limi= " limit 20";
}
		        $sql = "SELECT *,DATE_FORMAT({$config['table_prefix']}cars.date_add,'%d/%m/%Y') as dateadd  FROM `{$config['table_prefix']}cars` WHERE active >= 1 $sqlc order by id desc ".$limi;// LIMIT 5 and id = 2084
		        $result = $db->query( $sql );
		        $num_rows = mysqli_num_rows( $result );

		        $contor=0;
		        if ( $num_rows > 0 ) {
		            while ( $user = mysqli_fetch_assoc( $result ) ) {

						foreach ($arraytabels as $keyy){
					        $cate_profile	= $config[$keyy.'profile'][$user[$keyy]];
					        $user[$keyy]=$cate_profile['name'.$language_set];
				        }		            	    
		//echo $user[county]."\n";

        //$admin_profile	= $config[adminprofile][$user['admin']];
        //$user['admin_phone']=$config[adminprofile][$user['admin']]['phone'];
        //$user['admin_email']=$config[adminprofile][$user['admin']]['email'];
		
        /*
        
        //$user['category']=$config['categorytyperel'][$user[type]];//$cate_profile['name'.$language_set];
       
        if (!is_array($config[typeprofile][$user[type]])){
        $t_profile = $Global_Class->getprofile( $user[type], "type", 'id' );
        $config[typeprofile][$user[type]]=$t_profile;
        }else{
        $t_profile	= $config[typeprofile][$user[type]];
        }
        
        if (!in_array($user[type],$config['categorytyperel'])){
        $user['type']=$config['categorytyperel'][1];
        }else{
        $user['type']=$config['categorytyperel'][$user[type]];	
        }
        */
        
        $user[year1]=makeurl($user['year']);
        $user[make1]=makeurl($user['make']);
        $user[model1]=makeurl($user['model']);
		if ($user['specialprice']>0) $user['price']=$user['specialprice'];
		
        $user['url']=$config['url_path']."{$user['id']}_car_{$user[year1]}_{$user[make1]}_{$user[model1]}.html";
        $user['price']=round($user['price'],0);
        //$user['shortdescription']=htmlspecialchars(str_replace(array("\n","\r\n","\r")," ",$user['shortdescription']));
        //$user['shortdescription']=str_replace(array("&"),"&amp;",$user['shortdescription']);
        
        $user['description']=htmlspecialchars(str_replace(array("\n","\r\n","\r")," ",$user['description']));
        //$user['description']=str_replace(array("&"),"&amp;",$user['description']);
        

		//print_r($user);
		//$config['ignorelist']=false;
		//$config['foundcountry']=false;
		//$user['active']=$config['activelist'][$user['active']];
		//echo $user['county'];
		//echo "<BR>";
		//$user['county']=$config['statearray'][$user['county']];
		/*foreach ($config['statearray'] as $keyv=>$valv){
			if (@preg_match($user['county'],$keyv)){
				$user['county']=$valv;	
				$config['foundcountry']=true;	
				break;
			}
		}
		
		if (!$user['foundcountry']){
			$config['ignorelist']=true;
		}
		*/
		
		if (1){
		//if (1){
		$user['language_set1']=$language_set1;
		
        $sql = "SELECT description,picture FROM `{$config['table_prefix']}gallery` where carsid='{$user['id']}' order by `order`";
        $result123 = $db->query( $sql );
        $num_rows_gallery = mysqli_num_rows( $result123 );


        $count=1;

        if ( $num_rows_gallery > 0 ) {
        	$user['gallery'].=<<<END
<pictures>
END;

            while ( $var_gallery = mysqli_fetch_assoc( $result123 ) ) {
                                //$img_sz =  @getimagesize( $config['temp'] . $var_gallery['thumbnail'] );
                                if ($var_gallery[picture]!='') {
                                		 //$var_gallery['description'] = iconvnew("ISO-8859-1", "UTF-8", $var_gallery['description']);
                                         $user['gallery'].=<<<END
        <picture>
            <picture_url><![CDATA[{$config['url_path_temp']}{$var_gallery[picture]}]]></picture_url>
            <picture_title><![CDATA[{$var_gallery[description]}]]></picture_title>
        </picture>
END;

                                         $count++;
                                }
            } // while
            
            

$user['gallery'].=<<<END
</pictures>
END;

        }

        //echo $user['description'];
        //echo "\n";
        
        //echo $user['description'] = mysql_iconv($user['description'], "ISO-8859-1", "UTF-8");
        //exit(0);
        
        //$var_gallery['description'] = iconvnew("ISO-8859-1", "UTF-8", $var_gallery['description']);
        /*
        $user['url'] = iconvnew("ISO-8859-1", "UTF-8", $user['url']);
        $user['title'] = iconvnew("ISO-8859-1", "UTF-8", $user['title']);
        $user['type'] = iconvnew("ISO-8859-1", "UTF-8", $user['type']);
        $user['description'] = iconvnew("ISO-8859-1", "UTF-8", $user['description']);
        $user['category'] = iconvnew("ISO-8859-1", "UTF-8", $user['category']);
        $user['address'] = iconvnew("ISO-8859-1", "UTF-8", $user['address']);
        $user['city'] = iconvnew("ISO-8859-1", "UTF-8", $user['city']);
        $user['county'] = iconvnew("ISO-8859-1", "UTF-8", $user['county']);
        $user['county'] = iconvnew("ISO-8859-1", "UTF-8", $user['county']);
        $user['postal'] = iconvnew("ISO-8859-1", "UTF-8", $user['postal']);
        $user['bathrooms'] = iconvnew("ISO-8859-1", "UTF-8", $user['bathrooms']);
        $user['bedrooms'] = iconvnew("ISO-8859-1", "UTF-8", $user['bedrooms']);
        $user['lotsize'] = iconvnew("ISO-8859-1", "UTF-8", $user['lotsize']);
       */
        foreach ( $user as $k=>$v){
        	$user[$k] = iconvnew("ISO-8859-1", "UTF-8", $v);	
        }
        $outxml.=$tpl->replace( $user, "trovitxml_list.xml" );
        //print_R($user);
        //exit(0);
		}
		            } // while
		        }



        break;
}

//exit(0);

$outputtoscreen = <<<END
<?xml version="1.0" encoding="utf-8" ?> 
<trovit>
$outxml
</trovit>
END;
header('Content-type: text/xml');
echo $outputtoscreen;
//echo "<BR><textarea ROWS=12 COLS=130>".$config[global_query]."</TEXTAREA><br>";

function iconvnew($from, $to,$string) {
    // keep current character set values:
    $string = mb_convert_encoding($string, $to);

    return $string;
} 

?>