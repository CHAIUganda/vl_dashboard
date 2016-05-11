<?php 
$link=mysql_connect('localhost',"root","chai8910");
if(!$link){
	die('connection to server failed:' . mysql_error());
}
mysql_select_db("vldash",$link) or die(mysql_error());


$res=mysql_query("SELECT * FROM data");

$data=array();
while($row=mysql_fetch_assoc($res)){
	$data[]=$row;
}
echo json_encode($data);
?>