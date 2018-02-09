<?php namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\Dashboard;
use EID\LiveData;



class DataSetDump extends Command{

	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'datasetdump:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data set dump';

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
    	echo "--started---".date('YmdHis')."\n";
        ini_set('memory_limit', '2500M');
        //
        $this->comment("Engine has started at :: ".date('YmdHis'));
        try {
            //get dataset dump
         $year=2017;
         $month=5;
         $year_month = $month > 10 ? "$year$month": $year."0".$month;
         $data_set_dump = LiveData::getSamplesDataSetByMonth($year,$month);
         $this->makeCsv($data_set_dump,$year_month);
        } catch (Exception $e) {
            echo $e->message;
        }
        


        //convert to csv
         $this->comment("Engine has finished at :: ".date('YmdHis'));
    }

    private function makeCsv($dataset,$year_month){

        $facilities = LiveData::getFacilitiesInAnArrayForm();

        $regimens = LiveData::getRegimensInAnArrayForm();
        $regimen_line = LiveData::getRegimenLinesInAnArrayForm();
        $regimen_time = LiveData::regimenTimeInArrayForm();
        $treatment_initiation = LiveData::getTreatmentInitiationInAnArrayForm();

        $sampleTypes = LiveData::getSampleTypesInArrayForm();

        $file_name = "/Users/simon/Desktop/CDC/dataset_".$year_month.".csv";
        $fp = fopen($file_name, 'w');
        
            //headers
            $header['facilityID']='facilityID';
            $header['facility_code']='dhis2_facility_id';
            $header['district_code']='dhis2_district_id';

            $header['year']='year';
            $header['mth']='month';
            
            $header['validity']='validity';
            $header['samples_tested']='samples_tested';
            $header['suppressed']='suppressed';

            $header['age']='age_group';
            $header['sex']='sex';
            $header['regimen']='regimen';
            $header['reg_line']='reg_line';
            $header['reg_time']='reg_time';

            $header['trt']='trt';
            $header['number_patients_received']='number_patients_received';
            $header['pregnancyStatus']='pregnancyStatus';
            $header['numberPregant']='numberPregant';
            $header['breastFeedingStatus']='breastFeedingStatus';

            $header['numberBreastFeeding']='numberBreastFeeding';
            $header['activeTBStatus']='activeTBStatus';
            $header['numberActiveOnTB']='numberActiveOnTB';
            $header['sampleTypeID']='sampleTypeID';
        fputcsv($fp, $header);


        foreach ($dataset as $key=>$record) {
            echo "-----1---\n";
            $fields['facilityID']=$record->facilityID;
            $facility_id=$record->facilityID;
            if(intval($facility_id) < 1 || intval($facility_id) == 3645)
                break;
            echo ".....$facility_id .....\n";
            echo "-----2---\n";
            $facility = $facilities[$record->facilityID];
            echo "-----3---\n";
            $fields['facility_code'] = isset($facility) ? $facility['dhis2_uid']: 'Null';
            echo "-----4---\n";
            $fields['district_code']=isset($facility) ? $facility['district_uid']: 'Null';
            echo "-----5---\n";

            $fields['year']=$record->year_created;
            echo "-----6---\n";
            $fields['mth']=$record->mth;

            echo "-----7---\n";
            $fields['validity']=$record->validity;
            echo "-----8---\n";
            $fields['samples_tested']=$record->samples_tested;
            echo "-----9---\n";
            $fields['suppressed']=$record->suppressed;
            echo "-----10---\n";

            $fields['age']=$record->age_group;
            echo "-----11---\n";
            $fields['sex']=$record->sex;
            echo "-----12---\n";
            $fields['regimen']=$this->getItemName($regimens,$record->regimen);
            echo "-----13---\n";
            $fields['reg_line']= $this->getItemName($regimen_line,$record->reg_line);
            echo "-----14---\n";
            $fields['reg_time']= $this->getItemName($regimen_time,$record->reg_time);
            echo "-----15---\n";


            $fields['trt']=$this->getItemName($treatment_initiation,$record->trt);
            echo "-----16---\n";
            $fields['number_patients_received']=isset($record->number_patients_received)?$record->number_patients_received : 0;
            echo "-----17---\n";
            $fields['pregnancyStatus']=isset($record->pregnancyStatus)?$record->pregnancyStatus : 0;
            echo "-----18---\n";
            $fields['numberPregant']=isset($record->numberPregant)?$record->numberPregant : 0;
            echo "-----19---\n";
            $fields['breastFeedingStatus']=isset($record->breastFeedingStatus)? $record->breastFeedingStatus : 0;
            echo "-----20---\n";

            $fields['numberBreastFeeding']=isset($record->numberBreastFeeding)? $record->numberBreastFeeding : 0;
            echo "-----21---\n";
            $fields['activeTBStatus']=isset($record->activeTBStatus)? $record->activeTBStatus : 0;
            echo "-----22---\n";
            $fields['numberActiveOnTB']=isset($record->numberActiveOnTB)? $record->numberActiveOnTB : 0;
            echo "-----23---\n";
            $fields['sampleTypeID']= $this->getItemName($sampleTypes ,$record->sampleTypeID);
            echo "-----24---\n";
            

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

