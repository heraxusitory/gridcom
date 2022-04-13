<?php


use App\Http\Controllers\API\MTO\v1\OrderController;

Route::group(['prefix' => 'orders'], function () {
    Route::post('sync', [OrderController::class, 'sync']);
});
