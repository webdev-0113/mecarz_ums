<?

$sBasePath = $_SERVER['PHP_SELF'] ;
$sBasePath = substr( $sBasePath, 0, strpos( $sBasePath, "/editor/" ) ) ;
echo $Config['UserFilesPath'] = $sBasePath.'/UserFiles/' ;


?>