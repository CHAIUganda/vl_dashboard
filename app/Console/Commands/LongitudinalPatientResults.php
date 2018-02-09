<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\LiveData;
use EID\Hub;
use EID\Facility;
use EID\HealthFacility;
use EID\Ip;
use EID\District;
use EID\Dashboard;

use EID\TreatmentIndication;
use EID\SamplesData;
use EID\RegimenData;
use EID\Mongo;


class LongitudinalPatientResults extends Command{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'results:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Engine to help generate longitudinal patient results';

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

        
        try {
            $this->_loadData();
            
           
            //
        } catch (Exception $e) {
            echo 'Message: ' .$e->getMessage();
        }
      
        
        
        $this->comment("Engine has stopped at :: ".date('YmdHis'));

    }

    private function _loadData(){
        //get facilities
        $facilities = $this->_getFacilities();

        //get patients
        $patients_records = $this->_getPatientsRecords();
        $list_results = array();

        $counter=0;
        //fetch patients results
        //first row header
                $result_array=array();
                array_push($result_array,"patient_id");
                array_push($result_array,"gender");
                array_push($result_array,"date_of_birth");

                array_push($result_array,"dhis2_facility_id");
                array_push($result_array,"district_uid");

                array_push($result_array," ");
                array_push($result_array,"sample_id");
                array_push($result_array,"collection_date");
                array_push($result_array,"copies_ml");
                array_push($result_array,"treatment_initiation_date");
                array_push($result_array,"treatment_line");
                array_push($result_array,"regimen");
                array_push($result_array," ");
                array_push($list_results, $result_array);
        foreach ($patients_records as $key => $patient_record) {
            $result_array=array();


            $patient_unique_number = $patient_record->patientUniqueID;
            $patient_gender = $patient_record->gender;
            $patient_date_of_birth = $patient_record->dateOfBirth;


            $health_facility = $this->_getHealthFacility($patient_unique_number,$facilities);

            $dhis2_uid = $health_facility != null? $health_facility->dhis2_uid: null;
            $dhis2_district_uid = $health_facility != null? $health_facility->dhis2_district_uid: null;

            $samples_record = $patient_record->record;

                $random_patient_id = rand(10, 30);
                array_push($result_array,$random_patient_id);
                array_push($result_array,$patient_gender);
                array_push($result_array,$patient_date_of_birth);

                array_push($result_array,$dhis2_uid);
                array_push($result_array,$dhis2_district_uid);

                array_push($result_array," ");
                //array_push($result_array,$samples_record);
            
            $split_sample_records = $this->splitSampleRecords($samples_record);
            foreach ($split_sample_records as $key => $value) {

                array_push($result_array,$value["sample_id"]);
                array_push($result_array,$value["collection_date"]);
                array_push($result_array,$value["results"]);
                array_push($result_array,$value["treatment_initiation_date"]);
                array_push($result_array,$value["treatment_line"]);
                array_push($result_array,$value["regimen"]);
                array_push($result_array," ");
            }
            
            //add results of another patient
            array_push($list_results, $result_array);
            
            $counter++;
            //echo "... entering results for patient: $counter \n";
        }//end loop

        //make csv
        echo ".... generating csv...\n";
        $fp = fopen('/tmp/results'.date('YmdHis').'.csv', 'w');
        foreach ($list_results as $fields) {
             fputcsv($fp, $fields);
        }

        fclose($fp);

    }//end
    private function splitSampleRecords($samples_records){
        $samples_details = array();
        if(isset($samples_records)){
            
            $records_array = explode(":", $samples_records);
            
            foreach ($records_array as $key => $value) {
                $record_string_array = explode(",",$value);
                $record = array();
                if(sizeof($record_string_array) == 6){//if five items, 
                    $record = array(
                    "sample_id"=>isset($record_string_array[0])?$record_string_array[0]:'null',
                    "collection_date"=>isset($record_string_array[1])?$record_string_array[1]:'null',
                    "results"=>isset($record_string_array[2])?$record_string_array[2]:'null',
                    "treatment_initiation_date"=>isset($record_string_array[3])?$record_string_array[3]:'null',
                    "treatment_line"=>isset($record_string_array[4])?$record_string_array[4]:'null',
                    "regimen"=>isset($record_string_array[5])?$record_string_array[5]:'null'
                    );
                }elseif (sizeof($record_string_array) == 5) {//meaning results field has nothing
                    $record = array(
                    "sample_id"=>isset($record_string_array[0])?$record_string_array[0]:'null',
                    "collection_date"=>isset($record_string_array[1])?$record_string_array[1]:'null',
                    "results"=>'null',
                    "treatment_initiation_date"=>isset($record_string_array[2])?$record_string_array[2]:'null',
                    "treatment_line"=>isset($record_string_array[3])?$record_string_array[3]:'null',
                    "regimen"=>isset($record_string_array[4])?$record_string_array[4]:'null'
                    );
                }
                
               array_push($samples_details, $record);
            }//end loop
        }//end isset
        return $samples_details;
    }
    private function _getPatientsRecords(){
        $sql = "select s.patientUniqueID,p.dateOfBirth,p.gender,
            group_concat(
                CONCAT_WS(',', s.vlSampleID,s.collectionDate,results.resultNumeric,
                s.treatmentInitiationDate,ts.appendix ,r.appendix) SEPARATOR ':') as record

            from 
                vl_samples s left join (select id,vlSampleID, created,resultNumeric
                  from vl_results_merged group by vlSampleID) results on s.vlSampleID=results.vlSampleID 
               left join (select distinct sampleID,id,outcomeReasonsID from vl_samples_verify) v on s.id=v.sampleID

                inner join vl_patients p on s.patientID =p.id 
                inner join vl_appendix_regimen r on s.currentRegimenID = r.id
                inner join vl_appendix_treatmentstatus ts on ts.id = r.treatmentStatusID
            group by s.patientUniqueID";
        $patients_records =  \DB::connection('live_db')->select($sql);
       
        return $patients_records;
    }

    private function _getFacilities(){
        $sql = "select * from vl_facilities";
        $facilities =  \DB::connection('live_db')->select($sql);
       
        $facilities_array = array();
        foreach ($facilities as $key => $value) {
            $id="".$value->id;

            $health_facility = new HealthFacility();
            $health_facility->dhis2_name = $value->dhis2_name;
            $health_facility->dhis2_uid = $value->dhis2_uid;
            $health_facility->cphl_name =  $value->facility;
            $health_facility->dhis2_district_uid = $value->district_uid;

            $facilities_array[$id]=$health_facility;
        }
        return $facilities_array;
    }

    private function _getCphlFacilityId($patientUniqueID){
        $string_array= explode("-", $patientUniqueID);
        $facility_id = $string_array[0];
        return $facility_id;
    }
    private function _getHealthFacility($patientUniqueID,$facilities){
       $facility_id = 0;
       $health_facility = null;
       try {
           $facility_id = $this->_getCphlFacilityId($patientUniqueID);
           $facility_id = intval($facility_id);
           $health_facility= isset($facilities[$facility_id])? $facilities[$facility_id] : null;
           //$health_facility= $facilities[$facility_id];
       } catch (Exception $e) {
        
           $health_facility = "Unknown";
       }
       

       return $health_facility;
    }
}
    
    