<?php

namespace App\Http\Requests\Consignments;

use App\Models\Consignments\Consignment;
use App\Models\Consignments\ConsignmentPosition;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPositions\OrderPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateConsignmentFormRequest extends FormRequest
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

    /**
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    public function rules()
    {
        $data = request()->all();
        Validator::make($data, [
            'action' => ['required', Rule::in(Consignment::getActions())],
//            'order_id' => 'required|exists:orders,id',
            'organization_id' => ['required', 'exists:organizations,id'],
            'provider_contr_agent_id' => 'required|exists:contr_agents,id',
            'provider_contract_id' => 'required|exists:provider_contracts,id',
            'contractor_contr_agent_id' => 'required|exists:contr_agents,id',
            'work_agreement_id' => 'required|exists:work_agreements,id',
            'customer_object_id' => 'required|exists:customer_objects,id',
            'customer_sub_object_id' => 'nullable|exists:customer_sub_objects,id',
//            'contr_agent' => ['required', 'exists:organizations,id'],
            'responsible_full_name' => 'required|string|max:255',
            'responsible_phone' => 'required|string|max:255',
            'comment' => 'required|string',
        ])->validate();

//        $order_id = request()->order_id;
//        $order = Order::query()
//            ->with(['positions.nomenclature'])
//            ->findOrFail($order_id);
        $orders_query = Order::query()
            ->where('customer_status', '<>', Order::CUSTOMER_STATUS_DRAFT)
            ->where('provider_status', '<>', Order::PROVIDER_STATUS_DRAFT)
            ->whereRelation('customer', 'organization_id', $data['organization_id'])
            ->whereRelation('customer', 'object_id', $data['customer_object_id'])
            ->whereRelation('customer', 'work_agreement_id', $data['work_agreement_id'])
            ->whereRelation('provider', 'contr_agent_id', $data['provider_contr_agent_id'])
            ->whereRelation('provider', 'provider_contract_id', $data['provider_contract_id'])
            ->whereRelation('contractor', 'contr_agent_id', $data['contractor_contr_agent_id'])
            ->with([
//                'positions.nomenclature',
                'positions' => function ($query) {
                    $query->where('status', OrderPosition::STATUS_AGREED)->with('nomenclature');
                }
            ]);

        if ($data['customer_sub_object_id'] ?? null) {
            $orders_query = $orders_query->whereRelation('customer', 'sub_object_id', $data['customer_sub_object_id']);
        }

        $orders = $orders_query->get();

//        $nomenclature_ids = $order->positions->map(function ($position) {
//            return $position->nomenclature->id;
//        })->unique();


        $validator = Validator::make($data, [
            'positions' => [Rule::requiredIf(fn() => $data['action'] === Consignment::ACTION_APPROVE()), 'array'],
            'positions.*' => 'required',
            'positions.*.order_id' => ['required', 'integer', 'exists:orders,id'],
            'positions.*.nomenclature_id' => ['required', 'integer', 'exists:nomenclature,id'/*, Rule::in($nomenclature_ids)*/],
            'positions.*.price_without_vat' => 'required|numeric',
            'positions.*.count' => 'required|numeric',
        ]);

        $validator->after(function ($validator) use ($data, $orders) {
            foreach ($data['positions'] ?? [] as $key => $position) {
                if (!in_array($position['order_id'], $orders->pluck('id')->toArray())) {

                    $validator->errors()->add('positions.' . $key . '.order_id', 'The positions.' . $key . '.order_id is invalid');
                    break;
//                    new BadRequestException('The positions.' . $key . '.order_id is invalid', 422));
                }
                $order = $orders->find($position['order_id']);
                $nomenclature_ids = $order->positions->map(function ($position) {
                    return $position->nomenclature->id;
                })->unique();
                if (!in_array($position['nomenclature_id'], $nomenclature_ids->toArray())) {
                    $validator->errors()->add('positions.' . $key . '.nomenclature_id', 'The positions.' . $key . '.nomenclature_id is invalid');
                    break;
                }
                $price_without_vat_match = (float)$order->positions
                    ->where('nomenclature_id', $position['nomenclature_id'])->contains('price_without_vat', (float)$position['price_without_vat']);
                if (!$price_without_vat_match) {
                    $validator->errors()->add('positions.' . $key . '.price_without_vat', 'The positions.' . $key . '.price_without_vat is invalid');
                    break;
                }

                $max_available_count_in_order_position = (float)$order->positions
                    ->where('price_without_vat', $position['price_without_vat'])
                    ->firstWhere('nomenclature_id', $position['nomenclature_id'])->count;
                $common_count_by_consignments = (float)ConsignmentPosition::query()
                    ->where([
                        'order_id' => $order->id,
                        'nomenclature_id' => $position['nomenclature_id'],
                        'price_without_vat' => $position['price_without_vat'],
                    ])->sum('count');
                $max_count = abs($max_available_count_in_order_position - $common_count_by_consignments);
                Log::debug('$max_available_count_in_order_position and $common_count_by_consignments and $max_count', [$max_available_count_in_order_position, $common_count_by_consignments, $max_count]);
                if ((float)$position['count'] > $max_count || $max_count == 0) {
                    $validator->errors()->add('positions.' . $key . '.count', 'The positions.' . $key . '.count may not be greater than ' . $max_count . ' or equal to zero');
                    break;
                }
            }

        })->validate();


        return [//            'positions.*.unit_id' => 'required|integer|exists:nomenclature_units,id',
//            'positions.*.price_without_vat' => 'required|numeric',
            //TODO ?????????????????????????? ???????????? ??????
            'positions.*.vat_rate' => ['required', Rule::in(array_keys(config('vat_rates')))],
            'positions.*.country' => ['required', 'string', Rule::in(array_keys(config('countries')))],
            'positions.*.cargo_custom_declaration' => 'nullable|string',
            'positions.*.declaration' => 'nullable|string',];
    }
////        return [
////            'action' => ['required', Rule::in(Consignment::getActions())],
////            'order_id' => 'required|exists:orders,id',
////            'responsible_full_name' => 'required|string|max:255',
////            'responsible_phone' => 'required|string|max:255',
////            'comment' => 'required|string',
////            'positions' => 'required_if:action,approve|array',
////            'positions.*' => 'required',
////            'positions.*.nomenclature_id' => 'required|integer|exists:nomenclature,id',
//////            'positions.*.unit_id' => 'required|integer|exists:nomenclature_units,id',
////            'positions.*.count' => 'required|numeric',
//////            'positions.*.price_without_vat' => 'required|numeric',
////            //TODO ?????????????????????????? ???????????? ??????
////            'positions.*.vat_rate' => ['required', Rule::in([1, 1.13, 1.2, 1.3, 1.4])],
////            'positions.*.country' => ['required','string', Rule::in(array_keys(config('countries')))],
////            'positions.*.cargo_custom_declaration' => 'required|string',
////            'positions.*.declaration' => 'required|string',
////        ];
//        $data = request()->all();
//        Validator::make($data, [
//            'action' => ['required', Rule::in(Consignment::getActions())],
////            'order_id' => 'required|exists:orders,id',
//            'organization_id' => ['required', 'exists:organizations,id'],
//            'provider_contr_agent_id' => 'required|exists:contr_agents,id',
//            'provider_contract_id' => 'required|exists:provider_contracts,id',
//            'contractor_contr_agent_id' => 'required|exists:contr_agents,id',
//            'work_agreement_id' => 'required|exists:work_agreement,id',
//            'customer_object_id' => 'required|customer_objects,id',
//            'customer_sub_object_id' => 'required|customer_sub_objects,id',
////            'contr_agent' => ['required', 'exists:organizations,id'],
//            'responsible_full_name' => 'required|string|max:255',
//            'responsible_phone' => 'required|string|max:255',
//            'comment' => 'required|string',
//        ])->validate();
//
////        $order_id = request()->order_id;
////        $order = Order::query()
////            ->with(['positions.nomenclature'])
////            ->findOrFail($order_id);
//        $orders_query = Order::query()
//            ->whereRelation('customer', 'organization_id', $data['organization_id'])
//            ->whereRelation('customer', 'object_id', $data['customer_object_id'])
//            ->whereRelation('customer', 'sub_object_id', $data['customer_sub_object_id'])
//            ->whereRelation('provider', 'contr_agent_id', $data['provider_contr_agent_id'])
//            ->whereRelation('provider', 'provider_contract_id', $data['provider_contract_id'])
//            ->whereRelation('contractor', 'contr_agent_id', $data['contractor_contr_agent_id'])
//            ->whereRelation('contractor', 'work_agreement_id', $data['work_agreement_id'])
//            ->with('positions.nomenclature');
//
//        $orders = $orders_query->get();
//
////        $nomenclature_ids = $order->positions->map(function ($position) {
////            return $position->nomenclature->id;
////        })->unique();
//
//
//        Validator::validate($data, [
//            'positions' => 'required|array',
//            'positions.*' => 'required',
//            'positions.*.order_id' => ['required', 'integer',],
//            'positions.*.nomenclature_id' => ['required', 'integer'/*, Rule::in($nomenclature_ids)*/],
//        ]);
//
//        foreach ($data['positions'] as $key => $position) {
//            throw_if(in_array($position['order_id'], $orders->pluck('id')->toArray()),
//                new BadRequestException('The positions.' . $key . '.order_id is invalid', 422));
//            $nomenclature_ids = $orders->find($position['order_id'])->positions->map(function ($position) {
//                return $position->nomenclature->id;
//            })->unique();
//            throw_if(in_array($position['nomenclature_id'], $nomenclature_ids), new BadRequestException('The positions.' . $key . '.nomenclature_id is invalid', 422));
//        }
//
//        return [
////            'positions.*.unit_id' => 'required|integer|exists:nomenclature_units,id',
//            'positions.*.count' => 'required|numeric',
////            'positions.*.price_without_vat' => 'required|numeric',
//            //TODO ?????????????????????????? ???????????? ??????
//            'positions.*.vat_rate' => ['required', Rule::in([1, 1.13, 1.2, 1.3, 1.4])],
//            'positions.*.country' => ['required', 'string', Rule::in(array_keys(config('countries')))],
//            'positions.*.cargo_custom_declaration' => 'required|string',
//            'positions.*.declaration' => 'required|string',
//        ];
//    }
}
