<?php
$config['sqldb'] = 'mecarz_mecarz';
$config['sqluser'] = 'mecarz_admin';
$config['sqlpass'] = 'car5pa55';
$config['sqlhost'] = 'localhost';
$config['table_prefix'] = 'mecarz';

$config['path'] = '/home/mecarz/public_html/mecarz.com/';
if (eregi("www.mecarz.com",$_SERVER["HTTP_HOST"])){
$config['url_path'] = 'http://www.mecarz.com/';
}else {
$config['url_path'] = 'http://mecarz.com/';
}

$config['wywiwyg_path']=$config['path'].'FCKeditor/';
$config['wywiwyg_editor']=$config['url_path'].'FCKeditor/';

define("_INSTALL","1");
define("_VER","4.0");
?>