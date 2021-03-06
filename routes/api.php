<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'products', 'middleware' => 'auth:api'], function () {
    Route::get('/', 'ProductController@index');
    Route::get('/search', 'ProductController@search');
    Route::post('/', 'ProductController@store');
    Route::post('/{id}', 'ProductController@update');
    Route::get('/count', 'ProductController@count');
    Route::delete('/{id}', 'ProductController@destroy');
});
