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
					$tab = \Request::get("tab");

					return $tab=='released'?$result->worksheetReferenceNumber:"<a href='/lab_qc/qc/$result->id'>$result->worksheetReferenceNumber</a>";
				})
				->make(true);
	}

	public function qc($id){
		if(\Request::has('choices')){
			$worksheet_id = \Request::get('worksheet_id');
			$choices = \Request::get('choices');
			$results = \Request::get('pat_results');
			$suppressions = \Request::get('suppressions');
			$test_date = \Request::get('test_date');
			$now = date("Y-m-d H:i:s");
			$createdby = \Auth::user()->name;
			$sql = "INSERT INTO vl_results_released (
					worksheet_id, sample_id, result, suppressed, test_date, created, createdby) 
					VALUES ";
			$sql1 = "INSERT INTO vl_logs_samplerepeats (sampleID, oldWorksheetID, created, createdby) VALUES ";
			$passes = 0;$reschedules = 0;
			$sample_arr = array_keys($choices);
			$check_samples = implode(",", $sample_arr);
			\DB::connection('live_db')->unprepared("DELETE FROM vl_results_released WHERE sample_id IN ($check_samples)");
			foreach ($choices as $sample_id => $choice ) {
				$result = $choice=='invalid'?'Failed':$results[$sample_id];
				$suppressed = $choice=='invalid'?'UNKNOWN':$suppressions[$sample_id];
				if($choice == 'release' || $choice == 'invalid'){
					 $sql .= "($worksheet_id, $sample_id, '$result', '$suppressed', '$test_date', '$now', '$createdby'),";
					 $passes++;
				}else if($choice == 'reschedule'){
					$sql1 .= "($sample_id, $worksheet_id, '$now', '$createdby'),";
					$reschedules++;
				}			
			}

			$sql = trim($sql, ",");
			$sql1 = trim($sql1, ",");
	
			if($passes>0) \DB::connection('live_db')->unprepared($sql);
			if($reschedules>0) \DB::connection('live_db')->unprepared($sql1);
			$sql2 = "UPDATE vl_samples_worksheetcredentials SET `stage` = 'passed_lab_qc' WHERE id = $worksheet_id";
			\DB::connection('live_db')->unprepared($sql2);
			return redirect("/lab_qc/index/");
		}
		$samples = WorksheetResults::worksheetSamples($id);
		$wk = WorksheetResults::getWorksheet($id);
		return view('lab_qc.qc', compact('samples','id', 'wk'));
	}

}