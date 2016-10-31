<?php

Route::get('/', array('uses' => 'HomeController@index'));

/* Authentication API */
Route::post('auth/login',             'AuthController@login');
Route::get('auth/login',             'AuthController@login');
Route::post('auth/signup',            'AuthController@signup');
Route::post('auth/facebook',          'AuthController@facebook');
Route::post('auth/google',            'AuthController@google');
Route::get('auth/unlink/{provider}',  array('before'=>'auth', 'uses'=>'AuthController@unlink'));


Route::group(array('prefix'=>'/v1'), function() {

    //Route::get('data/{userid}/{resource}/{time}',array('uses'=>'MobileApiController@getData'));
    Route::get('data/{userid}/{resource}/{time}',array('uses'=>'MobileApiController@getData'));
    Route::post('data/{userid}/{resource}',array( 'uses'=>'MobileApiController@saveData'));


    Route::post('reports/bedutilization', 'ReportController@bedUtilization');
    Route::get('reports/bedutilization',  'ReportController@bedUtilization');
    Route::get('reports/spline',          'ReportController@spline');
    Route::post('reports/spline',         'ReportController@spline');
    Route::resource('reports',            'ReportController');

    Route::get('log/orate',           'LogController@getORate');
    Route::get('log/otime',           'LogController@getOTime');

    Route::resource('location',       'LocationController');

    Route::get('hospital/period/{p}', 'HospitalController@periodInfo');
    Route::get('hospital/nurse/{id}', 'HospitalController@nurseInfo')->where('id','[0-9]+');
    Route::get('hospital/map',        'HospitalController@mapInfo');
    Route::get('hospital/tree',       'HospitalController@treeInfo');
    Route::get('hospital/byuser',      'HospitalController@getByUser');
    Route::get('hospital/bylocation',  'HospitalController@getByLocation');
    Route::get('hospital/status/{status}/{wid}', 'HospitalController@updateStatus');
    Route::resource('hospital',        'HospitalController');

    Route::resource('ward',       'WardController');

    Route::get('bed/{hid}/{wid}',     'BedController@getBedsByLocation');
    Route::post('bed/togglestatus',   'BedController@toggleStatus');
    Route::post('bed/updatestatus',   'BedController@updateStatus');
    Route::resource('bed',            'BedController');


    Route::get('me',                  'UserController@getUser');
    Route::put('me',                  'UserController@updateUser');
    Route::post('user',               'UserController@updateUserSimple');
    Route::get('user/{id}',           'UserController@show')->where('id', '[0-9]+');
    Route::get('user/verify',         'UserController@verify');
    Route::resource('user',           'UserController');
});

Blade::extend(function($value) {
    return preg_replace('/\{\?(.+)\?\}/', '<?php ${1} ?>', $value);
});
