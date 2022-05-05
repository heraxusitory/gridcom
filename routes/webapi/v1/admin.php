<?php

use App\Http\Controllers\WebAPI\v1\IntegrationController;

Route::group(['prefix' => 'admin'], function () {
    Route::group(['prefix' => 'integrations'], function () {
        Route::post('', [IntegrationController::class, 'create']);
        Route::get('', [IntegrationController::class, 'index']);
        Route::post('reset_password', [IntegrationController::class, 'resetPassword']);
        Route::group(['prefix' => '{integration_id}'], function () {
            Route::get('', [IntegrationController::class, 'getIntegration']);
            Route::delete('', [IntegrationController::class, 'delete']);
        });
    });
});
