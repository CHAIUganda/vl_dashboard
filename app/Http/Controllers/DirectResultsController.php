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
		return view('direct.facilities', ['sect'=>'results']);
	}

	public function facility_data(){
		$cols = ['facility', 'hub', 'coordinator_name', 'coordinator_contact', 'coordinator_email',
				 'num_pending_dispatch', 'num_dispatched', 'last_dispatched_at',  'facility'];
		$arr = $this->fetch_facilities($cols);
		extract($arr);
		$data = [];
		$clss = " class='btn btn-danger btn-xs' ";
		$tick = "<span class='glyphicon glyphicon-ok'></span>";
		$env = "<span class='glyphicon glyphicon-envelope'></span>";
		#$ha_clss = 'onclick="this.parentElement.style.color = \'#F5A9A9\'";';
		$ha_clss = "class='has-account'";

		$accounts = $this->facilities_with_accounts();
		
		foreach ($facilities as $facility) {
			$url = "/direct/results/$facility->id/";
			$env_url = "javascript:windPop(\"/print_envelope/$facility->id\")";
			$has_account = (in_array($facility->id, $accounts) && empty(\Auth::User()->facility_id))?" $ha_clss ":"";
			$data[]	= [
						"<a title='view pending' href='$url' $has_account >".trim($facility->facility)."</a>",
						$facility->hub,
						$facility->coordinator_name,
						$facility->coordinator_contact,
						$facility->coordinator_email,
						"<a title='view pending' href='$url'>$facility->num_pending_dispatch</a>", 
						"<a title='view printed/downloaded' href='$url?tab=completed'>$facility->num_dispatched</a>",
						\MyHTML::localiseDate($facility->last_dispatched_at, 'd-M-Y'),
						"<a href='$url' $clss>view pending</a>
						<a title='view printed/downloaded' href='$url?tab=completed' $clss>$tick</a>
						<a title='print envelope' $clss href='$env_url'>$env</a>
						",
						];
		}
		$draw = \Request::get('draw');
		return compact("draw", "recordsTotal", "recordsFiltered", "data");
	}

	public function getResultsPrintingStatistics(){
		
	    $facilities = $this->fetch_facilities_statistics();
		

		return compact('facilities');

	}
	public function results($facility_id){
		$facility_name = $this->fetch_facility($facility_id);
		$tab = \Request::has('tab')?\Request::get('tab'):'pending';
		$type = \Request::has('type')?\Request::get('type'):'valids';
		return view('direct.results', compact('facility_id', 'facility_name','tab', 'type'));
	}

	public function results_data($facility_id){
		$cols = ['r.sample_id','form_number', 'art_number', 'other_id', 'date_collected', 'date_received',
				 'released_at','dispatch_date', 'r.sample_id'];
		$arr = $this->fetch_results($cols, $facility_id);
		extract($arr);

		$data = [];
		foreach ($results as $result) {
			$select_str = "<input type='checkbox' class='samples' name='samples[]' value='$result->sample_id'>";
			$url = "/direct/result/$result->sample_id/?tab=".\Request::get('tab');
			$links = ['Print' => "javascript:windPop('$url')",'Download' => "$url&pdf=1"];
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
		$draw = \Request::get('draw');
		return compact("draw", "recordsTotal", "recordsFiltered", "data");
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
		$this->log_last_dispatch();
		if($tab=='pending'){
			$this->save_dispatch($samples);
			$print_version = "1.0";
		}else{
			$print_version = "2.0";
		}	

		if(\Request::has('pdf')){
			$pdf = \PDF::loadView('direct.result_slip', compact('vldbresult', 'print_version'));
			return $pdf->download('vl_results_'.\Request::get('facility').'.pdf');
		}
		return view('direct.result_slip', compact('vldbresult', 'print_version'));
	}

	public function forms_download(){
		$form_numbers = \Request::get('form_numbers');		
		$vldbresult = $this->fetch_result([], $form_numbers);
		$print_version = "";
		$pdf = \PDF::loadView('direct.result_slip', compact("vldbresult", "print_version"));
		return $pdf->download('vl_results_'.\Request::get('facility').'.pdf');
	}


	private function fetch_facilities($cols){
		$params = \MyHTML::datatableParams($cols);
		extract($params);
		$user_facility = \Auth::user()->facility_id;
		$facility_cond = $hub_cond = "1";
		if(!empty($user_facility)){
			$user_facilities = !empty(\Auth::user()->other_facilities)? unserialize(\Auth::user()->other_facilities):[];
			array_push($user_facilities, $user_facility);
			$facility_cond = " f.id IN (".implode(",", $user_facilities).")";
		}elseif(!empty(\Auth::user()->hub_id)){
			$hub_cond = "h.id=".\Auth::user()->hub_id;
		}

		$such_cond = !empty($search)?" (facility LIKE '$search%' OR hub LIKE '$search%')":"1";
		$cond = "$such_cond AND $facility_cond AND $hub_cond";

		$sql0 = "SELECT f.id,facility, hub, f.coordinator_name, f.coordinator_contact, f.coordinator_email, 
				 num_pending_dispatch, num_dispatched, last_dispatched_at
				 FROM backend_facilities AS f
				 LEFT JOIN backend_hubs AS h ON f.hub_id=h.id
				 INNER JOIN backend_facility_stats AS fs ON f.id=fs.facility_id
				 WHERE $cond ORDER BY $orderby LIMIT $start, $length";
		$facilities = $this->db->select($sql0);

		$sql1 = "SELECT count(f.id) AS num  FROM backend_facilities f 
				 LEFT JOIN backend_hubs h ON f.hub_id=h.id
				 INNER JOIN backend_facility_stats AS fs ON f.id=fs.facility_id";
		$recordsTotal = collect($this->db->select("$sql1 WHERE $facility_cond AND $hub_cond"))->first()->num;

		$sql2 = "$sql1 WHERE $cond";
		$recordsFiltered = empty($search)?$recordsTotal:collect($this->db->select($sql2))->first()->num;
		return compact('facilities', 'recordsTotal', 'recordsFiltered');
	}

	private function fetch_facilities_statistics(){
		
		
		$sql0 = "SELECT f.id,facility, hub, ip,

				 num_pending_dispatch, num_dispatched, last_dispatched_at,fs.oldest_pending_printing 

				 FROM backend_facilities AS f
				 LEFT JOIN backend_hubs AS h ON f.hub_id=h.id
				 LEFT JOIN backend_ips AS ips ON ips.id = h.ip_id 
				 INNER JOIN backend_facility_stats AS fs ON f.id=fs.facility_id 
				 where ips.active=1 and num_pending_dispatch > 0";
		$facilities = $this->db->select($sql0);
		
		
		return compact('facilities');
	}

	private function fetch_facility($id){
		$sql = "SELECT facility FROM backend_facilities WHERE id=$id";
		return collect($this->db->select($sql))->first()->facility;
	}	

	private function fetch_results($cols, $facility_id){
		$params = \MyHTML::datatableParams($cols);
		extract($params);
		$tab = \Request::has('tab')?\Request::get('tab'):'pending';
		$crtd_at = $tab=='pending'?env('PENDING_DATE'):env('QC_START_DATE');

		$cond = " released=1 AND s.facility_id=$facility_id AND s.created_at >='$crtd_at' ";		
		$cond = $tab == 'pending'? "$cond AND d.id IS NULL":"$cond AND d.id IS NOT NULL";
		$cond2 = !empty($search)?" $cond AND form_number='$search'":$cond;

		$sql0 = $this->results_sql(implode(",", $cols), $cond2)." ORDER BY $orderby LIMIT $start, $length";
		$results = $this->db->select($sql0);

		
		$sql1 = $this->results_sql(" count(r.id) AS num", $cond);
		$recordsTotal = collect($this->db->select($sql1))->first()->num;

		$sql2 = $this->results_sql(" count(r.id) AS num", $cond2);
		$recordsFiltered = empty($search)?$recordsTotal:collect($this->db->select($sql2))->first()->num;
		return compact('results', 'recordsTotal', 'recordsFiltered');
	}

	private function results_sql($fields="*", $cond="1"){
		$type = \Request::get('type');
		if($type=='valids'){
			$cond .= " AND (r.suppressed=1 OR r.suppressed=2)";
		}elseif($type=='invalids'){
			$cond .= " AND r.suppressed=3";
		}else{
			$cond .= " AND v.accepted=0";
		}

		if($type=='valids'||$type=='invalids'){
			return  "SELECT $fields
					 FROM vl_results_qc AS q
					 INNER JOIN vl_results AS r ON q.result_id=r.id
					 INNER JOIN vl_samples AS s ON r.sample_id=s.id
					 INNER JOIN vl_patients AS p ON s.patient_id=p.id
					 LEFT JOIN vl_results_dispatch AS d ON s.id=d.sample_id	
					 WHERE $cond		 
					 ";
		}else{
			return $this->rejects_sql($fields, $cond);
		}
		
		
	}

	private function rejects_sql($fields="*", $cond="1"){
		return  "SELECT $fields
				 FROM vl_rejected_samples_release AS r
				 INNER JOIN vl_samples AS s ON r.sample_id=s.id
				 INNER JOIN vl_verifications AS v ON s.id=v.sample_id
				 INNER JOIN vl_patients AS p ON s.patient_id=p.id
				 LEFT JOIN vl_results_dispatch AS d ON s.id=d.sample_id	
				 WHERE $cond		 
				 ";
	}

	private function type_cond(){
		$type = \Request::get('type');
		if($type=='valids'){
			return "(r.suppressed=1 OR r.suppressed=2)";
		}elseif($type=='invalids'){
			return "r.suppressed=3";
		}else{
			return "v.accepted=0";
		}
	}

	private function fetch_result($samples, $f=0){
		$samples_str = implode(",", $samples);
		$samples_cond = !empty($f)?"form_number in ($f)":"s.id in ($samples_str)";
		
		$sql = " SELECT *, cr.appendix AS current_regimen, tl.code AS tx_line, rs.appendix AS rejection_reason,
				 rj.released_at AS rj_released_at, s.id as sid, up2.signature as appr_sign, up.signature as testby_sign
				 FROM vl_samples AS s
				 LEFT JOIN vl_rejected_samples_release AS rj ON s.id=rj.sample_id
				 LEFT JOIN vl_results AS r ON s.id=r.sample_id
				 LEFT JOIN vl_results_qc AS q ON r.id=q.result_id
				 LEFT JOIN vl_verifications AS v ON s.id=v.sample_id
				 LEFT JOIN backend_appendices AS cr ON s.current_regimen_id=cr.id
				 LEFT JOIN backend_appendices AS tl ON s.treatment_line_id=tl.id
				 LEFT JOIN backend_appendices AS rs ON v.rejection_reason_id=rs.id
				 LEFT JOIN vl_envelopes AS e ON s.envelope_id=e.id
				 LEFT JOIN backend_facilities AS f ON s.facility_id=f.id
				 LEFT JOIN backend_hubs AS h ON f.hub_id=h.id
				 LEFT JOIN backend_districts AS d ON f.district_id=d.id
				 LEFT JOIN vl_patients AS p ON s.patient_id=p.id
				 LEFT JOIN auth_user AS u ON r.test_by_id=u.id
				 LEFT JOIN auth_user AS u2 ON r.authorised_by_id=u2.id
				 LEFT JOIN backend_user_profiles AS up ON u.id=up.user_id
				 LEFT JOIN backend_user_profiles AS up2 ON u2.id=up2.user_id
				 WHERE s.created_at >='".env('QC_START_DATE')."' AND $samples_cond LIMIT 100		 
				 ";
		return $this->db->select($sql);
	}

	private function log_last_dispatch(){
		$now = date("Y-m-d H:i:s");
		if(\Request::has('facility_id')){
			$f_id = \Request::get('facility_id');
			$sql1 = "UPDATE backend_facility_stats SET last_dispatched_at='$now' WHERE facility_id=$f_id";
			$this->db->unprepared($sql1);
		}	
	}

	private function save_dispatch($samples){
		$now = date("Y-m-d H:i:s");
		$by = addslashes(\Auth::user()->name);
		$dispatch_type =  \Request::has('pdf')? 'D':'P';
		$s_str = implode(",", $samples);
		$this->db->unprepared("DELETE FROM vl_results_dispatch WHERE sample_id IN ($s_str)");
		$sql = "INSERT INTO vl_results_dispatch (dispatch_type, dispatch_date, dispatched_by, sample_id) VALUES";
		foreach($samples AS $sample_id){
			$sql .= "('$dispatch_type', '$now', '$by', $sample_id),";
		}
		$sql = trim($sql, ',');
		$this->db->unprepared($sql);

		if(\Request::has('facility_id')){
			$f_id = \Request::get('facility_id');
			$n = count($samples);
			$sql1 = "UPDATE backend_facility_stats SET num_pending_dispatch=(num_pending_dispatch-$n),
					 num_dispatched=(num_dispatched+$n) 
					 WHERE facility_id=$f_id";
			$this->db->unprepared($sql1);
		}		
	}
	

	public function search_result(){ 	
    	$txt = \Request::get("txt");
    	$txt = trim($txt);
    	$facility_cond = \Request::has('f')?'s.facility_id='.\Request::get('f'):1;
    	$type = \Request::get('type');
    	$res_tbls = "LEFT JOIN vl_results AS r ON s.id=r.sample_id LEFT JOIN vl_results_qc AS q ON r.id=q.result_id";
    	$rej_tbls = "LEFT JOIN vl_rejected_samples_release AS rj ON s.id=rj.sample_id";

       	$type_tbls = "$res_tbls $rej_tbls";
       	$released_cond = " (q.released=1 OR rj.released=1) ";
    	$sql = "SELECT form_number, art_number, other_id, s.id
    			FROM vl_samples AS s LEFT JOIN vl_patients AS p ON s.patient_id=p.id
    			$type_tbls    			
    			WHERE s.created_at >='".env('QC_START_DATE')."' AND $facility_cond AND $released_cond";
    	$results = $this->db->select("$sql AND form_number='$txt' LIMIT 5");

    	if(count($results)==0){
    		$results = $this->db->select("$sql AND (art_number='$txt' OR other_id='$txt') LIMIT 5");
    		if(count($results)==0){
    			$txt1 = str_replace(' ', '', $txt);
    			$results = $this->db->select("$sql AND (unique_id LIKE '$txt1%' OR other_id LIKE '$txt%') LIMIT 5");
    			if(count($results)==0){
    				return "No match found in released results for $txt";
    			}
    		}
    	}


    	$ret = "<table class='table table-striped table-condensed table-bordered'>
    			<tr><th>Form Number</th><th>Art Number</th><th>Other ID</th><th /></tr>";
    	foreach ($results AS $result){
    		$url = "/direct/result/".$result->id;
    		$print_url = "<a href='javascript:windPop(\"$url\")'>print</a>";
    		$download_url = "<a href='$url?pdf=1'>download</a>";
    		$ret .= "<tr><td>$result->form_number</td><td>".$result->art_number."</td><td>".$result->other_id."</td><td>$print_url | $download_url</td></tr>";
    	}
    	return $ret."</table>";
    }

    private function facilities_with_accounts(){
    	$res = \EID\User::where('facility_id','!=', 0)->whereNotNull('facility_id')->get();
    	$ret = []; 
    	foreach ($res as $r) {
    		$ret[] = $r->facility_id;    		
    	}
    	return $ret;
    }

	public function saveManualDispatch(){
		$samples = $this->db->unprepared("SELECT id FROM `vl_rejected_samples_release` WHERE reject_released_by_id = 1");
		$now = date("Y-m-d H:i:s");
		$by = addslashes('Admin');
		$dispatch_type =  'D';
		$s_str = implode(",", $samples);
		$this->db->unprepared("DELETE FROM vl_results_dispatch WHERE sample_id IN ($s_str)");
		$sql = "INSERT INTO vl_results_dispatch (dispatch_type, dispatch_date, dispatched_by, sample_id) VALUES";
		foreach($samples AS $sample_id){
			$sql .= "('$dispatch_type', '$now', '$by', $sample_id),";
		}
		$sql = trim($sql, ',');
		$this->db->unprepared($sql);

	}


}