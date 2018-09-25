<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register nginx routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "nginx" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::middleware('auth')->group(function () {
	Route::get('/home', 'HomeController@index')->name('home');
	
	Route::get('/nginx', 'NginxController@index')->name('nginx');
	Route::get('/nginx/{domain}', 'NginxController@show')->name('nginx.deep');
	Route::post('/nginx/search', 'NginxController@search')->name('nginx.search');
	
	Route::get('/apache2', 'Apache2Controller@index')->name('apache2');
	Route::get('/apache2/{domain}', 'Apache2Controller@show')->name('apache2.deep');
	Route::post('/apache2/search', 'Apache2Controller@search')->name('apache2.search');
	
	Route::get('/query', 'QueryController@index')->name('query');
});

