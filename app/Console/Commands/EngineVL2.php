<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;


use EID\Mongo;


class EngineVL2 extends Command
{
    /*
    Initial api pick - year(y) and month(m)  - when sample was created
    New samples today or results released today - today(t)
    Latest changes in lastest number of hours - hours(h)
    Get Facilities facilities(f)
    Get Districts districts(d)
    Get Hubs hubs(h)
    */
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enginevl2:run {--M|months=} {--L|limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data from the Viral Load 2 API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->mongo=Mongo::connect();
        $this->db = \DB::connection('direct_db');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '2024M');
        //
        $this->comment("Engine has started at :: ".date('YmdHis'));
        //$this->facilities = $this->option('facilities');
        $this->months = $this->option('months');
        $this->months = empty($this->months)?3:$this->months;

        $this->limit = $this->option('limit');
        $this->limit = empty($this->limit)?10:$this->limit;

        $to = date("Y-m-d", (strtotime(date("Y-m-d"))-(24*60*60)));
        $fro = $this->_getFro();
        $this->cond = "date(s.created_at)>='$fro' AND  date(s.created_at)<='$to'";

        $this->comment($this->cond);
        //print_r($this->_lastNMonths());

        $this->_load();
        //$this->comment($this->_removeSamples());
        $this->comment("Engine has stopped at :: ".date('YmdHis'));

    }

    private function _getData($start){
    	
    	$sql = "SELECT s.*, f.district_id, f.hub_id, p.gender, p.dob,
                a1.code AS treatment_indication,
                a2.code AS current_regimen,
                a2.tag AS treatment_line,
                a3.tag AS rejection_reason,
                r.suppressed, r.result_alphanumeric
                FROM vl_samples s
                LEFT JOIN vl_patients p ON s.patient_id=p.id
    	        LEFT JOIN vl_verifications v ON s.id=v.sample_id
    	        LEFT JOIN vl_results r ON s.id=r.sample_id
                LEFT JOIN backend_facilities f ON s.facility_id=f.id
                LEFT JOIN backend_appendices a1 ON s.treatment_indication_id=a1.id
                LEFT JOIN backend_appendices a2 ON s.current_regimen_id=a2.id
                LEFT JOIN backend_appendices a3 ON v.rejection_reason_id=a3.id
                LEFT JOIN backend_appendices a4 ON v.rejection_reason_id=a3.id
    	        WHERE $this->cond 
    	        LIMIT $start, $this->limit";
    	return $this->db->select($sql);
    	#$this->comment($sql);
    }

    private function _load(){
    	$this->_removeSamples();
    	$count_sql = "SELECT count(id) AS num FROM vl_samples s WHERE $this->cond";
    	$count = collect($this->db->select($count_sql))->first()->num;
    	//$this->comment($count_sql);
    	//$this->comment($count);
    	$loops = ceil($count/$this->limit);

    	for ($i=0; $i < $loops; $i++) { 
    		$start = $i*$this->limit;
    		$this->comment("$i start is $start");

    		$samples = $this->_getData($start);
            $s_arr = [];
            foreach ($samples as $sample) {
                 $s_arr[] = $this->_getDashboardData($sample);
            }
            $this->mongo->dashboard_new_backend->batchInsert($s_arr);
    	}
 
    }		

    private function _getDashboardData($sample){
        $data = [];

        $year_month = date("Ym",strtotime($sample->created_at));
        $data["year_month"] = (int)$year_month;
        $data["sample_id"] = (int)$sample->id;
        $data["vl_sample_id"] = $sample->vl_sample_id;
        $data["patient_unique_id"] = $sample->patient_unique_id;                 

        $data["facility_id"] = (int)$sample->facility_id;
        $data['district_id'] = isset($sample->district_id)?(int)$sample->district_id:0;
        $data['hub_id'] = isset($sample->hub_id)?(int)$sample->hub_id:0;
        $age = $this->_getAge($sample->dob, $sample->created_at);
        $data["age"] = $age;
        $data["age_group_id"] = $age;
        $data["gender"] = $this->_getGender($sample->gender);
        $data["treatment_indication_id"] = isset($sample->treatment_indication)?(int)$sample->treatment_indication:0;//treatment_initiation

        $data["regimen"] = isset($sample->current_regimen)?(int)$sample->current_regimen:0;//current regimen
        $data["regimen_line"] = isset($sample->treatment_line)?(int)$sample->treatment_line:0;
        $data["regimen_time_id"] = $this->_getRegTime($sample->treatment_initiation_date, $sample->created_at);

        $data["pregnancy_status"] = $this->_choice($sample->pregnant);
        $data["breastfeeding_status"] = $this->_choice($sample->breast_feeding); 
        $data["active_tb_status"] = $this->_choice($sample->active_tb_status);

        $data["sample_type_id"] = $sample->sample_type=='D'?1:2;
        $suppressed = $this->_choice($sample->suppressed);
        $data["sample_result_validity"] = $suppressed=='No'||$suppressed=='Yes'?'valid':'invalid';
        $data["suppression_status"] = $suppressed!='UNKNOWN'?strtolower($suppressed):$suppressed;

        $data["tested"]=!empty($sample->result_alphanumeric)?"yes":"no";
        $data["rejection_reason"] = isset($sample->rejection_reason)?$this->_getRejectionCat($sample->rejection_reason):"UNKNOWN";
        return $data;
    }

    private function _loadHubs(){
        $this->mongo->hubs->drop();
        $res=LiveData::getHubs();
        foreach($res AS $row){
            $data=['id'=>$row->id,'name'=>$row->hub];
            $this->mongo->hubs->insert($data);
        }
    }

    private function _removeSamples(){
    	$last_n_months = $this->_lastNMonths();
    	$cond = ['year_month'=>['$in'=>$last_n_months]];
        $result=$this->mongo->dashboard_new_backend->remove($cond, ['justOne'=>false]);
        return $result['n'];//return 1 for when a record has been successfully removed,0 when nothing has been found.
    }

    private function _getAge($dob, $date_then){
        if(!empty($dob) && !empty($date_then)){
            $diff = date_diff(date_create($dob),date_create($date_then));
            return $diff->y;
        }else{
            return -1;
        }        
    }

    private function _getGender($val){
        return $val=='M'||$val=='F'?strtolower($val):"x";
    }

    private function _getRegTime($initiation_date, $created_at){
        //2628000 is the number of seconds in a month
        //{1:'6-12 months',2:'1-2 years',3:'2-3 years',4:'3-5 years',5:'5+ years'}
        $time = round((strtotime($created_at) - strtotime($initiation_date))/2628000);
        $code = 0;
        switch ($time) {
            case $time>=6 && $time<=12:
               $code = 1;
                break;
            case$time>=13 && $time<=24:
               $code = 2;
                break;
            case $time>=25 && $time<=36:
               $code = 3;
                break;
            case $time>=35 && $time<=60:
               $code = 4;
                break;
            case $time>=60:
               $code = 5;
                break;
            
            default:
                return 0;
                break;
        }
        return $code;
    }

    private static function _getRejectionCat($tag){
        if(strpos($tag, 'eligibility')){
            $ret = "eligibility";
        }else if(strpos($tag, 'data_quality')){
            $ret = "incomplete_form";
        }else if(strpos($tag, 'sample_quality')){
            $ret = "quality_of_sample";
        }else{
            $ret = "UNKNOWN";
        }
        return $ret;
    }

    /*private static function _getRejectionCat($val){
        $eligibility = [77,78,14,64,65,76];
        $incomplete_form = [4,71,72,69,70,67,68,79,80,87,88,86, 61,81,82];
        $quality_of_sample = [9,60,74,10,59,8,63,75,2,7,85,1,5,62 ,3,15,83,84];

        if (in_array((int)$val, $eligibility)){
            $ret = "eligibility";
        }else if(in_array((int)$val, $incomplete_form)){
            $ret = "incomplete_form";
        }else if(in_array((int)$val, $quality_of_sample)){
            $ret = "quality_of_sample";
        }else{
            $ret = "UNKNOWN";
        }
        return $ret;
    }*/

    private function _get($resouce, $params_str=""){
        $api = env('API')."/api/$resouce/?$params_str";
        $api_key = env('API_KEY');
        $curl_command = "curl -X GET '$api' -H 'Authorization: Token $api_key'";
        $results = exec($curl_command);
        return json_decode($results);
    }

    private function _post($resouce, $params_str=""){
        $api = env('API')."/api/$resouce/";
        $api_key = env('API_KEY');
        $curl_command = "curl -X POST '$api' -H 'Authorization: Token $api_key' -d '$params_str' ";
        $results = exec($curl_command);
        return json_decode($results);
    }

    private function _getMonthDates($year, $month){
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $ret = [];
        for ($i=1; $i <= $days ; $i++) { 
           $date_str = "$year-$month-$i";
           $ret[] = date("Y-m-d", strtotime($date_str));
        }
        return $ret;
    }

    private function _getFro(){
    	$fro_m = (date('m')-$this->months)+1;
    	$yr = date('Y');
    	if($fro_m<1){
    		$fro_m += 12;
    		$yr -= 1;
    	}
    	
    	return $yr."-".str_pad($fro_m,2,0,STR_PAD_LEFT).'-'.'01';
    }

    private function _lastNMonths(){
    	$months = [];
    	for ($i = 0; $i < $this->months ; $i++) {  $months[] = (int)date("Ym", strtotime( date( 'Y-m-01' )." -$i months")); }
    	return $months;
    }

    private function _choice($val){
        if($val=='N'){
            return 'No';
        }else if ($val=='Y'){
            return 'Yes';
        }else{
            return 'UNKNOWN';
        }
    }




}
