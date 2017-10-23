<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\LiveData;
use EID\Hub;
use EID\Facility;
use EID\Ip;
use EID\District;
use EID\Dashboard;

use EID\TreatmentIndication;
use EID\SamplesData;
use EID\RegimenData;
use EID\Mongo;


class Engine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engine:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Engine to help generate data for the dashboard';

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
        ini_set('memory_limit', '2500M');
        //
        $this->comment("Engine has started at :: ".date('YmdHis'));

        
    
        
        $this->_loadData();
        
        $this->comment("Engine has stopped at :: ".date('YmdHis'));

    }

    private function _loadData(){
        
        
        $facilities=$this->_getFacilities();
        $turnAroundTimeInMonths=env('TAT_MONTHS', 3);//Number of Months to consider for worst turn -around-time
       
        for ($month=0; $month < $turnAroundTimeInMonths; $month++) { 
            $turnAroundYear=intval(date("Y",strtotime("-$month month")));
            $turnAroundMonth=intval(date("m",strtotime("-$month month")));

            $samples_records = LiveData::getSamplesRecordsByMonth($turnAroundYear,$turnAroundMonth);
            $recordsInserted=0;
            $recordsRemoved=0;
            
            try {
                foreach($samples_records AS $s){
                    $data=[];
                    $year_month = $turnAroundYear.str_pad($s->monthOfYear,2,0,STR_PAD_LEFT);
            
                    $data["sample_id"]=isset($s->id)? (int)$s->id: 0;
                    $data["vl_sample_id"]=isset($s->vlSampleID)? $s->vlSampleID: 0;
                    $data["patient_unique_id"]=isset($s->patientUniqueID)? $s->patientUniqueID: "UNKNOWN";//
                    $data["year_month"] = (int)$year_month;
                    
                        if(array_key_exists(intval($s->facilityID), $facilities)){
                            $facility= $facilities[$s->facilityID];
                            $data['district_id']=isset($facility->districtID)?$facility->districtID:0;
                            $data['hub_id']=isset($facility->hubID)?(int)$facility->hubID:0;
                        }else{
                            echo "facilityID: ". $s->facilityID ."not known \n";
                            continue;
                        }

                    

                    $data["facility_id"] = isset($s->facilityID)?(int)$s->facilityID:0;
                    $data["age"] = isset($s->age)?(int)$s->age:-1;
                    $data["age_group_id"] = isset($s->age)?(int)$s->age:-1;
                    $data["gender"] = isset($s->sex)?$s->sex:0;
                    $data["treatment_indication_id"] = isset($s->trt)?(int)$s->trt:0;//treatment_initiation

                    $data["regimen"] = isset($s->currentRegimenID)?(int)$s->currentRegimenID:0;//current regimen
                    $data["regimen_line"] = isset($s->position)?(int)$s->position:0;
                    $data["regimen_time_id"] = isset($s->reg_time)?(int)$s->reg_time:0;
                    $data["pregnancy_status"] = isset($s->pregnant)? $s->pregnant : "UNKNOWN";
                    $data["breastfeeding_status"] = isset($s->breastfeeding)? $s->breastfeeding : "UNKNOWN";
                    $data["active_tb_status"] = isset($s->activeTBStatus)? $s->activeTBStatus : "UNKNOWN";

                    $data["sample_type_id"] = isset($s->sampleTypeID)?(int)$s->sampleTypeID:0;
                    $data["sample_result_validity"] = isset($s->sampleResultValidity)? $s->sampleResultValidity : "UNKNOWN";
                    $data["suppression_status"] = $this->_getSuppressionStatus($s);
                    $data["tested"]=isset($s->resultsSampleID)?"yes":"no";
                    $data["rejection_reason"]=isset($s->rejectionReason)? $s->rejectionReason : "UNKNOWN";

                    //
                   $sampleID=(int)$s->id;
                   $recordsRemoved = $recordsRemoved + $this->removeSample($sampleID);

                   $this->mongo->dashboard_new_backend->insert($data);
                   $recordsInserted ++;
                }//end of for loop
              echo " Removed $recordsRemoved records for $turnAroundYear-$turnAroundMonth\n";
              echo " Inserted $recordsInserted records for $turnAroundYear-$turnAroundMonth\n";
              
            } catch (Exception $e) {
                var_dump($e);
            }//end catch

        }//end of while loop
    }
   
    /*
    * return 1 for when a record has been successfully removed,0 when nothing has been found.
    */
    private function removeSample($numberSampleID){
        $options=[];
        $options['justOne']=false;
        $result=$this->mongo->dashboard_new_backend->remove(array('sample_id' => $numberSampleID), $options);
        return $result['n'];//return 1 for when a record has been successfully removed,0 when nothing has been found.
    }
    private function _getFacilities(){
        $sql = "SELECT id,districtID,hubID FROM vl_facilities";
        $facilities =  \DB::connection('live_db')->select($sql);
        $facilities_map = [];
        foreach ($facilities as $key => $value) {
            $facilities_map[$value->id]=$value;
        }
        return $facilities_map;
    }

    private function _getLocationIDs($facilities,$facility_id){
        foreach ($facilities as $key => $facility) {
            if($facility->id == $facility_id){
                return $facility;
             }
                
        }
        return false;
    }
    private function _getSuppressionStatus($samplesRecord){
        $suppression_status = "no";
        if($samplesRecord->sampleResultValidity == 'valid'){
             if((int)$samplesRecord->resultNumeric < 1000){
                 $suppression_status = "yes";
             }
               
            /*if((int)$samplesRecord->sampleTypeID == 1 && (int)$samplesRecord->resultNumeric < 1000)
                $suppression_status = "yes";
           
                
            else if((int)$samplesRecord->sampleTypeID == 2 && (int)$samplesRecord->resultNumeric < 5000)
                $suppression_status = "yes";*/
            
        }

        return $suppression_status; 
    }
    private function _getSuppressedNumbers($samplesArray){
        if($samplesArray->validity == 'valid'){
            if((int)$samplesArray->resultNumeric < 1000){
               return (int)$samplesArray->samples_tested; 
            }
                
           /* if((int)$samplesArray->sampleTypeID == 1 && (int)$samplesArray->resultNumeric < 1000)
                return (int)$samplesArray->samples_tested;
            else if((int)$samplesArray->sampleTypeID == 2 && (int)$samplesArray->resultNumeric < 5000)
                return (int)$samplesArray->samples_tested;*/
        }

        return 0; 
    }
    private function _loadHubs(){
        $this->mongo->hubs->drop();
        $res=LiveData::getHubs();
        foreach($res AS $row){
            $data=['id'=>$row->id,'name'=>$row->hub];
            $this->mongo->hubs->insert($data);
        }
    }


    private function _loadFacilities(){
        $this->mongo->facilities->drop();
        $res=LiveData::getFacilities();
        foreach($res AS $row){
            $facility_name = $row->dhis2_name!=null ? $row->dhis2_name:$row->facility;
            $data=['id'=>$row->id,'name'=>$facility_name,'hub_id'=>$row->hubID,'ip_id'=>$row->ipID,'district_id'=>$row->districtID];
            $this->mongo->facilities->insert($data);
        }
    }

    private function _loadIPs(){
        $this->mongo->ips->drop();
        $res=LiveData::getIPs();
        foreach($res AS $row){
            $data=['id'=>$row->id,'name'=>$row->ip];
            $this->mongo->ips->insert($data);
        }
    }

    private function _loadDistricts(){
        $this->mongo->districts->drop();
        $res=LiveData::getDistricts();
        foreach($res AS $row){

            $district_name = $row->dhis2_name!=null ? $row->dhis2_name:$row->district;
            
            $data=['id'=>$row->id,'name'=>$district_name];
            $this->mongo->districts->insert($data);
        }
    }

    private function _loadRegimens(){
        $this->mongo->regimens->drop();
        $res = LiveData::getRegimens();
        foreach($res AS $row){
            $data=['id'=>$row->id,'name'=>$row->appendix];
            $this->mongo->regimens->insert($data);
        }
    }

    private function _validCases(){
        $ret="";
        $cases=[
            "Failed",
            "Failed.",
            "Invalid",
            "Invalid test result. There is insufficient sample to repeat the assay.",
            "There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a a new sample.",
            "There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a new sample."];

        foreach ($cases as $v) {
            $ret.=" resultAlphanumeric NOT LIKE '$v' AND";
        }
        $ret=" (".substr($ret, 0,-3).") ";
        return $ret;    
    }

    private function _suppressedCases(){
        return " ((s.sampleTypeID=1 AND resultNumeric<=5000) OR (s.sampleTypeID=2 AND resultNumeric<=1000))";
    }


    private function _multi_search($arr=[],$conds=[],$ret_item=""){
        $filtered_arr=array_filter($arr,function($row) use($conds){
            foreach ($conds as $k => $v)  {
                if(property_exists($row,$k)){
                    if($row->$k!=$v) return false;
                }else{
                    return false;
                }
            }
            return true;
        });
        return current($filtered_arr)?current($filtered_arr)->$ret_item:0;
    }

    private function _insertSQL($data,$table){
        $columns_str=$values_str="";
        foreach ($data as $k => $v) {
            $columns_str.="`$k`,";
            $val=str_replace("'", "''", $v);
            $val=trim($val);
            $values_str.="'$val',"; 
        }
        $now=date("YmdHis");
        return "INSERT INTO `$table` ($columns_str `created_at`,`updated_at`) VALUES ($values_str '$now','$now');";
    }
}
