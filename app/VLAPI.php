<?php

namespace EID;


class VLAPI{

	public static function get($resouce="", $params=[]){
		$params_str="?";
		foreach ($params as $param_key => $param_value) {
			$params_str .= "$param_key=$param_value&";			
		}
		$params_str = substr($params_str, 0, -1);
        $api = env('API')."/api/$resouce/$params_str";
        $api_key = env('API_KEY');
        $results = exec("curl -X GET '$api' -H 'Authorization: Token $api_key'");
        return $results;
        //return "curl -X GET '$api' -H 'Authorization: Token $api_key'";
    }

    public static function get_orderby($cols){
    	$order = \Request::get('order');
    	$order_by = null;		
		if(isset($order[0])){
			$col = $cols[$order[0]['column']];
			$dir = $order[0]['dir'];
			$order_by = $dir=='ASC'?"$col":"-$col";
		}
		return $order_by;
    }


}

//$client = new MongoClient("mongodb://user:pass@localhost/db");