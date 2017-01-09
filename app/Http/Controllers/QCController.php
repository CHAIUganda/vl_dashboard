<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\LiveData;

class QCController extends Controller {

	public function index(){
		return view('qc.index');
	}

	public function worksheet_search($q){
		$worksheets = LiveData::searchWorksheet($q);
		$ret = "";
		foreach ($worksheets as $wk) {
			$ret .= "<a href='/qc/$wk->id/'>$wk->worksheetReferenceNumber</a><br>";			
		}
		return $ret;
	}

	public function qc($id){
		$now = date("Y-m-d H:i:s");
		$qc_by = \Auth::user()->email;
		if(\Request::has('samples')){
			$samples = \Request::get('samples');
			$sql = "INSERT INTO vl_facility_printing (sample_id, qc_at, qc_by) VALUES ";
			foreach ($samples as $sample_id) {
				$sql .= "($sample_id, '$now', '$qc_by'),";				
			}

			$sql = trim($sql, ",");
			\DB::connection('live_db')->unprepared($sql);
			redirect("/qc/$id/");
		}
		$samples = LiveData::worksheetSamples($id);
		$wk = LiveData::select("*")->from("vl_samples_worksheetcredentials")->where('id','=',$id)->limit(1)->get();
		$wk = $wk[0];
		return view('qc.qc', compact('samples', 'id', 'wk'));
	}

	public function sample($id){
		return LiveData::getSample($id);
	}


}