<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;

class LoadAruaData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tester';

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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "---Started Loading Arua Data--\n";

        //read file into array
        //$arua_data = $this->getAruaData();
        //echo "CSV file read succefully \n";
        //var_dump($arua_data);

        //insert patients

        //insert regimens
        //insert samples
        //insert sample results(into results_merged)
    }
  
    private function getAruaData(){
        //load list of districts with Hubs
        $file = fopen("./docs/others/arua.csv", "r");
        $data = array();  
        $counter = 0;  
        while ( !feof($file)){
            
            $array_instance = fgetcsv($file);
            //print_r($array_instance);
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
                
                array_push($data, $sample); 
            if($counter == 20){
                break;
            }

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
    private function generateDateOfBirth($age){
        $date_of_birth = "0000-00-00";
        if(is_integer($age)){
            
        }
        return $date_of_birth;
    }
    private function insertPatients($arua_data){
        $arua_data_with_unique_patient_ids = $this->unique_multidim_array($arua_data,'patient_art'); 
        for ($index=0; $index < sizeof($arua_data_with_unique_patient_ids))); $index++) { 
            $dummy_patient = $arua_data_with_unique_patient_ids[$index];
            $facility_id=50;
            
            $art_number=$dummy_patient["patient_art"];
            $uniqueID=$facility."-".$art_number;//art_number + facility id
            $gender=$this->generateGender($dummy_patient["gender"]);
            $date_of_birth=$this->generateDateOfBirth($dummy_patient["age"]);
         } 
    }
}
