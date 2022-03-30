<?php

use App\Http\Controllers\ConsignmentRegisters\ConsignmentRegisterController;
use App\Http\Controllers\Consignments\ConsignmentController;
use App\Http\Controllers\Integrations\AsMts\SyncOrderController;
use App\Http\Controllers\Integrations\AsMts\SyncReferenceController;
use App\Http\Controllers\Notifications\ContractorNotificationController;
use App\Http\Controllers\Notifications\ProviderNotificationController;
use App\Http\Controllers\Orders\OrderContractorController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\Orders\OrderProviderController;
use App\Http\Controllers\Orders\References\ReferenceController;
use App\Http\Controllers\PaymentRegisters\PaymentRegisterController;
use App\Http\Controllers\ProviderOrders\ProviderOrderController;
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
        Route::get('report', [OrderController::class, 'getReport']);
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

Route::group(['prefix' => 'provider-orders'], function () {
    Route::get('', [ProviderOrderController::class, 'index']);
    Route::group(['prefix' => '{provider_order_id}'], function () {
        Route::get('', [ProviderOrderController::class, 'getOrder']);
        Route::group(['prefix' => 'requirement-corrections'], function () {
            Route::group(['prefix' => '{requirement_correction_id}'], function () {
                Route::post('approve', [ProviderOrderController::class, 'approve']);
                Route::post('reject', [ProviderOrderController::class, 'reject']);
                Route::post('reject-positions', [ProviderOrderController::class, 'rejectPositions']);
            });
        });
    });
});

Route::group(['prefix' => 'consignments'], function () {
    Route::get('', [ConsignmentController::class, 'index']);
    Route::post('create', [ConsignmentController::class, 'create']);
    Route::get('search-orders', [ConsignmentController::class, 'searchOrders']);
    Route::group(['prefix' => '{consignment_id}'], function () {
        Route::get('', [ConsignmentController::class, 'getConsignment']);
        Route::put('', [ConsignmentController::class, 'update']);
        Route::delete('', [ConsignmentController::class, 'delete']);
    });
});

Route::group(['prefix' => 'consignment-registers'], function () {
    Route::get('', [ConsignmentRegisterController::class, 'index']);
    Route::post('create', [ConsignmentRegisterController::class, 'create']);
    Route::get('search-orders', [ConsignmentRegisterController::class, 'searchOrders']);
    Route::get('search-consignments', [ConsignmentRegisterController::class, 'searchConsignments']);
    Route::group(['prefix' => '{consignment_id}'], function () {
        Route::get('', [ConsignmentRegisterController::class, 'getConsignmentRegister']);
        Route::put('', [ConsignmentRegisterController::class, 'update']);
        Route::delete('', [ConsignmentRegisterController::class, 'delete']);
    });
});

Route::group(['prefix' => 'payment-registers'], function () {
    Route::get('', [PaymentRegisterController::class, 'index']);
    Route::post('create', [PaymentRegisterController::class, 'create']);
    Route::get('search-provider-contracts', [PaymentRegisterController::class, 'searchProviderContracts']);
    Route::get('search-orders', [PaymentRegisterController::class, 'searchOrders']);
    Route::group(['prefix' => '{payment_register_id}'], function () {
        Route::get('', [PaymentRegisterController::class, 'getPaymentRegister']);
        Route::put('', [PaymentRegisterController::class, 'update']);
        Route::delete('', [PaymentRegisterController::class, 'delete']);
    });
});

Route::group(['prefix' => 'contractor-notifications'], function () {
    Route::get('', [ContractorNotificationController::class, 'index']);
    Route::get('search-orders', [ContractorNotificationController::class, 'searchOrders']);
    Route::get('search-provider-contracts', [ContractorNotificationController::class, 'searchProviderContracts']);
    Route::post('', [ContractorNotificationController::class, 'create']);
    Route::group(['prefix' => '{notification_id}'], function () {
        Route::get('', [ContractorNotificationController::class, 'getNotification']);
        Route::put('', [ContractorNotificationController::class, 'update']);
        Route::delete('', [ContractorNotificationController::class, 'delete']);
    });
});
//Route::group(['prefix' => 'organization-notifications'], function () {
//    Route::get('', [ProviderNotificationController::class, 'index']);
//    Route::post('', [ProviderNotificationController::class, 'create']);
//    Route::group(['prefix' => '{notification_id}'], function () {
//        Route::get('', [ProviderNotificationController::class, 'getNotification']);
//        Route::put('', [ProviderNotificationController::class, 'update']);
//        Route::delete('', [ProviderNotificationController::class, 'delete']);
//    });
//});

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
