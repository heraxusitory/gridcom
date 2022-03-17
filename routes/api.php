<?php

use App\Http\Controllers\ConsignmentNotes\ConsignmentNoteController;
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
    Route::get('{order_id}', [OrderController::class, 'getOrder']);
    Route::put('{order_id}', [OrderController::class, 'update']);
    Route::delete('{order_id}', [OrderController::class, 'delete']);
    Route::post('create', [OrderController::class, 'create']);

    Route::group(['prefix' => 'references'], function () {
        Route::get('organizations', [ReferenceController::class, 'getOrganizations']);
        Route::get('work_agreements', [ReferenceController::class, 'getWorkAgreements']);
        Route::get('provider_contracts', [ReferenceController::class, 'getProviderContracts']);
        Route::get('objects', [ReferenceController::class, 'getObjects']);
        Route::get('contr_agents', [ReferenceController::class, 'getContrAgents']);
    });
});

Route::group(['prefix' => 'consignments'], function () {
    Route::get('', [ConsignmentNoteController::class, 'index']);
    Route::post('create', [ConsignmentNoteController::class, 'create']);
    Route::post('create', [ConsignmentNoteController::class, 'update']);
});

Route::group(['prefix' => 'integrations/as-mts/synchronization'], function () {
    Route::group(['prefix' => 'preferences'], function () {
        Route::post('organizations', [\App\Http\Controllers\Integrations\AsMts\SyncReferenceController::class, 'syncOrganizations']);
    });
    Route::group(['prefix' => 'orders'], function () {
        Route::post('', [\App\Http\Controllers\Integrations\AsMts\SyncOrderController::class, 'sync']);
    });
});
