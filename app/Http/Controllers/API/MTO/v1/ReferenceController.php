<?php


namespace App\Http\Controllers\API\MTO\v1;


use App\Http\Controllers\Controller;
use App\Models\References\ContactPerson;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\CustomerSubObject;
use App\Models\References\Nomenclature;
use App\Models\References\NomenclatureUnit;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReferenceController extends Controller
{
    public function syncOrganizations(Request $request)
    {
        Validator::make($request->all(),
            [
                'organizations' => 'required|array',
                'organizations.*.id' => 'required|uuid',
                'organizations.*.name' => 'required|string|max:255'
            ])->validate();
        $organizations = $request->organizations;

        try {
            foreach ($organizations as $organization) {
                Organization::query()->updateOrCreate([
                    'uuid' => $organization['id']
                ], [
                    'name' => $organization['name'],
                    'is_confirmed' => true,
                ]);
            }
            return response()->json();
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function syncContrAgents(Request $request)
    {
        Validator::make($request->all(), [
            'contr_agents' => 'required|array',
            'contr_agents.*.id' => 'required|uuid',
            'contr_agents.*.name' => 'required|string|max:255',
        ])->validate();
        $contr_agents = $request->contr_agents;

        try {
            foreach ($contr_agents as $contr_agent) {
                ContrAgent::query()->updateOrCreate([
                    'uuid' => $contr_agent['id']
                ], [
                    'name' => $contr_agent['name'],
                    'is_confirmed' => true,
                ]);
            }
            return response()->json();
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function syncContactPersons(Request $request)
    {
        Validator::make($request->all(), [
            'contact_persons' => 'required|array',
            'contact_persons.*.id' => 'required|uuid',
            'contact_persons.*.contr_agent_id' => 'required|uuid|exists:contr_agents,uuid',
            'contact_persons.*.full_name' => 'required|string|max:255',
            'contact_persons.*.email' => 'nullable|string|max:255',
            'contact_persons.*.phone' => 'required|string|max:255',
        ])->validate();
        $contact_persons = $request->contact_persons;

        try {
            foreach ($contact_persons as $contact_person) {
                $contr_agent = ContrAgent::query()->where('uuid', $contact_person['contr_agent_id'])->firstOrFail();
                ContactPerson::query()->updateOrCreate(['uuid' => $contact_person['id']],
                    [
                        'contr_agent_id' => $contr_agent->id,
                        'full_name' => $contact_person['full_name'],
                        'email' => $contact_person['email'],
                        'phone' => $contact_person['phone'],
                    ]);
            }
            return response()->json();
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function syncCustomerObjects(Request $request)
    {
        Validator::make($request->all(), [
            'customer_objects' => 'required|array',
            'customer_objects.*.id' => 'required|uuid',
            'customer_objects.*.name' => 'required|string|max:255',
        ])->validate();

        $customer_objects = $request->customer_objects;

        try {
            foreach ($customer_objects as $customer_object) {
                CustomerObject::query()->updateOrCreate([
                    'uuid' => $customer_object['id']
                ], [
                    'name' => $customer_object['name'],
                    'is_confirmed' => true,
                ]);
            }
            return response()->json();
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function syncCustomerSubObjects(Request $request)
    {
        Validator::make($request->all(), [
            'customer_sub_objects' => 'required|array',
            'customer_sub_objects.*.id' => 'required|uuid',
            'customer_sub_objects.*.name' => 'required|string|max:255',
            'customer_sub_objects.*.customer_object_id' => 'required|uuid',
        ])->validate();
        $sub_objects = $request->customer_sub_objects;

        try {
            foreach ($sub_objects as $sub_object) {
                $customer_object = CustomerObject::query()->firstOrCreate(['uuid' => $sub_object['customer_object_id']]);
                CustomerSubObject::query()->updateOrCreate([
                    'uuid' => $sub_object['id']
                ], [
                    'name' => $sub_object['name'],
                    'customer_object_id' => $customer_object->id,
                    'is_confirmed' => true,
                ]);
            }
            return response()->json();
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function syncNomenclature(Request $request)
    {
        Validator::make($request->all(), [
            'nomenclature' => 'required|array',
            'nomenclature.*.id' => 'required|uuid',
            'nomenclature.*.mnemocode' => 'required|string|max:255',
            'nomenclature.*.name' => 'required|string|max:255',
            'nomenclature.*.price' => 'required|numeric',
            'nomenclature.*.unit_id' => 'required|uuid',
        ])->validate();
        $nomenlcature = $request->nomenclature;

        try {
            foreach ($nomenlcature as $item) {
                DB::transaction(function () use ($item) {
                    $nomenclature_unit = NomenclatureUnit::query()->firstOrCreate(['uuid' => $item['unit_id']]);
                    $nomenclature = Nomenclature::query()->updateOrCreate([
                        'uuid' => $item['id']
                    ], [
                        'mnemocode' => $item['mnemocode'],
                        'name' => $item['name'],
                        'price' => $item['price'],
                        'is_confirmed' => true,
                    ]);
                    $nomenclature->units()->attach($nomenclature_unit->id);
                });
            }
            return response()->json();
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function syncNomenclatureUnits(Request $request)
    {
        Validator::make($request->all(), [
            'nomenclature_units' => 'required|array',
            'nomenclature_units.*.id' => 'required|uuid',
            'nomenclature_units.*.name' => 'required|string|max:255',
        ])->validate();

        $units = $request->nomenclature_units;
        try {
            foreach ($units as $unit) {
                NomenclatureUnit::query()->updateOrCreate([
                    'uuid' => $unit['id']
                ], [
                    'name' => $unit['name'],
                    'is_confirmed' => true,
                ]);
            }
            return response()->json();
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function syncProviderContracts(Request $request)
    {
        Validator::make($request->all(), [
            'provider_contracts' => 'required|array',
            'provider_contracts.*.id' => 'required|uuid',
            'provider_contracts.*.number' => 'required|string|max:255',
            'provider_contracts.*.date' => 'required|date_format:d.m.Y',
        ])->validate();
        $contracts = $request->provider_contracts;

        try {
            foreach ($contracts as $contract) {
                ProviderContractDocument::query()->updateOrCreate([
                    'uuid' => $contract['id']
                ], [
                    'number' => $contract['number'],
                    'date' => $contract['date'],
                    'is_confirmed' => true,
                ]);
            }
            return response()->json();
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    public function syncWorkAgreements(Request $request)
    {
        Validator::make($request->all(), [
            'work_agreements' => 'required|array',
            'work_agreements.*.id' => 'required|uuid',
            'work_agreements.*.number' => 'required|string|max:255',
            'work_agreements.*.date' => 'required|date_format:d.m.Y',
        ])->validate();
        $contracts = $request->work_agreements;

        try {
            foreach ($contracts as $contract) {
                WorkAgreementDocument::query()->updateOrCreate([
                    'uuid' => $contract['id']
                ], [
                    'number' => $contract['number'],
                    'date' => $contract['date'],
                    'is_confirmed' => true,
                ]);
            }
            return response()->json();
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }
}
