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


class NewEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newengine:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Engine to help generate data-backend for the dashboard';

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
        ini_set('memory_limit', '2900M');
        //
        $this->comment("NewEngine has started at :: ".date('YmdHis'));
        //
        /*
        $this->mongo->drop(); 
        $this->_loadHubs();
        $this->_loadDistricts();
        $this->_loadFacilities();
        $this->_loadIPs();
        $this->_loadRegimens();
        */
       
        $this->_loadData();

        

        $this->comment("NewEngine has stopped at :: ".date('YmdHis'));

    }
  
    
    private function _loadData(){
        $this->mongo->dashboard_new_backend->drop();
        $year=2014;
        $current_year=date('Y');
        //$current_year=2014;

       
        while($year<=$current_year){
            $samples_records = LiveData::getSamplesRecords($year);
            $counter=0;
            
            try {
                foreach($samples_records AS $s){
                    $data=[];
                    $year_month = $year.str_pad($s->monthOfYear,2,0,STR_PAD_LEFT);
            
                    $data["sample_id"]=isset($s->id)? (int)$s->id: 0;
                    $data["vl_sample_id"]=isset($s->vlSampleID)? $s->vlSampleID: 0;
                    $data["patient_unique_id"]=isset($s->patientUniqueID)? $s->patientUniqueID: "UNKNOWN";//
                    $data["year_month"] = (int)$year_month;
                    $data['district_id']=isset($s->districtID)?(int)$s->districtID:0;
                    $data['hub_id']=isset($s->hubID)?(int)$s->hubID:0;

                    $data["facility_id"] = isset($s->facilityID)?(int)$s->facilityID:0;
                    $data["age"] = isset($s->age)?(int)$s->age:-1;
                    $data["age_group_id"] = isset($s->age_group)?(int)$s->age_group:-1;
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

                   $this->mongo->dashboard_new_backend->insert($data);
                   $counter ++;
                }//end of for loop
              echo " inserted $counter records for $year\n";
              $year++;
            } catch (Exception $e) {
                var_dump($e);
            }//end catch

        }//end of while loop
    }

    private function _getSuppressionStatus($samplesRecord){
        $suppression_status = "no";
        if($samplesRecord->sampleResultValidity == 'valid'){
            if((int)$samplesRecord->sampleTypeID == 1 && (int)$samplesRecord->resultNumeric < 1000)
                $suppression_status = "yes";
           
                
            else if((int)$samplesRecord->sampleTypeID == 2 && (int)$samplesRecord->resultNumeric < 5000)
                $suppression_status = "yes";
            
        }

        return $suppression_status; 
    }

    

}
