<?php


namespace App\Http\Controllers\API\MTO\v1;


use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\Consignments\Consignment;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\CustomerSubObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\WorkAgreementDocument;
use App\Models\SyncStacks\MTOSyncStack;
use App\Serializers\CustomerSerializer;
use App\Transformers\API\MTO\v1\ConsignmentRegisterTransformer;
use App\Transformers\API\MTO\v1\ConsignmentTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConsignmentRegisterController
{
//    public function sync(Request $request)
//    {
//        Validator::make($request->all(), [
//            'consignment_registers' => 'required|array',
//            'consignment_registers.*.id' => 'required|uuid',
//            'consignment_registers.*.number' => 'required|string|max:255',
//            'consignment_registers.*.customer_status' => ['required', Rule::in(ConsignmentRegister::getCustomerStatuses())],
//            'consignment_registers.*.contr_agent_status' => ['required', Rule::in(ConsignmentRegister::getContrAgentStatuses())],
//
//            'consignment_registers.*.organization_id' => 'required|uuid',
//            'consignment_registers.*.contractor_contr_agent_id' => 'required|uuid',
//            'consignment_registers.*.provider_contr_agent_id' => 'required|uuid',
//            'consignment_registers.*.customer_object_id' => 'required|uuid',
//            'consignment_registers.*.customer_sub_object_id' => 'required|uuid',
//            'consignment_registers.*.work_agreement_id' => 'required|uuid',
//
//            'consignment_registers.*.responsible_full_name' => 'nullable|string|max:255',
//            'consignment_registers.*.responsible_phone' => 'nullable|string|max:255',
//            'consignment_registers.*.comment' => 'nullable|string',
//            'consignment_registers.*.date' => 'required|date_format:Y-m-d',
//
//            'consignment_registers.*.positions' => 'nullable|array',
//            'consignment_registers.*.positions.*.id' => 'required|uuid',
//            'consignment_registers.*.positions.*.consignment_id' => 'required|uuid',
//            'consignment_registers.*.positions.*.nomenclature_id' => 'required|uuid',
//            'consignment_registers.*.positions.*.count' => 'required|numeric',
//            'consignment_registers.*.positions.*.vat_rate' => 'required|numeric',
//            'consignment_registers.*.positions.*.result_status' => 'required|string|max:255',
//        ])->validate();
//
//        try {
//            $data = $request->all()['consignment_registers'];
//
//            foreach ($data as $item) {
//                DB::transaction(function () use ($item) {
//                    $position_data = $item['positions'] ?? [];
//
//                    $consignment_register = ConsignmentRegister::withoutEvents(function () use ($item) {
//                        $organization = Organization::query()->firstOrCreate([
//                            'uuid' => $item['organization_id'],
//                        ]);
//                        $contractor_contr_agent = ContrAgent::query()->firstOrCreate([
//                            'uuid' => $item['contractor_contr_agent_id'],
//                        ]);
//                        $provider_contr_agent = ContrAgent::query()->firstOrCreate([
//                            'uuid' => $item['provider_contr_agent_id'],
//                        ]);
//                        $customer_object = CustomerObject::query()->firstOrCreate([
//                            'uuid' => $item['customer_object_id'],
//                        ]);
//                        $customer_sub_object = /*$customer_object->subObjects()->firstOrCreate([
//                            'uuid' => $item['customer_sub_object_id'],
//                        ]);*/
//                            CustomerSubObject::query()->firstOrCreate(['uuid' => $item['customer_sub_object_id']], ['customer_object_id' => $customer_object->id]);
////                        $customer_sub_object->customer_object_id = $customer_object->id;
////                        $customer_sub_object->save();
//
//                        $work_agreement = WorkAgreementDocument::query()->firstOrCreate([
//                            'uuid' => $item['work_agreement_id'],
//                        ]);
//
//                        return ConsignmentRegister::query()->updateOrCreate([
//                            'uuid' => $item['id'],
//                        ], [
//                            'number' => $item['number'],
//                            'customer_status' => $item['customer_status'],
//                            'contr_agent_status' => $item['contr_agent_status'],
//                            'organization_id' => $organization->id,
//                            'contractor_contr_agent_id' => $contractor_contr_agent->id,
//                            'provider_contr_agent_id' => $provider_contr_agent->id,
//                            'customer_object_id' => $customer_object->id,
//                            'customer_sub_object_id' => $customer_sub_object->id,
//                            'work_agreement_id' => $work_agreement->id,
//                            'responsible_full_name' => $item['responsible_full_name'],
//                            'responsible_phone' => $item['responsible_phone'],
//                            'comment' => $item['comment'],
//                            'date' => (new Carbon($item['date']))->format('d.m.Y'),
//                        ]);
//                    });
//
//                    $position_ids = [];
//                    foreach ($position_data as $position) {
//                        $consignment = Consignment::query()->firstOrCreate([
//                            'uuid' => $position['consignment_id']
//                        ]);
//                        $nomenclature = Nomenclature::query()->firstOrCreate([
//                            'uuid' => $position['nomenclature_id'],
//                        ]);
//
//                        $position = $consignment_register->positions()->updateOrCreate([
//                            'position_id' => $position['id'],
//                        ], [
//                            'consignment_id' => $consignment->id,
//                            'nomenclature_id' => $nomenclature->id,
//                            'count' => $position['count'],
//                            'vat_rate' => $position['vat_rate'],
//                            'result_status' => $position['result_status'],
//                        ]);
//                        $position_ids[] = $position->id;
//                    }
//                    $consignment_register->positions()->whereNotIn('id', $position_ids)->delete();
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
                $consignments = MTOSyncStack::getModelEntities(ConsignmentRegister::class);
                return fractal()->collection($consignments)->transformWith(ConsignmentRegisterTransformer::class)->serializeWith(CustomerSerializer::class);
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
                return response()->json('Из стека удалено ' . $count . ' реестров накладных');
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return response()->json(['message' => 'System error'], 500);
        }
    }
}
