<?php


namespace App\Http\Requests\Consignments;


use App\Models\Consignments\Consignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateConsignmentFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'action' => ['required', Rule::in(Consignment::getActions())],
            'order_id' => 'required|exists:orders,id',
            'responsible_full_name' => 'required|string|max:255',
            'responsible_phone' => 'required|string|max:255',
            'comment' => 'required|string',
            'positions' => 'required|array',
            'positions.*' => 'required',
            'positions.*.nomenclature_id' => 'required|integer|exists:nomenclature,id',
            'positions.*.unit_id' => 'required|integer|exists:nomenclature_units,id',
            'positions.*.count' => 'required|numeric',
            'positions.*.price_without_vat' => 'required|numeric',
            //TODO отрефакторить ставку НДС
            'positions.*.vat_rate' => ['required', Rule::in([1, 1.13, 1.2, 1.3, 1.4])],
            'positions.*.country' => 'required|string',
            'positions.*.cargo_custom_declaration' => 'required|string',
            'positions.*.declaration' => 'required|string',
        ];
    }
}
