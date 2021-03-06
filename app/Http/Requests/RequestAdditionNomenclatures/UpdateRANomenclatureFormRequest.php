<?php

namespace App\Http\Requests\RequestAdditionNomenclatures;

use App\Models\Orders\Order;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateRANomenclatureFormRequest extends FormRequest
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
        //TODO: костыль переделать , когда будут роли и юзеры
        Validator::validate($data, [
            'action' => ['required', Rule::in(RequestAdditionNomenclature::getActions())],
            'type' => ['required', Rule::in(RequestAdditionNomenclature::getTypes())]
//            'contr_agent_type' => ['required', Rule::in(['provider', 'contractor']),
        ]);

        if (Auth::user()->isContractor()) {
            Validator::validate($data, [
                'work_agreement_id' => 'required|exists:work_agreements,id'
            ]);
            $organization_ids = Order::query()
                ->with('customer')
                ->whereRelation('customer', 'work_agreement_id', $data['work_agreement_id'])
                ->get()->pluck('customer.organization_id')->unique();

//            return Organization::query()->whereIn('id', $organization_ids)->pluck('id');

        } elseif (Auth::user()->isProvider()) {
            Validator::validate($data, [
                'provider_contract_id' => 'required|exists:provider_contracts,id'
            ]);
            $organization_ids = Order::query()
                ->with('customer')
                ->whereRelation('provider', 'provider_contract_id', $data['provider_contract_id'])
                ->get()
                ->pluck('customer.organization_id')->unique();

//            return Organization::query()->whereIn('id', $organization_ids)->pluck('id');
        } else {
            throw new BadRequestException('Данное действие разрешено следующим ролям: подрядчик, поставщик.', 403);
        }

        return [
            'organization_id' => ['required', Rule::in($organization_ids)],
            'nomenclature_id' => ['nullable', Rule::requiredIf(function () use ($data) {
                return $data['type'] === RequestAdditionNomenclature::TYPE_CHANGE();
            }), 'exists:nomenclature,id'],
            'nomenclature_name' => ['nullable', Rule::requiredIf(function () use ($data) {
                return $data['type'] === RequestAdditionNomenclature::TYPE_NEW();
            })],
            'nomenclature_unit' => ['nullable', Rule::requiredIf(function () use ($data) {
                return $data['type'] === RequestAdditionNomenclature::TYPE_NEW();
            })],
            'description' => 'required|string',
            'responsible_full_name' => 'required|string|max:255',
            'contr_agent_comment' => 'required|string',
//            'organization_comment' => 'nullable|string',
            'file' => 'nullable|file',
        ];
    }
}
