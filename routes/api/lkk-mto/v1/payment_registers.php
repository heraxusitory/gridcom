<?php

use App\Http\Controllers\API\MTO\v1\PaymentRegisterController;

Route::group(['prefix' => 'payment_registers'], function () {
    Route::post('sync', [PaymentRegisterController::class, 'sync']);
    Route::post('synchronize', [PaymentRegisterController::class, 'synchronize']);
    Route::post('put_in_queue', [PaymentRegisterController::class, 'putInQueue']);

});
