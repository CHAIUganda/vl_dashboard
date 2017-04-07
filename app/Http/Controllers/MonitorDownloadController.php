<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\WorksheetResults;
use Log;

class MonitorDownloadController extends Controller {

	public function getIndex(){
		return view('results.monitor_download');
	}

	public function getData(){
		$results = WorksheetResults::getSamples();
		return \Datatables::of($results)->make(true);
	}
	
}