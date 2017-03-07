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
		$facilities = WorksheetResults::getFacilityList('pending');
		return \Datatables::of($facilities)
				->addColumn('num_pending', function($result){
					return "<a href='/results_list?f=$result->id'> $result->num_pending</a>";
				})
				->addColumn('facility', function($result){
					return "<a href='/results_list?f=$result->id'> $result->facility</a>";
				})
				->addColumn('action', function($result){
					$url = "/results_list?f=$result->id";
					return "<a class='btn btn-danger btn-xs' href='$url'>view pending</a>
							<a class='btn btn-danger btn-xs' href='$url&printed=YES'>printed/downloaded</a>";
				})->make(true);
	}
	
}