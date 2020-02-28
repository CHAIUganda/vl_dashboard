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
    protected $signature = 'jobs:run {--F|locations} {--pvls} 
    {--from_date= :in the format yyyy-MM-DD} {--to_date= :in the format yyyy-MM-DD }';

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

        $locations_flag = $this->option('locations');
        if($locations_flag == 'locations'){
            $this->_loadFacilitlyLocations();
        }

        $pvls_flag = $this->option('pvls');
        $from_date_value = $this->option('from_date');
        $to_date_value = $this->option('to_date');

        if($pvls_flag == 'pvls'){
            $this->_generatePvlsReport($from_date_value,$to_date_value);
        }
            
    }


    private function _loadFacilitlyLocations(){
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

    private function _generatePvlsReport($from_date_parameter,$to_date_parameter){
        $this->comment('generating PVLS report');

        //generate CPHL names.
        $pepfar_pvls_locations = LiveData::getPvlsPepfarLocations();

        //indication

        $array_pvls_map = array();

        echo ".... generating csv...\n";
        $fp = fopen('/tmp/pvls_report'.date('YmdHis').'.csv', 'w');
        foreach ($pepfar_pvls_locations as $fields) {
             fputcsv($fp, $fields);
        }

        
        fclose($fp);
    }
}

