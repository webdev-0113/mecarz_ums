<?

class TPL {

function replace($var=array(),$file,$path="",$condtemplates=1) {
 global $config,$lang,$HTTP_POST_VARS,$msg_global,$multiplelanguage;
 unset($output);
 if ($path=="") {
     $output = implode("", file($config['tpl'].$file));
 }else{
     $output = implode("", file($config['tplini'].$path."/".$file));
 }
/*
 //end var auto
 //##########
 //##########
 
 $html = $output;
 $output2 = preg_replace("/>(\w+[\w\s&;-]*)</e", "'>{{tpl_auto_'.str_replace(array(' '),'_','\\1').'}}<'", $html);
 $output23 = preg_replace("/>(\w+(\s)*\w+(\s)*\w+(\s)*\w+)</e", "'>{{tpl_auto_'.str_replace(' ','_','\\1').'}}<'", $output1);

 preg_match_all ( "/{{([\w\s&;-]+)}}/e", $output2, $keywords, PREG_PATTERN_ORDER );

 foreach ( $keywords[0] as $key => $val )
 {
         if ( preg_match( "{{tpl_auto_", $val ) )
         {

                 $val = eregi_replace( "{{|}}", "", $val );
                 //$val = eregi_replace( "\_\&\_", "_", $val );
                 $val1 = eregi_replace( "tpl_auto_", "", $val );
                 $val1 = eregi_replace( "_", " ", $val1 );
                 $val1 = ucwords( $val1 );
                 if ($lang[$val]=='') {
                     $msg_global .= "\$lang['$val'] = \"$val1\";\n";
                 }
         }
 }


 //##########
 //##########
 ///ends
 */
 if (!is_array($var)) $var=array();
 if (is_array($var) and preg_match("/{{",$output)) {
  foreach ($var as $key => $value) {
       if ($key=="error")
          $output=eregi_replace ("({{error}})", "<font color=\"red\" style=\"font-weight:bold;\">&nbsp;\\1</font>", $output);
       if ($key=="error1")
          $output=eregi_replace ("({{error1}})", "<font color=\"red\" style=\"font-weight:bold;\">&nbsp;\\1</font>", $output);
          
       if ($key=="tpl_msg")
          $output=eregi_replace ("({{tpl_msg}})", "<font class=\"msg\" style=\"font-weight:bold;\">&nbsp;\\1</font>", $output);

       $value=stripslashes($value);
       $output=str_replace("{{".$key."}}",$value,$output);
  }
  if (count($multiplelanguage)>0){
  if ($_SESSION['language_session']=='') {
        $language_set1='0';
        $language_session1="&amp;language_session=0";
  }else{
        $language_set1=$_SESSION['language_session'];
        $language_session1="&amp;language_session=".$_SESSION['language_session'];
  }
  $language_setini=$language_set1;
  $language_set1=$language_set1.'-';
  }
  $output = preg_replace("/{{language_set}}/e", "\$language_set1", $output);
  $output = preg_replace("/{{language_setini}}/e", "\$language_setini", $output);
  $output = preg_replace("/{{language_session}}/e", "\$language_session1", $output);


  $output = preg_replace("/<a href=\"(.*).html\">/e", "'<a href=\"'.eregi_replace(' ','-','\\1').'.html\">'", $output);
  $output = preg_replace("/{{tpl_auto_(\w+)}}/e", "\$lang['tpl_auto_\\1']", $output);
  $output = preg_replace("/{{config_auto_(\w+)}}/e", "\$config['config_auto_\\1']", $output);
  $output = preg_replace("/{{input_(\w+)_val}}/e", "PrepareForWrite(\$_REQUEST['input_\\1'])", $output);
  if ($condtemplates){
   $output = ereg_replace('{{[^}}]*}}','',$output);
  }
 }
 return $output;
}

}

?>