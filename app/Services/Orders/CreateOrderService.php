<?php


namespace App\Services\Orders;


use App\Events\NewStack;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Models\Provider;
use App\Models\References\ContactPerson;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Models\SyncStacks\ContractorSyncStack;
use App\Models\SyncStacks\MTOSyncStack;
use App\Models\SyncStacks\ProviderSyncStack;
use App\Services\IService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

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
                throw new BadRequestException('Action is required', 400);
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
            $customer_sub_object = $customer_object->subObjects()->find($customer_sub_object_id);

            $customer = Customer::create([
                'organization_id' => $organization_id,
                'work_agreement_id' => $work_agreement->id,
                'work_type' => $customer_data['work_type'],
                'object_id' => $customer_object->id,
                'sub_object_id' => $customer_sub_object->id ?? null,
                'work_start_date' => $customer_data['work_start_date'],
                'work_end_date' => $customer_data['work_end_date'],
            ]);

            //provider
            $provider_id = $provider_data['contr_agent_id'];
            $provider_contract_id = $provider_data['contract_id'];

            $provider_contr_agent = ContrAgent::query()->findOrFail($provider_id);
            $provider_contract = ProviderContractDocument::query()
                ->findOrFail($provider_contract_id);

            $provider = Provider::query()->create([
                'provider_contract_id' => $provider_contract->id,
//                'contact_id' => $provider_contact->id,
                'contr_agent_id' => $provider_contr_agent->id,
                'full_name' => $provider_data['contact']['full_name'],
                'email' => $provider_data['contact']['email'],
                'phone' => $provider_data['contact']['phone'],
            ]);

            //contractor

            $contractor_id = $contractor_data['contr_agent_id'];
            /** @var ContrAgent $contractor_contr_agent */
            $contractor_contr_agent = ContrAgent::query()->findOrFail($contractor_id);

            $contractor = Contractor::query()->create([
//                'contact_id' => $contractor_contact->id,
                'contr_agent_id' => $contractor_data['contr_agent_id'],
                'full_name' => $contractor_data['contact']['full_name'],
                'email' => $contractor_data['contact']['email'],
                'phone' => $contractor_data['contact']['phone'],
                'contractor_responsible_full_name' => $contractor_data['responsible_full_name'],
                'contractor_responsible_phone' => $contractor_data['responsible_phone'],
            ]);

            /** @var Order $order */
            $order = Order::query()->create([
                'uuid' => Str::uuid(),
                'order_date' => Carbon::today()->format('Y-m-d'),
                'deadline_date' => $payload['deadline_date'],
                'customer_status' => $customer_status,
                'provider_status' => $provider_status,
                'customer_id' => $customer->id,
                'provider_id' => $provider->id,
                'contractor_id' => $contractor->id,
            ]);

            //positions
            foreach ($positions_data as $position) {
                $nomenclature = Nomenclature::query()->findOrFail($position['nomenclature_id']);
                $order->positions()->create([
                    'position_id' => Str::uuid(),
                    'status' => OrderPosition::STATUS_UNDER_CONSIDERATION,
                    'nomenclature_id' => $position['nomenclature_id'],
                    'unit_id' => $position['unit_id'],
                    'count' => $position['count'],
                    'price_without_vat' => $position['price_without_vat'],
                    'amount_without_vat' => round($position['count'] * $position['price_without_vat'], 2),
                    'delivery_time' => $position['delivery_time'],
                    'delivery_plan_time' => $position['delivery_time'],
                    'delivery_address' => $position['delivery_address'],
                ]);
            }
            if ($order->provider_status === Order::PROVIDER_STATUS_UNDER_CONSIDERATION) {
                event(new NewStack($order,
                        (new ContractorSyncStack())->setContractor($contractor_contr_agent),
                        (new ProviderSyncStack())->setProvider($order->provider->contr_agent),
                        new MTOSyncStack())
                );
            }
            return $order;
        });
    }
}
