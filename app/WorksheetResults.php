<?php

namespace EID;

use Illuminate\Database\Eloquent\Model;

class WorksheetResults extends Model
{
    //
    protected $connection = 'live_db';


    public static function getWorksheetList($tab, $data_qc='no'){
      if($data_qc=='yes'){
         $ret = self::leftjoin('vl_users AS u', 'u.email', '=', 'w.createdby')
            ->select('w.id','worksheetReferenceNumber', 'w.created', 'u.names AS createdby')
            ->from('vl_samples_worksheetcredentials AS w');
      }else{
        $ret = self::leftjoin('vl_results_roche AS r', 'r.worksheetID', '=', 'w.id')
            ->leftjoin('vl_results_abbott AS a', 'a.worksheetID', '=', 'w.id')
            ->leftjoin('vl_users AS u', 'u.email', '=', 'w.createdby')
            ->select('w.id','worksheetReferenceNumber', 'w.created', 'u.names AS createdby', 'w.stage', \DB::raw(self::fail_case()))
            ->from('vl_samples_worksheetcredentials AS w');

        $ret = $ret->where(function($query){
                $qc_date = env('QC_START_DATE','2017-03-02');
                $query->where('r.created','>=',$qc_date)->orWhere('a.created','>=',$qc_date);
              }); 
      }
      
      if($tab == 'released'){
        $ret = $ret->where('stage', '=', 'passed_lab_qc');
      }elseif($tab == 'abbott' || $tab == 'roche'){
        $stg = ($data_qc=='yes')?'passed_lab_qc':'has_results';
        $ret = $ret->where('stage', '=', $stg)->where('machineType', '=', $tab);
      }elseif($tab == 'passed_data_qc'){
        $ret = $ret->where('stage', '=', 'passed_data_qc');
      }

      return $ret->groupby('w.id')->orderby('w.id', 'DESC');
    }   

    public static function worksheetSamples($id){
      $ret = self::leftjoin('vl_samples AS s', 's.id', '=', 'sampleID')
                ->leftjoin('vl_patients As p', 'p.id', '=', 'patientID')
                ->leftjoin('vl_facilities AS f', 'f.id', '=', 's.facilityID')
                ->leftjoin('vl_hubs AS h', 'h.id', '=', 'f.hubID')
                ->leftjoin('vl_results_abbott AS res_a', 'res_a.SampleID', '=', 's.vlSampleID')
                ->leftjoin('vl_results_roche AS res_r', 'res_r.SampleID', '=', 's.vlSampleID')
                ->leftjoin('vl_results_multiplicationfactor AS fctr', 'fctr.worksheetID', '=', 'wk.worksheetID')
                ->select('s.*','wk.sampleID', 'hub', 'facility', 'p.*', 'res_a.result AS abbott_result','res_a.flags', 
                         'res_r.Result AS roche_result', 'factor', 'res_a.created AS abbott_date', 'res_r.created AS roche_date')
                ->from('vl_samples_worksheet AS wk')
                ->where('wk.worksheetID','=',$id)
                ->get();
      return $ret;
    }

    public static function getWorksheet($id){
       $wk = self::select("*")->from("vl_samples_worksheetcredentials")->where('id','=',$id)->limit(1)->get();
       return $wk[0];

    }

    public static function getFacilityList(){
      $stats = "SUM(CASE WHEN p.printed = 'NO' AND p.downloaded = 'NO' THEN 1 ELSE 0 END) AS num_pending,
                SUM(CASE WHEN p.printed = 'YES' THEN 1 ELSE 0 END) AS num_printed,
                SUM(CASE WHEN p.downloaded = 'YES' THEN 1 ELSE 0 END) AS num_downloaded,
                MAX(printed_at) AS printed_at
                ";
      $res = LiveData::leftjoin('vl_samples AS s', 's.id', '=', 'p.sample_id')
                    ->leftjoin('vl_facilities AS f', 'f.id', '=', 's.facilityID')
                    ->leftjoin('vl_hubs AS h', 'h.id', '=', 'f.hubID')                
                    ->select('f.*', 'hub', \DB::raw($stats))
                    ->from('vl_facility_printing AS p');
      if(!empty(\Auth::user()->hub_id)){
        $res = $res->where('f.hubID', \Auth::user()->hub_id);
      }elseif(!empty(\Auth::user()->facility_id)){
        $res = $res->where('f.id', '=', \Auth::user()->facility_id);
      }
      return $res->groupby('f.id')->orderby('num_pending', 'DESC');
    }


    private static function fail_case(){
      $abbott_result_fails = "('-1.00',
               '3153 There is insufficient volume in the vessel to perform an aspirate or dispense operation.',
               '3109 A no liquid detected error was encountered by the Liquid Handler.',
               'A no liquid detected error was encountered by the Liquid Handler.',
               'Unable to process result, instrument response is invalid.',
               '3118 A clot limit passed error was encountered by the Liquid Handler.',
               '3119 A no clot exit detected error was encountered by the Liquid Handler.',
               '3130 A less liquid than expected error was encountered by the Liquid Handler.',
               '3131 A more liquid than expected error was encountered by the Liquid Handler.',
               '3152 The specified submerge position for the requested liquid volume exceeds the calibrated Z bottom',
               '4455 Unable to process result, instrument response is invalid.',
               'A no liquid detected error was encountered by the Liquid Handler.',
               'Failed          Internal control cycle number is too high. Valid range is [18.48, 22.48].',
               'Failed          Failed            Internal control cycle number is too high. Valid range is [18.48,',
               'Failed          Failed          Internal control cycle number is too high. Valid range is [18.48, 2',
               'OPEN',
               'There is insufficient volume in the vessel to perform an aspirate or dispense operation.',
               'Unable to process result, instrument response is invalid.')";
      $abbott_flags = 
        "('4442 Internal control cycle number is too high.',
                 '4450 Normalized fluorescence too low.',
                 '4447 Insufficient level of Assay reference dye.',
                 '4457 Internal control failed.')";

       $abott_fail = " (a.result IN $abbott_result_fails OR a.flags IN $abbott_flags) ";
      
      return "SUM(CASE WHEN 
                  (r.`result`='Failed' OR r.`result`='Invalid') OR $abott_fail
                  THEN 1 ELSE 0 END) AS num_failed";
     
    }


   

}
