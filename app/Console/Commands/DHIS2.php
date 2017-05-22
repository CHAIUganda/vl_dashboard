<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;

class DHIS2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dhis2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to run DHIS2 Works';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        
        $this->updateDistrictWithDHIS2Data();
        $this->handleMergedCPHLandDHIS2Facilities();
        $this->handleComprehensiveDhis2FacilitiesList();
        
        
    }//end handle
    private function handleComprehensiveDhis2FacilitiesList(){
        echo "\n------ started handleComprehensiveDhis2FacilitiesList---- \n";
        $comprehensiveListOfDhis2Facilities=$this->getComprehensiveListOfDhis2Facilities();
        $this->insertComprehensiveListOfDhis2FacilitiesIntoDB($comprehensiveListOfDhis2Facilities);
         
         

        echo "\n------ finished handleComprehensiveDhis2FacilitiesList---- \n";
    }
    private function doesStringEndWithHub($variable){
        $variable = strtolower($variable);
        $searchterm = " hub";

        $pos = strrpos($variable, $searchterm);

        if ($pos !== false && strlen($searchterm) + $pos == strlen($variable))
            {
                return true;
            }else{
                return false;
            }
    }
    private function handleMergedCPHLandDHIS2Facilities(){
        echo "\n ......started merger.....\n";
        //$this->extractFacilitiesNotInDHIS();
        
        $dhis2_facilities=$this->getDhis2Facilities();
        
        $vl_facilities=$this->getVlFacilities();
        
        $final_facilities=array();
        
        
        //matching
        foreach ($dhis2_facilities as $key => $dhis2_facility) {
            //skip first row of column names
            if($dhis2_facility['facility_CPHL'] =='Facility_CPHL'){
                continue;
            }
                

            $isMatched=false;
            $dummy_cphl_name = $dhis2_facility['facility_CPHL'];
            $dummy_cphl_level=$dhis2_facility['level'];
            $dhis_distict_name = trim(strval($dhis2_facility['district_CPHL']));
            $dummy_facilities=array();
            foreach ($vl_facilities as $vl_facility) {
                $vl_id = $vl_facility['id'];
                $vl_name = $vl_facility['name'];
                $vl_district_id = $vl_facility['district_id'];
                
                $vl_district_name = $this->getDistrictName(intval($vl_district_id ));
                $vl_district_name = $vl_district_name != null ? trim(strval($vl_district_name)): null;
            
                similar_text($dummy_cphl_name, $vl_name, $percent);
                //similar_text($dhis_distict_name, $vl_district_name,$district_percent);
                  if($vl_district_name == $dhis_distict_name && $percent >85){
                            //add to array dbid, dbname, cphl_name,level,dhis_name,dhis_fuid,district_uid
                            $matched['db_id']=$vl_id;
                            $matched['db_name']=$vl_name;
                            $matched['cphl_name'] = $dummy_cphl_name;
                            $matched['dummy_cphl_level'] = $dummy_cphl_level;
                            $matched['dhis_name']=$dhis2_facility['DHIS2_Facility_Sync'];
                            $matched['dhis_fuid']=$dhis2_facility['facility_uid'];
                            $matched['district_uid']=$dhis2_facility['district_uid'];
                            $matched['hub']=$dhis2_facility['hub'];

                            $dummy_facilities[$percent] = $matched;
                            $isMatched=true;
                        
                  }//end if
                
            }//end fore-each loop

           
            //sort array
            krsort($dummy_facilities);
            
            foreach ($dummy_facilities as $key => $value) {
                 array_push($final_facilities, $value);
                break;
            }//end loop for sorting

            //add facility that has not been found to match
            if($isMatched == false){
                    $unamatched['db_id']=0;
                    $unamatched['db_name']="";
                    $unamatched['cphl_name'] = $dummy_cphl_name;
                    $unamatched['dummy_cphl_level'] = $dummy_cphl_level;
                    $unamatched['dhis_name']=$dhis2_facility['DHIS2_Facility_Sync'];
                    $unamatched['dhis_fuid']=$dhis2_facility['facility_uid'];
                    $unamatched['district_uid']=$dhis2_facility['district_uid'];
                    $unamatched['hub']=$dhis2_facility['hub'];

                    array_push($final_facilities, $unamatched);
             }
        }//end loop
        echo "\n ......merger ends.....\n";
        
        //insert into csv file
        $this->insertCphlDhis2FacilitiesIntoDB($final_facilities);
        echo "\n Final list updated \n";
        
    }
    
    private function getDistictDbId($district_uid){
        $sql="SELECT * FROM vl_districts where dhis2_uid='$district_uid'";
        $district =  \DB::connection('live_db')->select($sql);
        if(sizeof($district) == 0){
            return 0;
        }
        $district_object = $district[0];
        $district_name = get_object_vars($district_object);
        
        foreach ($district_name as $key => $value) {
           return $value;
        }
    }
    private function getHubDetails($hubname){
        
            $sql = "SELECT * FROM vl_hubs where SOUNDEX(hub)=soundex('$hubname')";
            $hub =  \DB::connection('live_db')->select($sql);
            return $hub;
      
    }

    private function getDistrictsWithHubs(){
        //load list of districts with Hubs
        $file = fopen("/Users/simon/projects/METS/viral-load-dashboard/cphl/districts_hubs.csv", "r");
        $data = array();    
        while ( !feof($file)){

            $array_instance = fgetcsv($file);
            //print_r($array_instance);
                $district['id']=$array_instance[0];
                $district['district_name']=$array_instance[1];
                $district['hub_name']=$array_instance[2];
                $district['dhis2_name']=$array_instance[3];
                $district['dhis2_uid']=$array_instance[4];
                
                array_push($data, $district); 
            
        }
    
        return $data;
    }
    
    private function getHubDetailsUsingDistrictUid($data,$district_uid){

        //read hubname from array
        $hubName="";
        $sql=null;
        $hub=null;
        foreach ($data as $district) {
            if($district['dhis2_uid'] == $district_uid){
                $hubName = $district['hub_name'];
                break;
            }
        }
        //select from mysql
        $hubName = trim($hubName);
        if($hubName != ""){
            $sql = "SELECT * FROM vl_hubs where hub like '%$hubName%'";
            $hub =  \DB::connection('live_db')->select($sql);
        }else{
             $sql = "SELECT * FROM vl_hubs where id=97";
            $hub =  \DB::connection('live_db')->select($sql);
        }

        return $hub;
    }

    private function extractFacilitiesNotInDHIS(){
        $dhis2_facilities=$this->getDhis2Facilities();
        $file = fopen("/Users/simon/projects/METS/viral-load-dashboard/cphl/notInDhis2.csv","w");
       
        foreach ($dhis2_facilities as $facility)
          {
            $test = trim($facility['DHIS2_Facility_Sync']);
            $number=strlen($test);
            
            if(intval($number) == 0){
                echo "\n ".$facility['facility_CPHL'];
                fputcsv($file,explode(',',$facility['facility_CPHL'])); 
            }
  
          }
          fclose($file);
       
    }
    private function insertCphlDhis2FacilitiesIntoDB($final_facilities){
        try{
            echo "\n ....Start.....\n";
            $districts_with_hubs=$this->getDistrictsWithHubs();
            foreach ($final_facilities as $facility){
                $db_id = intval($facility['db_id']);

                $facility_uid=$facility['dhis_fuid'];
                $facility_name=$facility['dhis_name'];
                $facility_name = addslashes($facility_name);
                $district_uid=$facility['district_uid'];
                
              
                if($db_id > 0){
                    $sql = "update vl_facilities set district_uid='".$district_uid."',dhis2_uid='".$facility_uid."',dhis2_name='".$facility_name."' where id=$db_id";
                    $affectedDistricts =  \DB::connection('live_db')->update($sql);
                }else if ($db_id  == 0) {
                    $db_district_id = $this->getDistictDbId($district_uid);
                     
                    $hub_name=$facility['hub'];
                     
                    $hub=array();

                    
                   if($this->doesStringEndWithHub($hub_name)){
                       
                       $hub  = $this->getHubDetails($hub_name);
                       
                    }else{
                        
                        $hub = $this->getHubDetailsUsingDistrictUid($districts_with_hubs,$district_uid);
                        
                    }
                    if(empty($hub)){
                        continue;
                    }
                     $hub_object = $hub[0];
                     $db_hub_id=$hub_object->id;
                     $db_ip_id=$hub_object->ipID;
                    //---
                     $hub_object = $hub[0];
                     

                     $sql="insert into vl_facilities(district_uid,dhis2_uid,facility,dhis2_name,districtID,hubId,ipID
                        ,phone,contactPerson,physicalAddress,returnAddress,active,created,createdBy) 
                        values('$district_uid','$facility_uid','$facility_name','$facility_name',
                        $db_district_id,$db_hub_id,$db_ip_id,'0000000','No name','imported from DHIS2','None','1','2017-05-11 03:04:03',
                        'smuwanga@musph.ac.ug')";

                  
                   $affectedRows =  \DB::connection('live_db')->insert($sql);
                   
                }
            }
           }catch(Exception $e){
                 echo "\n ".$ex->getMessage()." \n";
           }
           echo "\n ....Finish..CPHL and DHIS2 merger...\n";

    }
    private function insertComprehensiveListOfDhis2FacilitiesIntoDB($final_facilities){
        $districts_with_hubs=$this->getDistrictsWithHubs();

        $counter=0;
        foreach ($final_facilities as $facility){
            try{


                $facility_uid = $facility['facility_uid'];
                $facility_name = $facility['facility_name'];
                $facility_name = addslashes($facility_name);
                $district_uid=$facility['district_uid'];

                //skip first row of headers
                if($facility_name == "Facility_name"){
                    continue;
                }

                //remove those facilities that aren't present in the db
                $isExisiting = $this->isFacilitiyInDB($facility_uid);
                if($isExisiting){
                    continue;
                }

                //insert
                $db_district_id = $this->getDistictDbId($district_uid);

                
                $hub = $this->getHubDetailsUsingDistrictUid($districts_with_hubs,$district_uid);
                
                        
                if(empty($hub)){
                    continue;
                 }
                $hub_object = $hub[0];
                $db_hub_id=$hub_object->id;
                $db_ip_id=$hub_object->ipID;

                $sql="insert into vl_facilities(district_uid,dhis2_uid,facility,dhis2_name,districtID,hubId,ipID
                            ,phone,contactPerson,physicalAddress,returnAddress,active,created,createdBy) 
                            values('$district_uid','$facility_uid','$facility_name','$facility_name',
                            $db_district_id,$db_hub_id,$db_ip_id,'0000000','No name','imported from DHIS2','None','1','2017-05-15 03:04:03',
                            'smuwanga@musph.ac.ug')";

                
                $affectedRows =  \DB::connection('live_db')->insert($sql);
                $counter++;
                
            }catch(Exception $e){
                 echo "\n ".$ex->getMessage()." \n";
           }
        }//end foreach loop
        echo "\n ----inserted facility: $counter-------";

    }

    private function isFacilitiyInDB($facility_uid){
        $isExisiting=false;
        $sql = "SELECT count(*) count FROM vl_facilities where dhis2_uid like '$facility_uid'";
        $rows =  \DB::connection('live_db')->select($sql);
        
        if(!empty($rows)){
            $rowsObject = $rows[0];
            $countOfRows = $rowsObject->count;
            if($countOfRows > 0){
                $isExisiting=true;
            }
        }
        return $isExisiting;
    }
    private function getVlFacilities(){
        $vl_file = fopen("/Users/simon/projects/METS/viral-load-dashboard/cphl/vl_facilities.csv", "r");
        $vl_facilities = array();
         while (!feof($vl_file)) {
            $array_instance = fgetcsv($vl_file);

            $facility['id']=$array_instance[0];
            $facility['name']=$array_instance[1];
            $facility['district_id']=$array_instance[2];
            array_push($vl_facilities, $facility);
         }

         return $vl_facilities;
    }


    private function getDistrictName($id){
        $sql="SELECT district FROM vl_districts where id=".$id;
        $district =  \DB::connection('live_db')->select($sql);
        if(sizeof($district) == 0){
            return null;
        }
        $district_object = $district[0];
        $district_name = get_object_vars($district_object);
        
        foreach ($district_name as $key => $value) {
           return $value;
        }
        
    }
    private function getComprehensiveListOfDhis2Facilities(){
        $file = fopen("/Users/simon/projects/METS/viral-load-dashboard/cphl/comprehensive_dhis2_facilities.csv", "r");
        $data = array();
        //loading CSV entire data
       
        
        while ( ! feof($file )) {

            $array_instance = fgetcsv($file);
            //print_r($array_instance);

             
                $facility['facility_uid']=$array_instance[0];
                $facility['facility_name']=$array_instance[1];

                $facility['facility_uid2']=$array_instance[2];
                $facility['level']=$array_instance[3];
                

                $facility['ownership']=$array_instance[4];
                $facility['authority']=$array_instance[5];

                $facility['coordinates']=$array_instance[6];
                $facility['Subcounty_uid']=$array_instance[7];

                $facility['subcounty_name']=$array_instance[8];
                $facility['district_uid']=$array_instance[9];

                $facility['district_name']=$array_instance[10];
                $facility['region_uid']=$array_instance[11];

                $facility['region_name']=$array_instance[12];
                
                array_push($data, $facility);
             
            
        }
        
        //remove duplicates
        $facilities = $this->unique_multidim_array($data,'facility_uid'); 

        return $facilities;
    }
    private function getDhis2Facilities(){
        $file = fopen("/Users/simon/projects/METS/viral-load-dashboard/cphl/dhis2_facilities.csv", "r");
        $data = array();
        //loading CSV entire data
       
        
        while ( ! feof($file )) {

            $array_instance = fgetcsv($file);
            //print_r($array_instance);

             
                $facility['facility_id_cphl']=$array_instance[0];
                $facility['facility_CPHL']=$array_instance[1];
                $facility['level']=$array_instance[2];
                $facility['hub']=$array_instance[3];
                $facility['DHIS2_Facility_Sync']=$array_instance[4];
                $facility['level_DHIS2']=$array_instance[5];
                $facility['facility_uid']=$array_instance[6];
                
                $facility['coordinates_DHIS2']=$array_instance[7];
                $facility['ownership_DHIS2']=$array_instance[8];
                $facility['authority_DHIS2']=$array_instance[9];
                $facility['subcounty_DHIS2']=$array_instance[10];
                $facility['subcounty_uid']=$array_instance[11];
                $facility['district_CPHL']=$array_instance[12];
                $facility['district_DHIS2']=$array_instance[13];
                $facility['district_uid']=$array_instance[14];
                $facility['region_CPHL']=$array_instance[15];
                $facility['region_DHIS2']=$array_instance[16];
                $facility['region_uid']=$array_instance[17];
                
                array_push($data, $facility);
             
            
        }
        
        //remove duplicates
        $facilities = $this->unique_multidim_array($data,'facility_uid'); 

        return $facilities;
    }

    /**
    *$facility_id_cphl=0
    *$facility_uid=1;
    *$facility_name=2;
    *$facility_uid=3;
    *$Level=4;
    *$Ownership=5;
    *$Authority=6;
    *$Coordinates=7;
    *$Subcounty_uid=8;
    *$Subcounty_name=9;
    *$District_uid=10;
    *District_name=11;
    *$Region_uid=12;
    *$Region_name=13;
    */
    private function updateDistrictWithDHIS2Data()
    {
        
        $file = fopen("/Users/simon/projects/METS/viral-load-dashboard/cphl/dhis2.csv", "r");
        $data = array();
        //loading CSV entire data
        while ( ! feof($file )) {
            
            //print_r(fgetcsv($file));
            
            
            $array_instance = fgetcsv($file);
            
            $facility['district_uid']=$array_instance[9];
            $facility['district_name']=$array_instance[10];
            $facility['region_uid']=$array_instance[11];
            $facility['region_name']=$array_instance[12];

            //skip first row of fields
            if ($facility['district_uid'] == 'District_uid') {
                echo "first row of columns names skipped\n";
                continue;
            }
            array_push($data, $facility);
        }
        
        //remove duplicates
        $districts = $this->unique_multidim_array($data,'district_uid'); 
        var_dump($districts);
        //upgrade District Data
        //add the data to the tables.
        
        foreach ($districts as $district_object) {
            $dhis2_uid=$district_object['district_uid'];
            $dhis2_name_original=$district_object['district_name'];
            $dhis2_name_array=explode(" ",$dhis2_name_original);
            $dhis2_name = trim($dhis2_name_array[0]);
            $sql = "update vl_districts set dhis2_uid='".$dhis2_uid."',dhis2_name='".$dhis2_name_original."' where district like '".$dhis2_name."' ";
            $affectedDistricts =  \DB::connection('live_db')->update($sql);

            if ($affectedDistricts == 0) {
               $sql = "insert into vl_districts (dhis2_uid,district,dhis2_name) values('".$dhis2_uid."','".$dhis2_name."','".$dhis2_name."')";
               $affectedDistricts =  \DB::connection('live_db')->insert($sql);
            }
            //print_r($sql);
        }
        echo "Sucessfully updated districts\n";
    }
}
