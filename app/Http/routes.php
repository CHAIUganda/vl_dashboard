<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//Route::get('/',function(){ return view('db/show'); });

//Route::get('/{time?}',"DashboardController@show");

Route::get("/","DashboardController@init");

Route::get('/vdash/{fro_date?}/{to_date?}',"DashboardController@dash");

Route::get('/poll/',function(){ return view('vdash'); });

Route::get("/districts","DashboardController@districts");

Route::get("/hubs","DashboardController@hubs");

Route::get("/facilities","DashboardController@facilities");

Route::get("/live","DashboardController@live");


Route::get("/other_data/","DashboardController@other_data");

Route::get("/immy",function(){
	$all=\Request::all();
	extract($all);
	//$dists=json_decode($districts);
	//print_r($districts);
	foreach ($districts as $k) echo $k;  
});

/*Route::get('/w',function(){
	return view("welcome");
});*/

