<?php

use App\Http\Controllers\WebAPI\v1\PaymentRegisters\PaymentRegisterController;
use App\Models\PaymentRegisters\PaymentRegister;

Route::group(['prefix' => 'payment-registers'], function () {
    Route::get('', [PaymentRegisterController::class, 'index'])->can('view,' . PaymentRegister::class)->middleware('role:provider,contractor');
    Route::post('create', [PaymentRegisterController::class, 'create'])->can('create,' . PaymentRegister::class)->middleware('role:contractor');
    Route::get('search-provider-contracts', [PaymentRegisterController::class, 'searchProviderContracts'])->can('view,' . PaymentRegister::class)->middleware('role:provider,contractor');
    Route::get('search-orders', [PaymentRegisterController::class, 'searchOrders'])->can('view,' . PaymentRegister::class)->middleware('role:provider,contractor');
    Route::group(['prefix' => '{payment_register_id}'], function () {
        Route::get('', [PaymentRegisterController::class, 'getPaymentRegister'])->can('view,' . PaymentRegister::class)->middleware('role:provider,contractor');
        Route::put('', [PaymentRegisterController::class, 'update'])->can('update,' . PaymentRegister::class)->middleware('role:provider,contractor');
        Route::delete('', [PaymentRegisterController::class, 'delete'])->can('delete,' . PaymentRegister::class)->middleware('role:contractor');

        //роуты поставшика
        Route::post('approve', [PaymentRegisterController::class, 'approve'])->can('update,' . PaymentRegister::class)->middleware('role:provider');
        Route::post('reject', [PaymentRegisterController::class, 'reject'])->can('update,' . PaymentRegister::class)->middleware('role:provider');


        Route::group(['prefix' => 'positions'], function () {
            Route::get('', [PaymentRegisterController::class, 'getPositions']);
        });
    });
});
