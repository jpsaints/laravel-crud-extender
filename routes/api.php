<?php

Route::middleware('auth:api')->group(function () {

	Route::get('test', 'TestController@index');
	Route::get('test/{id}', 'TestController@show');
	Route::post('test', 'TestController@store');
	Route::put('test/{id}', 'TestController@update');
	Route::delete('test/{id}', 'TestController@destroy');

});