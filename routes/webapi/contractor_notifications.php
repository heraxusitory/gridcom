<?php

use App\Http\Controllers\WebAPI\v1\Notifications\ContractorNotificationController;
use App\Models\Notifications\ContractorNotification;

Route::group(['prefix' => 'contractor-notifications', 'middleware' => 'role:provider,contractor'], function () {
    Route::get('', [ContractorNotificationController::class, 'index'])->can('view,' . ContractorNotification::class);
    Route::get('search-orders', [ContractorNotificationController::class, 'searchOrders'])->can('view,' . ContractorNotification::class);
    Route::get('search-provider-contracts', [ContractorNotificationController::class, 'searchProviderContracts'])->can('view,' . ContractorNotification::class);
    Route::post('', [ContractorNotificationController::class, 'create'])->can('create,' . ContractorNotification::class);
    Route::group(['prefix' => '{notification_id}'], function () {
        Route::get('', [ContractorNotificationController::class, 'getNotification'])->can('view,' . ContractorNotification::class);
        Route::put('', [ContractorNotificationController::class, 'update'])->can('update,' . ContractorNotification::class);
        Route::delete('', [ContractorNotificationController::class, 'delete'])->can('delete,' . ContractorNotification::class);
    });
});
