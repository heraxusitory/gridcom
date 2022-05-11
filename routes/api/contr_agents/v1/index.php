<?php

use App\Http\Controllers\API\ContrAgents\Orders\OrderController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth.basic:api']], function () {

    Route::group(['prefix' => 'orders'], function () {
        Route::post('sync', [OrderController::class, 'sync'])->middleware('contr_agent_role:contractor');
        Route::post('synchronize', [OrderController::class, 'synchronize'])->middleware('contr_agent_role:contractor,provider');
        Route::post('remove_from_stack', [OrderController::class, 'removeFromStack'])->middleware('contr_agent_role:contractor,provider');;
    });
//    require 'references.php';
//    require 'consignment_registers.php';
//    require 'consignments.php';
//    require 'payment_registers.php';
//    require 'provider_orders.php';
//    require 'requirement_corrections.php';
//    require 'order_corrections.php';
//    require 'organization_notifications.php';
//    require 'request_additions.php';
//    require 'price_negotiations.php';
});
