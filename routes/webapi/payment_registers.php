<?php

use App\Http\Controllers\WebAPI\v1\PaymentRegisters\PaymentRegisterController;
use App\Models\PaymentRegisters\PaymentRegister;

Route::group(['prefix' => 'payment-registers', 'middleware' => 'role:provider,contractor'], function () {
    Route::get('', [PaymentRegisterController::class, 'index'])->can('view,' . PaymentRegister::class);
    Route::post('create', [PaymentRegisterController::class, 'create'])->can('create,' . PaymentRegister::class);
    Route::get('search-provider-contracts', [PaymentRegisterController::class, 'searchProviderContracts'])->can('view,' . PaymentRegister::class);
    Route::get('search-orders', [PaymentRegisterController::class, 'searchOrders'])->can('view,' . PaymentRegister::class);
    Route::group(['prefix' => '{payment_register_id}'], function () {
        Route::get('', [PaymentRegisterController::class, 'getPaymentRegister'])->can('view,' . PaymentRegister::class);
        Route::put('', [PaymentRegisterController::class, 'update'])->can('update,' . PaymentRegister::class);
        Route::delete('', [PaymentRegisterController::class, 'delete'])->can('delete,' . PaymentRegister::class);
    });
});
