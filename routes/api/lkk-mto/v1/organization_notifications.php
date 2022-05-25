<?php

use App\Http\Controllers\API\MTO\v1\Notifications\OrganizationNotificationController;

Route::group(['prefix' => 'organization_notifications'], function () {
    Route::post('sync', [OrganizationNotificationController::class, 'sync']);
    Route::post('synchronize', [OrganizationNotificationController::class, 'synchronize']);
    Route::post('remove_from_stack', [OrganizationNotificationController::class, 'removeFromStack']);

});
