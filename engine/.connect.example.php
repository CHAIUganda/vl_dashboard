<?php 
$link=mysql_connect('localhost',"username","secret");
if(!$link){
	die('connection to server failed:' . mysql_error());
}
mysql_select_db("db",$link) or die(mysql_error());
?>