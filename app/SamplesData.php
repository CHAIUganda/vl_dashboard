<?php

namespace EID;

use Illuminate\Database\Eloquent\Model;

class SamplesData extends Model
{
    //
    protected $table = 'samples_data';

    public static function getSampleData($fro_date,$to_date){
    	return self::select("*")->from("samples_data")
    				->where("year_month",">=",$fro_date)
    				->where("year_month","<=",$to_date)
    				->get()->toJson();
	}


	public static function getSamplesData($cols="",$conds="1",$group_by=""){
		$res=self::leftjoin("facilities AS f","f.facility_id","=","s.facility_id")
				 ->leftjoin("districts AS d","d.district_id","=","f.district_id")
				 ->select(\DB::raw("$cols"))->from("samples_data AS s")
				 ->whereRaw($conds);
		$res=!empty($group_by)?$res->groupby($group_by):$res;
		return $res->get();
	}
}
