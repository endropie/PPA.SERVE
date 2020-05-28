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


Route::middleware('auth')->get('/user', function () {
    return auth()->user;
});

Route::middleware('auth')->group(function () {

    Route::get('/calculate-delivery', function() {
        return App\Models\Common\ItemStock::deliveryTransferAmount();
    });

    Route::get('/check-delivery', function() {
        return App\Models\Common\ItemStock::deliveryCheckAmount();
    });

    Route::get('/test-incoming-good', function() {
        // vendor/bin/phpunit --testdox --filter 'Tests\\Feature\\IncomingGoodTest'
    });
});


Accurate::routes();
Auth::routes();

Route::get('/', function () { return view('welcome'); });

Route::get('/home', 'HomeController@index')->name('home');
