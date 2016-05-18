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
        $this->_loadData();
        // $this->_loadHubs();
        $this->comment("Engine has stopped at :: ".date('YmdHis'));

    }

    private function _loadData(){
        $year=2013;
        $current_year=date('Y');
        while($year<=$current_year){
            $samples=LiveData::getSamples($year);
            /*print_r($samples);
            break;*/
            $dbs_samples=LiveData::getSamples($year," sampleTypeID=1 ");
            $rjctn_rsns=LiveData::getRejects($year);
            $rjctn_rsns2=LiveData::getRejects2($year);

            $t_rslts=LiveData::getResults($year);
            $v_rslts=LiveData::getResults($year,$this->_validCases());
            $sprsd_cond=$this->_validCases()." AND ".$this->_suppressedCases();
            $sprsd=LiveData::getResults($year,$sprsd_cond);
            //mth,age_group,facilityID,sex,reg,trt

            $trmt_indctn=LiveData::getTrmtIndctn($year);

            foreach($samples AS $smpl){
                $smpl=(array)$smpl;
                $conds=[];
                $conds2=[];
                $sample_data = new SamplesData;
                $sample_data->year_month = $year.str_pad($smpl['mth'],2,0,STR_PAD_LEFT);
                $sample_data->age_group_id = $conds['age_group'] = $smpl['age_group'];
                $sample_data->facility_id = $conds['facilityID'] = $smpl['facilityID'];
                $sample_data->gender= $conds['sex'] = $smpl['sex'];
                $sample_data->treatment_indication = $conds['trt'] = $smpl['trt'];
                $sample_data->regimen_group_id = $conds['reg'] = $smpl['reg'];
                $sample_data->samples_received=$smpl['num'];
                
                $conds['mth'] = $conds2['mth'] =$smpl['mth'];
                $sample_data->dbs_samples = $this->_multi_search($dbs_samples,$conds,'num');
                $sample_data->rejected_samples = $this->_multi_search($rjctn_rsns,$conds,'num');
                $sample_data->sample_quality_rejections=$this->_multi_search($rjctn_rsns2,$conds+['rjctn_rsn'=>'quality_of_sample'],'num');
                $sample_data->incomplete_form_rejections=$this->_multi_search($rjctn_rsns2,$conds+['rjctn_rsn'=>'incomplete_form'],'num');
                $sample_data->eligibility_rejections=$this->_multi_search($rjctn_rsns2,$conds+['rjctn_rsn'=>'eligibility'],'num');

                $sample_data->total_results = $this->_multi_search($t_rslts,$conds,'num');
                $sample_data->valid_results = $this->_multi_search($v_rslts,$conds,'num');
                $sample_data->suppressed = $this->_multi_search($sprsd,$conds,'num');

                $sample_data->save();

                $trmt_indctn_obj = new TreatmentIndication;
                $trmt_indctn_obj->year_month=$conds2[]=$year.str_pad($smpl['mth'],2,0,STR_PAD_LEFT);
                $trmt_indctn_obj->age_group_id = $conds2['age_group'] = $smpl['age_group'];
                $trmt_indctn_obj->facility_id = $conds2['facilityID'] = $smpl['facilityID'];
                $trmt_indctn_obj->cd4_less_than_500 = $this->_multi_search($t_rslts,$conds2+['treatmentInitiationID'=>3],'num');
                $trmt_indctn_obj->pmtct_option_b_plus = $this->_multi_search($t_rslts,$conds2+['treatmentInitiationID'=>1],'num');
                $trmt_indctn_obj->children_under_15 = $this->_multi_search($t_rslts,$conds2+['treatmentInitiationID'=>2],'num');
                $trmt_indctn_obj->other_treatment = $this->_multi_search($t_rslts,$conds2+['treatmentInitiationID'=>5],'num');
                $trmt_indctn_obj->treatment_blank_on_form = $this->_multi_search($t_rslts,$conds2+['treatmentInitiationID'=>0],'num');
                $trmt_indctn_obj->tb_infection = $this->_multi_search($t_rslts,$conds2+['treatmentInitiationID'=>4],'num');
                
                $trmt_indctn_obj->save();
            }

            $year++;

        }


       /* $this->_loadSampleData(); 
        $this->_loadRegimenData();
        $this->_loadTreatmentIndicationData();*/
    }

    private function _loadSampleData(){

    }

    private function _loadRegimenData(){

    }

    private function _loadTreatmentIndicationData(){

    }

    private function _loadHubs(){
        $hubs=LiveData::getHubs();
        foreach ($hubs as $hub) {
            $hb=new Hub; 
            $hb->hub_id=$hub->id;         
            $hb->name=$hub->hub;
            $hb->save();
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
}
