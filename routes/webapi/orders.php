<?php

use App\Http\Controllers\WebAPI\v1\Orders\OrderContractorController;
use App\Http\Controllers\WebAPI\v1\Orders\OrderController;
use App\Http\Controllers\WebAPI\v1\Orders\OrderProviderController;
use App\Models\Orders\LKK\Order;

Route::group(['prefix' => 'orders'], function () {
    Route::get('', [OrderController::class, 'index'])->middleware('role:provider,contractor')->can('view,' . Order::class);
    Route::post('create', [OrderContractorController::class, 'create'])->middleware('role:contractor')->can('create,' . Order::class);

    Route::group(['prefix' => '{order_id}'], function () {
        Route::get('', [OrderController::class, 'getOrder'])->middleware('role:provider,contractor')->can('view,' . Order::class);
        Route::get('report', [OrderController::class, 'getReport'])->middleware('role:provider,contractor')->can('view,' . Order::class);
        Route::put('', [OrderContractorController::class, 'update'])->middleware('role:contractor')->can('update,' . Order::class);
        Route::delete('', [OrderContractorController::class, 'delete'])->middleware('role:contractor')->can('delete,' . Order::class);

        Route::group(['prefix' => 'positions'], function () {
            Route::group(['prefix' => '{order_position}'], function () {
                Route::patch('', [OrderProviderController::class, 'changePosition'])->middleware('role:provider')->can('update,' . Order::class);
            });
        });

        //роуты поставщика
        Route::group(['middleware' => 'role:provider'], function () {
            Route::post('approve', [OrderProviderController::class, 'approve'])->can('update,' . Order::class);
            Route::post('reject', [OrderProviderController::class, 'reject'])->can('update,' . Order::class);
            Route::post('reject_positions', [OrderProviderController::class, 'rejectPositions'])->can('update,' . Order::class);
        });
    });
});
