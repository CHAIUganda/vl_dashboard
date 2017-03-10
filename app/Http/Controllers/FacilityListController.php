<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\WorksheetResults;
use Log;

class FacilityListController extends Controller {

	public function getIndex(){
		$sect = 'results';
		if(empty(\Auth::user()->facility_id) AND empty(\Auth::user()->hub_id)){
			$tab = \Request::has('tab')?\Request::get('tab'):'pending';
		}		
		
		return view('results.facility_list', compact('sect', 'tab'));
	}

	public function getData(){
		$tab = "";
		if(empty(\Auth::user()->facility_id) AND empty(\Auth::user()->hub_id)){
			$tab = \Request::has('tab')?\Request::get('tab'):'pending';
		}
		$facilities = WorksheetResults::getFacilityList($tab);
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