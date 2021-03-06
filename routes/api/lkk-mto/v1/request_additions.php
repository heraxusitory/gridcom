<?php

use App\Http\Controllers\API\MTO\v1\RequestAdditions\RequestAdditionNomenclatureController;
use App\Http\Controllers\API\MTO\v1\RequestAdditions\RequestAdditionObjectController;

Route::group(['prefix' => 'request_additions'], function () {
    Route::group(['prefix' => 'nomenclature'], function () {
        Route::post('sync', [RequestAdditionNomenclatureController::class, 'sync']);
        Route::post('synchronize', [RequestAdditionNomenclatureController::class, 'synchronize']);
        Route::post('remove_from_stack', [RequestAdditionNomenclatureController::class, 'removeFromStack']);
        Route::group(['prefix' => '{ra_nomenclature_id}'], function () {
            Route::get('download_file', [RequestAdditionNomenclatureController::class, 'downloadFile']);
        });
    });
    Route::group(['prefix' => 'objects'], function () {
        Route::post('sync', [RequestAdditionObjectController::class, 'sync']);
        Route::post('synchronize', [RequestAdditionObjectController::class, 'synchronize']);
        Route::post('remove_from_stack', [RequestAdditionObjectController::class, 'removeFromStack']);
        Route::group(['prefix' => '{ra_object_id}'], function () {
            Route::get('download_file', [RequestAdditionObjectController::class, 'downloadFile']);
        });
    });
});
