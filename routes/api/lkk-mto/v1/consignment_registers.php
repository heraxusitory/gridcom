<?php


use App\Http\Controllers\API\MTO\v1\ConsignmentRegisterController;

Route::group(['prefix' => 'consignment_registers'], function () {
    Route::post('sync', [ConsignmentRegisterController::class, 'sync']);
});
