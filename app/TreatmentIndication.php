<?php

namespace EID;

use Illuminate\Database\Eloquent\Model;

class TreatmentIndication extends Model
{
    //
    protected $table = 'treatment_indication';

    public static function getTrtmtIndctn($fro_date,$to_date){
    	return self::select("*")->from("treatment_indication")
    				->where("year_month",">=",$fro_date)
    				->where("year_month","<=",$to_date)
    				->get()->toJson();
	}

	public static function getTIData($cols="",$conds="1",$group_by=""){
		$res=self::leftjoin("facilities AS f","f.facility_id","=","t.facility_id")
				 ->select(\DB::raw("$cols"))->from("treatment_indication AS t")
				 ->whereRaw($conds);
		$res=!empty($group_by)?$res->groupby($group_by):$res;
		return $res->get();
	}
}
