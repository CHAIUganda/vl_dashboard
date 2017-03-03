<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\WorksheetResults;
use Log;

class MonitoringController extends Controller {

	public function getIndex(){
		$sect = 'admin';
		return view('results.facility_list', compact('sect'));
	}

	public function getData(){
		$facilities = WorksheetResults::getFacilityList();
		return \Datatables::of($facilities)->make(true);
	}
	
}