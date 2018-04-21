<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\VLAPI;
use EID\Mongo;

use EID\LiveData;
use Log;
use DateTime;
use DateInterval;

class DirectResultsController extends Controller {

	public function __construct()
    {
        $this->db = \DB::connection('direct_db');
    }


	public function facility_list(){
		return view('direct.facility_list', ['sect'=>'results']);
	}

	public function facility_data(){
		$cols = ['facility', 'hub', 'coordinator_name', 'coordinator_contact', 'coordinator_email', 'facility'];
		$arr = $this->fetch_facilities($cols);
		extract($arr);
		$data = [];
		$clss = " class='btn btn-danger btn-xs' ";
		foreach ($facilities as $facility) {
			$url = "/direct/results/$facility->id/";
			$data[]	= [
						"<a href='$url'>$facility->facility</a>",
						$facility->hub,
						$facility->coordinator_name,
						$facility->coordinator_contact,
						$facility->coordinator_email,
						$facility->id,
						];
		}

		return compact("recordsTotal", "recordsFiltered", "data");
	}

	public function results($facility_id){
		$facility_name = $this->fetch_facility($facility_id);
		$tab = \Request::has('tab')?\Request::get('tab'):'pending';
		return view('direct.results', compact('facility_id', 'facility_name','tab'));
	}

	public function results_data($facility_id){
		$cols = ['r.sample_id','form_number', 'art_number', 'other_id', 'date_collected', 'date_received',
				 'released_at','dispatch_date', 'r.sample_id'];
		$arr = $this->fetch_results($cols, $facility_id);
		extract($arr);

		$data = [];
		foreach ($results as $result) {
			$select_str = "<input type='checkbox' class='samples' name='samples[]' value='$result->sample_id'>";
			$url = "/direct/result/$result->sample_id";
			$links = ['Print' => "javascript:windPop('$url')",'Download' => "$url?&pdf=1"];
			$data[] = [
				$select_str, 
				$result->form_number, 
				$result->art_number, 
				$result->other_id, 
				\MyHTML::localiseDate($result->date_collected, 'd-M-Y'), 
				\MyHTML::localiseDate($result->date_received, 'd-M-Y'), 
				\MyHTML::localiseDate($result->released_at, 'd-M-Y'),
				\MyHTML::localiseDate($result->dispatch_date, 'd-M-Y'),
				\MyHTML::dropdownLinks($links)];
		}

		return compact("recordsTotal", "recordsFiltered", "data");
	}

	public function result($id=""){
		$vldbresult = [];
		if(!empty($id)){
			$samples = [$id];
		}else{
			$samples = \Request::get("samples");
			if(count($samples)==0){
				return "please select at least one sample";
			}
		}
		$vldbresult = $this->fetch_result($samples);
			
		$tab = \Request::get('tab');
		$dispatch_type =  \Request::has('pdf')? 'D':'P';
		$log_update['resultsdispatch'] = [
			'dispatch_type'=>$dispatch_type, 
			'dispatch_date'=>date("Y-m-d").'T'.date("H:i:s"),
			'dispatched_by'=>\Auth::user()->username, 
			];
		

		if(\Request::has('pdf')){
			$pdf = \PDF::loadView('direct.result_slip', compact("vldbresult"));
			return $pdf->download('vl_results_'.\Request::get('facility').'.pdf');
		}
		return view('direct.result_slip', compact('vldbresult'));
	}


	private function fetch_facilities($cols){
		$params = \MyHTML::datatableParams($cols);
		extract($params);
		$cond = !empty($search)?" facility LIKE '$search%' OR hub LIKE '$search%'":"1";

		$sql0 = "SELECT f.id,facility, hub, f.coordinator_name, f.coordinator_contact, f.coordinator_email
				 FROM backend_facilities AS f
				 LEFT JOIN backend_hubs AS h ON f.hub_id=h.id
				 WHERE $cond ORDER BY $orderby LIMIT $start, $length";
		$facilities = $this->db->select($sql0);

		$sql1 = "SELECT count(f.id) AS num  FROM backend_facilities f LEFT JOIN backend_hubs h ON f.hub_id=h.id";
		$recordsTotal = collect($this->db->select($sql1))->first()->num;

		$sql2 = "$sql1 WHERE $cond";
		$recordsFiltered = empty($cond)?$recordsTotal:collect($this->db->select($sql2))->first()->num;
		return compact('facilities', 'recordsTotal', 'recordsFiltered');
	}

	private function fetch_facility($id){
		$sql = "SELECT facility FROM backend_facilities WHERE id=$id";
		return collect($this->db->select($sql))->first()->facility;
	}	

	private function fetch_results($cols, $facility_id){
		$tab = \Request::has('tab')?\Request::get('tab'):'pending';
		$params = \MyHTML::datatableParams($cols);
		extract($params);
		$cond = " facility_id=$facility_id";
		$cond = $tab == 'pending'? "$cond AND d.id IS NULL":"$cond AND d.id IS NOT NULL";
		$cond2 = !empty($search)?" $cond AND form_number='$search'":$cond;

		$sql0 = $this->results_sql(implode(",", $cols), $cond2)." ORDER BY $orderby LIMIT $start, $length";
		$results = $this->db->select($sql0);

		$sql1 = $this->results_sql(" count(q.id) AS num", $cond);
		$recordsTotal = collect($this->db->select($sql1))->first()->num;

		$sql2 = $this->results_sql(" count(q.id) AS num", $cond2);
		$recordsFiltered = empty($search)?$recordsTotal:collect($this->db->select($sql2))->first()->num;
		return compact('results', 'recordsTotal', 'recordsFiltered');
	}

	private function results_sql($fields="*", $cond="1"){
		return  "SELECT $fields
				 FROM vl_results_qc AS q
				 INNER JOIN vl_results AS r ON q.result_id=r.id
				 INNER JOIN vl_samples AS s ON r.sample_id=s.id
				 INNER JOIN vl_patients AS p ON s.patient_id=p.id
				 LEFT JOIN vl_results_dispatch AS d ON s.id=d.sample_id	
				 WHERE $cond		 
				 ";
	}

	private function fetch_result($samples){
		$samples_str = implode(",", $samples);
		$sql = "SELECT *, cr.appendix AS current_regimen, tl.code AS tx_line
				 FROM vl_results_qc AS q
				 INNER JOIN vl_results AS r ON q.result_id=r.id
				 INNER JOIN vl_samples AS s ON r.sample_id=s.id
				 INNER JOIN vl_verifications AS v ON s.id=v.sample_id
				 INNER JOIN backend_appendices AS cr ON s.current_regimen_id=cr.id
				 INNER JOIN backend_appendices AS tl ON s.treatment_line_id=tl.id
				 INNER JOIN vl_envelopes AS e ON s.envelope_id=e.id
				 INNER JOIN backend_facilities AS f ON s.facility_id=f.id
				 INNER JOIN backend_hubs AS h ON f.hub_id=h.id
				 INNER JOIN backend_districts AS d ON f.district_id=d.id
				 INNER JOIN vl_patients AS p ON s.patient_id=p.id
				 INNER JOIN auth_user AS u ON r.test_by_id=u.id
				 INNER JOIN backend_user_profiles AS up ON u.id=up.user_id

				 WHERE r.sample_id in ($samples_str) LIMIT 100		 
				 ";
		return $this->db->select($sql);
	}

	

	public function search_result($txt){
    	$txt = str_replace(' ', '', $txt);
    	$cond = [];
    	$cond['$and'][] = ["created_at"=>['$gte'=>$this->mDate(env('QC_START_DATE'))]];
    	if(\Request::has('f')) $cond['$and'][] = ['facility.pk'=>(int)\Request::get('f')];
    	$mongo_search = new \MongoRegex("/$txt/i");
    	$cond['$and'][] = ['$or'=>[['result.resultsqc.released'=>true], ['rejectedsamplesrelease.released'=>true]]];
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


}