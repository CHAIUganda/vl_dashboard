<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\WorksheetResults;


class LabQCController extends Controller {

	public function getIndex(){
		$tab=\Request::get("tab");
		$tab=empty($tab)?'roche':$tab;
		return view('lab_qc.index', compact('tab'));
	}

	public function getData(){
		$tab=\Request::get("tab");
		$tab=empty($tab)?'roche':$tab;

		$results = WorksheetResults::getWorksheetList($tab);
		return \Datatables::of($results)
				->addColumn('worksheetReferenceNumber', function($result){
					return "<a href='/lab_qc/qc/$result->id'>$result->worksheetReferenceNumber</a>";
				})
				->make(true);
	}

	public function qc($id){
		if(\Request::has('choices')){
			$worksheet_id = \Request::get('worksheet_id');
			$choices = \Request::get('choices');
			$results = \Request::get('pat_results');
			$suppressions = \Request::get('suppressions');
			$now = date("Y-m-d H:i:s");
			$createdby = \Auth::user()->email;
			$sql = "INSERT INTO vl_results_released (
					worksheet_id, sample_id, result, suppressed, created, createdby) 
					VALUES ";
			$sql1 = "INSERT INTO vl_logs_samplerepeats (sampleID, oldWorksheetID, created, createdby) VALUES ";
			foreach ($choices as $sample_id => $choice ) {
				$result = $choice=='invalid'?'Failed':$results[$sample_id];
				$suppressed = $choice=='invalid'?'UNKNOWN':$suppressions[$sample_id];
				if($choice == 'release' || $choice == 'invalid'){
					 $sql .= "($worksheet_id, $sample_id, '$result', '$suppressed', '$now', '$createdby'),";
				}else if($choice == 'reschedule'){
					$sql1 .= "($sample_id, $worksheet_id, '$now', '$createdby'),";
				}			
			}

			$sql = trim($sql, ",");
			$sql1 = trim($sql1, ",");
			\DB::connection('live_db')->unprepared($sql);
			\DB::connection('live_db')->unprepared($sql2);
			$sql2 = "UPDATE vl_samples_worksheetcredentials SET released = 'YES' WHERE id = $worksheet_id";
			\DB::connection('live_db')->unprepared($sql2);
			return redirect("/lab_qc/index/");
		}
		$samples = WorksheetResults::worksheetSamples($id);
		$wk = WorksheetResults::getWorksheet($id);
		return view('lab_qc.qc', compact('samples','id', 'wk'));
	}

}