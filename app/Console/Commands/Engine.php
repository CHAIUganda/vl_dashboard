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

        /*$this->_loadHubs();
        $this->_loadDistricts();
        $this->_loadFacilities();
        $this->_loadIPs();*/
        $this->_loadData();

        $this->comment("Engine has stopped at :: ".date('YmdHis'));

    }

    private function _loadData(){
        $year=2013;
        $current_year=date('Y');
        while($year<=$current_year){
            $samples=LiveData::getSamples($year);
            $dbs_samples=LiveData::getSamples($year," sampleTypeID=1 ");
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

                $sample_data = new SamplesData;
                //filter params
                $sample_data->year_month = $year.str_pad($s->mth,2,0,STR_PAD_LEFT);
                $sample_data->age_group_id = isset($s->age_group)?$s->age_group:0;
                $sample_data->facility_id = isset($s->facilityID)?$s->facilityID:0;
                $sample_data->gender = isset($s->sex)?$s->sex:0; 
                $sample_data->treatment_indication_id = isset($s->trt)?$s->trt:0;
                $sample_data->regimen_group_id = isset($s->reg_type)?$s->reg_type:0;
                $sample_data->regimen_line = isset($s->reg_line)?$s->reg_line:0;
                $sample_data->regimen_time_id = isset($s->reg_time)?$s->reg_time:0;

                //numbers
                $sample_data->samples_received = isset($s->num)?$s->num:0;
                $sample_data->dbs_samples = isset($dbs_samples[$key])?$dbs_samples[$key]:0;
                $sample_data->rejected_samples = isset($rjctn_rsns[$key])?$rjctn_rsns[$key]:0;

                $sample_data->sample_quality_rejections=isset($rjctn_rsns2[$key.'quality_of_sample'])?$rjctn_rsns2[$key.'quality_of_sample']:0;
                $sample_data->incomplete_form_rejections=isset($rjctn_rsns2[$key.'incomplete_form'])?$rjctn_rsns2[$key.'incomplete_form']:0;
                $sample_data->eligibility_rejections=isset($rjctn_rsns2[$key.'eligibility'])?$rjctn_rsns2[$key.'eligibility']:0;

                $sample_data->total_results = isset($t_rslts[$key])?$t_rslts[$key]:0;
                $sample_data->valid_results = isset($v_rslts[$key])?$v_rslts[$key]:0;
                $sample_data->suppressed = isset($sprsd[$key])?$sprsd[$key]:0;
                $sample_data->save();
                $i++;
                //echo "$i\n";                
            }
            echo " inserted $i records for $year\n";
            $year++;
        }
    }

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
