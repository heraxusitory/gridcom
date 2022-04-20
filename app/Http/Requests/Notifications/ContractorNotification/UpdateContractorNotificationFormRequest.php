<?php

namespace App\Http\Requests\Notifications\ContractorNotification;

use App\Models\Notifications\Notification;
use App\Models\Orders\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateContractorNotificationFormRequest extends FormRequest
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
     */
    public function rules()
    {
        $data = request()->all();
        Validator::validate($data, [
            'action' => ['required', Rule::in(Notification::getActions())],
            'contractor_contr_agent_id' => 'required|exists:contr_agents,id',
            'provider_contr_agent_id' => ['required', 'exists:contr_agents,id', Rule::in([Auth::user()->contr_agent_id()])] #TODO заглушка, поменять когда будут сущности ролей и пользователей для поставщика
        ]);

        $orders = Order::query()
            ->whereRelation('contractor', 'contr_agent_id', $data['contractor_contr_agent_id'])
            ->whereRelation('provider', 'contr_agent_id', $data['provider_contr_agent_id'])
            ->with(['provider', 'positions'])->get();

        $provider_contract_ids = $orders->map(function ($order) {
            return $order->provider->provider_contract_id;
        })->unique();

        Validator::validate($data, [
            'provider_contract_id' => ['required', Rule::in($provider_contract_ids)],
            'date_fact_delivery' => ['required', 'date_format:d.m.Y'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'car_info' => ['required', 'string', 'max:255'],
            'driver_phone' => ['required', 'string', 'max:255'],
            'responsible_full_name' => ['required', 'string', 'max:255'],
            'responsible_phone' => ['required', 'string', 'max:255'],
            'positions' => [Rule::requiredIf(function () use ($data) {
                return $data['action'] === Notification::ACTION_APPROVE();
            }), 'array'],
            'positions.*' => 'required',
        ]);

        foreach ($orders as $order) {
            $nomenclature_ids = $order->positions->pluck('nomenclature_id')->unique();

            Validator::validate(request()->all(), [
                'positions.*.position_id' => 'nullable,uuid',
                'positions.*.order_id' => ['required', Rule::in($order->pluck('id'))],
                'positions.*.nomenclature_id' => ['required', 'integer', Rule::in($nomenclature_ids)],
            ]);
        }

        return [
            'positions.*.count' => ['required', 'numeric'],
            'positions.*.vat_rate' => ['required', 'numeric', Rule::in(array_keys(config('vat_rates')))],
        ];
    }
}
