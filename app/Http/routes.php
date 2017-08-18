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

Route::get('/home', function(){ return "Access denied"; });

//Route::get('/',function(){ return view('db/show'); });

//Route::get('/{time?}',"DashboardController@show");
Route::group(['middleware' => 'auth'], function()
{	
	Route::group(['middleware' => ['role:admin']], function() {
		// Registration routes...
		Route::match(array('GET', 'POST'), '/admin/create_user', [ 'uses' => 'AdminController@create_user']);
		Route::match(array('GET', 'POST'), '/admin/user_edit/{id}', [ 'uses' => 'AdminController@edit_user']);
		Route::get('/admin/user_pass_reset/{id}', [ 'uses' => 'AdminController@pass_reset']);
		
		#Route::get('admin/list_users', 'AdminController@list_users');	

		Route::controllers(['admin/list_users' => 'AdminController']);
		Route::controllers(['logs' => 'LogsController']);  
	});

	Route::group(['middleware'=>['permission:monitoring']], function(){
		Route::controllers(['/monitor' => 'MonitoringController']);
		Route::controllers(['/monitor_download' => 'MonitorDownloadController']);
		Route::get('/monitor_summary', function(){ $res = EID\WorksheetResults::getSummary(); return view('results.monitor_summary', compact('res')); });
	});

	Route::group(['middleware'=>['permission:print_results']], function() { 
		Route::controllers(['results' => 'FacilityListController']);
		Route::controllers(['results_list' => 'ResultsController']);
		#Route::get('/results', ['as' => 'facilities', 'uses' => 'ResultsController@facilities']);
		Route::match(array('GET', 'POST'), '/result/{id?}/', [ 'as' => 'result', 'uses' => 'ResultsController@getResult']);
		Route::get('/log_printing/',['as' => 'log_printing', 'uses'=>'ResultsController@log_printing']);
		Route::get('/print_envelope/{id}', ['as' => 'print_envelope', 'uses' => 'ResultsController@print_envelope']);
		Route::get('/searchbyhub/{txt}', ['as' => 'searchbyhub', 'uses' => 'ResultsController@searchbyhub']);
		Route::get('/search_result/{txt}', ['as' => 'search_result', 'uses' => 'ResultsController@search_result']);

	});

	Route::group(['middleware'=>['permission:qc']], function() { 
		Route::controllers(['/qc' => 'QCController']);
		Route::get('/data_qc/{id}/', ['as' => 'data_qc', 'uses' => 'QCController@data_qc']);
		Route::post('/data_qc/{id}/', ['as' => 'data_qc_post', 'uses' => 'QCController@data_qc']);
		Route::get('/qc/wk_search/{q}/', [ 'as' => 'qc_worksheet_search', 'uses' => 'QCController@worksheet_search']);
		Route::get('/sample/{id}', ['as' => 'sample', 'uses' => 'QCController@sample']);
		Route::get('/qc/byhub/{id}', ['as' => 'qcbyhub', 'uses' => 'QCController@byhub']);
		Route::get('/qc/byfacility/{id}', ['as' => 'qcbyfacility', 'uses' => 'QCController@byfacility']);
		
		Route::get('/qc_rejected/{rejection_date}', ['as' => 'qc_rejected', 'uses' => 'QCController@qc_rejected']);
		Route::get('/qc_rejected_save/{sample_id}/', ['as' => 'qc_rejected_sample', 'uses' => 'QCController@qc_rejected_sample']);
		
	});

	Route::group(['middleware'=>['permission:lab_qc']], function() { 
		Route::controllers(['/lab_qc/index' => 'LabQCController']);
		Route::match(array('GET', 'POST'), '/lab_qc/qc/{id}/', ['middleware' => ['permission:lab_qc'], 'as' => 'labqc_qc', 'uses' => 'LabQCController@qc']);	
	});


    Route::group(['middleware'=>['permission:view_reports_as_facility|view_reports_as_hub']], function() { 
		Route::get('/suppression_trends/index', ['uses' => function(){ return view('suppression_trends.index'); }]);
		Route::get('/suppression_trends/reports', ['uses' => 'ResultsController@getPatientResults']);
	});


	Route::match(array('GET', 'POST'),'/change_password',['uses'=>'AdminController@change_password']);
	//Route::get('/qc', ['middleware' => ['permission:qc'], 'as' => 'qc', 'uses' => 'QCController@index']);

});
Route::get("/","DashboardController@init");

Route::get("/live","DashboardController@live");

Route::get("/other_data/","DashboardController@other_data");

Route::get('/pdf_test', function(){
	$data = "testing pdf stuff";
	$pdf = PDF::loadView('pdf.test', compact("data"));
	return $pdf->download('pdf_test.pdf');
});

//Route::post('/downloadCsv', 'DashboardController@downloadCsv');

/*Route::get('/w',function(){
	return view("welcome");
});*/