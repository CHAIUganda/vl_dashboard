<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\VLAPI;
use EID\Mongo;

use EID\LiveData;
use Log;
use DateTime;
use DateInterval;

class APIResultsController extends Controller {

	public function __construct()
    {
        $this->mongo=Mongo::connect();
    }

	public function facility_list(){
		$facilities = $this->getFacilities();
		return view('api_results.facility_list', ['sect'=>'results', 'facilities'=>$facilities]);
	}

	public function facility_list_data(){
		$cols = ['facility', 'coordinator_name', 'coordinator_contact', 'coordinator_email'];
		$params = $this->get_params($cols);
		$params['hub'] = \Auth::user()->hub_id;
		$params['facility'] = \Auth::user()->facility_id;
		$facilities = $this->getFacilities1($params);

		$data = [];
		foreach ($facilities['data'] as $record) {
			extract($record);
			$facility_str = "<a href='/api/results/$pk'>$facility</a>";
			$data[] = [$facility_str, $coordinator_name, $coordinator_contact, $coordinator_email];
		}
		
		return [
			"draw" => \Request::get('draw'),
			"recordsTotal" => $facilities['recordsTotal'],
			"recordsFiltered" => $facilities['recordsFiltered'], 
			"data"=> $data
			];
	}

	public function results($facility_id){
		$facility = $this->mongo->api_facilities->findOne(['pk'=>(int)$facility_id]);
		$facility_name = isset($facility['facility'])?$facility['facility']:"";
		$tab = \Request::has('tab')?\Request::get('tab'):'pending';
		return view('api_results.results', compact('facility_id', 'facility_name','tab'));
	}

	public function results_data($facility_id){
		$cols = ['select','form_number', 'patient.art_number', 'patient.other_id', 'date_collected', 'date_received', 'result.resultsqc.released_at', 'options'];
		$params = $this->get_params($cols);
		$params['facility_id'] = $facility_id;

		$samples = $this->getSamples($params);

		$data = [];
		foreach ($samples['data'] as $sample) {
			extract($sample);
			$select_str = "<input type='checkbox' class='samples' name='samples[]' value='$_id'>";
			$url = "/api/result/$_id";
			$links = ['Print' => "javascript:windPop('$url')",'Download' => "$url?&pdf=1"];
			$released_at = $result['resultsqc']['released_at']?$result['resultsqc']['released_at']:$rejectedsamplesrelease['released_at'];
			$data[] = [
				$select_str, 
				$form_number, 
				$patient['art_number'], 
				$patient['other_id'], 
				\MyHTML::localiseDate($date_collected, 'd-M-Y'), 
				\MyHTML::localiseDate($date_received, 'd-M-Y'), 
				\MyHTML::localiseDate($released_at, 'd-M-Y'),
				\MyHTML::dropdownLinks($links)];
		}

		return [
			"draw" => \Request::get('draw'),
			"recordsTotal" => $samples['recordsTotal'],
			"recordsFiltered" => $samples['recordsFiltered'], 
			"data"=> $data
			];
	}

	public function result($id=""){
		if(!empty($id)){
			$cond = ['_id'=>$this->_id($id)];
		}else{
			$samples = \Request::get("samples");
			if(count($samples)==0){
				return "please select at least one sample";
			}else{
				$objs = array_map(function($id){ return $this->_id($id); }, $samples);
				$cond = ['_id'=>['$in'=>$objs]];
			}
		}
		
		$vldbresult = $this->mongo->api_samples->find($cond);
		$dispatch_type =  \Request::has('pdf')? 'D':'P';
		$log_update['resultsdispatch'] = [
			'dispatch_type'=>$dispatch_type, 
			'dispatch_date'=>date("Y-m-d").'T'.date("H:i:s"),
			'dispatched_by'=>\Auth::user()->username, 
			];
			
		$this->mongo->api_samples->update($cond,['$set'=>$log_update], ['multiple'=>true]);

		if(\Request::has('pdf')){
			$pdf = \PDF::loadView('api_results.result_slip', compact("vldbresult"));
			return $pdf->download('vl_results_'.session('facility').'.pdf');
		}
		return view('api_results.result_slip', compact('vldbresult'));
	}

	public function search_result($txt){
    	$txt = str_replace(' ', '', $txt);
    	$cond = [];
    	$cond['$and'][] = ["created_at"=>['$gte'=>$this->mDate(env('QC_START_DATE'))]];
    	if(\Request::has('f')) $cond['$and'][] = ['facility.pk'=>(int)\Request::get('f')];
    	$mongo_search = new \MongoRegex("/$txt/i");
		$cond['$and'][] = ['$or'=>[['form_number' => $mongo_search], ['patient.art_number' => $mongo_search]]];
		$results = $this->mongo->api_samples->find($cond);
    	$ret = "<table class='table table-striped table-condensed table-bordered'>
    			<tr><th>Form Number</th><th>Art Number</th><th /></tr>";
    	foreach ($results AS $result){
    		$url = "/api/result/".$result['_id'];
    		$print_url = "<a href='javascript:windPop(\"$url\")'>print</a>";
    		$download_url = "<a href='$url?pdf=1'>download</a>";
    		$ret .= "<tr><td>$result[form_number]</td><td>".$result['patient']['art_number']."</td><td>$print_url | $download_url</td></tr>";	
    	}
    	return $ret."</table>";
    }

	private function _id($id){
		return new \MongoId($id);
	}

	private function get_params($cols){
    	$order = \Request::get('order');
    	$tab = \Request::get('tab');
    	$orderby = [$cols[0]=>1];		
		if(isset($order[0])){
			$col = $cols[$order[0]['column']];
			$dir = $order[0]['dir'];
			$orderby = $dir=='asc'?[$col=>1]:[$col=>-1];
		}

		$search = \Request::has('search')?\Request::get('search')['value']:"";
		$search = trim($search);
		$start = \Request::get('start');
		$length = \Request::get('length');
		$printed = $tab=='completed'?true:false;

		return compact('orderby','search', 'start', 'length', 'printed');
    }



	private function getFacilities1($params){
		$ret=[];
		extract($params);
		$cond=[];
		if(!empty($hub)) $cond['$and'][]=["hub"=>$hub];
		$ret['recordsTotal'] = $this->mongo->api_facilities->find($cond)->count();
		if(!empty($search)) $cond['$and'][] = ['facility'=>new \MongoRegex("/$search/i")];
		$ret['data'] = $this->mongo->api_facilities->find($cond)->sort($orderby)->skip($start)->limit($length);
		$ret['recordsFiltered'] = $this->mongo->api_facilities->find($cond)->count();
		return $ret;
	}

	private function getFacilities(){
		$ret = [];
		$cond = [];
		$hub = \Auth::user()->hub_id;
		$facility = \Auth::user()->facility_id;
		$cond['$and'][] = ["created_at"=>['$gte'=>$this->mDate(env('QC_START_DATE'))]];
		if(!empty($hub)) $cond['$and'][] = ["facility.hub.pk"=>(int)$hub];
		if(!empty($facility)){
			$user_facilities = !empty(\Auth::user()->other_facilities)? unserialize(\Auth::user()->other_facilities):[];
			 array_push($user_facilities, $facility);
			 $cond['$and'][] = ["facility.pk"=>['$in'=>array_map(function($f){return (int)$f; }, $user_facilities)] ];
		}		

		$project = ["_id"=>0, "facility"=>1];
		#['$eq'=>['$result.resultsqc.released',true]];
		$pending_conds['$and'][] = ['$or'=>[['$eq'=>['$result.resultsqc.released',true]], ['$eq'=>['$rejectedsamplesrelease.released',true]]]];
		$pending_conds['$and'][] = ['$eq'=>['$resultsdispatch',null]];
		$project['num_pending'] = ['$cond'=>['if'=>$pending_conds, 'then'=>1, 'else'=>0]];
		$project['num_dispatched'] = ['$cond'=>['if'=>['$ne'=>['$resultsdispatch',null]], 'then'=>1, 'else'=>0]];
		$group =  ['_id'=> '$facility','num_pending' => ['$sum'=> '$num_pending'],'num_dispatched'=> ['$sum'=> '$num_dispatched'] ];
		
		$mresult = $this->mongo->api_samples->aggregate(
			['$match'=>$cond], 
			['$project'=>$project], 
			['$group'=>$group]
			// ['$sort'=>$orderby],
			// ['$skip'=>(int)$start],
			// ['$limit'=>(int)$length]
			);
		//$ret['data'] = $mresult['result'];
		return $mresult['result'];
	}

	private function getSamples($params){
		$ret=[];
		extract($params);
		$cond=[];
		$cond['$and'][] = ['$or'=>[['result.resultsqc.released'=>true], ['rejectedsamplesrelease.released'=>true]]];
		$cond['$and'][] = ["facility.pk"=>(int)$facility_id];
		$cond['$and'][] = ["created_at"=>['$gte'=>$this->mDate(env('QC_START_DATE'))]];
		if($printed==false){
			$cond['$and'][] = ['resultsdispatch'=>null];
		}else{
			$cond['$and'][] = ['resultsdispatch'=>['$ne'=>null]];
		} 
		$ret['recordsTotal'] = $this->mongo->api_samples->find($cond)->count();
		if(!empty($search)){
			$mongo_search = new \MongoRegex("/$search/i");
			$cond['$and'][] = ['form_number' => $mongo_search];
		} 
		$ret['data'] = $this->mongo->api_samples->find($cond)->sort($orderby)->skip($start)->limit($length);
		$ret['recordsFiltered'] = $this->mongo->api_samples->find($cond)->count();

		return $ret;
	}

	private function mDate($date_str){
		if(empty($date_str)) $date_str = date("Y-m-d");
		return new \MongoDate(strtotime($date_str));
	}


}