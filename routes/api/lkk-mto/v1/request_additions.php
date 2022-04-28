<?php

use App\Http\Controllers\API\MTO\v1\RequestAdditions\RequestAdditionNomenclatureController;

Route::group(['prefix' => 'request_additions'], function () {
    Route::group(['prefix' => 'nomenclature'], function () {
        Route::post('synchronize', [RequestAdditionNomenclatureController::class, 'synchronize']);
        Route::post('put_in_queue', [RequestAdditionNomenclatureController::class, 'putInQueue']);

    });
    Route::group(['prefix' => 'objects'], function () {
//        Route::post('synchronize', [RequestAdditionObjectController::class, 'synchronize']);
    });
});
