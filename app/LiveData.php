<?php

namespace EID;

use Illuminate\Database\Eloquent\Model;

class LiveData extends Model
{
    //
    protected $connection = 'mysql2';

    const SEX_CASE = "CASE WHEN `gender`='Female' THEN 'f' WHEN `gender`='Male' THEN 'm' ELSE 'x' END";

    //const TRTMT_IDCTN_CASE = "CASE WHEN `treatmentInitiationID`=1 THEN 'b_plus' WHEN `treatmentInitiationID`=4 THEN 'tb' ELSE 'x' END";

    public static function getHubs(){
    	return LiveData::select('id','hub')->from('vl_hubs')->get();
    }

    public static function getDistricts(){
      return LiveData::select('id','district')->from('vl_districts')->get();
    }

    public static function getFacilities(){
      return LiveData::select('id','facility','ipID','hubID','districtID')->from('vl_facilities')->get();
    }

    public static function getIPs(){
      return LiveData::select('id','ip')->from('vl_ips')->get();
    }


    public static function getSamples($year,$cond=1){
    	$age_grp_case=self::ageGroupCase();
      $reg_type_case=self::regimenTypeCase();
      $reg_time_case=self::regimenTimeCase();
    	$sql="SELECT facilityID,month(s.created) AS mth,count(s.id) AS num,
                   $age_grp_case AS age_group,".self::SEX_CASE." AS sex,
                   $reg_type_case AS reg_type,
                   reg_t.treatmentStatusID AS reg_line,
                   $reg_time_case AS reg_time,
                   treatmentInitiationID AS trt   
		        FROM vl_samples AS s
		        LEFT JOIN vl_patients AS p ON s.patientID=p.id
            LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
		        WHERE YEAR(s.created)='$year' AND $cond		  
		        GROUP BY mth,age_group,facilityID,sex,reg_type,reg_line,reg_time,trt";

		  $res=\DB::connection('mysql2')->select($sql);
      if($cond==1) return $res;
      $ret=[];
      foreach ($res as $r) {
        $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
        $k.=$r->reg_type.$r->reg_line.$r->reg_time.$r->trt;
        $ret[$k]=$r->num;
      }
      return $ret; 
    }

    public static function getRejects($year){
        $age_grp_case=self::ageGroupCase();
        $reg_type_case=self::regimenTypeCase();
        $reg_time_case=self::regimenTimeCase();
        $sql="SELECT facilityID,month(s.created) AS mth,count(v.id) AS num,$age_grp_case AS age_group,
                     ".self::SEX_CASE." AS sex,
                     $reg_type_case AS reg_type,
                     reg_t.treatmentStatusID AS reg_line,
                     $reg_time_case AS reg_time,
                     treatmentInitiationID AS trt 
          FROM vl_samples_verify AS v
          LEFT JOIN vl_samples AS s ON v.sampleID=s.id 
          LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
          LEFT JOIN vl_patients AS p ON s.patientID=p.id
          WHERE YEAR(s.created)='$year' AND outcome='Rejected'
          GROUP BY mth,age_group,facilityID,sex,reg_type,reg_line,reg_time,trt
          ";
        //return \DB::connection('mysql2')->select($sql);
        $res=\DB::connection('mysql2')->select($sql);
        $ret=[];
        foreach ($res as $r) {
          $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
          $k.=$r->reg_type.$r->reg_line.$r->reg_time.$r->trt;
          $ret[$k]=$r->num;
        }
        return $ret;
    }

    public static function getRejects2($year){
        $rjctn_rsn_case=self::rjctnRsnCase();
        $age_grp_case=self::ageGroupCase();
        $reg_type_case=self::regimenTypeCase();
        $reg_time_case=self::regimenTimeCase();
        $sql="SELECT facilityID,month(s.created) AS mth,count(v.id) AS num,
                     $age_grp_case AS age_group ,$rjctn_rsn_case AS rjctn_rsn,
                     ".self::SEX_CASE." AS sex,                     
                     $reg_type_case AS reg_type,
                     reg_t.treatmentStatusID AS reg_line,
                     $reg_time_case AS reg_time,
                     treatmentInitiationID AS trt 
              FROM vl_samples_verify AS v
              LEFT JOIN vl_samples AS s ON v.sampleID=s.id 
              LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
              LEFT JOIN vl_patients AS p ON s.patientID=p.id
              WHERE YEAR(s.created)='$year' AND outcome='Rejected'
              GROUP BY rjctn_rsn,mth,age_group,facilityID,sex,reg_type,reg_line,reg_time,trt
              ";
        //return \DB::connection('mysql2')->select($sql);
        $res=\DB::connection('mysql2')->select($sql);
        $ret=[];
        foreach ($res as $r) {
          $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
          $k.=$r->reg_type.$r->reg_line.$r->reg_time.$r->trt.$r->rjctn_rsn;
          $ret[$k]=$r->num;
        }
        return $ret;
    }


    public static function getResults($year,$cond="1"){
        $age_grp_case=self::ageGroupCase();
        $reg_type_case=self::regimenTypeCase();
        $reg_time_case=self::regimenTimeCase();
        $sql="SELECT facilityID,month(s.created) AS mth,count(DISTINCT r.vlSampleID) AS num,$age_grp_case AS age_group,
                      ".self::SEX_CASE." AS sex,
                      $reg_type_case AS reg_type,
                      reg_t.treatmentStatusID AS reg_line,
                      $reg_time_case AS reg_time,
                      treatmentInitiationID AS trt 
              FROM vl_results_merged AS r
              LEFT JOIN vl_samples AS s ON r.vlSampleID=s.vlSampleID
              LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
              LEFT JOIN vl_patients AS p ON s.patientID=p.id
              WHERE YEAR(s.created)='$year' AND $cond
              GROUP BY mth,age_group,facilityID,sex,reg_type,reg_line,reg_time,trt
              ";
        //return \DB::connection('mysql2')->select($sql);
        $res=\DB::connection('mysql2')->select($sql);
        $ret=[];
        foreach ($res as $r) {
          $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
          $k.=$r->reg_type.$r->reg_line.$r->reg_time.$r->trt;
          $ret[$k]=$r->num;
        }
        return $ret;
    }


    private static function ageGroupCase(){
      //31536000 is the number of seconds in a year of 365 days
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

    private static function regimenTimeCase(){
      //2628000 is the number of seconds in a month
      //{1:'6-12 months',2:'1-2 years',3:'2-3 years',4:'3-5 years',5:'5+ years'}
       $time=" ROUND((UNIX_TIMESTAMP(s.created)-UNIX_TIMESTAMP(treatmentInitiationDate))/2628000) ";
       $arr=[
              1=>"$time>=6 && $time<=12",
              2=>"$time>=13 && $time<=24",
              3=>"$time>=25 && $time<=36",
              4=>"$time>=35 && $time<=60",
              5=>"$time>=60"
            ];
    
       $ret="CASE ";
       foreach ($arr as $k => $v) {
          $ret.="WHEN $v THEN '$k' ";
       }
       $ret." ELSE 99";
       $ret.=" END";
       return $ret;
    }

    private static function regimenTypeCase(){
      //{1:'AZT' ,2:'TDF/XTC/EFV' ,3:'TDF/XTC/NVP', 4:'ABC',5:'TDF/XTC/LPV/r' , 6:'TDF/XTC/ATV/r', 7:'Other'}
       $arr=[
              1=>"currentRegimenID in (1,2,13,16,21,22,27,28)",
              2=>"currentRegimenID in (4,6)",
              3=>"currentRegimenID in (3,5)",
              4=>"currentRegimenID in (7,8,17,18,23,24,29,30)",
              5=>"currentRegimenID in (11,12,25,26)",
              6=>"currentRegimenID in (14,15)",
              7=>"currentRegimenID in (19,20,31,71)"
            ];
    
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
