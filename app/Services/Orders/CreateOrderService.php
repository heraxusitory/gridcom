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

class CreateOrderService implements IService
{
    private $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }


    public function run()
    {
        $payload = $this->payload;

        return DB::transaction(function () use ($payload) {
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

            $customer = Customer::create([
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

            $provider = Provider::query()->create([
                'provider_contract_id' => $provider_contract->id,
                'contact_id' => $provider_contact->id,
            ]);

            //contractor

            $contractor_id = $contractor_data['id'];
            $contractor_contr_agent = ContrAgent::query()->findOrFail($contractor_id);
            $contractor_contact_data = array_merge($contractor_data['contact'], ['contr_agent_id' => $contractor_contr_agent->id]);
            $contractor_contact = ContactPerson::query()
                ->firstOrCreate($contractor_contact_data);

            $contractor = Contractor::query()->create([
                'contact_id' => $contractor_contact->id,
                'contractor_responsible_full_name' => $contractor_data['responsible_full_name'],
                'contractor_responsible_phone' => $contractor_data['responsible_phone'],
            ]);

            $order = Order::query()->create([
                'order_date' => Carbon::today()->format('d.m.Y'),
                'deadline_date' => $payload['deadline_date'],
                'customer_status' => 'На рассмотрении',
                'provider_status' => 'Черновик',
                'customer_id' => $customer->id,
                'provider_id' => $provider->id,
                'contractor_id' => $contractor->id,
            ]);

            //positions
            foreach ($positions_data as $position) {
                $nomenclature_id = $position['nomenclature_id'];

                $position = OrderPosition::query()->create([
                    'order_id' => $order->id,
                    'status' => 'На рассмотрении',
                    'nomenclature_id' => $nomenclature_id,
                    'unit_id' => $position['unit_id'],
                    'count' => $position['count'],
                    'price_without_vat' => $position['price_without_vat'],
                    'amount_without_vat' => round($position['count'] * $position['price_without_vat'], 2),
//                    'total_amount',
                    'delivery_time' => $position['delivery_time'],
                    'delivery_address' => $position['delivery_address'],
                ]);
            }

            return $order;
        });
    }
}
