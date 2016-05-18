<?php

namespace EID;

use Illuminate\Database\Eloquent\Model;

class Dashboard extends Model
{
    //
    protected $table = 'data';


    public static function getSampleData($fro_date,$to_date){
    	return Dashboard::select("*")->from("data")
    					->where("year_month",">=",$fro_date)
    					->where("year_month","<=",$to_date)
    					->get()->toJson();
	}


	public static function saveStuff($arr){
		$obj=new Dashboard;
		foreach ($arr as $key => $value) {
			$obj->$key=$value;
		}
		$obj->save();
	}
}
