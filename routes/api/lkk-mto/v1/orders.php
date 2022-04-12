<?php


use App\Http\Controllers\API\MTO\SyncOrderController;

Route::group(['prefix' => 'orders'], function () {
    Route::post('sync', [SyncOrderController::class, 'pull']);
});
