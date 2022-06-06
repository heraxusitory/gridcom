<?php

namespace App\Http\Requests\ConsignmentRegisters;

use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\ConsignmentRegisters\ConsignmentRegisterPosition;
use App\Models\Consignments\Consignment;
use App\Models\Orders\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateConsignmentRegisterFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $data = request()->all();
        Validator::make(request()->all(), [
            'action' => ['required', Rule::in(ConsignmentRegister::getActions())],
            'organization_id' => 'required|exists:organizations,id',
            'contractor_contr_agent_id' => 'required|exists:contr_agents,id',
            'provider_contr_agent_id' => 'required|exists:contr_agents,id',
            'customer_object_id' => 'required|exists:customer_objects,id',
            'customer_sub_object_id' => 'nullable|exists:customer_sub_objects,id',
        ])->validate();

        $orders = Order::query()
            ->whereRelation('customer', 'organization_id', request()->organization_id)
            ->whereRelation('customer', 'object_id', request()->customer_object_id)
            ->whereRelation('provider', 'contr_agent_id', request()->provider_contr_agent_id)
            ->whereRelation('contractor', 'contr_agent_id', request()->contractor_contr_agent_id)
            ->with('customer.contract');

        if (request()->customer_sub_object_id ?? null)
            $orders = $orders->whereRelation('customer', 'sub_object_id', request()->customer_sub_object_id);

        $orders = $orders->get();

        $work_agreement_ids = $orders->map(function ($order) {
            return $order->customer->work_agreement_id;
        })->unique();

        $validator = Validator::make(request()->all(), [
            'work_agreement_id' => ['required', Rule::in($work_agreement_ids)],
            'responsible_full_name' => 'required|string|max:255',
            'responsible_phone' => 'required|string|max:255',
            'comment' => 'required|string',
            'positions' => 'required|array',
            'positions.*' => 'required',
            'positions.*.consignment_id' => ['required', 'exists:consignments,id'],
            'positions.*.nomenclature_id' => ['required', 'integer', 'exists:nomenclature,id'],
            'positions.*.price_without_vat' => 'required|numeric',
        ]);

        //todo
        $orders = $orders->filter(function ($order) {
            return $order->customer->work_agreement_id === request()->work_agreement_id;
        });


//        $request_consignment_ids = collect(request()->positions)->pluck('consignment_id');
        $consignments = Consignment::query()
            ->whereHas('positions', function ($q) use ($orders) {
                $q->whereIn('order_id', $orders->pluck('id'));
            })
//            ->whereIn('id', $request_consignment_ids)
            ->with('positions')
            ->get();

        $validator->after(function ($validator) use ($data, $consignments) {
            foreach ($data['positions'] as $key => $position) {
                if (!in_array($position['consignment_id'], $consignments->pluck('id')->toArray())) {
                    $validator->errors()->add('positions.' . $key . '.consignment_id', 'The positions.' . $key . '.consignment_id is invalid');
                    break;
                }
                $consignment = $consignments->find($position['consignment_id']);
                $nomenclature_ids = $consignment->positions->map(function ($position) {
                    return $position->nomenclature->id;
                })->unique();
                if (!in_array($position['nomenclature_id'], $nomenclature_ids->toArray())) {
                    $validator->errors()->add('positions.' . $key . '.nomenclature_id', 'The positions.' . $key . '.nomenclature_id is invalid');
                    break;
                }

                $price_without_vat_match = (float)$consignment->positions
//                        ->firstWhere('nomenclature_id', $position['nomenclature_id'])->price_without_vat === (float)$position['price_without_vat'];
                    ->where('nomenclature_id', $position['nomenclature_id'])->contains('price_without_vat', (float)$position['price_without_vat']);

                if (!$price_without_vat_match) {
                    $validator->errors()->add('positions.' . $key . '.price_without_vat', 'The positions.' . $key . '.price_without_vat is invalid');
                    break;
                }

                $max_available_count_in_consignment_position = (float)$consignment->positions
                    ->where('price_without_vat', $position['price_without_vat'])
                    ->firstWhere('nomenclature_id', $position['nomenclature_id'])->count;
                $common_count_by_consignment_registers = (float)ConsignmentRegisterPosition::query()
                    ->where([
                        'consignment_id' => $consignment->id,
                        'nomenclature_id' => $position['nomenclature_id'],
                        'price_without_vat' => $position['price_without_vat'],
                    ])->sum('count');
                $max_count = abs($max_available_count_in_consignment_position - $common_count_by_consignment_registers);
                Log::debug('$max_available_count_in_consignment_position and $common_count_by_consignment_registers and $max_count', [$max_available_count_in_consignment_position, $common_count_by_consignment_registers, $max_count]);
                if ((float)$position['count'] > $max_count || $max_count == 0) {
                    $validator->errors()->add('positions.' . $key . '.count', 'The positions.' . $key . '.count may not be greater than ' . $max_count . ' or equal to zero');
                    break;
                }
            }
//            foreach ($consignments as $consignment) {
//                $nomenclature_ids = $consignment->positions->pluck('nomenclature_id')->unique();
//
//                Validator::validate(request()->all(), [
//                    'positions.*.consignment_id' => ['required', Rule::in($consignment->pluck('id'))],
//                    'positions.*.nomenclature_id' => ['required', 'integer', Rule::in($nomenclature_ids)],
//                ]);
//            }
        })->validate();


        return [
            'positions.*.count' => 'required|numeric',
            'positions.*.amount_without_vat' => 'required|numeric',
            'positions.*.amount_with_vat' => 'required|numeric',
            //TODO отрефакторить ставку НДС
            'positions.*.vat_rate' => ['required', Rule::in(array_keys(config('vat_rates')))],
//            'positions.*.country' => ['required', 'string', Rule::in(array_keys(config('countries')))],
//            'positions.*.cargo_custom_declaration' => 'required|string',
//            'positions.*.declaration' => 'required|string',
        ];
    }
}
