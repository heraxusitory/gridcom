<?php

use App\Http\Controllers\Consignments\ConsignmentController;
use App\Http\Controllers\Integrations\AsMts\SyncOrderController;
use App\Http\Controllers\Integrations\AsMts\SyncReferenceController;
use App\Http\Controllers\Orders\OrderContractorController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\Orders\OrderProviderController;
use App\Http\Controllers\Orders\References\ReferenceController;
use App\Http\Controllers\PaymentRegisters\PaymentRegisterController;
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

Route::group(['prefix' => 'references'], function () {
    Route::get('organizations', [ReferenceController::class, 'getOrganizations']);
    Route::get('work_agreements', [ReferenceController::class, 'getWorkAgreements']);
    Route::get('provider_contracts', [ReferenceController::class, 'getProviderContracts']);
    Route::get('objects', [ReferenceController::class, 'getObjects']);
    Route::get('contr_agents', [ReferenceController::class, 'getContrAgents']);
});

Route::group(['prefix' => 'orders'], function () {
    Route::get('', [OrderController::class, 'index']);
    Route::post('create', [OrderContractorController::class, 'create']);

    Route::group(['prefix' => '{order_id}'], function () {
        Route::get('', [OrderController::class, 'getOrder']);
        Route::put('', [OrderContractorController::class, 'update']);
        Route::delete('', [OrderContractorController::class, 'delete']);

        Route::group(['prefix' => 'positions'], function () {
           Route::group(['prefix' => '{order_position}'], function () {
               Route::patch('', [OrderProviderController::class, 'changePosition']);
           });
        });

        //роуты поставщика
        Route::post('approve', [OrderProviderController::class, 'approve']);
        Route::post('reject', [OrderProviderController::class, 'reject']);
        Route::post('reject_positions', [OrderProviderController::class, 'rejectPositions']);
    });
});

Route::group(['prefix' => 'consignments'], function () {
    Route::get('', [ConsignmentController::class, 'index']);
    Route::get('{consignment_id}', [ConsignmentController::class, 'getConsignment']);
    Route::post('create', [ConsignmentController::class, 'create']);
    Route::get('search-orders', [ConsignmentController::class, 'searchOrders']);
});

Route::group(['prefix' => 'payment-registers'], function () {
    Route::get('', [PaymentRegisterController::class, 'index']);
    Route::post('create', [PaymentRegisterController::class, 'create']);
    Route::group(['prefix' => '{payment_register_id}'], function () {
        Route::get('', [PaymentRegisterController::class, 'getPaymentRegister']);
        Route::put('', [PaymentRegisterController::class, 'update']);
        Route::delete('', [PaymentRegisterController::class, 'delete']);
    });
    Route::get('search-provider-contracts', [PaymentRegisterController::class, 'searchProviderContracts']);
    Route::get('search-orders', [PaymentRegisterController::class, 'searchOrders']);
});

Route::group(['prefix' => 'integrations/as-mts/'], function () {
    Route::group(['prefix' => 'preferences'], function () {
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
