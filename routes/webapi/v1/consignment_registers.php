<?php


use App\Http\Controllers\WebAPI\v1\ConsignmentRegisters\ConsignmentRegisterController;
use App\Models\ConsignmentRegisters\ConsignmentRegister;

Route::group(['prefix' => 'consignment-registers', 'middleware' => 'role:provider,contractor'], function () {
    Route::get('', [ConsignmentRegisterController::class, 'index'])->can('view,' . ConsignmentRegister::class);
    Route::post('create', [ConsignmentRegisterController::class, 'create'])->can('create,' . ConsignmentRegister::class);
    Route::get('search-orders', [ConsignmentRegisterController::class, 'searchOrders'])->can('view,' . ConsignmentRegister::class);
    Route::get('search-consignments', [ConsignmentRegisterController::class, 'searchConsignments'])->can('view,' . ConsignmentRegister::class);
    Route::group(['prefix' => '{consignment_id}'], function () {
        Route::get('', [ConsignmentRegisterController::class, 'getConsignmentRegister'])->can('view,' . ConsignmentRegister::class);
        Route::put('', [ConsignmentRegisterController::class, 'update'])->can('update,' . ConsignmentRegister::class);
        Route::delete('', [ConsignmentRegisterController::class, 'delete'])->can('delete,' . ConsignmentRegister::class);


        //роуты подрядчика
        Route::post('approve', [ConsignmentRegisterController::class, 'approve'])->can('update,' . ConsignmentRegister::class)->middleware('role:contractor');
        Route::post('reject', [ConsignmentRegisterController::class, 'reject'])->can('update,' . ConsignmentRegister::class)->middleware('role:contractor');


        Route::group(['prefix' => 'positions', [ConsignmentRegisterController::class, 'getPositions']]);
    });
});
