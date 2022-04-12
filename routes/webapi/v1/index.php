<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


//Route::group([
//    'middleware' => 'auth:api'
//], function () {
//    Route::get('/', function () {
//        return 1;
//    });
//});
//Route::group([
//    'middleware' => ['auth:webapi', 'role:contractor'],
////    'prefix' => 'auth'
//], function () {
//    Route::get('login', [AuthController::class, 'login']);
//    Route::post('logout', [AuthController::class, 'logout']);
//    Route::post('refresh', [AuthController::class, 'refresh']);
//    Route::post('me', [AuthController::class, 'me']);
//
//});

Route::group(['middleware' => ['auth:webapi']], function () {

    Route::get('me', [AuthController::class, 'me']);
    require 'references.php';
    require 'orders.php';
    require 'provider_orders.php';
    require 'consignments.php';
    require 'consignment_registers.php';
    require 'payment_registers.php';
    require 'contractor_notifications.php';
    require 'organization_notifications.php';
    require 'price_negotiations.php';
    require 'request_additions.php';
});
