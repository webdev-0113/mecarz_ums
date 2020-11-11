<?php
$config['sqldb'] = 'mecarz_mecarz';
//$config['sqluser'] = 'mecarz_admin';
//$config['sqlpass'] = 'car5pa55';
//$config['sqlhost'] = 'localhost';
$config['sqluser'] = 'root';
$config['sqlpass'] = '';
$config['sqlhost'] = '192.168.52.135';
$config['table_prefix'] = 'mecarz';

//$config['path'] = '/home/mecarz/public_html/mecarz.com/';
$config['path'] = '/opt/lamp/htdocs/mecarz_ums/';
if (preg_match("/www.mecarz.com/", $_SERVER["HTTP_HOST"])){
//    $config['url_path'] = 'http://www.mecarz.com/';
    $config['url_path'] = 'http://localhost/mecarz_ums/';
}else {
//    $config['url_path'] = 'http://mecarz.com/';
    $config['url_path'] = 'http://localhost/mecarz_ums/';
}

$config['wywiwyg_path']=$config['path'].'FCKeditor/';
$config['wywiwyg_editor']=$config['url_path'].'FCKeditor/';

define("_INSTALL","1");
define("_VER","4.0");
?>