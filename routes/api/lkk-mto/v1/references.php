<?php


use App\Http\Controllers\API\MTO\SyncReferenceController;

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
