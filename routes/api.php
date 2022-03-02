<?php

use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\Orders\References\ReferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'orders'], function () {
    Route::get('', [OrderController::class, 'index']);
    Route::post('create', [OrderController::class, 'create']);
    Route::post('{order_id}', [OrderController::class, 'getOrder']);

    Route::group(['prefix' => 'references'], function () {
        Route::get('organizations', [ReferenceController::class, 'getOrganizations']);
        Route::get('work_agreements', [ReferenceController::class, 'getWorkAgreements']);
        Route::get('provider_contracts', [ReferenceController::class, 'getProviderContracts']);
        Route::get('objects', [ReferenceController::class, 'getObjects']);
        Route::get('contr_agents', [ReferenceController::class, 'getContrAgents']);
    });
});
