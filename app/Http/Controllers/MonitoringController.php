<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\LiveData;
use Log;

class MonitoringController extends Controller {

	public function getIndex(){
		return view('results.facility_list');
	}

	public function getData(){
		$stats = "SUM(CASE WHEN p.ready = 'YES' THEN 1 ELSE 0 END) AS num_pending,
				  SUM(CASE WHEN p.printed = 'YES' OR p.downloaded = 'YES' THEN 1 ELSE 0 END) AS num_printed";
		$facilities = LiveData::leftjoin('vl_hubs AS h', 'h.id', '=', 'f.hubID')
					 ->leftjoin('vl_samples AS s', 's.facilityID', '=', 'f.id')
					 ->leftjoin('vl_facility_printing AS p', 'p.sample_id', '=', 's.id')
					 ->select('f.*', 'hub', \DB::raw($stats))->from('vl_facilities AS f');
		if(!empty(\Auth::user()->hub_id)){
			$facilities = $facilities->where('f.hubID', \Auth::user()->hub_id);
		}elseif(!empty(\Auth::user()->facility_id)){
			$facilities = $facilities->where('f.id', \Auth::user()->facility_id);
		}
		$facilities = $facilities->groupby('f.id')->orderby('num_pending', 'DESC');


		return \Datatables::of($facilities)->make(true);
	}
	
}