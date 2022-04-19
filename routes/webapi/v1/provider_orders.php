<?php

use App\Http\Controllers\WebAPI\v1\ProviderOrders\ProviderOrderController;
use App\Models\ProviderOrders\ProviderOrder;

Route::group(['prefix' => 'provider-orders', 'middleware' => 'role:provider'], function () {
    Route::get('', [ProviderOrderController::class, 'index'])->can('view,' . ProviderOrder::class);
    Route::group(['prefix' => '{provider_order_id}'], function () {
        Route::get('', [ProviderOrderController::class, 'getOrder'])->can('view,' . ProviderOrder::class);
        Route::group(['prefix' => 'requirement-corrections'], function () {
            Route::group(['prefix' => '{requirement_correction_id}'], function () {
                Route::post('approve', [ProviderOrderController::class, 'approve'])->can('update,' . ProviderOrder::class);
                Route::post('reject', [ProviderOrderController::class, 'reject'])->can('update,' . ProviderOrder::class);
                Route::post('reject-positions', [ProviderOrderController::class, 'rejectPositions'])->can('update,' . ProviderOrder::class);
            });
        });
    });
});