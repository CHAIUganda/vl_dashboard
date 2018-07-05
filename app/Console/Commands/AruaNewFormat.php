<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\LiveData;
use EID\Mongo;

class AruaNewFormat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arua:onwards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Loads Arua Data';

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
        echo "---April 2017 Onwards--\n";
        $_year=2017;
        $_month=11;
        $year_and_month=201711;
        //$file_location = "/Users/simon/Documents/Documents/METS/CBS/CPHL/AruaData/Nov2017Submission.csv";
        $file_location = "./docs/others/Dec2017Submission.csv";

        //read file into array
        $arua_data = $this->getAruaData($file_location);
        
    
        //insert patients
        echo "----- patients insertion is starting----\n";
        $this->insertPatients($arua_data);

        
        //insert samples
        //facility ID, District ID, Hub ID,year_month:201709
        echo "----- sample insertion is starting----\n";
        
        $this->insertSamples($arua_data,$year_and_month);
        
        
        //insert results
        echo "----- results insertion is starting----\n";
        $this->insertResults($arua_data,$year_and_month);
        echo "----- results insertion is complete----\n";

        //mongo transfer: This could be a separate script
        echo "----- Mongo job is starting----\n";
        $this->_loadDataFromMysql($_year,$_month);
        echo "----- Mongo job is complete----\n";

    }

    private function _loadDataFromMysql($_year,$_month){
        $facilities=$this->_getFacilities();
       
            $turnAroundYear=$_year;
            $turnAroundMonth=$_month;
            $externalSource="arua";//the place whose data we are fetching.

            $dummyYearMonthString=$turnAroundYear.str_pad($turnAroundMonth,2,0,STR_PAD_LEFT);
            $dummyYearMonth = intval($dummyYearMonthString);


            $samples_records = LiveData::getSamplesRecordsByMonthFromExternalSources($turnAroundYear,$turnAroundMonth,$externalSource);
            $recordsInserted=0;
            
            $recordsRemoved =0;
            try {
                foreach($samples_records AS $s){
                    $recordsRemoved =$recordsRemoved + $this->removeSample($s->id);

                    $data=[];
                    $year_month = $turnAroundYear.str_pad($s->monthOfYear,2,0,STR_PAD_LEFT);
            
                    $data["sample_id"]=isset($s->id)? (int)$s->id: 0;
                    $data["vl_sample_id"]=isset($s->vlSampleID)? $s->vlSampleID: 0;
                    $data["patient_unique_id"]=isset($s->patientUniqueID)? $s->patientUniqueID: "UNKNOWN";//
                    $data["year_month"] = (int)$year_month;
                    
                        if(array_key_exists(intval($s->facilityID), $facilities)){
                            $facility= $facilities[$s->facilityID];
                            $data['district_id']=isset($facility->districtID)?(int)$facility->districtID:0;
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
                    
                    //UNKNOWN is the default to represent NULL
                    $sample_results=[]; 
                    $sample_results = $this->_getResults($s);
                    $data["sample_result_validity"] = $sample_results["sample_result_validity"];
                    $data["suppression_status"] = $sample_results["suppression_status"];

                    $data["tested"]=isset($s->resultsSampleID)?"yes":"no";
                    $data["rejection_reason"]=isset($s->rejectionReason)? $s->rejectionReason : "UNKNOWN";

                    //
                   $sampleID=(int)$s->id;
                   

                   $this->mongo->dashboard_new_backend->insert($data);
                   $recordsInserted ++;
                }//end of for loop
              echo " Removed $recordsRemoved records for $turnAroundYear-$turnAroundMonth\n";
              echo " Inserted $recordsInserted records for $turnAroundYear-$turnAroundMonth\n";
              
            } catch (Exception $e) {
                var_dump($e);
            }//end catch

    }

    private function _getSuppressionStatus($resultNumeric,$sampleResultValidity){
        $suppression_status = "UNKNOWN";
       
        if($sampleResultValidity == 'valid'){
            if($resultNumeric < 1000){
                 $suppression_status = "yes";
            }else{
                $suppression_status = "no";
            }

        }

        return $suppression_status; 
    }

    private function _getResults($samplesRecord){
       
        $sampleResultValidity='UNKNOWN';
        $suppressionStatus='UNKNOWN';

        if($samplesRecord->concatinated_results != NULL){
            $exploded_results = explode(',', $samplesRecord->concatinated_results);
            $array_size = count($exploded_results);
            $last_index = $array_size - 1;
        
            $last_results = $exploded_results[$last_index];
            $exploded_last_results=explode(':', $last_results );

            $sampleResultValidity =$exploded_last_results[1];
            $suppressionStatus = $this->_getSuppressionStatus(intval($exploded_last_results[0]),$sampleResultValidity);
        }
        
        $results=[];
        $results["suppression_status"]=$suppressionStatus;
        $results["sample_result_validity"]=$sampleResultValidity;

        return $results;

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

     /*
    * return 1 for when a record has been successfully removed,0 when nothing has been found.
    */
    private function removeSample($numberSampleID){
        $options=[];
        $options['justOne']=false;
        $result=$this->mongo->dashboard_new_backend->remove(array('sample_id' => $numberSampleID), $options);
        return $result['n'];//return 1 for when a record has been successfully removed,0 when nothing has been found.
    }
    private function getAruaData($file_location){
        //load list of districts with Hubs

        $file = fopen($file_location, "r");
        //$file = fopen($file_location, "r");

        $data = array();  
        $counter = 0;  
        while ( !feof($file)){

            $array_instance = fgetcsv($file);
          
            
                $sample['form_number']=$array_instance[0];
                $sample['location_id']=$array_instance[1];
                $sample['sample_id']=$array_instance[2];
                $sample['facility']=$array_instance[3];
                $sample['district']=$array_instance[4];
                $sample['region']=$array_instance[5];
                $sample['hub']=$array_instance[6];
                $sample['implementing_partner']=$array_instance[7];

                $sample['date_of_collection']=$array_instance[8];
                $sample['sample_type']=$array_instance[9];
                $sample['patient_art']=$array_instance[10];
                $sample['patient_other_id']=$array_instance[11];
                $sample['gender']=$array_instance[12];
                $sample['date_of_birth']=$array_instance[13];
                $sample['age']=$array_instance[14];
                $sample['phone_number']=$array_instance[15];

                $sample['more_than_six_months_treatment']=$array_instance[16];//Has patient been on treatment for =>6 months?
                $sample['date_of_treatment_initiation']=$array_instance[17];
                $sample['current_regimen']=$array_instance[18];
                $sample['other_regimen']=$array_instance[19];
                $sample['indication_for_treatment_initiation']=$array_instance[20];
                $sample['line_of_treatment']=$array_instance[21];
                $sample['reason_for_failure']=$array_instance[22];
                $sample['is_patient_pregnant']=$array_instance[23];

                $sample['anc_number']=$array_instance[24];
                $sample['is_patient_breastfeeding']=$array_instance[25];
                $sample['patient_has_active_tb']=$array_instance[26];
                $sample['if_yes_are_they_on']=$array_instance[27];
                $sample['arv_adherence']=$array_instance[28];//routine monitor AND repeat VL test after 
                                                                 //suspect tx failure adherence counselling 
                                                                 //AND sustected tx failure
                $sample['routine_monitoring']=$array_instance[29];
                $sample['last_viral_load_date']=$array_instance[30];
                $sample['result_for_last_viral_load']=$array_instance[31];
                $sample['sample_type_at_last_viral_load']=$array_instance[32];
                $sample['repeat_vl_test']=$array_instance[33];//Repeat Viral Load Test after Suspected Treatment Failure adherence counseling

                $sample['last_viral_load_date_2']=$array_instance[34];
                $sample['last_viral_load_value_2']=$array_instance[35];
                $sample['sample_type_at_last_viral_load2']=$array_instance[36];
                $sample['suspected_treattment_failure']=$array_instance[37];
                $sample['last_viral_load_date_3']=$array_instance[38];
                $sample['last_viral_load_value_3']=$array_instance[39];
                $sample['sample_type_at_last_viral_load3']=$array_instance[40];
                $sample['tested']=$array_instance[41];
                $sample['worksheet']=$array_instance[42];
                $sample['machine_type']=$array_instance[43];
                $sample['result']=$array_instance[44];
                
                if($counter > 0){//skip first row
                   array_push($data, $sample); 
                }
               

            $counter ++;
        }
    
        return $data;
    }

    private function insertPatients($data){
        $counter = 0;
        $arua_data_with_unique_patient_ids = $this->unique_multidim_array($data,'patient_art'); 
        $NoPatients= intval(sizeof($arua_data_with_unique_patient_ids));
        echo "size of array: $NoPatients \n";
        //for ($index=0; $index < $NoPatients; $index++) { 
        foreach ($arua_data_with_unique_patient_ids as $key => $dummy_patient) {
            # code...
          
            if($dummy_patient["form_number"] == null)
                continue;
            //$dummy_patient = $arua_data_with_unique_patient_ids[$index];
            
            $facility_id=50;
            $date_of_collection=$dummy_patient['date_of_collection'];
            $art_number=$dummy_patient["patient_art"];
            $uniqueID=$facility_id."-A-".$art_number;//art_number + facility id
            $gender=$this->generateGender($dummy_patient["gender"]);

            $date_of_birth="0000-00-00";
            if ($dummy_patient["date_of_birth"] != null) {
                $time = strtotime($dummy_patient["date_of_birth"]);
                $date_of_birth = date('Y-m-d',$time);
            }else{
                $date_of_birth=$this->generateDateOfBirth(intval($dummy_patient["age"]),$date_of_collection);
            }
            

            $created=date('Y-m-d H:m:s');
            $created_by= 'arua@data.cphl';

            //if exisitng, skip, else insert
            
            if (!$this->isPatientExisting($uniqueID)) {
               $sql="insert into vl_patients(uniqueID,artNumber,gender,dateOfBirth,created,createdby) values 
               ('$uniqueID','$art_number','$gender','$date_of_birth','$created','$created_by')";
               //echo "$sql";
                try{
                  $affectedRows =  \DB::connection('live_db')->insert($sql);
                  $counter ++;
                }catch(Exception $e){
                  echo "\n oops ".$e->getMessage()." \n";
                }
                
            }else{
              echo "$uniqueID exists\n";
            }
         } 
         echo "$counter patients added\n";
    }//end insert patients


    private function generateSampleId($sample_record,$facilityID,$year_month){
        //'yyyymm/form_number/facilityID'
        $sample_id=$year_month."/".$sample_record['form_number']."/".$facilityID;
        return $sample_id;
    }
    private function insertSamples($arua_data,$year_and_month){
        $counter = 0;
        foreach ($arua_data as $key => $arua_data_record) {

            //skip empty rows
           if($arua_data_record['form_number'] == null)
                continue;

           $patientUniqueID="50-A-".$arua_data_record['patient_art'];

           $patientID = $this->getPatientID($patientUniqueID);
           $lrCategory="";
           $lrEnvelopeNumber="";
           $lrNumericID="";
           

           $formNumber=$arua_data_record['form_number'];
           $districtID=7;
           $hubID=1;
           $facilityID=50;
           $year_month=$year_and_month;

           $vlSampleID=$this->generateSampleId($arua_data_record,$facilityID,$year_month);
           $age = $arua_data_record['age'];
           $currentRegimenID = $this->getCurrentRegimenID($arua_data_record['current_regimen']);
          

           $pregnant=$this->getPregnancyStatus($arua_data_record['is_patient_pregnant']);
           $pregnantANCNumber=$arua_data_record['anc_number'];
           $breastfeeding=$this->getBreastFeedingStatus($arua_data_record['is_patient_breastfeeding']);
           $activeTBStatus=$this->getActiveTbStatus($arua_data_record['patient_has_active_tb']);
           $collectionDate=$this->changeDateFormat($arua_data_record['date_of_collection'],'Y-m-d');

           $receiptDate=$this->getDateReceived($arua_data_record['date_of_collection']);
           $treatmentLast6Months=$this->getTreatmentLast6Months($arua_data_record['more_than_six_months_treatment']);
           $treatmentInitiationDate=$this->changeDateFormat($arua_data_record['date_of_treatment_initiation'],'Y-m-d');
           $sampleTypeID=$this->getSampleTypeId($arua_data_record);

           /** What are the expected values for ViralLoad Testing? Look at the form */
           $viralLoadTestingID=$this->getViralLoadTestingID($arua_data_record['routine_monitoring']);//1, 2,3,4

           $treatmentInitiationID=$this->getTreatmentInitiationID($arua_data_record['indication_for_treatment_initiation']);
           $treatmentInitiationOther="";//NULL
           $treatmentStatusID=$this->getTreatmentStatus($arua_data_record['line_of_treatment']);
           $reasonForFailureID=$this->getReasonForTreatmentFailure($arua_data_record['reason_for_failure']);
           $tbTreatmentPhaseID=0;//Not able to deduce from the excel sheet

           $arvAdherenceID=$this->getAdherenceID($arua_data_record['arv_adherence']);
           $vlTestingRoutineMonitoring=0;
           $routineMonitoringLastVLDate="0000-00-00";
           $routineMonitoringValue="";
           $routineMonitoringSampleTypeID=2;//Plasma

           $vlTestingRepeatTesting=0;
           $repeatVLTestLastVLDate="0000-00-00";
           $repeatVLTestValue="";
           $repeatVLTestSampleTypeID=2;
           $vlTestingSuspectedTreatmentFailure="";

           $suspectedTreatmentFailureLastVLDate="0000-00-00";
           $suspectedTreatmentFailureValue="";
           $suspectedTreatmentFailureSampleTypeID=2;
           $verified=1;//
           $created=$this->getDateCreated($arua_data_record['date_of_collection']);//
           $createdby="arua@data.cphl";//


           $sql="INSERT INTO vl_samples(
                patientID,patientUniqueID,lrCategory,lrEnvelopeNumber,lrNumericID,
                vlSampleID,formNumber,districtID,hubID,facilityID,
                currentRegimenID,pregnant,pregnantANCNumber,breastfeeding,activeTBStatus,
                collectionDate,receiptDate,treatmentLast6Months,treatmentInitiationDate,
                
                sampleTypeID,viralLoadTestingID,treatmentInitiationID,treatmentInitiationOther,treatmentStatusID,
                reasonForFailureID,tbTreatmentPhaseID,arvAdherenceID,vlTestingRoutineMonitoring,routineMonitoringLastVLDate,
                routineMonitoringValue,routineMonitoringSampleTypeID,vlTestingRepeatTesting,repeatVLTestLastVLDate,repeatVLTestValue,
                repeatVLTestSampleTypeID,vlTestingSuspectedTreatmentFailure,suspectedTreatmentFailureLastVLDate,suspectedTreatmentFailureValue,suspectedTreatmentFailureSampleTypeID,
                verified,created,createdby
                  ) values(
                  $patientID,'$patientUniqueID','$lrCategory','$lrEnvelopeNumber','$lrNumericID',
                  '$vlSampleID','$formNumber',$districtID,$hubID,$facilityID,
                  $currentRegimenID,'$pregnant','$pregnantANCNumber','$breastfeeding','$activeTBStatus',
                  '$collectionDate','$receiptDate','$treatmentLast6Months','$treatmentInitiationDate',
                  $sampleTypeID,$viralLoadTestingID,$treatmentInitiationID,'$treatmentInitiationOther',$treatmentStatusID,
                  $reasonForFailureID,$tbTreatmentPhaseID,$arvAdherenceID,$vlTestingRoutineMonitoring,'$routineMonitoringLastVLDate',
                  '$routineMonitoringValue',$routineMonitoringSampleTypeID,$vlTestingRepeatTesting,'$repeatVLTestLastVLDate','$repeatVLTestValue',
                  $repeatVLTestSampleTypeID,'$vlTestingSuspectedTreatmentFailure','$suspectedTreatmentFailureLastVLDate','$suspectedTreatmentFailureValue',$suspectedTreatmentFailureSampleTypeID,
                  $verified,'$created','$createdby'
                  )";
           
             try{
                  $affectedRows =  \DB::connection('live_db')->insert($sql);
                  $counter ++;
                }catch(Exception $e){
                  echo "\n ".$e->getMessage()." \n";
                }

           
           /*
           if($counter == 4){
             break;
           }*/

        }//end for loop
    }//end of method
    

    private function getResultNumeric($testResult){
    
      $suppressedStatus = 0;

      if( trim($testResult) == trim("< 1000") ){
        $suppressedStatus = 20;
      }
       elseif (trim($testResult) == trim("> 1000")) {
        $suppressedStatus = 10000;
      }

      return $suppressedStatus;
    }
    private function getResultAlphanumeric($testResult){
      
      $suppressedStatus = "UNKNOWN";

      if( trim($testResult) == trim("< 1000") ){
        $suppressedStatus = "Not detected";
      }
      elseif (trim($testResult) == trim("> 1000")) {
        $suppressedStatus = "10,000 Copies \/ mL";
      }

      return $suppressedStatus;
    }
    private function getSuppressed($testResult){
      
      $suppressedStatus = "UNKNOWN";
      if( trim($testResult) == trim("< 1000") ){
        $suppressedStatus = "YES";
      }
      elseif (trim($testResult) == trim("> 1000")) {
        $suppressedStatus = "NO";
      }

      return $suppressedStatus;
    }
    private function insertResults($arua_data,$year_and_month){
      
      $counter = 0;
        foreach ($arua_data as $key => $arua_data_record) {
          //skip empty rows
          if($arua_data_record['form_number'] == null)
                continue;

          $machine="SAMBA";

          $worksheetID=Date('YmdHis')+"$counter";
          $worksheetID = intval($worksheetID);

        
          $districtID=7;
          $hubID=1;
          $facilityID=50;
          $year_month=$year_and_month;

          $vlSampleID=$this->generateSampleId($arua_data_record,$facilityID,$year_month);
          
          $resultAlphanumeric=$this->getResultAlphanumeric($arua_data_record['result']);
          $resultNumeric=$this->getResultNumeric($arua_data_record['result']);
          $suppressed=$this->getSuppressed($arua_data_record['result']);

          $created=$this->getDateCreated($arua_data_record['date_of_collection']);//
          $createdby="arua@data.cphl";//

          
          if($vlSampleID==""){
            continue;
          }
          $sql="insert into vl_results_merged
               (machine,worksheetID,vlSampleID,resultAlphanumeric,resultNumeric,
                suppressed,created,createdby) values 
               ('$machine',$worksheetID,'$vlSampleID','$resultAlphanumeric',$resultNumeric,
                '$suppressed','$created','$createdby')";
               
                try{
                  $affectedRows =  \DB::connection('live_db')->insert($sql);
                  $counter ++;
                }catch(Exception $e){
                 echo "\n  bad result entry. ".$e->getMessage()." \n";
                 echo "sql \n";

                }  
         
        }
    }

    private function getDateReceived($date_collection){
       //add 10 days to the $date_collection
      $date_collection_array = explode("/", $date_collection);
      $reformatted_date_collection = "".$date_collection_array[2]."-".$date_collection_array[1]."-".$date_collection_array[0];
       
      $date = date_create($reformatted_date_collection);
      date_add($date, date_interval_create_from_date_string('0 days'));
      return date_format($date, 'Y-m-d H:m:s');
    }

    private function getDateCreated($date_collection){

      $date_collection_array = explode("/", $date_collection);
      $reformatted_date_collection = "".$date_collection_array[2]."-".$date_collection_array[1]."-".$date_collection_array[0];
       
      $date = date_create($reformatted_date_collection);
      date_add($date, date_interval_create_from_date_string('0 days'));
      
      return date_format($date, 'Y-m-d H:m:s');
    }
    private function getPatientID($patientUniqueID){
      $id = 0;
      if(isset($patientUniqueID)){
        
        $sql = "SELECT distinct id FROM vl_patients where uniqueID like '$patientUniqueID'";
        $patient =  \DB::connection('live_db')->select($sql);
        
        

        $id = $patient[0]->id;
      }
      return $id;
    }
    /***
    * regimenID = -3// the was no definate value for age
    * regimenID = -2//default value in the switch case-block
    * regimenID = -1 // regimen not defined
    */
    private function getCurrentRegimenID($current_regimen){
        $regimenID =-1;
      switch ($current_regimen) {
       
        case '1c': $regimenID =1; break;
        case '1d': $regimenID =2; break;
        case '1e': $regimenID =3; break;
        case '1f': $regimenID =4; break;
        case '1g': $regimenID =5; break;

        case '1h': $regimenID =6; break;
        case '1i': $regimenID =7; break;
        case '1j': $regimenID =8; break;
        case '2b': $regimenID =11; break;
        case '2c': $regimenID =12; break;

        case '2e': $regimenID =13; break;
        case '2f': $regimenID =14; break;
        case '2g': $regimenID =15; break;
        case '2h': $regimenID =16; break;
        case '2i': $regimenID =17; break;

        case '2j': $regimenID =18; break;
        case '4a': $regimenID =19; break;
        case '4b': $regimenID =20; break;
        case '4c': $regimenID =21; break;
        case '4d': $regimenID =22; break;

        case '4e': $regimenID =23; break;
        case '4f': $regimenID =24; break;
        case '5d': $regimenID =25; break;
        case '5e': $regimenID =26; break;
        case '5g': $regimenID =27; break;

        case '5i': $regimenID =28; break;
        case '5j': $regimenID =29; break;
        case '5k': $regimenID =30; break;
        case 'Other Regimen': $regimenID =71; break;

        default://Left Blank
          $regimenID = 31;
          
        break;
      }

      return $regimenID;
    }
    private function getPregnancyStatus($sample_record){
        $pregnancy_status = "Left Blank";
        if(strtolower(trim($sample_record))== strtolower('yes')) {
          $pregnancy_status = "Yes";
      }elseif (strtolower(trim($sample_record))== strtolower('no')) {
           $pregnancy_status = "No";
      }
      return $pregnancy_status;
    }
    private function getBreastFeedingStatus($sample_record){
        $breast_feeding_status = "";
        if(strtolower(trim($sample_record))== strtolower('yes')){
          $breast_feeding_status = "Yes";
         }elseif (strtolower(trim($sample_record))== strtolower('no')) {
           $breast_feeding_status = "No";
         }
      return $breast_feeding_status;
    }
    private function getActiveTbStatus($sample_record){
      $active_tb_status = "";
      if(strtolower(trim($sample_record))== strtolower('yes') ){
          $active_tb_status = "Yes";
      }elseif (strtolower(trim($sample_record)) == strtolower('no')) {
           $active_tb_status = "No";
      }
      return $active_tb_status;
    }
    private function getTreatmentLast6Months($treatmentLast6Months){
      
      $hadTreatmentInLast6Months = "Left Blank";
      if(!empty($treatmentLast6Months)){

        if(strtolower(trim($treatmentLast6Months))== strtolower('≥5yrs') ){
          $hadTreatmentInLast6Months = "Yes";
        }elseif (strtolower(trim($treatmentLast6Months))== strtolower('2-<5yrs')) {
          $hadTreatmentInLast6Months = "Yes";
        }elseif (strtolower(trim($treatmentLast6Months))== strtolower('1-<2yrs')) {
          $hadTreatmentInLast6Months = "Yes";
        }elseif (strtolower(trim($treatmentLast6Months))== strtolower('6months-<1yr')) {
          $hadTreatmentInLast6Months = "Yes";
        }else{
          try {
            $months_on_treatment = intval($treatmentLast6Months);
            if($months_on_treatment > 6)
              $hadTreatmentInLast6Months = "Yes";
            else
              $hadTreatmentInLast6Months = "No";
          } catch (Exception $e) {
            echo "error on treatment_in last six months conversion\n";
            var_dump($e);
          }//end try catch
          
        }//end of else-if
        
      }

      return $hadTreatmentInLast6Months;
    }
    
    private function getSampleTypeId($sample_record){
      if(strcasecmp($sample_record['sample_type_at_last_viral_load'], "plasma") == 0 || 
        strcasecmp($sample_record['sample_type_at_last_viral_load2'], "plasma") == 0){
        return 2;
      }else{
        return 0;
      }
    }
    private function getViralLoadTestingID($routineMonitoring){
      
      if(strpos(strtolower($routineMonitoring), strtolower('yes')) ){
          return 1;//Routine Monitoring
      }elseif (strpos(strtolower($routineMonitoring), strtolower('After enhanced adherence'))) {
          return 2;//Repeat viral load 
      }else{
          return 4;//Left Blank
      }
    }

    private function getTreatmentInitiationID($treatmentIndication){
      
      if(trim(strtolower($treatmentIndication)) == trim(strtolower('CD4<500')) ){
        return 3;//CD4<500
      }elseif ( trim(strtolower($treatmentIndication)) == trim(strtolower('child <15yrs')) ) {
        return 2;
      }elseif(trim(strtolower($treatmentIndication)) == trim(strtolower('Child Under 15')) ){//Child Under 15
        return 2;
      }elseif (trim(strtolower($treatmentIndication)) == trim(strtolower('PMTCT/Option B+'))) {//PMTCT/Option B+
          return 1;
      }elseif (trim(strtolower($treatmentIndication)) == trim(strtolower('TB Infection'))) {//TB Infection
          return 4;
      }elseif (trim(strtolower($treatmentIndication)) == trim(strtolower('Other'))) {
          return 5;
      }
      else{
        return 0;
      }
    }

    private function getTreatmentStatus($line_of_treatment){
      $id = 4;//left blank
      
      if(strpos(strtolower($line_of_treatment), strtolower('First')) ){
          return 1;
      }elseif (strpos(strtolower($line_of_treatment), strtolower('Second'))) {
        return 2;
      }elseif (strpos(strtolower($line_of_treatment), strtolower('Third'))) {
        return 3;
      }
      else{
          return 4;//Left Blank
      }

      return $id;
    }

    private function getReasonForTreatmentFailure($reasonForFailureID){
        $id = 4;// N/A
      if(trim(strtolower($reasonForFailureID)) == trim(strtolower('N/A'))){
          $id = 4;//N/A
      }elseif(trim(strtolower($reasonForFailureID)) == trim(strtolower('virological'))){
        $id = 1;//Virological
      }elseif(trim(strtolower($reasonForFailureID)) == trim(strtolower('Clinical'))){
        $id = 2;//Virological
      }elseif(trim(strtolower($reasonForFailureID)) == trim(strtolower('Immunological'))){
        $id = 3;//Virological
      }
      return $id;
    }
    private function getAdherenceID($arvAdherence){
        $id = 0;//
        if(trim(strtolower($arvAdherence)) == trim(strtolower('Good > 95%')) ){
          $id = 1;
        }elseif (trim(strtolower($arvAdherence)) == trim(strtolower('Fair 85 - 94%'))) {
          $id = 2;
        }elseif (trim(strtolower($arvAdherence)) == trim(strtolower('<85%'))) {
          $id = 3;
        }
        return $id;
    }
    private function unique_multidim_array($array, $key) { 
        $temp_array = array(); 
        $i = 0; 
        $key_array = array(); 
        
        foreach($array as $val) { 
            if (!in_array($val[$key], $key_array)) { 
                $key_array[$i] = $val[$key]; 
                $temp_array[$i] = $val; 
            } 
            $i++; 
        } 
        return $temp_array; 
    }
    private function generateGender($gender){
       if($gender != null) {
            return $gender;
       }
       else return "Left Blank";
    }
    private function generateDateOfBirth($age,$date_of_collection){
        $date_of_birth = "0000-00-00";
        if(is_integer($age)){
            $someDay = strtotime($date_of_collection);
            $date_of_birth = strtotime('-'.$age.' years', $someDay);
            $date_of_birth= date('Y-m-d', $date_of_birth);
        }
        return $date_of_birth;
    }

    private function changeDateFormat($date_string,$format_string){
        $date_string = str_replace('/', '-', $date_string);
        $old_date_format = strtotime($date_string);
        $new_date_format = date($format_string,$old_date_format);

        return $new_date_format;
    }
    private function isPatientExisting($uniqueID){
      $isExisting = false;
      if(isset($uniqueID)){
        
        $sql = "SELECT count(*) count FROM vl_patients where uniqueID like '$uniqueID'";
        $rows =  \DB::connection('live_db')->select($sql);
       
        if(!empty($rows) > 0){
        
            $rowsObject = $rows[0];
            $countOfRows = $rowsObject->count;
            if($countOfRows > 0){
                $isExisting=true;
            }
        }
      }
      return $isExisting;
    }
}
