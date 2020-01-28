<?php namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\Dashboard;
use EID\Mongo;
use EID\LiveData;

/**
* There was need to add more fields(especially those of DHIS2) to Mongo. So they have been added. 
* This script drops the existing mongo facilities collection, and creates a new one with DHIS2 fields
*/

class ViralLoadJobs extends Command{

	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs jobs';

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
        $this->db = \DB::connection('direct_db');
    }

    public function handle() {

        //Districts
        //Facilities
        //Hubs

        echo "\n ....started loading locations .... \n";
        $this->_loadDistricts();
        $this->_loadHubs();
        
    	$this->_loadFacilities();
        echo "\n ..... finished loading locations in Mongo ....\n";
            
    }
    private function _loadDistricts(){
        $this->mongo->districts->drop();
        $res=LiveData::getDistricts();
        foreach($res AS $row){
            $data=['id'=>$row->id,'name'=>$row->district];
            $this->mongo->districts->insert($data);
        }
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
            
            $data=['id'=>$row->id,'name'=>$row->facility,'dhis2_name'=>$row->dhis2_name,'hub_id'=>$row->hub_id,
            'district_id'=>$row->district_id, 'dhis2_uid'=>$row->dhis2_uid];
            $this->mongo->facilities->insert($data);
        }
    }

}

