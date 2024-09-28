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

use Endropie\AccurateClient\Facade as Accurate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->get('/user', function () {
    return auth()->user;
});


Auth::routes();
Accurate::routes();

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    app('db')->connection()->getPdo();
    return 'OK';
 });
