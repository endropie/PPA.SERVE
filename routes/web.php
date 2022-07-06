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

    Route::get('/test-incoming-good', function () {
        // vendor/bin/phpunit --testdox --filter 'Tests\\Feature\\IncomingGoodTest'
    });
});

Auth::routes();
Accurate::routes();

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/check-job', function () {
    Cache::lock('foo')->get(function () {
        echo "TEST LOCK";
    });
});
Route::get('/test-job', function () {

    \DB::beginTransaction();
    $id = request('id', 400001);
    $number = request('number', 0);
    $item = \App\Models\Common\Item::findOrFail($id);
    $item->update(["description" => null]);

    $x = \App\Jobs\TestJob::dispatch($item, $number)->onQueue($item->id);
    \DB::commit();



    return dd($x->job()->item);
});
