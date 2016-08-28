<?php namespace EID\Http\Controllers;

use EID\Http\Requests;
use EID\Http\Controllers\Controller;

use EID\Dashboard;
use EID\SamplesData;
use EID\TreatmentIndication;

use EID\Hub;
use EID\District;
use EID\Facility;

use EID\Mongo;

use Validator;
use Lang;
use Redirect;
use Request;
use Session;

class DashboardController extends Controller {
	//private $mongo = \MongoClient::connect('vldash');

	public function __construct(){
		$this->months=\MyHTML::initMonths();
		$this->mongo=Mongo::connect();
		$this->conditions=$this->_setConditions();

		//$this->middleware('auth');
	}

	public function init(){
		$to_date=date("Ym");
		$fro_date=$this->_dateNMonthsBack();
		return view("vdash",compact("fro_date","to_date"));
	}

	public function downloadCsv($type)
	{
		if(Request::ajax()) {
      		$data = Input::all();
      		print_r($data);die;
        }

		//$data = Item::get()->toArray();
		//return Excel::create('itsolutionstuff_example', function($excel) use ($data) {
		//	$excel->sheet('mySheet', function($sheet) use ($data)
	    //    {
		//		$sheet->fromArray($data);
	    //    });
		//})->download($type);
	}
	private function _setConditions(){
		extract(\Request::all());
		if((empty($fro_date) && empty($to_date))||$fro_date=='all' && $to_date=='all'){
			$to_date=date("Ym");
			$fro_date=$this->_dateNMonthsBack();
		}
		$conds=[];
		$conds['$and'][]=['year_month'=>  ['$gte'=> (int)$fro_date] ];
		$conds['$and'][]=[ 'year_month'=>  ['$lte'=> (int)$to_date] ];
		if(!empty($districts)&&$districts!='[]') $conds['$and'][]=[ 'district_id'=>  ['$in'=> json_decode($districts)] ];
		if(!empty($hubs)&&$hubs!='[]') $conds['$and'][]=[ 'hub_id'=>  ['$in'=> json_decode($hubs)] ];
		if(!empty($age_ids)&&$age_ids!='[]') $conds['$and'][]=[ 'age_group_id'=>  ['$in'=> json_decode($age_ids)] ];
		if(!empty($genders)&&$genders!='[]') $conds['$and'][]=[ 'gender'=>  ['$in'=> json_decode($genders)] ];
		if(!empty($regimens)&&$regimens!='[]') $conds['$and'][]=[ 'regimen_group_id'=>  ['$in'=> json_decode($regimens)] ];
		if(!empty($lines)&&$lines!='[]') $conds['$and'][]=[ 'regimen_line'=>  ['$in'=> json_decode($lines)] ];

		//print_r($conds);

		return $conds;
	}


	public function other_data(){
		$hubs=iterator_to_array($this->mongo->hubs->find());
		$districts=iterator_to_array($this->mongo->districts->find());
		$facilities=iterator_to_array($this->mongo->facilities->find());
		return compact("hubs","districts","facilities");
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


	/*public function live(){
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
	}*/

	public function live(){
		$whole_numbers=$this->_wholeNumbers();
		//return ['y'=>8,'a'=>9,'c'=>13,'x'=>19];
		$t_indication=$this->_treatmentIndicationNumbers();
		$f_numbers=$this->_facilityNumbers();
		$dist_numbers=$this->_districtNumbers();
		$drn_numbers=$this->_durationNumbers();
		$reg_groups=$this->_regimenGroupNumbers();
		$reg_times=$this->_regimenTimeNumbers();
		$line_numbers=$this->_lineNumbers();
		return compact("whole_numbers","t_indication","f_numbers","dist_numbers","drn_numbers","reg_groups","reg_times","line_numbers");
	}

	/*private function _wholeNumbers($conds){
		$cols=" SUM(samples_received) AS samples_received,
				SUM(suppressed) AS suppressed,
				SUM(valid_results) AS valid_results,
				SUM(rejected_samples) AS rejected_samples";
		return SamplesData::getSamplesData($cols,$conds)->first();
	}*/


	private function _wholeNumbers(){
		$grp=[];
		$grp['_id']=null;
		$grp['samples_received']=['$sum'=>'$samples_received'];
		$grp['suppressed']=['$sum'=>'$suppressed'];
		$grp['valid_results']=['$sum'=>'$valid_results'];
		$grp['rejected_samples']=['$sum'=>'$rejected_samples'];
		$res=$this->mongo->dashboard_data->aggregate(['$match'=>$this->conditions],['$group'=>$grp]);
		$ret=isset($res['result'][0])?$res['result'][0]:[];
		return $ret;
	}

	/*private function _treatmentIndicationNumbers($conds){
		$cols="treatment_indication_id,SUM(samples_received) AS samples_received";
		$res=SamplesData::getSamplesData($cols,$conds,"treatment_indication_id");
		$ret=[];
		foreach($res AS $row) $ret[$row->treatment_indication_id]=$row->samples_received;
		return $ret;
	}*/

	private function _treatmentIndicationNumbers(){
		$grp=[];
		$grp['_id']='$treatment_indication_id';
		$grp['samples_received']=['$sum'=>'$samples_received'];
		$res=$this->mongo->dashboard_data->aggregate(['$match'=>$this->conditions],['$group'=>$grp]);	
		$ret=[];

		if(isset($res['result'])) foreach ($res['result'] as $row) $ret[$row['_id']]=$row['samples_received'];
		return $ret;
	}

	private function _lineNumbers(){
		$grp=[];
		$grp['_id']='$regimen_line';
		$grp['samples_received']=['$sum'=>'$samples_received'];
		$res=$this->mongo->dashboard_data->aggregate(['$match'=>$this->conditions],['$group'=>$grp]);	
		$ret=[];

		if(isset($res['result'])) foreach ($res['result'] as $row) $ret[$row['_id']]=$row['samples_received'];
		return $ret;
	}

	/*private function _facilityNumbers($conds){
		$cols=" f.facility_id,f.name,
				SUM(samples_received) AS samples_received,
				SUM(suppressed) AS suppressed,
				SUM(valid_results) AS valid_results,
				SUM(rejected_samples) AS rejected_samples,
				SUM(dbs_samples) AS dbs_samples,
				SUM(total_results) AS total_results
				";
		return SamplesData::getSamplesData($cols,$conds,'f.facility_id');
	}*/

	private function _facilityNumbers(){
		$grp=[];
		$grp['_id']='$facility_id';
		$grp['samples_received']=['$sum'=>'$samples_received'];
		$grp['suppressed']=['$sum'=>'$suppressed'];
		$grp['valid_results']=['$sum'=>'$valid_results'];
		$grp['rejected_samples']=['$sum'=>'$rejected_samples'];
		$grp['dbs_samples']=['$sum'=>'$dbs_samples'];
		$grp['total_results']=['$sum'=>'$total_results'];
		$res=$this->mongo->dashboard_data->aggregate(['$match'=>$this->conditions],['$group'=>$grp]);
		return isset($res['result'])?$res['result']:[];
	}

	/*private function _districtNumbers($conds){
		$cols=" d.district_id,d.name,
				SUM(samples_received) AS samples_received,
				SUM(suppressed) AS suppressed,
				SUM(valid_results) AS valid_results,
				SUM(rejected_samples) AS rejected_samples,
				SUM(dbs_samples) AS dbs_samples,
				SUM(total_results) AS total_results
				";
		return SamplesData::getSamplesData($cols,$conds,'d.district_id');
	}*/

	private function _districtNumbers(){
		$grp=[];
		$grp['_id']='$district_id';
		$grp['samples_received']=['$sum'=>'$samples_received'];
		$grp['patients_tested']=['$sum'=>'$patients_tested'];
		$grp['suppressed']=['$sum'=>'$suppressed'];
		$grp['valid_results']=['$sum'=>'$valid_results'];
		$grp['rejected_samples']=['$sum'=>'$rejected_samples'];
		$grp['dbs_samples']=['$sum'=>'$dbs_samples'];
		$grp['dbs_patients']=['$sum'=>'$dbs_patients_tested'];
		$grp['total_results']=['$sum'=>'$total_results'];
		$res=$this->mongo->dashboard_data->aggregate(['$match'=>$this->conditions],['$group'=>$grp]);
		return isset($res['result'])?$res['result']:[];
	}

	/*private function _durationNumbers($conds){
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
	}*/

	private function _durationNumbers(){
		$grp=[];
		$grp['_id']='$year_month';
		$grp['samples_received']=['$sum'=>'$samples_received'];
		$grp['suppressed']=['$sum'=>'$suppressed'];
		$grp['valid_results']=['$sum'=>'$valid_results'];
		$grp['dbs_samples']=['$sum'=>'$dbs_samples'];
		$grp['sample_quality_rejections']=['$sum'=>'$sample_quality_rejections'];
		$grp['eligibility_rejections']=['$sum'=>'$eligibility_rejections'];
		$grp['incomplete_form_rejections']=['$sum'=>'$incomplete_form_rejections'];

		$res=$this->mongo->dashboard_data->aggregate(['$match'=>$this->conditions],['$group'=>$grp], ['$sort'=>["_id"=>1]]);
		return isset($res['result'])?$res['result']:[];
	}

	private function _regimenGroupNumbers(){
		$grp=[];
		$grp['_id']='$regimen_group_id';
		$grp['samples_received']=['$sum'=>'$samples_received'];
		$grp['suppressed']=['$sum'=>'$suppressed'];
		$grp['total_results']=['$sum'=>'$total_results'];
		$grp['valid_results']=['$sum'=>'$valid_results'];

		$res=$this->mongo->dashboard_data->aggregate(['$match'=>$this->conditions],['$group'=>$grp]);
		return isset($res['result'])?$res['result']:[];
	}

	private function _regimenTimeNumbers(){
		$grp=[];
		$grp['_id']='$regimen_time_id';
		$grp['samples_received']=['$sum'=>'$samples_received'];
		$grp['suppressed']=['$sum'=>'$suppressed'];
		$grp['total_results']=['$sum'=>'$total_results'];
		$grp['valid_results']=['$sum'=>'$valid_results'];
		
		$res=$this->mongo->dashboard_data->aggregate(['$match'=>$this->conditions],['$group'=>$grp]);
		return isset($res['result'])?$res['result']:[];
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



	/*

	I would say that he is a ‘master’, if it were not for my belief that no one ‘masters’ anything, that each finds or makes his candle, then tries to see by the guttering light. Mum has made a good candle. And Mum has good eyes.

	Gwendolyn Brooks


	Whether you are witness or executioner, the victim whose humanity you can never erase
	knows with clarity, more solid than granite that no matter which side you are on,
	any day or night, an injury to one remains an injury to all
	some where on this coninent, the voice of the ancient warns, that those who shit on the road, will find flies on their way back..

	*/

}