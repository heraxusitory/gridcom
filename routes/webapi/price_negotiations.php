<?php

use App\Http\Controllers\WebAPI\v1\PriceNegotiations\PriceNegotiationController;
use App\Models\PriceNegotiations\PriceNegotiation;

Route::group(['prefix' => 'price-negotiations', 'middleware' => 'role:provider,contractor'], function () {
    Route::get('', [PriceNegotiationController::class, 'index'])->can('view,' . PriceNegotiation::class);
    Route::post('', [PriceNegotiationController::class, 'create'])->can('create,' . PriceNegotiation::class);
    Route::get('search-orders', [PriceNegotiationController::class, 'searchOrdersWithNomenclature'])->can('view,' . PriceNegotiation::class);
    Route::group(['prefix' => '{price_negotiation_id}'], function () {
        Route::get('', [PriceNegotiationController::class, 'getPriceNegotiation'])->can('view,' . PriceNegotiation::class);
        Route::post('', [PriceNegotiationController::class, 'update'])->can('update,' . PriceNegotiation::class);
        Route::delete('', [PriceNegotiationController::class, 'delete'])->can('delete,' . PriceNegotiation::class);
    });
});
