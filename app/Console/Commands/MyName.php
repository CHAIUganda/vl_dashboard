<?php namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\Dashboard;
use EID\Mongo;


class MyName extends Command{

	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myname';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display my name';

    /**
     * Execute the console command.
     *
     * @return mixed
     */


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

    public function handle($x="p") {
    	//read cphl facilities
        $facilities = $this->loadFacilities();

        //read dhis facilities
        $dhis2_facilities = $this->loadDhis2Facilities();
        
        //merge
        $merged_data=[];
        echo "-----1---\n";
                    $dummy_facility['uid']="uid";
                    $dummy_facility['cphl_facility_name']='cphl_facility_name';
                    $dummy_facility['dhis2_facility_name']="dhis2_facility_name";
                    $dummy_facility['dhis2_facility_uid']="dhis2_facility_uid";
                    $dummy_facility['dhis2_district_uid']="dhis2_district_uid";

                    $dummy_facility['Level']="Level";
                    $dummy_facility['Ownership']="Ownership";
                    $dummy_facility['Authority']="Authority";

                    $dummy_facility['Coordinates']="Coordinates";
                    $dummy_facility['Subcounty_uid']="Subcounty_uid";
                    $dummy_facility['Subcounty_name']="Subcounty_name";
                    $dummy_facility['District_uid']="District_uid";
                    $dummy_facility['District_name']="District_name";

                    $dummy_facility['Region_uid']="Region_uid";
                    $dummy_facility['Region_name']="Region_name";

        array_push($merged_data, $dummy_facility);

        foreach ($facilities as $key => $facility) {
            foreach ($dhis2_facilities as $index => $dhis2_facility) {
                if($facility['dhis2_facility_uid'] == $dhis2_facility['Facility_uid']){
                    
                    $dummy_facility['uid']=$facility['uid'];
                    $dummy_facility['cphl_facility_name']=$facility['cphl_facility_name'];
                    $dummy_facility['dhis2_facility_name']=$facility['dhis2_facility_name'];
                    $dummy_facility['dhis2_facility_uid']=$dhis2_facility['Facility_uid'];
                    $dummy_facility['dhis2_district_uid']=$facility['dhis2_district_uid'];

                    $dummy_facility['Level']=$dhis2_facility['Level'];
                    $dummy_facility['Ownership']=$dhis2_facility['Ownership'];
                    $dummy_facility['Authority']=$dhis2_facility['Authority'];

                    $dummy_facility['Coordinates']=$dhis2_facility['Coordinates'];
                    $dummy_facility['Subcounty_uid']=$dhis2_facility['Subcounty_uid'];
                    $dummy_facility['Subcounty_name']=$dhis2_facility['Subcounty_name'];
                    $dummy_facility['District_uid']=$dhis2_facility['District_uid'];
                    $dummy_facility['District_name']=$dhis2_facility['District_name'];

                    $dummy_facility['Region_uid']=$dhis2_facility['Region_uid'];
                    $dummy_facility['Region_name']=$dhis2_facility['Region_name'];

                    array_push($merged_data, $dummy_facility);
                }//end if
            }//end inner foreach
        }

        //make csv
        
        $fp = fopen('/Users/simon/Desktop/consolidated_20171102.csv', 'w');
        foreach ($merged_data as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
        echo "-----2---\n";

    }

    private function loadFacilities(){
        $file = fopen("./docs/others/facilities_20171019.csv", "r");
        $data = array();
        //loading CSV entire data
       
        
        while ( ! feof($file )) {

            $array_instance = fgetcsv($file);
            //print_r($array_instance);

                
                $facility['uid']=$array_instance[0];
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
     private function loadDhis2Facilities(){
        $file = fopen("/Users/simon/Desktop/dhis2.csv", "r");
        $data = array();
        //loading CSV entire data
       
        
        while ( ! feof($file )) {

            $array_instance = fgetcsv($file);
            //print_r($array_instance);

             
                $facility['Facility_uid']=$array_instance[0];
                $facility['Facility_name']=$array_instance[1];
                $facility['Level']=$array_instance[2];
                $facility['Ownership']=$array_instance[3];
                $facility['Authority']=$array_instance[4];

                $facility['Coordinates']=$array_instance[5];
                $facility['Subcounty_uid']=$array_instance[6];
                $facility['Subcounty_name']=$array_instance[7];
                $facility['District_uid']=$array_instance[8];
                $facility['District_name']=$array_instance[9];

                $facility['Region_uid']=$array_instance[10];
                $facility['Region_name']=$array_instance[11];
                
                array_push($data, $facility);

                
        }
        
        //remove duplicates
        $facilities = $this->unique_multidim_array($data,'Facility_uid'); 

        return $facilities;
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

