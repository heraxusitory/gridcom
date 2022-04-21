<?php

use App\Http\Controllers\API\MTO\v1\PaymentRegisterController;

Route::group(['prefix' => 'payment_registers'], function () {
    Route::post('sync', [PaymentRegisterController::class, 'sync']);
});