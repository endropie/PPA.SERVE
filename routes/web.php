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

Route::get('/calculate-delivery', function() {
    return App\Models\Common\ItemStock::deliveryTransferAmount();
});

Route::get('/check-delivery', function() {
    return App\Models\Common\ItemStock::deliveryCheckAmount();
});

Route::get('/test-incoming-good', function() {
    // vendor/bin/phpunit --testdox --filter 'Tests\\Feature\\IncomingGoodTest'
});

Route::get('migrate', function (Request $request) {
    if (empty(request('key'))) dd('NOT FOUND KEY');

    $print = [];

    Artisan::call('migrate:fresh', ['--seed' => true]);
    $print[] = Artisan::output();

    Artisan::call('migrate',['--path' => 'vendor/laravel/passport/database/migrations','--force' => true]);
    $print[] = Artisan::output();

    Artisan::call('passport:install');
    $print[] = Artisan::output();

    dd($print);
});

Auth::routes();

Route::get('/', function () { return view('welcome'); });

Route::get('/home', 'HomeController@index')->name('home');
