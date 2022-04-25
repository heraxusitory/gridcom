<?php

use App\Http\Controllers\API\MTO\v1\ProviderOrders\OrderCorrectionController;

Route::group(['prefix' => 'order_corrections'], function () {
    Route::post('sync', [OrderCorrectionController::class, 'sync']);
});
