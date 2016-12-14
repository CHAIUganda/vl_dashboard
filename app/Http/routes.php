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

// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');


//Route::get('/',function(){ return view('db/show'); });

//Route::get('/{time?}',"DashboardController@show");
Route::group(['middleware' => 'auth'], function()
{	
	Route::controllers([
	    'results'       => 'ResultsController',
	]);

	Route::match(array('GET', 'POST'), '/result/{id?}/', ['as' => 'result', 'uses' => 'ResultsController@getResult']);
	Route::get('/qc', ['as' => 'qc', 'uses' => 'QCController@index']);
	Route::match(array('GET', 'POST'), '/qc/{id}', ['as' => 'qc', 'uses' => 'QCController@qc']);
	Route::get('/qc/wk_search/{q}/', ['as' => 'qc_worksheet_search', 'uses' => 'QCController@worksheet_search']);
});
Route::get("/","DashboardController@init");

Route::get("/live","DashboardController@live");

Route::get("/other_data/","DashboardController@other_data");

//Route::post('/downloadCsv', 'DashboardController@downloadCsv');

/*Route::get('/w',function(){
	return view("welcome");
});*/