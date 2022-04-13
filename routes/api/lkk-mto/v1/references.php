<?php


use App\Http\Controllers\API\MTO\v1\ReferenceController;

Route::group(['prefix' => 'references'], function () {
    Route::post('organizations/sync', [ReferenceController::class, 'syncOrganizations']);
    Route::post('provider_contracts/sync', [ReferenceController::class, 'syncProviderContracts']);
    Route::post('work_agreements/sync', [ReferenceController::class, 'syncWorkAgreements']);
    Route::post('contr_agents/sync', [ReferenceController::class, 'syncContrAgents']);
    Route::post('contact_persons/sync', [ReferenceController::class, 'syncContactPersons']);
    Route::post('objects/sync', [ReferenceController::class, 'syncCustomerObjects']);
    Route::post('sub_objects/sync', [ReferenceController::class, 'syncCustomerSubObjects']);
    Route::post('nomenclature/sync', [ReferenceController::class, 'syncNomenclature']);
    Route::post('nomenclature_units/sync', [ReferenceController::class, 'syncNomenclatureUnits']);
});
