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

Route::prefix('v1')->namespace('Api')->group(function() {
    Route::name('app')->get('app', function() {
        return response()->json(setting()->all());
    });

    Route::prefix('landing')->name('landing.')->group(function () {
        Route::name('schedule-boards')->get('schedule-boards', 'Transports\ScheduleBoards@landing');
    });

    Route::name('login')->post('login', 'Auth\Authentication@login');
    Route::name('register')->post('register', 'Auth\Authentication@register');

    Route::middleware([
        'auth:api',
        ])->group( function(){

        Route::prefix('auth')->name('auth.')->group(function () {
            Route::middleware(['auth:api'])->group( function(){
                Route::name('user')->post('/', 'Auth\Authentication@user');
                Route::name('valid-token')->post('/valid-token', 'Auth\Authentication@validToken');
                Route::name('change-password')->post('/change-password', 'Auth\Authentication@setChangePassword');
                Route::name('logout')->post('logout', 'Auth\Authentication@logout');
            });
            Route::apiResource('users', 'Auth\Users');
            Route::apiResource('roles', 'Auth\Roles');
            Route::apiResource('permissions', 'Auth\Permissions');
        });

        Route::prefix('setting')->name('setting.')->group(function () {
            Route::post('/{name}', 'Setting@set');
        });

        Route::prefix('common')->name('common.')->group(function () {
            Route::get('items/stockables', 'Common\Items@stockables');
            Route::apiResource('items', 'Common\Items');
            Route::apiResource('employees', 'Common\Employees');
        });

        Route::prefix('incomes')->name('incomes.')->group(function () {
            Route::apiResource('customers', 'Incomes\Customers');
            Route::apiResource('forecasts', 'Incomes\Forecasts');
            Route::apiResource('request-orders', 'Incomes\RequestOrders');
            Route::apiResource('pre-deliveries', 'Incomes\PreDeliveries');
            Route::apiResource('delivery-orders', 'Incomes\DeliveryOrders');
        });

        Route::prefix('warehouses')->name('warehouses.')->group(function () {
            Route::apiResource('transports', 'Warehouses\Transports');
            Route::apiResource('incoming-goods', 'Warehouses\IncomingGoods');
            Route::apiResource('opnames', 'Warehouses\Opnames');
            Route::apiResource('opname-stocks', 'Warehouses\OpnameStocks');
            Route::apiResource('opname-vouchers', 'Warehouses\OpnameVouchers');
            Route::apiResource('outgoing-goods', 'Warehouses\OutgoingGoods');
            Route::apiResource('outgoing-good-verifications', 'Warehouses\OutgoingGoodVerifications');
        });

        Route::prefix('factories')->name('factories.')->group(function () {
            Route::get('work-orders/items', 'Factories\WorkOrders@items');

            Route::apiResource('work-productions', 'Factories\WorkProductions');
            Route::apiResource('work-orders', 'Factories\WorkOrders');
            Route::apiResource('packings', 'Factories\Packings');
        });

        Route::prefix('transports')->name('transports.')->group(function () {
            Route::apiResource('schedule-boards', 'Transports\ScheduleBoards');
        });

        Route::prefix('references')->name('references.')->group(function () {
            Route::apiResource('departments', 'References\Departments');
            Route::apiResource('positions', 'References\Positions');
            Route::apiResource('vehicles', 'References\Vehicles');
            Route::apiResource('faults', 'References\Faults');
            Route::apiResource('type-faults', 'References\TypeFaults');
            Route::apiResource('lines', 'References\Lines');
            Route::apiResource('shifts', 'References\Shifts');
            Route::apiResource('provinces', 'References\Provinces');
            Route::apiResource('units', 'References\Units');
            Route::apiResource('sizes', 'References\Sizes');
            Route::apiResource('brands', 'References\Brands');
            Route::apiResource('colors', 'References\Colors');
            Route::apiResource('type-items', 'References\TypeItems');
            Route::apiResource('category-items', 'References\CategoryItems');
            Route::apiResource('specifications', 'References\Specifications');
        });

    });
});
