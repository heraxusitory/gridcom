<?php


use App\Http\Controllers\API\MTO\v1\ProviderOrderController;

Route::group(['prefix' => 'provider_orders'], function () {
    Route::post('sync', [ProviderOrderController::class, 'sync']);
});
