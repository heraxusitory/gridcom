<?php


namespace App\Http\Controllers\API\Contractors\Orders;


use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPositions\OrderPosition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|uuid',
            'orders.*.number' => 'nullable|string|max:255',
            'orders.*.deadline_date' => 'nullable|date_format:Y-m-d',
            'orders.*.customer_status' => [Rule::in(Order::getCustomerStatuses())],
            'orders.*.provider_status' => [Rule::in(Order::getProviderStatuses())],

            'orders.*.order_customer.organization_id' => 'required|uuid',
            'orders.*.order_customer.work_agreement_id' => 'required|uuid',
            'orders.*.order_customer.work_type' => 'required|string|max:255',
            'orders.*.order_customer.object_id' => 'required|uuid',
            'orders.*.order_customer.sub_object_id' => 'required|uuid',
            'orders.*.order_customer.work_start_date' => 'required|date_format:Y-m-d',
            'orders.*.order_customer.work_end_date' => 'required|date_format:Y-m-d',

            'orders.*.order_provider.provider_contract_id' => 'nullable|required_without:orders.*.order_provider.provider_contract_name|uuid',
            'orders.*.order_provider.provider_contract_name' => 'nullable|required_without:orders.*.order_provider.provider_contract_id|string|max:255',
            'orders.*.order_provider.contr_agent_id' => 'nullable|required_without:orders.*.order_provider.contr_agent_name|uuid',
            'orders.*.order_provider.contr_agent_name' => 'nullable|required_without:orders.*.order_provider.contr_agent_id|string|max:255',
            'orders.*.order_provider.full_name' => 'nullable|string|max:255',
            'orders.*.order_provider.email' => 'nullable|string|max:255',
            'orders.*.order_provider.phone' => 'nullable|string|max:255',

            'orders.*.order_contractor.contr_agent_id' => 'required|uuid',
            'orders.*.order_contractor.full_name' => 'nullable|string|max:255',
            'orders.*.order_contractor.email' => 'nullable|string|max:255',
            'orders.*.order_contractor.phone' => 'nullable|string|max:255',
            'orders.*.order_contractor.contractor_responsible_full_name' => 'nullable|string|max:255',
            'orders.*.order_contractor.contractor_responsible_phone' => 'nullable|string|max:255',

            'orders.*.order_positions' => 'nullable|array',
            'orders.*.order_positions.*.position_id' => 'required|uuid',
            'orders.*.order_positions.*.status' => ['nullable', Rule::in(OrderPosition::getStatuses())],
            'orders.*.order_positions.*.nomenclature_id' => 'required|uuid',
            'orders.*.order_positions.*.count' => 'nullable|numeric',
            'orders.*.order_positions.*.price_without_vat' => 'nullable|numeric',
            'orders.*.order_positions.*.amount_without_vat' => 'nullable|numeric',
            'orders.*.order_positions.*.delivery_time' => 'nullable|date_format:Y-m-d',
            'orders.*.order_positions.*.delivery_address' => 'nullable|string|max:255',
        ]);
    }
}
