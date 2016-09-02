<?php

namespace EID\Console\Commands;

use Illuminate\Console\Command;
use EID\LiveData;
use EID\Hub;
use EID\Facility;
use EID\Ip;
use EID\District;
use EID\Dashboard;

use EID\TreatmentIndication;
use EID\SamplesData;
use EID\RegimenData;
use EID\Mongo;


class Engine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engine:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Engine to help generate data for the dashboard';

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
        //
        $this->comment("Engine has started at :: ".date('YmdHis'));
        //
        //$this->mongo->drop(); 
        $this->_loadHubs();
        $this->_loadDistricts();
        $this->_loadFacilities();
        $this->_loadIPs();
        $this->_loadData();

        $this->comment("Engine has stopped at :: ".date('YmdHis'));

    }

    private function _loadData(){
        $this->mongo->dashboard_data->drop();
        $year=2013;
        $current_year=date('Y');
        $facilities_arr=LiveData::getFacilities2();
        while($year<=$current_year){
            $samples=LiveData::getSamples($year);
            $dbs_samples=LiveData::getSamples($year," sampleTypeID=1 ");

            $dbs_number_of_patients_received = LiveData::getNumberOfPatients($year, " sampleTypeID=1");
            $number_of_patients_received = LiveData::getNumberOfPatients($year);

            $rjctn_rsns=LiveData::getRejects($year);
            $rjctn_rsns2=LiveData::getRejects2($year);

            $t_rslts=LiveData::getResults($year);
            $v_rslts=LiveData::getResults($year,$this->_validCases());
            $sprsd_cond=$this->_validCases()." AND ".$this->_suppressedCases();
            $sprsd=LiveData::getResults($year,$sprsd_cond); 
            $i=0;
            foreach($samples AS $s){
                $key=$s->mth.$s->age_group.$s->facilityID.$s->sex;
                $key.=$s->reg_type.$s->reg_line.$s->reg_time.$s->trt;

                $data=[];
                //filter params
                $y_m = $year.str_pad($s->mth,2,0,STR_PAD_LEFT);
                $data["year_month"] = (int)$y_m;
                $data["age_group_id"] = isset($s->age_group)?(int)$s->age_group:0;
                $data["facility_id"] = isset($s->facilityID)?(int)$s->facilityID:0;
                if(isset($s->facilityID)){
                    $f_obj=isset($facilities_arr[$s->facilityID])?$facilities_arr[$s->facilityID]:new \stdClass;
                    $data['district_id']=isset($f_obj->districtID)?(int)$f_obj->districtID:0;
                    $data['hub_id']=isset($f_obj->hubID)?(int)$f_obj->hubID:0;
                    $data['ip_id']=isset($f_obj->ipID)?(int)$f_obj->ipID:0;
                }else{ 
                    $data['district_id']=0;
                    $data['hub_id']=0;
                    $data['ip_id']=0;
                }
                $data["gender"] = isset($s->sex)?$s->sex:0; 
                $data["treatment_indication_id"] = isset($s->trt)?(int)$s->trt:0;
                $data["regimen_group_id"] = isset($s->reg_type)?(int)$s->reg_type:0;
                $data["regimen_line"] = isset($s->reg_line)?(int)$s->reg_line:0;
                $data["regimen_time_id"] = isset($s->reg_time)?(int)$s->reg_time:0;

                //numbers
                $data["samples_received"] = isset($s->num)?(int)$s->num:0;
                $data["dbs_samples"] = isset($dbs_samples[$key])?(int)$dbs_samples[$key]:0;
                $data["rejected_samples"] = isset($rjctn_rsns[$key])?(int)$rjctn_rsns[$key]:0;

                #$data["patients_tested"] = isset($s->numberOfPatientsTested)?(int)$s->numberOfPatientsTested:0;
                $data["dbs_patients_received"] = isset($dbs_number_of_patients_received[$key])?(int)$dbs_number_of_patients_received[$key]:0;
                $data["patients_received"] = isset($s->number_patients_received)?(int)$s->number_patients_received:0;

                $data["sample_quality_rejections"]=isset($rjctn_rsns2[$key.'quality_of_sample'])?(int)$rjctn_rsns2[$key.'quality_of_sample']:0;
                $data["incomplete_form_rejections"]=isset($rjctn_rsns2[$key.'incomplete_form'])?(int)$rjctn_rsns2[$key.'incomplete_form']:0;
                $data["eligibility_rejections"]=isset($rjctn_rsns2[$key.'eligibility'])?(int)$rjctn_rsns2[$key.'eligibility']:0;

                $data["total_results"] = isset($t_rslts[$key])?(int)$t_rslts[$key]:0;
                $data["valid_results"] = isset($v_rslts[$key])?(int)$v_rslts[$key]:0;
                $data["suppressed"]= isset($sprsd[$key])?(int)$sprsd[$key]:0;
                $this->mongo->dashboard_data->insert($data);
                $i++;
                //echo "$i\n";                
            }
            echo " inserted $i records for $year\n";
            $year++;
        }
    }
/*
    private function _loadHubs(){
        $hubs=LiveData::getHubs();
        $sql="";
        foreach ($hubs as $hub) {
            $h=new \stdClass;
            $h->hub_id=$hub->id;
            $h->name=$hub->hub;
            $sql.=$this->_insertSQL($h,"hubs");
        }
        \DB::unprepared($sql);
    }

    private function _loadFacilities(){
        $res=LiveData::getFacilities();
        $sql="";
        foreach ($res as $row) {
            $f=new \stdClass;
            $f->facility_id=$row->id;         
            $f->name=$row->facility;
            $f->district_id=$row->districtID;
            $f->hub_id=$row->hubID;
            $f->ip_id=$row->ipID;
            $sql.=$this->_insertSQL($f,"facilities");
        }
        \DB::unprepared($sql);
    }

    private function _loadDistricts(){
        $res=LiveData::getDistricts();
        $sql="";
        foreach ($res as $row) {
            $d=new \stdClass;
            $d->district_id=$row->id;         
            $d->name=$row->district;            
            $sql.=$this->_insertSQL($d,"districts");
        }
        \DB::unprepared($sql);
    }

    private function _loadIPs(){
        $res=LiveData::getIPs();
        $sql="";
        foreach ($res as $row) {
            $p=new \stdClass;
            $p->ip_id=$row->id;         
            $p->name=$row->ip;            
            $sql.=$this->_insertSQL($p,"ips");
        }
        \DB::unprepared($sql);
    }
*/
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
            $data=['id'=>$row->id,'name'=>$row->facility,'hub_id'=>$row->hubID,'ip_id'=>$row->ipID,'district_id'=>$row->districtID];
            $this->mongo->facilities->insert($data);
        }
    }

    private function _loadIPs(){
        $this->mongo->ips->drop();
        $res=LiveData::getIPs();
        foreach($res AS $row){
            $data=['id'=>$row->id,'name'=>$row->ip];
            $this->mongo->ips->insert($data);
        }
    }

    private function _loadDistricts(){
        $this->mongo->districts->drop();
        $res=LiveData::getDistricts();
        foreach($res AS $row){
            $data=['id'=>$row->id,'name'=>$row->district];
            $this->mongo->districts->insert($data);
        }
    }

    private function _validCases(){
        $ret="";
        $cases=[
            "Failed",
            "Failed.",
            "Invalid",
            "Invalid test result. There is insufficient sample to repeat the assay.",
            "There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a a new sample.",
            "There is No Result Given. The Test Failed the Quality Control Criteria. We advise you send a new sample."];

        foreach ($cases as $v) {
            $ret.=" resultAlphanumeric NOT LIKE '$v' AND";
        }
        $ret=" (".substr($ret, 0,-3).") ";
        return $ret;    
    }

    private function _suppressedCases(){
        return " ((s.sampleTypeID=1 AND resultNumeric<=5000) OR (s.sampleTypeID=2 AND resultNumeric<=1000))";
    }


    private function _multi_search($arr=[],$conds=[],$ret_item=""){
        $filtered_arr=array_filter($arr,function($row) use($conds){
            foreach ($conds as $k => $v)  {
                if(property_exists($row,$k)){
                    if($row->$k!=$v) return false;
                }else{
                    return false;
                }
            }
            return true;
        });
        return current($filtered_arr)?current($filtered_arr)->$ret_item:0;
    }

    private function _insertSQL($data,$table){
        $columns_str=$values_str="";
        foreach ($data as $k => $v) {
            $columns_str.="`$k`,";
            $val=str_replace("'", "''", $v);
            $val=trim($val);
            $values_str.="'$val',"; 
        }
        $now=date("YmdHis");
        return "INSERT INTO `$table` ($columns_str `created_at`,`updated_at`) VALUES ($values_str '$now','$now');";
    }
}
