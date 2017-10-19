<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;

class FacilityEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facility:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Facility records';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "......started...\n";
        $facilities = $this->loadFacilities();
        
        echo "......facilities read...\n";
        $this->updateFacilitiesWithDHIS2Names($facilities);
        echo "......facilities updated...\n";
    }

    private function loadFacilities(){
        $file = fopen("./docs/others/facilities_20171019.csv", "r");
        $data = array();
        //loading CSV entire data
       
        
        while ( ! feof($file )) {

            $array_instance = fgetcsv($file);
            //print_r($array_instance);

             
                $facility['cphl_facility_id']=$array_instance[0];
                $facility['cphl_facility_name']=$array_instance[1];
                $facility['dhis2_facility_name']=$array_instance[2];
                $facility['dhis2_facility_uid']=$array_instance[3];
                $facility['dhis2_district_uid']=$array_instance[4];
               
                
                
                
                array_push($data, $facility);
             
                
                
        }
        
        //remove duplicates
        $facilities = $this->unique_multidim_array($data,'dhis2_facility_uid'); 

        return $facilities;
    }

    private function updateFacilitiesWithDHIS2Names($facilities){
        //
        foreach ($facilities as $key => $facility) {
            $cphl_facility_id = intval($facility['cphl_facility_id']);
            $dhis2_facility_name = addslashes($facility['dhis2_facility_name']);
            $dhis2_facility_uid  = $facility['dhis2_facility_uid'];
            $dhis2_district_uid  = $facility['dhis2_district_uid'];

            try{
                $sql = "update vl_facilities set district_uid='".$dhis2_district_uid."',dhis2_uid='".$dhis2_facility_uid."',dhis2_name='".$dhis2_facility_name."' where id=$cphl_facility_id";
                    $affectedDistricts =  \DB::connection('live_db')->update($sql);

            }catch(Exception $e){
                 echo "\n ".$ex->getMessage()." \n";
           }
        }
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
}
