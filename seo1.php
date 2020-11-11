<?php



if ($_REQUEST['p']=='signup'){
$lang['tpl_auto_Pragraph'] =<<<END

<br><b>Selling your car on <a href="http://www.MECarz.com">MECarz.com</a> is free and only involve three straight forward steps below:</b></br>
<br>1- Register by filling the forum below with your information (Used Car Seller Information)</br>
<br>2- Login at the following link: <a href="http://www.mecarz.com/admin/index.php"> Login Link</a></br>
<br>3- Post the used car you are aiming to sell information & wait buyers calls!!!</br>




END;

}


/**** Auto generate the text for the Country pages Start   ***/ 


if ($_REQUEST['country']>0 and $_REQUEST['state']==0  and $_REQUEST['city']==0 and $_REQUEST['category']==0 and $_REQUEST['make']==0 and $_REQUEST['model']==0 and $_REQUEST['p']=='search' and $_REQUEST['changeseo']==1){

$category_profile = $Global_Class->getprofile( $_REQUEST[country], "country", 'id' );
$numbercars = $Global_Class->getnumrows( $_REQUEST[country], "cars", "country"," and active>=1 ") ;




$sql = "SELECT * FROM `{$config['table_prefix']}state` where countryid={$_REQUEST['country']} order by name";
              $result = $db->query( $sql );
              $num_rows = mysqli_num_rows( $result );
              $contor=0;
              $looper=0;
              if ( $num_rows > 0 ) {

$out.="<pre></tr>"; 
                  while ( $var = mysqli_fetch_assoc( $result ) ) {
$number = $Global_Class->getnumrows( $var[id], "cars", "state" ," and active>=1 ") ;
$url=makeurl($var["name".$language_set]);
$out.="<td><font color=\"F53D00\"size=\"2\">@</font><a href='/searchstate-{$var[id]}-{$url}.html'>{$var["name".$language_set]} Used Cars ({$number})</a></td>";
                           //$var["name".$language_set]
$looper = $looper + 1;
if (($looper%3) ==0) $out.="<tr></tr>";                           
  
                  } // while
$out.="</pre>"; 

              }


$lang['tpl_auto_Pragraph'] =<<<END

<br></br>
<table border="1" width=100%>
<tr>
  <td>

<font size="5" color="F53D00" ><h1><strong>Used Cars in {$category_profile['name'.$language_set]}</strong></h1></font><br>
<font size="3"><b>{$numbercars} used cars currently posted for sale in {$category_profile['name'.$language_set]} </b></font></br>

<br>MECarz.com is the leading website for selling and buying <font font-weight="bold"> used cars in {$category_profile['name'.$language_set]} </font>. With our growing inventory of <strong>{$category_profile['name'.$language_set]} used cars</strong>, trucks, crossover and SUVs mostly posted directly by private used car owners in your surrounding area. Though private car sellers are not the only source of cars posted on MECarz.com, as many <strong>{$category_profile['name'.$language_set]} car dealerships</strong> have found no better way to market their used cars in {$category_profile['name'.$language_set]} than MECarz.com classifieds. Finding  <strong>pre-owned vehicles in {$category_profile['name'.$language_set]} </strong> that are for sale has never been easier.</br>







<table border="0" width=100%>
<tr>

<h2><font size="2" color="F53D00" > Which {$category_profile['name'.$language_set]} State/Province you are looking for car in? </font></h2>

$out



</td>
</tr>


</table>


<br></br>



</td>
</tr>


</table>
<br></br>

END;

}

/**** Auto generate the text for the Country pages End   ***/ 


/**** Auto generate the text for the State pages Start   ***/ 

if ($_REQUEST['country']==0 and $_REQUEST['state']>0 and $_REQUEST['city']==0 and $_REQUEST['category']==0 and $_REQUEST['make']==0 and $_REQUEST['model']==0 and $_REQUEST['p']=='search' and $_REQUEST['changeseo']==1){

$category_profile = $Global_Class->getprofile( $_REQUEST[state], "state", 'id' );
$numbercars = $Global_Class->getnumrows( $_REQUEST[state], "cars", "state"," and active>=1 ") ;




$sql = "SELECT * FROM `{$config['table_prefix']}city` where stateid={$_REQUEST['state']} order by name";
              $result = $db->query( $sql );
              $num_rows = mysqli_num_rows( $result );
              $contor=0;
              $looper=0;

              /* if the state have cities then you will generate the city list table */
            
              if ( $num_rows > 0 ) {



                    $out.="<table border=\"0\" width=100%>"; 
                    $out.="<tr><font size=\"2\" color=\"F53D00\" > Which {$category_profile['name'.$language_set]} City you are looking for car in? </font></tr>"; 
                    $out.="<pre>"; 


                    while ( $var = mysqli_fetch_assoc( $result ) ) {
                         $number = $Global_Class->getnumrows( $var[id], "cars", "city" ," and active>=1 ") ;
                         $url=makeurl($var["name".$language_set]);
                         $out.="<td><font color=\"F53D00\"size=\"2\">@</font> <a href='/searchcity-{$var[id]}-{$url}.html'>{$var["name".$language_set]} Used Cars ({$number})</a></td>";
                         //$var["name".$language_set]
                         $looper = $looper + 1;
                         if (($looper%3) ==0) $out.="</tr><tr>";                           
  
                     } // while
                     $out.="</pre>"; 
                     $out.="</table><br></br>"; 

              }  // If End here
             





 // Below is the code for states that does not have cities for smaller countries like Kuwait Start here.
              else{
              
                     $sql = "SELECT * FROM `{$config['table_prefix']}make` where 1 order by name";
                     $result = $db->query( $sql );
                     $num_rows = mysqli_num_rows( $result );
                     $contor=0;
                     $looper=0;
                     if ( $num_rows > 0 ) {

                           $out.="<table border=\"0\" width=100%>"; 
                           $out.="<tr><font size=\"2\" color=\"F53D00\" > Which Used Cars in {$category_profile['name'.$language_set]}  brand you are looking for?</font></tr>"; 
                           $out.="<pre>"; 


                           while ( $var = mysqli_fetch_assoc( $result ) ) {
                                 $url=makeurl($var["name".$language_set]);
                                 $out.="<td><font color=\"F53D00\"size=\"2\">@</font><a href='/statemake-{$_REQUEST['state']}-{$category_profile['name'.$language_set]}-{$var[id]}-{$url}.html'>{$var["name".$language_set]} Used Cars in {$category_profile['name'.$language_set]}</a></td>";
                                 //$var["name".$language_set]
                                 $looper = $looper + 1;
                                 if (($looper%3) ==0) $out.="</tr><tr>";                           
  
                          } // while
                          $out.="</pre>"; 
                          $out.="</table><br></br>"; 

                       }


                } // End else here
 // The code for states that does not have cities for smaller countries like Kuwait End Here.



$lang['tpl_auto_Pragraph'] =<<<END

<br></br>
<table border="1" width=100%>
<tr>
  <td>

<font size="5" color="F53D00" ><h1><strong>Used Cars in {$category_profile['name'.$language_set]}</strong></h1></font><br>
<font size="3"><b>{$numbercars} used cars currently posted for sale in {$category_profile['name'.$language_set]} </b></font></br>

<br>MECarz.com is the leading website for selling and buying <font font-weight="bold"> used cars in {$category_profile['name'.$language_set]} </font>. With our growing inventory of <strong>{$category_profile['name'.$language_set]} used cars</strong>, trucks, crossover and SUVs mostly posted directly by private used car owners in your surrounding area. Though private car sellers are not the only source of cars posted on MECarz.com, as many <strong>{$category_profile['name'.$language_set]} car dealerships</strong> have found no better way to market their used cars in {$category_profile['name'.$language_set]} than MECarz.com classifieds. Finding  <strong>pre-owned vehicles in {$category_profile['name'.$language_set]} </strong> that are for sale has never been easier.</br>





<br></br>  

$out

</td>
</tr>


</table>
<br></br>
END;

}

/**** Auto generate the text for the State pages End   ***/ 










/**** Auto generate the text for the City pages Start   ***/ 


if ($_REQUEST['country']==0 and $_REQUEST['state']==0 and $_REQUEST['city']>0 and $_REQUEST['category']==0 and $_REQUEST['make']==0 and $_REQUEST['model']==0 and $_REQUEST['p']=='search' and $_REQUEST['changeseo']==1){

$category_profile = $Global_Class->getprofile( $_REQUEST[city], "city", 'id' );
$numbercars = $Global_Class->getnumrows( $_REQUEST[city], "cars", "city"," and active>=1 ") ;



$sql = "SELECT * FROM `{$config['table_prefix']}make` where 1 order by name";
              $result = $db->query( $sql );
              $num_rows = mysqli_num_rows( $result );
              $contor=0;
              $looper=0;
              if ( $num_rows > 0 ) {



$out.="<table border=\"0\" width=100%>"; 
$out.="<tr><font size=\"2\" color=\"F53D00\" > Which Used Cars in {$category_profile['name'.$language_set]}  brand you are looking for?</font></tr>"; 
$out.="<pre>"; 


                  while ( $var = mysqli_fetch_assoc( $result ) ) {
$url=makeurl($var["name".$language_set]);
$out.="<td><font color=\"F53D00\"size=\"2\">@</font><a href='/citymake-{$_REQUEST['city']}-{$category_profile['name'.$language_set]}-{$var[id]}-{$url}.html'>{$var["name".$language_set]} Used Cars in {$category_profile['name'.$language_set]}</a></td>";
                           //$var["name".$language_set]
$looper = $looper + 1;
if (($looper%3) ==0) $out.="</tr><tr>";                           
  
                  } // while
$out.="</pre>"; 
$out.="</table><br></br>"; 

              }






$lang['tpl_auto_Pragraph'] =<<<END

<br></br>
<table border="1" width=100%>
<tr>
  <td>

<font size="5" color="F53D00" ><h1><strong>Used Cars in {$category_profile['name'.$language_set]}</strong></h1></font><br>
<font size="3"><strong>{$numbercars} used cars currently posted for sale in {$category_profile['name'.$language_set]} </strong></font></br>

<br>MECarz.com is the leading website for selling and buying <font font-weight="bold"> used cars in {$category_profile['name'.$language_set]} </font>. With our growing inventory of <strong>{$category_profile['name'.$language_set]} used cars</strong>, trucks, crossover and SUVs mostly posted directly by private used car owners in your surrounding area. Though private car sellers are not the only source of cars posted on MECarz.com, as many <strong>{$category_profile['name'.$language_set]} car dealerships</strong> have found no better way to market their used cars in {$category_profile['name'.$language_set]} than MECarz.com classifieds. Finding  <strong>pre-owned vehicles in {$category_profile['name'.$language_set]} </strong> that are for sale has never been easier.</br>

<br></br>  



$out


</td>
</tr>


</table>
<br></br>
END;

}

/**** Auto generate the text for the City pages End   ***/ 






/**** Auto generate the text for the City Make pages Start   ***/ 


if ($_REQUEST['country']==0 and $_REQUEST['state']==0 and $_REQUEST['city']>0 and $_REQUEST['category']==0 and $_REQUEST['make']>0 and $_REQUEST['model']==0 and $_REQUEST['p']=='search' and $_REQUEST['changeseo']==1){

$category_profile = $Global_Class->getprofile( $_REQUEST[city], "city", 'id' );
$make_profile = $Global_Class->getprofile( $_REQUEST['make'], "make", 'id' );

$lang['tpl_auto_Pragraph'] =<<<END

<br></br>
<table border="1" width=100%>
<tr>
  <td>

<font size="5" color="F53D00" ><h1><strong>{$make_profile['name'.$language_set]} Used Cars in {$category_profile['name'.$language_set]}</strong></h1></font><br>

<br>MECarz.com is the leading website for selling and buying <font font-weight="bold"> {$make_profile['name'.$language_set]} used cars in {$category_profile['name'.$language_set]} </font>. With our growing inventory of <strong>{$category_profile['name'.$language_set]} {$make_profile['name'.$language_set]} used cars</strong>, trucks, crossover and SUVs mostly posted directly by private used car owners in your surrounding area. Though private car sellers are not the only source of cars posted on MECarz.com, as many <strong>{$category_profile['name'.$language_set]} {$make_profile['name'.$language_set]} car dealerships</strong> have found no better way to market their {$make_profile['name'.$language_set]} used cars in {$category_profile['name'.$language_set]} than MECarz.com classifieds. Finding  <strong>pre-owned {$make_profile['name'.$language_set]} vehicles in {$category_profile['name'.$language_set]} </strong> that are for sale has never been easier.</br>

<br></br>  



$out


</td>
</tr>


</table>

END;

}


/**** Auto generate the text for the City Make pages End   ***/ 
















?>
