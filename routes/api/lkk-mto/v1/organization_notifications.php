<?php

use App\Http\Controllers\API\MTO\v1\Notifications\OrganizationNotificationController;

Route::group(['prefix' => 'organization_notifications'], function () {
    Route::post('synchronize', [OrganizationNotificationController::class, 'synchronize']);
    Route::post('put_in_queue', [OrganizationNotificationController::class, 'putInQueue']);

});
