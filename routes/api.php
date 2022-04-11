<?php

use App\Http\Controllers\Integrations\AsMts\SyncOrderController;
use App\Http\Controllers\Integrations\AsMts\SyncReferenceController;
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

Route::group(['prefix' => 'integrations/as-mts/', 'middleware' => 'auth.basic'], function () {
    Route::group(['prefix' => 'references'], function () {
        Route::post('organizations/sync', [SyncReferenceController::class, 'syncOrganizations']);
        Route::post('provider_contracts/sync', [SyncReferenceController::class, 'syncProviderContracts']);
        Route::post('work_agreements/sync', [SyncReferenceController::class, 'syncWorkAgreements']);
        Route::post('contr_agents/sync', [SyncReferenceController::class, 'syncContrAgents']);
        Route::post('contact_persons/sync', [SyncReferenceController::class, 'syncContactPersons']);
        Route::post('objects/sync', [SyncReferenceController::class, 'syncCustomerObjects']);
        Route::post('sub_objects/sync', [SyncReferenceController::class, 'syncCustomerSubObjects']);
        Route::post('nomenclature/sync', [SyncReferenceController::class, 'syncNomenclature']);
        Route::post('nomenclature_units/sync', [SyncReferenceController::class, 'syncNomenclatureUnits']);
    });
    Route::group(['prefix' => 'orders'], function () {
        Route::post('sync', [SyncOrderController::class, 'pull']);
    });
});
