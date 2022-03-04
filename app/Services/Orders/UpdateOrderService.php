<?php


namespace App\Services\Orders;


use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Models\Provider;
use App\Models\References\ContactPerson;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateOrderService implements IService
{

    private $payload;
    /**
     * @var Order
     */
    private $order;

    public function __construct($payload, Order $order)
    {
        $this->payload = $payload;
        $this->order = $order;
    }

    public function run()
    {
        $payload = $this->payload;

        switch ($payload['action']) {
            case Order::ACTION_DRAFT:
                $customer_status = Order::CUSTOMER_STATUS_DRAFT;
                $provider_status = Order::PROVIDER_STATUS_DRAFT;
                break;
            case Order::ACTION_APPROVE:
                $customer_status = Order::CUSTOMER_STATUS_UNDER_CONSIDERATION;
                $provider_status = Order::PROVIDER_STATUS_UNDER_CONSIDERATION;
                break;
            default:
                break;
        }

        return DB::transaction(function () use ($payload, $customer_status, $provider_status) {
            $customer_data = $payload['customer'];
            $provider_data = $payload['provider'];
            $contractor_data = $payload['contractor'];

            $positions_data = $payload['positions'];

            //customer
            $organization_id = $customer_data['organization_id'];
            $work_agreement_id = $customer_data['work_agreement_id'];
//                $work_agreement_date = $customer_data['work_agreement']['date'];
            $customer_object_id = $customer_data['object_id'];
            $customer_sub_object_id = $customer_data['sub_object_id'];

            $organization = Organization::query()->findOrFail($organization_id);
            $work_agreement = WorkAgreementDocument::query()
                ->findOrFail($work_agreement_id);

            $customer_object = CustomerObject::query()->findOrFail($customer_object_id);
            $customer_sub_object = $customer_object->subObjects()->findOrFail($customer_sub_object_id);

            $this->order->customer()->update([
                'organization_id' => $organization_id,
                'work_agreement_id' => $work_agreement->id,
                'work_type' => $customer_data['work_type'],
                'object_id' => $customer_object->id,
                'sub_object_id' => $customer_sub_object->id,
            ]);

            //provider
            $provider_id = $provider_data['id'];
            $provider_contract_id = $provider_data['contract_id'];

            $provider_contr_agent = ContrAgent::query()->findOrFail($provider_id);
            $provider_contract = ProviderContractDocument::query()
                ->findOrFail($provider_contract_id);

            $provider_contact_data = array_merge($provider_data['contact'], ['contr_agent_id' => $provider_contr_agent->id]);
            $provider_contact = ContactPerson::query()
                ->firstOrCreate($provider_contact_data);

            $this->order->provider()->update([
                'provider_contract_id' => $provider_contract->id,
                'contact_id' => $provider_contact->id,
            ]);

            //contractor

            $contractor_id = $contractor_data['id'];
            $contractor_contr_agent = ContrAgent::query()->findOrFail($contractor_id);
            $contractor_contact_data = array_merge($contractor_data['contact'], ['contr_agent_id' => $contractor_contr_agent->id]);
            $contractor_contact = ContactPerson::query()
                ->firstOrCreate($contractor_contact_data);

            $this->order->contractor()->update([
                'contact_id' => $contractor_contact->id,
                'contractor_responsible_full_name' => $contractor_data['responsible_full_name'],
                'contractor_responsible_phone' => $contractor_data['responsible_phone'],
            ]);

            $this->order->update([
                'order_date' => Carbon::today()->format('d.m.Y'),
                'deadline_date' => $payload['deadline_date'],
                'customer_status' => $customer_status,
                'provider_status' => $provider_status,
//                'customer_id' => $customer->id,
//                'provider_id' => $provider->id,
//                'contractor_id' => $contractor->id,
            ]);

            //positions
            foreach ($positions_data as $position) {
                $this->order->positions()->firstOrCreate([
//                    'order_id' => $order->id,
                    'status' => OrderPosition::STATUS_UNDER_CONSIDERATION,
                    'nomenclature_id' => $position['nomenclature_id'],
                    'unit_id' => $position['unit_id'],
                    'count' => $position['count'],
                    'price_without_vat' => $position['price_without_vat'],
                    'amount_without_vat' => round($position['count'] * $position['price_without_vat'], 2),
//                    'total_amount',
                    'delivery_time' => $position['delivery_time'],
                    'delivery_address' => $position['delivery_address'],
                ]);
            }

            return $this->order;
        });
    }
}
