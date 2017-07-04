<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;

class Arua extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arua:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //read file into array
        $arua_data = $this->getAruaData();
        

        //insert patients
        //$this->insertPatients($arua_data);

        
        //insert samples
       echo  $this->getTreatmentLast6Months('0 - New');
        //$this->insertSamples($arua_data);
        
        //insert results
    }

    private function getAruaData(){
        //load list of districts with Hubs
        $file = fopen("./docs/others/arua.csv", "r");
        $data = array();  
        $counter = 0;  
        while ( !feof($file)){

            $array_instance = fgetcsv($file);
          
            
                $sample['form_no']=$array_instance[0];
                $sample['sample_id']=$array_instance[1];
                $sample['facility']=$array_instance[2];
                $sample['district']=$array_instance[3];
                $sample['hub']=$array_instance[4];

                $sample['date_collection']=$array_instance[5];
                $sample['sample_type']=$array_instance[6];
                $sample['patient_art']=$array_instance[7];
                $sample['gender']=$array_instance[8];
                $sample['age']=$array_instance[9];

                $sample['more_than_six_months_treatment']=$array_instance[10];//Has patient been on treatment for =>6 months?
                $sample['date_tx_initiated']=$array_instance[11];
                $sample['current_regimen']=$array_instance[12];
                $sample['other_regimen']=$array_instance[13];
                $sample['line_of_treatment']=$array_instance[14];

                $sample['treatment_indication']=$array_instance[15];
                $sample['reason_for_treatment']=$array_instance[16];
                $sample['patient_has_active_tb']=$array_instance[17];
                $sample['arv_adherence']=$array_instance[18];
                $sample['routine_monitoring']=$array_instance[19];//routine monitor AND repeat VL test after 
                                                                 //suspect tx failure adherence counselling 
                                                                 //AND sustected tx failure
                $sample['last_vl_date']=$array_instance[20];
                $sample['value_and_result']=$array_instance[21];
                $sample['tested']=$array_instance[22];
                $sample['machine_type']=$array_instance[23];
                
                if($counter > 0){//skip first row
                   array_push($data, $sample); 
                }
               
            /*if($counter == 20){
                break;
            }*/

            $counter ++;
        }
    
        return $data;
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
       if($gender == "F") return "Female";
       else if($gender == "M") return "Male";
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
       
        var_dump($rows);
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
    private function insertPatients($arua_data){
        $counter = 0;
        $arua_data_with_unique_patient_ids = $this->unique_multidim_array($arua_data,'patient_art'); 
        
        for ($index=0; $index < sizeof($arua_data_with_unique_patient_ids); $index++) { 
         
            $dummy_patient = $arua_data_with_unique_patient_ids[$index];
            $facility_id=50;
            $date_of_collection=$dummy_patient['date_collection'];
            $art_number=$dummy_patient["patient_art"];
            $uniqueID=$facility_id."-A-".$art_number;//art_number + facility id
            $gender=$this->generateGender($dummy_patient["gender"]);
            $date_of_birth=$this->generateDateOfBirth(intval($dummy_patient["age"]),$date_of_collection);
            $created=date('Y-m-d H:m:s');
            $created_by= 'smuwanga@musph.ac.ug';

            //if exisitng, skip, else insert
            if (!$this->isPatientExisting($uniqueID)) {
               $sql="insert into vl_patients(uniqueID,artNumber,gender,dateOfBirth,created,createdby) values 
               ('$uniqueID','$art_number','$gender','$date_of_birth','$created','$created_by')";
               echo "$sql";
                try{
                  $affectedRows =  \DB::connection('live_db')->insert($sql);
                  $counter ++;
                }catch(Exception $e){
                 echo "\n ".$e->getMessage()." \n";
                }
                
            }
         } 
         echo "$counter patients added\n";
    }//end insert patients

    /***
    * regimenID = -3// the was no definate value for age
    * regimenID = -2//default value in the switch case-block
    * regimenID = -1 // regimen not defined
    */
    private function getCurrentRegimenID($name,$age){
      /*
      $regimens="0,AZT-3TC-ATV/r,AZT-3TC-EFV,AZT-3TC-NVP,azt/3tc/lpv/r,D4T-3TC-NVP,
      OTHER SPECIFY,TDF-3TC-ATV/r,TDF-3TC-EFV,
      TDF-3TC-LPV/r,TDF-3TC-NVP,TDF-FTC-EFV,TDF/3TC/EFV,#N/A";
      $regimens_array = explode(",", $regimens);
     */ 
      $age = trim($age);
      if($age == '#N/A' || $age == ''){
        return -3;
      }
        
      $age = intval($age);
      $regimenID=0;

      switch ($name) {
        case 'AZT-3TC-ATV/r':
          if($age <= 14){
            $regimenID = 28;
          }else{
            $regimenID = 16;
          }
          break;
        case 'AZT-3TC-EFV':
          if($age <= 14){
            $regimenID = 22;
          }else{
            $regimenID = 2;
          }
          break;

        case 'AZT-3TC-NVP':
            if($age <= 14){
              $regimenID = 21;
            }else{
              $regimenID = 1;
            }
          break;
        case 'azt/3tc/lpv/r':
            if($age <= 14){
              $regimenID = -1;//not defined
            }else{
              $regimenID = 13;
            }
          break;
        case 'D4T-3TC-NVP':
            if($age <= 14){
              $regimenID = 19;
            }else{
              $regimenID = -1;
            }
          break;
        case 'TDF-3TC-ATV/r':
            if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID = 15;
            }
          break;
        case 'TDF-3TC-EFV':
            if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID = 4;
            }
          break;
        case 'TDF-3TC-LPV/r':
            if($age <= 14){
              $regimenID = 25;
            }else{
              $regimenID = 11;
            }
          break;
        case 'TDF-3TC-NVP':
            if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID = 3;
            }
          break;
        case 'TDF-FTC-EFV':
            if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID =6;
            }
          break;
        case 'TDF/3TC/EFV':
            if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID =4;
            }
          break;

        case 'OTHER SPECIFY':
            if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID =-1;
            }
          break;

        case '3TC/AZT/LPV'://others
          if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID =13;
            }
          break;
        case 'ABC-3TC-ATV/r'://others
          if($age <= 14){
              $regimenID = 30;
            }else{
              $regimenID =18;
            }
          break;
        case 'ABC-3TC-ATVr'://others
          if($age <= 14){
              $regimenID = 30;
            }else{
              $regimenID =18;
            }
          break;
        case 'ABC-3TC-EFV'://others
          if($age <= 14){
              $regimenID = 24;
            }else{
              $regimenID =7;
            }
          break;
        case 'ABC-3TC-LPV/r'://others
          if($age <= 14){
              $regimenID = 29;
            }else{
              $regimenID =17;
            }
        break;

        case 'ABC-3TC-LPVr'://others
          if($age <= 14){
              $regimenID = 29;
            }else{
              $regimenID =17;
            }
        break;
        case 'ABC-3TC-NVP'://others
          if($age <= 14){
              $regimenID = 23;
            }else{
              $regimenID =8;
            }
        break;
        case 'abc/3tc/nvp'://others
          if($age <= 14){
              $regimenID = 23;
            }else{
              $regimenID =8;
            }
        break;
        case 'AZT-3TC-ATVr'://others
          if($age <= 14){
              $regimenID = 28;
            }else{
              $regimenID =16;
            }
        break;
        case 'AZT-3TC-LPV/r'://others
          if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID =13;
            }
        break;
        case 'AZT-3TC-LPVr'://others
          if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID =13;
            }
        break;
        case 'azt/3tc/efv'://others
          if($age <= 14){
              $regimenID = 22;
            }else{
              $regimenID =2;
            }
        break;
        case 'TDF-3TC-ATVr'://others
          if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID =15;
            }
        break;
        case 'TDF-3TC-LPV/r'://others
          if($age <= 14){
              $regimenID = 25;
            }else{
              $regimenID =11;
            }
        break;
        case 'TDF/3TC/DRV/RAL'://others
          if($age <= 14){
              $regimenID = -1;
            }else{
              $regimenID =-1;
            }
        break;
        default:
          $regimenID = -2;
          
        break;
      }

      return $regimenID;
    }

    private function getTreatmentLast6Months($treatmentLast6Months){
      $first_character = explode('-', $treatmentLast6Months);
      $hadTreatmentInLast6Months = "Left Blank";
      if (intval($first_character[0]) == 0) {
        $hadTreatmentInLast6Months = "Yes";
      }elseif (intval($first_character[0]) == 1){
        $hadTreatmentInLast6Months = "No";
      }

      return $hadTreatmentInLast6Months;
    }
    private function insertSamples($arua_data){
        $counter = 0;
        foreach ($arua_data as $key => $arua_data_record) {

           $patientUniqueID="50-A-".$arua_data_record['patient_art'];
           $lrCategory="";
           $lrEnvelopeNumber="";
           $lrNumericID="";
           $vlSampleID=$arua_data_record['sample_id'];

           $formNumber=$arua_data_record['form_no'];
           $districtID=7;
           $hubID=1;
           $facilityID=50;

           $age = $arua_data_record['age'];
           $currentRegimenID = $this->getCurrentRegimenID($arua_data_record['current_regimen'],$age);
           if($arua_data_record['current_regimen'] =='OTHER SPECIFY'){//
              $currentRegimenID = $this->getCurrentRegimenID($arua_data_record['other_regimen'],$age);
            }
            

           $pregnant="";
           $pregnantANCNumber="";
           $breastfeeding="";
           $activeTBStatus="No";
           $collectionDate=$this->changeDateFormat($arua_data_record['date_collection'],'Y-m-d');

           $receiptDate="2017-04-01";
           $treatmentLast6Months=$this->getTreatmentLast6Months($arua_data_record['more_than_six_months_treatment']);
           $treatmentInitiationDate="";
           $sampleTypeID="";
           $viralLoadTestingID="";//1, 2,3,4

           $treatmentInitiationID="";
           $treatmentInitiationOther="";
           $treatmentStatusID="";
           $reasonForFailureID="";
           $tbTreatmentPhaseID="";//Not able tp deduce from the excel shhet

           $arvAdherenceID="";
           $vlTestingRoutineMonitoring="";
           $routineMonitoringLastVLDate="";
           $routineMonitoringValue="";
           $routineMonitoringSampleTypeID="";

           $vlTestingRepeatTesting="";
           $repeatVLTestLastVLDate="";
           $repeatVLTestValue="";
           $repeatVLTestSampleTypeID="";
           $vlTestingSuspectedTreatmentFailure="";

           $suspectedTreatmentFailureLastVLDate="";
           $suspectedTreatmentFailureValue="";
           $suspectedTreatmentFailureSampleTypeID="";
           $verified="";//
           $created="";//
           $createdby="";//

           $counter ++;
           /*
           if($counter == 4){
             break;
           }*/
        }
    }
}
