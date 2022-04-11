<?php

use App\Http\Controllers\WebAPI\v1\Consignments\ConsignmentController;
use App\Models\Consignments\Consignment;

Route::group(['prefix' => 'consignments', 'middleware' => 'role:provider,contractor'], function () {
    Route::get('', [ConsignmentController::class, 'index'])->can('view,' . Consignment::class);
    Route::post('create', [ConsignmentController::class, 'create'])->can('create,' . Consignment::class);
    Route::get('search-orders', [ConsignmentController::class, 'searchOrders'])->can('view,' . Consignment::class);
    Route::group(['prefix' => '{consignment_id}'], function () {
        Route::get('', [ConsignmentController::class, 'getConsignment'])->can('view,' . Consignment::class);
        Route::put('', [ConsignmentController::class, 'update'])->can('update,' . Consignment::class);
        Route::delete('', [ConsignmentController::class, 'delete'])->can('delete,' . Consignment::class);
    });
});
