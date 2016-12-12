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

Route::controllers([
    'results'       => 'ResultsController',
]);

Route::match(array('GET', 'POST'), '/result/{id?}/', ['as' => 'result', 'uses' => 'ResultsController@getResult']);

Route::get("/","DashboardController@init");

Route::get("/live","DashboardController@live");

Route::get("/other_data/","DashboardController@other_data");

//Route::post('/downloadCsv', 'DashboardController@downloadCsv');

/*Route::get('/w',function(){
	return view("welcome");
});*/