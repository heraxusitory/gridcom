<?php

namespace App\Http\Requests\PriceNegotiations;

use App\Models\Orders\Order;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\CustomerObject;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use function PHPUnit\Framework\throwException;

class CreatePriceNegotiationFormRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws Exception
     */
    public function rules()
    {
        $user = auth('webapi')->user();

        if ($user->isProvider()) {
            $type = Rule::in(PriceNegotiation::TYPE_CONTRACT_HOME_METHOD());
        } elseif ($user->isContractor()) {
            $type = Rule::in(PriceNegotiation::TYPE_CONTRACT_WORK());
        } else {
            throw new Exception('Пользователь не имеет соответствующей роли для совершения данного действия', 403);
        };

        $data = request()->all();
        Validator::validate($data, [
            'action' => ['required', Rule::in(PriceNegotiation::ACTIONS())],
            'type' => ['required', $type],
        ]);

        switch ($data['type']) {
            case  PriceNegotiation::TYPE_CONTRACT_WORK():
                Validator::validate($data, [
                    'object_id' => 'required|exists:customer_objects,id',
                ]);

                $object = CustomerObject::query()->findOrFail($data['object_id']);
                $sub_object_ids = $object->subObjects()->pluck('id');
                Validator::validate($data, [
                    'sub_object_id' => ['nullable', Rule::in($sub_object_ids)]
                ]);

                Validator::validate($data, [
                    'provider_contr_agent_id' => 'required|exists:contr_agents,id',
                    'contractor_contr_agent_id' => 'required|exists:contr_agents,id',
                    'organization_id' => 'required|exists:organizations,id',
                ]);

                $order_query = Order::query()
                    ->whereRelation('contractor', 'contr_agent_id', $data['contractor_contr_agent_id'])
                    ->whereRelation('customer', 'organization_id', $data['organization_id'])
                    ->whereRelation('customer', 'object_id', $data['object_id'])
                    ->with(['positions.nomenclature', 'customer', 'contractor', 'provider']);

                if (!empty($data['sub_object_id'])) {
                    $order_query->whereRelation('customer', 'sub_object_id', $data['sub_object_id']);
                }
                $order_ids = $order_query->pluck('id');
                $orders = $order_query->get();


                Validator::validate($data, [
                    'order_id' => ['required', Rule::in($order_ids)],
                ]);
                $nomenclature_ids = $order_query->findOrFail($data['order_id'])->positions->map(function ($position) {
                    return $position->nomenclature->id;
                })->unique();

                $validator = Validator::make($data, [
                    'responsible_full_name' => 'required|string|max:255',
                    'responsible_phone' => 'required|string|max:255',
                    'comment' => 'required|string',
                    'file' => 'nullable|file',
                    'positions' => 'required|array',
                    'positions.*' => 'required',
                    'positions.*.nomenclature_id' => ['required', Rule::in($nomenclature_ids)],
                    'positions.*.current_price_without_vat' => ['required', 'numeric']
                ]);

                $validator->validate();

                $validator->after(function ($validator) use ($data, $orders) {
                    foreach ($data['positions'] as $key => $position) {
                        $current_price_without_vat_match = (float)$orders->find($data['order_id'])->positions
//                                ->firstWhere('nomenclature_id', $position['nomenclature_id'])->price_without_vat === (float)$position['current_price_without_vat'];
                            ->where('nomenclature_id', $position['nomenclature_id'])->contains('price_without_vat', (float)$position['current_price_without_vat']);

                        if (!$current_price_without_vat_match) {
                            $validator->errors()->add('positions.' . $key . '.current_price_without_vat', 'The positions.' . $key . '.current_price_without_vat is invalid');
                            break;
                        }
                    }
                })->validate();

                return [
                    'positions.*.new_price_without_vat' => 'required|numeric',
                ];

            case PriceNegotiation::TYPE_CONTRACT_HOME_METHOD():
                Validator::validate($data, [
                    'provider_contr_agent_id' => 'required|exists:contr_agents,id',
                    'organization_id' => 'required|exists:organizations,id',
                ]);

                $order_query = ProviderOrder::query()
                    ->where('provider_contr_agent_id', $data['provider_contr_agent_id'])
                    ->where('organization_id', $data['organization_id'])
                    ->with('actual_positions.nomenclature');
                $order_ids = $order_query->pluck('id');
                $orders = $order_query->get();

                Validator::validate($data, [
                    'order_id' => ['required', Rule::in($order_ids)],
                ]);

                $nomenclature_ids = $order_query->findOrFail($data['order_id'])->actual_positions->map(function ($position) {
                    return $position->nomenclature->id;
                })->unique();

                $validator = Validator::make($data, [
                    'responsible_full_name' => 'required|string|max:255',
                    'responsible_phone' => 'required|string|max:255',
                    'comment' => 'required|string',
                    'file' => 'nullable|file',
                    'positions' => 'required|array',
                    'positions.*' => 'required',
                    'positions.*.nomenclature_id' => ['required', Rule::in($nomenclature_ids)],
                    'positions.*.current_price_without_vat' => ['required', 'numeric'],
                ]);

                $validator->validate();

                $validator->after(function ($validator) use ($data, $orders) {
                    foreach ($data['positions'] as $key => $position) {
                        $current_price_without_vat_match = (float)$orders->find($data['order_id'])->actual_positions
//                                ->firstWhere('nomenclature_id', $position['nomenclature_id'])->price_without_vat === (float)$position['current_price_without_vat'];
                            ->where('nomenclature_id', $position['nomenclature_id'])->contains('price_without_vat', (float)$position['current_price_without_vat']);
                        if (!$current_price_without_vat_match) {
                            $validator->errors()->add('positions.' . $key . '.current_price_without_vat', 'The positions.' . $key . '.current_price_without_vat is invalid');
                            break;
                        }
                    }
                })->validate();
                return [
                    'positions.*.new_price_without_vat' => 'required|numeric',
                ];
            default:
                throw new BadRequestException('Type is required', 400);
        }
    }
}
