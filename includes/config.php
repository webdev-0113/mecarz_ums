<?php
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
if (in_array("input_name", $_REQUEST)) {
    $_POST['input_name']=str_replace("/"," ",$_POST['input_name']);
}

$lang['tpl_auto_metacharset']='iso-8859-1';
require "config_write.php";
@ini_set("url_rewriter.tags","");

$config['admin_menu_top1']=array("cars","stats","settings","category","members","paymenthistory","banner","import");

$config["admin_menu_top2"]['cars']=array(
"cars", "sponsored","summary",'logging');
$config["admin_menu_top2"]['settings']=array(
"admin", "rights","settings", "template","backup","tpl","language","ips","fixtools");
$config["admin_menu_top2"]['category']=array(
"category", "make", "model","country",'state',"city","year", "bodytype","transmission","intcolor","extcolor", "features", "news", "faq", "customlinks","homepage"
,'typeofvehicle','doors','classifyemissions','gearbox','climaticcontrol',
);
$config["admin_menu_top2"]['members']=array(
"members",  "sendemail", "sendemailadmin");
$config["admin_menu_top2"]['paymenthistory']=array(
"adprofiles", "payment", "orders","paymenthistory");
$config["admin_menu_top2"]['import']=array(
"import","export","importsettings");
$config["admin_menu_top2"]['banner']=array(
"banner","bannerstats","bannersettings"
);
$config["admin_menu_top2"]['stats']=array(
"stats","statssettings"
);

$config["admin_menu_top3"]=array(
"gallery", "carsfeatures");
$config["admin_menu_top3_default"]="cars";
$config["admin_section_sponsored_field_id"]="carid";

$config['use_br_after_error']="<br />";
$config['use_point_after_error']=" : ";

$config['admin_not_delete_need']=array("template","settings","bannersettings","sendemail","homepage","payment","statssettings");
$config['admin_special_menu']=array("payment");

$config["config_options_in_admin"]=array(
"cars", "sponsored","summary",
"admin", "rights","settings","statssettings","bannersettings", "template","ips",'logging',
"category", "make", "model","country",'state',"city","year","bodytype","transmission","intcolor","extcolor",'typeofvehicle','doors','classifyemissions','gearbox','climaticcontrol', "features", "news", "faq", "customlinks","homepage",
"members",  "sendemail", "sendemailadmin",
"adprofiles", "payment", "orders","paymenthistory");

$config['auto_multiple']['gallery']='3';

//2.1
$config['nrresult']=10; //per page

$config['url_path_editor'] = $config['url_path']."editor_files/";

$config['url_path_temp'] = $config['url_path']."temp/";
$config['url_path_tpl_admin'] = $config['url_path']."tpl/admin/";
$config["temp"]=$config['path']."temp/";// end `/`
$config['tpladmin']=$config['path']."tpl/admin/";// end `/`
$config['tpl']=$config['path']."tpl/";// end `/`
$config['tplini']=$config['path']."tpl/";// end `/`

$config['tplvisit']=$config['path']."tpl/visit/";// end `/`

$config["config_auto_year_start"]=2005;
$config["config_auto_year_finish"]=2010;
$config["config_auto_startday_week"]=1;

$config['tpl_path_admin']=$config['url_path']."tpl/admin/";
$config['tpl_path_visit']=$config['url_path']."tpl/visit/";

$config['cols']=70;
$config['rows']=5;

$config['width_popup']=600;
$config['height_popup']=500;

$IMG_HEIGHT = '120';           # Accepted height of resized image  for small pictures    "*" not resized
$IMG_WIDTH  = '120';           # Accepted width of resized image   for small  pictures

$IMG_HEIGHT_BIG = '500';           # Accepted height of resized image  for small pictures    "*" not resized
$IMG_WIDTH_BIG  = '500';           # Accepted width of resized image   for small  pictures


$IMG_HEIGHT_SIGLA = '*';           # Accepted height of resized image  for small pictures    "*" not resized
$IMG_WIDTH_SIGLA  = '*';           # Accepted width of resized image   for small  pictures


$IMG_HEIGHT_LOGO = '80';           # Accepted height of resized image  for small pictures    "*" not resized
$IMG_WIDTH_LOGO  = '80';           # Accepted width of resized image   for small  pictures


$config['width_vehicle_information_sheet']=850;
$config['height_vehicle_information_sheet']=600;



$config['wywiwyg_DefaultLanguage'] = 'en';
$config['wywiwyg_sizerows'] = 60;
$config['wywiwyg_sizecols'] = 9;

$config["config_sponsored_play"]=true; //true or false
$config['show_category_indetailpage']=true;

$config['auto_multiple']['gallery']=3;
$config['sponsored_shortdescription_cut']=80;

$use_imagecreatetruecolor = true; // these flags enble ImageCreateTrueColor(); ImageCopyResampled();
$use_imagecopyresampled  = true;// I cant test them coz my host doesn't allow these...
$JPG_QUALITY        =        80; // output jpeg quality
$IMG_ROOT = $config['path']."temp/"; //path to tmp directory
                        # Remeber to set proper attributes to that folder. 777 will work :)
##################################################################################################
##################################################################################################
##################################################################################################

$config['emailverif']="^[a-z0-9]+([_.-][a-z0-9]+)*@([a-z0-9]+([.-][a-z0-9]+)*)+\\.[a-z]{2,4}$";
$config["config_auto_year_start_"]=$config["config_auto_year_start"]-1;
$config["config_auto_year_finish_"]=$config["config_auto_year_finish"]+1;
$config["config_date_format"]="M d, Y";
$config["config_date_format_admin"]="M d, Y";

$config["config_price1_format"]=".";
$config["config_price2_format"]=",";

$config["config_separator"]=" :: ";

$config['show_number_of_cars_in_dropdown']=1;

$config['down_payment']="2500";

$config['show_category_indetailpage']=1;
$config['user_same_format_for_all_customlinks']=1;

define( "_Sun", "S" );
define( "_Mon", "M" );
define( "_Tue", "T" );
define( "_Wed", "W" );
define( "_Thu", "T" );
define( "_Fri", "F" );
define( "_Sat", "S" );

define( "_January", "Jan" );
define( "_February", "Feb" );
define( "_March", "Mar" );
define( "_April", "Apr" );
define( "_May", "May" );
define( "_June", "Jun" );
define( "_July", "Jul" );
define( "_August", "Aug" );
define( "_September", "Sep" );
define( "_October", "Oct" );
define( "_November", "Nov" );
define( "_December", "Dec" );
define( "_STARTDAY", "1" );

$config["months_name"] = array( _January, _February, _March, _April, _May, _June,
        _July, _August, _September, _October, _November, _December );
$config["days_in_month"] = array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
$config["days_name"] = array( _Sun, _Mon, _Tue, _Wed, _Thu, _Fri, _Sat );

$config["config_auto_days_name"] = '';
$config["config_auto_month_name"] = '';
foreach($config["days_name"] as $key=>$val){
    $config["config_auto_days_name"].="\"$val\",";
}
$config["config_auto_days_name"].="\"\"";

foreach($config["months_name"] as $key=>$val){
       $config["config_auto_month_name"].="\"$val\",";
}
$config["config_auto_month_name"].="\"\"";
$config["config_search_field"]=array("category","make","model","country",'state',"city","fueltype","year","price","price1","miles","miles1","orderby","method","gallery","features",'typeofvehicle','doors','classifyemissions','gearbox','climaticcontrol','power',"admin","year1",'searchby','active');
$config["config_orderby"]=array("id", "admin", "category", "make", "model", "year", "price","bodytype", "engine", "stereo", "fueltype", "transmission", "intcolor", "extcolor", "miles", "date_add","date_modify");
$config["config_method"] = array("asc","desc");

$config["config_order_menuadmin"]=array(
"cars", "sponsored","summary","|",
"admin", "rights","settings","statssettings", "template","ips",'logging',"language","|",
"category", "make", "model","country",'state',"city","year", "|",
"bodytype","transmission","intcolor","extcolor",'typeofvehicle','doors','classifyemissions','gearbox','climaticcontrol',"|",
"features", "news", "faq", "customlinks","homepage","|",
"members",  "sendemail", "sendemailadmin","|",
"adprofiles", "payment", "orders","paymenthistory");

$config["config_statsinadmin"]=array(
"cars", "sponsored","admin", "members");//"rights", "category", "make", "model", "features", "news", "faq", "customlinks",

$config['aMaxLifeTime']=300; //session time in seconds
$config['admin_number_intop']=10; //number cars in admin in summary

$config['show_one_homepage_lastcars']=true;
$config['no_show_one_homepage_lastcars']=8;
$config['show_lastcars_onallpages']=true;

$config['show_one_homepage_sponsored']=true;
$config['no_show_one_homepage_sponsored']=4;
$config['show_sponsored_onallpages']=true;

$config['admin_section']['cars']['varchar_fields'] = array(  "price", "specialprice", "vin",  "engine", "stereo","miles","stock", "pricemesg","sitetitle" ,"mapto","power" );
$config['admin_section']['cars']['multiplefields'] = array( "pricemesg", "sitetitle" );
$config['admin_section']['cars']['text_fields'] = array("description","shortdescription","metadescription","metakeywords");
$config['admin_section']['cars']['multiplefields_text'] = array( "description","shortdescription","metadescription","metakeywords");
$config['admin_section']['cars']['text_fields_wysiwyg'] = array( "description" );
$config['admin_section']['cars']['dropdown_fields'] = array( "country",'state',"city","year","category", "make", "model","bodytype","transmission","intcolor", "extcolor",'typeofvehicle','doors','classifyemissions','gearbox','climaticcontrol');//
$config['admin_section']['cars']['dropdown_fields_fromlanguage'] = array("fueltype");
$config['admin_section']['cars']['fields_search_cars'] = array("id","stock","model","year", "price", "vin", "bodytype", "engine", "stereo", "transmission","intcolor", "extcolor", "miles",'typeofvehicle','doors','classifyemissions','gearbox','climaticcontrol',);
$config['admin_section']['cars']['fields_view_cars'] = array("id","stock","model","make","year","country",'state',"price","active","daysactive", "date_add","date_modify","noview");

$config['admin_section']['cars']['require_array'] = array("admin", "category", "make", "fueltype","active","model","year");


$config['admin_section']['cars']['onchange'] = array("make" ,"country","state");
$config['admin_section']['cars']['onchange_sub'] = array( "model","city","state");//'state',
$config['admin_section']['cars']['onchange_sub_id']["model"]='makeid';
$config['admin_section']['cars']['onchange_sub_rel']["model"]='make';
$config['admin_section']['cars']['onchange_sub_id']["city"]='stateid';
$config['admin_section']['cars']['onchange_sub_rel']["city"]='state';
$config['admin_section']['cars']['onchange_sub_id']["state"]='countryid';
$config['admin_section']['cars']['onchange_sub_rel']["state"]='country';
/*
$config['admin_section']['cars']['onchange_sub_id']["state"]='makeid';
$config['admin_section']['cars']['onchange_sub_rel']["state"]='country';
*/

$config['admin_section']['cars']['onchangefunction']['shortdescription']='metakeydescription(this)';

$config['frontend_section']['searcharray_simple']=array('fueltype','year','active');
$config['frontend_section']['searcharray_dropdown']=array("admin","country",'state',"city",'model','category','make',"bodytype","transmission","intcolor", "extcolor",'typeofvehicle','doors','classifyemissions','gearbox','climaticcontrol');

$config['frontend_section']['searcharray_fromtofirst']=array('miles');
$config['frontend_section']['searcharray_fromtolast']=array('miles1');

$config['show_nopicture_indetailspage']=false;

$config['price_before']=true;//currency before price

$config['admin_section']['cars']['order']=array();
//"stock", "admin", "category", "make", "model", "year", "price", "specialprice", "pricemesg", "pricemesg1", "vin", "bodytype", "engine", "stereo", "fueltype", "transmission", "shortdescription", "shortdescription1", "description", "description1", "intcolor", "extcolor", "miles", "mapto", "active", "sitetitle", "sitetitle1", "metadescription", "metadescription1", "metakeywords", "metakeywords1", "displaymodel"
$config['default_digits']="0123456789";
$config['default_character']=" -=ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz�A��A�AA�E�E�I��NO��O�OU�U��a��a�aa�e�e�i��i��o�ou�u��0123456789";

$config['varchar_special_maxlength']['cars']['year']=4;
$config['varchar_special_maxlength_goodchars']['cars']['year']=$config['default_digits'];

$config['varchar_special_maxlength']['cars']['price']=11;
$config['varchar_special_maxlength_goodchars']['cars']['price']=$config['default_digits'].".";

$config['varchar_special_maxlength']['cars']['specialprice']=11;
$config['varchar_special_maxlength_goodchars']['cars']['specialprice']=$config['default_digits'].".";

$config['default_size_for_fields_inbackend']=70;

$config['default_fields_in_admin']=array("order");;
$config['default_fieldssize_in_admin']=3;

$config['default_fields_in_searchadmin']=array("id");;
$config['default_fieldssize_in_searchadmin']=5;

$config['mysqldump_pathtosearch']=array('/usr/bin/mysql/','/usr/bin/','/usr/local/bin/','/usr/local/mysql/bin/','c:/apache/mysql/bin/','c:/mysql/bin/','d:/mysql/bin/','e:/mysql/bin/', 'c:/apache2triad/mysql/bin/', 'd:/apache2triad/mysql/bin/', 'e:/apache2triad/mysql/bin/', 'c:/server/mysql/bin/');

$config['useimagesecurity']=TRUE;


$lang['config_price3_format']=range(0,5);
$lang['banned_ip_after_try_to_loggin']=range(1,99);
$lang['add_image_text_position']=range(0,3);
$lang['no_show_one_homepage_lastlisting']=array(2,4,6,8);
$lang['no_show_one_homepage_sponsored']=range(0,6);
$lang['add_image_text_font']=range(1,5);


$config['use_old_sponsored_format']=false;
//$config['site_profile_listing_account']=
$config['delay_How_many_days_this_object_will_be_active']=30;
if ($config['delay_How_many_days_this_object_will_be_active']>0){
	$config['admin_section']['cars']['fields_view_cars'][]='daystoexpire';
}
$config['how_many_days_before_expire_object_send_email']=3;
$config['how_many_days_before_expire_canbe_reactivated']=3;

$config['how_many_days_before_expire_account_send_email']=3;

$config['send_email_to_admin_every_day']=TRUE;

$config['show_only_yes_no_for_active']=TRUE;
$config['lang_Paymenttransaction']='Payment transaction';


function authorizeNet_hmac ($key, $data){
	return (bin2hex (mhash(MHASH_MD5, $data, $key)));
}
function authorizeNet_CalculateFP ($loginid, $x_tran_key, $amount, $sequence, $tstamp, $currency = ""){
	return (authorizeNet_hmac ($x_tran_key, $loginid . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . $currency));
}
//function authorizeNet_InsertFP ($login_id, $x_tran_key, $amount){
//	srand(time());
//	$sequence = rand(1, 1000);
//	$tstamp = time ();
//	$fingerprint = authorizeNet_hmac ($x_tran_key, $login_id . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . $currency);
//	return array($sequence,$tstamp,$fingerprint);
//
//}
	

$config['table_banner'] = $config['table_prefix'].'banner';
$config['table_bannertypes'] = $config['table_prefix'].'bannertypes';
$config['table_bannerdates'] = $config['table_prefix'].'bannerdates';
$config['table_bannerclicks'] = $config['table_prefix'].'bannerclicks';
$config['table_paymenthistory'] = $config['table_prefix'].'paymenthistory';

$config['banner_settings_width']='237';
$config['banner_settings_height']='61';
$config['banner_settings_target']='_blank';
$config['timeformat'] = "F j, Y, H:i";
$config['timeformat_events'] = "d-m-Y";
$config['timeformat_mysql'] = "Y-m-d"; 



$config['import_relation']['cars__stock']=0;
$config['import_relation']['cars__admin']=-1;
$config['import_relation']['cars__category']=1;
$config['import_relation']['cars__make']=2;
$config['import_relation']['cars__model']=3;
$config['import_relation']['cars__year']=-1;
$config['import_relation']['cars__price']=4;
$config['import_relation']['cars__specialprice']=-1;
$config['import_relation']['cars__pricemesg']=-1;
$config['import_relation']['cars__pricemesg1']=-1;
$config['import_relation']['cars__pricemesg2']=-1;
$config['import_relation']['cars__pricemesg3']=-1;
$config['import_relation']['cars__address']=-1;
$config['import_relation']['cars__vin']=6;
$config['import_relation']['cars__bodytype']=7;
$config['import_relation']['cars__engine']=-1;
$config['import_relation']['cars__stereo']=-1;
$config['import_relation']['cars__fueltype']=-1;
$config['import_relation']['cars__transmission']=-1;
$config['import_relation']['cars__shortdescription']=8;
$config['import_relation']['cars__shortdescription1']=-1;
$config['import_relation']['cars__shortdescription2']=-1;
$config['import_relation']['cars__shortdescription3']=-1;
$config['import_relation']['cars__description']=8;
$config['import_relation']['cars__description1']=-1;
$config['import_relation']['cars__description2']=-1;
$config['import_relation']['cars__description3']=-1;
$config['import_relation']['cars__intcolor']=-1;
$config['import_relation']['cars__extcolor']=-1;
$config['import_relation']['cars__miles']=9;
$config['import_relation']['cars__noview']=-1;
$config['import_relation']['cars__active']=-1;
$config['import_relation']['cars__date_add']=-1;
$config['import_relation']['cars__date_modify']=-1;
$config['import_relation']['cars__sitetitle']=3;
$config['import_relation']['cars__sitetitle1']=-1;
$config['import_relation']['cars__sitetitle2']=-1;
$config['import_relation']['cars__sitetitle3']=-1;
$config['import_relation']['cars__metadescription']=10;
$config['import_relation']['cars__metadescription1']=-1;
$config['import_relation']['cars__metadescription2']=-1;
$config['import_relation']['cars__metadescription3']=-1;
$config['import_relation']['cars__metakeywords']=11;
$config['import_relation']['cars__metakeywords1']=-1;
$config['import_relation']['cars__metakeywords2']=-1;
$config['import_relation']['cars__metakeywords3']=-1;
$config['import_relation']['cars__displaymodel']=-1;
$config['import_relation']['cars__delay']=-1;
$config['import_relation']['cars__date_delay']=-1;
$config['import_relation']['cars__unicid']=-1;
$config['import_relation']['cars__emailrenewsent']=-1;
$config['import_relation']['cars__daystoexpire']=-1;
$config['import_relation']['cars__daysactive']=-1;

$config['import_relation_active']=1;

$config['admin_section']['cars']['tablefield_array_options']=array("gallery","carsfeatures","messages");
$config['admin_section']['cars']['tablefield_array_options_val']=array("gallery"=>"carsid","carsfeatures"=>"carsid","messages"=>"carsid");

$config['number_pictures_import']=5;
$config['admin_section']['cars']['notimportfields']=array('daysactive','daystoexpire','emailrenewsent','unicid','date_delay','delay','date_add','date_modify','noview');
$config['explort_features_true']=1;//features true
$config['explort_features_false']=0;//features false

$config['reducesql_getnumber']=false;
$config['chars_to_see_on_list_page']=60;
$config['chars_after_on_list_page']="...";

//stats here
$config['table_admin'] = $config['table_prefix'].'statssettings';
$config['table_visits'] = $config['table_prefix'].'statsvisits';
$config['table_settingsupdate']=$config['table_prefix'].'statssettingsupdate';

$config['config_auto_minutes']=4;
$config['cutlinkssize']=31;

$config['timeformat'] = "D M d, y";
$config['timeformat1'] = "M, y";
$config['timeformat2'] = "H";
$config['timeformat_events'] = "d-m-Y";
$config['timeformat_mysql'] = "Y-m-d";

$config['ignore_domain']=FALSE;

$config['config_auto_http']=$config['url_path'];

$config['use_2_objects_in_list_per_line'] = true;
$config['use_2_objects_in_list_per_line_color'] = 1;

$config['igonorefiledcontact']=array("p","code","submit","input_name");
?>