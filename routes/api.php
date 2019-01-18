<?php

use Illuminate\Http\Request;

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

Route::prefix('v1')->namespace('Api')->group(function() {
     
    Route::prefix('auth')->name('auth.')->group(function () {
        // Route::apiResource('admins', 'auth\adminApiController');
        // Route::apiResource('users', 'auth\userApiController');
        // Route::apiResource('roles', 'auth\RoleApiController');
        // Route::apiResource('permissions', 'auth\PermissionApiController');
    });

    Route::prefix('accounting')->name('.accounting')->group(function () {
        
        Route::apiResource('accounts', 'Accounting\Accounts');
        // Route::apiResource('account-type', 'Accounting\AccountTypes');
        // Route::apiResource('journals', 'Accounting\Journals');
        // Route::post('journals/import', 'Accounting\Journals@setImport');

        // Route::get('journal-entries', 'Accounting\Journals@entries');
        // Route::get('reports/ProfitLoss', 'Accounting\reports@viewProfitLoss');
        // Route::get('reports/BalanceSheet', 'Accounting\reports@viewBalanceSheet');
    });

    Route::apiResource('items', 'Items');
    
    Route::prefix('income')->name('.setting')->group(function () {
        Route::apiResource('customer', 'Customer');
    });

    Route::prefix('references')->name('.references')->group(function () {
        Route::apiResource('units', 'references\Units');
        Route::apiResource('categories', 'references\Categories');
        Route::apiResource('ordertypes', 'references\OrderTypes');
        Route::apiResource('brands', 'references\Brands');
        Route::apiResource('colours', 'references\Colours');
        Route::apiResource('specifications', 'references\Specifications');
    });
});