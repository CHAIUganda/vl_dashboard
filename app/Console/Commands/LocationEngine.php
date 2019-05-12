<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use EID\Mongo;

class LocationEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:update {--F|facilities} {--H|hubs} {--D|districts} {--dhis2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Locations records';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->mongo=Mongo::connect();
        $this->db = \DB::connection('direct_db');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->dhis2 = $this->option('dhis2'); 
        $this->facilities = $this->option('facilities');
        if($this->dhis2){
            echo "......started...\n";
            $facilities = $this->loadFacilities();
            
            echo "......facilities read...\n";
            $this->updateFacilitiesWithDHIS2Names($facilities);
            echo "......facilities updated with dhis2 names...\n";
        }
        if($this->facilities){
            $this->comment("....load facilities  latest list....");
            $this->loadAllFacilities();
            $this->comment("....finished....");
        }
    }
    /* 
    * Loads all facilities with the latest details/fields about facilities like dhis uid, dhis name
    */
    private function loadAllFacilities(){
        $sql="SELECT f.id,f.facility name,f.dhis2_name,f.hub_id,
        h.ip_id, f.district_id,f.dhis2_uid,d.dhis2_uid as district_uid 
        FROM backend_facilities f 
        left join backend_hubs h on f.hub_id = h.id 
        left join backend_districts d on d.id = f.district_id";

        $facilities_array = $this->db->select($sql);

        $this->mongo->facilities->drop();
        
        foreach($facilities_array AS $row){
            $data=[
                  'id'=>$row->id,
                  'name'=>$row->name,
                  'dhis2_name'=>$row->dhis2_name,
                  'hub_id'=>$row->hub_id,
                  'ip_id'=>$row->ip_id,

                  'district_id'=>$row->district_id,
                  'dhis2_uid'=>$row->dhis2_uid,
                  'district_uid'=>$row->district_uid,
                  'updated'=>'done'
                  ];
            $this->mongo->facilities->insert($data);
        }

    }

    /* 
    * Loads all districts with the latest details/fields about districts like dhis uid, dhis name
    */
    private function loadAllDistricts(){
        

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
