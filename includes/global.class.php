<?php

function user_ip()
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

class GlobalClass{
//new
    function search($default_tabel , $default_option , $default_option2 , $default_active_search , $default_nrresult , $relation ,  $relation_table , $default_template , $sql_default , $search_fields, $id_, $error=""){
        global $config,$lang;
        global $_REQUEST;
        global $db,$tpl; //class
        global $sql_default_global,$HTTP_SESSION_VARS, $tablefield_array_options, $tablefield_array_options_val;
        global $sql_default_global_from,$right_cookie,$settings_profile; //12 sept 2004 for view all bookings to select from 2 tabels

        $sql_default_global=$sql_default;

        $variable = array (

            "method"=>$_REQUEST['method'],
            "orderby"=>$_REQUEST['orderby'],
            "error"=>$error,
            "nrresult"=>$_REQUEST['nrresult'],
            "page"=>$_REQUEST['page']
        );
        if ($_SESSION['session_page']>0 and $_REQUEST['page']=='') {
            $variable['page'] = $_SESSION['session_page'];
        }
        $_SESSION['session_page'] = $variable['page'];
        if (!is_array($relation)) $relation=array();
        if (!is_array($search_fields)) $search_fields=array();
        if (!is_array($tablefield_array_options)) $tablefield_array_options=array();

        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        while ($tablefield_array_r = mysqli_fetch_array($result)){
            $tablefield_array[]=$tablefield_array_r['Field'];
            $variable[$tablefield_array_r['Field']]=$_REQUEST["input_".$tablefield_array_r['Field']];
        }
        @mysqli_free_result($result);

        if ( ($variable['page']=="")||($variable['page']<1) ) $variable['page']=0;


        $variable['orderby']  = (!in_array($variable['orderby'],$tablefield_array)) ? $tablefield_array[0]:$variable['orderby'];
        if ($default_option2=='') $default_option2="view";
        $param="p=$default_option&amp;o=$default_option2";
        $sql_cond1 = '';
        $sql_table = '';
        if ($default_active_search==1) {
            foreach($tablefield_array as $key=>$val){
                if ($variable[$val]!="") {
                    $param.="&amp;input_$val=".$variable[$val];
                    if (in_array($val,$relation)){
                        $sql_cond1.=" and {$config['table_prefix']}".$relation_table[$val][0].".".$relation_table[$val][1]."={$config['table_prefix']}$default_tabel.$val and {$config['table_prefix']}".$relation_table[$val][0].".".$relation_table[$val][2]." like '%".$variable[$val]."%'";
                        $sql_table.= " , {$config['table_prefix']}".$relation_table[$val][0];
                    }else{
                        if ($_REQUEST['id_back']=='yes' and $val=='id'){
                            $sql_cond1.=" and {$config['table_prefix']}$default_tabel.$val = '".$variable[$val]."'";
                        }else{

                            if (preg_match("/(([0-9]{2})-([0-9]{2})-([0-9]{4}))/",$variable[$val]) and $_REQUEST['o']=='search') {

                                $pattern = "/([0-9]{2})-([0-9]{2})-([0-9]{4})/i";
                                $replacement = "\$3-\$2-\$1";
                                $variable[$val] = preg_replace($pattern, $replacement, $variable[$val]);


                            }
                            $sql_cond1.=" and {$config['table_prefix']}$default_tabel.$val like '%".$variable[$val]."%'";
                        }
                    }
                }
            }
        }

        if ($_REQUEST['f']!=''){
            $param.="&f=".trim($_REQUEST['f']);

            $var_header['searchfor']='f';
            $var_header['searchforvalue']=$_REQUEST['f'];
        }
        if ($_REQUEST['o']=='search'){
            $param.="&o=search";
        }
        if ($_REQUEST['admin']!=''){
            $param.="&admin=".trim($_REQUEST['admin'])."";
            $admin_profile = $this->getprofile( $_REQUEST['admin'],"admin","id" );

            switch ($_REQUEST['admin']){
                default:
                    $sql_default.=" and admin='{$_REQUEST['admin']}' ";
                    break;
            }
            $var_header['error1']=$lang['tpl_auto_Filter'].$lang['tpl_auto_Admin']." ".$admin_profile['username'];
            $var_header['searchfor']='admin';
            $var_header['searchforvalue']=$_REQUEST['admin'];
        }

        switch ($_REQUEST['f']){
            case "picturesstock":
                $listin_array_id_gallery = $this->getarrayid('gallery','carsid',$sqlini=' GROUP BY carsid');
                if (!is_array($listin_array_id_gallery)) $listin_array_id_gallery=array();

                $sql_default.=" AND ( FIND_IN_SET( id, '".implode(",",$listin_array_id_gallery)."' ) > 0 ) ";

                $var_header['error1']=$lang['tpl_auto_Filter'].$lang['tpl_auto_You_have_Picture_cars'];
                break;
            case "inpicturesstock":
                $listin_array_id_gallery = $this->getarrayid('gallery','carsid',$sqlini=' GROUP BY carsid');
                if (!is_array($listin_array_id_gallery)) $listin_array_id_gallery=array();

                $sql_default.=" AND ( FIND_IN_SET( id, '".implode(",",$listin_array_id_gallery)."' ) = 0 ) ";
                $var_header['error1']=$lang['tpl_auto_Filter'].$lang['tpl_auto_You_have_not_Picture_cars'];
                break;
            case "activestock":
                $sql_default.=" and active>=1 ";
                $var_header['error1']=$lang['tpl_auto_Filter'].$lang['tpl_auto_You_have_active_cars'];
                break;
            case "inactivestock":
                $sql_default.=" and active=0 ";
                $var_header['error1']=$lang['tpl_auto_Filter'].$lang['tpl_auto_You_have_inactive_cars'];
                break;
        }


        //$sqlcount = "SELECT COUNT(*) FROM {$config['table_prefix']}$default_tabel $sql_default_global_from $sql_table WHERE 1 $sql_default ";

        $sql = "SELECT {$config['table_prefix']}$default_tabel.* FROM {$config['table_prefix']}$default_tabel $sql_default_global_from $sql_table WHERE 1 $sql_default ";

        $sql.=$sql_cond1;
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows_ini = mysqli_num_rows($result);
        //list($num_rows_ini) = mysqli_fetch_row($result);
        @mysqli_free_result($result);

        if ( ($variable['nrresult']<=0)||($variable['nrresult']=="") )
            $variable['nrresult']=$default_nrresult;

        $param.="&amp;nrresult=".$variable['nrresult'];


        $param_ini = $param;

        $pageoutfin="";
        /*
                       if ($variable['nrresult']=="0") $variable['nrresult']=$num_rows_ini;

                       for($i=0;$i<($num_rows_ini/$variable['nrresult']);$i++){
                           $ii=$i+1;
                           if ($i==0) $pageoutfin.=$lang["tpl_Select_Page"];
                           if ($i==$variable['page']) {
                             $pageoutfin.="[<b>$ii</b>]";
                           }else{
                             $pageoutfin.=" <a href=\"index.php?$param"."&amp;method=".$variable['method']."&amp;orderby=".$variable['orderby']."&amp;page=$i\">$ii</a> \n";
                           }

                       }
        */

//new
        if ($variable['nrresult'] == "0")
            $variable['nrresult'] = $num_rows_ini;
        $start = $variable['page']-5;
        if ($start < 0){
            $start = 0;
        }
        $ends = $variable['page'] + 6;
        if ($ends > $num_rows_ini / $variable['nrresult']){
            $ends = $num_rows_ini / $variable['nrresult'];
        }
        for($i = $start;$i < $ends;$i++){
            $ii = $i + 1;
            if ($start != 0 AND $i == $start){
                $ii = "..." . $ii;
            }
            if ($ends < $num_rows_ini / $variable['nrresult'] AND $i == $ends-1){
                $ii = $ii . "...";
            }
            if ($i == $start) $pageoutfin .= "<b>" . $lang["tpl_Select_Page"] . "</b>";
            if ($i==$variable['page']) {
                $pageoutfin.="[<b>$ii</b>]";
            }else{
                $pageoutfin.=" <a href=\"index.php?$param"."&amp;method=".$variable['method']."&amp;orderby=".$variable['orderby']."&amp;page=$i\">$ii</a> \n";
            }
        }

//end
        $variable['method'] = ($variable['method']=="asc" or $variable['method']=='') ? "desc" : "asc";
        $codesort[$variable['orderby']]=($variable['method']=="asc") ? "<font class=\"sign\">&uarr;</font>": "<font class=\"sign\">&darr;</font>";

        $param.="&amp;orderby=".$variable['orderby'];


        $page_ini=$variable['page'];
        $variable['page']=$variable['page']*$variable['nrresult'];
        $sql.= " ORDER BY {$config['table_prefix']}$default_tabel.{$variable['orderby']} {$variable['method']} LIMIT {$variable['page']},{$variable['nrresult']}";
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);

        $i=0;
        $out1="";


        if (($num_rows>0)){
            $pageout=$pageoutfin;
            while ($var = mysqli_fetch_assoc($result)){


                $var['chk_box']="<input type=checkbox name=options_array[{$var[$id_]}] value=\"1\">";
                $colspan = 1;
                $id_ini = $var[$id_];
                if ($default_tabel=='admin'){

                    $number = $this->getnumrows($var['id'], "cars", 'admin');

                    $var['email'].= " (<a href='index.php?p=cars&admin=".$var['id']."&page=0'>".$number."</a>) ";

                }
                if ($right_cookie['view_all_cars']) {
                    if ($default_tabel=='admin' and $settings_profile['adprofiles']!=0){

                        $var['daystoexpire'].= " (<a href='index.php?p=admin&o=view&o1=reset&id=".$var[$id_]."'>".$lang['tpl_auto_restart']."</a>) ";
                        //$var['daystoexpire'].= " <form action='index.php'><input type='hidden' name='p' value='admin'><input type='hidden' name='o' value='view'><input type='hidden' name='o1' value='activatem'><input type='hidden' name='id' value='".$var[$id_]."'>(<a href='index.php?p=admin&o=view&o1=activatem&id=".$var[$id_]."'>".$lang['tpl_auto_activatemanual']."</a>) ";

                    }
                }

                if ($default_tabel=='cars'){

                    $var_gallery = $this->getprofile_order($var['id'], "gallery", "order", "carsid");

                    if ($var_gallery['thumbnail'] == ""){
                        $var_gallery['thumbnail'] = $settings_profile['thumbnail'];
                    }

                    $thumbnailtemp = $config['url_path_temp'] . $var_gallery['thumbnail'];

                    $var['galleryimage'] = "<img src=\"{$thumbnailtemp}\">\n";
                }


                if ($default_tabel=='sponsored'){

                    global $settings_profile;
                    $var_gallery = $this->getprofile_order( $var['carid'], "gallery", "order", 'carsid' );

                    if ($var_gallery['thumbnail']==""){
                        $var_gallery['thumbnail']=$settings_profile['thumbnail'];
                    }
                    if ($var_gallery['thumbnail']!=''){
                        $var['galleryimage']="<img src=\"".$config['url_path_temp'] . $var_gallery['thumbnail']."\">\n<br>";
                    }
                }

                if ($default_tabel=='sponsored'){

                    $cars_profile = $this->getprofile($var['carid'], "cars", 'id');

                    $make_profile = $this->getprofile($cars_profile['make'], "make", 'id');
                    $usertemp1['make'] = $make_profile["name{$language_set}"];

                    $model_profile = $this->getprofile($cars_profile[model], "model", 'id');
                    $usertemp1['model'] = $model_profile["name{$language_set}"];

                    $var['carid']=$cars_profile[id]." - ".$usertemp1['make']." - ".$usertemp1['model']." - ".$cars_profile['year'];
                    $nocond=true;
                }
                foreach($tablefield_array_options as $key=>$val){
                    if (!$config['reducesql_getnumber']){
                        $number_temp = $this->getnumrows($var[$id_],$val,$tablefield_array_options_val[$val]);
                        $number_temp="<B>[{$number_temp}]</B>";
                    }
                    $var[$val]="<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"index.php?p=$val&amp;o=view&amp;oid={$var[$id_]}&amp;page=0\">".$lang["tpl_auto_array_option_$val"]."{$number_temp}</a>";
                }
                foreach($tablefield_array as $key=>$val){
                    if ($val=="price"){
                        $var[$val]=nr_afis($var[$val]);
                    }

                    if (in_array($val,$relation)  and !$nocond){
                        $profile_seconde=$this->getprofile($var[$val],$relation_table[$val][0],$relation_table[$val][1]);
                        if ($relation_table[$val][3]==1){
                            $id_out = " (".$lang["tpl_auto_".$relation_table[$val][0]."id"].": ".$var[$val].") ";
                        }else{
                            $id_out='';
                        }
                        //$var[$val] = $profile_seconde[$relation_table[$val][2]].$id_out;
                        $var[$val] = ($profile_seconde[$relation_table[$val][2]]=='')?$var[$val]:$profile_seconde[$relation_table[$val][2]].$id_out;
                    }
                }
                foreach($tablefield_array as $key=>$val){
                    if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/",$var[$val])) {
                        if ($var[$val] == '0000-00-00') {
                            $var[$val]="-";
                        }else{
                            $var[$val]=dateformat($config["config_date_format_admin"],strtotime($var[$val]));;
                        }
                    }
                }
                if ($default_active_search==1) {
                    foreach($tablefield_array as $key=>$val){
                        if ($val=="active" or $val=="approve" or $val=="success"){
                        }else
                            if ($variable[$val]!="") {
                                $var[$val]=preg_replace ("({$variable[$val]})", $lang["foundstyle"], $var[$val]);
                            }
                    }
                }

                $variable['method1'] = ($variable['method']=="asc") ? "desc" : "asc";

                if ($i%2) $var['class_temp']="class_temp1";
                else $var['class_temp']="class_temp2";
                $out_fields = '';
                foreach($tablefield_array as $key=>$val){
                    if ($val=="active" or $val=="approve" or $val=="success"){
                        $var[$val]="&nbsp;&nbsp;&nbsp;<img src=\"../images/active{$var[$val]}.gif\" border=0>";
                    }elseif ($val=="thumbnail"){
                        $var[$val]="<img src=\"../temp/{$var[$val]}\" border=0>";
                    }
                    if (in_array($val,$search_fields)) {
                        if ($default_tabel!='admin' and !preg_match("/\.jpg/",$var[$val]) and strlen($var[$val])>$config['chars_to_see_on_list_page']){
                            $var[$val]=substr($var[$val],0,$config['chars_to_see_on_list_page']).$config['chars_after_on_list_page'];
                        }
                        $var_fields['fields']=$var[$val];
                        $out_fields.=$tpl->replace($var_fields,"global_search_rows_fields.html");
                        if ($val=='id' and ( $default_tabel=='cars' or $default_tabel=='sponsored')){
                            $var_fields['fields']=$var['galleryimage'];
                            $out_fields.=$tpl->replace($var_fields,"global_search_rows_fields.html");
                            $colspan++;
                        }
                        $colspan++;
                    }
                }
                $var['edit']=" ";
                if ($config['config_auto_oid']!=''){
                    $oidnew="&amp;oid={$config['config_auto_oid']}";
                }else{
                    $oidnew="";
                }
                foreach($tablefield_array_options as $key=>$val){
                    $var_fields['fields']=$var[$val];

                    $var['edit'].=$var_fields['fields'] . "  ";
                }
                $var['edit'] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"index.php?p=$default_option&amp;o=edit&amp;id={$id_ini}{$oidnew}\" class=\"edit\">".$lang["tpl_Edit"]."</a>  ";
                $var['edit'] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"index.php?p=$default_option&amp;o=see&amp;id={$id_ini}{$oidnew}\" class=\"edit\">".$lang["tpl_auto_See"]."</a>  ";
                if ($default_tabel=='cars' and $lang["tpl_auto_See_in_site"]!=''){
                    $var['edit'] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"../index.php?p=details&amp;id={$id_ini}\" class=\"edit\" target='_blank'>".$lang["tpl_auto_See_in_site"]."</a>  ";
                    $var['edit'] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"../index.php?p=vehicle_information2&amp;id={$id_ini}\" class=\"edit\" target='_blank'>".$lang["tpl_auto_See_in_site1"]."</a>  ";
                }

                $var['colspan'] = $colspan;
                $var['option_fields']=$out_fields;
                $var['ii']=$i;
                $out1.=$tpl->replace($var,"global_search_rows.html");

                unset($var);
                unset($out_fields);
                unset($var_fields);
                $out_fields="";
                $var_fields="";
                $i++;
            }
            @mysqli_free_result($result);
            $vartemp['nr']=$num_rows_ini;
            $vartemp['nr1']=$variable['page']+1;
            $vartemp['nr2']=$variable['page']+$num_rows;
            if ($num_rows < $nrresult) $vartemp['nr2']=$page+$num_rows;
            $resultnr=$lang["msg_result_nr"];

            foreach ($vartemp as $key => $value) {
                $resultnr=str_replace("{{".$key."}}",$value,$resultnr);
            }
            $var_header['resultnr']=$resultnr;
            $var_header['pageout']=$pageout;
            unset($vartemp);

        }else {
            $condno=true;
        }
        $out_fields="";
        $nr_fields=0;
        foreach($tablefield_array as $key=>$val){
            if (in_array($val,$search_fields)) {
                if ($lang['tabel_'.$default_option]['short_'.$val]!='' ) {
                    $lang['tabel_'.$default_option][$val] = $lang['tabel_'.$default_option]['short_'.$val];
                }
                $var_fields['fields']="<a href=\"index.php?$param_ini&amp;orderby=$val&amp;page=$page_ini&amp;method=".sort_order($val,$variable['orderby'],$variable['method'])."\"><B>".ucfirst ($lang['tabel_'.$default_option][$val])."</B></a>".$codesort[$val]."";
                $var_fields['class'] = "";
                if ($key==$id_) {
                    $var_fields['width']="5%";
                }elseif (count($search_fields)==1) {
                    $var_fields['width'] = "100%";
                }else{
                    @$var_fields['width']=(round(85/(count($search_fields)+count($tablefield_array_options)-1))) ;
                    $var_fields['width'] .= "%";
                }
                $out_fields.=$tpl->replace($var_fields,"global_search_rows_fields.html");
                $nr_fields++;
                if ($val=='id' and ( $default_tabel=='cars' or $default_tabel=='sponsored')){
                    $var_fields['fields']=$lang["tpl_auto_gallery"];
                    $out_fields.=$tpl->replace($var_fields,"global_search_rows_fields.html");
                    $nr_fields++;
                }
            }
        }
        /*
        foreach($tablefield_array_options as $key=>$val){
                                                                  $var_fields['fields']=$lang["tpl_auto_array_option_$val"];
           if ($key==$id_) {
                   $var_fields['width']="5%";
           }else{
                   $var_fields['width']=(round(85/(count($search_fields)+count($tablefield_array_options)-1))) ;
                   $var_fields['width'] .= "%";
           }
                             $out_fields.=$tpl->replace($var_fields,"global_search_rows_fields.html");
           $nr_fields++;
        }*/
        if ($nr_fields>5) {
            $var_header['width']="99%";
        }else{
            $nr_fields_width = $nr_fields*20;
            $var_header['width']="$nr_fields_width%";
        }


        $var_header["p"]="$default_option";
        $var_header["o"]="delete";
        $var_header["error"]=$error;
        $var_header["auto_option"]=$lang["tpl_auto_$default_option"] ;
        $var_header["option_fields"]=$out_fields;
        $var_header["colspan"] = 1 + $nr_fields;
        $var_header['explain']=$lang["tpl_auto_explain_$default_option"] ;
        $var_header['class'] = "";
        if ($condno and $default_option!='paymenthistory'){
            $out1="<tr><td colspan=\"".$var_header["colspan"]."\" align=\"center\"><div class=\"error\">".$lang["error_nosearch_result"]." <a href=\"index.php?p={$default_option}&amp;o=add&oid={$_REQUEST[oid]}\" class=\"error\">".$lang['tpl_auto_click_here_to_add']."</a></div></td></tr>";

            $temp="";
        }
        $var_header['result']=$out1;
        if ($config['config_second_multiple_show']==1) {
            $var_header['multiple_options'] = $tpl->replace($var_header,"global_multiple_options.html");
        }
        if ($config['config_sold_multiple_show']==1) {
            $var_header['multiple_options'] .= $tpl->replace($var_header,"global_multiple_options_sold.html");
        }

        if (!is_array($config['admin_not_delete_need'])) $config['admin_not_delete_need']=array();
        if (in_array($default_option,$config['admin_not_delete_need'] ) ) {
            $out=$tpl->replace($var_header,"global_search_rows_result_second.html");
        }else{
            $out=$tpl->replace($var_header,"global_search_rows_result.html");
        }
        return $out;
    }

    function search1($default_tabel,$default_option,$fileds_not_show=array()){
        global $config,$lang;
        global $_REQUEST;
        global $db,$tpl; //class

        $variable = array (

            "method"=>$_REQUEST['method'],
            "orderby"=>$_REQUEST['orderby'],
            "error"=>$error,
            "nrresult"=>$_REQUEST['nrresult'],
            "page"=>$_REQUEST['page']
        );

        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        $i=0;
        if (!is_array($config['admin_section'][$default_option]['radio_fields']))$config['admin_section'][$default_option]['radio_fields']=array();
        $out1 = '';
        $out_temp1 = '';
        while ($tablefield_array_r = mysqli_fetch_array($result)){
            $tablefield_array[]=$tablefield_array_r['Field'];
            if (in_array($tablefield_array_r['Field'],$fileds_not_show)){
                $variable[$tablefield_array_r['Field']]=$_REQUEST[$tablefield_array_r['Field']];

                if ($i%2) $var['class_temp']="class_temp1";
                else $var['class_temp']="class_temp2";

                $var['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                $var['tpl_input_name']="input_".$tablefield_array_r['Field'];
                $var['tpl_input_name_val']=$_REQUEST[$var['tpl_input_name']];

                //$out1.=$tpl->replace($var,"global_search_option.html");
                if (in_array($tablefield_array_r['Field'],$config['default_fields_in_searchadmin'])){
                    $var['goodchars'] = $config['default_digits'];
                    $var['size'] = $config['default_fieldssize_in_searchadmin'];
                    $var['maxlength'] = $config['default_fieldssize_in_searchadmin'];
                }
                if (!is_array($config['admin_section'][$default_option]['date_fields'])) $config['admin_section'][$default_option]['date_fields']=array();
                if (in_array($tablefield_array_r['Field'],$config['admin_section'][$default_option]['date_fields'])){
                    /*
                                    $var['tpl_input_day']="input_".$tablefield_array_r['Field']."_day";
                             $var['tpl_input_month']="input_".$tablefield_array_r['Field']."_month";
                             $var['tpl_input_year'] = "input_".$tablefield_array_r['Field']."_year";
                             $var['tpl_input_day_val'] = $this->days($_POST[$var['tpl_input_day']]);
                             $var['tpl_input_month_val'] = $this->months($_POST[$var['tpl_input_month']]);
                             $var['tpl_input_year_val'] = $this ->years($_POST[$var['tpl_input_year']]);
                             */
                    $var['tpl_input_date']="input_".$tablefield_array_r['Field'];
                    $var['tpl_input_date_val']=$_POST["input_".$tablefield_array_r['Field']];
                    $out1.=$tpl->replace($var,"global_add_date.html");
                }else
                    if (in_array($tablefield_array_r['Field'],$config['admin_section'][$default_option]['radio_fields'])){
                        $radio_explode=explode("|#",$config['admin_section'][$default_option]['radioval'][$tablefield_array_r['Field']]);
                        $var_temp_val['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                        foreach($radio_explode as $key=>$val){
                            $var_temp_val['tpl_input_name']="input_".$tablefield_array_r['Field'];
                            $value_radio = explode("|",$val);
                            if ($value_radio[1]=='') {
                                $value_radio[1]=$value_radio[0];
                            }
                            $var_temp_val['tpl_input_name_val']=$value_radio[1];
                            $var_temp_val['tpl_input_name_value']=$value_radio[0];
                            if ($_POST["input_".$tablefield_array_r['Field']]==""){
                                $_POST["input_".$tablefield_array_r['Field']]=$value_radio[0];
                            }
                            if ($_POST["input_".$tablefield_array_r['Field']]==$value_radio[0]){
                                $var_temp_val['checked']="checked";
                            }
                            $out_temp1.=$tpl->replace($var_temp_val,"global_add_radio_val.html");
                            unset($var_temp_val);
                        }
                        $var['tpl_input_name_val']=$out_temp1;
                        $out_temp1="";
                        $out1.=$tpl->replace($var,"global_add_radio.html");
                    }else
                        if ($var['maxlength']!='' and $var['goodchars']!=''){
                            $out1.=$tpl->replace($var,"global_search_option_special.html");
                        }else{
                            if ($i>=2){
                                $var['startdivsearch']=' style="display: none;"';
                            }
                            $var['i']=$i;
                            $out1.=$tpl->replace($var,"global_search_option.html");
                        }
                $i++;
                unset($var);
            }
        }
        @mysqli_free_result($result);

        $var_header['option']=$out1;
        $var_header["p"]="$default_option";
        $var_header["o"]="search";
        if ($i>2){
            $var_header['i']=$i;
        }else{
            $var_header['startdivsearch']=' style="display: none;"';
        }
        $out=$tpl->replace($var_header,"global_search.html");
        return $out;
    }
    function deletemultiple($user_delete,$default_tabel,$msg1,$msg2,$camp,$filearray,$id_){
        global $config,$tpl,$lang;
        global $db; //class
        $out="";
        if ($user_delete=="") $user_delete=array();
        foreach ($user_delete as $key => $value) {
            $profile=$this->getprofile($key,$default_tabel,$id_);
            $result=$this->delete_id($key, $default_tabel, $filearray, $id_);
            $error=($result) ? $msg1 : $msg2;
            $out.=preg_replace ("({{name}})", "<b>".$profile[$camp]."</b>", $error);
        }
        if ($out=="")
            $out=$lang['tpl_auto_There_was_a_problem'].$lang["error8"]." " .$lang['tpl_auto_No_checkbox_selected_Please_select_at_least_one_checkbox_and_try_again'];
        return $out;
    }
    function delete_id($id,$default_tabel,$filearray,$id_){
        global $config,$right_cookie, $_COOKIE;
        global $db; //database
        if (!is_array($filearray)) {
            $filearray=array();
        }
        $profile=$this->getprofile($id,$default_tabel,$id_);

        if ($default_tabel=='admin'){

            $number = $this->getnumrows($profile['id'], "cars", 'admin');

            if ($number>0) return false;

        }

        foreach ($profile as $key=>$val){
            if (in_array($key,$filearray)){
                @unlink($config["temp"].$val);
            }
        }
        /*
               if (!$right_cookie['view_all_listing']) {
                      if ($profile[admin]!=$_COOKIE['id_cookie']) {
                          return false;
                      }
               }
               */

        if ($default_tabel=='rights' AND $id==1){
            return false;
        }
        if ($default_tabel=='admin' AND $id==1){
            return false;
        }

        if ($default_tabel=='clients' AND $id==1){
            return false;
        }
        if ($default_tabel=='gallery'){
            @unlink($config["temp"].$profile['picture']);
            @unlink($config["temp"].$profile['thumbnail']);
        }
        if ($default_tabel=='cars'){
            $sql = "SELECT `picture`,`thumbnail` FROM `{$config['table_prefix']}gallery` WHERE `carsid`='$id'";
            $result = $db->query($sql);
            $num_rows = mysqli_num_rows($result);
            if ($num_rows>0){
                while ($var = mysqli_fetch_assoc($result)){
                    @unlink($config["temp"].$var['picture']);
                    @unlink($config["temp"].$var['thumbnail']);
                }
                @mysqli_free_result($result);

            }
            $sql = "delete from `{$config['table_prefix']}gallery` WHERE `carsid`='$id'";
            $result = $db->query($sql);
            $sql = "delete from `{$config['table_prefix']}carsfeatures` WHERE `carsid`='$id'";
            $result = $db->query($sql);
            $sql = "delete from `{$config['table_prefix']}sponsored` WHERE `carid`='$id'";
            $result = $db->query($sql);
            $sql = "delete from `{$config['table_prefix']}messages` WHERE `carsid`='$id'";
            $result = $db->query($sql);
        }

        $sql = "delete from `{$config['table_prefix']}$default_tabel` WHERE `$id_`='$id'";
        $result = $db->query($sql,__FILE__,__LINE__);
        if ($default_tabel!='logging'){

            global $_COOKIE,$lang,$_REQUEST;
            $row = array(
                "admin"=>$_COOKIE['id_cookie'],
                "action"=>$lang['logging']['delete']." ".$lang["tpl_auto_".$_REQUEST['p']]." ( ".$id.": ".$profile[$config['admin_section'][$_REQUEST['p']]['field_name_for_delete']].") ",
                "sql"=>"$sql"
            );

            addlogging( $row );
        }

        if ($result){
            /*
                  $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel`";
                  $result = $db->query($sql,__FILE__,__LINE__);
                  $num_rows_ini = mysqli_num_rows($result);
                  @mysqli_free_result($result);
                  if ($num_rows_ini<1){
                      $sql = "TRUNCATE TABLE `{$config['table_prefix']}$default_tabel`";
                      @$result = $db->query($sql,__FILE__,__LINE__);
                  }
                  */
            return true;
        }else return false;
    }
    function getprofile($id,$default_tabel,$id_,$sql1=""){
        global $config;
        global $db,$_REQUEST; //database

        if (!preg_match("/limit/",$sql1)){
            $sql1.=" LIMIT 1";
        }
        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` WHERE `$id_`='$id' $sql1";
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        if ($num_rows>0){
            $user = mysqli_fetch_assoc($result);
            $config['profilesmem'][$default_tabel][$id]=$user;
            $config['profilesmem'][$default_tabel][$id]['saved']=1;
            @mysqli_free_result($result);
            return ($user);
        }else return false;

    }
    function getprofilefirst($default_tabel,$sql1=""){
        global $config;
        global $db; //database
        if (!preg_match("/limit/",$sql1)){
            $sql1.=" LIMIT 1";
        }
        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` WHERE 1 $sql1";
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        if ($num_rows>0){
            $user = mysqli_fetch_assoc($result);
            @mysqli_free_result($result);
            return ($user);
        }else return false;
    }
    function getprofile1($id,$id1,$default_tabel,$id_,$id1_){
        global $config;
        global $db; //database
        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` WHERE `$id_`='$id' AND `$id1_`='$id1'";
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        if ($num_rows>0){
            $user = mysqli_fetch_assoc($result);
            @mysqli_free_result($result);
            return ($user);
        }else return false;
    }

    function getnumrows($id,$default_tabel,$id_,$sql=''){
        global $config;
        global $db; //database
        $sql = "select count(*) from `{$config['table_prefix']}$default_tabel` WHERE `$id_`='$id' $sql";
        //echo "<BR>";
        $result = $db->query($sql,__FILE__,__LINE__);
        //$user = mysqli_fetch_assoc($result);
        list($num_rows) = mysqli_fetch_row($result);
        //$num_rows = mysqli_num_rows($result);
        @mysqli_free_result($result);
        return intval($num_rows);
    }
    function getnumber($default_tabel,$sql=''){
        global $config;
        global $db; //database
        $sql = "select count(*) from `{$config['table_prefix']}$default_tabel` WHERE 1 $sql";
        //echo "<BR>";
        $result = $db->query($sql,__FILE__,__LINE__);
        list($num_rows) = mysqli_fetch_row($result);
        @mysqli_free_result($result);
        return $num_rows;
    }
    function getdropdown($id,$default_tabel,$orderby,$id_,$name_,$number=0,$sqlini="",$cardatabase="cars"){
        global $config;
        global $db; //database
        /*
               $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
               $result = $db->query($sql,__FILE__,__LINE__);
               while ($tablefield_array_r = mysqli_fetch_array($result)){
                      $tablefield_array[]=$tablefield_array_r['Field'];
               }
               @mysqli_free_result($result);
               $orderby  = (!in_array($orderby,$tablefield_array)) ? $tablefield_array[0]:$orderby;
			   */
        /*
         	   if (!is_array($config["javascriptprofiles"][$default_tabel.'javascript'])) {
         	   	$config["javascriptprofiles"][$default_tabel.'javascript']=array();
         	   }
         	   if (count($config["javascriptprofiles"][$default_tabel.'javascript'])>0 AND $sqlini==''){
         	   	   $fruits=$config["javascriptprofiles"][$default_tabel.'javascript'];
         	   	   asort($fruits);
				   reset($fruits);
	         	   foreach ($fruits as $user){
                        $out .= "<option";
                        $out .= ($user[$id_] == $id ) ? " selected": "";
                        if ($number==1 AND $config['show_number_of_cars_in_dropdown']==1) {
                           $number_out = $this->getnumrows($user[$id_],$cardatabase,$default_tabel);
                           $number_out = " ({$number_out})";
                        }else{
                           $number_out="";
                        }
                        $out .= " value='".$user[$id_]."'>".$user[$name_]."{$number_out}</option>\n";
	         	   }
	         	   return ($out);
         	   }
         	   */
        $sql = "SELECT `$id_`,`$name_` FROM `{$config['table_prefix']}$default_tabel` WHERE 1 $sqlini ORDER BY $orderby";//GROUP BY $id_

        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        $out = '';
        if ($num_rows>0){
            while ($user = mysqli_fetch_assoc($result)){
                $out .= "<option";
                $out .= ($user[$id_] == $id ) ? " selected": "";
                if ($number==1 AND $config['show_number_of_cars_in_dropdown']==1) {
                    $number_out = $this->getnumrows($user[$id_],$cardatabase,$default_tabel);
                    $number_out = " ({$number_out})";
                }else{
                    $number_out="";
                }
                $out .= " value='".$user[$id_]."'>".$user[$name_]."{$number_out}</option>\n";
            }
            @mysqli_free_result($result);
            return ($out);
        }else return false;
    }
    function getdropdownspon($id,$default_tabel,$orderby,$id_,$name_,$number=0,$sqlini="",$cardatabase="cars"){
        global $config;
        global $db; //database
        /*
               $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
               $result = $db->query($sql,__FILE__,__LINE__);
               while ($tablefield_array_r = mysqli_fetch_array($result)){
                      $tablefield_array[]=$tablefield_array_r['Field'];
               }
               @mysqli_free_result($result);
               $orderby  = (!in_array($orderby,$tablefield_array)) ? $tablefield_array[0]:$orderby;
               */

        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` WHERE 1 $sqlini GROUP BY $id_ ORDER BY $orderby";

        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        $out = '';
        if ($num_rows>0){
            while ($user = mysqli_fetch_assoc($result)){
                $out .= "<option";
                $out .= ($user[$id_] == $id ) ? " selected": "";
                $make_profile = $this->getprofile($user['make'], "make", 'id');
                $user['make'] = $make_profile["name{$language_set}"];

                $model_profile = $this->getprofile($user['model'], "model", 'id');
                $user['model'] = $model_profile["name{$language_set}"];
                $out .= " value='".$user[$id_]."'>".$user[$id_]." - ".$user[stock]." - ".$user['make']." - ".$user['model']." - ".$user['year']."</option>\n";
            }
            @mysqli_free_result($result);
            return ($out);
        }else return false;
    }
    function getfromdropdown($dropdown,$id){
        global $config;
        global $db; //database

        $temp=explode("</option>\n",$dropdown);
        if (!is_array($temp)) $temp=array();
        foreach ($temp as $key=>$val){
            if (preg_match("/selected/",$val)){
                $temp1=explode("'>",$val);
                return ($temp1[1]);
            }
        }
        $temp1=explode("'>",$temp[0]);
        return ($temp1[1]);
        /*
               $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
               $result = $db->query($sql,__FILE__,__LINE__);
               while ($tablefield_array_r = mysqli_fetch_array($result)){
                      $tablefield_array[]=$tablefield_array_r['Field'];
               }
               @mysqli_free_result($result);
               $orderby  = (!in_array($orderby,$tablefield_array)) ? $tablefield_array[0]:$orderby;
               */
        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` GROUP BY $id_ ORDER BY $orderby";
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        if ($num_rows>0){
            while ($user = mysqli_fetch_assoc($result)){
                $out .= "<option";
                $out .= ($user[$id_] == $id ) ? " selected": "";
                $out .= " value='".$user[$id_]."'>".$user[$name_]."</option>\n";
            }
            @mysqli_free_result($result);
            return ($out);
        }else return false;
    }
    function getdropdown_array_car($val,$array){
        global $lang;
        $out = '';
        if (!is_array($array)) {
            $array=array();
        }
        foreach ($array as $key=>$val_){
            if ($val==$val_) {
                $selected=" selected";
            }else{
                $selected="";
            }
            $out .= "<option$selected value='$val_'>".$lang['tabel_cars'][$val_]."</option>\n";
        }
        return $out;
    }
    function getdropdown_arrayid($val,$array){
        if (!is_array($array)) {
            $array=array();
        }
        $out = '';
        foreach ($array as $key=>$val_){
            if ($val==$key) {
                $selected=" selected";
            }else{
                $selected="";
            }
            $out .= "<option$selected value='$key'>$val_</option>\n";
        }
        return $out;
    }

    function getdropdownarrayval( $id, $array )
    {
        global $config;
        global $db; //database
        $out = '';
        if (!is_array($array)) $array=array();
        foreach  ( $array as $key=>$val )
        {
            $key=trim($key);
            $out .= "<option";
            $out .= ( $key == $id ) ? " selected": "";
            $out .= " value='" . $key . "'>" . $val . "</option>\n";
        }
        return ( $out );
    }
    function add($default_tabel,$default_option,$default_option2,$varchar,$text,$file,$dropdown,$dropdownval,$radio,$radioval,$checkbox,$password,$date_fields, $error=""){
        global $config,$lang;
        global $db,$tpl,$_POST,$datetime_fields,$text_fields_wysiwyg,$settings_profile; //class
        global $javascript_special,$dropdownval_onchange,$datetime_fields,$_COOKIE;
        $var_initial =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "redirect"=>$redirect,
            "error"=>$error
        );
        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        $i=0;
        if (!is_array($varchar)) $varchar=array();
        if (!is_array($text)) $text=array();
        if (!is_array($file)) $file=array();
        if (!is_array($dropdown)) $dropdown=array();
        if (!is_array($date_fields)) $date_fields=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($text_fields_wysiwyg)) $text_fields_wysiwyg=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($radio)) $radio=array();
        while ($tablefield_array_r = mysqli_fetch_array($result)){

            $tablefield_array[]=$tablefield_array_r['Field'];

        }
        @mysqli_free_result($result);
        $tablefield_arraynew=array();
        if (!is_array($config['admin_section'][$default_tabel]['order']))$config['admin_section'][$default_tabel]['order']=array();
        foreach ($config['admin_section'][$default_tabel]['order'] as $key=>$val){
            if (in_array($val,$tablefield_array)){
                $tablefield_arraynew[]=$val;
            }
        }
        foreach ($tablefield_array as $key=>$val){
            if (!in_array($val,$tablefield_arraynew)){
                $tablefield_arraynew[]=$val;
            }
        }
        //print_r($tablefield_array);
        $out1 = '';
        $out_temp1 = '';
        $outtemp123 = '';
        foreach ($tablefield_arraynew as $kkk=>$vvv){
            $tablefield_array_r['Field']=$vvv;
            $aaate = "\"$vvv\", ";
            $j=$i;
            if ($i%2) $var['class_temp']="class_temp1";
            else $var['class_temp']="class_temp2";

            $var['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
            $var['tpl_input_name']="input_".$tablefield_array_r['Field'];
            $var['tpl_input_name_val']=$_POST["input_".$tablefield_array_r['Field']];

            if (in_array($tablefield_array_r['Field'],$varchar)){
                $var['maxlength']=$config['varchar_special_maxlength'][$tablefield_array_r['Field']];
                $var['size']=($config['varchar_special_maxlength'][$tablefield_array_r['Field']]>$config['default_size_for_fields_inbackend'])?$config['default_size_for_fields_inbackend']:$config['varchar_special_maxlength'][$tablefield_array_r['Field']];                         $var['size']=$config['varchar_special_maxlength'][$tablefield_array_r['Field']];
                $var['goodchars']=$config['varchar_special_maxlength_goodchars'][$tablefield_array_r['Field']];
                if (in_array($tablefield_array_r['Field'],$config['default_fields_in_admin'])){
                    $var['goodchars'] = $config['default_digits'];
                    $var['size'] = $config['default_fieldssize_in_admin'];
                    $var['maxlength'] = $config['default_fieldssize_in_admin'];
                }
                $var['onchangefunction']=$config['admin_section']['cars']['onchangefunction'][$tablefield_array_r['Field']];
                if ($var['maxlength']!='' AND $var['goodchars']!=''){
                    $out1.=$tpl->replace($var,"global_add_varcharspecial.html");
                }else{
                    $out1.=$tpl->replace($var,"global_add_varchar.html");
                }
            }elseif (in_array($tablefield_array_r['Field'],$text)){
                $var['rows']=$config['rows'];
                $var['cols']=$config['cols'];

                if (in_array($tablefield_array_r['Field'],$text_fields_wysiwyg) AND $settings_profile['wysiwyg']==1) {
                    $oFCKeditor = new FCKeditor($var['tpl_input_name']) ;
                    $oFCKeditor->BasePath = $config['wywiwyg_editor'] ;                // '/FCKeditor/' is the default value so this line could be deleted.
                    $oFCKeditor->DefaultLanguage = $config['wywiwyg_DefaultLanguage'];
                    $oFCKeditor->Value = $var['tpl_input_name_val'] ;

                    if ($default_option=="cars") {
                        $oFCKeditor->ToolbarSet = 'Description' ;
                    }
                    $var['wywiwyg_value'] = $oFCKeditor->CreateFCKeditor( $var['tpl_input_name'], $var['cols']*$config['wywiwyg_sizecols'],$var['rows']*$config['wywiwyg_sizerows'] ) ;

                    $file_text_use = "global_add_wywiwyg.html";
                }else{
                    $var['wywiwyg_value'] = '';
                    $file_text_use = "global_add_text.html";
                }
                $var['onchangefunction']=$config['admin_section']['cars']['onchangefunction'][$tablefield_array_r['Field']];
                $out1.=$tpl->replace($var,$file_text_use);

            }elseif (in_array($tablefield_array_r['Field'],$file)){
                $out1.=$tpl->replace($var,"global_add_file.html");
            }elseif (in_array($tablefield_array_r['Field'],$dropdown)){
                $var['tpl_input_name_val']=$dropdownval[$tablefield_array_r['Field']];
                $var['onchange'] = $dropdownval_onchange [$tablefield_array_r['Field']];
                if ($tablefield_array_r['Field']=='adprofiles'){

                }
                if ($default_option=="sponsored" AND $tablefield_array_r['Field']==$config["admin_section_sponsored_field_id"]){
                    $out1.=$tpl->replace($var,"global_add_dropdown2.html");
                }elseif ($javascript_special[$tablefield_array_r['Field']]!=''){
                    $var['modelsArray']=$javascript_special[$tablefield_array_r['Field']];
                    $out1.=$tpl->replace($var,"global_add_dropdown_special.html");
                }else{
                    $out1.=$tpl->replace($var,"global_add_dropdown.html");
                }
            }elseif (in_array($tablefield_array_r['Field'],$radio)){
                $radio_explode=explode("|#",$radioval[$tablefield_array_r['Field']]);
                $var_temp_val['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                foreach($radio_explode as $key=>$val){
                    $var_temp_val['tpl_input_name']="input_".$tablefield_array_r['Field'];
                    $value_radio = explode("|",$val);
                    if ($value_radio[1]=='') {
                        $value_radio[1]=$value_radio[0];
                    }
                    $var_temp_val['tpl_input_name_val']=$value_radio[1];
                    $var_temp_val['tpl_input_name_value']=$value_radio[0];
                    if ($_POST["input_".$tablefield_array_r['Field']]==""){
                        $_POST["input_".$tablefield_array_r['Field']]=$value_radio[0];
                    }
                    if ($_POST["input_".$tablefield_array_r['Field']]==$value_radio[0]){
                        $var_temp_val['checked']="checked";
                    }
                    $out_temp1.=$tpl->replace($var_temp_val,"global_add_radio_val.html");
                    unset($var_temp_val);
                }
                $var['tpl_input_name_val']=$out_temp1;
                $out_temp1="";
                $out1.=$tpl->replace($var,"global_add_radio.html");
            }elseif (in_array($tablefield_array_r['Field'],$checkbox)){
                if ($_POST["input_".$tablefield_array_r['Field']]=="on")
                    $var['tpl_input_name_val']="checked";

                if ($default_option=="rights" AND preg_match("/_add|_view|_delete/",$tablefield_array_r['Field']) ){
                    $var1=$var;
                    $templang = explode("_",$tablefield_array_r['Field']);
                    $var1['tpl_name']=$lang["tpl_auto_".$templang[1]];
                    $outtemp123.=$tpl->replace($var1,"global_add_checkbox_rights_repeat.html");
                    $i--;
                }elseif ($default_option=="rights" AND preg_match("/_edit/",$tablefield_array_r['Field']) ){
                    $var1=$var;
                    $var1['tpl_name']=$lang["tpl_auto_edit"];
                    $outtemp123.=$tpl->replace($var1,"global_add_checkbox_rights_repeat.html");
                    $var['repeat'] = $outtemp123;
                    $outtemp123="";
                    $out1.=$tpl->replace($var,"global_add_checkbox_rights.html");
                }else {
                    $out1.=$tpl->replace($var,"global_add_checkbox.html");
                }
            }elseif (in_array($tablefield_array_r['Field'],$password)){
                $out1.=$tpl->replace($var,"global_add_password.html");
            }elseif (in_array($tablefield_array_r['Field'],$date_fields)){
                /*
                             $var['tpl_input_day']="input_".$tablefield_array_r['Field']."_day";
                             $var['tpl_input_month']="input_".$tablefield_array_r['Field']."_month";
                             $var['tpl_input_year'] = "input_".$tablefield_array_r['Field']."_year";

                             $var['tpl_input_day_val'] = $this->days($_POST[$var['tpl_input_day']]);
                             $var['tpl_input_month_val'] = $this->months($_POST[$var['tpl_input_month']]);
                             $var['tpl_input_year_val'] = $this ->years($_POST[$var['tpl_input_year']]);
                             */
                $var['tpl_input_date']="input_".$tablefield_array_r['Field'];
                $var['tpl_input_date_val']=$_POST["input_".$tablefield_array_r['Field']];

                $out1.=$tpl->replace($var,"global_add_date.html");
            }elseif (in_array($tablefield_array_r['Field'],$datetime_fields)){
                $var['tpl_input_day']="input_".$tablefield_array_r['Field']."_day";
                $var['tpl_input_month']="input_".$tablefield_array_r['Field']."_month";
                $var['tpl_input_year'] = "input_".$tablefield_array_r['Field']."_year";
                $var['tpl_input_time'] = "input_".$tablefield_array_r['Field']."_time";
                $var['tpl_input_day_val'] = $this->days($_POST[$var['tpl_input_day']]);
                $var['tpl_input_month_val'] = $this->months($_POST[$var['tpl_input_month']]);
                $var['tpl_input_year_val'] = $this ->years($_POST[$var['tpl_input_year']]);
                $var['tpl_input_time_val'] = $_POST[$var['tpl_input_time']];
                $out1.=$tpl->replace($var,"global_add_datetime.html");
            }else{
                $i--;
            }
            if ($j==$i){
                //echo $aaate;
            }
            unset($var);
            $i++;
        }

        if ($default_tabel=='cars'){
            if ($i%2) $var['class_temp']="class_temp1";
            else $var['class_temp']="class_temp2";
            if (!$config['addfeaturesoncars']){
                $var['tpl_name']='';
                $var['tpl_input_name_val']="<h1>".$lang['tpl_auto_features']."</h1>";
                $config['config2_multiple_options'][1] .= $tpl->replace($var,"global_add_see.html");
                $i++;
                $config['config2_multiple_options'][0]=1;
                global $language_set;
                $config['config2_multiple_options'][1].=$this->getcheckbox('',"features","name{$language_set}","id","name{$language_set}","carsfeatures","carsid","featuresid");
            }
            $addg=true;
            if ($_REQUEST['o']=="add") {
                $admin_profile = $this->getprofile(  $_COOKIE['id_cookie'], "admin", "id" );
                if ($admin_profile['nopictures']!=0 and 0>=$admin_profile['nopictures']) {
                    $_REQUEST['o']="view";
                    $addg=false;
                }
                if($admin_profile['nopictures']>0  and $config['auto_multiple']['gallery']>$admin_profile['nopictures']){
                    $config['auto_multiple']['gallery']=$admin_profile['nopictures']->nopictures;
                }

                if($admin_profile['nopictures']==0 ){
                    $config['auto_multiple']['gallery']=$admin_profile['nopictures']=0;
                }
                //exit;
            }
            if ($addg and $config['auto_multiple']['gallery']>0){

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
                $file_size["thumbnail"][1]=$IMG_HEIGHT;
                $file_size["thumbnail"][0]=$IMG_WIDTH;
                $file_size["picture"][1]=$IMG_HEIGHT_BIG;
                $file_size["picture"][0]=$IMG_WIDTH_BIG;
                $copy_from=array("thumbnail");
                $copy_from_val["thumbnail"]="picture";

                $filearray=array("thumbnail","image");

                $relation = array();
                $relation_table = array();

                $require_array = array("picture"); //include array
                $password = array(); // for md5 fields
                $copy_from_id = array("carsid");
                $copy_from_id_value['carsid'] = $_SESSION['option_oid1'];

                $email_fields = array();
                $date_fields = array();
                if ($i%2) $var['class_temp']="class_temp1";
                else $var['class_temp']="class_temp2";
                $var['tpl_name']='';
                $var['tpl_input_name_val']="<h1>".$lang['tpl_auto_gallery']."</h1>";
                $config['config2_multiple_options'][1] .= $tpl->replace($var,"global_add_see.html");
                $i++;

                $config['config2_multiple_options'][1] .= $this->addgallery( 'gallery', 'gallery',  "add1", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, $date_fields,'',1 );
                $default_tabel='cars';

            }
        }
        if ($default_tabel=='cars'){
            $var['tpl_name']='';
            $var['tpl_input_name_val']="<h1>".$lang['tpl_auto_'.$default_option]."</h1>";
            $out1 = $tpl->replace($var,"global_add_see.html").$out1;
        }
        $var_initial['options']=$out1;
        if ($config['config2_multiple_options'][0]) {
            $var_initial['options'] .= $config['config2_multiple_options'][1];
        }


        $var_initial['what_add']=$lang["tpl_auto_$default_option"];
        if (count($varchar)==1){
            $var_initial['explain']=$lang['tpl_auto_You_can_input_can_have_multiple_values_separated_by_commas'];
        }

        $outtemp=$tpl->replace($var_initial,"global_add.html");
        return ($outtemp);
    }
    function add1($default_tabel,$default_option,$default_option2,$fields,$file,$file_size,$relation,$relation_table,$copy_from,$copy_from_val,$require_array,$password,$copy_from_id,$copy_from_id_value,$email_fields,$search_fields,$date_fields, $id_, $error=""){
        global $config,$lang,$IMG_WIDTH_FLAG,$IMG_HEIGHT_FLAG,$_POST,$varchar_fields;
        global $db,$tpl,$Image_Class,$Global_Class; //class
        global $sql_default_global,$datetime_fields;
        $var =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "redirect"=>$redirect,
            "error"=>$error
        );

        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        if (!is_array($fields)) $fields=array();
        if (!is_array($email_fields)) $email_fields=array();
        if (!is_array($date_fields)) $date_fields=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($radio)) $radio=array();
        $value_toexplode_array=array();
        $tablefinal=array();
        while ($tablefield_array_r = mysqli_fetch_array($result)){
            if (is_array($tablefield_array_r)){
                $tablefinal[] = $tablefield_array_r;
            }
            if (!in_array($tablefield_array_r['Field'],$fields)){

                if (in_array($tablefield_array_r['Field'],$varchar_fields)){
                    if (preg_match("/,/",$_POST["input_".$tablefield_array_r['Field']]) and count($varchar_fields)==1){
                        $value_toexplode=explode(",",$_POST["input_".$tablefield_array_r['Field']]);
                        foreach ($value_toexplode as $keytemp=>$valtemp){
                            $valtemp = trim($valtemp);
                            if ($valtemp!='');
                            $value_toexplode_array[]=$valtemp;
                        }
                    }else{
                        $value_toexplode_array=array($_POST["input_".$tablefield_array_r['Field']]);
                    }
                }
            }
        }
        @mysqli_free_result($result);
        if (count($value_toexplode_array)==0) $value_toexplode_array=array(1);
        foreach ($value_toexplode_array as $keyexplode=>$valexplode){
            //foreach ($valexplode as $key1_=>$val1_)
            {
                //echo $keyexplode." - $val1_<Br>";
                if (count($varchar_fields)==1){
                    $_POST["input_".$varchar_fields[0]]= $valexplode;
                }

                foreach  ($tablefinal as $tablefield_array_r){
                    if (!in_array($tablefield_array_r['Field'],$fields)){

                        $tablefield_array[]=$tablefield_array_r['Field'];
                        $variable[$tablefield_array_r['Field']]=$_POST["input_".$tablefield_array_r['Field']];

                        if (in_array($tablefield_array_r['Field'],$file)){
                            if( ! $imagine = $Image_Class->resizer_main("input_".$tablefield_array_r['Field'],$file_size[$tablefield_array_r['Field']][0],$file_size[$tablefield_array_r['Field']][1])){
                                $imagine="";
                                if (in_array($tablefield_array_r['Field'],$require_array)){
                                    $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                                }
                            }else{
                                $variable[$tablefield_array_r['Field']]=$imagine;
                            }
                        }
                        if (in_array($tablefield_array_r['Field'],$copy_from)){
                            if( ! $imagine = $Image_Class->resizer_main("input_".$copy_from_val[$tablefield_array_r['Field']],$file_size[$tablefield_array_r['Field']][0],$file_size[$tablefield_array_r['Field']][1])){
                                $imagine="";
                                if (in_array($tablefield_array_r['Field'],$require_array)){
                                    $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                                }
                            }else{
                                $variable[$tablefield_array_r['Field']]=$imagine;
                            }
                        }

                        if (in_array($tablefield_array_r['Field'],$password)){
                            $variable[$tablefield_array_r['Field']]=md5($variable[$tablefield_array_r['Field']]);
                        }
                        if (in_array($tablefield_array_r['Field'],$copy_from_id)){
                            $variable[$tablefield_array_r['Field']]=$copy_from_id_value[$tablefield_array_r['Field']];
                        }
                        if (in_array($tablefield_array_r['Field'],$email_fields) AND ( !preg_match( $config['emailverif'], $_POST["input_".$tablefield_array_r['Field']] ) ) ){
                            $var["error"] .= $lang["msg_error_email"].$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                        }

                        if (in_array($tablefield_array_r['Field'],$date_fields)){
                            $temp = explode("-",$_POST["input_".$tablefield_array_r['Field']]);
                            $var['tpl_input_day']=$temp[0];
                            $var['tpl_input_month']=$temp[1];
                            $var['tpl_input_year'] = $temp[2];

                            if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
                                $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
                            }

                            $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']}";
                            $variable[$tablefield_array_r['Field']]=@date ("Y-m-d",strtotime($variable[$tablefield_array_r['Field']]));
                        }
                        if (in_array($tablefield_array_r['Field'],$datetime_fields)){
                            $var['tpl_input_day']=$_POST["input_".$tablefield_array_r['Field']."_day"];
                            $var['tpl_input_month']=$_POST["input_".$tablefield_array_r['Field']."_month"];
                            $var['tpl_input_year'] = $_POST["input_".$tablefield_array_r['Field']."_year"];
                            $var['tpl_input_time'] = $_POST["input_".$tablefield_array_r['Field']."_time"];
                            if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
                                $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
                            }
                            $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']} {$var['tpl_input_time']}";
                            $variable[$tablefield_array_r['Field']]=date ("Y-m-d G:i:s",strtotime($variable[$tablefield_array_r['Field']]));
                        }
                        if (in_array($tablefield_array_r['Field'],$require_array)){
                            $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                        }
                        $variable[$tablefield_array_r['Field']]=PrepareForStore($variable[$tablefield_array_r['Field']]);
                        $sql_input.=" , `".$tablefield_array_r['Field']."` ";
                        $sql_input_val.=" , '".$variable[$tablefield_array_r['Field']]."' ";
                        if ($var['error']!=""){
                            //$var['error'].=$config['use_point_after_error'].$_POST["input_".$tablefield_array_r['Field']].$config['use_br_after_error'];
                        }
                    }
                }
                //echo $var['error'];
                if ($var['error']==""){
                    $sql = "INSERT INTO `{$config['table_prefix']}$default_tabel` "
                        ." ( `$id_` $sql_input )"
                        ." VALUES ( '' "
                        ." $sql_input_val );";
                    //echo "<br />";
                    $sql_input_val="";
                    $sql_input="";
                    $result = $db->query($sql,__FILE__,__LINE__);
                    //return ( $output.=$Global_Class->search($default_tabel,$default_option,"",0,$config['nrresult'],$relation,$relation_table,$default_option."_search_rows",$sql_default_global,$search_fields, $id_, $lang["tpl_".$default_option."_add"]) );
                    //echo count($varchar_fields);
                    //exit;
                    global $_COOKIE;
                    $row = array(
                        "admin"=>$_COOKIE['id_cookie'],
                        "action"=>$lang['logging']['add']." ".$lang["tpl_auto_".$_REQUEST['p']]." ( ".mysqli_insert_id().": ".$variable[$config['admin_section'][$_REQUEST['p']]['field_name_for_delete']].") ",
                        "sql"=>"$sql"
                    );


                    if (count($varchar_fields)!=1){
                        if ($var['error']==""){
                            $var_temp_return[0]=true;
                            $var_temp_return[1]=$lang["tpl_".$default_option."_add"];
                            $var_temp_return[2]=mysqli_insert_id();
                            if ($default_tabel=='cars'){
                                $this->insertcheckbox($var_temp_return[2],'features','name','id','name','carsfeatures','carsid','featuresid');

                                $addg=true;
                                if ($o=="add") {
                                    $admin_profile = $Global_Class->getprofile(  $_COOKIE['id_cookie'], "admin", "id" );
                                    if ($admin_profile['nopictures']!=0 and 0>=$admin_profile['nopictures']) {
                                        $_REQUEST['o']="view";
                                        $addg=false;
                                    }
                                    if($admin_profile['nopictures']>0  and $config['auto_multiple']['gallery']>$admin_profile['nopictures']){
                                        $config['auto_multiple']['gallery']=$admin_profile['nopictures']-$nopictures;
                                    }
                                }
                                if ($addg){

                                    $varchar_fields = array( "description", "order" );
                                    $multiplefields = array( "description" );
                                    $text_fields = array();
                                    $file_fields = array();

                                    $dropdown_fields = array();
                                    $dropdownval = array();


                                    $radio_fields = array();
                                    $radioval = array();
                                    $checkbox_fields = array();

                                    $password_fields = array();

                                    $file = array("picture","thumbnail"); // for pictures all
                                    $file_fields = array("picture"); // for only show when add
                                    global $IMG_HEIGHT,$IMG_WIDTH,$IMG_HEIGHT_BIG,$IMG_WIDTH_BIG;
                                    $file_size["thumbnail"][1]=$IMG_HEIGHT;
                                    $file_size["thumbnail"][0]=$IMG_WIDTH;
                                    $file_size["picture"][1]=$IMG_HEIGHT_BIG;
                                    $file_size["picture"][0]=$IMG_WIDTH_BIG;
                                    $copy_from=array("thumbnail");
                                    $copy_from_val["thumbnail"]="picture";

                                    $filearray=array("thumbnail","image");

                                    $relation = array();
                                    $relation_table = array();

                                    $require_array = array("picture"); //include array
                                    $password = array(); // for md5 fields
                                    $copy_from_id = array("carsid");
                                    $copy_from_id_value['carsid'] =  $var_temp_return[2];

                                    $email_fields = array();
                                    $date_fields = array();

                                    $_POST['input_description']=$_POST['input_descriptiongallery'];

                                    $output_add = $Global_Class->addgallery1("gallery", "gallery", "", array("id"), $file,$file_size,$relation,$relation_table,$copy_from,$copy_from_val,$require_array,$password,$copy_from_id,$copy_from_id_value, $email_fields, $search_fields, $date_fields, $id_ );
                                }
                            }
                            addlogging( $row );
                            return $var_temp_return;
                        }
                    }
                }

            }//end foreach
        }
        //exit;

        if ($var['error']==""){
            $var_temp_return[0]=true;
            $var_temp_return[1]=count($value_toexplode_array)." ".$lang["tpl_".$default_option."_add"];
            $var_temp_return[2]=mysqli_insert_id();
            return $var_temp_return;
        }
        $var_temp_return[0]=false;
        $var_temp_return[1]=$var['error'];
        return $var_temp_return;
    }
    function addold($default_tabel,$default_option,$default_option2,$varchar,$text,$file,$dropdown,$dropdownval,$radio,$radioval,$checkbox,$password,$date_fields, $error=""){
        global $config,$lang;
        global $db,$tpl,$_POST,$datetime_fields,$text_fields_wysiwyg,$settings_profile; //class
        global $javascript_special,$dropdownval_onchange,$datetime_fields;
        $var_initial =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "redirect"=>$redirect,
            "error"=>$error
        );
        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql);
        $i=0;
        if (!is_array($varchar)) $varchar=array();
        if (!is_array($text)) $text=array();
        if (!is_array($file)) $file=array();
        if (!is_array($dropdown)) $dropdown=array();
        if (!is_array($date_fields)) $date_fields=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($text_fields_wysiwyg)) $text_fields_wysiwyg=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($radio)) $radio=array();

        while ($tablefield_array_r = mysqli_fetch_array($result)){

            $tablefield_array[]=$tablefield_array_r['Field'];

        }
        @mysqli_free_result($result);
        $tablefield_arraynew=array();
        if (!is_array($config['admin_section'][$default_tabel]['order']))$config['admin_section'][$default_tabel]['order']=array();
        foreach ($config['admin_section'][$default_tabel]['order'] as $key=>$val){
            if (in_array($val,$tablefield_array)){
                $tablefield_arraynew[]=$val;
            }
        }
        foreach ($tablefield_array as $key=>$val){
            if (!in_array($val,$tablefield_arraynew)){
                $tablefield_arraynew[]=$val;
            }
        }
        //print_r($tablefield_array);

        if ($config['auto_multiple'][$default_tabel]>0){
            $array_multiple=range(0, $config['auto_multiple'][$default_tabel]-1);
        }else{
            $array_multiple=array(0);
        }
        //print_r($array_multiple);

        foreach ($array_multiple as $kmultiple){
            foreach ($tablefield_arraynew as $kkk=>$vvv){
                if ($kmultiple==0) $kmultiplenew="";
                else $kmultiplenew="multiple{$kmultiple}_";
                $tablefield_array_r['Field']=$vvv;
                //echo "<br />";
                $aaate = "\"$vvv\", ";
                $j=$i;
                if ($i%2) $var['class_temp']="class_temp1";
                else $var['class_temp']="class_temp2";

                $var['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                $var['tpl_input_name']=$kmultiplenew."input_".$tablefield_array_r['Field'];
                $var['tpl_input_name_val']=$_POST[$kmultiplenew."input_".$tablefield_array_r['Field']];

                if (in_array($tablefield_array_r['Field'],$varchar)){
                    $var['maxlength']=$config['varchar_special_maxlength'][$tablefield_array_r['Field']];
                    $var['size']=($config['varchar_special_maxlength'][$tablefield_array_r['Field']]>$config['default_size_for_fields_inbackend'])?$config['default_size_for_fields_inbackend']:$config['varchar_special_maxlength'][$tablefield_array_r['Field']];                   $var['size']=$config['varchar_special_maxlength'][$tablefield_array_r['Field']];
                    $var['goodchars']=$config['varchar_special_maxlength_goodchars'][$tablefield_array_r['Field']];

                    if ($var['maxlength']!='' and $var['goodchars']!='' ){
                        $out1.=$tpl->replace($var,"global_add_varcharspecial.html");
                    }else{
                        $out1.=$tpl->replace($var,"global_add_varchar.html");
                    }
                }elseif (in_array($tablefield_array_r['Field'],$text)){
                    $var['rows']=$config['rows'];
                    $var['cols']=$config['cols'];

                    if (in_array($tablefield_array_r['Field'],$text_fields_wysiwyg) and $settings_profile['wysiwyg']==1) {

                        $oFCKeditor = new FCKeditor($var['tpl_input_name']) ;
                        $oFCKeditor->BasePath = $config['wywiwyg_editor'] ;                // '/FCKeditor/' is the default value so this line could be deleted.
                        $oFCKeditor->DefaultLanguage = $config['wywiwyg_DefaultLanguage'];
                        $oFCKeditor->Value = $var['tpl_input_name_val'] ;
                        if ($default_option=="cars" or $default_option=="carsdealer" ) {
                            $oFCKeditor->ToolbarSet = 'Description' ;
                        }
                        $var['wywiwyg_value'] = $oFCKeditor->CreateFCKeditor( $var['tpl_input_name'], $var['cols']*$config['wywiwyg_sizecols'],$var['rows']*$config['wywiwyg_sizerows'] ) ;

                        $file_text_use = "global_add_wywiwyg.html";
                    }else{
                        $var['wywiwyg_value'] = '';
                        $file_text_use = "global_add_text.html";
                    }
                    $out1.=$tpl->replace($var,$file_text_use);

                }elseif (in_array($tablefield_array_r['Field'],$file)){
                    $out1.=$tpl->replace($var,"global_add_file.html");
                }elseif (in_array($tablefield_array_r['Field'],$dropdown)){
                    $var['tpl_input_name_val']=$dropdownval[$tablefield_array_r['Field']];
                    $var['onchange'] = $dropdownval_onchange [$tablefield_array_r['Field']];
                    if ($default_option=="sponsored" and $tablefield_array_r['Field']==$config["admin_section_sponsored_field_id"]){
                        $out1.=$tpl->replace($var,"global_add_dropdown2.html");
                    }elseif ($javascript_special[$tablefield_array_r['Field']]!=''){
                        $var['modelsArray']=$javascript_special[$tablefield_array_r['Field']];
                        $out1.=$tpl->replace($var,"global_add_dropdown_special.html");
                    }else{
                        $out1.=$tpl->replace($var,"global_add_dropdown.html");
                    }
                }elseif (in_array($tablefield_array_r['Field'],$radio)){
                    $radio_explode=explode("|#",$radioval[$tablefield_array_r['Field']]);
                    $var_temp_val['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                    foreach($radio_explode as $key=>$val){
                        $var_temp_val['tpl_input_name']=$kmultiplenew."input_".$tablefield_array_r['Field'];
                        $value_radio = explode("|",$val);
                        if ($value_radio[1]=='') {
                            $value_radio[1]=$value_radio[0];
                        }
                        $var_temp_val['tpl_input_name_val']=$value_radio[1];
                        $var_temp_val['tpl_input_name_value']=$value_radio[0];
                        if ($_POST["input_".$tablefield_array_r['Field']]==""){
                            $_POST["input_".$tablefield_array_r['Field']]=$value_radio[0];
                        }
                        if ($_POST["input_".$tablefield_array_r['Field']]==$value_radio[0]){
                            $var_temp_val['checked']="checked";
                        }
                        $out_temp1.=$tpl->replace($var_temp_val,"global_add_radio_val.html");
                        unset($var_temp_val);
                    }
                    $var['tpl_input_name_val']=$out_temp1;
                    $out_temp1="";
                    $out1.=$tpl->replace($var,"global_add_radio.html");
                }elseif (in_array($tablefield_array_r['Field'],$checkbox)){
                    if ($_POST[$kmultiplenew."input_".$tablefield_array_r['Field']]=="on")
                        $var['tpl_input_name_val']="checked";

                    if ($default_option=="rights" AND preg_match("/_add|_view|_delete/",$tablefield_array_r['Field']) ){
                        $var1=$var;
                        $templang = explode("_",$tablefield_array_r['Field']);
                        $var1['tpl_name']=$lang["tpl_auto_".$templang[1]];
                        $outtemp123.=$tpl->replace($var1,"global_add_checkbox_rights_repeat.html");
                        $i--;
                    }elseif ($default_option=="rights" AND preg_match("/_edit/",$tablefield_array_r['Field']) ){
                        $var1=$var;
                        $var1['tpl_name']=$lang["tpl_auto_edit"];
                        $outtemp123.=$tpl->replace($var1,"global_add_checkbox_rights_repeat.html");
                        $var['repeat'] = $outtemp123;
                        $outtemp123="";
                        $out1.=$tpl->replace($var,"global_add_checkbox_rights.html");
                    }else {
                        $out1.=$tpl->replace($var,"global_add_checkbox.html");
                    }
                }elseif (in_array($tablefield_array_r['Field'],$password)){
                    $out1.=$tpl->replace($var,"global_add_password.html");
                }elseif (in_array($tablefield_array_r['Field'],$date_fields)){
                    /*
                             $var['tpl_input_day']="input_".$tablefield_array_r['Field']."_day";
                             $var['tpl_input_month']="input_".$tablefield_array_r['Field']."_month";
                             $var['tpl_input_year'] = "input_".$tablefield_array_r['Field']."_year";

                             $var['tpl_input_day_val'] = $this->days($_POST[$var['tpl_input_day']]);
                             $var['tpl_input_month_val'] = $this->months($_POST[$var['tpl_input_month']]);
                             $var['tpl_input_year_val'] = $this ->years($_POST[$var['tpl_input_year']]);
                             */
                    $var['tpl_input_date']="input_".$tablefield_array_r['Field'];
                    $var['tpl_input_date_val']=$_POST["input_".$tablefield_array_r['Field']];
                    $out1.=$tpl->replace($var,"global_add_date.html");
                }elseif (in_array($tablefield_array_r['Field'],$datetime_fields)){
                    $var['tpl_input_day']=$kmultiplenew."input_".$tablefield_array_r['Field']."_day";
                    $var['tpl_input_month']=$kmultiplenew."input_".$tablefield_array_r['Field']."_month";
                    $var['tpl_input_year'] = $kmultiplenew."input_".$tablefield_array_r['Field']."_year";
                    $var['tpl_input_time'] = $kmultiplenew."input_".$tablefield_array_r['Field']."_time";
                    $var['tpl_input_day_val'] = $this->days($_POST[$var['tpl_input_day']]);
                    $var['tpl_input_month_val'] = $this->months($_POST[$var['tpl_input_month']]);
                    $var['tpl_input_year_val'] = $this ->years($_POST[$var['tpl_input_year']]);
                    $var['tpl_input_time_val'] = $_POST[$var['tpl_input_time']];
                    $out1.=$tpl->replace($var,"global_add_datetime.html");
                }else{
                    $i--;
                }
                if ($j==$i){
                    //echo $aaate;
                }
                unset($var);
                $i++;
            }
        }
        $var_initial['options']=$out1;
        if ($config['config2_multiple_options'][0]) {
            $var_initial['options'] .= $config['config2_multiple_options'][1];
        }

        $var_initial['what_add']=$lang["tpl_auto_$default_option"];
        $outtemp=$tpl->replace($var_initial,"global_add.html");
        return ($outtemp);
    }
    function addold1($default_tabel,$default_option,$default_option2,$fields,$file,$file_size,$relation,$relation_table,$copy_from,$copy_from_val,$require_array,$password,$copy_from_id,$copy_from_id_value,$email_fields,$search_fields,$date_fields, $id_, $error=""){
        global $config,$lang,$IMG_WIDTH_FLAG,$IMG_HEIGHT_FLAG,$_POST;
        global $db,$tpl,$Image_Class,$Global_Class; //class
        global $sql_default_global,$datetime_fields;
        $var =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "redirect"=>$redirect,
            "error"=>$error
        );

        if ($config['auto_multiple'][$default_tabel]>0){
            $array_multiple=range(0, $config['auto_multiple'][$default_tabel]-1);
        }else{
            $array_multiple=array("0");
        }
        foreach ($array_multiple as $kmultiple){
            if ($kmultiple==0) $kmultiplenew="";
            else $kmultiplenew="multiple{$kmultiple}_";


            $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
            $result = $db->query($sql);
            if (!is_array($fields)) $fields=array();
            if (!is_array($email_fields)) $email_fields=array();

            if (!is_array($varchar)) $varchar=array();
            if (!is_array($text)) $text=array();
            if (!is_array($file)) $file=array();
            if (!is_array($dropdown)) $dropdown=array();
            if (!is_array($date_fields)) $date_fields=array();
            if (!is_array($datetime_fields)) $datetime_fields=array();
            if (!is_array($text_fields_wysiwyg)) $text_fields_wysiwyg=array();
            if (!is_array($datetime_fields)) $datetime_fields=array();
            if (!is_array($radio)) $radio=array();

            $condtoinsert=true;
            while ($tablefield_array_r = mysqli_fetch_array($result)){

                if (!in_array($tablefield_array_r['Field'],$fields)){
                    $tablefield_array_rinitial = $tablefield_array_r['Field'] ;
                    //$tablefield_array_r['Field']=$vvv;

                    $tablefield_array[]=$tablefield_array_r['Field'];
                    //print_r($_POST);
                    $variable[$tablefield_array_r['Field']]=$_POST[$kmultiplenew."input_".$tablefield_array_r['Field']];

                    if (in_array($tablefield_array_r['Field'],$file)){
                        if( ! $imagine = $Image_Class->resizer_main($kmultiplenew."input_".$tablefield_array_r['Field'],$file_size[$tablefield_array_r['Field']][0],$file_size[$tablefield_array_r['Field']][1])){
                            $imagine="";
                            if (in_array($tablefield_array_r['Field'],$require_array)){
                                if ($kmultiple<=0)
                                    $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                                if ($kmultiple>0){
                                    $condtoinsert=false;
                                }
                            }
                        }else{
                            $variable[$tablefield_array_r['Field']]=$imagine;
                            if ($kmultiple>=0){
                                $condtoinsert=true;
                            }
                        }
                    }
                    if (in_array($tablefield_array_r['Field'],$copy_from)){
                        if( ! $imagine = $Image_Class->resizer_main($kmultiplenew."input_".$copy_from_val[$tablefield_array_r['Field']],$file_size[$tablefield_array_r['Field']][0],$file_size[$tablefield_array_r['Field']][1])){
                            $imagine="";
                            if (in_array($tablefield_array_r['Field'],$require_array)){                                                                                                                   if ($kmultiple<=0)
                                $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                            }

                        }else{
                            $variable[$tablefield_array_r['Field']]=$imagine;

                        }
                    }

                    if (in_array($tablefield_array_r['Field'],$password)){
                        $variable[$tablefield_array_r['Field']]=md5($variable[$tablefield_array_r['Field']]);
                    }
                    if (in_array($tablefield_array_r['Field'],$copy_from_id)){
                        $variable[$tablefield_array_r['Field']]=$copy_from_id_value[$tablefield_array_r['Field']];
                    }
                    if (in_array($tablefield_array_r['Field'],$email_fields) AND ( !preg_match( $config['emailverif'], $_POST[$kmultiplenew."input_".$tablefield_array_r['Field']] ) ) ){
                        $var["error"] .= $lang["msg_error_email"].$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                    }

                    if (in_array($tablefield_array_r['Field'],$date_fields)){
                        $temp = explode("-",$_POST["input_".$tablefield_array_r['Field']]);
                        $var['tpl_input_day']=$temp[0];
                        $var['tpl_input_month']=$temp[1];
                        $var['tpl_input_year'] = $temp[2];

                        if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
                            $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
                        }

                        $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']}";
                        $variable[$tablefield_array_r['Field']]=@date ("Y-m-d",strtotime($variable[$tablefield_array_r['Field']]));
                    }
                    if (in_array($tablefield_array_r['Field'],$datetime_fields)){
                        $var['tpl_input_day']=$_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_day"];
                        $var['tpl_input_month']=$_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_month"];
                        $var['tpl_input_year'] = $_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_year"];
                        $var['tpl_input_time'] = $_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_time"];
                        if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
                            $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
                        }
                        $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']} {$var['tpl_input_time']}";
                        $variable[$tablefield_array_r['Field']]=date ("Y-m-d G:i:s",strtotime($variable[$tablefield_array_r['Field']]));
                    }
                    if (in_array($tablefield_array_r['Field'],$require_array)){
                        if ($kmultiple<=0)
                            $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                    }
                    $variable[$tablefield_array_r['Field']]=PrepareForStore($variable[$tablefield_array_r['Field']]);
                    $sql_input.=" , `".$tablefield_array_rinitial."` ";
                    $sql_input_val.=" , '".$variable[$tablefield_array_r['Field']]."' ";
                }
            }
            @mysqli_free_result($result);

            if ($var['error']==""){

                if ($condtoinsert){
                    $sqlfinal .= "INSERT INTO `{$config['table_prefix']}$default_tabel` "
                        ." ( `$id_` $sql_input )"
                        ." VALUES ( '' "
                        ." $sql_input_val )||||";
                }

                $sql_input_val='';
                $sql_input='';
                //return ( $output.=$Global_Class->search($default_tabel,$default_option,"",0,$config['nrresult'],$relation,$relation_table,$default_option."_search_rows",$sql_default_global,$search_fields, $id_, $lang["tpl_".$default_option."_add"]) );

            }
        }

        if ($var['error']==""){
            $sqlfinalarr=explode("||||",$sqlfinal);
            foreach ($sqlfinalarr as $sqlfinal){
                if ($sqlfinal!=''){
                    $result = $db->query($sqlfinal);

                    global $_COOKIE;
                    $row = array(
                        "admin"=>$_COOKIE['id_cookie'],
                        "action"=>$lang['logging']['add']." ".$lang["tpl_auto_".$_REQUEST['p']]." ( ".mysqli_insert_id().": ".$variable[$config['admin_section'][$_REQUEST['p']]['field_name_for_delete']].") ",
                        "sql"=>"$sqlfinal"
                    );

                    addlogging( $row );
                }
            }
            $var_temp_return[0]=true;
            $var_temp_return[1]=$lang["tpl_".$default_option."_add"];
            $var_temp_return[2]=mysqli_insert_id();
            if ($kmultiple==$config['auto_multiple'][$default_tabel]-1 or $config['auto_multiple'][$default_tabel]<=0){
                return $var_temp_return;
            }
        }
        $var_temp_return[0]=false;
        $var_temp_return[1]=$var['error'];
        return $var_temp_return;
    }

//galleryadd
    function addgallery($default_tabel,$default_option,$default_option2,$varchar,$text,$file,$dropdown,$dropdownval,$radio,$radioval,$checkbox,$password,$date_fields, $error="",$speciladd=0){
        global $config,$lang;
        global $db,$tpl,$_POST,$datetime_fields,$text_fields_wysiwyg,$settings_profile; //class
        global $javascript_special,$dropdownval_onchange,$datetime_fields;
        $var_initial =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "redirect"=>$redirect,
            "error"=>$error
        );
        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql);
        $i=0;
        if (!is_array($varchar)) $varchar=array();
        if (!is_array($text)) $text=array();
        if (!is_array($file)) $file=array();
        if (!is_array($dropdown)) $dropdown=array();
        if (!is_array($date_fields)) $date_fields=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($text_fields_wysiwyg)) $text_fields_wysiwyg=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($radio)) $radio=array();

        while ($tablefield_array_r = mysqli_fetch_array($result)){

            $tablefield_array[]=$tablefield_array_r['Field'];

        }
        @mysqli_free_result($result);
        $tablefield_arraynew=array();
        if (!is_array($config['admin_section'][$default_tabel]['order']))$config['admin_section'][$default_tabel]['order']=array();
        foreach ($config['admin_section'][$default_tabel]['order'] as $key=>$val){
            if (in_array($val,$tablefield_array)){
                $tablefield_arraynew[]=$val;
            }
        }
        foreach ($tablefield_array as $key=>$val){
            if (!in_array($val,$tablefield_arraynew)){
                $tablefield_arraynew[]=$val;
            }
        }
        //print_r($tablefield_array);

        if ($config['auto_multiple'][$default_tabel]>0){
            $array_multiple=range(0, $config['auto_multiple'][$default_tabel]-1);
        }else{
            return ($lang['tpl_auto_You_don_t_have_the_right_to_add_any_pictures']);
            $array_multiple=array(0);
        }
        //print_r($array_multiple);
        foreach ($array_multiple as $kmultiple){
            foreach ($tablefield_arraynew as $kkk=>$vvv){
                if ($kmultiple==0) $kmultiplenew="";
                else $kmultiplenew="multiple{$kmultiple}_";
                $tablefield_array_r['Field']=$vvv;
                //echo "<br />";
                $aaate = "\"$vvv\", ";
                $j=$i;
                if ($i%2) $var['class_temp']="class_temp1";
                else $var['class_temp']="class_temp2";
                $var['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                $var['tpl_input_name']=$kmultiplenew."input_".$tablefield_array_r['Field'];
                if ($speciladd==1 and $var['tpl_input_name']=='input_description'){
                    $var['tpl_input_name']='input_descriptiongallery';
                }
                $var['tpl_input_name_val']=$_POST[$kmultiplenew."input_".$tablefield_array_r['Field']];

                if (in_array($tablefield_array_r['Field'],$varchar)){
                    $var['maxlength']=$config['varchar_special_maxlength'][$tablefield_array_r['Field']];
                    $var['size']=($config['varchar_special_maxlength'][$tablefield_array_r['Field']]>$config['default_size_for_fields_inbackend'])?$config['default_size_for_fields_inbackend']:$config['varchar_special_maxlength'][$tablefield_array_r['Field']];                   $var['size']=$config['varchar_special_maxlength'][$tablefield_array_r['Field']];
                    $var['goodchars']=$config['varchar_special_maxlength_goodchars'][$tablefield_array_r['Field']];

                    if ($var['maxlength']!='' and $var['goodchars']!='' ){
                        $out1.=$tpl->replace($var,"global_add_varcharspecial.html");
                    }else{
                        $out1.=$tpl->replace($var,"global_add_varchar.html");
                    }
                }elseif (in_array($tablefield_array_r['Field'],$text)){
                    $var['rows']=$config['rows'];
                    $var['cols']=$config['cols'];

                    if (in_array($tablefield_array_r['Field'],$text_fields_wysiwyg) and $settings_profile['wysiwyg']==1) {


                        $oFCKeditor = new FCKeditor($var['tpl_input_name']) ;
                        $oFCKeditor->BasePath = $config['wywiwyg_editor'] ;                // '/FCKeditor/' is the default value so this line could be deleted.
                        $oFCKeditor->DefaultLanguage = $config['wywiwyg_DefaultLanguage'];
                        $oFCKeditor->Value = $var['tpl_input_name_val'] ;
                        if ($default_option=="cars" or $default_option=="carsdealer" ) {
                            $oFCKeditor->ToolbarSet = 'Description' ;
                        }
                        $var['wywiwyg_value'] = $oFCKeditor->CreateFCKeditor( $var['tpl_input_name'], $var['cols']*$config['wywiwyg_sizecols'],$var['rows']*$config['wywiwyg_sizerows'] ) ;

                        $file_text_use = "global_add_wywiwyg.html";
                    }else{
                        $var['wywiwyg_value'] = '';
                        $file_text_use = "global_add_text.html";
                    }
                    $out1.=$tpl->replace($var,$file_text_use);

                }elseif (in_array($tablefield_array_r['Field'],$file)){
                    $out1.=$tpl->replace($var,"global_add_file.html");
                }elseif (in_array($tablefield_array_r['Field'],$dropdown)){
                    $var['tpl_input_name_val']=$dropdownval[$tablefield_array_r['Field']];
                    $var['onchange'] = $dropdownval_onchange [$tablefield_array_r['Field']];
                    if ($javascript_special[$tablefield_array_r['Field']]!=''){
                        $var['modelsArray']=$javascript_special[$tablefield_array_r['Field']];
                        $out1.=$tpl->replace($var,"global_add_dropdown_special.html");
                    }else{
                        $out1.=$tpl->replace($var,"global_add_dropdown.html");
                    }
                }elseif (in_array($tablefield_array_r['Field'],$radio)){
                    $radio_explode=explode("|#",$radioval[$tablefield_array_r['Field']]);
                    $var_temp_val['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                    foreach($radio_explode as $key=>$val){
                        $var_temp_val['tpl_input_name']=$kmultiplenew."input_".$tablefield_array_r['Field'];
                        $value_radio = explode("|",$val);
                        if ($value_radio[1]=='') {
                            $value_radio[1]=$value_radio[0];
                        }
                        $var_temp_val['tpl_input_name_val']=$value_radio[1];
                        $var_temp_val['tpl_input_name_value']=$value_radio[0];
                        if ($_POST["input_".$tablefield_array_r['Field']]==""){
                            $_POST["input_".$tablefield_array_r['Field']]=$value_radio[0];
                        }
                        if ($_POST["input_".$tablefield_array_r['Field']]==$value_radio[0]){
                            $var_temp_val['checked']="checked";
                        }
                        $out_temp1.=$tpl->replace($var_temp_val,"global_add_radio_val.html");
                        unset($var_temp_val);
                    }
                    $var['tpl_input_name_val']=$out_temp1;
                    $out_temp1="";
                    $out1.=$tpl->replace($var,"global_add_radio.html");
                }elseif (in_array($tablefield_array_r['Field'],$checkbox)){
                    if ($_POST[$kmultiplenew."input_".$tablefield_array_r['Field']]=="on")
                        $var['tpl_input_name_val']="checked";

                    if ($default_option=="rights" AND preg_match("/_add|_view|_delete/",$tablefield_array_r['Field']) ){
                        $var1=$var;
                        $templang = explode("_",$tablefield_array_r['Field']);
                        $var1['tpl_name']=$lang["tpl_auto_".$templang[1]];
                        $outtemp123.=$tpl->replace($var1,"global_add_checkbox_rights_repeat.html");
                        $i--;
                    }elseif ($default_option=="rights" AND preg_match("/_edit/",$tablefield_array_r['Field']) ){
                        $var1=$var;
                        $var1['tpl_name']=$lang["tpl_auto_edit"];
                        $outtemp123.=$tpl->replace($var1,"global_add_checkbox_rights_repeat.html");
                        $var['repeat'] = $outtemp123;
                        $outtemp123="";
                        $out1.=$tpl->replace($var,"global_add_checkbox_rights.html");
                    }else {
                        $out1.=$tpl->replace($var,"global_add_checkbox.html");
                    }
                }elseif (in_array($tablefield_array_r['Field'],$password)){
                    $out1.=$tpl->replace($var,"global_add_password.html");
                }elseif (in_array($tablefield_array_r['Field'],$date_fields)){
                    $var['tpl_input_day']=$kmultiplenew."input_".$tablefield_array_r['Field']."_day";
                    $var['tpl_input_month']=$kmultiplenew."input_".$tablefield_array_r['Field']."_month";
                    $var['tpl_input_year'] = $kmultiplenew."input_".$tablefield_array_r['Field']."_year";
                    $var['tpl_input_day_val'] = $this->days($_POST[$var['tpl_input_day']]);
                    $var['tpl_input_month_val'] = $this->months($_POST[$var['tpl_input_month']]);
                    $var['tpl_input_year_val'] = $this ->years($_POST[$var['tpl_input_year']]);
                    $out1.=$tpl->replace($var,"global_add_date.html");
                }elseif (in_array($tablefield_array_r['Field'],$datetime_fields)){
                    $var['tpl_input_day']=$kmultiplenew."input_".$tablefield_array_r['Field']."_day";
                    $var['tpl_input_month']=$kmultiplenew."input_".$tablefield_array_r['Field']."_month";
                    $var['tpl_input_year'] = $kmultiplenew."input_".$tablefield_array_r['Field']."_year";
                    $var['tpl_input_time'] = $kmultiplenew."input_".$tablefield_array_r['Field']."_time";
                    $var['tpl_input_day_val'] = $this->days($_POST[$var['tpl_input_day']]);
                    $var['tpl_input_month_val'] = $this->months($_POST[$var['tpl_input_month']]);
                    $var['tpl_input_year_val'] = $this ->years($_POST[$var['tpl_input_year']]);
                    $var['tpl_input_time_val'] = $_POST[$var['tpl_input_time']];
                    $out1.=$tpl->replace($var,"global_add_datetime.html");
                }else{
                    $i--;
                }
                if ($j==$i){
                    //echo $aaate;
                }
                unset($var);
                $i++;
            }
        }
        if ($speciladd==1){
            return $out1;
        }
        $var_initial['options']=$out1;
        if ($config['config2_multiple_options'][0]) {
            $var_initial['options'] .= $config['config2_multiple_options'][1];
        }

        $var_initial['what_add']=$lang["tpl_auto_$default_option"];
        $outtemp=$tpl->replace($var_initial,"global_add.html");
        return ($outtemp);
    }
    function addgallery1($default_tabel,$default_option,$default_option2,$fields,$file,$file_size,$relation,$relation_table,$copy_from,$copy_from_val,$require_array,$password,$copy_from_id,$copy_from_id_value,$email_fields,$search_fields,$date_fields, $id_, $error=""){
        global $config,$lang,$IMG_WIDTH_FLAG,$IMG_HEIGHT_FLAG,$_POST;
        global $db,$tpl,$Image_Class,$Global_Class; //class
        global $sql_default_global,$datetime_fields;
        $var =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "redirect"=>$redirect,
            "error"=>$error
        );

        if ($config['auto_multiple'][$default_tabel]>0){
            $array_multiple=range(0, $config['auto_multiple'][$default_tabel]-1);
        }else{
            $array_multiple=array("0");
        }
        foreach ($array_multiple as $kmultiple){
            if ($kmultiple==0) $kmultiplenew="";
            else $kmultiplenew="multiple{$kmultiple}_";


            $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
            $result = $db->query($sql);
            if (!is_array($fields)) $fields=array();
            if (!is_array($email_fields)) $email_fields=array();
            if (!is_array($date_fields)) $date_fields=array();
            if (!is_array($datetime_fields)) $datetime_fields=array();
            $condtoinsert=true;
            while ($tablefield_array_r = mysqli_fetch_array($result)){

                if (!in_array($tablefield_array_r['Field'],$fields)){
                    $tablefield_array_rinitial = $tablefield_array_r['Field'] ;
                    //$tablefield_array_r['Field']=$vvv;

                    $tablefield_array[]=$tablefield_array_r['Field'];
                    //print_r($_POST);
                    $variable[$tablefield_array_r['Field']]=$_POST[$kmultiplenew."input_".$tablefield_array_r['Field']];

                    if (in_array($tablefield_array_r['Field'],$file)){

                        $namenew=$_POST[$kmultiplenew."input_description"];
                        $oid=$copy_from_id_value['carsid'];


                        $oidp = $Global_Class->getprofile( $oid,"cars","id" );
                        $mak = $Global_Class->getprofile( $oidp['make'],"make","id" );
                        $mod = $Global_Class->getprofile( $oidp['model'],"model","id" );


                        $namenew=makeurl2($oid."-".$mak['name']."-".$mod['name']."-".$namenew);

                        if( ! $imagine = $Image_Class->resizer_main($kmultiplenew."input_".$tablefield_array_r['Field'],$file_size[$tablefield_array_r['Field']][0],$file_size[$tablefield_array_r['Field']][1],'',$namenew)){
                            $imagine="";
                            if (in_array($tablefield_array_r['Field'],$require_array)){
                                if ($kmultiple<=0)
                                    $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                                if ($kmultiple>0){
                                    $condtoinsert=false;
                                }
                            }
                        }else{
                            $variable[$tablefield_array_r['Field']]=$imagine;
                            if ($kmultiple>=0){
                                $condtoinsert=true;
                            }
                        }
                    }
                    if (in_array($tablefield_array_r['Field'],$copy_from)){
                        if( ! $imagine = $Image_Class->resizer_main($kmultiplenew."input_".$copy_from_val[$tablefield_array_r['Field']],$file_size[$tablefield_array_r['Field']][0],$file_size[$tablefield_array_r['Field']][1],'',$namenew."-thumb")){
                            $imagine="";
                            if (in_array($tablefield_array_r['Field'],$require_array)){                                                                                                                   if ($kmultiple<=0)
                                $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                            }

                        }else{
                            $variable[$tablefield_array_r['Field']]=$imagine;

                        }
                    }

                    if (in_array($tablefield_array_r['Field'],$password)){
                        $variable[$tablefield_array_r['Field']]=md5($variable[$tablefield_array_r['Field']]);
                    }
                    if (in_array($tablefield_array_r['Field'],$copy_from_id)){
                        $variable[$tablefield_array_r['Field']]=$copy_from_id_value[$tablefield_array_r['Field']];
                    }
                    if (in_array($tablefield_array_r['Field'],$email_fields) AND ( !preg_match( $config['emailverif'], $_POST[$kmultiplenew."input_".$tablefield_array_r['Field']] ) ) ){
                        $var["error"] .= $lang["msg_error_email"].$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                    }

                    if (in_array($tablefield_array_r['Field'],$date_fields)){
                        $var['tpl_input_day']=$_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_day"];
                        $var['tpl_input_month']=$_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_month"];
                        $var['tpl_input_year'] = $_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_year"];

                        if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
                            $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
                        }
                        $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']}";
                        $variable[$tablefield_array_r['Field']]=date ("Y-m-d",strtotime($variable[$tablefield_array_r['Field']]));
                    }
                    if (in_array($tablefield_array_r['Field'],$datetime_fields)){
                        $var['tpl_input_day']=$_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_day"];
                        $var['tpl_input_month']=$_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_month"];
                        $var['tpl_input_year'] = $_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_year"];
                        $var['tpl_input_time'] = $_POST[$kmultiplenew."input_".$tablefield_array_r['Field']."_time"];
                        if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
                            $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
                        }
                        $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']} {$var['tpl_input_time']}";
                        $variable[$tablefield_array_r['Field']]=date ("Y-m-d G:i:s",strtotime($variable[$tablefield_array_r['Field']]));
                    }
                    if (in_array($tablefield_array_r['Field'],$require_array)){
                        if ($kmultiple<=0)
                            $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                    }
                    $variable[$tablefield_array_r['Field']]=PrepareForStore($variable[$tablefield_array_r['Field']]);
                    $sql_input.=" , `".$tablefield_array_rinitial."` ";
                    $sql_input_val.=" , '".$variable[$tablefield_array_r['Field']]."' ";
                }
            }
            @mysqli_free_result($result);

            if ($var['error']==""){

                if ($condtoinsert){
                    $sqlfinal .= "INSERT INTO `{$config['table_prefix']}$default_tabel` "
                        ." ( `$id_` $sql_input )"
                        ." VALUES ( '' "
                        ." $sql_input_val )||||";
                }

                $sql_input_val='';
                $sql_input='';
                //return ( $output.=$Global_Class->search($default_tabel,$default_option,"",0,$config['nrresult'],$relation,$relation_table,$default_option."_search_rows",$sql_default_global,$search_fields, $id_, $lang["tpl_".$default_option."_add"]) );

            }
        }

        if ($var['error']==""){
            $sqlfinalarr=explode("||||",$sqlfinal);
            foreach ($sqlfinalarr as $sqlfinal){
                if ($sqlfinal!=''){
                    $result = $db->query($sqlfinal);

                    global $_COOKIE;
                    $row = array(
                        "admin"=>$_COOKIE['id_cookie'],
                        "action"=>$lang['logging']['add']." ".$lang["tpl_auto_".$_REQUEST['p']]." ( ".mysqli_insert_id().": ".$variable[$config['admin_section'][$_REQUEST['p']]['field_name_for_delete']].") ",
                        "sql"=>"$sqlfinal"
                    );

                    addlogging( $row );
                }
            }
            $var_temp_return[0]=true;
            $var_temp_return[1]=$lang["tpl_".$default_option."_add"];
            $var_temp_return[2]=mysqli_insert_id();
            if ($kmultiple==$config['auto_multiple'][$default_tabel]-1 or $config['auto_multiple'][$default_tabel]<=0){
                return $var_temp_return;
            }
        }
        $var_temp_return[0]=false;
        $var_temp_return[1]=$var['error'];
        return $var_temp_return;
    }

//gallery add end


    function add_($default_tabel,$default_option,$default_option2,$varchar,$text,$file,$dropdown,$dropdownval,$radio,$radioval,$checkbox,$password,$date_fields, $error=""){
        global $config,$lang;
        global $db,$tpl,$_POST,$datetime_fields,$text_fields_wysiwyg,$settings_profile; //class
        global $javascript_special,$dropdownval_onchange,$datetime_fields;
        $var_initial =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "redirect"=>$redirect,
            "error"=>$error
        );
        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        $i=0;
        if (!is_array($varchar)) $varchar=array();
        if (!is_array($text)) $text=array();
        if (!is_array($file)) $file=array();
        if (!is_array($dropdown)) $dropdown=array();
        if (!is_array($date_fields)) $date_fields=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($text_fields_wysiwyg)) $text_fields_wysiwyg=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($radio)) $radio=array();
        while ($tablefield_array_r = mysqli_fetch_array($result)){

            $tablefield_array[]=$tablefield_array_r['Field'];

        }
        @mysqli_free_result($result);
        $tablefield_arraynew=array();
        if (!is_array($config['admin_section'][$default_tabel]['order']))$config['admin_section'][$default_tabel]['order']=array();
        foreach ($config['admin_section'][$default_tabel]['order'] as $key=>$val){
            if (in_array($val,$tablefield_array)){
                $tablefield_arraynew[]=$val;
            }
        }
        foreach ($tablefield_array as $key=>$val){
            if (!in_array($val,$tablefield_arraynew)){
                $tablefield_arraynew[]=$val;
            }
        }
        //print_r($tablefield_array);
        foreach ($tablefield_arraynew as $kkk=>$vvv){
            $tablefield_array_r['Field']=$vvv;
            $aaate = "\"$vvv\", ";
            $j=$i;
            if ($i%2) $var['class_temp']="class_temp1";
            else $var['class_temp']="class_temp2";

            $var['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
            $var['tpl_input_name']="input_".$tablefield_array_r['Field'];
            $var['tpl_input_name_val']=$_POST["input_".$tablefield_array_r['Field']];

            if (in_array($tablefield_array_r['Field'],$varchar)){
                $var['maxlength']=$config['varchar_special_maxlength'][$tablefield_array_r['Field']];
                $var['size']=($config['varchar_special_maxlength'][$tablefield_array_r['Field']]>$config['default_size_for_fields_inbackend'])?$config['default_size_for_fields_inbackend']:$config['varchar_special_maxlength'][$tablefield_array_r['Field']];                         $var['size']=$config['varchar_special_maxlength'][$tablefield_array_r['Field']];
                $var['goodchars']=$config['varchar_special_maxlength_goodchars'][$tablefield_array_r['Field']];
                if (in_array($tablefield_array_r['Field'],$config['default_fields_in_admin'])){
                    $var['goodchars'] = $config['default_digits'];
                    $var['size'] = $config['default_fieldssize_in_admin'];
                    $var['maxlength'] = $config['default_fieldssize_in_admin'];
                }
                if ($var['maxlength']!='' and $var['goodchars']!=''){
                    $out1.=$tpl->replace($var,"global_add_varcharspecial.html");
                }else{
                    $out1.=$tpl->replace($var,"global_add_varchar.html");
                }
            }elseif (in_array($tablefield_array_r['Field'],$text)){
                $var['rows']=$config['rows'];
                $var['cols']=$config['cols'];

                if (in_array($tablefield_array_r['Field'],$text_fields_wysiwyg) and $settings_profile['wysiwyg']==1) {

                    $oFCKeditor = new FCKeditor($var['tpl_input_name']) ;
                    $oFCKeditor->BasePath = $config['wywiwyg_editor'] ;                // '/FCKeditor/' is the default value so this line could be deleted.
                    $oFCKeditor->DefaultLanguage = $config['wywiwyg_DefaultLanguage'];
                    $oFCKeditor->Value = $var['tpl_input_name_val'] ;
                    if ($default_option=="cars" or $default_option=="carsdealer" ) {
                        $oFCKeditor->ToolbarSet = 'Description' ;
                    }
                    $var['wywiwyg_value'] = $oFCKeditor->CreateFCKeditor( $var['tpl_input_name'], $var['cols']*$config['wywiwyg_sizecols'],$var['rows']*$config['wywiwyg_sizerows'] ) ;

                    $file_text_use = "global_add_wywiwyg.html";
                }else{
                    $var['wywiwyg_value'] = '';
                    $file_text_use = "global_add_text.html";
                }
                $out1.=$tpl->replace($var,$file_text_use);

            }elseif (in_array($tablefield_array_r['Field'],$file)){
                $out1.=$tpl->replace($var,"global_add_file.html");
            }elseif (in_array($tablefield_array_r['Field'],$dropdown)){
                $var['tpl_input_name_val']=$dropdownval[$tablefield_array_r['Field']];
                $var['onchange'] = $dropdownval_onchange [$tablefield_array_r['Field']];
                if ($default_option=="sponsored" and $tablefield_array_r['Field']==$config["admin_section_sponsored_field_id"]){
                    $out1.=$tpl->replace($var,"global_add_dropdown2.html");
                }elseif ($javascript_special[$tablefield_array_r['Field']]!=''){
                    $var['modelsArray']=$javascript_special[$tablefield_array_r['Field']];
                    $out1.=$tpl->replace($var,"global_add_dropdown_special.html");
                }else{
                    $out1.=$tpl->replace($var,"global_add_dropdown.html");
                }
            }elseif (in_array($tablefield_array_r['Field'],$radio)){
                $radio_explode=explode("|#",$radioval[$tablefield_array_r['Field']]);
                $var_temp_val['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                foreach($radio_explode as $key=>$val){
                    $var_temp_val['tpl_input_name']="input_".$tablefield_array_r['Field'];
                    $value_radio = explode("|",$val);
                    if ($value_radio[1]=='') {
                        $value_radio[1]=$value_radio[0];
                    }
                    $var_temp_val['tpl_input_name_val']=$value_radio[1];
                    $var_temp_val['tpl_input_name_value']=$value_radio[0];
                    if ($_POST["input_".$tablefield_array_r['Field']]==""){
                        $_POST["input_".$tablefield_array_r['Field']]=$value_radio[0];
                    }
                    if ($_POST["input_".$tablefield_array_r['Field']]==$value_radio[0]){
                        $var_temp_val['checked']="checked";
                    }
                    $out_temp1.=$tpl->replace($var_temp_val,"global_add_radio_val.html");
                    unset($var_temp_val);
                }
                $var['tpl_input_name_val']=$out_temp1;
                $out_temp1="";
                $out1.=$tpl->replace($var,"global_add_radio.html");
            }elseif (in_array($tablefield_array_r['Field'],$checkbox)){
                if ($_POST["input_".$tablefield_array_r['Field']]=="on")
                    $var['tpl_input_name_val']="checked";

                if ($default_option=="rights" AND preg_match("/_add|_view|_delete/",$tablefield_array_r['Field']) ){
                    $var1=$var;
                    $templang = explode("_",$tablefield_array_r['Field']);
                    $var1['tpl_name']=$lang["tpl_auto_".$templang[1]];
                    $outtemp123.=$tpl->replace($var1,"global_add_checkbox_rights_repeat.html");
                    $i--;
                }elseif ($default_option=="rights" AND preg_match("/_edit/",$tablefield_array_r['Field']) ){
                    $var1=$var;
                    $var1['tpl_name']=$lang["tpl_auto_edit"];
                    $outtemp123.=$tpl->replace($var1,"global_add_checkbox_rights_repeat.html");
                    $var['repeat'] = $outtemp123;
                    $outtemp123="";
                    $out1.=$tpl->replace($var,"global_add_checkbox_rights.html");
                }else {
                    $out1.=$tpl->replace($var,"global_add_checkbox.html");
                }
            }elseif (in_array($tablefield_array_r['Field'],$password)){
                $out1.=$tpl->replace($var,"global_add_password.html");
            }elseif (in_array($tablefield_array_r['Field'],$date_fields)){
                /*
                             $var['tpl_input_day']="input_".$tablefield_array_r['Field']."_day";
                             $var['tpl_input_month']="input_".$tablefield_array_r['Field']."_month";
                             $var['tpl_input_year'] = "input_".$tablefield_array_r['Field']."_year";

                             $var['tpl_input_day_val'] = $this->days($_POST[$var['tpl_input_day']]);
                             $var['tpl_input_month_val'] = $this->months($_POST[$var['tpl_input_month']]);
                             $var['tpl_input_year_val'] = $this ->years($_POST[$var['tpl_input_year']]);
                             */
                $var['tpl_input_date']="input_".$tablefield_array_r['Field'];
                $var['tpl_input_date_val']=$_POST["input_".$tablefield_array_r['Field']];

                $out1.=$tpl->replace($var,"global_add_date.html");
            }elseif (in_array($tablefield_array_r['Field'],$datetime_fields)){
                $var['tpl_input_day']="input_".$tablefield_array_r['Field']."_day";
                $var['tpl_input_month']="input_".$tablefield_array_r['Field']."_month";
                $var['tpl_input_year'] = "input_".$tablefield_array_r['Field']."_year";
                $var['tpl_input_time'] = "input_".$tablefield_array_r['Field']."_time";
                $var['tpl_input_day_val'] = $this->days($_POST[$var['tpl_input_day']]);
                $var['tpl_input_month_val'] = $this->months($_POST[$var['tpl_input_month']]);
                $var['tpl_input_year_val'] = $this ->years($_POST[$var['tpl_input_year']]);
                $var['tpl_input_time_val'] = $_POST[$var['tpl_input_time']];
                $out1.=$tpl->replace($var,"global_add_datetime.html");
            }else{
                $i--;
            }
            if ($j==$i){
                //echo $aaate;
            }
            unset($var);
            $i++;
        }
        $var_initial['options']=$out1;
        if ($config['config2_multiple_options'][0]) {
            $var_initial['options'] .= $config['config2_multiple_options'][1];
        }

        $var_initial['what_add']=$lang["tpl_auto_$default_option"];
        if (count($varchar)==1){
            $var_initial['explain']=$lang['tpl_auto_You_can_input_can_have_multiple_values_separated_by_commas'];
        }
        $outtemp=$tpl->replace($var_initial,"global_add.html");
        return ($outtemp);
    }
    function add1_($default_tabel,$default_option,$default_option2,$fields,$file,$file_size,$relation,$relation_table,$copy_from,$copy_from_val,$require_array,$password,$copy_from_id,$copy_from_id_value,$email_fields,$search_fields,$date_fields, $id_, $error=""){
        global $config,$lang,$IMG_WIDTH_FLAG,$IMG_HEIGHT_FLAG,$_POST,$varchar_fields;
        global $db,$tpl,$Image_Class,$Global_Class; //class
        global $sql_default_global,$datetime_fields;
        $var =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "redirect"=>$redirect,
            "error"=>$error
        );

        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        if (!is_array($fields)) $fields=array();
        if (!is_array($email_fields)) $email_fields=array();
        if (!is_array($date_fields)) $date_fields=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($radio)) $radio=array();
        $value_toexplode_array=array();
        $tablefinal=array();
        while ($tablefield_array_r = mysqli_fetch_array($result)){
            if (is_array($tablefield_array_r)){
                $tablefinal[] = $tablefield_array_r;
            }
            if (!in_array($tablefield_array_r['Field'],$fields)){

                if (in_array($tablefield_array_r['Field'],$varchar_fields)){
                    if (preg_match("/,/",$_POST["input_".$tablefield_array_r['Field']]) and count($varchar_fields)==1){
                        $value_toexplode=explode(",",$_POST["input_".$tablefield_array_r['Field']]);
                        foreach ($value_toexplode as $keytemp=>$valtemp){
                            $valtemp = trim($valtemp);
                            if ($valtemp!='');
                            $value_toexplode_array[]=$valtemp;
                        }
                    }else{
                        $value_toexplode_array=array($_POST["input_".$tablefield_array_r['Field']]);
                    }
                }
            }
        }
        @mysqli_free_result($result);
        if (count($value_toexplode_array)==0) $value_toexplode_array=array(1);
        foreach ($value_toexplode_array as $keyexplode=>$valexplode){
            //foreach ($valexplode as $key1_=>$val1_)
            {
                //echo $keyexplode." - $val1_<Br>";
                if (count($varchar_fields)==1){
                    $_POST["input_".$varchar_fields[0]]= $valexplode;
                }

                foreach  ($tablefinal as $tablefield_array_r){
                    if (!in_array($tablefield_array_r['Field'],$fields)){

                        $tablefield_array[]=$tablefield_array_r['Field'];
                        $variable[$tablefield_array_r['Field']]=$_POST["input_".$tablefield_array_r['Field']];

                        if (in_array($tablefield_array_r['Field'],$file)){
                            if( ! $imagine = $Image_Class->resizer_main("input_".$tablefield_array_r['Field'],$file_size[$tablefield_array_r['Field']][0],$file_size[$tablefield_array_r['Field']][1])){
                                $imagine="";
                                if (in_array($tablefield_array_r['Field'],$require_array)){
                                    $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                                }
                            }else{
                                $variable[$tablefield_array_r['Field']]=$imagine;
                            }
                        }
                        if (in_array($tablefield_array_r['Field'],$copy_from)){
                            if( ! $imagine = $Image_Class->resizer_main("input_".$copy_from_val[$tablefield_array_r['Field']],$file_size[$tablefield_array_r['Field']][0],$file_size[$tablefield_array_r['Field']][1])){
                                $imagine="";
                                if (in_array($tablefield_array_r['Field'],$require_array)){
                                    $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                                }
                            }else{
                                $variable[$tablefield_array_r['Field']]=$imagine;
                            }
                        }

                        if (in_array($tablefield_array_r['Field'],$password)){
                            $variable[$tablefield_array_r['Field']]=md5($variable[$tablefield_array_r['Field']]);
                        }
                        if (in_array($tablefield_array_r['Field'],$copy_from_id)){
                            $variable[$tablefield_array_r['Field']]=$copy_from_id_value[$tablefield_array_r['Field']];
                        }
                        if (in_array($tablefield_array_r['Field'],$email_fields) AND ( !preg_match( $config['emailverif'], $_POST["input_".$tablefield_array_r['Field']] ) ) ){
                            $var["error"] .= $lang["msg_error_email"].$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                        }

                        if (in_array($tablefield_array_r['Field'],$date_fields)){
                            $temp = explode("-",$_POST["input_".$tablefield_array_r['Field']]);
                            $var['tpl_input_day']=$temp[0];
                            $var['tpl_input_month']=$temp[1];
                            $var['tpl_input_year'] = $temp[2];

                            if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
                                $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
                            }

                            $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']}";
                            $variable[$tablefield_array_r['Field']]=@date ("Y-m-d",strtotime($variable[$tablefield_array_r['Field']]));
                        }
                        if (in_array($tablefield_array_r['Field'],$datetime_fields)){
                            $var['tpl_input_day']=$_POST["input_".$tablefield_array_r['Field']."_day"];
                            $var['tpl_input_month']=$_POST["input_".$tablefield_array_r['Field']."_month"];
                            $var['tpl_input_year'] = $_POST["input_".$tablefield_array_r['Field']."_year"];
                            $var['tpl_input_time'] = $_POST["input_".$tablefield_array_r['Field']."_time"];
                            if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
                                $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
                            }
                            $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']} {$var['tpl_input_time']}";
                            $variable[$tablefield_array_r['Field']]=date ("Y-m-d G:i:s",strtotime($variable[$tablefield_array_r['Field']]));
                        }
                        if (in_array($tablefield_array_r['Field'],$require_array)){
                            $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                        }
                        $variable[$tablefield_array_r['Field']]=PrepareForStore($variable[$tablefield_array_r['Field']]);
                        $sql_input.=" , `".$tablefield_array_r['Field']."` ";
                        $sql_input_val.=" , '".$variable[$tablefield_array_r['Field']]."' ";
                        if ($var['error']!=""){
                            //$var['error'].=$config['use_point_after_error'].$_POST["input_".$tablefield_array_r['Field']].$config['use_br_after_error'];
                        }
                    }
                }
                //echo $var['error'];
                if ($var['error']==""){
                    $sql = "INSERT INTO `{$config['table_prefix']}$default_tabel` "
                        ." ( `$id_` $sql_input )"
                        ." VALUES ( '' "
                        ." $sql_input_val );";
                    //echo "<br />";
                    $sql_input_val="";
                    $sql_input="";
                    $result = $db->query($sql,__FILE__,__LINE__);
                    //return ( $output.=$Global_Class->search($default_tabel,$default_option,"",0,$config['nrresult'],$relation,$relation_table,$default_option."_search_rows",$sql_default_global,$search_fields, $id_, $lang["tpl_".$default_option."_add"]) );
                    //echo count($varchar_fields);
                    //exit;

                    global $_COOKIE;
                    $row = array(
                        "admin"=>$_COOKIE['id_cookie'],
                        "action"=>$lang['logging']['add']." ".$lang["tpl_auto_".$_REQUEST['p']]." ( ".mysqli_insert_id().": ".$variable[$config['admin_section'][$_REQUEST['p']]['field_name_for_delete']].") ",
                        "sql"=>"$sqlfinal"
                    );

                    addlogging( $row );

                    if (count($varchar_fields)!=1){
                        if ($var['error']==""){
                            $var_temp_return[0]=true;
                            $var_temp_return[1]=$lang["tpl_".$default_option."_add"];
                            $var_temp_return[2]=mysqli_insert_id();
                            return $var_temp_return;
                        }
                    }
                }

            }//end foreach
        }
        //exit;

        if ($var['error']==""){
            $var_temp_return[0]=true;
            $var_temp_return[1]=count($value_toexplode_array)." ".$lang["tpl_".$default_option."_add"];
            $var_temp_return[2]=mysqli_insert_id();
            return $var_temp_return;
        }
        $var_temp_return[0]=false;
        $var_temp_return[1]=$var['error'];
        return $var_temp_return;
    }
    function edit($id,$default_tabel,$default_option,$default_option2,$varchar,$text,$file,$dropdown,$dropdownval,$radio,$radioval,$checkbox,$password,$id_,$date_fields, $profile, $error=""){
        global $config,$lang;
        global $db,$tpl,$_POST,$datetime_fields,$text_fields_wysiwyg,$settings_profile; //class
        global $javascript_special,$dropdownval_onchange,$datetime_fields;
        $var_initial =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "id"=>"$id",
            "redirect"=>$redirect,
            "error"=>$error
        );
        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        $i=0;
        if (!is_array($varchar)) $varchar=array();
        if (!is_array($text)) $text=array();
        if (!is_array($file)) $file=array();
        if (!is_array($dropdown)) $dropdown=array();
        if (!is_array($date_fields)) $date_fields=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($text_fields_wysiwyg)) $text_fields_wysiwyg=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($radio)) $radio=array();
        while ($tablefield_array_r = mysqli_fetch_array($result)){

            $tablefield_array[]=$tablefield_array_r['Field'];

        }
        @mysqli_free_result($result);
        $tablefield_arraynew=array();
        if (!is_array($config['admin_section'][$default_tabel]['order']))$config['admin_section'][$default_tabel]['order']=array();
        foreach ($config['admin_section'][$default_tabel]['order'] as $key=>$val){
            if (in_array($val,$tablefield_array)){
                $tablefield_arraynew[]=$val;
            }
        }
        foreach ($tablefield_array as $key=>$val){
            if (!in_array($val,$tablefield_arraynew)){
                $tablefield_arraynew[]=$val;
            }
        }
        //print_r($tablefield_array);
        foreach ($tablefield_arraynew as $kkk=>$vvv){
            $tablefield_array_r['Field']=$vvv;
            //echo "\"".$vvv."\", ";

            if ($vvv=='adprofiles' and $default_tabel=='settings'){
                if ($i%2) $var['class_temp']="class_temp1";
                else $var['class_temp']="class_temp2";
                $var['tpl_name']='';
                $var['tpl_input_name_val']="<h1>".$lang['tpl_auto_paymentsettings']."</h1>";
                $out1.= $tpl->replace($var,"global_add_see.html");
                $i++;
            }

            if ($i%2) $var['class_temp']="class_temp1";
            else $var['class_temp']="class_temp2";

            $var['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
            $var['tpl_input_name']="input_".$tablefield_array_r['Field'];
            if ($error!="") {
                $var['tpl_input_name_val']=$_POST["input_".$tablefield_array_r['Field']];
            }else {
                $var['tpl_input_name_val']=$profile[$tablefield_array_r['Field']];
            }

            if (in_array($tablefield_array_r['Field'],$varchar)){
                $var['maxlength']=$config['varchar_special_maxlength'][$tablefield_array_r['Field']];
                $var['size']=($config['varchar_special_maxlength'][$tablefield_array_r['Field']]>$config['default_size_for_fields_inbackend'])?$config['default_size_for_fields_inbackend']:$config['varchar_special_maxlength'][$tablefield_array_r['Field']];                         $var['size']=$config['varchar_special_maxlength'][$tablefield_array_r['Field']];
                $var['goodchars']=$config['varchar_special_maxlength_goodchars'][$tablefield_array_r['Field']];
                if (in_array($tablefield_array_r['Field'],$config['default_fields_in_admin'])){
                    $var['goodchars'] = $config['default_digits'];
                    $var['size'] = $config['default_fieldssize_in_admin'];
                    $var['maxlength'] = $config['default_fieldssize_in_admin'];
                }

                if ($var['maxlength']!='' and $var['goodchars']!='' ){
                    $out1.=$tpl->replace($var,"global_add_varcharspecial.html");
                }elseif ($default_tabel=='bannersettings'){
                    $out1.=$tpl->replace($var,"global_add_varcharcolor.html");
                }else{
                    $out1.=$tpl->replace($var,"global_add_varchar.html");
                }
            }elseif (in_array($tablefield_array_r['Field'],$text)){
                $var['rows']=$config['rows'];
                $var['cols']=$config['cols'];
                if (in_array($tablefield_array_r['Field'],$text_fields_wysiwyg) and $settings_profile['wysiwyg']==1) {


                    $oFCKeditor = new FCKeditor($var['tpl_input_name']) ;
                    $oFCKeditor->BasePath = $config['wywiwyg_editor'] ;                // '/FCKeditor/' is the default value so this line could be deleted.
                    $oFCKeditor->DefaultLanguage = $config['wywiwyg_DefaultLanguage'];
                    $oFCKeditor->Value = $var['tpl_input_name_val'] ;
                    if ($default_option=="cars" or $default_option=="carsdealer" ) {
                        $oFCKeditor->ToolbarSet = 'Description' ;
                    }
                    $var['wywiwyg_value'] = $oFCKeditor->CreateFCKeditor( $var['tpl_input_name'], $var['cols']*$config['wywiwyg_sizecols'],$var['rows']*$config['wywiwyg_sizerows'] ) ;

                    $file_text_use = "global_add_wywiwyg.html";
                }else{
                    $var['wywiwyg_value'] = '';
                    $file_text_use = "global_add_text.html";
                }
                $out1.=$tpl->replace($var,$file_text_use);

            }elseif (in_array($tablefield_array_r['Field'],$file)){
                //$var['tpl_input_name_val']=$profile[$tablefield_array_r['Field']];
                if ($profile[$tablefield_array_r['Field']] != null){
                    //$var['tpl_input_name_val']="";
                    //foreach($file as $key=>$val){
                    //if ($tablefield_array_r['Field'])
                    list($width, $height, $type, $attr) = @getimagesize("{$config['temp']}{$profile[$tablefield_array_r['Field']]}");
                    if ($width>500){
                        $addbefore="<a href=\"{$config['url_path_temp']}{$profile[$tablefield_array_r['Field']]}\" target=\"_blank\" border=\"0\">";
                        $addafter="</a>";
                        $newwidth=" width=\"500\" ";
                    }else{
                        $addbefore="";
                        $addafter="";
                        $newwidth="";

                    }
                    $var['tpl_input_name_val']=$addbefore."<img src=\"{$config['url_path_temp']}{$profile[$tablefield_array_r['Field']]}\"$newwidth>$addafter\n<br>";
                    //}
                    $var['tpl_input_name_val'].="<input type=\"checkbox\" name=\"nopicture_".$tablefield_array_r['Field']."\">".$lang["tpl_Delete_Image"]."<br>\n";
                }
                $out1.=$tpl->replace($var,"global_add_file.html");
            }elseif (in_array($tablefield_array_r['Field'],$dropdown)){
                $var['tpl_input_name_val']=$dropdownval[$tablefield_array_r['Field']];
                $var['onchange'] = $dropdownval_onchange[$tablefield_array_r['Field']];
                if ($default_option=="sponsored" and $tablefield_array_r['Field']==$config["admin_section_sponsored_field_id"]){
                    $out1.=$tpl->replace($var,"global_add_dropdown2.html");
                }elseif ($javascript_special[$tablefield_array_r['Field']]!=''){
                    $var['modelsArray']=$javascript_special[$tablefield_array_r['Field']];
                    $out1.=$tpl->replace($var,"global_add_dropdown_special.html");
                }else{
                    $out1.=$tpl->replace($var,"global_add_dropdown.html");
                }

            }elseif (in_array($tablefield_array_r['Field'],$radio)){
                $radio_explode=explode("|#",$radioval[$tablefield_array_r['Field']]);
                $var_temp_val['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                foreach($radio_explode as $key=>$val){
                    $var_temp_val['tpl_input_name']="input_".$tablefield_array_r['Field'];
                    $value_radio = explode("|",$val);
                    if ($value_radio[1]=='') {
                        $value_radio[1]=$value_radio[0];
                    }
                    $var_temp_val['tpl_input_name_val']=$value_radio[1];
                    $var_temp_val['tpl_input_name_value']=$value_radio[0];
                    if ($profile[$tablefield_array_r['Field']]==$value_radio[0]){
                        $var_temp_val['checked']="checked";
                    }
                    $out_temp1.=$tpl->replace($var_temp_val,"global_add_radio_val.html");
                    unset($var_temp_val);
                }
                $var['tpl_input_name_val']=$out_temp1;
                $out_temp1="";
                $out1.=$tpl->replace($var,"global_add_radio.html");
            }elseif (in_array($tablefield_array_r['Field'],$checkbox)){
                if ($profile[$tablefield_array_r['Field']]=="1")
                    $var['tpl_input_name_val']="checked";
                else
                    $var['tpl_input_name_val']="";

                if ($default_option=="rights" AND preg_match("/_add|_view|_delete/",$tablefield_array_r['Field']) ){
                    $var1=$var;
                    $templang = explode("_",$tablefield_array_r['Field']);
                    $var1['tpl_name']=$lang["tpl_auto_".$templang[1]];
                    $outtemp123.=$tpl->replace($var1,"global_add_checkbox_rights_repeat.html");
                    $i--;
                }elseif ($default_option=="rights" AND preg_match("/_edit/",$tablefield_array_r['Field']) ){
                    $var1=$var;
                    $var1['tpl_name']=$lang["tpl_auto_edit"];
                    $outtemp123.=$tpl->replace($var1,"global_add_checkbox_rights_repeat.html");
                    $var['repeat'] = $outtemp123;
                    $outtemp123="";
                    $out1.=$tpl->replace($var,"global_add_checkbox_rights.html");
                }else {
                    $out1.=$tpl->replace($var,"global_add_checkbox.html");
                }

            }elseif (in_array($tablefield_array_r['Field'],$password)){
                $out1.=$tpl->replace($var,"global_add_password.html");
            }elseif (in_array($tablefield_array_r['Field'],$date_fields)){
                /*
                            $var['tpl_input_day']="input_".$tablefield_array_r['Field']."_day";
                            $var['tpl_input_month']="input_".$tablefield_array_r['Field']."_month";
                            $var['tpl_input_year'] = "input_".$tablefield_array_r['Field']."_year";
                            if ($error!="") {
                                    $var['tpl_input_day_val']=$_POST[$var['tpl_input_day']];
                                    $var['tpl_input_month_val']=$_POST[$var['tpl_input_month']];
                                    $var['tpl_input_year_val']=$_POST[$var['tpl_input_year']];
                            }else {
                                    $days_month_year=explode("-",$profile[$tablefield_array_r['Field']]);
                                    $var['tpl_input_day_val']=$days_month_year[2];
                                    $var['tpl_input_month_val']=$days_month_year[1];
                                    $var['tpl_input_year_val']=$days_month_year[0];
                            }
                             $var['tpl_input_day_val'] = $this->days($var['tpl_input_day_val']);
                             $var['tpl_input_month_val'] = $this->months($var['tpl_input_month_val']);
                             $var['tpl_input_year_val'] = $this->years($var['tpl_input_year_val']);
                                                        */
                $var['tpl_input_date']="input_".$tablefield_array_r['Field'];
                $var['tpl_input_date_val']=$_POST[$tablefield_array_r['Field']];
                if ($error!="") {
                    $var['tpl_input_date_val']=$_POST["input_".$tablefield_array_r['Field']];
                }else {
                    $temp = explode("-",$profile[$tablefield_array_r['Field']]);
                    $var['tpl_input_date_val']=$temp[2]."-".$temp[1]."-".$temp[0];
                }
                $out1.=$tpl->replace($var,"global_add_date.html");
            }elseif (in_array($tablefield_array_r['Field'],$datetime_fields)){
                $var['tpl_input_day']="input_".$tablefield_array_r['Field']."_day";
                $var['tpl_input_month']="input_".$tablefield_array_r['Field']."_month";
                $var['tpl_input_year'] = "input_".$tablefield_array_r['Field']."_year";
                $var['tpl_input_time'] = "input_".$tablefield_array_r['Field']."_time";
                if ($error!="") {
                    $var['tpl_input_day_val']=$_POST[$var['tpl_input_day']];
                    $var['tpl_input_month_val']=$_POST[$var['tpl_input_month']];
                    $var['tpl_input_year_val']=$_POST[$var['tpl_input_year']];
                    $var['tpl_input_time_val']=$_POST[$var['tpl_input_time']];
                }else {
                    $days_month_year=explode("-",$profile[$tablefield_array_r['Field']]);
                    $days_month_y = explode(" ",$days_month_year[2]);
                    $var['tpl_input_day_val']=$days_month_y[0];
                    $var['tpl_input_time_val']=$days_month_y[1];
                    $var['tpl_input_month_val']=$days_month_year[1];
                    $var['tpl_input_year_val']=$days_month_year[0];
                }

                $var['tpl_input_day_val'] = $this->days($var['tpl_input_day_val']);
                $var['tpl_input_month_val'] = $this->months($var['tpl_input_month_val']);
                $var['tpl_input_year_val'] = $this->years($var['tpl_input_year_val']);
                $out1.=$tpl->replace($var,"global_add_datetime.html");
            }else{
                $i--;
            }
            unset($var);
            $i++;
        }

        if ($default_tabel=='cars'){
            if ($i%2) $var['class_temp']="class_temp1";
            else $var['class_temp']="class_temp2";
            if (!$config['addfeaturesoncars']){
                $var['tpl_name']='';
                $var['tpl_input_name_val']="<h1>".$lang['tpl_auto_features']."</h1>";
                $config['config2_multiple_options'][1] .= $tpl->replace($var,"global_add_see.html");
                $i++;
                $config['config2_multiple_options'][0]=1;
                global  $language_set;
                $config['config2_multiple_options'][1].=$this->getcheckbox($_REQUEST['id'],"features","name{$language_set}","id","name{$language_set}","carsfeatures","carsid","featuresid");
            }
        }
        if ($default_tabel=='cars'){
            $var['tpl_name']='';
            $var['tpl_input_name_val']="<h1>".$lang['tpl_auto_'.$default_option]."</h1>";
            $out1 = $tpl->replace($var,"global_add_see.html").$out1;
        }
        $var_initial['options']=$out1;
        if ($config['config2_multiple_options'][0]) {
            $var_initial['options'] .= $config['config2_multiple_options'][1];
        }
        //$var_initial['options']=$out1;
        //$var_initial['what_add']=$lang["tpl_auto_$default_option"];
        $outtemp=$tpl->replace($var_initial,"global_edit.html");
        return ($outtemp);
    }
    function edit1 ($id,$default_tabel,$default_option,$default_option2,$fields,$file,$file_size,$relation,$relation_table,$copy_from,$copy_from_val,$require_array,$password,$copy_from_id,$copy_from_id_value,$email_fields,$id_, $search_fields, $date_fields){
        global $config,$lang,$IMG_WIDTH_FLAG,$IMG_HEIGHT_FLAG,$_POST;
        global $db,$tpl,$Image_Class,$Global_Class; //class
        global $sql_default_global,$datetime_fields;
        $var =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "id"=>"$id",
            "redirect"=>$redirect,
            "error"=>$error
        );

        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        if (!is_array($fields)) $fields=array();
        if (!is_array($email_fields)) $email_fields=array();
        if (!is_array($date_fields)) $date_fields=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($radio)) $radio=array();
        $profile=$this->getprofile($id,$default_tabel,$id_);
        while ($tablefield_array_r = mysqli_fetch_array($result)){
            if (!in_array($tablefield_array_r['Field'],$fields)){
                $tablefield_array[]=$tablefield_array_r['Field'];
                $variable[$tablefield_array_r['Field']]=$_POST["input_".$tablefield_array_r['Field']];

                if ($_POST['nopicture_'.$tablefield_array_r['Field']]!="on"){
                    if (in_array($tablefield_array_r['Field'],$file)){
                        if ($default_tabel=='gallery' and $tablefield_array_r['Field']=='picture'){
                            $namenew=$_POST[$kmultiplenew."input_description"];
                            $oid=$_POST["oid"];

                            $oidp = $Global_Class->getprofile( $oid,"cars","id" );
                            $mak = $Global_Class->getprofile( $oidp['make'],"make","id" );
                            $mod = $Global_Class->getprofile( $oidp['model'],"model","id" );


                            $namenew=makeurl2($oid."-".$mak['name']."-".$mod['name']."-".$namenew);
                        }
                        if( ! $imagine = $Image_Class->resizer_main("input_".$tablefield_array_r['Field'],$file_size[$tablefield_array_r['Field']][0],$file_size[$tablefield_array_r['Field']][1],'',$namenew)){
                            $imagine=$profile[$tablefield_array_r['Field']];
                            if ($profile[$tablefield_array_r['Field']]!='' && $_POST["input_".$tablefield_array_r['Field']]==''){

                            }else
                                if (in_array($tablefield_array_r['Field'],$require_array)){
                                    $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                                }
                            $variable[$tablefield_array_r['Field']]=$imagine;
                        }else{
                            //$profile=$this->getprofile($id,$default_tabel);
                            @unlink($config["temp"].$profile[$tablefield_array_r['Field']]);
                            $variable[$tablefield_array_r['Field']]=$imagine;
                        }
                    }
                    if (in_array($tablefield_array_r['Field'],$copy_from)){
                        if( ! $imagine = $Image_Class->resizer_main("input_".$copy_from_val[$tablefield_array_r['Field']],$file_size[$tablefield_array_r['Field']][0],$file_size[$tablefield_array_r['Field']][1],'',$namenew."-thumb")){
                            if ($_POST['nopicture_'.$copy_from_val[$tablefield_array_r['Field']]]!="on"){
                                $imagine=$profile[$tablefield_array_r['Field']];
                            }
                            if (in_array($tablefield_array_r['Field'],$require_array)){
                                $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                            }
                            $variable[$tablefield_array_r['Field']]=$imagine;
                        }else{
                            //$profile=$this->getprofile($id,$default_tabel);
                            @unlink($config["temp"].$profile[$tablefield_array_r['Field']]);
                            $variable[$tablefield_array_r['Field']]=$imagine;
                        }
                    }



                    if ( (in_array($tablefield_array_r['Field'],$require_array))&&(in_array($tablefield_array_r['Field'],$password)) ){
                        //echo $tablefield_array_r['Field'];
                        //print_r($_POST);
                        $var['error'].= ($variable[$tablefield_array_r['Field']]=="") ? $lang['error_'.$default_option][$tablefield_array_r['Field']] : "";
                    }
                }else{
                    //$profile=$this->getprofile($id,$default_tabel);
                    @unlink($config["temp"].$profile[$tablefield_array_r['Field']]);
                    $variable[$tablefield_array_r['Field']]="";
                    foreach($copy_from_val as $key_copy_from=>$val_copy_from){
                        if ($val_copy_from == $tablefield_array_r['Field']){
                            @unlink($config["temp"].$profile[$key_copy_from]);
                        }
                    }
                }
                //$profile=$this->getprofile($id,$default_tabel);
                /*
                         foreach($copy_from as $key=>$val){
                                  @unlink($config["temp"].$profile[$val]);
                         }    */


                if (in_array($tablefield_array_r['Field'],$copy_from_id)){
                    $variable[$tablefield_array_r['Field']]=$copy_from_id_value[$tablefield_array_r['Field']];
                }

                if (in_array($tablefield_array_r['Field'],$email_fields) AND ( !preg_match( $config['emailverif'], $_POST["input_".$tablefield_array_r['Field']] ) ) ){
                    $var["error"] .= $lang["msg_error_email"].$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                }
                if (in_array($tablefield_array_r['Field'],$date_fields)){
                    $temp = explode("-",$_POST["input_".$tablefield_array_r['Field']]);
                    $var['tpl_input_day']=$temp[0];
                    $var['tpl_input_month']=$temp[1];
                    $var['tpl_input_year'] = $temp[2];


                    if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
                        $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
                    }
                    $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']} {$var['tpl_input_time']}";
                    $variable[$tablefield_array_r['Field']]=@date ("Y-m-d",strtotime($variable[$tablefield_array_r['Field']]));
                }
                if (in_array($tablefield_array_r['Field'],$datetime_fields)){
                    $var['tpl_input_day']=$_POST["input_".$tablefield_array_r['Field']."_day"];
                    $var['tpl_input_month']=$_POST["input_".$tablefield_array_r['Field']."_month"];
                    $var['tpl_input_year'] = $_POST["input_".$tablefield_array_r['Field']."_year"];
                    $var['tpl_input_time'] = $_POST["input_".$tablefield_array_r['Field']."_time"];

                    if ((!@checkdate($var['tpl_input_month'],$var['tpl_input_day'],$var['tpl_input_year'])) ) {
                        $var["error"] .= $lang["error_date"].$lang['table_'.$default_option][$tablefield_array_r['Field']];
                    }
                    $variable[$tablefield_array_r['Field']]="{$var['tpl_input_year']}-{$var['tpl_input_month']}-{$var['tpl_input_day']} {$var['tpl_input_time']}";
                    $variable[$tablefield_array_r['Field']]=date ("Y-m-d G:i:s",strtotime($variable[$tablefield_array_r['Field']]));
                }
                if (in_array($tablefield_array_r['Field'],$password)){
                    if ($variable[$tablefield_array_r['Field']]!=""){
                        $variable[$tablefield_array_r['Field']]=md5($variable[$tablefield_array_r['Field']]);
                        $sql_input.=" , `".$tablefield_array_r['Field']."` = ";
                        $sql_input.=" '".$variable[$tablefield_array_r['Field']]."' ";
                    }
                }else{

                    $variable[$tablefield_array_r['Field']]=PrepareForStore($variable[$tablefield_array_r['Field']]);
                    $sql_input.=" , `".$tablefield_array_r['Field']."` = ";
                    $sql_input.=" '".$variable[$tablefield_array_r['Field']]."' ";
                }

            }
        }
        @mysqli_free_result($result);
        if ($var['error']==""){
            $sql = "UPDATE `{$config['table_prefix']}$default_tabel` SET `$id_`='$id' "
                ." $sql_input WHERE `$id_`='$id' limit 1";
            $result = $db->query($sql,__FILE__,__LINE__);

            global $_COOKIE;
            $row = array(
                "admin"=>$_COOKIE['id_cookie'],
                "action"=>$lang['logging']['edit']." ".$lang["tpl_auto_".$_REQUEST['p']]." ( ".$id.": ".$variable[$config['admin_section'][$_REQUEST['p']]['field_name_for_delete']].") ",
                "sql"=>"$sql"
            );

            addlogging( $row );
            if ($default_tabel=='cars'){
                $this->insertcheckbox($id,'features','name','id','name','carsfeatures','carsid','featuresid');
            }
            return array( true,$lang["tpl_".$default_option."_modify"]." ( {$lang[tpl_auto_id]}: $id )" );
        }
        $var_temp_return[0]=false;
        $var_temp_return[1]=$var['error'];
        return $var_temp_return;
    }

    function days($day=0,$today=0){
        global $config,$lang;
        global $db; //database
        if ($today==0) {
            $out .= "<option";
            $out .= " value='"."'>".$lang["tpl_day"]."</option>\n";
        }
        for ($i=1;$i<32;$i++){
            $out .= "<option";
            $out .= ($i == $day ) ? " selected": "";
            $out .= " value='".$i."'>".$i."</option>\n";
        }
        return ($out);
    }
    function months($month=0,$today=0){
        global $config;
        global $db,$lang; //database
        if ($today==0) {
            $out .= "<option";
            $out .= " value='"."'>".$lang["tpl_month"]."</option>\n";
        }
        for ($i=1;$i<=12;$i++){
            $out .= "<option";
            $out .= ($i == $month ) ? " selected": "";
            $out .= " value='".$i."'>".$config["months_name"][$i-1]."</option>\n";
        }
        return ($out);
    }
    function years($year=0,$today=0){
        global $config;
        global $db,$config,$lang; //database
        if ($today==1) {
            $config["config_auto_year_start"]=date("Y");
        }else{
            $out .= "<option";
            $out .= " value='"."'>".$lang["tpl_year"]."</option>\n";
        }
        for ($i=$config["config_auto_year_start"];$i<=$config["config_auto_year_finish"];$i++){
            $out .= "<option";
            $out .= ($i == $year ) ? " selected": "";
            $out .= " value='".$i."'>".$i."</option>\n";
        }
        return ($out);
    }

    function choose_option(){
        global $default_tabel,$p,
               $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,
               $radio_fields,$radioval,$checkbox_fields,$password_fields,
               $default_id, $file, $file_size,$relation, $relation_table, $copy_from, $copy_from_val,
               $require_array, $password, $copy_from_id,$copy_from_id_value,$field_name, $fields_not_show ,
               $error,$output_add, $email_fields, $id_ , $search_fields ,        $date_fields , $user_profile, $filearray,
               $session_activate_name,$session_parent,$field_activate, $text_fields_wysiwyg;
        global $Global_Class,$right_cookie;
        global $config,$lang;
        global $_REQUEST,$HTTP_POST_VARS;
        global $db,$tpl; //class
        global $sql_default_global,$HTTP_SESSION_VARS,$_POST;
        global $Global_Class,$right_cookie, $tablefield_array_options,
               $settings_profile, $multiplelanguage,$multiplefields,$multiplefields_text;

        if (!is_array($tablefield_array_options)) {
            $tablefield_array_optionsay=array();
        }
        if (!is_array($multiplefields)) {
            $multiplefields=array();
        }
        if (!is_array($multiplelanguage)) {
            $multiplelanguage=array();
        }
        if (!is_array($multiplefields_text)) {
            $multiplefields_text=array();
        }
        if (!is_array($text_fields_wysiwyg)) {
            $text_fields_wysiwyg=array();
        }
        foreach ($multiplefields as $mul_key=>$mul_val) {
            $count_mul = 0;
            foreach ($multiplelanguage as $multiple_key=>$multiple_val) {
                if ($count_mul==0) {
                    $count_mul = 1;
                }
                $varchar_fields[] = "$mul_val".$multiple_key;
                $lang['tabel_'.$default_tabel]["$mul_val".$multiple_key] = "<font class=\"languageadmin\">[ ".$multiple_val."] </font> ".$lang['tabel_'.$default_tabel]["$mul_val"];
                if ($config['varchar_special_maxlength'][$default_tabel][$mul_val]!=''){
                    $config['varchar_special_maxlength']["$mul_val".$multiple_key]=$config['varchar_special_maxlength'][$default_tabel][$mul_val];
                }
                if ($config['varchar_special_maxlength_goodchars'][$default_tabel][$mul_val]!=''){
                    $config['varchar_special_maxlength_goodchars']["$mul_val".$multiple_key]=$config['varchar_special_maxlength_goodchars'][$default_tabel][$mul_val];
                }
            }
            if ($count_mul==1) {
                $lang['tabel_'.$default_tabel]["$mul_val"] = "<font class=\"languageadmin\">[ ".ucfirst(substr($settings_profile['language'],0,-4))."] </font> ".$lang['tabel_'.$default_tabel]["$mul_val"];
            }

        }

        foreach ($multiplefields_text as $mul_key=>$mul_val) {
            $count_mul = 0;
            foreach ($multiplelanguage as $multiple_key=>$multiple_val) {
                if ($count_mul==0) {
                    $count_mul = 1;
                }
                $text_fields[] = "$mul_val".$multiple_key;
                if (in_array($mul_val,$text_fields_wysiwyg) ) {
                    $text_fields_wysiwyg[] = "$mul_val".$multiple_key;
                }
                $lang['tabel_'.$default_tabel]["$mul_val".$multiple_key] = "<font class=\"languageadmin\">[ ".$multiple_val."] </font> ".$lang['tabel_'.$default_tabel]["$mul_val"];
            }
            if ($count_mul==1) {
                $lang['tabel_'.$default_tabel]["$mul_val"] = "<font class=\"languageadmin\">[ ".ucfirst(substr($settings_profile['language'],0,-4))."] </font> ".$lang['tabel_'.$default_tabel]["$mul_val"];
            }

        }

        $o = $_REQUEST['o'];
        $oo = preg_replace( "0|1|2|3", "", $o );
        $oo = preg_replace( "activate|deactivate|sold", "edit", $oo );
        $oo_bold=$oo;
        if ( $o == "search" or $o=="see") $oo = "view";
        if ( ( ( $right_cookie[$p.'_view'] == 0 ) && ( $oo == "" ) ) || ( ( $right_cookie[$p.'_' . $oo] == 0 ) && ( $oo != "" ) ) )
        {
            $output .= $lang["error_permission"];
            return $output;
        }

        if ( $right_cookie[$p.'_add'] == 0 ){
            $lang["tpl_auto_Add"]="";
        }

        if ( $right_cookie[$p.'_edit'] == 0 ){
            $lang["tpl_Edit"]="";
        }

        if ( $right_cookie[$p.'_delete'] == 0 ){
            $lang["tpl_auto_Delete"]="";
            $config[config_auto_deletedelete]=" style='display:none;'";
        }
        if ( $oo_bold == "" or $oo_bold == "edit" or $oo_bold == "see") $oo_bold = "view";
        $var[$oo_bold."_start"]="[ <b>";
        $var[$oo_bold."_finish"]="</b> ]";

        $var['classadd'] = "ClassNormal";
        $var['classview'] = "ClassNormal";
        $var['classsearch'] = "ClassNormal";
        $var['class'.$oo_bold] = "ClassBold";


        $var['p']=$p;
        if ($session_activate_name!="" or ($p=='cars' and ( $o=='edit' or $o=='see'))) {
            if ($p=='cars' and ( $o=='edit' or $o=='see')){
                $session_parent='cars';
                $_SESSION['option_oid1']=$_REQUEST['id'];
            }else{
                $var[session_activate]="<a href=\"index.php?p=$session_parent&amp;o=see&amp;id={$HTTP_SESSION_VARS['option_oid1']}\">( ".$lang['tpl_auto_click_here_to_go_to']. $lang['tpl_auto_'.$session_parent]." <b>".$session_activate_name."</b> )</a> ";
            }

//start

            if ($session_parent=='cars' ){


                $tablefield_array_options1 = $config['admin_section'][$session_parent]['tablefield_array_options'];
                $tablefield_array_options_val1=$config['admin_section'][$session_parent]['tablefield_array_options_val'];



                foreach($tablefield_array_options1 as $key=>$val){

                    //if ($number_temp)
                    if ( !in_array($val,array('availabilitydays','availabilitydaysbooking','agentsdiscount'))){
                        $number_temp = $this->getnumrows($_SESSION['option_oid1'],$val,$tablefield_array_options_val1[$val]);

                        $var_initial[$val]="<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"index.php?p=$val&amp;o=view&amp;oid={$_SESSION['option_oid1']}&amp;page=0\">".$lang["tpl_auto_array_option_$val"]."<B>[{$number_temp}]</B></a>";
                    }else{
                        $var_initial[$val]="<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"index.php?p=$val&amp;o=view&amp;id={$_SESSION['option_oid1']}&amp;oid={$_SESSION['option_oid1']}&amp;page=0\"  onclick=\"OpenWindow(this.href,'$val', '{$config[admin_width_popup]}', '{$config[admin_height_popup]}','yes'); return false\" target=\"_blank\">".$lang["tpl_auto_array_option_$val"]."</a>";
                    }

                }
                $var[session_activate].="<br />";
                foreach($tablefield_array_options1 as $key=>$val){
                    $var_fields['fields']=$var_initial[$val];
                    $var[session_activate].=$var_fields['fields'] . "  ";
                }
                $var[session_activate] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"index.php?p=$session_parent&amp;o=edit&amp;id={$_SESSION['option_oid1']}\">".$lang["tpl_Edit"]."</a>  ";
                $var[session_activate] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"index.php?p=$session_parent&amp;o=see&amp;id={$_SESSION['option_oid1']}\">".$lang["tpl_auto_See"]."</a>  ";
                if ($lang["tpl_auto_See_in_site"]!=''){
                    $var['session_activate'] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"../index.php?p=details&amp;id={$_SESSION['option_oid1']}\" class=\"edit\" target='_blank'>".$lang["tpl_auto_See_in_site"]."</a>  ";
                    $var['session_activate'] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"../index.php?p=vehicle_information2&amp;id={$_SESSION['option_oid1']}\" class=\"edit\" target='_blank'>".$lang["tpl_auto_See_in_site1"]."</a>  ";
                }

            }

//end
        }else{
            $var[adminmenu4hide]=" style=\"display:none;\"";
        }
        if (!is_array($config['admin_not_delete_need'])) $config['admin_not_delete_need']=array();
        if (!in_array($p,$config['admin_not_delete_need'] ) ) {
            $output.=$tpl->replace($var,"admin_menu_global.html"); //read header
        }else{
            if (!in_array($p,$config['admin_special_menu'] ) ){
                if ($o==''){
                    $o='see';
                    $_REQUEST['id']=1;
                }
            }
        }

        switch ( $o )
        {
            case "add":
                if ($default_tabel=='gallery'){

                    $output .= $Global_Class->addgallery( $default_tabel, $p, "add1", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, $date_fields,$HTTP_POST_VARS['error'] );
                }else{
                    $output .= $Global_Class->add( $default_tabel, $p, "add1", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, $date_fields,$HTTP_POST_VARS['error'] );

                }
                break;
            case "add1":
                if ($output_add[1]=="") {
                    if ($default_tabel=='gallery'){
                        $output_add = $Global_Class->addgallery1( $default_tabel, $p, "", $default_id, $file,$file_size,$relation,$relation_table,$copy_from,$copy_from_val,$require_array,$password,$copy_from_id,$copy_from_id_value, $email_fields, $search_fields, $date_fields, $id_ );

                    }else{
                        $output_add = $Global_Class->add1( $default_tabel, $p, "", $default_id, $file,$file_size,$relation,$relation_table,$copy_from,$copy_from_val,$require_array,$password,$copy_from_id,$copy_from_id_value, $email_fields, $search_fields, $date_fields, $id_ );
                    }
                }
                if ( $output_add[0] == false )
                {
                    if ($default_tabel=='gallery'){

                        $output .= $Global_Class->addgallery( $default_tabel, $p, "add1", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, $date_fields , $output_add[1] );
                    }else{
                        $output .= $Global_Class->add( $default_tabel, $p, "add1", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, $date_fields , $output_add[1] );
                    }
                }
                else
                {
                    $user_profile = $Global_Class->getprofile(  $output_add[2], $default_tabel, $id_ );
                    if ($default_tabel=='gallery'){
                        $output .= $Global_Class->search( $default_tabel, $p, "", 0, $config['nrresult'], $relation, $relation_table, "{$p}_search_rows", $sql_default_global, $search_fields, $id_, $output_add[2]);
                    }else{
                        $output .= $Global_Class->see( $output_add[2], $default_tabel, $p, "edit1", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, $id_, $date_fields, $user_profile, $output_add[1] );
                    }

                    //$output .= $output_add;
                }
                break;
            case "delete":
                $output1 = $Global_Class->deletemultiple( $_POST['options_array'], $p, $lang["msg1_$p"], $lang["msg2_$p"], $field_name, $filearray, $id_ );
                $output .= $Global_Class->search( $default_tabel, $p, "", 0, $config['nrresult'], $relation, $relation_table, "{$p}_search_rows", $sql_default_global, $search_fields, $id_, $output1 );
                break;
            case "edit":
                if ( $Global_Class->getprofile( $_REQUEST['id'], $default_tabel, $id_,$sql_default_global) )
                {
                    $output .= $Global_Class->edit( $_REQUEST['id'], $default_tabel, $p, "edit1", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, $id_, $date_fields, $user_profile, $output_add[1] );
                }
                else
                {
                    $output .= $Global_Class->search( $default_tabel, $p, "", 0, $config['nrresult'], $relation, $relation_table, "{$p}_search_rows", $sql_default_global, $search_fields, $id_, $error = "" );
                }
                break;
            case "edit1":
                if ( $Global_Class->getprofile( $_REQUEST['id'], $default_tabel, $id_,$sql_default_global ) )
                {
                    if ($output_add[1]=="") {
                        $output_add = $Global_Class->edit1( $_REQUEST['id'], $default_tabel, $p, "", $default_id, $file,$file_size,$relation,$relation_table,$copy_from,$copy_from_val,$require_array,$password,$copy_from_id,$copy_from_id_value, $email_fields, $id_ , $search_fields, $date_fields);
                    }
                    if ( $output_add[0] == false )
                    {
                        $output .= $Global_Class->edit( $_REQUEST['id'], $default_tabel, $p, "edit1", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, $id_, $date_fields, $user_profile, $output_add[1] );
                    }
                    else
                    {
                        $user_profile = $Global_Class->getprofile(  $_REQUEST['id'], $default_tabel, $id_,$sql_default_global );
                        $output .= $Global_Class->see( $_REQUEST['id'], $default_tabel, $p, "see", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, $id_, $date_fields, $user_profile, $output_add[1] );
                        //$output .= $output_add;
                    }
                }
                else
                {
                    $output .= $Global_Class->search( $default_tabel, $p, "", 0, $config['nrresult'], $relation_id, $relation_table, "{$p}_search_rows", $sql_default_global, $search_fields, $id_, $error );
                }
                break;
            case "see":
                if ( $user_profile=$Global_Class->getprofile( $_REQUEST['id'], $default_tabel, $id_,$sql_default_global) )
                {
                    $output .= $Global_Class->see( $_REQUEST['id'], $default_tabel, $p, "see", $varchar_fields, $text_fields,$file_fields,$dropdown_fields,$dropdownval,$radio_fields,$radioval,$checkbox_fields,$password_fields, $id_, $date_fields, $user_profile, $output_add[1] );
                }
                else
                {
                    $output .= $Global_Class->search( $default_tabel, $p, "", 0, $config['nrresult'], $relation, $relation_table, "{$p}_search_rows", $sql_default_global, $search_fields, $id_, $error = "" );
                }
                break;
            case "activate":

                $output1 = $Global_Class->activatemultiple($_POST['options_array'],$default_tabel,$field_activate,1,$id_);
                $output .= $Global_Class->search( $default_tabel, $p, "", 0, $config['nrresult'], $relation, $relation_table, "{$p}_search_rows", $sql_default_global, $search_fields, $id_, $output1 );
                break;
            case "deactivate":
                $output1 = $Global_Class->activatemultiple($_POST['options_array'],$default_tabel,$field_activate,0,$id_);
                $output .= $Global_Class->search( $default_tabel, $p, "", 0, $config['nrresult'], $relation, $relation_table, "{$p}_search_rows", $sql_default_global, $search_fields, $id_, $output1 );
                break;
            case "sold":
                $output1 = $Global_Class->activatemultiple($_POST['options_array'],$default_tabel,$field_activate,2,$id_);
                $output .= $Global_Class->search( $default_tabel, $p, "", 0, $config['nrresult'], $relation, $relation_table, "{$p}_search_rows", $sql_default_global, $search_fields, $id_, $output1 );
                break;
            case "search":
                $output .= $Global_Class->search1( $default_tabel, $p, $fields_not_show );
                $output .= $Global_Class->search( $default_tabel, $p, "", 1, $config['nrresult'], $relation, $relation_table, "{$p}_search_rows", $sql_default_global, $search_fields, $id_, $error );
                break;
            default:
                $output .= $Global_Class->search( $default_tabel, $p, "", 0, $config['nrresult'], $relation, $relation_table, "{$p}_search_rows", $sql_default_global, $search_fields, $id_, $error);
                break;
        }

        return $output;
    }
    function getdropdown_array($val,$array){
        if (!is_array($array)) {
            $array=array();
        }
        foreach ($array as $key=>$val_){
            if ($val==$val_) {
                $selected=" selected";
            }else{
                $selected="";
            }
            $out .= "<option$selected value='$val_'>$val_</option>\n";
        }
        return $out;
    }
    function getdropdown_array1($val,$array){
        global $lang;
        if (!is_array($array)) {
            $array=array();
        }
        foreach ($array as $key=>$val_){
            if ($val==$val_) {
                $selected=" selected";
            }else{
                $selected="";
            }
            $out .= "<option$selected value='$val_'>".$lang['tpl_auto_'.$val_]."</option>\n";
        }
        return $out;
    }
    function getdropdown_array_listing($val,$array){
        global $lang;
        if (!is_array($array)) {
            $array=array();
        }
        foreach ($array as $key=>$val_){
            if ($val==$val_) {
                $selected=" selected";
            }else{
                $selected="";
            }
            $out .= "<option$selected value='$val_'>".$lang['tabel_cars'][$val_]."</option>\n";
        }
        return $out;
    }
    function getprofile_order($id,$default_tabel,$orderby,$id_=''){
        global $config;
        global $db; //database
        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` ";
        if ($id_!="") {
            $sql .= " WHERE `$id_`='$id'";
        }
        if ($default_tabel=='gallery' and $orderby == 'order'){
            $sql .= " ORDER BY `$orderby` ,`id`   LIMIT 1";
        }else{
            $sql .= " ORDER BY `$orderby` LIMIT 1";
        }
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        if ($num_rows>0){
            $user = mysqli_fetch_assoc($result);
            @mysqli_free_result($result);
            return ($user);
        }else return false;
    }
    //7 iunie 2004 activate multiple
    function activatemultiple($user_activate,$default_tabel,$camp,$valoare,$id_){
        global $config,$tpl,$lang;
        global $db; //class
        $out="";
        if ($config['autoactivatedisabled']){
            return  $lang['tpl_auto_your_account_expired'];
        }
        if ($user_activate=="") $user_activate=array();
        foreach ($user_activate as $key => $value) {
            $this->activate($key,$default_tabel,$camp,$valoare,$id_);
        }

        if (count($user_activate)==0){
            $out=$lang['tpl_auto_There_was_a_problem'].$lang['tpl_auto_No_checkbox_selected_Please_select_at_least_one_checkbox_and_try_again'];
        }else{
            $out = $lang['msg1'];
        }

        return $out;
    }
    function activate($id,$default_tabel,$camp,$valoare,$id_){
        global $config,$right_cookie, $_COOKIE;
        global $db; //database

        $profile=$this->getprofile($id,$default_tabel,$id_);
        /*
               if (!$right_cookie['view_all_listing']) {
                      if ($profile[admin]!=$_COOKIE['id_cookie']) {
                          return false;
                      }
               }
               */
        if (!$config['autoactivatedisabled']){
            $sql = "update `{$config['table_prefix']}$default_tabel` set `$camp`='$valoare' WHERE `$id_`='$id';";
            $result = $db->query($sql,__FILE__,__LINE__);
            return true;
        }else{
            return false;
        }
    }
    function getcheckbox($id,$default_tabel,$orderby,$id_,$name_,$default_tabel_relation,$relation_id1,$relation_id2){
        global $config,$tpl;
        global $db; //database
        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        while ($tablefield_array_r = mysqli_fetch_array($result)){
            $tablefield_array[]=$tablefield_array_r['Field'];
        }
        @mysqli_free_result($result);
        $orderby  = (!in_array($orderby,$tablefield_array)) ? $tablefield_array[0]:$orderby;
        if ($config[orderbyfeatures]!='') $orderby=$config[orderbyfeatures];
        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` ORDER BY $orderby";
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        $i=0;
        if ($num_rows>0){

            while ($user = mysqli_fetch_assoc($result)){
                if ($i%4==0 or $i%4==3) $var['class_temp']="class_temp1";
                else $var['class_temp']="class_temp3";

                $profile=$this->getprofile1($id,$user[$id_],$default_tabel_relation,$relation_id1,$relation_id2);

                if ($profile) {
                    $var['tpl_input_name_val']="checked";
                }else{
                    $var['tpl_input_name_val']="";
                }

                $var['tpl_input_name']="checkbox_".$user[$id_];
                $var['tpl_name']=$user[$name_];

                if ($i % 2 == 1){
                    $var_ = 1;
                }else{
                    $var_ = "";
                }

                $variab['features' . $var_] .=$tpl->replace($var,"global_add_checkbox.html");
                $i++;
            }
            @mysqli_free_result($result);
            $out.=$tpl->replace($variab,"global_add_features.html");
            return ($out);
        }else return false;
    }
    function insertcheckbox($id,$default_tabel,$orderby,$id_,$name_,$default_tabel_relation,$relation_id1,$relation_id2){
        global $config,$tpl,$_REQUEST;
        global $db; //database
        global $_COOKIE;
        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        while ($tablefield_array_r = mysqli_fetch_array($result)){
            $tablefield_array[]=$tablefield_array_r['Field'];
        }
        @mysqli_free_result($result);
        $orderby  = (!in_array($orderby,$tablefield_array)) ? $tablefield_array[0]:$orderby;
        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` ORDER BY $orderby";
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        $i=0;
        if ($num_rows>0){
            $sql = "delete from `{$config['table_prefix']}$default_tabel_relation` WHERE `$relation_id1`='$id'";
            $result_ = $db->query($sql,__FILE__,__LINE__);

            $row = array(
                "admin"=>$_COOKIE['id_cookie'],
                "action"=>$lang['logging']['edit']." ".$lang["tpl_auto_".$_REQUEST['p']]." ( ".$id." ) ",
                "sql"=>"$sql"
            );

            addlogging( $row );

            while ($user = mysqli_fetch_assoc($result)){
                if ($_REQUEST["checkbox_".$user[$id_]]==1) {
                    $sql = "insert into `{$config['table_prefix']}$default_tabel_relation` VALUES ('','$id','{$user[$id_]}');";
                    $result1 = $db->query($sql,__FILE__,__LINE__);

                    $row = array(
                        "admin"=>$_COOKIE['id_cookie'],
                        "action"=>$lang['logging']['edit']." ".$lang["tpl_auto_".$_REQUEST['p']]." ( ".$id." ".$user['name']." ) ",
                        "sql"=>"$sql"
                    );

                    addlogging( $row );

                }
            }
            @mysqli_free_result($result);
            return true;
        }else return false;
    }
    function sendemail($error=""){
        global $tpl,$lang,$settings_profile, $_REQUEST,$config;
        $var = array (
            "error" => $error
        );


        if ($settings_profile['wysiwyg']==1) {
            $oFCKeditor = new FCKeditor("input_message") ;
            $oFCKeditor->BasePath = $config['wywiwyg_editor'] ;                // '/FCKeditor/' is the default value so this line could be deleted.
            $oFCKeditor->DefaultLanguage = $config['wywiwyg_DefaultLanguage'];
            $oFCKeditor->Value = $_REQUEST["input_message"] ;
            $var['wywiwyg_value'] = $oFCKeditor->CreateFCKeditor( "input_message", $config['cols']*$config['wywiwyg_sizecols'],$config['rows']*$config['wywiwyg_sizerows'] ) ;
            $file_text_use = "global_add_wywiwyg.html";
            $var[class_temp]="class_temp1";
            $var[tpl_name]=$lang[tpl_auto_Message];
            $var[textarea]=$tpl->replace($var,$file_text_use);
        }else{
            $var['wywiwyg_value'] = '';
            $file_text_use = "global_add_text.html";
            $var[class_temp]="class_temp1";
            $var[tpl_name]=$lang[tpl_auto_Message];
            $var[cols]=$config['cols'];
            $var[rows]=$config['rows'];
            $var[tpl_input_name]="input_message";
            $var[tpl_input_name_val]=$_REQUEST["input_message"];
            $var[textarea]=$tpl->replace($var,$file_text_use);
        }
        $out = $tpl->replace( $var, "sendemail.html" );
        return $out;

    }
    function getarray($default_tabel,$orderby,$id_,$sqlini=''){
        global $config,$lang;
        global $db; //database

        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` WHERE 1 $sqlini GROUP BY $id_ ORDER BY $orderby";
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        if ($num_rows>0){
            while ($user = mysqli_fetch_assoc($result)){
                $out[] = $user;
            }
            @mysqli_free_result($result);
            return ($out);
        }else return false;
    }
    function getarrayid($default_tabel,$field,$sqlini=''){
        global $config,$lang;
        global $db; //database
        $out=array();
        $sql = "select `$field` from `{$config['table_prefix']}$default_tabel` WHERE 1 $sqlini ";
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        if ($num_rows>0){
            while ($user = mysqli_fetch_assoc($result)){
                $out[] = $user[$field];
            }
            @mysqli_free_result($result);
            return ($out);
        }else return false;
    }
    function sendemail1($error=""){
        global $_REQUEST;
        global $config,$tpl,$Email_class,$lang ;
        global $db; //database
        if ($_REQUEST[input_send]=="" AND $_REQUEST[send_to_all]=="") {
            $var['error'] = $lang['tpl_auto_please_and_email_to_send_or_check_to_send_to_all_members'];
        }
        if ($_REQUEST[input_from]=="") {
            $var['error'] = $lang['tpl_auto_please_type_from_name'];
        }
        if ($_REQUEST[input_fromemail]=="") {
            $var['error'] = $lang['tpl_auto_please_type_fromemail'];
        }
        if ($_REQUEST[input_subject]=="") {
            $var['error'] = $lang['tpl_auto_please_type_subject'];
        }
        if ($_REQUEST[input_message]=="") {
            $var['error'] = $lang['tpl_auto_please_type_message'];
        }
        if ($var['error']!='') {
            $out = $tpl->replace( $var, "sendemail.html" );
            return $out;
        }else{
            if ($_REQUEST[send_to_all]==1){
                $array_members = $this->getarray('members','id','id'," AND `active`=1");
            }else{
                $array_members[0][email] = $_REQUEST[input_send];
                $array_members[0][name] = "";
            }
            $contor =0;
            if (!is_array($array_members)) $array_members = array();
            foreach($array_members as $key=>$val){
                $settings_template['signup_subject'] = stripslashes( preg_replace( "/\{(\w+)\}/e", "\$val[\\1]", $_REQUEST[input_subject] ) );
                $settings_template['signup_body'] = stripslashes( preg_replace( "/\{(\w+)\}/e", "\$val[\\1]", $_REQUEST[input_message] ) );

                $sendresult = $Email_class->emailsend(  $val[email], $val[name], $_REQUEST[input_fromemail], $_REQUEST[input_from], $settings_template['signup_subject'], $settings_template['signup_body'] );

                if ($sendresult) {
                    $error .= ereg_replace("{email}", $val[email], $lang['tpl_auto_Email_send']);
                }else{
                    $error .= ereg_replace("{email}", $val[email], $lang['tpl_auto_Email_not_send']);
                }
                if ($key % $config['send_email_once']==0){
                    sleep($config['waitsecondes']);
                }
            }
            $val123[error] = $error;
            $output = $tpl->replace($val123,"sendemail1.html");
            return $output;
        }

    }

    function getdropdownlanguage($language,$cond=0){
        global $config;
        global $db,$lang; //database

        if (!$cond) {
            $out .= "<option";
            $out .= " value='-1'>".$lang['language_notuse']."</option>\n";
        }

        if ($handle = opendir($config['path'].'language')) {

            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $out .= "<option";
                    $out .= ($file == $language ) ? " selected": "";
                    $file1 = substr($file,0,-4);
                    $out .= " value='".$file."'>".$file1."</option>\n";
                }
            }

            closedir($handle);
        }


        return ($out);

    }
    function sendemailadmin($error=""){
        global $tpl,$lang,$settings_profile, $_REQUEST,$config,$language_set;
        $var = array (
            "error" => $error
        );
        $var['profiles'] = $this->getdropdown( $_REQUEST['profiles'], 'adprofiles', "name{$language_set}", "id", "name{$language_set}" );
        $var['rights'] = $this->getdropdown( $_REQUEST['rights'], 'rights', "name", "id", "name" );
        if ($settings_profile['wysiwyg']==1) {
            $oFCKeditor = new FCKeditor("input_message") ;
            $oFCKeditor->BasePath = $config['wywiwyg_editor'] ;                // '/FCKeditor/' is the default value so this line could be deleted.
            $oFCKeditor->DefaultLanguage = $config['wywiwyg_DefaultLanguage'];
            $oFCKeditor->Value = $_REQUEST["input_message"] ;
            $var['wywiwyg_value'] = $oFCKeditor->CreateFCKeditor( "input_message", $config['cols']*$config['wywiwyg_sizecols'],$config['rows']*$config['wywiwyg_sizerows'] ) ;
            $file_text_use = "global_add_wywiwyg.html";
            $var[class_temp]="class_temp1";
            $var[tpl_name]=$lang[tpl_auto_Message];
            $var[textarea]=$tpl->replace($var,$file_text_use);
        }else{
            $var['wywiwyg_value'] = '';
            $file_text_use = "global_add_text.html";
            $var[class_temp]="class_temp1";
            $var[tpl_name]=$lang[tpl_auto_Message];
            $var[cols]=$config['cols'];
            $var[rows]=$config['rows'];
            $var[tpl_input_name]="input_message";
            $var[tpl_input_name_val]=$_REQUEST["input_message"];
            $var[textarea]=$tpl->replace($var,$file_text_use);
        }
        $out = $tpl->replace( $var, "sendemailadmin.html" );
        return $out;

    }
    function sendemailadmin1($error=""){
        global $_REQUEST;
        global $config,$tpl,$Email_class,$lang ;
        global $db; //database
        if ($_REQUEST[input_send]=="" AND $_REQUEST[send_to_all]=="") {
            $var['error'] = $lang['tpl_auto_please_and_email_to_send_or_check_to_send_to_all_admin'];
        }
        if ($_REQUEST[input_from]=="") {
            $var['error'] = $lang['tpl_auto_please_type_from_name'];
        }
        if ($_REQUEST[input_fromemail]=="") {
            $var['error'] = $lang['tpl_auto_please_type_fromemail'];
        }
        if ($_REQUEST[input_subject]=="") {
            $var['error'] = $lang['tpl_auto_please_type_subject'];
        }
        if ($_REQUEST[input_message]=="") {
            $var['error'] = $lang['tpl_auto_please_type_message'];
        }
        if ($var['error']!='') {
            $out = $tpl->replace( $var, "sendemailadmin.html" );
            return $out;
        }else{
            if ($_REQUEST[send_to_all]==1){
                switch ($_REQUEST['sendtoadmin']){
                    case "alllisting":
                        $array_members = $this->getarray('admin','id','id'," ");
                        break;
                    case "nolisting":
                        $listin_array_id_gallery = $this->getarrayid('cars','admin',$sqlini=' GROUP BY admin');
                        if (!is_array($listin_array_id_gallery)) $listin_array_id_gallery=array();
                        $sql_cond .= " AND ( FIND_IN_SET( {$config['table_prefix']}admin.id, '".implode(",",$listin_array_id_gallery)."' ) = 0 ) ";

                        $array_members = $this->getarray('admin','id','id',$sql_cond);
                        break;
                    case "withlisting":
                        $listin_array_id_gallery = $this->getarrayid('cars','admin',$sqlini=' GROUP BY admin');
                        if (!is_array($listin_array_id_gallery)) $listin_array_id_gallery=array();
                        $sql_cond .= " AND ( FIND_IN_SET( {$config['table_prefix']}admin.id, '".implode(",",$listin_array_id_gallery)."' ) > 0 ) ";

                        $array_members = $this->getarray('admin','id','id',$sql_cond);
                        break;
                    case "profilelisting":
                        $sql_cond .= " AND adprofiles='".$_REQUEST['profiles']."' ";

                        $array_members = $this->getarray('admin','id','id',$sql_cond);

                        break;
                    case "rights":
                        $sql_cond .= " AND `right`='".$_REQUEST['rights']."' ";

                        $array_members = $this->getarray('admin','id','id',$sql_cond);

                        break;
                }
            }else{
                $array_members[0][email] = $_REQUEST[input_send];
                $array_members[0][name] = "";
            }
            $contor =0;
            foreach($array_members as $key=>$val){
                $settings_template['signup_subject'] = stripslashes( preg_replace( "/\{(\w+)\}/e", "\$val[\\1]", $_REQUEST[input_subject] ) );
                $settings_template['signup_body'] = stripslashes( preg_replace( "/\{(\w+)\}/e", "\$val[\\1]", $_REQUEST[input_message] ) );

                $sendresult = $Email_class->emailsend(  $val[email], $val[name], $_REQUEST[input_fromemail], $_REQUEST[input_from], $settings_template['signup_subject'], $settings_template['signup_body'] );

                if ($sendresult) {
                    $error .= ereg_replace("{email}", $val[email], $lang['tpl_auto_Email_send']);
                }else{
                    $error .= ereg_replace("{email}", $val[email], $lang['tpl_auto_Email_not_send']);
                }
                if ($key % $config['send_email_once']==0){
                    sleep($config['waitsecondes']);
                }
            }
            $email_var[error] = $error;
            $output = $tpl->replace($email_var,"sendemail1.html");
            return $output;
        }

    }
    function getjavascriptarray($default_tabel,$orderby,$id_,$name_,$default_tabel1,$orderby1,$id_1,$name_1,$relatedid){
        global $config;
        global $db; //database
        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        while ($tablefield_array_r = mysqli_fetch_array($result)){
            $tablefield_array[]=$tablefield_array_r['Field'];
        }
        @mysqli_free_result($result);
        $orderby  = (!in_array($orderby,$tablefield_array)) ? $tablefield_array[0]:$orderby;
        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` ORDER BY $orderby";
        $result = $db->query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        $contor=0;
        if ($num_rows>0){
            while ($user = mysqli_fetch_assoc($result)){
                /*$sql = "select count(*) from `{$config['table_prefix']}cars` WHERE `make`='{$user[$id_]}' and active>1";
		               //echo "<BR>";
		               $resultccc = $db->query($sql,__FILE__,__LINE__);
		               //$user = mysqli_fetch_assoc($result);
		               list($num_rowsxx) = mysqli_fetch_row($resultccc);
               		if ($num_rowsxx>0){

               		verific daca sunt masini cu acest make
               			*/
                $out .= "\tmodelsID[$contor]=".$user[$id_]."\n\tmodelsArray[".$user[$id_]."] = new Array( ";
                $contor++;
                //second
                $sql1 = "SELECT * FROM `{$config['table_prefix']}$default_tabel1` WHERE $relatedid='".$user[$id_]."' GROUP BY $id_1 ORDER BY $orderby1";
                $result1 = $db->query($sql1);
                $num_rows1 = mysqli_num_rows($result1);
                $out1="";
                if ($num_rows1>0){
                    while ($user1 = mysqli_fetch_assoc($result1)){
                        $user1[$name_1]=htmlspecialchars($user1[$name_1]);
                        $out1 .= "\"".$user1[$id_1]."|".$user1[$name_1]."\",";

                    }
                }
                //end
                $out1 = substr($out1,0,-1);
                $out .= " $out1 ); //".$user[$name_]."\n";
                //}
            }
            @mysqli_free_result($result);
            //echo $out;
            //exit;
            return ($out);
        }else return false;
    }
    function see($id,$default_tabel,$default_option,$default_option2,$varchar,$text,$file,$dropdown,$dropdownval,$radio,$radioval,$checkbox,$password,$id_,$date_fields, $profile, $error=""){
        global $config,$lang;
        global $db,$tpl,$_POST,$datetime_fields,$text_fields_wysiwyg,$settings_profile,$tablefield_array_options; //class
        global $tablefield_array_options_val,$search_fields;
        $var_initial =array (
            "p"=>"$default_option",
            "o"=>"$default_option2",
            "id"=>"$id",
            "redirect"=>$redirect,
            "error"=>$error
        );
        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db->query($sql,__FILE__,__LINE__);
        $i=0;
        if (!is_array($varchar)) $varchar=array();
        if (!is_array($text)) $text=array();
        if (!is_array($file)) $file=array();
        if (!is_array($dropdown)) $dropdown=array();
        if (!is_array($date_fields)) $date_fields=array();
        if (!is_array($datetime_fields)) $datetime_fields=array();
        if (!is_array($text_fields_wysiwyg)) $text_fields_wysiwyg=array();
        if (!is_array($search_fields)) $search_fields=array();
        if (!is_array($tablefield_array_options)) $tablefield_array_options=array();
        if (!is_array($profile)) $profile=array();

        foreach($profile as $key=>$val){
            if (preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/",$profile[$key])) {
                if ($profile[$key] == '0000-00-00') {
                    $profile[$key]="-";
                }else{
                    $profile[$key]=dateformat($config["config_date_format_admin"],strtotime($profile[$key]));;
                }
            }
        }

        //while ($tablefield_array_r = mysqli_fetch_array($result)){
        //$tablefield_array[]=$tablefield_array_r['Field'];

        while ($tablefield_array_r = mysqli_fetch_array($result)){

            $tablefield_array[]=$tablefield_array_r['Field'];

        }
        @mysqli_free_result($result);
        $tablefield_arraynew=array();
        if (!is_array($config['admin_section'][$default_tabel]['order']))$config['admin_section'][$default_tabel]['order']=array();
        foreach ($config['admin_section'][$default_tabel]['order'] as $key=>$val){
            if (in_array($val,$tablefield_array)){
                $tablefield_arraynew[]=$val;
            }
        }
        foreach ($tablefield_array as $key=>$val){
            if (!in_array($val,$tablefield_arraynew)){
                $tablefield_arraynew[]=$val;
            }
        }
        //print_r($tablefield_array);
        foreach ($tablefield_arraynew as $kkk=>$vvv){
            $tablefield_array_r['Field']=$vvv;

            if ($default_tabel=='settings' and $i==0){
                if ($i%2) $var['class_temp']="class_temp1";
                else $var['class_temp']="class_temp2";
                $var['tpl_name']=$lang['tpl_auto_My_Sitemap_URL_is'];
                $var['tpl_input_name_val']=$config['url_path']."sitemap.xml";
                $out1.=$tpl->replace($var,"global_add_see.html");
                $i++;
            }


            if ($i%2) $var['class_temp']="class_temp1";
            else $var['class_temp']="class_temp2";


            $var['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
            $var['tpl_input_name']="input_".$tablefield_array_r['Field'];

            $var['tpl_input_name_val']=($_POST['input_'.$tablefield_array_r['Field']]=='')?$profile[$tablefield_array_r['Field']]:$_POST['input_'.$tablefield_array_r['Field']];

            if (in_array($tablefield_array_r['Field'],$varchar)){
                $out1.=$tpl->replace($var,"global_add_see.html");
            }elseif (in_array($tablefield_array_r['Field'],$text)){
                if ($tablefield_array_r['Field']=='message' and $default_tabel=='paymenthistory'){
                    $var['tpl_input_name_val']="<pre>".$var['tpl_input_name_val']."</pre>";
                }
                $out1.=$tpl->replace($var,"global_add_see.html");

            }elseif (in_array($tablefield_array_r['Field'],$file)){
                //$var['tpl_input_name_val']=$profile[$tablefield_array_r['Field']];
                if ($profile[$tablefield_array_r['Field']] != null){
                    //$var['tpl_input_name_val']="";
                    //foreach($file as $key=>$val){
                    //if ($tablefield_array_r['Field'])

                    list($width, $height, $type, $attr) = @getimagesize("{$config['temp']}{$profile[$tablefield_array_r['Field']]}");
                    if ($width>500){
                        $addbefore="<a href=\"{$config['url_path_temp']}{$profile[$tablefield_array_r['Field']]}\" target=\"_blank\" border=\"0\">";
                        $addafter="</a>";
                        $newwidth=" width=\"500\" ";
                    }else{
                        $addbefore="";
                        $addafter="";
                        $newwidth="";

                    }

                    $var['tpl_input_name_val']=$addbefore."<img src=\"{$config['url_path_temp']}{$profile[$tablefield_array_r['Field']]}\"$newwidth>$addafter\n<br>";

                }
                $out1.=$tpl->replace($var,"global_add_see.html");
            }elseif (in_array($tablefield_array_r['Field'],$dropdown)){
                $var['tpl_input_name_val']=$this->getfromdropdown($dropdownval[$tablefield_array_r['Field']],$profile[$tablefield_array_r['Field']]);
                $out1.=$tpl->replace($var,"global_add_see.html");
            }elseif (in_array($tablefield_array_r['Field'],$radio)){
                $radio_explode=explode("|#",$radioval[$tablefield_array_r['Field']]);
                $var_temp_val['tpl_name']=$lang['tabel_'.$default_option][$tablefield_array_r['Field']];
                foreach($radio_explode as $key=>$val){
                    $var_temp_val['tpl_input_name']="input_".$tablefield_array_r['Field'];
                    $value_radio = explode("|",$val);
                    if ($value_radio[1]=='') {
                        $value_radio[1]=$value_radio[0];
                    }
                    $var_temp_val['tpl_input_name_val']=$value_radio[1];
                    $var_temp_val['tpl_input_name_value']=$value_radio[0];
                    if ($profile[$tablefield_array_r['Field']]==$value_radio[0]){
                        $out_temp1=$value_radio[1];
                    }

                }
                $var['tpl_input_name_val']=$out_temp1;
                $out_temp1="";
                $out1.=$tpl->replace($var,"global_add_see.html");
            }elseif (in_array($tablefield_array_r['Field'],$checkbox)){
                $var['tpl_input_name_val']=($var['tpl_input_name_val']==0)? $lang['no'] : $lang['yes'];

                if (preg_match("/_add/",$tablefield_array_r['Field'])){
                    $temp=preg_replace("_add","",$tablefield_array_r['Field']);
                    $var['tpl_name'].=$lang["tpl_auto_".$temp]." ".$lang["tpl_auto_add"];

                }elseif (preg_match("/_view/",$tablefield_array_r['Field'])){
                    $temp=preg_replace("_view","",$tablefield_array_r['Field']);
                    $var['tpl_name'].=$lang["tpl_auto_".$temp]." ".$lang["tpl_auto_view"];
                }elseif (preg_match("/_delete/",$tablefield_array_r['Field'])){
                    $temp=preg_replace("_delete","",$tablefield_array_r['Field']);
                    $var['tpl_name'].=$lang["tpl_auto_".$temp]." ".$lang["tpl_auto_delete"];
                }elseif (preg_match("/_edit/",$tablefield_array_r['Field'])){
                    //$var['tpl_name'].=$lang["tpl_auto_edit"];
                }
                $out1.=$tpl->replace($var,"global_add_see.html");
            }elseif (in_array($tablefield_array_r['Field'],$password)){
                $var['tpl_input_name_val']="***";
                $out1.=$tpl->replace($var,"global_add_see.html");
            }elseif (in_array($tablefield_array_r['Field'],$date_fields)){
                $out1.=$tpl->replace($var,"global_add_see.html");
            }elseif (in_array($tablefield_array_r['Field'],$datetime_fields)){
                $out1.=$tpl->replace($var,"global_add_see.html");
            }elseif (in_array($tablefield_array_r['Field'],$search_fields)){
                if (ereg("\.jpg",$profile[$tablefield_array_r['Field']])){
                    //$var['tpl_input_name_val']="";
                    //foreach($file as $key=>$val){
                    //if ($tablefield_array_r['Field'])
                    list($width, $height, $type, $attr) = @getimagesize("{$config['temp']}{$profile[$tablefield_array_r['Field']]}");
                    if ($width>500){
                        $addbefore="<a href=\"{$config['url_path_temp']}{$profile[$tablefield_array_r['Field']]}\" target=\"_blank\" border=\"0\">";
                        $addafter="</a>";
                        $newwidth=" width=\"500\" ";
                    }else{
                        $addbefore="";
                        $addafter="";
                        $newwidth="";

                    }
                    $var['tpl_input_name_val']=$addbefore."<img src=\"{$config['url_path_temp']}{$profile[$tablefield_array_r['Field']]}\"$newwidth>$addafter\n<br>";

                }
                $out1.=$tpl->replace($var,"global_add_see.html");

            }else{
                $i--;
            }

            unset($var);
            $i++;
        }
        @mysqli_free_result($result);
        $var_initial['options']=$out1;
        $var_initial['what_add']=$lang["tpl_auto_$default_option"];

        foreach($tablefield_array_options as $key=>$val){
            $number_temp = $this->getnumrows($id,$val,$tablefield_array_options_val[$val]);
            $var_initial[$val]="<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"index.php?p=$val&amp;o=view&amp;oid={$id}&amp;page=0\">".$lang["tpl_auto_array_option_$val"]."<B>[{$number_temp}]</B></a>";
        }
        $var_initial['edit']=" ";
        foreach($tablefield_array_options as $key=>$val){
            $var_fields['fields']=$var_initial[$val];

            $var_initial['edit'].=$var_fields['fields'] . "  ";
        }
        if ($config['config_auto_oid']!=''){
            $oidnew="&amp;oid={$config['config_auto_oid']}";
        }else{
            $oidnew="";
        }
        $var_initial['edit'] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"index.php?p=$default_option&amp;o=edit&amp;id={$id}{$oidnew}\">".$lang["tpl_Edit"]."</a>  ";
        $var_initial['edit'] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"index.php?p=$default_option&amp;o=see&amp;id={$id}{$oidnew}\">".$lang["tpl_auto_See"]."</a>  ";
        if ($default_tabel=='cars' and $lang["tpl_auto_See_in_site"]!=''){
            $var['session_activate'] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"../index.php?p=details&amp;id={$id}\" class=\"edit\" target='_blank'>".$lang["tpl_auto_See_in_site"]."</a>  ";
            $var['session_activate'] .= "<img src=\"../images/button.gif\" border=0>&nbsp;<a href=\"../index.php?p=vehicle_information2&amp;id={$id}\" class=\"edit\" target='_blank'>".$lang["tpl_auto_See_in_site1"]."</a>  ";
        }
        //0-{{id}}_car_{{year1}}_{{make1}}_{{model1}}.html


        $outtemp=$tpl->replace($var_initial,"global_see.html");
        return ($outtemp);
    }
}
?>