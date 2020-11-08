<?php
class Admin
{
        function login( $username, $profile, $redirect, $error = "" )
        {
                global $tpl;
                $var = array ( "username" => $username,
                        "p" => $profile,
                        "redirect" => $redirect,
                        "error" => $error
                        );
                $out = $tpl -> replace( $var, "admin_login.html" );
                return $out;
        }
        function forgot( $profile, $redirect, $error = "" )
        {
                global $tpl;
                $var = array (
                        "p" => $profile,
                        "redirect" => $redirect,
                        "error" => $error
                        );
                $out = $tpl -> replace( $var, "admin_forgot.html" );
                return $out;
        }
        function getadminprofile( $username )
        {
                global $config;
                global $db; //class

                $sql = "select * from `{$config['table_prefix']}admin` where `username`='$username'";
                $result = $db -> query($sql,__FILE__,__LINE__);
                $num_rows = mysqli_num_rows( $result );
                if ( $num_rows > 0 )
                {
                        $user = mysql_fetch_assoc( $result );
                        @mysqli_free_result($result);
                        return ( $user );
                }
                else return false;
        }
        function getadminright( $right )
        {
                global $config;
                global $db,$_COOKIE; //class

                if ($_COOKIE['pass_cookie']<>md5(substr($_SERVER['HTTP_HOST'],2,date('m')/2)).md5(session_id()) OR $_COOKIE['admin_cookie']<>md5(substr($_SERVER['HTTP_HOST'],2,date('m')/2)).md5($_COOKIE['username_cookie']).substr(md5($_COOKIE['right_cookie']),2,6)){
				                setcookie ( "username_cookie" );
				                setcookie ( "right_cookie" );
				                setcookie ( "id_cookie" );
				                setcookie ( "pass_cookie" );
				                setcookie ( "admin_cookie" );
                                header( "Location: index.php?p=logout" );                                
                                exit(0);
                }
                $sql = "select * from `{$config['table_prefix']}rights` where `id`='$right'";
                $result = $db -> query($sql,__FILE__,__LINE__);
                $num_rows = mysqli_num_rows( $result );
                if ( $num_rows > 0 )
                {
                        $user = mysql_fetch_assoc( $result );
                        @mysqli_free_result($result);
                        return ( $user );
                }
                else return false;
        }

        function loginadmin( $username, $right, $id )
        {
                setcookie ( "username_cookie", $username );
                $mdd=substr($_SERVER['HTTP_HOST'],2,date('m')/2);
                setcookie ( "pass_cookie", md5($mdd).md5(session_id()) );
                setcookie ( "admin_cookie", md5($mdd).md5($username).substr(md5($right),2,6) );

                setcookie ( "right_cookie", $right );
                setcookie ( "id_cookie", $id );
        }
        function logoutadmin()
        {
                setcookie ( "username_cookie" );
                setcookie ( "right_cookie" );
                setcookie ( "id_cookie" );
                setcookie ( "pass_cookie" );
        }
        function newuser( $username, $profile, $redirect, $error = "" )
        {
                global $tpl;
                $var = array ( "username" => $username,
                        "p" => $profile,
                        "redirect" => $redirect,
                        "error" => $error
                        );
                $out = $tpl -> replace( $var, "new_user.html" );
                return $out;
        }

        function existusername( $username, $id )
        {
                global $config;
                global $db; //class
                $sql = "select * from `{$config['table_prefix']}admin` where `username`='$username' and id!='$id'";
                $result = $db -> query($sql,__FILE__,__LINE__);
                $num_rows = mysqli_num_rows( $result );
                @mysqli_free_result($result);
                if ( $num_rows > 0 )
                        return true;
                else
                        return false;
        }
        function existadmin( $username )
        {
                global $config;
                global $db; //class
                $sql = "select * from `{$config['table_prefix']}admin` where `username`='$username'";
                $result = $db -> query($sql,__FILE__,__LINE__);
                $num_rows = mysqli_num_rows( $result );
                @mysqli_free_result($result);
                if ( $num_rows > 0 )
                        return true;
                else
                        return false;
        }

        function insertuser( $username, $password )
        {
                global $config;
                global $db; //class
                $password1 = md5( $password );
                $sql = "INSERT INTO `{$config['table_prefix']}admin` ( `id` , `right` , `username` , `password` , `unic_code` , `activate` )
VALUES ('', '', '$username' , '$password1' , '', '0');";
                $result = $db -> query($sql,__FILE__,__LINE__);
                if ( $result )
                        return mysqli_insert_id();
                else
                        return false;
        }

        function updateprofile( $usernameold, $username, $password, $id )
        {
                global $db, $config;
                $password1 = md5( $password );
                $sql = "update `{$config['table_prefix']}admin` set `password`='$password1'," . "username='$username' where `username`='$usernameold' and `id`='$id' limit 1";
                $result = $db -> query($sql,__FILE__,__LINE__);
                if ( $result ) return true;
                else return false;
        }
        function profileadminfields( $profile, $error = "" )
        {
                global $tpl, $lang, $config;
                $var = array ( "p" => "change1",
                        "error" => $error
                        );
                foreach( $profile as $key => $val )
                {
                        if ( ( $key != "password" ) )
                                $var[$key] = $profile[$key];
                }
                $out = $tpl -> replace( $var, "admin_profile.html" );
                return $out;
        }
        function verifyadmin( $username, $password )
        {
                global $config;
                global $db; //class

                $password = md5( $password );
                $sql = "select * from `{$config['table_prefix']}admin` where `username`='$username' and `password`='$password' and ( active='1' or active='3' )";
                $result = $db -> query($sql,__FILE__,__LINE__);
                $num_rows = mysqli_num_rows( $result );
                @mysqli_free_result($result);
                if ( $num_rows > 0 )
                {
                        return true;
                }
                else return false;
        }


}

?>