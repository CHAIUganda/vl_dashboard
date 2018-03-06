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
    	$number_of_valid_tests = 7;
        $number_suppressed = 6;
        

            $suppression_rate=0.0;
            if($number_suppressed>0){
                $suppression_rate = round(($number_suppressed /$number_of_valid_tests)*100);
            }

            echo "..... $suppression_rate \n";
    }

    private function loadFacilities(){
        $file = fopen("/Users/simon/Desktop/unmatched_cphl_dhis2.csv", "r");
        $data = array();
        //loading CSV entire data
       
        
        while ( ! feof($file )) {

            $array_instance = fgetcsv($file);
            //print_r($array_instance);

                
                $facility['Facility']=$array_instance[0];
                $facility['District']=$array_instance[1];
           
                
                array_push($data, $facility);

                
        }
        
        //remove duplicates
        $facilities = $this->unique_multidim_array($data,'Facility'); 

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

