<?php

use App\Http\Controllers\WebAPI\v1\References\ReferenceController;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;

Route::group(['prefix' => 'references', 'middleware' => 'role:provider,contractor'], function () {
    Route::get('organizations', [ReferenceController::class, 'getOrganizations'])->can('view,' . Organization::class);
    Route::get('work_agreements', [ReferenceController::class, 'getWorkAgreements'])->can('view,' . WorkAgreementDocument::class);
    Route::get('provider_contracts', [ReferenceController::class, 'getProviderContracts'])->can('view,' . ProviderContractDocument::class);
    Route::get('objects', [ReferenceController::class, 'getObjects'])->can('view,' . CustomerObject::class);
    Route::get('contr_agents', [ReferenceController::class, 'getContrAgents'])->can('view,' . ContrAgent::class);
});
