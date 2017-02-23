<?php

namespace EID;

use Illuminate\Database\Eloquent\Model;

class WorksheetResults extends Model
{
    //
    protected $connection = 'live_db';

    public static function getWorksheetList($tab){
      if($tab=='released'){
        return self::select('w.id','worksheetReferenceNumber', 'w.created', 'w.createdby')
                    ->from('vl_samples_worksheetcredentials AS w')->where('released','=','YES');
      }
      $ret = self::leftjoin('vl_samples_worksheetcredentials AS w', 'w.id', '=', 'r.worksheetID')
                  ->select('w.id','worksheetID','worksheetReferenceNumber', 'w.created', 'w.createdby');
      if($tab=='roche'){
        $ret = $ret->from('vl_results_roche AS r');
      }elseif($tab=='abbott'){
         $ret = $ret->from('vl_results_abbott AS r');
      }

      return $ret->where('released','<>','YES')->groupby('w.id')->orderby('w.id', 'DESC');
    }   

    public static function worksheetSamples($id){
      $ret = self::leftjoin('vl_samples AS s', 's.id', '=', 'sampleID')
                ->leftjoin('vl_patients As p', 'p.id', '=', 'patientID')
                ->leftjoin('vl_facilities AS f', 'f.id', '=', 's.facilityID')
                ->leftjoin('vl_hubs AS h', 'h.id', '=', 'f.hubID')
                ->leftjoin('vl_results_abbott AS res_a', 'res_a.SampleID', '=', 's.vlSampleID')
                ->leftjoin('vl_results_roche AS res_r', 'res_r.SampleID', '=', 's.vlSampleID')
                ->leftjoin('vl_results_multiplicationfactor AS fctr', 'fctr.worksheetID', '=', 'wk.worksheetID')
                ->select('s.*','wk.sampleID', 'hub', 'facility', 'p.*', 'res_a.result AS abbott_result', 'res_a.flags', 'res_r.Result AS roche_result', 'factor')
                ->from('vl_samples_worksheet AS wk')
                ->where('wk.worksheetID','=',$id)
                ->get();
      return $ret;
    }

    public static function getWorksheet($id){
       $wk = self::select("*")->from("vl_samples_worksheetcredentials")->where('id','=',$id)->limit(1)->get();
       return $wk[0];

    }


   

}
