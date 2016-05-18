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

Route::get('/vdash/{fro_date?}/{to_date?}',"DashboardController@dash");

Route::get('/poll/',function(){
	return view('vdash');
});

/*Route::get('/w',function(){
	return view("welcome");
});*/

