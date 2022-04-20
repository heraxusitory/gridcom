<?php


namespace App\Http\Controllers\WebAPI\v1\References;


use App\Http\Controllers\Controller;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    public function getOrganizations()
    {
        $organizations = Organization::query()->orderByDesc('created_at')->get();
        return response()->json(['data' => $organizations]);
    }

    public function getWorkAgreements(Request $request)
    {
        $work_agreements_query = WorkAgreementDocument::query();
//        if (isset($request->name))
//            $work_agreements_query->where('name', 'ILIKE', "%{$request->name}%");
        $work_agreements = $work_agreements_query->orderByDesc('created_at')->get();
        return response()->json(['data' => $work_agreements]);
    }

    public function getProviderContracts(Request $request)
    {
        $provider_contracts_query = ProviderContractDocument::query();
//        if (isset($request->name))
//            $provider_contracts_query->where('name', 'ILIKE', "%{$request->name}%");
        $provider_contracts = $provider_contracts_query->orderByDesc('created_at')->get();
        return response()->json(['data' => $provider_contracts]);
    }

    public function getObjects(Request $request)
    {
        $objects_query = CustomerObject::query()->with('subObjects');
//        if (isset($request->name))
//            $objects_query->where('name', 'ILIKE', "%{$request->name}%");

        $objects = $objects_query->orderByDesc('created_at')->get();
        return response()->json(['data' => $objects]);
    }

    public function getContrAgents(Request $request)
    {
        $contr_agents_query = ContrAgent::query();
//        if (isset($request->name))
//            $contr_agents_query->where('name', 'ILIKE', "%{$request->name}%");

        $contr_agents = $contr_agents_query->orderByDesc('created_at')->get();
        return response()->json(['data' => $contr_agents]);
    }

    public function getNomenclature(Request $request)
    {
        $nomenclature_query = Nomenclature::query()->with('units');
        if (isset($request->name))
            $nomenclature_query->where('name', 'ILIKE', "%{$request->name}%");
//        $per_page = isset($request->per_page) && (int)$request->per_page ? abs($request->per_page) : config('pagination.per_page');
        $nomenclature = $nomenclature_query->orderByDesc('created_at')->paginate($request->per_page);
        return response()->json(['data' => $nomenclature]);
    }
}
