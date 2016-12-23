<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\LiveData;

class ResultsController extends Controller {

	public function getIndex(){
		$printed=\Request::get("printed");
		$printed=empty($printed)?'NO':$printed;
		return view('results.index', compact('printed'));
	}

	public function getData(){
		$printed=\Request::get("printed");
		$printed=empty($printed)?'NO':$printed;

		$results = LiveData::getResultsList($printed);
		return \Datatables::of($results)
				->addColumn('sample_checkbox', function($result){
					session(['facility'=>$result->facility]);
					return "<input type='checkbox' name='samples[]' value=$result->sample_id>";
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

	public function getResult($id='x'){
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
		
		if(\Request::has('pdf')){
			$s_arr = $id!='x'?[$id]:explode(",", $slctd_samples_str);
			$sql = "INSERT INTO vl_facility_downloads (sample_id, downloaded_by, downloaded_on) VALUES";
			foreach ($s_arr as $smpl) {
				$sql .= "($smpl, '".\Auth::user()->email."', '".date('Y-m-d H:i:s')."'),";				
			}

			$sql = trim($sql, ',');
			\DB::connection('live_db')->unprepared($sql);
			$pdf = \PDF::loadView('results.pdfresults', compact("vldbresult"));
			return $pdf->download('vl_results_'.session('facility').'.pdf');
			//return \PDF::loadFile('http://www.github.com')->inline('github.pdf');
		}

		
		return view('results.result', compact("vldbresult", "printed"));
	}

	public function log_printing(){
		$printed = \Request::get('printed');
		$samples = \Request::get('s');
		$by = \Auth::user()->email;
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
		$facilities = LiveData::select('*')->from('vl_facilities');
		if(!empty(\Auth::user()->hub_id)){
			$facilities = $facilities->where('hubID', \Auth::user()->hub_id)->get();
		}elseif(!empty(\Auth::user()->facility_id)){
			$facilities = $facilities->where('id', \Auth::user()->facility_id)->get();
		}
		return view('results.facilities', compact('facilities'));
	}

}