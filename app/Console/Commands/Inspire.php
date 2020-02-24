<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use EID\LiveData;
use EID\User;

class Inspire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspire:run';

    /**
     * The console command description. 
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
 
        $this->db = \DB::connection('direct_db');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*
        $this->comment(PHP_EOL.Inspiring::quote().PHP_EOL);
        $new_directory='hiv_drug_resistance/';
        $directory_path="./docs/".$new_directory;
        if (!is_dir($directory_path)) {
            mkdir($directory_path, 0777, true);
        }

        //======
        $sample_id="019759/1810";
        $sample_id=str_replace("/", "_", $sample_id);
        $file_url=$directory_path.$sample_id.".json";
        $sample_result_file = fopen($file_url, "w") or die("Unable to open file!");
        $result_string = "";
        fwrite($sample_result_file, $result_string);
        
        fclose($sample_result_file);
        */
        
        //steps
        //load excel file into array
        $file = fopen("/Users/simon/data/vl/viralload_ewi_rwenzori.csv", "r");
        $rwenzori_patients = array();   
        array_push($rwenzori_patients, ['facility','district','art_number','sex','art_start_date','latest_viral_load_result']); 
 
        while ( !feof($file)){
            $array_instance = fgetcsv($file);
            //print_r($array_instance);
                $patient['facility']=$array_instance[0];
                $patient['district']=$array_instance[1];
                $patient['art_number']=$array_instance[2];
                $patient['sex']=$array_instance[3];
                

                $tester=$this->getPatientDetails($array_instance[1],$array_instance[0],$array_instance[2]);
                $patient_details_instance = null;
                foreach ($tester as $key => $value) {
                     $patient_details_instance = $value;
                }
              
                $patient['art_start_date']=isset($patient_details_instance->treatment_initiation_date) ? $patient_details_instance->treatment_initiation_date: 'N/A';
                $patient['latest_viral_load_result']=isset($patient_details_instance->result_alphanumeric) ? $patient_details_instance->result_alphanumeric: 'N/A';
                array_push($rwenzori_patients, $patient); 
            
        }
        fclose($file); 
        
        //generate new csv
        echo ".... generating csv...\n";
        $fp = fopen('/Users/simon/data/vl/viralload_ewi_rwenzori'.date('YmdHis').'.csv', 'w');
        foreach ($rwenzori_patients as $fields) {
             fputcsv($fp, $fields);
        }

        fclose($fp);

        
    }

    public function getPatientDetails($district,$facility,$art_number){

        $dummy_array_district = explode(" ", $district);
        $district_to_search = $dummy_array_district[0];

        $dummy_array_facility = explode(" ", $facility);
        $facility_to_search = $dummy_array_facility[0];

        $sql="SELECT s.id,s.vl_sample_id,s.treatment_initiation_date,s.created_at,
            r.result_alphanumeric,p.art_number,f.facility,d.district
            FROM vl_samples s
            left join vl_results r on r.sample_id = s.id 
            inner join vl_patients p on p.id = s.patient_id 
            inner join backend_facilities f on f.id=s.facility_id 
            inner join backend_districts d on d.id = f.district_id 

            where d.district like '".$district_to_search."%' and f.facility like '%".$facility_to_search."%' 
            and p.art_number like '".$art_number."' 
            order by s.created_at desc limit 1";


        return $this->db->select($sql);

    }

    
        
}
