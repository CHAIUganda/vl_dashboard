<?php namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\Dashboard;
use EID\LiveData;
use EID\Mongo;



class DataAPI extends Command{

	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataapi:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data API';

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
        
    }

    public function handle() {
    	echo "--started API---".date('YmdHis')."\n";
        ini_set('memory_limit', '2500M');
        //
        $this->comment("Engine has started at :: ".date('YmdHis'));
        try {
         
         //pull data from mongo
            $params = array('year'=>2015,
                'from_yearmonth' =>201501 ,
                'to_yearmonth'=>201512,
                'gender'=>'f',
                'start_age' =>15,
                'to_age'=>99//e.g. less than 15
            );
            $mongo_result_set = $this->getAnnualData($params);
            //var_dump($mongo_result_set);

         //disseminate as csv
            $file_name = $params['year'].'_'.$params['gender'].'_'.$params['start_age'].'_'.$params['to_age'];
            $this->makeCsv($mongo_result_set,$file_name);
        } catch (Exception $e) {
            echo $e->message;
        }
        


        //convert to csv
         $this->comment("Engine has finished at :: ".date('YmdHis'));
    }

    private function getAnnualData($params){
        
        $mongo=Mongo::connect();
        
            
            /*
            $project_array = array('facility_id' =>1 , 
                'year_month'=>1,'gender'=>1,'age' => 1,'sample_result_validity' => 1,
                'suppression_status'=>1);
            */
            //match stage
            //--$match_array = array('year_month' => array('$gte'=>201501,'$lte'=>201512));
            $and_for_year_month=array('year_month' => array('$gte'=>$params['from_yearmonth'],'$lte'=>$params['to_yearmonth']));
            $and_for_age=array('age' => array('$gte'=>$params['start_age'],'$lt'=>$params['to_age']));
            $and_for_gender=array('gender'=> array('$eq'=>$params['gender']));
            $match_array=array('$and' => array($and_for_year_month,$and_for_age,$and_for_gender));

          
            $eq_sample_result_validity = array('$eq' => array('$sample_result_validity','valid'));
            $cond_sample_result_validity = array($eq_sample_result_validity,1,0);


            $eq_number_suppressed = array('$eq' => array('$suppression_status','yes'));
            $cond_number_suppressed = array($eq_number_suppressed,1,0);

            $group_array = array(
                '_id' => array('facility_id'=>'$facility_id','year_month'=>'$year_month'), 
                'sample_result_validity' => array('$sum'=>  
                                array('$cond' => $cond_sample_result_validity )
                                ),
                
                'number_suppressed' => array('$sum'=>  
                                array('$cond' => $cond_number_suppressed )
                                )
                );

            //$mongo_query['$project'] = $project_array;
            $mongo_query['$match'] = $match_array;
            $mongo_query['$group'] = $group_array;

        $result_set=$mongo->dashboard_new_backend->aggregate(['$match'=>$match_array],['$group'=>$group_array]);
        return $result_set['result'];
    }

    private function makeCsv($dataset,$file_name){
        $facilities = LiveData::getFacilitiesInAnArrayForm();


        $file_name = "/tmp/data_api_". $file_name."_".date('YmdHis').".csv";
        $fp = fopen($file_name, 'w');
        
            //headers
            $header['facilityID']='facilityID';
            $header['facility_name']='facility_name';
            $header['facility_dhis2_code']='dhis2_facility_id';
            $header['district_dhis2_code']='dhis2_district_id';
            //$header['sex']='sex';
            
            $header['number_of_valid_tests']='valid_tests';
            //$header['number_tested']='samples_tested';
            $header['number_suppressed']='suppressed';

         fputcsv($fp, $header);

     
        foreach ($dataset as $key => $record) {
            $facility_id=$record['_id']['facility_id'];
            $fields['facilityID']=$facility_id;
            
            if(intval($facility_id) < 1 || intval($facility_id) == 3645)
                break;
            $fields['facility_name']=isset($facility) ? $facility['facility']: 'Null';
            $facility = $facilities[$facility_id];
            $fields['facility_code'] = isset($facility) ? $facility['dhis2_uid']: 'Null';
            $fields['district_code']=isset($facility) ? $facility['district_uid']: 'Null';
           
     
            //$fields['sex']=$record['_id']['gender'];

            $fields['number_of_valid_tests']=isset($record['sample_result_validity'])?$record['sample_result_validity'] : 0;
            //$fields['number_tested']=isset($record['number_tested'])?$record['number_tested'] : 0;
            $fields['number_suppressed']=isset($record['number_suppressed'])?$record['number_suppressed'] : 0;
            
            
            fputcsv($fp, $fields);
        }
        fclose($fp);
        echo "-----csv generated---\n";
    }
  
    private function getItemName($sourceList,$id){
        $item_name = 'Null';
        if($id == 0){
            return $item_name;
        }
        try {
            $item = $sourceList[$id];
        $item_name = isset($item)?  $item['appendix'] : 'Null';
        } catch (Exception $e) {
            
        }
        
        return $item_name;
    }

}

