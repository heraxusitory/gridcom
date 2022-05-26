<?php

namespace App\Http\Requests\Notifications\OrganizationNotification;

use App\Models\Notifications\Notification;
use App\Models\ProviderOrders\ProviderOrder;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UpdateOrganizationNotificationFormRequest extends FormRequest
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
            'organization_id' => 'required|exists:organizations,id',
            'contract_number' => 'required|string|exists:provider_orders,contract_number',
            'contract_date' => 'required|date_format:Y-m-d|exists:provider_orders,contract_date',
            'provider_contr_agent_id' => ['required', 'exists:contr_agents,id', Rule::in([Auth::user()->contr_agent_id()])], #TODO заглушка, поменять когда будут сущности ролей и пользователей для поставщика
//            'contract_stage' => ['required'/*, Rule::in(ProviderOrder::STAGES())*/]
        ]);

        $order_query = ProviderOrder::query()
            ->where('organization_id', $data['organization_id'])
            ->where('provider_contr_agent_id', $data['provider_contr_agent_id'])
            ->where('contract_number', $data['contract_number'])
            ->where('contract_date', $data['contract_date'])
            ->with(['actual_positions.nomenclature']);
        $orders = $order_query->get();

        $contract_numbers = $orders->pluck('contract_number');
        $contract_dates = $orders->pluck('contract_date')->map(function ($date) {
            return (new Carbon($date))->format('Y-m-d');
        });

//        $orders = $order_query->where('contract_stage', $data['contract_stage'])->get();

//        $provider_contract_ids = $orders->map(function ($order) {
//            return $order->provider->provider_contract_id;
//        })->unique();

        Validator::validate($data, [
            'contract_number' => ['required', Rule::in($contract_numbers)],
            'contract_date' => ['required', Rule::in($contract_dates)],
            'provider_contr_agent_id' => 'required|exists:contr_agents,id', #TODO заглушка, поменять когда будут сущности ролей и пользователей для поставщика
            'date_fact_delivery' => ['required', 'date_format:Y-m-d'],
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

        foreach ($data['positions'] as $key => $position) {
            Validator::validate($data, [
                'positions.' . $key . '.order_id' => ['required', Rule::in($orders->pluck('id'))],
            ]);
            $nomenclature_ids = $orders->find($position['order_id'])->actual_positions->pluck('nomenclature_id')->unique();

            Validator::validate($data, [
                'positions.' . $key . '.nomenclature_id' => ['required', 'integer', Rule::in($nomenclature_ids)],
            ]);

            Validator::make($data, [
                'positions.' . $key . '.price_without_vat' => ['required', 'numeric'],
            ])->after(function ($validator) use ($position, $orders, $key) {
                $price_without_vat_match = $orders->find($position['order_id'])->actual_positions
                        ->firstWhere('nomenclature_id', $position['nomenclature_id'])->price_without_vat === $position['price_without_vat'];
                if (!$price_without_vat_match) {
                    $validator->errors()->add('positions.' . $key . '.price_without_vat', 'The positions.' . $key . '.price_without_vat is invalid');
                }
            })->validate();
        }

        return [
            'positions.*.count' => ['required', 'numeric'],
            'positions.*.vat_rate' => ['required', 'numeric', Rule::in(array_keys(config('vat_rates')))],
        ];
    }
}
