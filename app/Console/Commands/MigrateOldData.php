<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;


use EID\Mongo;


class MigrateOldData extends Command
{
   
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Old data into Mongo DB';

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
        $this->migrateData();
    
        $this->comment("Engine has stopped at :: ".date('YmdHis'));

    }

    private function migrateData(){
       
        if (($handle = fopen(env('RAW_CSV'), "r")) !== FALSE) {
          while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $this->comment($row[0]);
            if($row[0]!='id'){
                $data = $this->_getDashboardData($row);
                $this->mongo->dashboard_new_backend->insert($data);
            }
          }
          fclose($handle);
        }
    }
   

    

    private function _getDashboardData($row){
        $data = [];

        $year_month = date("Ym",strtotime($row[10]));
        $data["year_month"] = (int)$year_month;
        $data["sample_id"] = "OLDID$row[0]";
        $data["vl_sample_id"] = "OLDID$row[0]";
        $data["patient_unique_id"] = "OLDID$row[0]";                

        $data["facility_id"] = (int)$row[5];
        $data['district_id'] = (int)$row[6];
        $data['hub_id'] = (int)$row[6];
        $age = $this->_getAge($row[18], $row[10]);
        $data["age"] = $age>0?$age:0;
        $data["age_group_id"] = $data["age"];
        $data["gender"] = $this->_getGender($row[16]);
        $data["treatment_indication_id"] =0;

        $data["regimen"] = $row[49]!='NULL'?$row[49]:0;
        $data["regimen_line"] = $row[50]!='NULL'?$row[50]:0;
        $data["regimen_time_id"] = 0;

        $data["pregnancy_status"] = "UNKNOWN";
        $data["breastfeeding_status"] = "UNKNOWN";
        $data["active_tb_status"] = "UNKNOWN";

        $data["sample_type_id"] = $row[12]=='Plasma'?2:1;
        $suppressed = $row[40];
        $data["sample_result_validity"] = $suppressed=='NO'||$suppressed=='YES'?'valid':'invalid';
        $data["suppression_status"] = $suppressed!='UNKNOWN'?strtolower($suppressed):$suppressed;

        $data["tested"]=$suppressed=='NO'||$suppressed=='YES'||$suppressed=='UNKNOWN'?"yes":"no";
        $data["rejection_reason"] = $row[51]!='NULL'?$row[51]:"UNKNOWN";
        return $data;
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




}
