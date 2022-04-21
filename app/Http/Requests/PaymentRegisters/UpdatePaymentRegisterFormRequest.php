<?php

namespace App\Http\Requests\PaymentRegisters;

use App\Models\Orders\LKK\Order;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\PaymentRegisters\PaymentRegisterPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UpdatePaymentRegisterFormRequest extends FormRequest
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
        $provider_contract_ids = DB::table('orders')
            ->join('order_providers', 'orders.provider_id', '=', 'order_providers.id')
            ->join('order_contractors', 'orders.contractor_id', '=', 'order_contractors.id')
            ->join('provider_contracts', 'order_providers.provider_contract_id', '=', 'provider_contracts.id')
            ->where('order_providers.contr_agent_id', request()->provider_contr_agent_id)
            ->where('order_contractors.contr_agent_id', request()->contractor_contr_agent_id)
            ->pluck('provider_contracts.id');

        $available_order_ids = Order::query()->whereRelation('provider', 'contr_agent_id', request()->provider_contr_agent_id)
            ->whereRelation('provider', 'provider_contract_id', request()->provider_contract_id)
            ->whereRelation('contractor', 'contr_agent_id', request()->contractor_contr_agent_id)->pluck('id');

        return [
            'action' => [Rule::in(PaymentRegister::getActions())],
            'provider_contr_agent_id' => 'required|exists:contr_agents,id',
            'contractor_contr_agent_id' => ['required','exists:contr_agents,id', Rule::in([Auth::user()->contr_agent_id()])],
            'provider_contract_id' => ['required', Rule::in($provider_contract_ids)],
            'responsible_full_name' => 'required|string',
            'responsible_phone' => 'required|string',
            'comment' => 'required|string',

            'positions' => 'required_if:action,approve|array',
            'positions.*.order_id' => ['required', Rule::in($available_order_ids)],
            'positions.*.payment_order_number' => ['required', 'string'],
            'positions.*.payment_order_date' => ['required', 'date_format:Y-m-d'],
            'positions.*.amount_payment' => ['required', 'numeric'],
            'positions.*.payment_type' => ['required', 'string', Rule::in(PaymentRegisterPosition::getPaymentTypes())],
        ];
    }
}
