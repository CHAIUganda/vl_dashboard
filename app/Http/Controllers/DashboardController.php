<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\Dashboard;
use EID\SamplesData;
use EID\TreatmentIndication;

use EID\Hub;
use EID\District;
use EID\Facility;

use Validator;
use Lang;
use Redirect;
use Request;
use Session;

class DashboardController extends Controller {

/*	$scope.loading=true;
        

       
        */

	public function __construct(){
		$this->months=\MyHTML::initMonths();
		//$this->middleware('auth');
	}

	public function dash($fro_date="",$to_date=""){
		if(empty($fro_date) && empty($to_date)){
			$n_months=$this->_latestNMonths(6);
			$fro_date=$n_months[0];
			$to_date=end($n_months);
		}
		return Dashboard::getSampleData($fro_date,$to_date);	
	}

	public function other_data(){
		$ret=[];
		$ret["hubs"]=Hub::all();
		$ret["districts"]=District::all();
		$ret["facilities"]=Facility::all();
		return json_encode($ret);
	}

	private function _latestNMonths($n=12){
        $ret=[];
        $m=date('m');
        $y=date('Y');
        for($i=1;$i<=$n;$i++){
            if($m==0){
                $m=12;
                $y--;
            }
            array_unshift($ret, $y.str_pad($m, 2,0, STR_PAD_LEFT));
            $m--;
        }
        return $ret;
    }

	public function show($time=""){
		if(empty($time)) $time=date("Y");

		return view('d');
	}

	public function live(){
		extract(\Request::all());
		if(empty($fro_date) && empty($to_date)){
			$n_months=$this->_latestNMonths(12);
			$fro_date=$n_months[0];
			$to_date=end($n_months);
		}
		$conds=" `year_month`>=$fro_date AND `year_month`<=$to_date";
		$conds.=!empty($districts)?" AND f.district_id in ($districts) ":"";
		$conds.=!empty($hubs)?" AND f.hub_id in ($hubs) ":"";
		$conds.=!empty($age_ids)?" AND s.age_group_id in ($age_ids) ":"";

		$ret=[];

		$sd_numbers=$this->_wholeNumbers($conds);
		$ret["samples_received"]=$sd_numbers->samples_received; 
		$ret["suppressed"]=$sd_numbers->suppressed;
		$ret["valid_results"]=$sd_numbers->valid_results;
		$ret["rejected_samples"]=$sd_numbers->rejected_samples;

		$ret["facility_numbers"]=$this->_facilityNumbers($conds);
		$ret["district_numbers"]=$this->_districtNumbers($conds);
		$ret["duration_numbers"]=$this->_durationNumbers($conds);

		$ret["treatment_indication"]=$this->_treatmentIndicationNumbers($conds);
		return json_encode($ret);
	}

	private function _wholeNumbers($conds){
		$cols=" SUM(samples_received) AS samples_received,
				SUM(suppressed) AS suppressed,
				SUM(valid_results) AS valid_results,
				SUM(rejected_samples) AS rejected_samples";
		return SamplesData::getSamplesData($cols,$conds)->first();
	}

	private function _wholeTINumbers($conds){
		$cols=" SUM(cd4_less_than_500) AS cd4_less_than_500,
				SUM(pmtct_option_b_plus) AS pmtct_option_b_plus	,
				SUM(children_under_15) AS children_under_15,
				SUM(other_treatment) AS other_treatment,
				SUM(treatment_blank_on_form) AS treatment_blank_on_form,
				SUM(tb_infection) AS tb_infection";
		return TreatmentIndication::getTIData($cols,$conds)->first();
	}

	private function _treatmentIndicationNumbers($conds){
		$cols="treatment_indication_id,SUM(samples_received) AS samples_received";
		$res=SamplesData::getSamplesData($cols,$conds,"treatment_indication_id");
		$ret=[];
		foreach($res AS $row) $ret[$row->treatment_indication_id]=$row->samples_received;
		return $ret;
	}

	private function _facilityNumbers($conds){
		$cols=" f.facility_id,f.name,
				SUM(samples_received) AS samples_received,
				SUM(suppressed) AS suppressed,
				SUM(valid_results) AS valid_results,
				SUM(rejected_samples) AS rejected_samples,
				SUM(dbs_samples) AS dbs_samples,
				SUM(total_results) AS total_results
				";
		return SamplesData::getSamplesData($cols,$conds,'f.facility_id');
	}

	private function _districtNumbers($conds){
		$cols=" d.district_id,d.name,
				SUM(samples_received) AS samples_received,
				SUM(suppressed) AS suppressed,
				SUM(valid_results) AS valid_results,
				SUM(rejected_samples) AS rejected_samples,
				SUM(dbs_samples) AS dbs_samples,
				SUM(total_results) AS total_results
				";
		return SamplesData::getSamplesData($cols,$conds,'d.district_id');
	}

	private function _durationNumbers($conds){
		$cols=" `year_month`,
				SUM(samples_received-dbs_samples) AS plasma_samples,
				SUM(dbs_samples) AS dbs_samples,
				SUM(suppressed) AS suppressed,
				SUM(valid_results) AS valid_results,
				SUM(sample_quality_rejections) AS sample_quality_rejections,
				SUM(eligibility_rejections) AS eligibility_rejections,
				SUM(incomplete_form_rejections) AS incomplete_form_rejections				
				";
		return SamplesData::getSamplesData($cols,$conds,'year_month');
	}


	private function median($arr){
		sort($arr);
		$quantity=count($arr);
		$half_quantity=(int)($quantity/2);
		$ret=0;
		if($quantity%2==0){
			 $ret=($arr[($half_quantity-1)]+$arr[$half_quantity])/2;
		}else{
			$ret=$arr[$half_quantity];
		}
		return $ret;
	}


	private function totalSums($totals){
		$ret=0;
		foreach ($totals as $lvl_id => $reg_data) {
			foreach ($reg_data as $reg_id => $dist_data) {
				$ret+=array_sum($dist_data);				
			}
		}
		return $ret;
	}


	private function getTotalsByMonth($arr){
		$ret=$this->months;
		foreach ($arr as $lvl_id => $reg_data) {
			foreach ($reg_data as $reg_id => $dist_data) {
				foreach ($dist_data as $month_data) {
					foreach ($month_data as $mth => $val) $ret[$mth]+=$val;			
				}								
			}
		}
		return $ret;
	}

	private function getAverageRate($arr_up,$arr_down){
		$ret=0;
		$ttl_up=0;
		$ttl_down=0;
		foreach ($arr_up as $lvl_id => $reg_data) {
			foreach ($reg_data as $reg_id => $dist_data) {
				foreach ($dist_data as $dist_id => $month_data) {
					$ttl_up+=array_sum($month_data);
					$ttl_down+=array_sum($arr_down[$lvl_id][$reg_id][$dist_id]);
				}
			}
		}
		$ret=$ttl_down>0?($ttl_up/$ttl_down)*100:0;
		$ret=round($ret,1);
		return $ret;
	}

	private function getAverageRatesByMonth($arr_up,$arr_down){
		$up_res=$this->months;
		$down_res=$this->months;
		$ret=$this->months;
		foreach ($arr_up as $lvl_id => $reg_data) {
			foreach ($reg_data as $reg_id => $dist_data) {
				foreach ($dist_data as $dist_id => $month_data) {
					foreach ($month_data as $mth => $val){
						$up_res[$mth]+=$val;
						$down_res[$mth]+=$arr_down[$lvl_id][$reg_id][$dist_id][$mth];
					}		
				}								
			}
		}

		foreach ($up_res as $m => $v) {			
			$ret_val=$down_res[$m]>0?($up_res[$m]/$down_res[$m])*100:0;
			$ret[$m]=round($ret_val,1);
		}
		return $ret;
	}

	private function facilityNumbers($counts,$positive_counts,$init_counts){
		$ret=[];
		foreach ($counts as $k => $v) {
			extract($v);			
			$abs_positives=array_key_exists($k, $positive_counts)?$positive_counts[$k]["value"]:0;
			$positivity_rate=$value>0?($abs_positives/$value)*100:0;
			$positivity_rate=round($positivity_rate,1);

			$initiated=array_key_exists($k, $init_counts)?$init_counts[$k]["value"]:0;
			$initiation_rate=$abs_positives>0?($initiated/$abs_positives)*100:0;
			$initiation_rate=round($initiation_rate);


			$ret[]=[
				"facility_id"=>$facility_id,
				"facility_name"=>$facility_name,
				"district_id"=>$district_id,
				"region_id"=>$region_id,
				"level_id"=>$level_id,
				"abs_positives"=>$abs_positives,
				"total_results"=>$value,
				"positivity_rate"=>$positivity_rate,
				"initiation_rate"=>$initiation_rate
				];
				
		}
		return $ret;
	}

	private function numberMaps($counts,$positive_counts,$init_counts){
		$ret=[];
		foreach ($counts as $k => $v) {
			extract($v);
			$abs_positives=array_key_exists($k, $positive_counts)?$positive_counts[$k]["value"]:0;
			$positivity_rate=$value>0?($abs_positives/$value)*100:0;
			$positivity_rate=round($positivity_rate,1);

			$initiated=array_key_exists($k, $init_counts)?$init_counts[$k]["value"]:0;
			$initiation_rate=$abs_positives>0?($initiated/$abs_positives)*100:0;
			$initiation_rate=round($initiation_rate);


			$ret[]=[
				"facility_id"=>$facility_id,
				"month"=>$month,				
				"facility_name"=>$facility_name,
				"district_id"=>$district_id,
				"region_id"=>$region_id,
				"level_id"=>$level_id,
				"abs_positives"=>$abs_positives,
				"total_results"=>$value,
				"positivity_rate"=>$positivity_rate,
				"initiation_rate"=>$initiation_rate
				];
				
		}
		return $ret;
	}



	/*

	I would say that he is a ‘master’, if it were not for my belief that no one ‘masters’ anything, that each finds or makes his candle, then tries to see by the guttering light. Mum has made a good candle. And Mum has good eyes.

	Gwendolyn Brooks


	Whether you are witness or executioner, the victim whose humanity you can never erase
	knows with clarity, more solid than granite that no matter which side you are on,
	any day or night, an injury to one remains an injury to all
	some where on this coninent, the voice of the ancient warns, that those who shit on the road, will find flies on their way back..

	*/

}