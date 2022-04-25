<?php


use App\Http\Controllers\API\MTO\v1\ProviderOrders\ProviderOrderController;

Route::group(['prefix' => 'provider_orders'], function () {
    Route::post('sync', [ProviderOrderController::class, 'sync']);
});
