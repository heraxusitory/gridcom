<?php


use App\Http\Controllers\WebAPI\v1\RequestAdditions\RequestAdditionNomenclatureController;
use App\Http\Controllers\WebAPI\v1\RequestAdditions\RequestAdditionObjectController;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Models\RequestAdditions\RequestAdditionObject;


Route::group(['prefix' => 'request-addition', 'middleware' => 'role:provider,contractor'], function () {
    Route::group(['prefix' => 'nomenclature'], function () {
        Route::get('', [RequestAdditionNomenclatureController::class, 'index'])->can('view,' . RequestAdditionNomenclature::class);
        Route::post('', [RequestAdditionNomenclatureController::class, 'create'])->can('create,' . RequestAdditionNomenclature::class);
        Route::get('organizations', [RequestAdditionNomenclatureController::class, 'getOrganizations'])->can('view,' . RequestAdditionNomenclature::class);
        Route::group(['prefix' => '{nomenclature_id}'], function () {
            Route::get('', [RequestAdditionNomenclatureController::class, 'get'])->can('view,' . RequestAdditionNomenclature::class);
            Route::post('', [RequestAdditionNomenclatureController::class, 'update'])->can('update,' . RequestAdditionNomenclature::class);
            Route::delete('', [RequestAdditionNomenclatureController::class, 'delete'])->can('delete,' . RequestAdditionNomenclature::class);
        });
    });
    Route::group(['prefix' => 'objects'], function () {
        Route::get('', [RequestAdditionObjectController::class, 'index'])->can('view,' . RequestAdditionObject::class);
        Route::post('', [RequestAdditionObjectController::class, 'create'])->can('create,' . RequestAdditionObject::class);
        Route::get('organizations', [RequestAdditionObjectController::class, 'getOrganizations'])->can('view,' . RequestAdditionObject::class);
        Route::group(['prefix' => '{nomenclature_id}'], function () {
            Route::get('', [RequestAdditionObjectController::class, 'get'])->can('view,' . RequestAdditionObject::class);
            Route::post('', [RequestAdditionObjectController::class, 'update'])->can('update,' . RequestAdditionObject::class);
            Route::delete('', [RequestAdditionObjectController::class, 'delete'])->can('delete,' . RequestAdditionObject::class);
        });
    });
});
