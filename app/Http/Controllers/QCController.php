<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\LiveData;
use EID\WorksheetResults;

class QCController extends Controller {

	public function getIndex(){
		$tab=\Request::get("tab");
		$tab=empty($tab)?'roche':$tab;
		return view('qc.index', compact('tab'));
	}

	public function getData(){
		$tab=\Request::get("tab");
		$tab=empty($tab)?'roche':$tab;

		$results = WorksheetResults::getWorksheetList($tab, 'yes');
		return \Datatables::of($results)
				->addColumn('worksheetReferenceNumber', function($result){
					return "<a href='/data_qc/$result->id'>$result->worksheetReferenceNumber</a>";
				})
				->make(true);
	}

	/*public function index(){
		$hubs = LiveData::getHubs();
		$facilities = LiveData::getFacilities();
		$hubs = \MyHTML::get_arr_pair($hubs, 'hub');
		$facilities = \MyHTML::get_arr_pair($facilities, 'facility');

		return view('qc.index', compact('hubs', 'facilities'));
	}*/

	public function worksheet_search($q){
		$worksheets = LiveData::searchWorksheet($q);
		$ret = "";
		foreach ($worksheets as $wk) {
			$ret .= "<a href='/qc/$wk->id/'>$wk->worksheetReferenceNumber</a><br>";			
		}
		return $ret;
	}

	public function data_qc($id){
		$now = date("Y-m-d H:i:s");
		$qc_by = \Auth::user()->email;
		if(\Request::has('choices')){
			$samples = \Request::get('choices');
			$comments = \Request::get('comments');
			$sql = "INSERT INTO vl_facility_printing (sample_id, ready, comments, qc_at, qc_by) VALUES ";
			foreach ($samples as $sample_id => $choice) {
				$ready = $choice == 'approved'?'YES':'NO';
				$comment = $choice == 'approved'?'':$comments[$sample_id];

				$sql .= "($sample_id,'$ready', '$comment', '$now', '$qc_by'),";				
			}

			$sql = trim($sql, ",");
			\DB::connection('live_db')->unprepared($sql);
			$sql2 = "UPDATE vl_samples_worksheetcredentials SET `stage` = 'passed_data_qc' WHERE id = ".\Request::get('worksheet_id');
			\DB::connection('live_db')->unprepared($sql2);
			return redirect("/qc/");
		}
		$samples = LiveData::worksheetSamples($id);
		$wk = LiveData::select("*")->from("vl_samples_worksheetcredentials")->where('id','=',$id)->limit(1)->get();
		$wk = $wk[0];

		return view('qc.qc', compact('samples', 'id', 'wk'));
	}

	public function sample($id){
		return LiveData::getSample($id);
	}

	public function byhub($id){
		$worksheets = LiveData::wkshtby(" f.hubID = $id");
		$ret = "";
		foreach ($worksheets as $wk) {
			$ret .= "<a href='/qc/$wk->id/'>$wk->worksheetReferenceNumber</a><br>";			
		}
		return $ret;
	}

	public function byfacility($id){
		$worksheets = LiveData::wkshtby(" f.id = $id");
		$ret = "";
		foreach ($worksheets as $wk) {
			$ret .= "<a href='/qc/$wk->id/'>$wk->worksheetReferenceNumber</a><br>";			
		}
		return $ret;
	}


}