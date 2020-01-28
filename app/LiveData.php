<?php

namespace EID;

use Illuminate\Database\Eloquent\Model;

class LiveData extends Model
{
    //
    protected $connection = 'live_db';

    const SEX_CASE = "CASE WHEN `gender`='Female' THEN 'f' WHEN `gender`='Male' THEN 'm' ELSE 'x' END";
    //const SEX_CASE = "CASE WHEN `p.gender`='Female' THEN 'f' WHEN `p.gender`='Male' THEN 'm' ELSE 'x' END";
    const PREGNANT_CASE = "CASE WHEN pregnant='Yes' THEN 'y' WHEN pregnant='No' THEN 'n' ELSE 'x' END";
    const BREAST_FEEDING_CASE="CASE WHEN breastfeeding='Yes' THEN 'y' WHEN breastfeeding='No' THEN 'n' ELSE 'x' END";
    const TB_STATUS_CASE="CASE WHEN activeTBStatus='Yes' THEN 'y' WHEN activeTBStatus='No' THEN 'n' ELSE 'x' END";
    const VALID_RESULTS_TABLE="SELECT id,vlSampleID,
      case
        when resultAlphanumeric in ('Failed','Failed.',
            'Invalid',
            'Invalid test result. There is insufficient sample to repeat the assay.',
            'There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a a new sample.',
            'There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a new sample.'
 )  then 'invalid' else 'valid' end as validity FROM vl_results_merged";


  const RESULT_VALIDITY_CASE ="case when resultAlphanumeric in ('Failed','Failed.',
                'Invalid',
                'Invalid test result. There is insufficient sample to repeat the assay.',
                'There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a a new sample.',
                'There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a new sample.'
              )  then 'invalid' else 'valid' end";
    //const TRTMT_IDCTN_CASE = "CASE WHEN `treatmentInitiationID`=1 THEN 'b_plus' WHEN `treatmentInitiationID`=4 THEN 'tb' ELSE 'x' END";

    
    public static function getSample($id){
      return 'x';
    }

    public static function getFacilityName($id){
      $r = LiveData::select('facility')->from('vl_facilities')->where('id','=', $id)->limit(1)->get();
      return (count($r)>0)?$r[0]->facility:"";
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

    public static function getResultsList($printed='', $count=0){
      $ret = LiveData::leftjoin('vl_samples AS s', 's.id', '=', 'pr.sample_id')
                      ->leftjoin('vl_patients As p', 'p.id', '=', 'patientID')
                      ->leftjoin('vl_facilities AS f', 'f.id', '=', 's.facilityID')
                      ->leftjoin('vl_hubs AS h', 'h.id', '=', 'f.hubID')
                      ->select('pr.sample_id','formNumber','collectionDate', 'receiptDate', 'hub', 'facility', 
                               'artNumber', 'otherID', 'qc_at','printed','printed_at','printed_by')
                      ->from('vl_facility_printing AS pr');
      $count_cond = ' ';
      if($printed=='NO'){
        //this to be interpreted as pending
        $ret = $ret->where('printed','=','NO')->where('downloaded','=','NO')->where('ready', '=', 'YES');
        $count_cond = "WHERE printed = 'NO' AND downloaded='NO' AND ready = 'YES'";
      }elseif($printed=='YES'){
        //this to be interpreted as printed or downloaded
        $ret = $ret->where(function($query){
                    $query->where('printed','=','YES')->orWhere('downloaded','=','YES');
              }); 
        $count_cond = "WHERE (printed = 'YES' OR downloaded='YES')";
      }

      $hub_id = \Auth::user()->hub_id;
      $facility_id = \Auth::user()->facility_id;
       if(\Request::has('f')){
        $xxxf = \Request::get('f');
         $ret = $ret->where('f.id','=', $xxxf);
         $count_cond .= " AND f.id=$xxxf";
      }elseif(!empty($hub_id)){
        $ret = $ret->where('f.hubID', $hub_id);
        $count_cond .= " AND f.hubID=$hub_id";
      }elseif(!empty($facility_id)){
         $ret = $ret->where('f.id', $facility_id);
         $count_cond .=" AND f.id=$facility_id";
      }

      $count_sql = "SELECT count(pr.id) AS num 
                    FROM vl_facility_printing AS pr
                    LEFT JOIN vl_samples AS s ON pr.sample_id=s.id
                    LEFT JOIN vl_facilities AS f ON s.facilityID=f.id
                    $count_cond";

      if($count==1){
        $count_arr = \DB::connection('live_db')->select($count_sql);
        return $count_arr[0]->num;
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
                      ->select('s.*','rr.sample_id', 'hub', 'facility', 'p.*', 'fp.id As fp_id','fp.ready', 'result', 'd.district')
                      ->from('vl_results_released AS rr')
                      ->where('rr.worksheet_id','=',$id)
                      ->orderby('lrEnvelopeNumber', 'ASC')
                      ->orderby('lrNumericID', 'ASC')
                      ->get();
      return $ret;
    }

    public static function getHubs(){
      $sql = "select id,hub from backend_hubs";
      return $res=\DB::connection('direct_db')->select($sql);
    	//return LiveData::select('id','hub')->from('vl_hubs')->get();
    }

    public static function getDistricts(){
      $sql = "select id,district,dhis2_uid from backend_districts";
      return $res=\DB::connection('direct_db')->select($sql);
      //return LiveData::select('id','district','dhis2_name')->from('vl_districts')->get();
    }

    public static function getFacilities(){
      
      $sql = "select id,facility,district_id,hub_id,dhis2_name,dhis2_uid from backend_facilities";
      return $res=\DB::connection('direct_db')->select($sql);
      //return LiveData::select('id','facility','dhis2_name','ipID','hubID','districtID','dhis2_uid','district_uid')->from('vl_facilities')->get();
    }
    public static function getFacilitiesInAnArrayForm(){
      $result_set = LiveData::select('id','facility','dhis2_name','ipID','hubID','districtID','dhis2_uid','district_uid')->from('vl_facilities')->get();
      $ret=[];
      foreach ($result_set as $key => $row) {
     
       $ret[$row->id] = array(
                        'id' => $row->id,
                        'facility'=>$row->facility, 
                        'dhis2_name'=>$row->dhis2_name,
                        'dhis2_uid'=>$row->dhis2_uid,
                        'district_uid'=>$row->district_uid
                      );
      }
      return $ret;
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

    public static function getRegimensInAnArrayForm(){
      $result_set = self::select('id', 'appendix')->from('vl_appendix_regimen')->get();

       foreach ($result_set as $key => $row) {
     
       $ret[$row->id] = array(
                        'id' => $row->id,
                        'appendix'=>$row->appendix
                      );
      }
      return $ret;
    }

    public static function getRegimenLinesInAnArrayForm(){
      $result_set = self::select('id', 'appendix')->from('vl_appendix_treatmentstatus')->get();

       foreach ($result_set as $key => $row) {
     
       $ret[$row->id] = array(
                        'id' => $row->id,
                        'appendix'=>$row->appendix
                      );
      }
      return $ret;
    }
  
    public static function getTreatmentInitiationInAnArrayForm(){
      $result_set = self::select('id', 'appendix')->from('vl_appendix_treatmentinitiation')->get();

       foreach ($result_set as $key => $row) {
     
       $ret[$row->id] = array(
                        'id' => $row->id,
                        'appendix'=>$row->appendix
                      );
      }
      return $ret;
    }
    public static function getSampleTypesInArrayForm(){
      $result_set = self::select('id', 'appendix')->from('vl_appendix_sampletype')->get();

       foreach ($result_set as $key => $row) {
     
       $ret[$row->id] = array(
                        'id' => $row->id,
                        'appendix'=>$row->appendix
                      );
      }
      return $ret;
    }
    public static function getSamples($year){
    	$age_grp_case=self::ageGroupCase();
      
      #$reg_type_case=self::regimenTypeCase();
      $reg_time_case=self::regimenTimeCase();
    	$sql="SELECT facilityID,month(s.created) AS mth,r.validity,count(distinct s.vlSampleID) AS num,
                   count(distinct r.vlSampleID) AS samples_tested,r.resultNumeric,
                   $age_grp_case AS age_group,".self::SEX_CASE." AS sex,
                   currentRegimenID AS regimen,
                   reg_t.treatmentStatusID AS reg_line,
                   $reg_time_case AS reg_time,
                   treatmentInitiationID AS trt,
                   count(distinct patientUniqueID) as number_patients_received,
                   ".self::PREGNANT_CASE." as pregnancyStatus,count(pregnant) as numberPregant,
                   ".self::BREAST_FEEDING_CASE." as breastFeedingStatus, count(breastfeeding) as numberBreastFeeding,
                   ".self::TB_STATUS_CASE." as activeTBStatus,count(activeTBStatus) as numberActiveOnTB,
                   s.sampleTypeID
                
		        FROM 
            (SELECT distinct r.vlSampleID,r.resultNumeric,case when r.resultAlphanumeric in ('Failed','Failed.',
                'Invalid',
                'Invalid test result. There is insufficient sample to repeat the assay.',
                'There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a a new sample.',
                'There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a new sample.'
              )  then 'invalid' else 'valid' end as validity
           FROM vl_results_merged r inner join vl_samples s on s.vlSampleID=r.vlSampleID where YEAR(s.created)='$year' group by r.vlSampleID) AS r

            right JOIN vl_samples AS s ON r.vlSampleID=s.vlSampleID
		        LEFT JOIN vl_patients AS p ON s.patientID=p.id

            LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
		        WHERE YEAR(s.created)='$year' AND $cond		  
		        GROUP BY mth,age_group,facilityID,sex,regimen,reg_line,reg_time,trt";

		  $res=\DB::connection('live_db')->select($sql);
      /*if($cond==1) return $res;
      $ret=[];
      foreach ($res as $r) {
        $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
        $k.=$r->regimen.$r->reg_line.$r->reg_time.$r->trt;
        $ret[$k]=$r->num;
      }*/
      return $res; 
    }

    public static function getSamplesDataSetByMonth($year,$month){
      $age_grp_case=self::ageGroupCase();
      
      #$reg_type_case=self::regimenTypeCase();
      $reg_time_case=self::regimenTimeCase();
      $sql="SELECT facilityID,month(s.created) AS mth,year(s.created) AS year_created,r.validity,count(distinct s.vlSampleID) AS num,
                   count(distinct r.vlSampleID) AS samples_tested,r.suppressed as suppressed,
                   $age_grp_case AS age_group,".self::SEX_CASE." AS sex,
                   currentRegimenID AS regimen,
                   reg_t.treatmentStatusID AS reg_line,
                   $reg_time_case AS reg_time,
                   treatmentInitiationID AS trt,
                   count(distinct patientUniqueID) as number_patients_received,
                   ".self::PREGNANT_CASE." as pregnancyStatus,count(pregnant) as numberPregant,
                   ".self::BREAST_FEEDING_CASE." as breastFeedingStatus, count(breastfeeding) as numberBreastFeeding,
                   ".self::TB_STATUS_CASE." as activeTBStatus,count(activeTBStatus) as numberActiveOnTB,
                   s.sampleTypeID
                
            FROM 
            (SELECT distinct r.vlSampleID,r.suppressed,case when r.resultAlphanumeric in ('Failed','Failed.',
                'Invalid',
                'Invalid test result. There is insufficient sample to repeat the assay.',
                'There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a a new sample.',
                'There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a new sample.'
              )  then 'invalid' else 'valid' end as validity
           FROM vl_results_merged r inner join vl_samples s on s.vlSampleID=r.vlSampleID where YEAR(s.created)='$year' group by r.vlSampleID) AS r

            right JOIN vl_samples AS s ON r.vlSampleID=s.vlSampleID
            LEFT JOIN vl_patients AS p ON s.patientID=p.id

            LEFT JOIN vl_appendix_regimen AS reg_t ON s.currentRegimenID=reg_t.id
            WHERE YEAR(s.created)=$year and MONTH(s.created)=$month     
            GROUP BY mth,age_group,facilityID,sex,regimen,reg_line,reg_time,trt,suppressed";

      $res=\DB::connection('live_db')->select($sql);
      /*if($cond==1) return $res;
      $ret=[];
      foreach ($res as $r) {
        $k=$r->mth.$r->age_group.$r->facilityID.$r->sex;
        $k.=$r->regimen.$r->reg_line.$r->reg_time.$r->trt;
        $ret[$k]=$r->num;
      }*/
      return $res; 
    }
    public static function getSamplesRecords($year){
      $age_grp_case=self::ageGroupCase();
      $reg_time_case=self::regimenTimeCase();
      $rjctn_rsn_case=self::rjctnRsnCase();

      $sql="select Distinct s.vlSampleID,s.id,month(s.created) as monthOfYear,s.districtID,s.hubID,s.facilityID,
              TIMESTAMPDIFF(YEAR,p.dateOfBirth,s.created) as age,
                s.patientUniqueID,
              s.created,".self::SEX_CASE." AS sex,s.currentRegimenID,ts.position,s.pregnant,s.breastfeeding,
              s.activeTBStatus,s.sampleTypeID, $reg_time_case AS reg_time,s.treatmentInitiationID AS trt,
              results.vlSampleID as resultsSampleID, results.concatinated_results,
              
              $rjctn_rsn_case as rejectionReason

            from 
                vl_samples s left join (select id,vlSampleID, created,
                  GROUP_CONCAT(resultNumeric,':',".self::RESULT_VALIDITY_CASE.") AS concatinated_results
                  from vl_results_merged group by vlSampleID) results on s.vlSampleID=results.vlSampleID 
               left join (select distinct sampleID,id,outcomeReasonsID from vl_samples_verify) v on s.id=v.sampleID

                inner join vl_patients p on s.patientID =p.id 
                inner join vl_appendix_regimen r on s.currentRegimenID = r.id
                inner join vl_appendix_treatmentstatus ts on ts.id = r.treatmentStatusID
                
            where year(s.created)=$year";
      $results=\DB::connection('live_db')->select($sql);
      return $results;
    }
    public static function getSamplesRecordsByMonth($year,$month){
      $age_grp_case=self::ageGroupCase();
      $reg_time_case=self::regimenTimeCase();
      $rjctn_rsn_case=self::rjctnRsnCase();

      $sql="select Distinct s.vlSampleID,s.id,month(s.created) as monthOfYear,s.districtID,s.hubID,s.facilityID,
              TIMESTAMPDIFF(YEAR,p.dateOfBirth,s.created) as age,
                s.patientUniqueID,
              s.created,".self::SEX_CASE." AS sex,s.currentRegimenID,ts.position,s.pregnant,s.breastfeeding,
              s.activeTBStatus,s.sampleTypeID, $reg_time_case AS reg_time,s.treatmentInitiationID AS trt,
              results.vlSampleID as resultsSampleID, results.concatinated_results,
              
              $rjctn_rsn_case as rejectionReason

            from 
                vl_samples s left join (select id,vlSampleID, created,
                  GROUP_CONCAT(resultNumeric,':',".self::RESULT_VALIDITY_CASE.") AS concatinated_results
                  from vl_results_merged group by vlSampleID) results on s.vlSampleID=results.vlSampleID 
               left join (select distinct sampleID,id,outcomeReasonsID from vl_samples_verify) v on s.id=v.sampleID

                inner join vl_patients p on s.patientID =p.id 
                inner join vl_appendix_regimen r on s.currentRegimenID = r.id
                inner join vl_appendix_treatmentstatus ts on ts.id = r.treatmentStatusID
                
            where year(s.created)=$year and MONTH(s.created)=$month";
      $results=\DB::connection('live_db')->select($sql);
      return $results;
    }
    public static function getSamplesRecordsByMonthFromExternalSources($year,$month,$externalSource){
      $age_grp_case=self::ageGroupCase();
      $reg_time_case=self::regimenTimeCase();
      $rjctn_rsn_case=self::rjctnRsnCase();

      $sql="select Distinct s.vlSampleID,s.id,month(s.created) as monthOfYear,s.districtID,s.hubID,s.facilityID,
              TIMESTAMPDIFF(YEAR,p.dateOfBirth,s.created) as age,
                s.patientUniqueID,
              s.created,".self::SEX_CASE." AS sex,s.currentRegimenID,ts.position,s.pregnant,s.breastfeeding,
              s.activeTBStatus,s.sampleTypeID, $reg_time_case AS reg_time,s.treatmentInitiationID AS trt,
              results.vlSampleID as resultsSampleID, results.concatinated_results,
              
              $rjctn_rsn_case as rejectionReason

            from 
                vl_samples s left join (select id,vlSampleID, created,
                  GROUP_CONCAT(resultNumeric,':',".self::RESULT_VALIDITY_CASE.") AS concatinated_results
                  from vl_results_merged group by vlSampleID) results on s.vlSampleID=results.vlSampleID 
               left join (select distinct sampleID,id,outcomeReasonsID from vl_samples_verify) v on s.id=v.sampleID

                inner join vl_patients p on s.patientID =p.id 
                inner join vl_appendix_regimen r on s.currentRegimenID = r.id
                inner join vl_appendix_treatmentstatus ts on ts.id = r.treatmentStatusID
                
            where year(s.created)=$year and MONTH(s.created)=$month and s.createdby like '$externalSource%'";
      $results=\DB::connection('live_db')->select($sql);
      
      return $results;
    }
    public static function getPatientSamplesRecords($patientUniqueID){ 
      $rjctn_rsn_case=self::rjctnRsnCase();

      $sql="select Distinct s.vlSampleID,s.id,s.districtID,s.hubID,s.facilityID,
                s.patientUniqueID,s.collectionDate,s.receiptDate,s.treatmentInitiationDate,s.currentRegimenID,ts.position,s.pregnant,s.breastfeeding,
              s.activeTBStatus,s.sampleTypeID,s.treatmentInitiationID AS trt,
              results.vlSampleID as resultsSampleID,r.appendix as regimen, results.resultNumeric,results.created AS date_tested

            from 
                vl_samples s left join (select id,vlSampleID, created,resultNumeric
                  from vl_results_merged group by vlSampleID) results on s.vlSampleID=results.vlSampleID 
               left join (select distinct sampleID,id,outcomeReasonsID from vl_samples_verify) v on s.id=v.sampleID

                inner join vl_patients p on s.patientID =p.id 
                inner join vl_appendix_regimen r on s.currentRegimenID = r.id
                inner join vl_appendix_treatmentstatus ts on ts.id = r.treatmentStatusID
                
            where s.patientUniqueID like '$patientUniqueID'";
      $results=\DB::connection('live_db')->select($sql);
      return $results;
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

    public static function getDataToAugmentSampleRecordsByMonth($year,$month){
      /*$sql="SELECT s.id,s.vlSampleID,s.collectionDate,s.receiptDate,s.created, 
            sr.created as date_tested, GROUP_CONCAT(sr.resultAlphanumeric separator ':') resultAlphanumeric 
            FROM vl_samples s left join vl_results_merged sr on s.vlSampleID = sr.vlSampleID 
            where YEAR(s.created)=$year and MONTH(s.created)= $month group by s.vlSampleID";*/
        
           $sql="select s.id,s.patient_unique_id,GROUP_CONCAT(pp.phone separator ',') contacts, 
              s.vl_sample_id,p.art_number, s.date_collected,s.date_received,s.created_at,
              GROUP_CONCAT(r.result_alphanumeric separator ':') result_alphanumeric,
              rr.appendix rejection_reason, rr.tag rejection_category

              from vl_samples s 
              left join vl_patients p on s.patient_id=p.id  
              left join vl_patient_phones pp on pp.patient_id = p.id 
              left join vl_results r on r.sample_id=s.id 
              left join vl_verifications v on v.sample_id=s.id 
              left join backend_appendices rr on rr.id = v.rejection_reason_id 
              where YEAR(s.created_at)=$year and MONTH(s.created_at)= $month group by s.vl_sample_id";

          
      $results=\DB::connection('direct_db')->select($sql);
      return $results;
    }
    public static function getDataToAugmentSampleRecordsByMonthWithLimits($year,$month,$firstRowIndex,$lastRowIndex){
      
           /*$rejectionReasonCase = self::rjctnRsnCase();
           $sql= "select sample.*,patient.* from (SELECT s.id sampleId,s.patientID,s.vlSampleID,
            s.collectionDate,s.receiptDate,s.created, sr.created as date_tested, 
            GROUP_CONCAT(sr.resultAlphanumeric separator ':') resultAlphanumeric ,
            outcomeReasonsID ,$rejectionReasonCase as rejectionCategory
            FROM vl_samples s 
            left join vl_results_merged sr on s.vlSampleID = sr.vlSampleID 
            left join vl_samples_verify r on s.vlSampleID= r.sampleID 
            where YEAR(s.created)=$year and MONTH(s.created)= $month group by s.vlSampleID) sample  
            left join (select p.id,p.artNumber,GROUP_CONCAT(c.phone separator ',') contacts from 
            vl_patients p left join vl_patients_phone c on p.id=c.patientID group by p.id) patient 
            on sample.patientID = patient.id LIMIT $firstRowIndex,$lastRowIndex";*/

            $sql="select s.id,s.patient_unique_id,GROUP_CONCAT(pp.phone separator ',') contacts, 
              s.vl_sample_id,p.art_number, s.date_collected,s.date_received,s.created_at,
              GROUP_CONCAT(r.result_alphanumeric separator ':') result_alphanumeric,
              rr.appendix rejection_reason, rr.tag rejection_category

              from vl_samples s 
              left join vl_patients p on s.patient_id=p.id  
              left join vl_patient_phones pp on pp.patient_id = p.id 
              left join vl_results r on r.sample_id=s.id 
              left join vl_verifications v on v.sample_id=s.id 
              left join backend_appendices rr on rr.id = v.rejection_reason_id 
              where YEAR(s.created_at)=$year and MONTH(s.created_at)= $month group by s.vl_sample_id 
              LIMIT $firstRowIndex,$lastRowIndex";

          
      $results=\DB::connection('direct_db')->select($sql);
      return $results;
    }
    public static function getCountOfDataToAugmentSampleRecordsByMonth($year,$month){

           $sql= "select count(*) samples_records from (select s.id,s.patient_unique_id,GROUP_CONCAT(pp.phone separator ',') contacts, 
              s.vl_sample_id,p.art_number, s.date_collected,s.date_received,s.created_at,
              GROUP_CONCAT(r.result_alphanumeric separator ':') result_alphanumeric,
              rr.appendix rejection_reason, rr.tag rejection_category

              from vl_samples s 
              left join vl_patients p on s.patient_id=p.id  
              left join vl_patient_phones pp on pp.patient_id = p.id 
              left join vl_results r on r.sample_id=s.id 
              left join vl_verifications v on v.sample_id=s.id 
              left join backend_appendices rr on rr.id = v.rejection_reason_id 
              where YEAR(s.created_at)=$year and MONTH(s.created_at)= $month group by s.vl_sample_id) records";
            
          
      $results=\DB::connection('direct_db')->select($sql);
      return $results[0]->samples_records;
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

    public static function getRejections(){

      $sql="SELECT id,appendix FROM vl_appendix_samplerejectionreason";
      $results=\DB::connection('live_db')->select($sql);
      return $results;
    }

    private static function ageGroupCase(){
      //31536000 is the number of seconds in a year of 365 days
       $age=" ROUND((UNIX_TIMESTAMP(s.created)-UNIX_TIMESTAMP(dateOfBirth))/31536000) ";
       $arr=[];
        for ($index=0; $index < 100; $index ++) { 
           /* //No age. Just get a descrete particular age e.g. 30 years. 
            $from_age = $index - 1;
            $to_age = $index;
            $arr[$index]="$age >=$from_age && $age < $to_age";
            */
            $age_value_for_this_index=$index;
            $arr[$index]="$age = $age_value_for_this_index";

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

    public static function regimenTimeInArrayForm(){

      $regimen_time[1]= array('id'=>1,'appendix' => '6 to 12 months');
      $regimen_time[2]= array('id'=>2,'appendix' => '12 to 24 months');
      $regimen_time[3]= array('id'=>3,'appendix' => '25 to 36 months');
      $regimen_time[4] = array('id'=>4,'appendix' => '37 to 60 months');
      $regimen_time[5] = array('id'=>5,'appendix' => 'More than 60 months');

      return $regimen_time;
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
