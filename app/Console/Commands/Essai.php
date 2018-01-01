<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;


use EID\Mongo;


class Essai extends Command
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
    protected $signature = 'essai:run {--F|facilities} {--H|hours=} {--T|today} {--M|month=} {--Y|year=} {--E|expanded}';

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
        $this->facilities = $this->option('facilities');
        $this->hours = $this->option('hours');
        $this->today = $this->option('today');
        $this->month = $this->option('month');
        $this->year = $this->option('year');
        $this->expanded = $this->option('expanded');       
        $this->_loadData();
        //
        //$this->comment($this->_get('facilities'));
        //$facilities = $this->_get('facilities');
        //$this->mongo->api_facilities->drop();
        //$this->mongo->api_facilities->batchInsert(json_decode($facilities));
        // $samples = $this->_get('samples');
        // $this->mongo->api_samples->drop();
        // $this->mongo->api_samples->batchInsert(json_decode($samples));

        // $this->comment("today is ".$this->option('today'));
        // $this->comment("month is ".$this->option('month'));
        // $this->comment("year is ".$this->argument('year'));

        $this->comment("Engine has stopped at :: ".date('YmdHis'));

    }

    private function _loadData(){
        $num_records = 0;
        if($this->facilities){
            $facilities =  $this->_get('facilities');
            $this->mongo->api_facilities->drop();
            $this->mongo->api_facilities->batchInsert($facilities);

        }elseif($this->hours){
            $samples = $this->_get('samples', "latest_hours=$this->hours");
            if(is_array($samples)){
                foreach ($samples as $sample) {
                   $data = $this->_getDashboardData($sample);
                   $this->mongo->dashboard_new_backend->update(['sample_id'=>(int)$sample->pk],$data, ["upsert"=>true]);
                   $existing_sample = $this->mongo->api_samples->findOne(['pk'=>(int)$sample->pk]);
                   $sample->created_at = Mongo::mDate($sample->created_at);
                   if($existing_sample){
                    $sample->resultsdispatch = $existing_sample->resultsdispatch;
                    $this->mongo->api_samples->update(['pk'=>(int)$sample->pk],$sample, ["upsert"=>true]);
                   }else{
                    $this->mongo->api_samples->insert($sample);
                   }
                   #$this->mongo->api_samples->update(['pk'=>(int)$sample->pk],["upsert"=>true]);
                   $num_records++;
                }
            }
        }elseif($this->today){
            $samples = $this->_get('samples', "changes_today=1");
            if(is_array($samples)){
                foreach ($samples as $sample) {
                   $data = $this->_getDashboardData($sample);
                   $this->mongo->dashboard_new_backend->update(['sample_id'=>(int)$sample->pk],$data, ["upsert"=>true]);
                   $num_records++;
                }
            }            
        }elseif(!empty($this->month) and !empty($this->year)){
            $dates = $this->_getMonthDates($this->year, $this->month);
            $year_month = intval($this->year.str_pad($this->month,2,0,STR_PAD_LEFT));
            
            if($this->expanded){
                $cond = ['created_at'=>['$gte'=>$dates[0], '$lte'=>end($dates)]];
                $this->mongo->api_samples->remove($cond, ['justOne'=>false]);
                foreach ($dates as $date) {                    
                    $samples = $this->_get('samples', "date=$date");
                    $num_samples = count($samples);
                    if(is_array($samples) && $num_samples>0){
                        $this->mongo->api_samples->batchInsert($samples);
                        $num_records += $num_samples;
                    }
                }
            }else{
                $this->_removeSamples(['year_month'=>$year_month]);
                foreach ($dates as $date) {
                    $samples = $this->_get('samples', "date=$date");
                    if(is_array($samples)){
                        foreach ($samples as $sample) {
                           $data = $this->_getDashboardData($sample);
                           $this->mongo->dashboard_new_backend->insert($data);
                           $num_records++;
                        }
                    }               
                }
            }
        }else{
            $this->comment("You are missing some options essai:run {--t|today} {--m|month=} {--y|year=}");
        } 
        $this->comment("$num_records Records updated");
    }

    private function _getDashboardData($sample){
        $data = [];

        $year_month = date("Ym",strtotime($sample->created_at));
        $data["year_month"] = (int)$year_month;
        $data["sample_id"] = (int)$sample->pk;
        $data["vl_sample_id"] = $sample->vl_sample_id;
        $data["patient_unique_id"] = $sample->patient_unique_id;                 

        $data["facility_id"] = (int)$sample->facility->pk;
        $data['district_id'] = isset($sample->facility->district->pk)?(int)$sample->facility->district->pk:0;
        $data['hub_id'] = isset($sample->facility->hub->pk)?(int)$sample->facility->hub->pk:0;
        $age = $this->_getAge($sample->patient->dob, $sample->created_at);
        $data["age"] = $age;
        $data["age_group_id"] = $age;
        $data["gender"] = $this->_getGender($sample->patient->gender);
        $data["treatment_indication_id"] = isset($sample->treatment_indication->code)?(int)$sample->treatment_indication->code:0;//treatment_initiation

        $data["regimen"] = isset($sample->current_regimen->code)?(int)$sample->current_regimen->code:0;//current regimen
        $data["regimen_line"] = isset($sample->treatment_line->code)?(int)$sample->treatment_line->code:0;
        $data["regimen_time_id"] = $this->_getRegTime($sample->treatment_initiation_date, $sample->created_at);

        $data["pregnancy_status"] = $sample->get_pregnant_display?$sample->get_pregnant_display:"UNKNOWN";
        $data["breastfeeding_status"] = $sample->get_breast_feeding_display?$sample->get_breast_feeding_display:"UNKNOWN";
        $data["active_tb_status"] = $sample->get_active_tb_status_display?$sample->get_active_tb_status_display:"UNKNOWN";

        $data["sample_type_id"] = $sample->sample_type=='D'?1:2;
        $suppressed = isset($sample->result->get_suppressed_display)?$sample->result->get_suppressed_display:"UNKNOWN";
        $data["sample_result_validity"] = $suppressed=='NO'||$suppressed=='YES'?'valid':'invalid';
        $data["suppression_status"] = $suppressed!='UNKNOWN'?strtolower($suppressed):$suppressed;

        $data["tested"]=!empty($sample->result)?"yes":"no";
        $data["rejection_reason"] = isset($sample->verification->rejection_reason->code)?$this->_getRejectionCat($sample->verification->rejection_reason->code):"UNKNOWN";
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

    private function _removeSamples($cond=[], $justOne=false){
        $result=$this->mongo->dashboard_new_backend->remove($cond, ['justOne'=>$justOne]);
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

    private static function _getRejectionCat($val){
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
    }

    private function _get($resouce, $params_str=""){
        $api = env('API')."/api/$resouce/?$params_str";
        $api_key = env('API_KEY');
        $curl_command = "curl -X GET '$api' -H 'Authorization: Token $api_key'";
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




}
