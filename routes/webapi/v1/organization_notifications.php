<?php

use App\Http\Controllers\WebAPI\v1\Notifications\OrganizationNotificationController;
use App\Models\Notifications\OrganizationNotification;

Route::group(['prefix' => 'organization-notifications', 'middleware' => 'role:provider'], function () {
    Route::get('', [OrganizationNotificationController::class, 'index'])->can('view,' . OrganizationNotification::class);
    Route::post('', [OrganizationNotificationController::class, 'create'])->can('create,' . OrganizationNotification::class);
    Route::get('search-orders', [OrganizationNotificationController::class, 'searchOrders'])->can('view,' . OrganizationNotification::class);
    Route::get('search-contracts', [OrganizationNotificationController::class, 'searchContracts'])->can('view,' . OrganizationNotification::class);
    Route::group(['prefix' => '{notification_id}'], function () {
        Route::get('', [OrganizationNotificationController::class, 'getNotification'])->can('view,' . OrganizationNotification::class);
        Route::put('', [OrganizationNotificationController::class, 'update'])->can('update,' . OrganizationNotification::class);
        Route::delete('', [OrganizationNotificationController::class, 'delete'])->can('delete,' . OrganizationNotification::class);
    });
});
