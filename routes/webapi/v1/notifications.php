<?php

use App\Http\Controllers\WebAPI\v1\NotificationController;

Route::group(['prefix' => 'notifications', 'middleware' => 'role:provider,contractor'], function () {
    Route::get('', [NotificationController::class, 'index']);
    Route::group(['prefix' => '{notification_id}'], function () {
        Route::delete('', [NotificationController::class, 'destroy']);
    });
});
