<?php

namespace EID;


class Mongo{

	public static function connect(){
		$host=env('MONGO_HOST');
		$db=env('MONGO_DB');
		$user=env('MONGO_USER');
		$pass=env('MONGO_PWD');
		$client = new \MongoClient("mongodb://$user:$pass@$host/$db");
		return $client->$db;
	}


}

//$client = new MongoClient("mongodb://user:pass@localhost/db");