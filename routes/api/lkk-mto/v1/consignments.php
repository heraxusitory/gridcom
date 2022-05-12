<?php

use App\Http\Controllers\API\MTO\v1\ConsignmentController;

Route::group(['prefix' => 'consignments'], function () {
    Route::post('sync', [ConsignmentController::class, 'sync']);
    Route::post('synchronize', [ConsignmentController::class, 'synchronize']);
    Route::post('remove_from_stack', [ConsignmentController::class, 'removeFromStack']);

});
