<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\WorksheetResults;
use EID\User;
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
					$xtra = \Request::has('h')?"&h=".\Request::get('h'):"";
					return "<a href='/results_list?f=$result->id$xtra'> $result->facility</a>";
				})
				->setRowAttr([
				    'style' => function($result) {
				        $pilot = "";
						if(empty(\Auth::user()->facility_id) AND empty(\Auth::user()->hub_id)){
							$pilot_facilities = 0;
							if(!empty($result->hubID)) $pilot_facilities = User::where('facility_id', $result->id )->orWhere('hub_id', $result->hubID)->count();
							$pilot = $pilot_facilities>=1? "background-color:#AED6F1":"";
						}

						if(empty(\Auth::user()->facility_id)){
							$pilot_facility_account = User::where('facility_id', $result->id)->count();
							$pilot = $pilot_facility_account>=1? "background-color:#F5A9A9;":"";
						}

						return $pilot;
				    },
				])
				->addColumn('options', function ($result) {
					$xtra = \Request::has('h')?"&h=".\Request::get('h'):"";
					$url = "/results_list?f=$result->id$xtra";
					
					return "<a title='view pending' class='btn btn-danger btn-xs' href='$url'>view pending</a>
							<a title='view printed/downloaded' class='btn btn-danger btn-xs' href='$url&printed=YES'><span class='glyphicon glyphicon-ok'></span></a>
							<a title='print envelope' class='btn btn-danger btn-xs' href='javascript:windPop(\"/print_envelope/$result->id\")'><span class='glyphicon glyphicon-envelope'></span></a>
							";						
					
			    })->make(true);
	}
	
}