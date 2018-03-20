<?php namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\Dashboard;
use EID\Mongo;
use EID\LiveData;

/**
* There was need to add more fields(especially those of DHIS2) to Mongo. So they have been added. 
* This script drops the existing mongo facilities collection, and creates a new one with DHIS2 fields
*/

class FacilityDhis2 extends Command{

	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facilityDhis2:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Include DHIS2 Facility Name, UID';

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

    public function handle() {
        echo "\n ....started loading facilities .... \n";
    	$this->_loadFacilities();
        echo "\n ..... finished loading facilities in Mongo ....\n";
            
    }
    private function _loadFacilities(){
        $this->mongo->facilities->drop();
        $res=LiveData::getFacilities();
        foreach($res AS $row){
            //$facility_name = $row->dhis2_name!=null ? $row->dhis2_name:$row->facility;
            $data=['id'=>$row->id,'name'=>$row->facility,'dhis2_name'=>$row->dhis2_name,'hub_id'=>$row->hubID,
            'ip_id'=>$row->ipID,'district_id'=>$row->districtID, 'dhis2_uid'=>$row->dhis2_uid,
            'district_uid'=>$row->district_uid];
            $this->mongo->facilities->insert($data);
        }
    }

}

