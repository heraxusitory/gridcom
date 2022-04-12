<?php


use App\Http\Controllers\API\MTO\v1\SyncOrderController;

Route::group(['prefix' => 'orders'], function () {
    Route::post('sync', [SyncOrderController::class, 'pull']);
});
