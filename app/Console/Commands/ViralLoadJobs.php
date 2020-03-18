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

    private function getNumbers($array_instance,$uid){
        $value = 0;

        $dummy_output = isset($array_instance[$uid])? $array_instance[$uid] : 0;
        
        if($dummy_output == 0){
           $value = 0; 
        }else{
            $value = $dummy_output['individuals'];
           
        }

        return $value;
    }
    private function _generatePvlsReport($from_date_parameter,$to_date_parameter){
        $this->comment('generating PVLS report');

        //generate CPHL names.
        $pepfar_pvls_locations = LiveData::getPvlsPepfarLocations();
        //indication

        // - Individuals who did a vL
         $this->comment('vl results...');
        $individualsWithVLresult = LiveData::getIndividualsWithVLresult($from_date_parameter,$to_date_parameter);
        
        // - Individuals with VL suppression
        $this->comment('vl suppression...');
        $individualsWithVLsuppression = LiveData::getIndividualsWithVLsuppression($from_date_parameter,$to_date_parameter);

        $routineIndication = LiveData::getRoutineIndication($from_date_parameter,$to_date_parameter);
        $targetedIndication = LiveData::getTargetedIndication($from_date_parameter,$to_date_parameter);

        $pregantRoutine = LiveData::getPregnantRoutine($from_date_parameter,$to_date_parameter);
        $pregantTargeted = LiveData::getPregnantTargeted($from_date_parameter,$to_date_parameter);

        $breastFeedingRoutine = LiveData::getBreastFeedingRoutine($from_date_parameter,$to_date_parameter);
        $breatFeedingTargeted = LiveData::getBreastFeedingTargeted($from_date_parameter,$to_date_parameter);

        $routine_suppressed_f_below_1 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',1,NULL);
        $routine_suppressed_f_from_1_to_4 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',1,4);
        $routine_suppressed_f_from_5_to_9 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',5,9);
        $routine_suppressed_f_from_10_to_14 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',10,14);
        $routine_suppressed_f_from_15_to_19 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',15,19);
        $routine_suppressed_f_from_20_to_24 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',20,24);
        $routine_suppressed_f_from_25_to_29 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',25,29);
        $routine_suppressed_f_from_30_to_34 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',30,34);
        $routine_suppressed_f_from_35_to_39 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',35,39);
        $routine_suppressed_f_from_40_to_44 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',40,44);
        $routine_suppressed_f_from_45_to_49 = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',45,49);
        $routine_suppressed_f_from_50_plus = LiveData::getRoutineSuppressedIndividuals($from_date_parameter,$to_date_parameter,
            'F',50,NULL);



        $final_pvls_report_array = array();

        array_push($final_pvls_report_array, [
            'dhis2_hf_id','region','dhis2_district','dhis2_subcounty','dhis2_name','datim_id',
            'art_support','art_im','art_agency','individuals_with_vl_result',
            'individuals_with_vl_suppression','indication_routine','indication_target',
            'pregant_routine','pregant_targeted','breast_feeding_routine','breast_feeding_targeted',
            'routine_suppressed_f_below_1','routine_suppressed_f_from_1_to_4','routine_suppressed_f_from_5_to_9',
            'routine_suppressed_f_from_10_to_14','routine_suppressed_f_from_15_to_19','routine_suppressed_f_from_20_to_24',
            'routine_suppressed_f_from_25_to_29','routine_suppressed_f_from_30_to_34','routine_suppressed_f_from_35_to_39',
            'routine_suppressed_f_from_40_to_44','routine_suppressed_f_from_45_to_49','routine_suppressed_f_from_50_plus'
            ]);
        foreach ($pepfar_pvls_locations as $key => $row) {

            //check if the location_dhis_uid == 
            $facility_pvls_report  = array(
                'dhis2_hf_id' => $row->dhis2_hf_id,
                'region' => $row->region,
                'dhis2_district' => $row->dhis2_district,
                'dhis2_subcounty'=>$row->dhis2_subcounty,
                'dhis2_name'=>$row->dhis2_name,
                'datim_id'=>$row->datim_id,
                'art_support'=>$row->art_support,
                'art_im'=>$row->art_im,
                'art_agency'=>$row->art_agency,

                'individuals_with_vl_result'=>$this->getNumbers($individualsWithVLresult,$row->dhis2_hf_id),
                'individuals_with_vl_suppression'=> $this->getNumbers($individualsWithVLsuppression,$row->dhis2_hf_id),
                'indication_routine' => $this->getNumbers($routineIndication,$row->dhis2_hf_id),
                'indication_target' =>  $this->getNumbers($targetedIndication,$row->dhis2_hf_id),
                'pregant_routine' => $this->getNumbers($pregantRoutine,$row->dhis2_hf_id),
                'pregant_targeted' =>$this->getNumbers($pregantTargeted,$row->dhis2_hf_id),
                'breast_feeding_routine' => $this->getNumbers($breastFeedingRoutine,$row->dhis2_hf_id),
                'breast_feeding_targeted' => $this->getNumbers($breatFeedingTargeted,$row->dhis2_hf_id),

                'routine_suppressed_f_below_1' =>$this->getNumbers($routine_suppressed_f_below_1,$row->dhis2_hf_id),
                'routine_suppressed_f_from_1_to_4' =>$this->getNumbers($routine_suppressed_f_from_1_to_4,$row->dhis2_hf_id),
                'routine_suppressed_f_from_5_to_9' =>$this->getNumbers($routine_suppressed_f_from_5_to_9,$row->dhis2_hf_id),
                'routine_suppressed_f_from_10_to_14' =>$this->getNumbers($routine_suppressed_f_from_10_to_14,$row->dhis2_hf_id),
                'routine_suppressed_f_from_15_to_19' =>$this->getNumbers($routine_suppressed_f_from_15_to_19,$row->dhis2_hf_id),

                'routine_suppressed_f_from_20_to_24' =>$this->getNumbers($routine_suppressed_f_from_20_to_24,$row->dhis2_hf_id),
                'routine_suppressed_f_from_25_to_29' =>$this->getNumbers($routine_suppressed_f_from_25_to_29,$row->dhis2_hf_id),
                'routine_suppressed_f_from_30_to_34' =>$this->getNumbers($routine_suppressed_f_from_30_to_34,$row->dhis2_hf_id),
                'routine_suppressed_f_from_35_to_39' =>$this->getNumbers($routine_suppressed_f_from_35_to_39,$row->dhis2_hf_id),
                'routine_suppressed_f_from_40_to_44' =>$this->getNumbers($routine_suppressed_f_from_40_to_44,$row->dhis2_hf_id),
                'routine_suppressed_f_from_45_to_49' =>$this->getNumbers($routine_suppressed_f_from_45_to_49,$row->dhis2_hf_id),
                'routine_suppressed_f_from_50_plus' =>$this->getNumbers($routine_suppressed_f_from_50_plus,$row->dhis2_hf_id)
                );

            array_push($final_pvls_report_array, $facility_pvls_report);
        }

INSERT INTO permissions (name, display_name, created_at, updated_at) VALUES ('collect_sample', 'Can collect test sample', '2019-01-14 09:46:00', '2019-01-14 09:46:00');
        

        echo ".... generating csv...\n";
        $fp = fopen('/Users/simon/data/vl/pvls/pvls_report'.date('YmdHis').'.csv', 'w');
        foreach ($final_pvls_report_array as $fields) {
             fputcsv($fp, $fields);
        }

        
        fclose($fp);
    }
}

