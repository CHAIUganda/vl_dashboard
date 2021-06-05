<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\Mongo;
use EID\LiveData;

class SampleRecordAugmentEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'samplerecord:augment {year?} {specificMonth?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Sample Record by adding new fields';

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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       ini_set('memory_limit','1028M');
        //
        $this->comment("Engine has started at :: ".date('YmdHis'));

        
        if($this->argument('year') != null){

          $year=intval($this->argument('year'));
          $specificMonth=intval($this->argument('specificMonth'));

          
          $this->_optimisedLoadDataInitiallyForSpecificMonth($year,$specificMonth);
        }else{
          $this->_loadData();
        }

        $this->comment("Engine has stopped at :: ".date('YmdHis'));
        
    }
    private function getRejectionsMap(){
      $rejections = LiveData::getRejections();
      $rejections_array=[];
      foreach ($rejections as $key => $value) {
         
          $id=$value->id;
          $appendix=$value->appendix;
          $rejections_array[$id]=$appendix;
      }

      return $rejections_array;
    }
    private function processAlphaNumericResult($result){
      $newResult='';

      
      if(isset($result)){
        $resultArray= explode(':', $result);
        $lastIndex = sizeof($resultArray) - 1;
        $newResult = $resultArray[$lastIndex];
      }else{
        $newResult='NIL';
      }
      

      return $newResult;
    }
    private function _loadData(){
        $turnAroundTimeInMonths=env('TAT_MONTHS', 3);//Number of Months to consider for worst turn -around-time
        
        $this->comment("TAT months:  $turnAroundTimeInMonths");
        for ($month=0; $month < $turnAroundTimeInMonths; $month++) { 
            $turnAroundYear=intval(date("Y",strtotime("-$month month")));
            $turnAroundMonth=intval(date("m",strtotime("-$month month")));

            $dummyYearMonthString=$turnAroundYear.str_pad($turnAroundMonth,2,0,STR_PAD_LEFT);
            $dummyYearMonth = intval($dummyYearMonthString);

            

            $samples_records = LiveData::getDataToAugmentSampleRecordsByMonth($turnAroundYear,$turnAroundMonth);
            $recordsUpdated=0;
            
            
            try {
                foreach($samples_records AS $s){
                    
                   $this->augmentSampleRecord(
                    $s->vl_sample_id,
                    'date_of_birth',isset($s->date_of_birth)?$s->date_of_birth: '0000-00-00'
                    
                    );

                   $recordsUpdated ++;
                }//end of for loop
             
              echo " Updated $recordsUpdated records for $turnAroundYear-$turnAroundMonth\n";
              
            } catch (Exception $e) {
                var_dump($e);
            }//end catch

        }//end of for loop

    }
    private function _cleanRejectionCategory($rejectionCategoryString){
      $rejectionCategoryArray = explode(",", $rejectionCategoryString);
      return $rejectionCategoryArray[1];
    }
    private function _loadDataInitially($turnAroundYear,$turnAroundMonth,$firstRowIndex,$lastRowIndex){
            //$rejections_map = $this->getRejectionsMap();
            $samples_records = LiveData::getDataToAugmentSampleRecordsByMonthWithLimits($turnAroundYear,$turnAroundMonth,$firstRowIndex,$lastRowIndex);
            echo "starting to update\n";
            $recordsUpdated=0;
            
            try {
                foreach($samples_records AS $s){
                    
                   $this->augmentSampleRecord(
                    $s->vl_sample_id,
                    'date_of_birth',isset($s->date_of_birth)?$s->date_of_birth: '0000-00-00'
                    );
                   
                   $recordsUpdated ++;
                }//end of for loop
             
             echo " Updated $recordsUpdated records for $turnAroundYear-$turnAroundMonth:$firstRowIndex -> $lastRowIndex \n";
              
            } catch (Exception $e) {
                var_dump($e);
            }//end catch

      
    }

    private function _optimisedLoadDataInitially($turnAroundYear,$turnAroundTimeInMonths){
      

      
      for ($month=1; $month <= $turnAroundTimeInMonths; $month++) { 
            
            $turnAroundMonth=$month;

            //get count of rows
            $countOfRows= LiveData::getCountOfDataToAugmentSampleRecordsByMonth($turnAroundYear,$turnAroundMonth);

            echo "Rows: $countOfRows \n";
            //set number of rows per fetch/page
            $rowsToBeFetched=2000;

            //modulus
            $modulus = $countOfRows%$rowsToBeFetched;
            
            $numberOfPages=intval($countOfRows/$rowsToBeFetched);
            if($modulus > 0){
              $numberOfPages =$numberOfPages + 1;
            }

            
            //loop over with Limit index, lastIndex
            $firstRowIndex=0;
            $lastRowIndex=0;
              for($i=0; $i <= $numberOfPages; $i++){
                
                if($i==1){
                  $firstRowIndex=0;
                  $lastRowIndex=$rowsToBeFetched;
                }elseif ($i == 2 && $i<$numberOfPages) {//second iterations except the last one
                  
                  $firstRowIndex = $firstRowIndex+ $rowsToBeFetched + 1;
                  $lastRowIndex=$lastRowIndex+ $rowsToBeFetched;
                }elseif ($i > 2 && $i<$numberOfPages) {//all other iterations except the last one
                  
                  $firstRowIndex = $firstRowIndex + $rowsToBeFetched ;
                  $lastRowIndex=$lastRowIndex + $rowsToBeFetched;
                }
                elseif ($i == $numberOfPages) {
                  $firstRowIndex=$firstRowIndex + $rowsToBeFetched ;
                  $lastRowIndex=$modulus;
                }
                
                //call method to fetch with limits
                $this->_loadDataInitially($turnAroundYear,$turnAroundMonth,$firstRowIndex,$lastRowIndex);
                 echo "Page:$i of $numberOfPages \n";
              }//end Loop


      }//loop for months of the year

      
    }

    private function _optimisedLoadDataInitiallyForSpecificMonth($turnAroundYear,$specificMonth){
      

            //get count of rows
            $countOfRows= LiveData::getCountOfDataToAugmentSampleRecordsByMonth($turnAroundYear,$specificMonth);

            echo "Rows: $countOfRows \n";
            //set number of rows per fetch/page
            $rowsToBeFetched=2000;

            //modulus
            $modulus = $countOfRows%$rowsToBeFetched;
            
            $numberOfPages=intval($countOfRows/$rowsToBeFetched);
            if($modulus > 0){
              $numberOfPages =$numberOfPages + 1;
            }

            
            //loop over with Limit index, lastIndex
              $firstRowIndex=0;
              $lastRowIndex=0;
              for($i=0; $i <= $numberOfPages; $i++){
                  
                if($i==1){
                  $firstRowIndex=0;
                  $lastRowIndex=$rowsToBeFetched;
                }elseif ($i == 2 && $i<$numberOfPages) {//second iterations except the last one
                  
                  $firstRowIndex = $firstRowIndex+ $rowsToBeFetched + 1;
                  $lastRowIndex=$lastRowIndex+ $rowsToBeFetched;
                }elseif ($i > 2 && $i<$numberOfPages) {//all other iterations except the last one
                  
                  $firstRowIndex = $firstRowIndex + $rowsToBeFetched ;
                  $lastRowIndex=$lastRowIndex + $rowsToBeFetched;
                }
                elseif ($i == $numberOfPages) {
                  $firstRowIndex=$firstRowIndex + $rowsToBeFetched ;
                  $lastRowIndex=$modulus;
                }
                
                //call method to fetch with limits
                $this->_loadDataInitially($turnAroundYear,$specificMonth,$firstRowIndex,$rowsToBeFetched);
                 echo "Page:$i of $numberOfPages \n";
              }//end Loop

              echo "Sample records for $turnAroundYear-$specificMonth updated \n";
      
    }
    private function augmentSampleRecord($vlSampleId,$field,$value){
        
        $addNewFieldArray = array('$set' => array(
            $field=>$value
            ));
        $result=$this->mongo->dashboard_new_backend->update(array('vl_sample_id' => $vlSampleId), $addNewFieldArray);
       // var_dump($result);
        
    }
    
}
