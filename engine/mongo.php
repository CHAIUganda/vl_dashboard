<?php
$link=mysql_connect('localhost',"root","chai8910");
mysql_select_db("vldash",$link) or die(mysql_error());

$mongo=new MongoClient();
$res=mysql_query(
	"SELECT s.*,f.*,d.name AS district_name FROM samples_data AS s 
	 LEFT JOIN facilities AS f ON s.facility_id=f.facility_id
	 LEFT JOIN districts AS d ON d.district_id=f.district_id ");

while($row=mysql_fetch_assoc($res)){ 
	extract($row);	
	$nn=[];
	$nn['year_month']=(int)$year_month;
	$nn['age_group_id']=(int)$age_group_id;
	$nn['facility_id']=(int)$facility_id;
	$nn['district_id']=(int)$district_id;
	$nn['hub_id']=(int)$hub_id;
	$nn['district_name']=(int)$district_name;
	$nn['ip_id']=(int)$ip_id;
	$nn['gender']=$gender;
	$nn['regimen_group_id']=(int)$regimen_group_id;
	$nn['regimen_line']=(int)$regimen_line;
	$nn['regimen_time_id']=(int)$regimen_time_id;
	$nn['treatment_indication_id']=(int)$treatment_indication_id;
	$nn['samples_received']=(int)$samples_received;
	$nn['dbs_samples']=(int)$dbs_samples;
	$nn['rejected_samples']=(int)$rejected_samples;
	$nn['sample_quality_rejections']=(int)$sample_quality_rejections;
	$nn['eligibility_rejections']=(int)$eligibility_rejections;
	$nn['incomplete_form_rejections']=(int)$incomplete_form_rejections;
	$nn['total_results']=(int)$total_results;
	$nn['valid_results']=(int)$valid_results;
	$nn['suppressed']=(int)$suppressed;
	$mongo->vldash->samples_data->insert($nn);
};


$res2=mysql_query("SELECT * FROM districts");
$res3=mysql_query("SELECT * FROM facilities");
$res4=mysql_query("SELECT * FROM ips");
$res5=mysql_query("SELECT * FROM hubs");

while($row=mysql_fetch_assoc($res2)){
	extract($row);
	$nn=['name'=>$name,'id'=>$district_id];
	$mongo->vldash->districts->insert($nn);
}

while($row=mysql_fetch_assoc($res3)){
	extract($row);
	$nn=['name'=>$name,'id'=>$facility_id,'hub_id'=>$hub_id,'ip_id'=>$ip_id,"district_id"=>$district_id];
	$mongo->vldash->facilities->insert($nn);
}

while($row=mysql_fetch_assoc($res4)){
	extract($row);
	$nn=['name'=>$name,'id'=>$ip_id];
	$mongo->vldash->ips->insert($nn);
}

while($row=mysql_fetch_assoc($res5)){
	extract($row);
	$nn=['name'=>$name,'id'=>$hub_id];
	$mongo->vldash->hubs->insert($nn);
}

?>