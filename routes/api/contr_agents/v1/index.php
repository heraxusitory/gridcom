<?php

use App\Http\Controllers\API\ContrAgents\ConsignmentController;
use App\Http\Controllers\API\ContrAgents\ConsignmentRegisterController;
use App\Http\Controllers\API\ContrAgents\Orders\OrderController;
use App\Http\Controllers\API\ContrAgents\OrganizationNotificationController;
use App\Http\Controllers\API\ContrAgents\PaymentRegisterController;
use App\Http\Controllers\API\ContrAgents\PriceNegotiationController;
use App\Http\Controllers\API\ContrAgents\ProviderOrderController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth.basic:api']], function () {

    Route::group(['prefix' => 'orders'], function () {
        Route::post('sync', [OrderController::class, 'sync'])->middleware('contr_agent_role:contractor');
        Route::post('synchronize', [OrderController::class, 'synchronize'])->middleware('contr_agent_role:contractor,provider');
        Route::post('remove_from_stack', [OrderController::class, 'removeFromStack'])->middleware('contr_agent_role:contractor,provider');;
    });
    Route::group(['prefix' => 'consignments', 'middleware' => 'contr_agent_role:contractor,provider'], function () {
        Route::post('sync', [ConsignmentController::class, 'sync']);
        Route::post('synchronize', [ConsignmentController::class, 'synchronize']);
        Route::post('remove_from_stack', [ConsignmentController::class, 'removeFromStack']);
    });
    Route::group(['prefix' => 'consignment_registers', 'middleware' => 'contr_agent_role:contractor,provider'], function () {
        Route::post('sync', [ConsignmentRegisterController::class, 'sync']);
        Route::post('synchronize', [ConsignmentRegisterController::class, 'synchronize']);
        Route::post('remove_from_stack', [ConsignmentRegisterController::class, 'removeFromStack']);
    });
    Route::group(['prefix' => 'payment_registers', 'middleware' => 'contr_agent_role:contractor,provider'], function () {
        Route::post('sync', [PaymentRegisterController::class, 'sync']);
        Route::post('synchronize', [PaymentRegisterController::class, 'synchronize']);
        Route::post('remove_from_stack', [PaymentRegisterController::class, 'removeFromStack']);
    });
    Route::group(['prefix' => 'organization_notifications', 'middleware' => 'contr_agent_role:provider'], function () {
        Route::post('sync', [OrganizationNotificationController::class, 'sync']);
        Route::post('synchronize', [OrganizationNotificationController::class, 'synchronize']);
        Route::post('remove_from_stack', [OrganizationNotificationController::class, 'removeFromStack']);
    });
    Route::group(['prefix' => 'provider_orders', 'middleware' => 'contr_agent_role:provider'], function () {
        Route::post('synchronize', [ProviderOrderController::class, 'synchronize']);
        Route::post('remove_from_stack', [ProviderOrderController::class, 'removeFromStack']);
    });
    Route::group(['prefix' => 'price_negotiations', 'middleware' => 'contr_agent_role:provider,contractor'], function () {
        Route::post('sync', [PriceNegotiationController::class, 'sync']);
        Route::post('synchronize', [PriceNegotiationController::class, 'synchronize']);
        Route::post('remove_from_stack', [PriceNegotiationController::class, 'removeFromStack']);
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
