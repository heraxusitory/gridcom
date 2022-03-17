<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFormRequest extends FormRequest
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
        return [
//            'number' => ,
            'action' => ['required', Rule::in(['draft', 'approve'])],
            'order_date' => 'date_format:d.m.Y',
            'deadline_date' => 'required|date_format:d.m.Y',
//            'customer_status',
//            'provider_status',
            'customer.organization_id' => 'required|string|exists:organizations,id',
            'customer.work_agreement_id' => 'required|string|exists:work_agreements,id',
//            'customer.work_agreement.date' => 'required|date_format:d:m:Y',
            'customer.work_type' => ['required', Rule::in(['Строительство', 'Разработка', 'Интеграция'])],
            'customer.object_id' => 'required|exists:customer_objects,id',
            'customer.sub_object_id' => 'required|exists:customer_sub_objects,id',
            'customer.work_start_date' => 'required|date_format:d.m.Y',
            'customer.work_end_date' => 'required|date_format:d.m.Y',

            'provider.contr_agent_id' => 'required|exists:contr_agents,id',
            'provider.contract_id' => 'required|exists:provider_contracts,id',
//            'provider.contract.date' => 'required|exists:provider_contracts,date',
//            'provider.contr_agent_id' => 'required|integeer',
            'provider.contact.full_name' => 'required|string',
            'provider.contact.email' => 'required|string',
            'provider.contact.phone' => 'required|string',
//            'provider.contact_id' => 'required|integer',

            'contractor.contr_agent_id' => 'required|exists:contr_agents,id',
//            'contractor.contact_id' => 'required|exists:contact_persons,id',
            'contractor.contact.full_name' => 'required|string',
            'contractor.contact.email' => 'required|string',
            'contractor.contact.phone' => 'required|string',
            'contractor.responsible_full_name' => 'required|string',
            'contractor.responsible_phone' => 'required|string',

            'positions' => 'required_if:action,approve|array',
            'positions.*.nomenclature_id' => 'required|exists:nomenclature,id',
            'positions.*.count' => 'required|integer',
            'positions.*.price_without_vat' => 'required|numeric',
            'positions.*.delivery_time' => 'required|date_format:d.m.Y',
            'positions.*.delivery_address' => 'required|string',
        ];
    }
}
