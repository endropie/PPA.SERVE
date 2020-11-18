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
        Route::post('uploads/file', 'Uploads@storeFile');
        Route::delete('uploads/file', 'Uploads@destroyFile');
        // Route::post('uploads/exist', 'Uploads@existFile');

        Route::prefix('auth')->name('auth.')->group(function () {
            Route::middleware(['auth:api'])->group( function(){
                Route::name('user')->post('/', 'Auth\Authentication@user');
                Route::name('valid-token')->post('/valid-token', 'Auth\Authentication@validToken');
                Route::name('confirm-password')->post('/confirm-password', 'Auth\Authentication@confirmPassword');
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
            Route::post('items/{id}/sample-validation', 'Common\Items@sampleValidation');
            Route::get('items/stockables', 'Common\Items@stockables');
            Route::apiResource('items', 'Common\Items');
            Route::apiResource('employees', 'Common\Employees');
        });

        Route::prefix('incomes')->name('incomes.')->group(function () {
            Route::get('delivery-orders/items', 'Incomes\DeliveryOrders@items');
            Route::put('delivery-internals/{id}/confirmed', 'Incomes\DeliveryInternals@confirmed');
            Route::put('delivery-internals/{id}/revised', 'Incomes\DeliveryInternals@revised');
            Route::put('delivery-loads/{id}/save-vehicle', 'Incomes\DeliveryLoads@vehicleUpdated');
            Route::post('customers/{id}/accurate/push', 'Incomes\Customers@push');
            Route::post('invoices/{id}/confirmed', 'Incomes\AccInvoices@confirmed');
            Route::post('invoices/{id}/reopened', 'Incomes\AccInvoices@reopened');
            Route::post('invoices/{id}/syncronized', 'Incomes\AccInvoices@syncronized');

            Route::apiResource('customers', 'Incomes\Customers');
            Route::apiResource('forecasts', 'Incomes\Forecasts');
            Route::apiResource('request-orders', 'Incomes\RequestOrders');
            Route::apiResource('invoices', 'Incomes\AccInvoices');
            Route::apiResource('pre-deliveries', 'Incomes\PreDeliveries');
            Route::apiResource('delivery-orders', 'Incomes\DeliveryOrders');
            Route::apiResource('delivery-internals', 'Incomes\DeliveryInternals');
            Route::apiResource('delivery-tasks', 'Incomes\DeliveryTasks');
            Route::apiResource('delivery-loads', 'Incomes\DeliveryLoads');
            Route::apiResource('delivery-verifies', 'Incomes\DeliveryVerifies');
            Route::apiResource('delivery-checkouts', 'Incomes\DeliveryCheckouts');
        });

        Route::prefix('warehouses')->name('warehouses.')->group(function () {
            Route::get('incoming-goods/items', 'Warehouses\IncomingGoods@items');

            Route::apiResource('transports', 'Warehouses\Transports');
            Route::apiResource('incoming-goods', 'Warehouses\IncomingGoods');
            Route::apiResource('opnames', 'Warehouses\Opnames');
            Route::apiResource('opname-stocks', 'Warehouses\OpnameStocks');
            Route::apiResource('opname-vouchers', 'Warehouses\OpnameVouchers');
            Route::apiResource('outgoing-goods', 'Warehouses\OutgoingGoods');
            Route::apiResource('outgoing-good-verifications', 'Warehouses\OutgoingGoodVerifications');
            Route::apiResource('deportation-goods', 'Warehouses\DeportationGoods');
        });

        Route::prefix('factories')->name('factories.')->group(function () {
            Route::get('work-orders/items', 'Factories\WorkOrders@items');
            Route::get('work-orders/lines', 'Factories\WorkOrders@lines');

            Route::apiResource('work-productions', 'Factories\WorkProductions');
            Route::apiResource('work-orders', 'Factories\WorkOrders');
            Route::apiResource('packings', 'Factories\Packings');
        });

        Route::prefix('transports')->name('transports.')->group(function () {
            Route::apiResource('schedule-boards', 'Transports\ScheduleBoards');
            Route::apiResource('trip-boards', 'Transports\Tripboards');
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
            Route::apiResource('reasons', 'References\Reasons');
            Route::apiResource('type-items', 'References\TypeItems');
            Route::apiResource('category-items', 'References\CategoryItems');
            Route::apiResource('specifications', 'References\Specifications');
        });

    });
});

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    $api->GET('/', function() {
        return response()->json([
            'app' => env('APP_NAME'),
            'prefix' => env('API_PREFIX'),
            'version' => env('API_VERSION'),
        ]);
    });
    $api->group(['namespace' => 'App\Api\Controllers'], function ($api) {
        ## Guest Access Route
        $api->group(['middleware' => 'api'], function ($api) {
            $api->group(['prefix' => 'auth'], function ($api) {
                $api->post('/login', 'Auth\Login@store');
                // $api->post("register", 'Auth\RegisterController@register');
                // $api->get("register/{token}", 'Auth\RegisterController@registerActivate');
                // $api->post("password/email", 'Auth\PasswordResetController@createToken');
                // $api->get("password/reset/{token}", 'Auth\PasswordResetController@findToken');
                // $api->post("password/reset", 'Auth\PasswordResetController@reset');
            });
        });

        $api->resource('opname-vouchers', 'Warehouses\OpnameVouchers');

        ## User Access Route
        $api->group(['middleware' => 'auth:api'], function ($api) {
            $api->get('profile', 'Auth\Profile@show');
            ## Auth Routes
            $api->group(['prefix' => 'auth'], function ($api) {
                $api->get('logout', 'Auth\Login@logout');
            });
            ## Common Routes
            $api->group(['prefix' => 'common'], function ($api) {
                $api->resource('items', 'Common\Items');
            });
            ## Warehouse Routes
            $api->group(['prefix' => 'warehouses'], function ($api) {
                $api->resource('opname-vouchers', 'Warehouses\OpnameVouchers');
            });
        });
    });
});

