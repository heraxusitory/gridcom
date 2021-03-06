<?php


namespace App\Http\Controllers\API\MTO\v1;


use App\Http\Controllers\Controller;
use App\Models\Consignments\Consignment;
use App\Models\Orders\Order;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\CustomerSubObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Models\SyncStacks\MTOSyncStack;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\ConsignmentTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConsignmentController extends Controller
{
//    public function sync(Request $request)
//    {
//        Validator::validate($request->all(), [
//            'consignments' => 'required|array',
//            'consignments.*.id' => 'required|uuid',
//            'consignments.*.number' => 'required|string|max:255',
//            'consignments.*.date' => 'required|date_format:Y-m-d',
//            'consignments.*.organization_id' => ['required', 'uuid',],
//            'consignments.*.provider_contr_agent_id' => 'required|uuid',
//            'consignments.*.provider_contract_id' => 'required|uuid',
//            'consignments.*.contractor_contr_agent_id' => 'required|uuid',
//            'consignments.*.work_agreement_id' => 'required|uuid',
//            'consignments.*.customer_object_id' => 'required|uuid',
//            'consignments.*.customer_sub_object_id' => 'required|uuid',
//            'consignments.*.responsible_full_name' => 'nullable|string|max:255',
//            'consignments.*.responsible_phone' => 'nullable|string|max:255',
//            'consignments.*.comment' => 'nullable|string',
//
//            'consignments.*.positions' => 'nullable|array',
//            'consignments.*.positions.*.id' => 'required|uuid',
//            'consignments.*.positions.*.order_id' => 'required|uuid',
//            'consignments.*.positions.*.nomenclature_id' => 'required|uuid',
//            'consignments.*.positions.*.count' => 'required|numeric',
//            'consignments.*.positions.*.price_without_vat' => 'required|numeric',
//            'consignments.*.positions.*.amount_without_vat' => 'required|numeric',
//            'consignments.*.positions.*.vat_rate' => 'required|numeric',
//            'consignments.*.positions.*.amount_with_vat' => 'required|numeric',
//            'consignments.*.positions.*.country' => ['required', 'string', Rule::in(array_keys(config('countries')))],
//            'consignments.*.positions.*.cargo_custom_declaration' => 'nullable|string',
//            'consignments.*.positions.*.declaration' => 'nullable|string',
//        ]);
//
//        try {
//            $data = $request->all()['consignments'];
//
//            foreach ($data as $item) {
//                DB::transaction(function () use ($item) {
//                    $position_data = $item['positions'] ?? [];
//
//                    $consignment = Consignment::withoutEvents(function () use ($item) {
//                        $organization = Organization::query()->firstOrCreate(['uuid' => $item['organization_id']]);
//                        $provider_contr_agent = ContrAgent::query()->firstOrCreate(['uuid' => $item['provider_contr_agent_id']]);
//                        $provider_contract = ProviderContractDocument::query()->firstOrCreate(['uuid' => $item['provider_contract_id']]);
//                        $contractor_contr_agent = ContrAgent::query()->firstOrCreate(['uuid' => $item['contractor_contr_agent_id']]);
//                        $work_agreement = WorkAgreementDocument::query()->firstOrCreate(['uuid' => $item['work_agreement_id']]);
//                        $object = CustomerObject::query()->firstOrCreate(['uuid' => $item['customer_object_id']]);
////                        $sub_object = $object->subObjects()->firstOrCreate(['uuid' => $item['customer_sub_object_id']]);
//                        $sub_object = CustomerSubObject::query()->firstOrCreate(['uuid' => $item['customer_sub_object_id']],
//                            ['customer_object_id' => $object->id]);
////                        $sub_object->customer_object_id = $object->id;
////                        $sub_object->save();
//
//                        $consignment = collect([
//                            'uuid' => $item['id'],
//                            'number' => $item['number'],
//                            'date' => (new Carbon($item['date']))->format('d.m.Y'),
//                            'organization_id' => $organization->id,
//                            'provider_contr_agent_id' => $provider_contr_agent->id,
//                            'provider_contract_id' => $provider_contract->id,
//                            'contractor_contr_agent_id' => $contractor_contr_agent->id,
//                            'work_agreement_id' => $work_agreement->id,
//                            'customer_object_id' => $object->id,
//                            'customer_sub_object_id' => $sub_object->id,
//                            'responsible_full_name' => $item['responsible_full_name'] ?? null,
//                            'responsible_phone' => $item['responsible_phone'] ?? null,
//                            'comment' => $item['comment'] ?? null,
//                        ]);
//                        return Consignment::query()->updateOrCreate([
//                            'uuid' => $consignment['uuid'],
//                        ], $consignment->toArray());
//                    });
//
//                    $position_ids = [];
//                    foreach ($position_data as $position) {
//                        $nomenclature = Nomenclature::query()->firstOrCreate([
//                            'uuid' => $position['nomenclature_id'],
//                        ]);
//                        $order = Order::query()->firstOrCreate([
//                            'uuid' => $position['order_id']
//                        ]);
//
//                        $position = collect([
//                            'position_id' => $position['id'],
//                            'order_id' => $order->id,
//                            'nomenclature_id' => $nomenclature->id,
//                            'count' => $position['count'],
//                            'price_without_vat' => $position['price_without_vat'],
//                            'amount_without_vat' => $position['amount_without_vat'],
//                            'vat_rate' => $position['vat_rate'],
//                            'amount_with_vat' => $position['amount_with_vat'],
//                            'country' => $position['country'],
//                            'cargo_custom_declaration' => $position['cargo_custom_declaration'] ?? null,
//                            'declaration' => $position['declaration'] ?? null,
//                        ]);
//                        $position = $consignment->positions()->updateOrCreate([
//                            'position_id' => $position['position_id']
//                        ], $position->toArray());
//                        $position_ids[] = $position->id;
//                    }
//                    $consignment->positions()->whereNotIn('id', $position_ids)->delete();
//                });
//            }
//            return response()->json();
//        } catch (\Exception $e) {
//            Log::error($e->getMessage(), $e->getTrace());
//            return response()->json(['message' => 'System error'], 500);
//        }
//    }

    public function synchronize(Request $request)
    {
        try {
            return DB::transaction(function () {
                $consignments = MTOSyncStack::getModelEntities(Consignment::class);
                return fractal()->collection($consignments)->transformWith(ConsignmentTransformer::class)->serializeWith(CustomerSerializer::class);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }

    public function removeFromStack(Request $request)
    {
        $request->validate([
            'stack_ids' => 'required|array',
            'stack_ids.*' => 'required|uuid',
        ]);
        try {
            return DB::transaction(function () use ($request) {
                $count = MTOSyncStack::destroy($request->stack_ids);
                return response()->json('???? ?????????? ?????????????? ' . $count . ' ??????????????????');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
