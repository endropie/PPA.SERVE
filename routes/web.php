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

Auth::routes();
Accurate::routes();

Route::get('/accurate-test', function () {
    // $customer = \App\Models\Income\Customer::first();
    // $response = $customer->accurate()->push();
    $customers = \App\Models\Income\Customer::whereNull('accurate_model_id')->get();
    $customers->each(function($customer) {
        $customer->accurate()->push();
    });
    return response()->json(['status' => true, 'counter' => $customers->count()]);
});

Route::get('/', function () { return view('welcome'); });

Route::get('/home', 'HomeController@index')->name('home');
