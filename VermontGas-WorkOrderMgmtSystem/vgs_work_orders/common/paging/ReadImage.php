<?php
//$url = "http://corvetteamerica.com/images/catalog/products/item_0001.jpg";
$url = "http://192.168.11.11:10080/wo/shared/images/vtg_logo2.gif";
$gif = file_get_contents($url);
ob_start();
header( "Content-type: image/gif"); 
echo $gif;
ob_end_flush();
//ini_set('allow_url_fopen', 1);
//$handle = fopen($url, "r"); 
//$gif = fread($handle, )
//$image = imagecreatefromgif($url); 
//imagegif( $image ); 
//echo $handle; 
//var_dump($image); 
//fclose($handle); 
?>
