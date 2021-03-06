<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix' => 'v1'], function() {
    Route::group(['prefix' => 'video'], function() {
        Route::put('store', 'VideoController@store');
        Route::put('archive', 'VideoController@archive');
        Route::put('now_playing', 'VideoController@now_playing');

        Route::delete('delete/{video_id}', 'VideoController@delete');
        Route::delete('stop_playing', 'VideoController@stop_playing');

        Route::post('update', ['uses' => 'VideoController@update', 'middleware' => 'web']);
    });

    Route::group(['prefix' => 'vote'], function() {
        Route::post('start', 'VoteController@start');
        Route::post('skip', 'VoteController@skip');
    });
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::get('/', function () {
        return view('index');
    });

    Route::get('receiver', function () {
        return view('receiver');
    });
});
