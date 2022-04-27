<?php

use App\Http\Controllers\API\MTO\v1\ProviderOrders\RequirementCorrectionController;

Route::group(['prefix' => 'requirement_corrections'], function () {
    Route::post('sync', [RequirementCorrectionController::class, 'sync']);
    Route::post('synchronize', [RequirementCorrectionController::class, 'synchronize']);
});
