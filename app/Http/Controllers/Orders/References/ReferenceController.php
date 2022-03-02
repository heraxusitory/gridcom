<?php


namespace App\Http\Controllers\Orders\References;


use App\Http\Controllers\Controller;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;

class ReferenceController extends Controller
{
    public function getOrganizations()
    {
        $organizations = Organization::query()->pluck('name', 'id');
        return response()->json(['data' => $organizations]);
    }

    public function getWorkAgreements()
    {
        $work_agreements = WorkAgreementDocument::all();
        return response()->json(['data' => $work_agreements]);
    }

    public function getProviderContracts()
    {
        $provider_contracts = ProviderContractDocument::all();
        return response()->json(['data' => $provider_contracts]);
    }

    public function getObjects()
    {
        $objects = CustomerObject::with('subObjects')->get();
        return response()->json(['data' => $objects]);
    }

    public function getContrAgents()
    {
        $contr_agents = ContrAgent::with(['contacts'])->get();
        return response()->json(['data' => $contr_agents]);
    }
}
