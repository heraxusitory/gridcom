<?php

use App\Http\Controllers\API\MTO\v1\PriceNegotiationController;

Route::group(['prefix' => 'price_negotiations'], function () {
    Route::post('synchronize', [PriceNegotiationController::class, 'synchronize']);
    Route::post('put_in_queue', [PriceNegotiationController::class, 'putInQueue']);
});
