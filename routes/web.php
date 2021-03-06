<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', 'HomeController@index');
Route::get('/test2', 'TransactionController@index');

Route::match(['get', 'post'], '/transactions', 'TransactionController@index')->name('showtransactions');
Route::get('/transactions/create', 'TransactionController@create');
Route::match(['get', 'post'], '/transactions/load', 'TransactionController@load')->name('loadtransaction');