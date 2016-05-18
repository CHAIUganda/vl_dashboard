<?php

namespace EID;

use Illuminate\Database\Eloquent\Model;

class LiveData extends Model
{
    //
    protected $connection = 'mysql2';

    const SEX_CASE = "CASE WHEN `gender`='Female' THEN 'f' WHEN `gender`='Male' THEN 'm' ELSE 'x' END";

    const TRTMT_IDCTN_CASE = "CASE WHEN `treatmentInitiationID`=1 THEN 'b_plus' WHEN `treatmentInitiationID`=4 THEN 'tb' ELSE 'x' END";

    public static function getHubs(){
    	return LiveData::select('id','hub')->from('vl_hubs')->get();
    }


    public static function getSamples($year,$cond=1){
    	$age_grp_case=self::ageGroupCase();
    	$sql="SELECT facilityID,month(s.created) AS mth,count(s.id) AS num,
                   $age_grp_case AS age_group,".self::SEX_CASE." AS sex,
                   currentRegimenID AS reg,".self::TRTMT_IDCTN_CASE." AS trt   
		        FROM vl_samples AS s
		        LEFT JOIN vl_patients AS p ON s.patientID=p.id
		        WHERE YEAR(s.created)='$year' AND $cond		  
		        GROUP BY mth,age_group,facilityID,sex,reg,trt";
		  return \DB::connection('mysql2')->select($sql); 
    }


    public static function getTrmtIndctn($year){
        $age_grp_case=LiveData::ageGroupCase();
        $sql="SELECT facilityID,month(s.created) AS mth,count(s.id) AS num,$age_grp_case AS age_group,treatmentInitiationID   
          FROM vl_samples AS s
          LEFT JOIN vl_patients AS p ON s.patientID=p.id
          WHERE YEAR(s.created)='$year'       
          GROUP BY mth,age_group,facilityID,treatmentInitiationID";
        return \DB::connection('mysql2')->select($sql);
    }

    public static function getRejects($year){
        $age_grp_case=LiveData::ageGroupCase();
        $sql="SELECT facilityID,month(s.created) AS mth,count(v.id) AS num,$age_grp_case AS age_group,
                     ".self::SEX_CASE." AS sex,currentRegimenID AS reg,".self::TRTMT_IDCTN_CASE." AS trt 
          FROM vl_samples_verify AS v
          LEFT JOIN vl_samples AS s ON v.sampleID=s.id 
          LEFT JOIN vl_patients AS p ON s.patientID=p.id
          WHERE YEAR(s.created)='$year' AND outcome='Rejected'
          GROUP BY mth,age_group,facilityID,sex,reg,trt
          ";
        return \DB::connection('mysql2')->select($sql);
    }

    public static function getRejects2($year){
        $rjctn_rsn_case=LiveData::rjctnRsnCase();
        $age_grp_case=LiveData::ageGroupCase();
        $sql="SELECT facilityID,month(s.created) AS mth,count(v.id) AS num,$age_grp_case AS age_group ,$rjctn_rsn_case AS rjctn_rsn,
                     ".self::SEX_CASE." AS sex,currentRegimenID AS reg,".self::TRTMT_IDCTN_CASE." AS trt 
              FROM vl_samples_verify AS v
              LEFT JOIN vl_samples AS s ON v.sampleID=s.id 
              LEFT JOIN vl_patients AS p ON s.patientID=p.id
              WHERE YEAR(s.created)='$year' AND outcome='Rejected'
              GROUP BY rjctn_rsn,mth,age_group,facilityID,sex,reg,trt
              ";
        return \DB::connection('mysql2')->select($sql);
    }


    public static function getResults($year,$cond="1"){
        $age_grp_case=LiveData::ageGroupCase();
        $sql="SELECT facilityID,month(s.created) AS mth,count(DISTINCT r.vlSampleID) AS num,$age_grp_case AS age_group,
                      ".self::SEX_CASE." AS sex,currentRegimenID AS reg,".self::TRTMT_IDCTN_CASE." AS trt 
              FROM vl_results_merged AS r
              LEFT JOIN vl_samples AS s ON r.vlSampleID=s.vlSampleID
              LEFT JOIN vl_patients AS p ON s.patientID=p.id
              WHERE YEAR(s.created)='$year' AND $cond
              GROUP BY mth,age_group,facilityID,sex,reg,trt
              ";
        return \DB::connection('mysql2')->select($sql);
    }


    private static function ageGroupCase(){
       $age=" ROUND((UNIX_TIMESTAMP(s.created)-UNIX_TIMESTAMP(dateOfBirth))/31536000) ";
       $arr=[1=>"$age <5",
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

    private static function rjctnRsnCase(){
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


}
