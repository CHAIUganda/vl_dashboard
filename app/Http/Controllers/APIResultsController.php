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
		return view('api_results.facility_list', ['sect'=>'results']);
	}

	public function facility_list_data(){
		$cols = ['facility', 'coordinator_name', 'coordinator_contact', 'coordinator_email'];
		$params = $this->get_params($cols);
		$params['hub'] = \Auth::user()->hub_id;
		$facilities = $this->getFacilities($params);

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
		return view('api_results.results', compact('facility_id'));
	}

	public function results_data($facility_id){
		$cols = ['select','form_number', 'patient.art_number', 'patient.other_id', 'date_collected', 'date_received', 'result.resultsqc.released_at', 'options'];
		$params = $this->get_params($cols);
		$params['facility_id'] = $facility_id;
		$samples = $this->getSamples($params);

		$data = [];
		foreach ($samples['data'] as $sample) {
			extract($sample);
			$select_str = "<input type='checkbox' >";
			$url = "/api/result/$pk";
			$links = ['Print preview' => "javascript:windPop('$url')",'Download' => "$url&pdf=1"];
			$data[] = [
				$select_str, 
				$form_number, 
				$patient['art_number'], 
				$patient['other_id'], 
				$date_collected, 
				$date_received, 
				$result['resultsqc']['released_at']?$result['resultsqc']['released_at']:$rejectedsamplesrelease['released_at'],
				\MyHTML::dropdownLinks($links)];
		}

		return [
			"draw" => \Request::get('draw'),
			"recordsTotal" => $samples['recordsTotal'],
			"recordsFiltered" => $samples['recordsFiltered'], 
			"data"=> $data
			];
	}

	public function result($id){
		$vldbresult = $this->mongo->api_samples->find(['pk'=>(int)$id]);
		return view('api_results.result_slip', compact('vldbresult'));
	}



	private function get_params($cols){
    	$order = \Request::get('order');
    	$orderby = [$cols[0]=>1];		
		if(isset($order[0])){
			$col = $cols[$order[0]['column']];
			$dir = $order[0]['dir'];
			$orderby = $dir=='asc'?[$col=>1]:[$col=>-1];
		}

		$search = \Request::has('search')?\Request::get('search')['value']:"";
		$start = \Request::get('start');
		$length = \Request::get('length');

		return compact('orderby','search', 'start', 'length');
    }



	private function getFacilities($params){
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

	private function getSamples($params){
		$ret=[];
		extract($params);
		$cond=[];
		$cond['$and'][] = ['$or'=>[['result.resultsqc.released'=>true], ['rejectedsamplesrelease.released'=>true]]];
		//if(!empty($facility_id)) $cond['$and'][]=["facility"=>$facility_id];
		$ret['recordsTotal'] = $this->mongo->api_samples->find($cond)->count();
		if(!empty($search)){
			$mongo_search = new \MongoRegex("/$search/i");
			$cond['$and'][] = ['form_number' => $mongo_search];
		} 
		$ret['data'] = $this->mongo->api_samples->find($cond)->sort($orderby)->skip($start)->limit($length);
		$ret['recordsFiltered'] = $this->mongo->api_samples->find($cond)->count();

		return $ret;
	}




	public function getIndex(){
		$printed=\Request::get("printed");
		$printed=empty($printed)?'NO':$printed;
		$search = \Request::get("search");
		$facility_name = LiveData::getFacilityName(\Request::get('f'));
		$facilities = [];
		if(\Request::has('h')){
			$facilities = LiveData::leftjoin('vl_samples AS s', 's.id', '=', 'p.sample_id')
                    ->leftjoin('vl_facilities AS f', 'f.id', '=', 's.facilityID')          
                    ->select('f.id','f.facility','f.hubID', \DB::raw("count(p.id) AS num"))
                    ->from('vl_facility_printing AS p')
                    ->where('f.hubID', \Request::get('h'))
                    ->where('p.ready', 'YES')->where('printed', 'NO')->where('downloaded', 'NO')
                    ->groupby('f.id')
                    ->orderby('facility', 'ASC')
                    ->get();
		}
		return view('results.index', compact('printed', 'facility_name', 'facilities', "search"));
	}

	public function getData(){
		$printed=\Request::get("printed");
		$printed=empty($printed)?'NO':$printed;

		$results = LiveData::getResultsList($printed);
		$results_count = LiveData::getResultsList($printed,1);
		return \Datatables::of($results)
				->setTotalRecords($results_count)
				->addColumn('sample_checkbox', function($result){
					return "<input type='checkbox' class='samples' name='samples[]' value=$result->sample_id>";
				})
				->addColumn('action', function ($result) {
					$url = "/result/$result->sample_id?printed=$result->printed";
					$links = [
						'Print preview' => "javascript:windPop('$url')",
						'Download' => "$url&pdf=1"
						];
			        return  \MyHTML::dropdownLinks($links);
			    })
				->make(true);
	}

	/*public function getResult($id='x'){
		$printed = \Request::get('printed');
		$slctd_samples =\Request::has("samples")? \Request::get("samples"): [];
		$slctd_samples_str = is_array($slctd_samples)? implode(',', $slctd_samples):"$slctd_samples";

		$sql = "SELECT  s.*, p.artNumber,p.otherID, p.gender, p.dateOfBirth,
				GROUP_CONCAT(ph.phone SEPARATOR ',') AS phone, f.facility, d.district, h.hub AS hub_name, 
				GROUP_CONCAT(res_r.Result, '|||', res_r.created SEPARATOR '::') AS roche_result,
				GROUP_CONCAT(res_a.result, '|||', res_a.created SEPARATOR '::') AS abbott_result,
				GROUP_CONCAT(res_o.result, '|||', res_o.created SEPARATOR '::') AS override_result,
				log_s.id AS repeated, v.outcome AS verify_outcome, reason.appendix AS rejection_reason,
				u.signaturePATH, wk.machineType, fctr.factor, sw.sampleID, sw.worksheetID
				FROM vl_samples AS s
				LEFT JOIN vl_facilities AS f ON s.facilityID=f.id
				LEFT JOIN vl_districts AS d ON f.districtID=d.id
				LEFT JOIN vl_hubs AS h ON f.hubID=h.id
				LEFT JOIN vl_patients As p ON s.patientID=p.id
				LEFT JOIN vl_patients_phone As ph ON p.id = ph.patientID
				LEFT JOIN vl_samples_verify AS v ON s.id=v.sampleID				
				LEFT JOIN vl_appendix_samplerejectionreason AS reason ON v.outcomeReasonsID=reason.id
				LEFT JOIN vl_samples_worksheet AS sw ON s.id=sw.sampleID
				LEFT JOIN vl_samples_worksheetcredentials AS wk ON sw.worksheetID=wk.id
				LEFT JOIN vl_results_roche AS res_r ON s.vlSampleID = res_r.SampleID
				LEFT JOIN vl_results_abbott AS res_a ON s.vlSampleID = res_a.SampleID
				LEFT JOIN vl_results_override AS res_o ON s.vlSampleID = res_o.sampleID
				LEFT JOIN vl_logs_samplerepeats AS log_s ON s.id = log_s.sampleID
				LEFT JOIN vl_users AS u ON wk.createdby = u.email
				LEFT JOIN vl_results_multiplicationfactor AS fctr ON wk.id=fctr.worksheetID
				WHERE
				";
		if($id=='x' and count($slctd_samples)==0) return "Please select atleast one";
		$sql .= $id!='x'?" s.id=$id LIMIT 1": " s.id IN ($slctd_samples_str) GROUP BY s.id";

		$vldbresult =  \DB::connection('live_db')->select($sql);
		
		if(\Request::has('pdf')) return $this->log_downloads($id,$slctd_samples_str,$vldbresult);

		return view('results.result', compact("vldbresult", "printed"));
	}*/

	public function getResult($id='x'){
		$printed = \Request::get('printed');
		$slctd_samples =\Request::has("samples")? \Request::get("samples"): [];
		$slctd_samples_str = is_array($slctd_samples)? implode(',', $slctd_samples):"$slctd_samples";

		$sql = "SELECT  s.*, fp.qc_at, p.artNumber,p.otherID, p.gender, p.dateOfBirth,
				GROUP_CONCAT(ph.phone SEPARATOR ',') AS phone, f.facility, d.district, h.hub AS hub_name, 
				released.result AS final_result,released.suppressed, released.test_date,  				
				log_s.id AS repeated, v.outcome AS verify_outcome, reason.appendix AS rejection_reason,
				u.signaturePATH, wk.machineType, sw.sampleID, sw.worksheetID,
				GROUP_CONCAT(merged.resultAlphanumeric, '|||', merged.suppressed, '|||', merged.created SEPARATOR '::') AS merged_result
				FROM vl_samples AS s
				LEFT JOIN vl_facilities AS f ON s.facilityID=f.id
				LEFT JOIN vl_districts AS d ON f.districtID=d.id
				LEFT JOIN vl_hubs AS h ON f.hubID=h.id
				LEFT JOIN vl_patients As p ON s.patientID=p.id
				LEFT JOIN vl_patients_phone As ph ON p.id = ph.patientID
				LEFT JOIN vl_samples_verify AS v ON s.id=v.sampleID				
				LEFT JOIN vl_appendix_samplerejectionreason AS reason ON v.outcomeReasonsID=reason.id
				LEFT JOIN vl_samples_worksheet AS sw ON s.id=sw.sampleID
				LEFT JOIN vl_samples_worksheetcredentials AS wk ON sw.worksheetID=wk.id
				LEFT JOIN vl_logs_samplerepeats AS log_s ON s.id = log_s.sampleID
				LEFT JOIN vl_users AS u ON wk.createdby = u.email
				LEFT JOIN vl_results_released AS released ON s.id = released.sample_id
				LEFT JOIN vl_results_merged AS merged ON merged.vlSampleID = s.vlSampleID
				LEFT JOIN vl_facility_printing AS fp ON s.id = fp.sample_id
				WHERE
				";
		if($id=='x' and count($slctd_samples)==0) return "Please select atleast one";
		$sql .= $id!='x'?" s.id=$id LIMIT 1": " s.id IN ($slctd_samples_str) GROUP BY s.id";

		$vldbresult =  \DB::connection('live_db')->select($sql);
		
		if(\Request::has('pdf')) return $this->log_downloads($id,$slctd_samples_str,$vldbresult);

		return view('results.result', compact("vldbresult", "printed"));
	}

	private function log_downloads($id,$slctd_samples_str,$vldbresult){
		$printed = \Request::get('printed');
		$by = addslashes(\Auth::user()->name); 
		$on = date('Y-m-d H:i:s');
		$s_arr = $id!='x'?[$id]:explode(",", $slctd_samples_str);

		if($printed=='NO'){
			$sql = "UPDATE vl_facility_printing SET downloaded='YES', 
					printed_at='$on', 
					printed_by='$by' 
					WHERE ";
			$sql .= !empty($slctd_samples_str)?" sample_id IN ($slctd_samples_str) ":"sample_id=$id";

		}else{
			$sql = "INSERT INTO vl_facility_downloads (sample_id, downloaded_by, downloaded_on) VALUES";
			foreach ($s_arr as $smpl) {
				$sql .= "($smpl, '".$by."', '".date('Y-m-d H:i:s')."'),";				
			}
			$sql = trim($sql, ',');		

		}	
		//return $sql;		

		\DB::connection('live_db')->unprepared($sql);
		$pdf = \PDF::loadView('results.pdfresults', compact("vldbresult"));
		return $pdf->download('vl_results_'.session('facility').'.pdf');
		//return \PDF::loadFile('http://www.github.com')->inline('github.pdf');
		
	}

	public function log_printing(){
		$printed = \Request::get('printed');
		$samples = \Request::get('s');
		$by = addslashes(\Auth::user()->name);
		$on = date('Y-m-d H:i:s');
		if($printed=='NO'){
			$sql = "UPDATE vl_facility_printing SET printed='YES', 
					printed_at='$on', 
					printed_by='$by' 
					WHERE sample_id IN ($samples)";
		}else{			
			$samples_arr = explode(",", $samples);
			$sql = "INSERT INTO vl_facility_reprinting (sample_id, printed_by, printed_on) VALUES";
			foreach ($samples_arr as $smpl) {
				$sql .= "($smpl, '$by', '$on'),";				
			}
			$sql = trim($sql, ',');
		}
		
		\DB::connection('live_db')->unprepared($sql);
	}

	public function facilities(){
		$facilities = LiveData::leftjoin('vl_hubs AS h', 'h.id', '=', 'f.hubID')->select('f.*', 'hub')->from('vl_facilities AS f');
		if(!empty(\Auth::user()->hub_id)){
			$facilities = $facilities->where('hubID', \Auth::user()->hub_id)->get();
		}elseif(!empty(\Auth::user()->facility_id)){
			$facilities = $facilities->where('f.id', \Auth::user()->facility_id)->get();
		}else{
			$facilities = $facilities->get();
		}

		return view('results.facilities', compact('facilities'));
	}

	public function getPatientResultsForHub(){
		extract(\Request::all());
		if((empty($fro_date) && empty($to_date))||$fro_date=='all' && $to_date=='all'){
			$to_date=date("Ym");
			$fro_date=$this->_dateNMonthsBack();
		}
        $hub_id = \Auth::user()->hub_id;
		$sql = "select sr.patientID,sr.vlSampleID,sr.created,sr.patientUniqueID, sr.result,
						h.hub, f.facility,sr.collectionDate,sr.receiptDate,p.artNumber,p.phone
						
					from 
						(select s.patientID,s.vlSampleID,s.created,s.patientUniqueID, r.result,
						 s.hubID, s.facilityID,s.collectionDate,s.receiptDate
						   from 
					           ( select * from vl_samples where hubID=$hub_id and  str_to_date(created,'%Y-%m') 
						between str_to_date('$fro_date','%Y%m') and str_to_date('$to_date','%Y%m')) s left join 
								(select sampleID, result as result, created 
									from vl_results_abbott order by sampleID,created) r
						on s.vlSampleID = r.sampleID 
					    ) sr,

						(SELECT p.uniqueID,p.artNumber,pp.phone FROM vl_patients p left join vl_patients_phone pp  on pp.patientID = p.id) p,
					vl_hubs h,vl_facilities f

					where 
						sr.patientUniqueID = p.uniqueID and
						sr.hubID = h.id and sr.facilityID = f.id

					order by sr.patientID
					";

		
        $patient_results = null;
        $patient_retested_dates = null;
        try{
        	ini_set('memory_limit','384M');
        	$patient_results =  \DB::connection('live_db')->select($sql);
        	
        	//$patient_retested_dates = $this->getPatientRetestedDates($fro_date,$to_date,$hub_id);
        	
        }catch(\Illuminate\Database\QueryException $e){
        	Log::info("---ooops---");
        	Log::error($e->getMessage());
        	
        }
	
		
		//return compact("patient_results");

        return $patient_results;
	}

	public function getPatientResultsForFacility(){
		extract(\Request::all());
		if((empty($fro_date) && empty($to_date))||$fro_date=='all' && $to_date=='all'){
			$to_date=date("Ym");
			$fro_date=$this->_dateNMonthsBack();
		}
        
        $facility_id = \Auth::user()->facility_id;
        
		$sql = "select sr.patientID,sr.vlSampleID,sr.created,sr.patientUniqueID, sr.result,
						h.hub, f.facility,sr.collectionDate,sr.receiptDate,p.artNumber,p.phone
						
					from 
						(select s.patientID,s.vlSampleID,s.created,s.patientUniqueID, r.result,
						 s.hubID, s.facilityID,s.collectionDate,s.receiptDate
						   from 
					           ( select * from vl_samples where facilityID=$facility_id and  str_to_date(created,'%Y-%m') 
						between str_to_date('$fro_date','%Y%m') and str_to_date('$to_date','%Y%m')) s left join 
								(select sampleID, result as result, created 
									from vl_results_abbott order by sampleID,created) r
						on s.vlSampleID = r.sampleID 
					    ) sr,

						(SELECT p.uniqueID,p.artNumber,pp.phone FROM vl_patients p left join vl_patients_phone pp  on pp.patientID = p.id) p,
					vl_hubs h,vl_facilities f

					where 
						sr.patientUniqueID = p.uniqueID and
						sr.hubID = h.id and sr.facilityID = f.id

					order by sr.patientID
					";

		Log::info($sql);
        $patient_results = null;
        $patient_retested_dates = null;
        try{
        	ini_set('memory_limit','384M');
        	$patient_results =  \DB::connection('live_db')->select($sql);
        	
        	//$patient_retested_dates = $this->getPatientRetestedDates($fro_date,$to_date,$hub_id);
        	
        }catch(\Illuminate\Database\QueryException $e){
        	Log::info("---ooops---");
        	Log::error($e->getMessage());
        	
        }		
		//return compact("patient_results");
		return $patient_results;

	}
	public function getPatientResults(){
		$patient_results = null;
		/*if(!empty(\Auth::user()->hub_id) && \Auth::user()->can('view_reports_as_hub')){
			$patient_results = $this->getPatientResultsForHub();
		}elseif(!empty(\Auth::user()->facility_id) && \Auth::user()->can('view_reports_as_facility')){
			$patient_results = $this->getPatientResultsForFacility();
		}
		*/
		if(!empty(\Auth::user()->hub_id) && intval(\Auth::user()->hub_id) > 0){
			$patient_results = $this->getPatientResultsForHub();
		}elseif(!empty(\Auth::user()->facility_id)){
			$patient_results = $this->getPatientResultsForFacility();
		}

		return compact("patient_results");
	}
	private function addSixMonths($to_date){
	    $year = intval(substr($to_date,0,4));
	    $month = intval(substr($to_date,4));
	    
	    //increment month and year
	    $month = $month + 6;
	    if($month > 12){
	        $difference = $month - 12;
	        $month = $difference;
	        $year ++;
	    }
	    $new_month=null;
	    if($month < 10){
	        $new_month = "0$month";
	    }else{
	         $new_month = "$month";
	    }
	    $new_date = "$year"."$new_month";
	    return $new_date;
	}
	private function getPatientRetestedDates($fro_date,$to_date,$hub_id){
		$to_date_incremented= $this->addSixMonths($to_date);
		ini_set('memory_limit','384M');
		
        
        $sql = "select patientID,patientUniqueID,collectionDate from vl_samples where hubID=$hub_id and  str_to_date(created,'%Y-%m') 
						between str_to_date('$fro_date','%Y%m') and str_to_date('$to_date_incremented','%Y%m') order by patientUniqueID,collectionDate";
		
		$patient_retested_dates =  \DB::connection('live_db')->select($sql);
		
		

		return $patient_retested_dates;

	}

	public function getPatientViralLoads(){
		extract(\Request::all());
		
		$sql = "SELECT s.vlSampleID,s.collectionDate, r.result FROM vl_samples s, vl_results_abbott r 
		 where s.vlSampleID = r.sampleID  and s.patientID=$patientID 
			union
		SELECT s.vlSampleID,s.collectionDate, r.result FROM vl_samples s, vl_results_roche r 
		where s.vlSampleID = r.sampleID and s.patientID=$patientID";

		$patient_viral_loads=null;
		try{
        	$patient_viral_loads =  \DB::connection('live_db')->select($sql);        	
        }catch(\Illuminate\Database\QueryException $e){
        	Log::info("---ooops---");
        	Log::error($e->getMessage());
        	
        }
		return compact("patient_viral_loads");
	}
/*
	public function getPatientResults(){
		
		$sql = "select sr.patientID,sr.vlSampleID,sr.created,sr.patientUniqueID, sr.result,
						h.hub, f.facility,sr.collectionDate,sr.receiptDate,p.artNumber,p.phone
						
					from 
						(select s.patientID,s.vlSampleID,s.created,s.patientUniqueID, r.result,
						 s.hubID, s.facilityID,s.collectionDate,s.receiptDate
						   from 
					           ( select * from vl_samples where hubID=21 and  str_to_date(created,'%Y-%m') 
						between str_to_date('2015-01','%Y-%m') and str_to_date('2016-02','%Y-%m')) s left join 
								(select sampleID, result as result, created 
									from vl_results_abbott order by sampleID,created) r
						on s.vlSampleID = r.sampleID 
					    ) sr,

						(SELECT p.uniqueID,p.artNumber,pp.phone FROM vl_patients p left join vl_patients_phone pp  on pp.patientID = p.id) p,
					vl_hubs h,vl_facilities f

					where 
						sr.patientUniqueID = p.uniqueID and
						sr.hubID = h.id and sr.facilityID = f.id

					order by sr.patientID
					";



		$patient_results =  \DB::connection('live_db')->select($sql);
		
		
		return compact("patient_results");
	}
	
	private function _dateNMonthsBack(){
    	$ret;
    	$n=env('INIT_MONTHS');
        $m=date('m');
        $y=date('Y');
        for($i=1;$i<=$n;$i++){
        	if($i==$n) $ret=$y.str_pad($m, 2,0, STR_PAD_LEFT);
	*/
	public function print_envelope($id){
		$facility = LiveData::leftjoin('vl_districts AS d', 'd.id', '=', 'f.districtID')
		                ->leftjoin('vl_hubs AS h', 'h.id', '=', 'f.hubID')
						->select('hub','district','facility', 'f.contactPerson', 'f.phone', 'f.email')->from('vl_facilities AS f')
						->where('f.id', '=', $id)->limit(1)->get();
		$facility = $facility[0];
		return view('results.print_envelope', compact('facility'));
	}

	public function searchbyhub($txt){
		$hubs = LiveData::select('id', 'hub')->from('vl_hubs')->where('hub', 'LIKE', "%$txt%")->limit(10)->get();
		$ret = "";
		foreach ($hubs as $hub) {
			$ret .= "<a href='/results?h=$hub->id&tab=".\Request::get('tab')."'>$hub->hub</a><br>";			
		}
    	return $ret;
    }

    public function search_result($txt){
    	$txt = str_replace(' ', '', $txt);
    	$f = \Request::get('f');
    	$f_limit = \Request::has('f')?"s.facilityID=$f AND":"";
    	$results = LiveData::leftjoin('vl_patients AS p', 'p.id', '=', 's.patientID')
    				->leftjoin('vl_facility_printing AS fp', 'fp.sample_id', '=', 's.id')
    				->leftjoin('vl_results_released AS rr', 'rr.sample_id', '=', 's.id')
    				->select('s.id AS pk', 'formNumber', 'artNumber')->from('vl_samples AS s')
    				->whereRaw("$f_limit (formNumber LIKE '%$txt%' OR REPLACE(artNumber, ' ','') LIKE '%$txt%')")
    				->whereNotNull('fp.id')
    				->whereNotNull('rr.id')
    				->limit(10)->get();
    	$ret = "<table class='table table-striped table-condensed table-bordered'>
    			<tr><th>Form Number</th><th>Art Number</th><th /></tr>";
    	foreach ($results AS $result){
    		$url = "/result/$result->pk";
    		$print_url = "<a href='javascript:windPop(\"$url\")'>print</a>";
    		$download_url = "<a href='$url?pdf=1'>download</a>";
    		$ret .= "<tr><td>$result->formNumber</td><td>$result->artNumber</td><td>$print_url | $download_url</td></tr>";	
    	}
    	return $ret."</table>";
    }
	
	private function _dateNMonthsBack(){
    	$ret;
    	$n=env('INIT_MONTHS');
        $m=date('m');
        $y=date('Y');
        for($i=1;$i<=$n;$i++){
        	if($i==$n) $ret=$y.str_pad($m, 2,0, STR_PAD_LEFT);

            if($m==0){
                $m=12;
                $y--;
            }
            $m--;
        }
        return $ret;
    }
}