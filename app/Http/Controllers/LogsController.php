<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\LiveData;

class LogsController extends Controller {

	public function getIndex(){
		return view('logs.logs');
	}

	public function getData(){	
		$results = LiveData::leftjoin('vl_samples AS s','s.id','=','p.sample_id')
						->leftjoin('vl_facilities AS f', 'f.id', '=', 's.facilityID')
						->leftjoin('vl_hubs AS h', 'h.id', '=', 'f.hubID')
						->select('printed_by','printed_at','formNumber','facility','hub')
						->from('vl_facility_printing AS p')->where('printed','YES')->orderby('printed_at', 'DESC');	
		return \Datatables::of($results)->make(true);
	}

}