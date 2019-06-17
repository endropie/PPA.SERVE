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

Route::get('set', function(){
    setting()->set([
        'app.name'                      => 'ADMIN PLAY',
        'app.logo'                      => '/logo.jpg',
        'app.logo_thumbnail'            => '/logo-thumbnail.jpg',
    ]);
    setting()->save();
});

Route::middleware('auth')->get('/test', function (Request $request) {
    return 'logined'; //$request->user();
});

Route::prefix('v1')->namespace('Api')->group(function() {

    Route::post('login', 'Auth\Authentication@login');
    Route::post('register', 'Auth\Authentication@register');
    // Route::post('auth', 'Auth\Authentication@user');

    Route::middleware([
        'auth:api'
        ])->group( function(){
    
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::middleware(['auth:api'])->group( function(){
                Route::name('user')->post('/', 'Auth\Authentication@user');
                Route::name('valid-token')->post('/valid-token', 'Auth\Authentication@validToken')->middleware(['auth:api']);
                Route::name('change-password')->post('/change-password', 'Auth\Authentication@setChangePassword');
            });
            Route::apiResource('users', 'Auth\Users');
            Route::apiResource('roles', 'Auth\Roles');
            Route::apiResource('permissions', 'Auth\Permissions');
        });

        Route::prefix('accounting')->name('accounting.')->group(function () {
        
            Route::apiResource('accounts', 'Accounting\Accounts');
            // Route::apiResource('account-type', 'Accounting\AccountTypes');
            // Route::apiResource('journals', 'Accounting\Journals');
            // Route::post('journals/import', 'Accounting\Journals@setImport');
    
            // Route::get('journal-entries', 'Accounting\Journals@entries');
            // Route::get('reports/ProfitLoss', 'Accounting\reports@viewProfitLoss');
            // Route::get('reports/BalanceSheet', 'Accounting\reports@viewBalanceSheet');
        });
    
        Route::prefix('common')->name('common.')->group(function () {
            Route::apiResource('items', 'Common\Items');
        });
        
        Route::prefix('incomes')->name('incomes.')->group(function () {
            Route::apiResource('customers', 'Incomes\Customers');
            Route::apiResource('forecasts', 'Incomes\Forecasts');
            Route::apiResource('request-orders', 'Incomes\RequestOrders');
            Route::apiResource('pre-deliveries', 'Incomes\PreDeliveries');
            Route::apiResource('ship-deliveries', 'Incomes\ShipDeliveries');
            Route::apiResource('ship-delivery-items', 'Incomes\ShipDeliveryItems');
            Route::patch('delivery-orders/{delivery_order}/revision', 'Incomes\DeliveryOrders@revision');
            Route::apiResource('delivery-orders', 'Incomes\DeliveryOrders');
        });
    
        Route::prefix('warehouses')->name('warehouses.')->group(function () {
            Route::apiResource('transports', 'Warehouses\Transports');
            Route::apiResource('incoming-goods', 'Warehouses\IncomingGoods');
        });
    
        Route::prefix('factories')->name('factories.')->group(function () {
            Route::post('work-orders/newgroup', 'Factories\WorkOrders@storeGroup');
    
            Route::apiResource('workin-productions', 'Factories\WorkinProductions');
            Route::apiResource('work-orders', 'Factories\WorkOrders');
            Route::apiResource('packings', 'Factories\Packings');
        });
    
        Route::prefix('references')->name('references.')->group(function () {
            Route::apiResource('operators', 'References\Operators');
            Route::apiResource('vehicles', 'References\Vehicles');
            Route::apiResource('faults', 'References\Faults');
            Route::apiResource('type_faults', 'References\TypeFaults');
            Route::apiResource('lines', 'References\Lines');
            Route::apiResource('shifts', 'References\Shifts');
            Route::apiResource('provinces', 'References\Provinces');
            Route::apiResource('units', 'References\Units');
            Route::apiResource('sizes', 'References\Sizes');
            Route::apiResource('brands', 'References\Brands');
            Route::apiResource('colors', 'References\Colors');
            Route::apiResource('type_items', 'References\TypeItems');
            Route::apiResource('category_items', 'References\CategoryItems');
            Route::apiResource('specifications', 'References\Specifications');
        });

    });   
});