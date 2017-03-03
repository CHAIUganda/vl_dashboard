<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\WorksheetResults;
use Log;

class FacilityListController extends Controller {

	public function getIndex(){
		$sect = 'results';
		return view('results.facility_list', compact('sect'));
	}

	public function getData(){
		$facilities = WorksheetResults::getFacilityList();
		return \Datatables::of($facilities)
				->addColumn('action', function($result){
					$url = "/results_list?f=$result->id";
					return "<a class='btn btn-danger btn-xs' href='$url'>view pending</a>
							<a class='btn btn-danger btn-xs' href='$url&printed=YES'>printed/downloaded</a>";
				})->make(true);
	}
	
}