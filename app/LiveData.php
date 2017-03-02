<?php

namespace EID;

use Illuminate\Database\Eloquent\Model;

class LiveData extends Model
{
    //
    protected $connection = 'live_db';

    const SEX_CASE = "CASE WHEN `gender`='Female' THEN 'f' WHEN `gender`='Male' THEN 'm' ELSE 'x' END";

    //const TRTMT_IDCTN_CASE = "CASE WHEN `treatmentInitiationID`=1 THEN 'b_plus' WHEN `treatmentInitiationID`=4 THEN 'tb' ELSE 'x' END";

    public static function getSample($id){
      return 'x';
    }

    public static function wkshtby($cond=1){
      $sql = "SELECT w.id, w.worksheetReferenceNumber FROM vl_samples_worksheet AS ws 
              LEFT JOIN vl_samples_worksheetcredentials AS w ON ws.worksheetID=w.id
              LEFT JOIN vl_samples AS s ON ws.sampleID = s.id
              LEFT JOIN vl_facilities AS f ON s.facilityID=f.id
              WHERE $cond
              GROUP BY w.id ORDER BY w.id DESC LIMIT 1000
              ";
      return \DB::connection('live_db')->select($sql); 
    }
    
    public static function getFacilitiesPrinting(){
      $hub_limit = \Auth::user()->hub_id;
      $f_limit = \Auth::user()->facility_id;
      $conds = "1";
      if(!empty($hub_limit)){
        $conds = "f.hubID = $hub_limit";
      }elseif(!empty($f_limit)){
        $conds = "f.id = $f_limit";
      }

      $sql = "SELECT f.id AS fid,facility, contactPerson, phone, email, 
              COUNT(CASE WHEN printed='YES' THEN 1 END) AS printed_yes,
              COUNT(CASE WHEN printed='NO' THEN 1 END) AS printed_no
              FROM vl_facility_printing AS fp
              LEFT JOIN vl_samples AS s ON s.id = fp.sample_id
              RIGHT JOIN vl_facilities AS f ON s.facilityID = f.id
              WHERE $conds
              GROUP BY f.facility";

      return \DB::connection('live_db')->select($sql);
    }

     public static function getLogsStats(){
      $sql = "SELECT h.hub, 
              COUNT(CASE WHEN printed='YES' THEN 1 END) AS num_printed,
              COUNT(CASE WHEN printed='NO' THEN 1 END) AS num_pending,
              COUNT(frp.id) AS num_reprinted,
              COUNT(fd.id) AS num_downloaded

              FROM vl_samples AS s
              LEFT JOIN vl_facility_printing AS fp ON s.id = fp.sample_id
              LEFT JOIN vl_facility_reprinting AS frp ON s.id = frp.sample_id
              LEFT JOIN vl_facility_downloads AS fd ON s.id = fd.sample_id
              RIGHT JOIN vl_facilities AS f ON s.facilityID = f.id
              RIGHT JOIN vl_hubs AS h ON f.hubID = h.id
              WHERE 1
              GROUP BY h.hub";
      return \DB::connection('live_db')->select($sql);

    }

    public static function getResultsList($printed=''){
      $ret = LiveData::leftjoin('vl_samples AS s', 's.id', '=', 'sample_id')
                      ->leftjoin('vl_patients As p', 'p.id', '=', 'patientID')
                      ->leftjoin('vl_facilities AS f', 'f.id', '=', 's.facilityID')
                      ->leftjoin('vl_hubs AS h', 'h.id', '=', 'f.hubID')
                      ->leftjoin('vl_results_released AS rr', 'rr.sample_id', '=', 'pr.sample_id')
                      ->select('pr.sample_id','formNumber','collectionDate', 'receiptDate', 'hub', 'facility', 
                               'artNumber', 'otherID', 'qc_at','printed','printed_at','printed_by')
                      ->from('vl_facility_printing AS pr')->where('ready', '=', 'YES')->whereNotNull('rr.sample_id');
      if($printed=='NO'){
        //this to be interpreted as pending
        $ret = $ret->where('printed','=','NO')->where('downloaded','=','NO');
      }elseif($printed=='YES'){
        //this to be interpreted as printed or downloaded
        $ret =$ret->where(function($query){
                    $query->where('printed','=','YES')->orWhere('downloaded','=','YES');
              }); 
      }

      $hub_id = \Auth::user()->hub_id;
      $facility_id = \Auth::user()->facility_id;
       if(\Request::has('f')){
         $ret = $ret->where('f.id','=', \Request::get('f'));
      }elseif(!empty($hub_id)){
        $ret = $ret->where('f.hubID', $hub_id);
      }elseif(!empty($facility_id)){
         $ret = $ret->where('f.id', $facility_id);
      }else{
         $ret = $ret->where('s.id', 0);
      } 

      $ret = $printed=='YES'?$ret->orderby('printed_at', 'DESC'):$ret->orderby('qc_at', 'DESC');
      return $ret;    
    }

    public static function searchWorksheet($q){
      return  LiveData::select('id', 'worksheetReferenceNumber')
                      ->from('vl_samples_worksheetcredentials')
                      ->where('worksheetReferenceNumber','like',"%$q%")
                      ->limit(10)
                      ->get();
    }

    public static function worksheetSamples($id){
      $ret = LiveData::leftjoin('vl_samples AS s', 's.id', '=', 'rr.sample_id')
                      ->leftjoin('vl_patients As p', 'p.id', '=', 'patientID')
                      ->leftjoin('vl_facilities AS f', 'f.id', '=', 's.facilityID')
                      ->leftjoin('vl_districts AS d', 'd.id', '=', 'f.districtID')
                      ->leftjoin('vl_hubs AS h', 'h.id', '=', 'f.hubID')
                      ->leftjoin('vl_facility_printing AS fp', 'fp.sample_id', '=', 's.id')
                      ->select('s.*','rr.sample_id', 'hub', 'facility', 'p.*', 'fp.id As fp_id', 'result', 'd.district')
                      ->from('vl_results_released AS rr')
                      ->where('rr.worksheet_id','=',$id)
                      ->orderby('lrEnvelopeNumber', 'ASC')
                      ->orderby('lrNumericID', 'ASC')
                      ->get();
      return $ret;
    }

    public static function getHubs(){
    	return LiveData::select('id','hub')->from('vl_hubs')->get();
    }

    public static function getDistricts(){
      return LiveData::select('id','district')->from('vl_districts')->get();
    }

    public static function getFacilities(){
      return LiveData::select('id','facility','ipID','hubID','districtID')->from('vl_facilities')->get();
    }

    public static function getFacilities2(){
      $res=LiveData::select('id','facility','ipID','hubID','districtID')->from('vl_facilities')->get();
      $ret=[];
      foreach ($res as $row)  $ret[$row->id]=$row;
      return $ret;
    }

    public static function getIPs(){
      return LiveData::select('id','ip')->from('vl_ips')->get();
    }

    public static function getRegimens(){
      return self::select('id', 'appendix')->from('vl_appendix_regimen')->get();
    }


    public static function getSamples($year,$cond=1){
    	$age_grp_case=self::ageGroupCase();
      #$reg_type_case=self::regimenTypeCase();
      $reg_time_case=self::regimenTimeCase();
    	$sql="SELECT facilityID,month(s.created) AS mth,count(s.id) AS num,
                   $age_grp_case AS age_group,".self::SEX_CASE." AS sex,
                   currentRegimenID AS regimen,
                   reg_t.treatmentStatusID AS reg_line,
                   $reg_time_case AS reg_time,
                   treatmentInitiationID AS trt,
                   count(distinct patientUniqueID) as number_patients_received   
		        FROM vl_samples AS s
		        LEFT JOIN vl_patients AS p ON s.patientID=p.id
            LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
		        WHERE YEAR(s.created)='$year' AND $cond		  
		        GROUP BY mth,age_group,facilityID,sex,regimen,reg_line,reg_time,trt";

		  $res=\DB::connection('live_db')->select($sql);
      if($cond==1) return $res;
      $ret=[];
      foreach ($res as $r) {
        $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
        $k.=$r->regimen.$r->reg_line.$r->reg_time.$r->trt;
        $ret[$k]=$r->num;
      }
      return $ret; 
    }

    public static function getNumberOfPatients($year,$cond=1){
      $age_grp_case=self::ageGroupCase();
      #$reg_type_case=self::regimenTypeCase();
      $reg_time_case=self::regimenTimeCase();
      $sql="SELECT facilityID,month(s.created) AS mth,count(s.id) AS num,
                   $age_grp_case AS age_group,".self::SEX_CASE." AS sex,
                   currentRegimenID AS regimen,
                   reg_t.treatmentStatusID AS reg_line,
                   $reg_time_case AS reg_time,
                   treatmentInitiationID AS trt,
                   count(distinct patientUniqueID) as numberOfPatientsTested    
            FROM vl_samples AS s
            LEFT JOIN vl_patients AS p ON s.patientID=p.id
            LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
            WHERE YEAR(s.created)='$year' AND $cond     
            GROUP BY mth,age_group,facilityID,sex,regimen,reg_line,reg_time,trt";

      $res=\DB::connection('live_db')->select($sql);
      if($cond==1){
        return $res;
      } 
        
      $ret=[];
      foreach ($res as $r) {
        $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
        $k.=$r->regimen.$r->reg_line.$r->reg_time.$r->trt;
        $ret[$k]=$r->numberOfPatientsTested;
      }
       
      return $ret; 
    }

    public static function getRejects($year){
        $age_grp_case=self::ageGroupCase();
        //$reg_type_case=self::regimenTypeCase();
        $reg_time_case=self::regimenTimeCase();
        $sql="SELECT facilityID,month(s.created) AS mth,count(v.id) AS num,$age_grp_case AS age_group,
                     ".self::SEX_CASE." AS sex,
                     currentRegimenID AS regimen,
                     reg_t.treatmentStatusID AS reg_line,
                     $reg_time_case AS reg_time,
                     treatmentInitiationID AS trt 
          FROM vl_samples_verify AS v
          LEFT JOIN vl_samples AS s ON v.sampleID=s.id 
          LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
          LEFT JOIN vl_patients AS p ON s.patientID=p.id
          WHERE YEAR(s.created)='$year' AND outcome='Rejected'
          GROUP BY mth,age_group,facilityID,sex,regimen,reg_line,reg_time,trt
          ";
        $res=\DB::connection('live_db')->select($sql);
        $ret=[];
        foreach ($res as $r) {
          $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
          $k.=$r->regimen.$r->reg_line.$r->reg_time.$r->trt;
          $ret[$k]=$r->num;
        }
        return $ret;
    }

    public static function getRejects2($year){
        $rjctn_rsn_case=self::rjctnRsnCase();
        $age_grp_case=self::ageGroupCase();
        //$reg_type_case=self::regimenTypeCase();
        $reg_time_case=self::regimenTimeCase();
        $sql="SELECT facilityID,month(s.created) AS mth,count(v.id) AS num,
                     $age_grp_case AS age_group ,$rjctn_rsn_case AS rjctn_rsn,
                     ".self::SEX_CASE." AS sex,                     
                     currentRegimenID AS regimen,
                     reg_t.treatmentStatusID AS reg_line,
                     $reg_time_case AS reg_time,
                     treatmentInitiationID AS trt 
              FROM vl_samples_verify AS v
              LEFT JOIN vl_samples AS s ON v.sampleID=s.id 
              LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
              LEFT JOIN vl_patients AS p ON s.patientID=p.id
              WHERE YEAR(s.created)='$year' AND outcome='Rejected'
              GROUP BY rjctn_rsn,mth,age_group,facilityID,sex,regimen,reg_line,reg_time,trt
              ";
        $res=\DB::connection('live_db')->select($sql);
        $ret=[];
        foreach ($res as $r) {
          $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
          $k.=$r->regimen.$r->reg_line.$r->reg_time.$r->trt.$r->rjctn_rsn;
          $ret[$k]=$r->num;
        }
        return $ret;
    }


    public static function getResults($year,$cond="1"){
        $age_grp_case=self::ageGroupCase();
        //$reg_type_case=self::regimenTypeCase();
        $reg_time_case=self::regimenTimeCase();
        $sql="SELECT facilityID,month(s.created) AS mth,count(DISTINCT r.vlSampleID) AS num,$age_grp_case AS age_group,
                      ".self::SEX_CASE." AS sex,
                      currentRegimenID AS regimen,
                      reg_t.treatmentStatusID AS reg_line,
                      $reg_time_case AS reg_time,
                      treatmentInitiationID AS trt 
              FROM vl_results_merged AS r
              LEFT JOIN vl_samples AS s ON r.vlSampleID=s.vlSampleID
              LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
              LEFT JOIN vl_patients AS p ON s.patientID=p.id
              WHERE YEAR(s.created)='$year' AND $cond
              GROUP BY mth,age_group,facilityID,sex,regimen,reg_line,reg_time,trt
              ";
        
        $res=\DB::connection('live_db')->select($sql);
        $ret=[];
        foreach ($res as $r) {
          $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
          $k.=$r->regimen.$r->reg_line.$r->reg_time.$r->trt;
          $ret[$k]=$r->num;
        }
        return $ret;
    }


    private static function ageGroupCase(){
      //31536000 is the number of seconds in a year of 365 days
       $age=" ROUND((UNIX_TIMESTAMP(s.created)-UNIX_TIMESTAMP(dateOfBirth))/31536000) ";
       $arr=[];
        for ($index=1; $index < 100; $index ++) { 
            $from_age = $index - 1;
            $to_age = $index;
            $arr[$index]="$age >=$from_age && $age < $to_age";

        }
    
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
      /* $arr=[
              1=>"currentRegimenID in (1,2,13,16,21,22,27,28)",
              2=>"currentRegimenID in (4,6)",
              3=>"currentRegimenID in (3,5)",
              4=>"currentRegimenID in (7,8,17,18,23,24,29,30)",
              5=>"currentRegimenID in (11,12,25,26)",
              6=>"currentRegimenID in (14,15)",
              7=>"currentRegimenID in (19,20,31,71)"
            ];*/
      //{1: 'AZT based', 2: 'ABC based', 3: 'TDF based', 4: 'Other'}

       $arr=[
              1=>"currentRegimenID in (1,2,13,16,21,22,27,28)",
              2=>"currentRegimenID in (7,8,17,18,23,24,29,30)",
              3=>"currentRegimenID in (4,6,3,5,11,12,25,26,14,15)",
              4=>"currentRegimenID in (19,20,31,71)"
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
