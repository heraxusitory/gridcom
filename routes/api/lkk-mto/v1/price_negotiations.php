<?php

use App\Http\Controllers\API\MTO\v1\PriceNegotiationController;

Route::group(['prefix' => 'price_negotiations'], function () {
    Route::post('sync', [PriceNegotiationController::class, 'sync']);
    Route::post('synchronize', [PriceNegotiationController::class, 'synchronize']);
    Route::post('removeFromStack', [PriceNegotiationController::class, 'removeFromStack']);
});
