<?php
echo "started at ".date("H:i:s")."\n";
require_once(".connect.php");

function districts(){
	$ret=[];
	$res=mysql_query("SELECT id,district from vl_districts");
	while ($row=mysql_fetch_array($res)){
		extract($row);
		$ret[]=['id'=>$id,'district'=>$district];
	}
	return $ret;
}

function hubs(){
	$ret=[];
	$res=mysql_query("SELECT id,hub from vl_hubs");
	while ($row=mysql_fetch_array($res)){
		extract($row);
		$ret[]=['id'=>$id,'hub'=>$hub];
	}
	return $ret;
}


function facilities(){
	$ret=[];
	$res=mysql_query("SELECT f.id,facility,districtID,hubID
					  FROM vl_facilities AS f
					  WHERE facility!=''");
	while ($row=mysql_fetch_array($res)){
		extract($row);
		$ret[$id]=[ 'id'=>$id,'facility'=>$facility,'districtID'=>$districtID,'hubID'=>$hubID];
	}
	return $ret;
}

function ageGroupCase(){
	$age=" ROUND((UNIX_TIMESTAMP(s.created)-UNIX_TIMESTAMP(dateOfBirth))/31536000) ";
	$arr=[  1=>"$age <5",
			2=>"$age >=5 && $age <=9",
			3=>"$age >=10 && $age<=18",
			4=>"$age >=19 && $age <=25",
			5=>"$age >=26"];
	
	$ret="CASE ";
	foreach ($arr as $k => $v) {
		$ret.="WHEN $v THEN '$k' ";
	}
	$ret.=" END";
	return $ret;
}

function rjctnRsnCase(){
	$arr=[  
			'eligibility'=>"outcomeReasonsID in (77,78,14,64,65,76) ",
			'incomplete_form'=>"outcomeReasonsID in (4,71,72,69,70,67,68,79,80,87,88,86, 61,81,82)",
			'quality_of_sample'=>"outcomeReasonsID in (9,60,74,10,59,8,63,75,2,7,85,1,5,62 ,3,15,83,84)"
		];
	
	$ret="CASE ";
	foreach ($arr as $k => $v) {
		$ret.="WHEN $v THEN '$k' ";
	}
	$ret.=" END";
	return $ret;
}

function validCases(){
	$ret="";
	$cases=[
		"Failed",
		"Failed.",
		"Invalid",
		"Invalid test result. There is insufficient sample to repeat the assay.",
		"There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a a new sample.",
		"There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a new sample."];

	foreach ($cases as $v) {
		$ret.=" resultAlphanumeric NOT LIKE '$v' AND";
	}
	$ret=" (".substr($ret, 0,-3).") ";
	return $ret;	
}

function suppressedCases(){
	return " ((s.sampleTypeID=1 AND resultNumeric<=5000) OR (s.sampleTypeID=2 AND resultNumeric<=1000))";
}

function getResults($year,$cond="1"){
	$ret=[];
	$age_grp_case=ageGroupCase();

	$sql="SELECT facilityID,month(s.created) AS mth,count(r.id) AS num,$age_grp_case AS age_group 
		  FROM vl_results_merged AS r
		  LEFT JOIN vl_samples AS s ON r.vlSampleID=s.vlSampleID
		  LEFT JOIN vl_patients AS p ON s.patientID=p.id
		  WHERE YEAR(s.created)='$year' AND $cond
		  GROUP BY mth,age_group,facilityID
		  ";
	//return $sql;


	$res=mysql_query($sql)or die(mysql_error());
	while($row=mysql_fetch_assoc($res)){
		extract($row);
		$ret[$mth][$age_group][$facilityID]=$num;
	}
	return $ret;
}

function getRejects($year){
	$ret=[];
	$age_grp_case=ageGroupCase();
	$sql="SELECT facilityID,month(s.created) AS mth,count(v.id) AS num,$age_grp_case AS age_group 
		  FROM vl_samples_verify AS v
		  LEFT JOIN vl_samples AS s ON v.sampleID=s.id 
		  LEFT JOIN vl_patients AS p ON s.patientID=p.id
		  WHERE YEAR(s.created)='$year' AND outcome='Rejected'
		  GROUP BY mth,age_group,facilityID
		  ";
	$res=mysql_query($sql)or die(mysql_error());
	while($row=mysql_fetch_assoc($res)){
		extract($row);
		$ret[$mth][$age_group][$facilityID]=$num;
	}
	return $ret;
}

function getRejects2($year){
	$ret=[];
	$rjctn_rsn_case=rjctnRsnCase();
	$age_grp_case=ageGroupCase();
	$sql="SELECT facilityID,month(s.created) AS mth,count(v.id) AS num,$age_grp_case AS age_group ,$rjctn_rsn_case AS rjctn_rsn
		  FROM vl_samples_verify AS v
		  LEFT JOIN vl_samples AS s ON v.sampleID=s.id 
		  LEFT JOIN vl_patients AS p ON s.patientID=p.id
		  WHERE YEAR(s.created)='$year' AND outcome='Rejected'
		  GROUP BY rjctn_rsn,mth,age_group,facilityID
		  ";
	$res=mysql_query($sql)or die(mysql_error());
	while($row=mysql_fetch_assoc($res)){
		extract($row);
		$ret[$mth][$age_group][$facilityID][$rjctn_rsn]=$num;
	}
	return $ret;
}


function getSamples($year,$cond=1){
	$ret=[];
	$age_grp_case=ageGroupCase();
	$sql="SELECT facilityID,month(s.created) AS mth,count(s.id) AS num,$age_grp_case AS age_group   
		  FROM vl_samples AS s
		  LEFT JOIN vl_patients AS p ON s.patientID=p.id
		  WHERE YEAR(s.created)='$year' AND $cond		  
		  GROUP BY mth,age_group,facilityID";
	$res=mysql_query($sql)or die(mysql_error());
	while($row=mysql_fetch_assoc($res)){
		extract($row);
		$ret[$mth][$age_group][$facilityID]=$num;
	}
	return $ret;
}

function getTrmtIndctn($year){
	$ret=[];
	$age_grp_case=ageGroupCase();
	$sql="SELECT facilityID,month(s.created) AS mth,count(s.id) AS num,$age_grp_case AS age_group,treatmentInitiationID   
		  FROM vl_samples AS s
		  LEFT JOIN vl_patients AS p ON s.patientID=p.id
		  WHERE YEAR(s.created)='$year'		  
		  GROUP BY mth,age_group,facilityID,treatmentInitiationID";
	$res=mysql_query($sql) or die(mysql_error());
	while($row=mysql_fetch_assoc($res)){
		extract($row);
		$ret[$mth][$age_group][$facilityID][$treatmentInitiationID]=$num;
	}
	return $ret;
}


$districts=districts();
$hubs=hubs();
$facilities=facilities();

file_put_contents("../public/json/districts.json", json_encode($districts));
file_put_contents("../public/json/hubs.json", json_encode($hubs));
file_put_contents("../public/json/facilities.json", json_encode($facilities));

$year=2015;
$current_year=date('Y');
$results=[];

while($year<=$current_year){
	$samples=getSamples($year);
	$dbs_samples=getSamples($year," sampleTypeID=1 ");
	$trmt_indctn=getTrmtIndctn($year);
	$rjctn_rsns=getRejects($year);
	$rjctn_rsns2=getRejects2($year);

	$t_rslts=getResults($year);
	$v_rslts=getResults($year,validCases());
	$sprsd_cond=validCases()." AND ".suppressedCases();
	$sprsd=getResults($year,$sprsd_cond);

	/*echo $t_rslts."\n";
	echo $v_rslts."\n";
	echo $sprsd."\n";
	break;*/

	foreach ($samples as $mth => $age_groups) {
		foreach ($age_groups as $age_group_id => $facilities) {
			foreach ($facilities as $facility_id => $num) {
				$data=[];
				$data["year"]=$year;
				$data["month"]=$mth;
				$data["age_group_id"]=$age_group_id;
				$data["facility_id"]=$facility_id;

				$data["samples_received"]=$num;
				$data["dbs_samples"]=isset($dbs_samples[$mth][$age_group_id][$facility_id])?$dbs_samples[$mth][$age_group_id][$facility_id]:0;

				$ti_arr=isset($trmt_indctn[$mth][$age_group_id][$facility_id])?$trmt_indctn[$mth][$age_group_id][$facility_id]:[];
				$data['cd4_less_than_500']=isset($ti_arr[3])?$ti_arr[3]:0;
				$data['pmtct_option_b_plus']=isset($ti_arr[1])?$ti_arr[1]:0;
				$data['children_under_15']=isset($ti_arr[2])?$ti_arr[2]:0;
				$data['other_treatment']=isset($ti_arr[5])?$ti_arr[5]:0;
				$data['treatment_blank_on_form']=isset($ti_arr[0])?$ti_arr[0]:0;
				$data['tb_infection']=isset($ti_arr[4])?$ti_arr[4]:0;
				
				$data['rejected_samples']=isset($rjctn_rsns[$mth][$age_group_id][$facility_id])?$rjctn_rsns[$mth][$age_group_id][$facility_id]:0;
				$rr_arr=isset($rjctn_rsns2[$mth][$age_group_id][$facility_id])?$rjctn_rsns2[$mth][$age_group_id][$facility_id]:[];
				$data['sample_quality_rejections']=isset($rr_arr['quality_of_sample'])?$rr_arr['quality_of_sample']:0;
           		$data['eligibility_rejections']=isset($rr_arr['eligibility'])?$rr_arr['eligibility']:0;
           		$data['incomplete_form_rejections']=isset($rr_arr['incomplete_form'])?$rr_arr['incomplete_form']:0;

				$data['total_results']=isset($t_rslts[$mth][$age_group_id][$facility_id])?$t_rslts[$mth][$age_group_id][$facility_id]:0;
				$data['valid_results']=isset($v_rslts[$mth][$age_group_id][$facility_id])?$v_rslts[$mth][$age_group_id][$facility_id]:0;
				$data['suppressed']=isset($sprsd[$mth][$age_group_id][$facility_id])?$sprsd[$mth][$age_group_id][$facility_id]:0;

				//print_r($data)."\n";
				if(array_key_exists($facility_id, $facilities)) $results[]=$data;
			}
		}
	}

	$year++;
}

$data['results']=$results;
$data['data_date']="Data last updated at ".date("H:i:s")." on ".date("d/m/Y");

file_put_contents("../public/json/data.json", json_encode($data));
echo "finished at ".date("H:i:s")."\n";
?>