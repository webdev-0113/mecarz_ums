<?php
class VisitClass{
    function sitemapxml()
    {
        global $db, $Global_Class, $tpl,$language_set,$multiplelanguage;
        global $config, $_REQUEST, $lang, $IMG_HEIGHT,$IMG_WIDTH,$settings_profile,$_SESSION, $_COOKIE,$array_lang;

        if ($_SESSION['orderby']==""){
            $orderby="date_add";
        }else{
            $orderby=$_SESSION['orderby'];
        }
        if ($_SESSION['method']==""){
            $method="desc";
        }else{
            $method=$_SESSION['method'];
        }

        $variable = array (
            "nrresult"=>9999999,
            "page"=>$_REQUEST['page'],
            "agent"=>$_REQUEST['agent']
        );
        $out='<?xml version="1.0" encoding="UTF-8"'.'?'.'>'."\n";
        $out.='<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">'."\n";
        if (!is_array($multiplelanguage)) $multiplelanguage=array(""=>"");

        sort($multiplelanguage);
        foreach ($array_lang as $language_set=>$nname){

            if (count($array_lang)>1){
                $language_set1=$language_set;
                $_SESSION['language_session']=$language_set;;
                $language_session1="&amp;language_session=".$language_set;
                $language_set1=$language_set1.'-';
            }
            if ($language_set==0) $language_set="";
            $out .= $this->category_contentxml($language_set1);
            $sql = "SELECT * FROM `{$config['table_prefix']}cars` WHERE {$config['table_prefix']}cars.active >= 1  ";//LIMIT {$variable['page']},{$variable['nrresult']};

            $result = $db -> query($sql);
            $num_rows = mysqli_num_rows( $result );
            $contor=0;
            if ( $num_rows > 0 ) {
                while ( $user = mysqli_fetch_assoc( $result ) ) {

                    $user = $this->prepareuser($user);

                    foreach ($user as $k=>$v){
                        $user[$k.'1'] = makeurl($v);
                    }
                    //print_r($user);
                    $user['language_set1']=$language_set1;
                    $user['url']=$config['url_path'].trim($tpl -> replace( $user, "urlsitemap.html" ));
                    $out .= $this->sm_addUrl($user['url'],$this->mysql2date('Y-m-d\TH:i:s+00:00', $user['datam'], false),"",false);

                    //$out .= $tpl -> replace( $user, "listing_sitemap.html" );

                    $contor++;
                } // while
            }
            @mysqli_free_result($result);

        }
        $out.='</urlset>';

        return $out;


    }
    function category_contentxml($language_set1)
    {
        global $db, $Global_Class, $tpl;
        global $config, $_REQUEST, $lang, $language_set;

        $sql = "SELECT id,name from {$config['table_prefix']}category where 1 order by `order` asc";
        $result = $db -> query($sql);
        $num_rows = mysqli_num_rows($result);
        $out = '';
        if ($num_rows > 0){
            while ($user = mysqli_fetch_assoc($result)){
                $user['name'] = $user["name" . "$language_set"];
                $user['name1'] = makeurl($user['name']);
                $user['language_set1']=$language_set1;

                $user[url] = $tpl -> replace( $user, "category_template_xml.html" );
                $user['url']=$config['url_path'].trim($user[url]);
                $out .= $this->sm_addUrl($user['url'],$this->mysql2date('Y-m-d\TH:i:s+00:00', "", false),"",false);
            }

        }
        @mysqli_free_result($result);
        return $out;
    }
    function sm_addUrl($loc,$lastMod,$changeFreq="monthly",$priority=0.5) {
        $sm_freq_names=array("always", "hourly", "daily", "weekly", "monthly", "yearly","never");
        $s="";
        $s.= "\t<url>\n";
        $s.= "\t\t<loc>$loc</loc>\n";
        if(!empty($lastMod) && $lastMod!="0000-00-00T00:00:00+00:00") $s.= "\t\t<lastmod>$lastMod</lastmod>\n";
        if(!empty($changeFreq) && in_array($changeFreq,$sm_freq_names)) $s.= "\t\t<changefreq>$changeFreq</changefreq>\n";
        if($priority!==false && $priority!=="") $s.= "\t\t<priority>$priority</priority>\n";
        $s.= "\t</url>\n";
        return $s;
    }
    function mysql2date($dateformatstring, $mysqlstring, $translate = true) {
        global $month, $weekday, $month_abbrev, $weekday_abbrev;
        $m = $mysqlstring;
        if (empty($m)) {
            return false;
        }
        $i = mktime(substr($m,11,2),substr($m,14,2),substr($m,17,2),substr($m,5,2),substr($m,8,2),substr($m,0,4));
        if (!empty($month) && !empty($weekday) && $translate) {
            $datemonth = $month[date('m', $i)];
            $datemonth_abbrev = $month_abbrev[$datemonth];
            $dateweekday = $weekday[date('w', $i)];
            $dateweekday_abbrev = $weekday_abbrev[$dateweekday];
            $dateformatstring = ' '.$dateformatstring;
            $dateformatstring = preg_replace("/([^\\\])D/", "\\1".backslashit($dateweekday_abbrev), $dateformatstring);
            $dateformatstring = preg_replace("/([^\\\])F/", "\\1".backslashit($datemonth), $dateformatstring);
            $dateformatstring = preg_replace("/([^\\\])l/", "\\1".backslashit($dateweekday), $dateformatstring);
            $dateformatstring = preg_replace("/([^\\\])M/", "\\1".backslashit($datemonth_abbrev), $dateformatstring);

            $dateformatstring = substr($dateformatstring, 1, strlen($dateformatstring)-1);
        }
        $j = @date($dateformatstring, $i);
        if (!$j) {
            // for debug purposes
            //        echo $i." ".$mysqlstring;
        }
        return $j;
    }
    function customlinks($template)
    {
        global $db, $Global_Class, $tpl;
        global $config, $_REQUEST, $language_set;
        $sql = "SELECT * FROM `{$config['table_prefix']}customlinks` WHERE 1 and `active`='1' ORDER BY `order` asc;";
        $result = $db -> query($sql);
        $num_rows = mysqli_num_rows($result);
        $contor = 0;
        $out = '';
        if ($num_rows > 0){
            while ($user = mysqli_fetch_assoc($result)){
                $user['name'] = $user["name" . "$language_set"];
                $user["content"] = $user["content" . "$language_set"];
                $user['name1'] = preg_replace(" |/", "-", $user["name" . "$language_set"]);
                $out .= $tpl -> replace($user, $template);
                // if ($contor%3==0 AND $contor!=0) $out .= "<BR>";
                $contor++;
            } // while
        }
        @mysqli_free_result($result);
        return $out;
    }
    function news_content()
    {
        global $db, $Global_Class, $tpl, $lang;
        global $config, $_REQUEST, $language_set;

        $sql = "SELECT * from {$config['table_prefix']}news where date<=NOW() order by date DESC";
        $result = $db -> query($sql);
        $num_rows = mysqli_num_rows($result);
        $profile = array();
        $out = '';
        if ($num_rows > 0){
            while ($user = mysqli_fetch_assoc($result)){
                $user['date'] = dateformat($config["config_date_format"], strtotime($user['date']));
                $user['name'] = $user["name" . "$language_set"];
                $user["content"] = $user["content" . "$language_set"];
                $user["title"] = $user["title" . "$language_set"];

                // user[content]=nl2br($user[content]);
                $user['href'] = "#{$user['id']}";
                $profile['body'] .= "\n<a name=\"" . $user['id'] . "\">&raquo;</a> \n" . $user['date'] . ": \n" . $user["content" . $language_set] . "\n<br><br>
                 <A href=\"#top\"><IMG height=9 alt=\"{$lang['tpl_auto_Top_of_page']}\"  src=\"images/up.gif\" width=7 border=0></A><A href=#top>{$lang['tpl_auto_Top_of_page']}</A><BR><BR>\n";
                $profile['links'] .= $tpl -> replace($user, "news_template.html");
            }

        }
        @mysqli_free_result($result);
        $out .= $tpl -> replace($profile, "news.html");
        return $out;
    }
    function faq_content()
    {
        global $db, $Global_Class, $tpl;
        global $config, $_REQUEST, $lang, $language_set;

        $sql = "SELECT * from {$config['table_prefix']}faq where 1 order by `order` asc";
        $result = $db -> query($sql);
        $num_rows = mysqli_num_rows($result);

        $profile = array();
        $out = '';
        if ($num_rows > 0){
            while ($user = mysqli_fetch_assoc($result)){
                $user['href'] = "#{$user['id']}";
                // $user[content]=nl2br($user[content]);
                $profile['body'] .= "
                                   <b>&nbsp;<a name={$user['id']}>{$lang['tpl_Q']}</a>: " . $user["name" . "$language_set"] . "</b><br>\n
                                   <b>&nbsp;{$lang['tpl_A']}</b>: " . $user["content{$language_set}"] . "<br><br>
                                                                 <A href=#top><IMG height=9 alt=\"{$lang['tpl_auto_Top_of_page']}\"  src=\"images/up.gif\" width=7 border=0></A><A href=\"#top\">{$lang['tpl_auto_Top_of_page']}</A><BR><BR>\n";
                $user['name'] = $user["name" . "$language_set"];
                $user["content"] = $user["content" . "$language_set"];
                $user["title"] = $user["title" . "$language_set"];
                $profile['links'] .= $tpl -> replace($user, "faq_template.html");
            }

        }
        @mysqli_free_result($result);
        $out .= $tpl -> replace($profile, "faq.html");
        return $out;
    }
    function category_content()
    {
        global $db, $Global_Class, $tpl;
        global $config, $_REQUEST, $lang, $language_set;

        $sql = "SELECT * from {$config['table_prefix']}category where 1 order by `order` asc";
        $result = $db -> query($sql);
        $num_rows = mysqli_num_rows($result);

        $profile = array();
        $out = '';
        if ($num_rows > 0){
            while ($user = mysqli_fetch_assoc($result)){
                $user['name'] = $user["name" . "$language_set"];
                $user['description'] = $user["description" . "$language_set"];
                $user['nr'] = $Global_Class -> getnumrows($user["id"], "cars", "category"," and active>=1 ") ;
                $profile['category'] .= $tpl -> replace($user, "category_template.html");

            }

        }
        @mysqli_free_result($result);
        $out .= $tpl -> replace($profile, "category.html");
        return $out;
    }
    function cars_list($pageoutfin, $nr_car_found,$myproper=0)
    {
        global $db, $Global_Class, $tpl, $language_set;
        global $config, $_REQUEST, $lang, $IMG_HEIGHT, $IMG_WIDTH, $settings_profile, $_SESSION, $HTTP_COOKIE_VARS;

        $tabel_cars = "cars";
        $tabel_gallery = "gallery";
        $tabel_carsfeatures = "carsfeatures";
        $carsid = "carsid";

        $variable = array (
            "nrresult" => $settings_profile['nrpageuser'],
            "page" => $_REQUEST['page'],
            "agent" => $_REQUEST['agent']
        );
        $consearch=false;
        if ($_SESSION['orderby'] == ""){
            $orderby = "date_add";
        }else{
            $orderby = $_SESSION['orderby'];
            $consearch=true;
        }
        if ($_SESSION['method'] == ""){
            $method = "desc";
        }else{
            $method = $_SESSION['method'];
            $consearch=true;
        }

        $array_list = $config["config_search_field"];
        if (!is_array($array_list)) $array_list = array();
        foreach($array_list as $key => $val){
            $variable[$val] = $_SESSION[$val];
            if ($variable[$val]=='...'){
                $variable[$val]='';
            }
        }
        $sql_cond = '';
        $param = '';
        if (!$myproper){

            /*
            if ($variable[city] != "" AND $variable[city] != "..." AND $variable[city] != " "){
                $citytypearray = $Global_Class -> getarrayid("admin","id"," and city='{$variable[city]}' ");
                if (!is_array($citytypearray)) $citytypearray=array();
                $sql_cond .= " AND ( FIND_IN_SET( admin, '".implode(",",$citytypearray)."' ) > 0 ) ";
                $param .= "&amp;city={$variable[city]}";
            }
            */
            if ($variable['price'] != "" and $variable['price1'] != "" ){
                $sql_cond .= " and  ( (price >= '{$variable['price']}' AND price <= '{$variable['price1']}' ) OR ( specialprice >= '{$variable['price']}' AND specialprice <= '{$variable['price1']}'  AND specialprice > '0.0' ) )";
                $param .= "&amp;price={$variable['price']}";
                $param .= "&amp;price1={$variable['price1']}";
                $consearch=true;

            }else
                if ($variable['price'] != ""){
                    $sql_cond .= " and (price >= '{$variable['price']}' OR (specialprice >= '{$variable['price1']}' AND specialprice > '0.0') ) ";
                    $param .= "&amp;price={$variable['price']}";
                    $consearch=true;
                }
                else
                    if ($variable['price1'] != ""){
                        $sql_cond .= " and (price <= '{$variable['price1']}' OR ( specialprice <= '{$variable['price1']}'  AND specialprice > '0.0') ) ";
                        $param .= "&amp;price1={$variable['price1']}";
                        $consearch=true;
                    }

            if ($variable['year'] != "" and $variable['year1'] != ""){
                $sql_cond .= " and  ( (year >= '{$variable['year']}' AND year <= '{$variable['year1']}' ) )";
                $param .= "&amp;year={$variable['year']}";
                $param .= "&amp;year1={$variable['year1']}";
                $consearch=true;

            }else
                if ($variable['year'] != ""){
                    $sql_cond .= " and (year = '{$variable['year']}' ) ";
                    $param .= "&amp;year={$variable['year']}";
                    $consearch=true;
                }
                else
                    if ($variable['year1'] != ""){
                        $sql_cond .= " and (year <= '{$variable['year1']}' ) ";
                        $param .= "&amp;year1={$variable['year1']}";
                        $consearch=true;
                    }

            foreach ($config['frontend_section']['searcharray_simple'] as $key=>$val) {
                if ($variable[$val]!="" and $variable[$val]!="..."){
                    $sql_cond.=" and `{$val}` like '%".$variable[$val]."%' ";
                    $param.="&amp;{$val}=".$variable[$val]."";
                    $consearch=true;
                }
            }
            foreach ($config['frontend_section']['searcharray_dropdown'] as $key=>$val) {
                if ($variable[$val]!="" and $variable[$val]!="..." and $variable[$val]!="0"){
                    $sql_cond.=" and `{$val}` = '".$variable[$val]."' ";
                    $param.="&amp;{$val}=".$variable[$val]."";
                    $consearch=true;
                }
            }

            foreach ($config['frontend_section']['searcharray_fromtofirst'] as $key=>$val) {
                if ($variable[$val]!=""){
                    $sql_cond.=" and `{$val}` >= '".$variable[$val]."' ";
                    $param.="&amp;{$val}=".$variable[$val]."";
                    $consearch=true;
                }
            }
            foreach ($config['frontend_section']['searcharray_fromtolast'] as $key=>$val) {
                if ($variable[$val]!=""){
                    $val1=substr($val,0,-1);
                    $sql_cond.=" and `{$val1}` <= '".$variable[$val]."' ";
                    $param.="&amp;{$val}=".$variable[$val]."";
                    $consearch=true;
                }
            }
            if ($variable['gallery']!=""){
                $listin_array_id_gallery = $Global_Class -> getarrayid('gallery','carsid',$sqlini=' group by carsid');
                if (!is_array($listin_array_id_gallery)) $listin_array_id_gallery=array();
                $sql_cond .= " AND ( FIND_IN_SET( id, '".implode(",",$listin_array_id_gallery)."' ) > 0 ) ";
                $param.="&amp;gallery=checked";
                $consearch=true;
            }

            if ($variable['agent'] != ""){
                $sql_cond = " and admin = '{$variable['agent']}' ";
                $param = "&amp;agent={$variable['agent']}";
                $consearch=true;
            }

        }
        if ($myproper){
            $listin_array_id_gallery = $_COOKIE['mycars'];
            $sql_cond .= " AND ( FIND_IN_SET( id, '".$listin_array_id_gallery."' ) > 0 ) ";
        }
        if (!$_REQUEST['first']){
            $variable['features']=unserialize(base64_decode($variable['features']));

        }

        if (!is_array($variable['features'])) $variable['features']=array();

        if (count($variable['features'])>0 ){
            foreach ($variable['features'] as $key=>$val){
                $sqlini=' '."and featuresid ='$key' ".' group by carsid';
                $listin_array_id_gallery = $Global_Class -> getarrayid('carsfeatures','carsid',$sqlini);

                if (!is_array($listin_array_id_gallery)) $listin_array_id_gallery=array();
                if (!is_array($listing_features_result)) $listing_features_result=$listin_array_id_gallery;
                $listing_features_result = array_intersect($listin_array_id_gallery, $listing_features_result);
            }

            $sql_cond .= " AND ( FIND_IN_SET( id, '".implode(",",$listing_features_result)."' ) > 0 ) ";

            $variable['features']=base64_encode(serialize($variable['features']));
            $param.="&amp;features=".$variable['features'];
            $consearch=true;
        }

        if ($variable['searchby']>0)  {
            if ($variable['searchby']==1)  {
                $listin_array_id_gallery = $Global_Class->getarrayid('admin','id',' AND `showdropdown`=\'0\' ');
            }else{
                $listin_array_id_gallery = $Global_Class->getarrayid('admin','id',' AND `showdropdown`=\'1\' ');
            }



            $sql_cond .= " AND ( FIND_IN_SET( admin, '".implode(",",$listin_array_id_gallery)."' ) > 0 ) ";

            $param.="&amp;searchby=".$variable['searchby'];
            $consearch=true;

        }

        $param .= "&amp;submit1=yes&amp;p=" . $_REQUEST['p'];
        $param .= "&amp;orderby=" . $orderby;
        $param .= "&amp;method=" . $method;

        if ($consearch){
            $urllink = "index.php?$param"."&amp;page=";
            $afterurl="";
        }else{
            $urllink = "page-";
            $afterurl=".html";
        }

        $sql = "SELECT COUNT(*) FROM `{$config['table_prefix']}$tabel_cars` WHERE active >= 1 $sql_cond ORDER BY  $orderby $method;";
        $result = $db -> query($sql);
        list($num_rows_ini) = mysqli_fetch_row($result);
        @mysqli_free_result($result);
        $num_rows_ini_=$num_rows_ini;

        $sql = "SELECT * FROM `{$config['table_prefix']}$tabel_cars` WHERE active >= 1 $sql_cond ORDER BY  $orderby $method;";

        $param_ini = $param;
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

            if ($i == $variable['page']){
                $pageoutfin .= "[<b>$ii</b>]";
            }else{
                if (!$consearch){
                    $userurl['i']=$i;
                    $userurl['param']=$param;
                    $userurl['p']=$_REQUEST['p'];
                    $userurl['ii']=$ii;
                    $pageoutfin.=$tpl -> replace( $userurl, "url.html" );
                }else{
                    $pageoutfin .= " <a href=\"index.php?$param" . "&amp;page=$i\">$ii</a> \n";
                }
            }
        }
        $page_ini = $variable['page'];
        $variable['page'] = $variable['page'] * $variable['nrresult'];

        $sql = "SELECT * FROM `{$config['table_prefix']}$tabel_cars` WHERE active >= 1 $sql_cond ORDER BY  $orderby $method LIMIT {$variable['page']},{$variable['nrresult']};";
        $result = $db -> query($sql);
        $num_rows = mysqli_num_rows($result);
        $nr = $num_rows;
        $nr_total = $num_rows_ini_;
        $nr_car_found = preg_replace("/\{{(\w+)\}}/e", "\$\\1", $lang["tpl_auto_found"]);
        $contor = 0;
        $newarray=array();

        $sql_gallery = '';
        if ($num_rows > 0){
            while ($user = mysqli_fetch_assoc($result)){
                $sql_gallery .= " or (`$carsid`='{$user['id']}') ";
                $condtosearch=true;

                $user = $this->prepareuser($user);

                $user['carslisting'] = ($contor % $config['use_2_objects_in_list_per_line_color'] == 0) ? "carslisting1" : "carslisting0";
                if ($myproper){
                    $user['remove']=$tpl -> replace( $user, "remove.html" );
                    $user['checkbox']=$tpl -> replace( $user, "checkbox.html" );
                }else{
                    $user['remove']="";
                }

                $newarray[$user['id']]=$user;
                $contor++;

            } // while
            @mysqli_free_result($result);
        }


        $sql = "SELECT * FROM `{$config['table_prefix']}$tabel_gallery` WHERE 0 $sql_gallery  group by `carsid` order by `order`";
        $resultgal = $db -> query($sql);
        $num_rowsgal = mysqli_num_rows($resultgal);

        if ($num_rows > 0){
            while ($var_gallery = mysqli_fetch_assoc($resultgal)){

                if ($var_gallery['thumbnail'] == ""){
                    $var_gallery['thumbnail'] = $settings_profile['thumbnail'];
                }

                $newarray[$var_gallery['carsid']]['thumbnail'] = $config['url_path_temp'] . $var_gallery['thumbnail'];

            } // while
            @mysqli_free_result($resultgal);
        }
        $count=0;
        $out = '';
        foreach ($newarray as $user){

            if ($user['thumbnail'] == ""){
                $user['thumbnail'] = $config['url_path_temp'] .$settings_profile['thumbnail'];
            }
            if ($config['use_2_objects_in_list_per_line']){
                if ($count % 2 == 0 ){
                    $out .=  "
                                                                      <tr>
                                                                        <td width=50% align=center valign=top style='height:100%;'>";
                }else{
                    $out .=  "
                                                                        <td width=50% align=center valign=top style='height:100%;'>";
                }
            }
            if($user['active']==2){
                $user['soldclass']='<span class="sold">SOLD</span>';
            }else{
                $user['soldclass']='';
            }
            $out .= $tpl -> replace($user, "cars_shortdescription.html");
            if ($config['use_2_objects_in_list_per_line']){
                if ($count % 2 == 0 ){
                    $out .=  "
                                                                        </td>";
                }else{
                    $out .=  "
                                                                        </td>
                                                                      </tr>
                                                                        ";
                }
            }

            if ($count == 3 ){
                $out .=  "<tr>
                                       <td width=100% align=center valign=top style='height:100%;' colspan=2>";
                $out .= $tpl -> replace($user, "google1.html");
                $out .=  "</td>
                                                                      </tr>
                                                                        ";
            }

            if ($count == 7 ){
                $out .=  "<tr>
                                       <td width=100% align=center valign=top style='height:100%;' colspan=2>";
                $out .= $tpl -> replace($user, "google2.html");
                $out .=  "</td>
                                                                      </tr>
                                                                        ";
            }

            $count++;


        }

        if (!$condtosearch) $pageoutfin="";
        //$nr=$nr_total=$num_rows_ini_=$contor;
        if ($contor>0){
            $nr_listing_found = preg_replace( "/\{{(\w+)\}}/e", "\$\\1", $lang["tpl_auto_found"] );
            if ($myproper){
                $out .= $tpl -> replace( $user, "compare.html" );
            }
        }elseif ($out==''){
            if ($myproper){
                $out=$lang['tpl_auto_You_have_not_yet_added_any_properties'] ;
            }else{
                $out=$lang['tpl_auto_no_listing_found'];
            }
            $pageoutfin="";
        }
        return $out;
    }

    function cars_next_prev($id)
    {
        global $db, $Global_Class, $tpl, $language_set;
        global $config, $_REQUEST, $lang, $IMG_HEIGHT, $IMG_WIDTH, $settings_profile, $_SESSION, $HTTP_COOKIE_VARS;

        $tabel_cars = "cars";
        $tabel_gallery = "gallery";
        $tabel_carsfeatures = "carsfeatures";
        $carsid = "carsid";

        $variable = array (
            "nrresult" => $settings_profile['nrpageuser'],
            "page" => $_REQUEST['page'],
            "agent" => $_REQUEST['agent']
        );

        if ($_SESSION['orderby'] == ""){
            $orderby = "date_add";
        }else{
            $orderby = $_SESSION['orderby'];
        }
        if ($_SESSION['method'] == ""){
            $method = "desc";
        }else{
            $method = $_SESSION['method'];
        }

        $array_list = $config["config_search_field"];
        if (!is_array($array_list)) $array_list = array();
        foreach($array_list as $key => $val){
            $variable[$val] = $_SESSION[$val];
        }
        $sql_cond = '';
        $param = '';
        if ($variable['agent'] != ""){
            $sql_cond .= " and admin = '{$variable['agent']}' ";
            $param .= "&amp;agent={$variable['agent']}";
        }
        if ($variable['price'] != ""){
            $sql_cond .= " and (price >= '{$variable['price']}' OR (specialprice >= '{$variable['price1']}' AND specialprice > '0.0') ) ";
            $param .= "&amp;price={$variable['price']}";
        }
        if ($variable['price1'] != ""){
            $sql_cond .= " and (price <= '{$variable['price1']}' OR ( specialprice <= '{$variable['price1']}'  AND specialprice > '0.0') ) ";
            $param .= "&amp;price1={$variable['price1']}";
        }
        /*
        if ($variable[city] != "" AND $variable[city] != "..." AND $variable[city] != " "){
            $citytypearray = $Global_Class -> getarrayid("admin","id"," and city='{$variable[city]}' ");
            if (!is_array($citytypearray)) $citytypearray=array();
            $sql_cond .= " AND ( FIND_IN_SET( admin, '".implode(",",$citytypearray)."' ) > 0 ) ";
            $param .= "&amp;city={$variable[city]}";
        }
       */
        foreach ($config['frontend_section']['searcharray_simple'] as $key=>$val) {
            if ($variable[$val]!="" and $variable[$val]!="..."){
                $sql_cond.=" and `{$val}` like '%".$variable[$val]."%' ";
                $param.="&amp;{$val}=".$variable[$val]."";
            }
        }
        foreach ($config['frontend_section']['searcharray_dropdown'] as $key=>$val) {
            if ($variable[$val]!="" and $variable[$val]!="..." and $variable[$val]!="0"){
                $sql_cond.=" and `{$val}` = '".$variable[$val]."' ";
                $param.="&amp;{$val}=".$variable[$val]."";
            }
        }

        foreach ($config['frontend_section']['searcharray_fromtofirst'] as $key=>$val) {
            if ($variable[$val]!=""){
                $sql_cond.=" and `{$val}` >= '".$variable[$val]."' ";
                $param.="&amp;{$val}=".$variable[$val]."";
            }
        }
        foreach ($config['frontend_section']['searcharray_fromtolast'] as $key=>$val) {
            if ($variable[$val]!=""){
                $val1=substr($val,0,-1);
                $sql_cond.=" and `{$val1}` <= '".$variable[$val]."' ";
                $param.="&amp;{$val}=".$variable[$val]."";
            }
        }
        if ($variable['gallery']!=""){
            $listin_array_id_gallery = $Global_Class -> getarrayid('gallery','carsid',$sqlini=' group by carsid');
            if (!is_array($listin_array_id_gallery)) $listin_array_id_gallery=array();
            $sql_cond .= " AND ( FIND_IN_SET( id, '".implode(",",$listin_array_id_gallery)."' ) > 0 ) ";
            $param.="&amp;gallery=checked";
        }

        if (!$_REQUEST['first']){
            $variable['features']=unserialize(base64_decode($variable['features']));

        }

        if (!is_array($variable['features'])) $variable['features']=array();

        if (count($variable['features'])>0 ){
            $sqlcond="";
            foreach ($variable['features'] as $key=>$val){
                $sqlini=' '."and featuresid ='$key' ".' group by carsid';
                $listin_array_id_gallery = $Global_Class -> getarrayid('carsfeatures','carsid',$sqlini);

                if (!is_array($listin_array_id_gallery)) $listin_array_id_gallery=array();
                if (!is_array($listing_features_result)) $listing_features_result=$listin_array_id_gallery;
                $listing_features_result = array_intersect($listin_array_id_gallery, $listing_features_result);
            }

            $sql_cond .= " AND ( FIND_IN_SET( id, '".implode(",",$listing_features_result)."' ) > 0 ) ";

            $variable['features']=base64_encode(serialize($variable['features']));
            $param.="&amp;features=".$variable['features'];
            $consearch=true;
        }

        $param .= "&amp;p=" . $_REQUEST['p'];
        $param .= "&amp;orderby=" . $orderby;
        $param .= "&amp;method=" . $method;

        $param_ini = $param;


        $sql = "SELECT `id` FROM `{$config['table_prefix']}$tabel_cars` WHERE active >= 1 $sql_cond ORDER BY  $orderby $method ";
        $result = $db -> query($sql);
        $num_rows = mysqli_num_rows($result);
        $nr = $num_rows;
//        $nr_total = $num_rows_ini_;

        $contor = 0;
        $id_prev='';
        $id_next='';
        $found=0;

        if ($num_rows > 0){
            while ($user = mysqli_fetch_assoc($result)){
                if ($found==1){
                    $found++;

                }
                if ($id==$user['id']){
                    $found=1;

                }
                if ($found==0){
                    $id_prev=$user['id'];

                }
                if ($found==2){
                    $id_next=$user['id'];
                    break;
                }
            } // while
        }
        @mysqli_free_result($result);
        return array($id_prev,$id_next);
    }

    function cars_details(& $user, & $num_rows_gallery,$typeofdisplay=0)
    {
        global $db, $Global_Class, $tpl, $lang, $language_set;
        global $config, $_REQUEST, $settings_profile, $IMG_HEIGHT_LOGO, $IMG_WIDTH_LOGO, $HTTP_COOKIE_VARS;


        $tabel_cars = "cars";
        $tabel_gallery = "gallery";
        $tabel_carsfeatures = "carsfeatures";
        $carsid = "carsid";

        $user = $this->prepareuser($user);

        $user['name'] = $user['category'] . " " . $lang[tpl_auto_separator_sign] . " " . $user['make'] . " " . $lang[tpl_auto_separator_sign] . " " . $user['model'];
        $sql = "SELECT * FROM `{$config['table_prefix']}$tabel_gallery` where {$carsid}='{$user['id']}' order by `order`";
        $result = $db -> query($sql);
        $num_rows_gallery = mysqli_num_rows($result);


        $count = 0;
        $picture_profile = array();
        if ($num_rows_gallery > 0){
            while ($var_gallery = mysqli_fetch_assoc($result)){
                $img_sz = @getimagesize($config["temp"] . $var_gallery['thumbnail']);
                $img_sz1 = @getimagesize($config["temp"] . $var_gallery[picture]);
                $config['picturewidth']=max($img_sz1[0],$config['picturewidth']);
                $config['pictureheight']=max($img_sz1[1],$config['pictureheight']);
                //print_r($var_gallery);
                if ($img_sz > 0 and $img_sz1>0){

                    $var_gallery["description{$language_set}"]=str_replace('"',"'",$var_gallery["description{$language_set}"]);

                    $user[picture_big] .= "\t\t\tpicture_array[$count]=\"" . $config['url_path_temp'] . $var_gallery[picture] . "\";\n";
                    $user[picture_big] .= "\t\t\tpicturestring_array[$count]=\"" . $var_gallery["description{$language_set}"] . "\";\n";
                    if($user['active']==2){
                        $user['soldclass']='<span class="sold">SOLD</span>';
                    }else{
                        $user['soldclass']='';
                    }
                    $picture_profile['picture' . $count] .= $user['soldclass']."<img class=\"hand\" src=\"" . $config['url_path_temp'] . $var_gallery['thumbnail'] . "\" width=\"{$img_sz[0]}\" height=\"{$img_sz[1]}\" border=\"0\" onClick=\"show_picture($count,1);\" alt=\"{$user['year']} {$user['make']} {$user['model']} {$var_gallery["description{$language_set}"]} thumb\" title=\"{$user['year']} {$user['make']} {$user['model']} {$var_gallery["description{$language_set}"]} thumb\">";
                    $picture_profile['bigpicture' . $count] .= "<img class=\"hand\" id=\"bigpicture{$count}\" src=\"" . $config['url_path_temp'] . $var_gallery[picture] . "\" border=\"0\" onClick=\"show_picture($count,1);\">";
                    if ($num_rows_gallery>1) {
                        $picture_profile['descriptionsmall' . $count] = $var_gallery["description{$language_set}"];
                        $picture_profile['picturesmall' . $count] .= "<img class=\"hand\" src=\"" . $config['url_path_temp'] . $var_gallery['thumbnail'] . "\" width=\"{$img_sz[0]}\" height=\"{$img_sz[1]}\" border=\"0\" onClick=\"show_picturebig($count,1);\">";
                    }
                    $picture_profile['description' . $count] = $var_gallery["description{$language_set}"];


                    if ($typeofdisplay>=1){
                        if ($count % 2 == 0 or $typeofdisplay==2){
                            $user[picture_big_show] .= "
                                                                      <tr>
                                                                        <td width=50% align=center valign=top>";
                        }else{
                            $user[picture_big_show] .= "
                                                                        <td width=50% align=center valign=top>";
                        }
                        $user[picture_big_show] .= "<img src=\"" . $config['url_path_temp'] . $var_gallery[picture] . "\" border=\"0\"><br>{$var_gallery[description]}";
                        if ($count % 2 == 0 and $typeofdisplay!=2){
                            $user[picture_big_show] .= "
                                                                        </td>";
                        }else{
                            $user[picture_big_show] .= "
                                                                        </td>
                                                                      </tr>
                                                                        ";
                        }
                    }
                    $count++;
                }
            } // while
            @mysqli_free_result($result);
            $user[number] = $count;
            if ($user[displaymodel]==0){
                $user[displaymodel]="";
            }
            $picture_profile[width] = $settings_profile['thumbnail_width'];
            $picture_profile[height] = $settings_profile['thumbnail_height'];
            $user[picture] = $tpl -> replace($picture_profile,  "cars_gallery{$user[displaymodel]}.html" );

        }else{
            if ($config['show_nopicture_indetailspage']){
                $var_gallery['thumbnail']=$settings_profile['thumbnail'];
                $var_gallery[picture] =$settings_profile['thumbnail'];
                $img_sz = @getimagesize($config['temp'] . $var_gallery['thumbnail']);
                if ($img_sz > 0){
                    $user[picture_big] .= "\t\t\tpicture_array[$count]=\"" . $config['url_path_temp'] . $var_gallery[picture] . "\";\n";
                    $user[picture_big] .= "\t\t\tpicturestring_array[$count]=\"" . $var_gallery["description{$language_set}"] . "\";\n";
                    $picture_profile['picture' . $count] .= "<img class=\"hand\" src=\"" . $config['url_path_temp'] . $var_gallery['thumbnail'] . "\" width=\"{$img_sz[0]}\" height=\"{$img_sz[1]}\" border=\"0\">";
                    $picture_profile['bigpicture' . $count] .= "<img class=\"hand\" id=\"bigpicture{$count}\" src=\"" . $config['url_path_temp'] . $var_gallery[picture] . "\" border=\"0\">";
                    if ($num_rows_gallery>1) {
                        $picture_profile['descriptionsmall' . $count] = $var_gallery["description{$language_set}"];
                        $picture_profile['picturesmall' . $count] .= "<img class=\"hand\" src=\"" . $config['url_path_temp'] . $var_gallery['thumbnail'] . "\" width=\"{$img_sz[0]}\" height=\"{$img_sz[1]}\" border=\"0\">";
                    }
                    $picture_profile['description' . $count] = $var_gallery["description{$language_set}"];
                    $count++;
                }

                $user[number]=1;
                $picture_profile[width]=$settings_profile['thumbnail_width'];
                $picture_profile[height]=$settings_profile['thumbnail_height'];
                if ($user[displaymodel]==0){
                    $user[displaymodel]="";
                }
                $user[picture] = $tpl -> replace( $picture_profile, "cars_gallery{$user[displaymodel]}.html" );
            }
            $lang['tpl_auto_Click_on_a_picture_to_view_the_gallery'] ="";
        }


        $sql = "SELECT {$config['table_prefix']}features.* FROM `{$config['table_prefix']}$tabel_carsfeatures`,`{$config['table_prefix']}features` where {$config['table_prefix']}$tabel_carsfeatures.{$carsid}='{$user['id']}' and {$config['table_prefix']}features.id={$config['table_prefix']}$tabel_carsfeatures.featuresid order by {$config['table_prefix']}features.name{$language_set}";
        $result = $db -> query($sql);
        $num_rows = mysqli_num_rows($result);
        $contor = 0;
        if ($num_rows > 0){
            while ($var_features = mysqli_fetch_assoc($result)){
                if ($contor % 2 == 1){
                    $var = 1;
                }else{
                    $var = "";
                }
                $user['features' . $var] .= "<img src=\"images/close.gif\" border=\"0\">&nbsp;" . $var_features["name{$language_set}"] . "<br>";
                $contor++;

            } // while
        }
        @mysqli_free_result($result);

        $sql = "UPDATE `{$config['table_prefix']}$tabel_cars` SET noview=noview+1 where id='{$user['id']}' limit 1;";
        $result = $db -> query($sql);

        /**
         * $user[fulloptions]=str_replace("-","&raquo;",$user[fulloptions]);
         * $user[shortoptions]=str_replace("-","&raquo;",$user[shortoptions]);
         * $user_profile=$user;
         */
        /**
         * if ( $HTTP_COOKIE_VARS['username_dealer_cookie'] != "" AND $user[dealerprice] > 0.0 ){
         * $user['price'] = "<S>{$settings_profile['currency']} {$user['price']}</S> | <font class=specialprice>{$settings_profile['currency']} {$user[dealerprice]}</font>";
         * }else{
         */

        // }
        $admin_profile = $Global_Class -> getprofile($user['admin'], "admin", 'id');
        if ($admin_profile['logo'] != ""){
            $admin_profile['logo'] = "<img src=\"" . $config['url_path_temp'] . $admin_profile['logo'] . "\" border=0>";
        }
        if ($admin_profile['nocontactemail'] == 1){
            $admin_profile['email'] = 'N/A';
        }else
            if ($admin_profile['noemail'] == 1){
                $admin_profile['email'] = $tpl -> replace($user, "hide_email.html");
            }
        $admin_profile[carid]=$user['id'];
        if($user['active']==2){
            $user[contact] = $tpl -> replace($admin_profile, "soldinfo.html");
        }else{
            $user[contact] = $tpl -> replace($admin_profile, "cars_contact.html");
        }

        $user['description'] = nl2br($user['description']);
        if ($user['active'] >= 2){
            $user['sold'] = $lang['array_types'][$user['active']];
        }


        if ($typeofdisplay==0){
            list($user['id_prev'],$user['id_next']) = $this->cars_next_prev($user['id']);
            if ($user['id_next']==''){
                $lang['tpl_auto_nextcar']="";
            }else{

                $usernext = $Global_Class -> getprofile( $user['id_next'], "cars", 'id' );
                $usernext = $this->prepareuser($usernext);
                $user[nexturl] = $tpl -> replace( $usernext, "cars_link.html" );
            }


            if ($user['id_prev']==''){
                $lang['tpl_auto_previouscar']="";
            }else{

                $userprev = $Global_Class -> getprofile( $user['id_prev'], "cars", 'id' );
                $userprev = $this->prepareuser($userprev);

                $user[prevurl] = $tpl -> replace( $userprev, "cars_link.html" );
            }
        }

        $out = '';
        if ($typeofdisplay==1){
            $out .= $tpl -> replace( $user, "vehicle_information_sheet_body.html" );
        }elseif ($typeofdisplay==2){
            $out .= $tpl -> replace( $user, "cars_compare_body.html" );
        }else{
            $out .= $tpl -> replace( $user, "cars_details.html" );
        }

        return $out;
    }

    function cars_sponsored()
    {
        global $db, $Global_Class, $tpl, $language_set;
        global $config, $_REQUEST, $lang, $IMG_HEIGHT, $IMG_WIDTH, $settings_profile, $_SESSION, $HTTP_COOKIE_VARS;


        $tabel_sponsored = "sponsored";
        $tabel_cars = "cars";
        $tabel_gallery = "gallery";
        $tabel_carsfeatures = "carsfeatures";
        $carsid = "carsid";

        if (!$config["config_sponsored_play"]){


            $sql = "SELECT {$config['table_prefix']}cars.* FROM `{$config['table_prefix']}{$tabel_sponsored}`,{$config['table_prefix']}cars WHERE {$config['table_prefix']}$tabel_sponsored.date_start<=NOW() and NOW()<={$config['table_prefix']}$tabel_sponsored.date_ends and  {$config['table_prefix']}cars.id={$config['table_prefix']}{$tabel_sponsored}.carid GROUP BY {$config['table_prefix']}{$tabel_sponsored}carid ORDER BY RAND();";
            $result = $db -> query($sql);
            $num_rows = mysqli_num_rows($result);
            $contor = 0;
            $newarray=array();
            $sql_gallery="";
            if ($num_rows > 0){
                while ($user = mysqli_fetch_assoc($result)){



                    //$user = $Global_Class -> getprofile($users['carid'], "$tabel_cars", "id");

                    $sql_gallery .= " or (`$carsid`='{$user['id']}') ";

                    //$var_gallery = $Global_Class -> getprofile_order($user['id'], "$tabel_gallery", "order", $carsid);

                    $user = $this->prepareuser($user);

                    $newarray[$user['id']]=$user;

                    $contor++;
                } // while
                @mysqli_free_result($result);

                $sql = "SELECT * FROM `{$config['table_prefix']}$tabel_gallery` WHERE 0 $sql_gallery  group by `carsid` order by `order`";
                $resultgal = $db -> query($sql);
                $num_rowsgal = mysqli_num_rows($resultgal);

                if ($num_rows > 0){
                    while ($var_gallery = mysqli_fetch_assoc($resultgal)){

                        if ($var_gallery['thumbnail'] == ""){
                            $var_gallery['thumbnail'] = $settings_profile['thumbnail'];
                        }

                        $newarray[$var_gallery['carsid']]['thumbnail'] = $config['url_path_temp'] . $var_gallery['thumbnail'];

                    } // while
                    @mysqli_free_result($resultgal);
                }
                $out = '';
                foreach ($newarray as $user){

                    if ($user['thumbnail'] == ""){
                        $user['thumbnail'] = $config['url_path_temp'] .$settings_profile['thumbnail'];
                    }
                    $out .= $tpl -> replace($user, "cars_sponsored.html");

                }

            }else{
                $lang["tpl_auto_sponsored"] = '';
            }
            return $out;
        }else{
            $maxheight = 0;
            global $db, $Global_Class, $tpl;
            global $config, $_REQUEST, $lang, $IMG_HEIGHT, $IMG_WIDTH, $settings_profile, $_SESSION, $HTTP_COOKIE_VARS;

            $sql = "SELECT {$config['table_prefix']}cars.* FROM `{$config['table_prefix']}{$tabel_sponsored}`,{$config['table_prefix']}cars WHERE {$config['table_prefix']}{$tabel_sponsored}.date_start<=NOW() and NOW()<={$config['table_prefix']}{$tabel_sponsored}.date_ends and  {$config['table_prefix']}cars.id={$config['table_prefix']}{$tabel_sponsored}.carid GROUP BY {$config['table_prefix']}{$tabel_sponsored}.carid ORDER BY RAND();";
            $result = $db -> query($sql);
            $num_rows = mysqli_num_rows($result);
            $contor = 0;
            $newarray=array();
            $sql_gallery = '';
            if ($num_rows > 0){
                while ($user = mysqli_fetch_assoc($result)){

                    //$user = $Global_Class -> getprofile($users['carid'], "$tabel_cars", "id");

                    $sql_gallery .= " or (`$carsid`='{$user['id']}') ";

                    //$var_gallery = $Global_Class -> getprofile_order($user['id'], "$tabel_gallery", "order", $carsid);

                    $user = $this->prepareuser($user);

                    $newarray[$user['id']]=$user;

                } // while
                @mysqli_free_result($result);

                $sql = "SELECT * FROM `{$config['table_prefix']}$tabel_gallery` WHERE 0 $sql_gallery   group by `carsid` order by `order`";
                $resultgal = $db -> query($sql);
                $num_rowsgal = mysqli_num_rows($resultgal);

                if ($num_rows > 0){
                    while ($var_gallery = mysqli_fetch_assoc($resultgal)){

                        if ($var_gallery['thumbnail'] == ""){
                            $var_gallery['thumbnail'] = $settings_profile['thumbnail'];
                        }
                        $user['thumbnail'] = $config['url_path_temp'] . $var_gallery['thumbnail'];
                        $img_sz = @getimagesize($config['temp'] . $var_gallery['thumbnail']);
                        if ($img_sz[1] > $maxheight){
                            $maxheight = $img_sz[1];
                        }
                        $newarray[$var_gallery['carsid']]['thumbnail'] = $config['url_path_temp'] . $var_gallery['thumbnail'];

                    } // while
                    @mysqli_free_result($resultgal);
                }
                //print_r($newarray);
                $outspo = array();
                $out = '';
                foreach ($newarray as $user){

                    if ($user['thumbnail'] == ""){
                        $user['thumbnail'] = $config['url_path_temp'] .$settings_profile['thumbnail'];
                    }

                    $user['listingclass'] = ($contor % 2 == 0) ? "carslisting1" : "carslisting0";


                    $outspo['Images'] .= $user['thumbnail'] . ";";
                    $outspo['Title'] .= "{$user['year']} {$user['make']} {$user['model']}" . ";";
                    $outspo['Url'] .= "index.php?p=details&id={$user['id']};";
                    $outspo['Text'] .= preg_replace( "\r\n|\n"," ",substr(strip_tags($user['shortdescription']),0,$config['sponsored_shortdescription_cut'])).";";

                    $contor++;

                }
                foreach ($outspo as $key => $val){
                    $outspo[$key] = substr($val, 0, -1);
                }
                if ($contor == 1){
                    foreach ($outspo as $key => $val){
                        $outspo[$key] = $outspo[$key] . ";" . $outspo[$key];
                    }
                }
                $user = array_merge ($user, $outspo);
                $user['maxheight'] = $maxheight + 30;
                $out .= $tpl -> replace($user, "cars_sponsored_play.html");
            }else{
                $lang["tpl_auto_sponsored"] = '';
            }

            return $out;
        }
    }
    function cars_details_contact(& $user, & $num_rows_gallery)
    {
        global $db, $Global_Class, $tpl, $lang, $language_set;
        global $config, $_REQUEST, $settings_profile, $IMG_HEIGHT_LOGO, $IMG_WIDTH_LOGO, $HTTP_COOKIE_VARS;

        if ($HTTP_COOKIE_VARS['username_dealer_cookie'] != ""){
            $tabel_cars = "carsdealer";
            $tabel_gallery = "carsdealergallery";
            $tabel_carsfeatures = "carsdealerfeatures";
            $carsid = "carsid";
        }else{
            $tabel_cars = "cars";
            $tabel_gallery = "gallery";
            $tabel_carsfeatures = "carsfeatures";
            $carsid = "carsid";
        }
        if ($_SESSION['rent']){
            $tabel_cars = "rentcars";
            $tabel_gallery = "rentcarsgallery";
            $tabel_carsfeatures = "rentcarsfeatures";
            $carsid = "rentcarsid";
        }

        $category_profile = $Global_Class -> getprofile($user['category'], "category", 'id');
        $user['category'] = $category_profile['name'];




        $admin_profile = $Global_Class -> getprofile($user['admin'], "admin", 'id');
        if ($admin_profile['logo'] != ""){
            $admin_profile['logo'] = "<img src=\"" . $config['url_path_temp'] . $admin_profile['logo'] . "\" border=0>";
        }
        if ($admin_profile['nocontactemail'] == 1){
            $admin_profile['email'] = 'N/A';
        }else
            if ($admin_profile['noemail'] == 1){
                $admin_profile['email'] = $tpl -> replace($user, "hide_email.html");
            }
        $out = $tpl -> replace($admin_profile, "cars_contact_allopass.html");
        return $out;
    }
    function homepage8($cond=0)
    {
        global $db, $Global_Class, $tpl,$language_set;
        global $config, $_REQUEST, $lang, $IMG_HEIGHT,$IMG_WIDTH,$settings_profile,$_SESSION, $HTTP_COOKIE_VARS;

        if ($config['no_show_one_homepage_lastlcars']<0) $config['no_show_one_homepage_lastcars']=8;
        if ($config['no_show_one_homepage_sponsored']<0) $config['no_show_one_homepage_sponsored']=4;
        if ($cond){
            $sql = "SELECT * FROM `{$config['table_prefix']}sponsored` WHERE date_start<=NOW() and NOW()<=date_ends GROUP BY carid ORDER BY RAND() limit ".$config['no_show_one_homepage_sponsored'];
            $width=intval(100/$config['no_show_one_homepage_sponsored']);
        }else{
            $sql = "SELECT * FROM `{$config['table_prefix']}cars` WHERE active >= 1  ORDER BY id desc LIMIT ".$config['no_show_one_homepage_lastcars'];
            $width=intval(200/$config['no_show_one_homepage_lastcars']);
        }
        $result = $db -> query($sql);
        $num_rows = mysqli_num_rows( $result );
        $contor=0;
        $newarray=array();
        $sql_gallery = '';
        if ( $num_rows > 0 ) {
            while ( $user = mysqli_fetch_assoc( $result ) ) {
                if ($cond){
                    $user = $Global_Class -> getprofile( $user[carid],"cars","id" );
                }
                if ($user['active']==0) break;

                $user = $this->prepareuser($user);

                $sql_gallery .= " or (`carsid`='{$user['id']}') ";

                //$var_gallery = $Global_Class -> getprofile_order( $user['id'], "gallery", "order", 'carsid' );

                $user['width']=$settings_profile['thumbnail_width'];

                if ($user['active']>=2){
                    $user['sold']="<font class=soldsmall>".$lang['array_types'][$user['active']]."</font>";
                }

                $newarray[$user['id']]=$user;

            } // while
            @mysqli_free_result($result);

            $sql = "SELECT * FROM `{$config['table_prefix']}gallery` WHERE 0 $sql_gallery group by `carsid` order by `order`";
            $resultgal = $db -> query($sql);
            $num_rowsgal = mysqli_num_rows($resultgal);

            if ($num_rows > 0){
                while ($var_gallery = mysqli_fetch_assoc($resultgal)){

                    if ($var_gallery['thumbnail'] == ""){
                        $var_gallery['thumbnail'] = $settings_profile['thumbnail'];
                    }

                    $newarray[$var_gallery['carsid']]['thumbnail'] = $config['url_path_temp'] . $var_gallery['thumbnail'];

                } // while
                @mysqli_free_result($resultgal);
            }
            $news_profile  = array();
            foreach ($newarray as $user){

                if ($user['thumbnail'] == ""){
                    $user['thumbnail'] = $config['url_path_temp'] .$settings_profile['thumbnail'];
                }

                if ($contor<$config['no_show_one_homepage_lastcars']/2){
                    $vartemp="homepage1";
                }else{
                    $vartemp="homepage2";
                }
                $contor++;
                $user['widthtd']=$width;
                $news_profile[$vartemp] .=  $tpl -> replace($user, "cars_shortdescriptionhomepage.html");

            }

            if ($cond) $news_profile['tpl_auto_last_cars']=$lang['tpl_auto_sponsored'];
            if ($contor>0){
                if ($news_profile['homepage2']=='') $news_profile['homepage2']="<td>&nbsp;</td>";
                $out = $tpl->replace($news_profile,"cars_homepage.html");
            }

            return $out;
        }



    }
    function allsponsored(){
        global $db, $Global_Class, $tpl, $language_set;
        global $config, $_REQUEST, $lang, $IMG_HEIGHT, $IMG_WIDTH, $settings_profile, $_SESSION, $HTTP_COOKIE_VARS;

        $tabel_sponsored = "sponsored";
        $tabel_cars = "cars";
        $tabel_gallery = "gallery";
        $tabel_carsfeatures = "carsfeatures";
        $carsid = "carsid";
        $sql = "SELECT * FROM `{$config['table_prefix']}{$tabel_sponsored}` WHERE date_start<=NOW() and NOW()<=date_ends GROUP BY carid ORDER BY RAND();";
        $result = $db -> query($sql);
        $num_rows = mysqli_num_rows($result);
        $contor = 0;
        $newarray=array();
        $sql_gallery = '';
        $var_gallery = array();
        $out = '';
        if ($num_rows > 0){
            while ($users = mysqli_fetch_assoc($result)){
                $user = $Global_Class -> getprofile($users['carid'], "$tabel_cars", "id");

                $sql_gallery .= " or (`$carsid`='{$user['id']}') ";

                //$var_gallery = $Global_Class -> getprofile_order($user['id'], "$tabel_gallery", "order", $carsid);

                $user = $this->prepareuser($user);

                if ($var_gallery['thumbnail'] == ""){
                    $var_gallery['thumbnail'] = $settings_profile['thumbnail'];
                }
                $user['thumbnail'] = $config['url_path_temp'] . $var_gallery['thumbnail'];


                $user['carslisting'] = ($contor%2==0) ? "carslisting1" : "carslisting0";

                $newarray[$user['id']]=$user;
                //$out .= $tpl -> replace($user, "cars_shortdescription.html");
                $contor++;
            } // while
            @mysqli_free_result($result);

            $sql = "SELECT * FROM `{$config['table_prefix']}$tabel_gallery` WHERE 0 $sql_gallery  group by `carsid` order by `order`";
            $resultgal = $db -> query($sql);
            $num_rowsgal = mysqli_num_rows($resultgal);

            if ($num_rows > 0){
                while ($var_gallery = mysqli_fetch_assoc($resultgal)){

                    if ($var_gallery['thumbnail'] == ""){
                        $var_gallery['thumbnail'] = $settings_profile['thumbnail'];
                    }

                    $newarray[$var_gallery['carsid']]['thumbnail'] = $config['url_path_temp'] . $var_gallery['thumbnail'];

                } // while
                @mysqli_free_result($resultgal);
            }

            foreach ($newarray as $user){

                if ($user['thumbnail'] == ""){
                    $user['thumbnail'] = $config['url_path_temp'] .$settings_profile['thumbnail'];
                }
                $out .= $tpl -> replace($user, "cars_shortdescription.html");
            }
        }else{
            $lang["tpl_auto_sponsored"] = '';
        }
        return $out;

    }
    function adprofiles_content(& $nrmax)
    {
        global $db, $Global_Class, $tpl, $lang;
        global $config, $_REQUEST, $language_set, $settings_profile;
        $profile = array();
        $out = '';
        if ($settings_profile['adprofiles'] == 1){
            $sql = "SELECT * from {$config['table_prefix']}adprofiles where `active`=1 order by `order`";
            $result = $db -> query($sql);
            $num_rows = mysqli_num_rows($result);
            $nrmax = $num_rows;
            if ($num_rows > 0){
                while ($user = mysqli_fetch_assoc($result)){
                    $user['name'] = $user["name" . "$language_set"];
                    $user['description'] = $user["description" . "$language_set"];
                    $user['currency'] = $settings_profile['currency'];
                    $user['price'] = nr_afis($user['price']);
                    if ($num_rows==1){
                        $user['checked']=' checked';
                    }
                    $profile['adprofiles'] .= $tpl -> replace($user, "adprofiles_template.html");
                }
                @mysqli_free_result($result);

            }
        }else{
            $nrmax = 0;
        }
        $out .= $tpl -> replace($profile, "adprofiles.html");
        if ($nrmax < 1)
            $out = "<input type=\"hidden\" name=\"adprofiles\" value=\"\">".$out;
        return $out;
    }

    function resetarray($cond=1){
        global $db, $Global_Class, $tpl, $language_set;
        global $config, $_REQUEST, $lang, $IMG_HEIGHT, $IMG_WIDTH, $settings_profile, $_SESSION, $HTTP_COOKIE_VARS;

        if (!is_array($config["config_search_field"])) $config["config_search_field"]=array();
        $array_list=$config["config_search_field"];
        if ($cond){
            if ($_REQUEST['submit1']!="" or $_REQUEST['submit1']!="" or $_REQUEST['reset']==1) {
                foreach($array_list as $key=>$val){
                    $news_profile[$val]=$_REQUEST[$val];
                    $_SESSION[$val]=$_REQUEST[$val];
                }
            }
        }else{
            foreach($array_list as $key=>$val){
                $news_profile[$val]=$_SESSION[$val];
            }
        }
    }
    function frontend($page,$output_car,$nr_car_found="",$pageoutfin=""){
        global $db, $Global_Class, $tpl, $language_set;
        global $config, $_REQUEST, $lang, $IMG_HEIGHT, $IMG_WIDTH, $settings_profile, $_SESSION, $HTTP_COOKIE_VARS,$IMG_WIDTH_LOGO;
        if (!is_array($config["config_search_field"])) $config["config_search_field"]=array();
        $array_list=$config["config_search_field"];
        foreach($array_list as $key=>$val){
            $news_profile[$val]=$_SESSION[$val];
        }

        $news_profile['page'] = $page;

        $count1=20;
        foreach ($config['admin_section']['cars']['dropdown_fields'] as $key1=>$val1){


            if ($val1=='state'){
                if ($_SESSION['country']<>''){
                    $sqlstate=" and countryid='{$_SESSION['country']}' ";
                }else{
                    $sqlstate="";
                }

                $news_profile["state"] = $Global_Class -> getdropdown( $_SESSION['state'], "state", "name{$language_set}", "id", "name{$language_set}",0, $sqlstate,$_SESSION['cardatase'] );
            }elseif ($val1=='city'){
                if ($_SESSION['state']<>''){
                    $sqlstate=" and stateid='{$_SESSION['state']}' ";
                }else{
                    $sqlstate="";
                }

                $news_profile["city"] = $Global_Class -> getdropdown( $_SESSION['city'], "city", "name{$language_set}", "id", "name{$language_set}",0, $sqlstate,$_SESSION['cardatase'] );
            }elseif ($val1=='model'){
                if ($_SESSION['make']!=''){
                    $news_profile["model"] = $Global_Class -> getdropdown( $_SESSION['model'], "model", "name{$language_set}", "id", "name{$language_set}",0, " and makeid='{$_SESSION['make']}' ",$_SESSION['cardatase'] );
                }
            } elseif ($val1!='year'){
                $news_profile[$val1] = $Global_Class -> getdropdown( $_SESSION[$val1], "$val1", "name{$language_set}", "id", "name{$language_set}",0,"",$_SESSION['cardatase'] );
            }
        }
        foreach ($config['admin_section']['cars']['dropdown_fields_fromlanguage'] as $key1=>$val1){

            $news_profile[$val1] = $Global_Class -> getdropdown_array( $_SESSION[$val1], $lang[$val1] );

        }


        $news_profile['searchby'] = $Global_Class -> getdropdown_arrayid( $_SESSION['searchby'], array(1=>$lang['tpl_auto_byowner'],2=>$lang['tpl_auto_byagent']) );

        if ($_SESSION['method']=='') $_SESSION['method']='desc';
        $news_profile["orderby"] = $Global_Class -> getdropdown_array_car( $_SESSION['orderby'], $config["config_orderby"] );
        $news_profile["method"] = $Global_Class -> getdropdown_array1( $_SESSION['method'], $config["config_method"] );

        //$news_profile["city"] = $Global_Class -> getdropdown( $_SESSION['city'], "city", "name{$language_set}", "id", "name{$language_set}",0,"",$_SESSION['cardatase'] );

        $news_profile["nr_car_found"] = $nr_car_found;

        $news_profile['pageoutfin']=$pageoutfin;
        $news_profile['signupmembers'] = $news_profile['signupmembers'];
        //$news_profile['sponsored'] = $this -> cars_sponsored();
        if (trim($news_profile['sponsored'])!='' and !$config['show_sponsored_onallpages']){
            $news_profile["sponsored_show"] = $tpl->replace($news_profile,"sponsored_show.html");
        }

        if (!is_array($config["javascriptprofiles"]['makemodeljavascript'])) $config["javascriptprofiles"]['makemodeljavascript']=array();

        if (file_exists($config['path'].'temp/makemodel'.$language_set.'.txt') and filesize($config['path'].'temp/makemodel'.$language_set.'.txt')>0){
            $language_set1=($language_set=='' or $language_set==0)?'':$language_set;
            $news_profile['modelsArray'] = @implode('',@file($config['path'].'temp/makemodel'.$language_set.'.txt'));
            //$config["javascriptprofiles"]['makemodeljavascript'][$language_set1] ;
        }else{
            $news_profile['modelsArray'] = $Global_Class -> getjavascriptarray("make","name{$language_set}","id","name{$language_set}","model","name{$language_set}","id","name{$language_set}","makeid");
        }


        //$news_profile['modelscountryArray'] = $this -> getjavascriptarray("country","name{$language_set}","id","name{$language_set}","city","name{$language_set}","id","name{$language_set}","makeid");

        $news_profile['admin'] = $Global_Class -> getdropdown( $_SESSION['admin'], "admin", "name", "id", "name",0," and `showdropdown`='1' " );
        $news_profile['year1'] = $Global_Class -> getdropdown( $_SESSION['year1'], "year", "name", "id", "name",0,"",$_SESSION['cardatase'] );
        $news_profile['year'] = $Global_Class -> getdropdown( $_SESSION['year'], "year", "name", "id", "name",0,"",$_SESSION['cardatase'] );

        if ($_SESSION['adv_search']){
            $news_profile["simple_search"] = $tpl->replace($news_profile,"simple_search.html");
        }else{
            $news_profile["simple_search"] = $tpl->replace($news_profile,"simple_search.html");
        }
        if ($_REQUEST['p']=='simplesearchid'){
            echo $news_profile["simple_search"];
            exit(0);
        }
        //echo $news_profile["simple_search"];
        //exit;
        $news_profile["newsletter_form"] = $tpl->replace($news_profile,"newsletter_form.html");
        $news_profile['output_car_details'] = $output_car;
        if (in_array ($_REQUEST['p'], array ("search","advsearch"))){
            $news_profile[p]=$_REQUEST['p'];
            $news_profile['cars_orderby'] = $tpl->replace($news_profile,"cars_orderby.html");
        }

        if ($_REQUEST['p']=='details'){
            $news_profile['hideondetails']='style="display:none;"';
        }

        if (($_REQUEST['p']=='' and $config['show_one_homepage_sponsored']) or ($_REQUEST['p']!='' and $config['show_sponsored_onallpages'])){
            if ($config['use_old_sponsored_format']){
                $news_profile['sponsored'] = $this -> cars_sponsored();
                if (trim($news_profile['sponsored'])!=''){
                    $news_profile["sponsored_show"] = $tpl->replace($news_profile,"sponsored_show.html");
                }
            }else{
                $news_profile['homepage_sponsored'] = $this->homepage8(1);
            }
        }else{
            $news_profile["sponsored_show"]='';
            $news_profile['homepage_sponsored']='';
        }


        if (( $_REQUEST['p']=='' and  $config['show_one_homepage_lastlisting']) or ( $_REQUEST['p']!='' and $config['show_lastlisting_onallpages']) ){
            $news_profile['homepage8'] = $this->homepage8();
        }

        if($_REQUEST['p']=='advsearch' and $_REQUEST['o']=='advsearch1'){

            $sql = "SELECT {$config['table_prefix']}features.* FROM `{$config['table_prefix']}features` where 1 order by {$config['table_prefix']}features.name{$language_set}";
            $result = $db -> query( $sql );
            $num_rows = mysqli_num_rows( $result );
            $contor=0;
            if ( $num_rows > 0 ) {
                while ( $var_features = mysqli_fetch_assoc( $result ) ) {
                    $var = ($contor%2==1) ? "1" : "0";
                    $news_profile['features'.$var] .= "<input type=checkbox name=\"features[".$var_features['id']."]\" value=1>". $var_features["name$language_set"]."<br>";
                    $contor++;
                } // while
                @mysqli_free_result($result);
            }

            $news_profile['output_car_details'] = $tpl->replace($news_profile,"adv_search.html");
            if ($_REQUEST['p']=='advsearchid'){
                echo $news_profile['output_car_details'];
                exit(0);
            }
            $news_profile["simple_search"]='';
            $news_profile['hideondetails']='style="display:none;"';
        }
        if ($_REQUEST['agent']!='')    {
            $admin_profile = $Global_Class -> getprofile( $_REQUEST['agent'], "admin", 'id' );

            if ($admin_profile['logo']!="") {
                $admin_profile['logo']="<img src=\"".$config['url_path_temp'] . $admin_profile['logo']."\"  border=0>";
            }
            if ($admin_profile['nocontactemail'] == 1){
                $admin_profile['email'] = 'N/A';
            }else
                if ($admin_profile['noemail']==1) {
                    $admin_profile['email'] = "";
                }
            if ($admin_profile){
                $news_profile['output_car_details'] = $tpl -> replace( $admin_profile, "cars_contact1.html" ).$news_profile['output_car_details'];
            }

        }
        $output = $tpl->replace($news_profile,"cars.html");
        return $output;
    }
    function showprice($user){
        global $config,$settings_profile,$language_set,$lang;

        $user['price']=nr_afis($user['price']);
        $user['specialprice']=nr_afis($user['specialprice']);
        if ( $user['price1'] > 0.0 ){
            $user['price']=$user['price1'];
        }

        if ($config['price_before']){
            $pricebefore="{$settings_profile['currency']} ";
            $priceafter="";
        }else{
            $pricebefore="";
            $priceafter=" {$settings_profile['currency']}";
        }
        if ($user['pricemesg'] == ''){
            if ($user['specialprice'] > 0.0){
                $user['price'] = "<S>{$pricebefore}{$user['price']}{$priceafter}</S> | <font class=\"specialprice\">{$pricebefore}{$user['specialprice']}{$priceafter}</font>";
            }else{
                $user['price'] = "{$pricebefore}{$user['price']}{$priceafter}";
            }
        }else{
            $user['price'] = $user["pricemesg{$language_set}"];
        }
        return $user;
    }
    function prepareuser($user){

        global $config,$settings_profile,$language_set,$lang,$Global_Class;
        /*
        if ($_SERVER['REMOTE_ADDR']=='86.121.245.22' and $user['id']==242){

            echo $language_set;
            print_r($user);
        }
        */
        $userini=$user;
        $user['categoryid'] = ($user['category']);
        $user['makeid'] = ($user['make']);
        $user['modelid'] = ($user['model']);
        $user['yearid'] = ($user['year']);
        $user['countryid'] = ($user['country']);
        $user['cityid'] = ($user['city']);
        $user['stateid'] = ($user['state']);
        if (!is_array($config['admin_section']['cars']['multiplefields'])) $config['admin_section']['listing']['multiplefields']=array();
        foreach ($config['admin_section']['cars']['multiplefields'] as $key=>$val){
            $user[$val]=$user[$val.$language_set];
        }

        if (!is_array($config['admin_section']['cars']['multiplefields_text'])) $config['admin_section']['listing']['multiplefields_text']=array();
        foreach ($config['admin_section']['cars']['multiplefields_text'] as $key=>$val){
            $user[$val]=$user[$val.$language_set];
        }

        if (!is_array($config['admin_section']['cars']['dropdown_fields'])) $config['admin_section']['cars']['dropdown_fields']=array();
        foreach ($config['admin_section']['cars']['dropdown_fields'] as $key=>$val){
            $temp_profile = $Global_Class -> getprofile($user[$val], "$val", 'id');
            //print_r($temp_profile);
            if ($val=='year') $temp_profile["name".$language_set]=$temp_profile["name"];

            $user[$val] = $temp_profile["name".$language_set];
            $userini[$val] = $temp_profile["name"];
        }


        $user['width'] = $settings_profile['thumbnail_width'];
        $user['height'] = $settings_profile['thumbnail_height'];



        $user = $this->showprice($user);

        if ($user['active'] == 2){
            $user['soldstart'] = "<S><font class=soldsmall>";
            $user['soldends'] = "</font></S>";
        }

        if ($user['active'] >= 2){
            $user['sold'] = "<font class=soldsmall>" . $lang['array_types'][$user['active']] . "</font>";
        }

        $user['category1'] = makeurl($userini['category']);
        $user['make1'] = makeurl($userini['make']);
        $user['model1'] = makeurl($userini['model']);
        $user['year1'] = makeurl($userini['year']);
        $user['country1'] = makeurl($userini['country']);
        $user['state1'] = makeurl($userini['state']);
        $user['city1'] = makeurl($userini['city']);


        return $user;
    }

    function getjavascriptarray($default_tabel,$orderby,$id_,$name_,$default_tabel1,$orderby1,$id_1,$name_1,$relatedid){
        global $config;
        global $db; //database
        $sql="SHOW FIELDS FROM `{$config['table_prefix']}$default_tabel` ";
        $result = $db -> query($sql,__FILE__,__LINE__);
        while ($tablefield_array_r = mysqli_fetch_array($result)){
            $tablefield_array[]=$tablefield_array_r['Field'];
        }
        @mysqli_free_result($result);
        $orderby  = (!in_array($orderby,$tablefield_array)) ? $tablefield_array[0]:$orderby;
        $sql = "SELECT * FROM `{$config['table_prefix']}$default_tabel` ORDER BY $orderby";
        $result = $db -> query($sql,__FILE__,__LINE__);
        $num_rows = mysqli_num_rows($result);
        $contor=0;
        $out = '';
        if ($num_rows>0){
            while ($user = mysqli_fetch_assoc($result)){

                $out .= "\tmodelscountryID[$contor]=".$user[$id_]."\n\tmodelscountryArray[".$user[$id_]."] = new Array( ";
                $contor++;
                //second
                $sql1 = "SELECT * FROM `{$config['table_prefix']}$default_tabel1` WHERE $relatedid='".$user[$id_]."' GROUP BY $id_1 ORDER BY $orderby1";
                $result1 = $db -> query($sql1);
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
            }
            @mysqli_free_result($result);
            //echo $out;
            //exit;
            return ($out);
        }else return false;
    }
}


?>
