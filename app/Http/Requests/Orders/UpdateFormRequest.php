<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormRequest extends FormRequest
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
            'order_date' => 'date_format:d:m:Y',
            'deadline_date' => 'date_format:d:m:Y',
//            'customer_status',
//            'provider_status',
            'customer.organization' => 'required|string|exists:organizations,name',
            'customer.work_agreement.number' => 'required|string|exists:work_agreements,number',
            'customer.work_agreement.date' => 'required|date_format:d:m:Y',
            'customer.work_type' => ['required', Rule::in(['Cтроительство', 'Разработка', 'Интеграция'])],
            'customer.object' => 'required|exists:customer_objects,name',
            'customer.sub_object' => 'required|exists:customer_sub_objects,name',

            'provider.name' => 'required|exists:contr_agents,name',
            'provider.contract.number' => 'required|exists:provider_contracts,number',
            'provider.contract.date' => 'required|exists:provider_contracts,date',
            'provider.contact.full_name' => 'required|string',
            'provider.contact.email' => 'required|string',
            'provider.contact.phone' => 'required|string',

            'contractor.name' => 'required|exists:contr_agents,name',
            'contractor.full_name' => 'required|string',
            'contractor.email' => 'required|string',
            'contractor.phone' => 'required|string',
            'contractor.responsible_full_name' => 'required|string',
            'contractor.responsible_phone' => 'required|string',
        ];
    }
}
