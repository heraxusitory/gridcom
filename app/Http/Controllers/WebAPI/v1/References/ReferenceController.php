<?php


namespace App\Http\Controllers\WebAPI\v1\References;


use App\Http\Controllers\Controller;
use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\Notifications\ContractorNotification;
use App\Models\Notifications\Notification;
use App\Models\Notifications\OrganizationNotification;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPositions\OrderPosition;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\PaymentRegisters\PaymentRegisterPosition;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\ProviderOrders\Corrections\RequirementCorrection;
use App\Models\ProviderOrders\Corrections\RequirementCorrectionPosition;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Nomenclature;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Models\RequestAdditions\RequestAdditionObject;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReferenceController extends Controller
{
    public function getOrganizations()
    {
        $organizations = Organization::query()
            ->orderByDesc('created_at')
            ->where('is_visible_to_client', true)
            ->get();
        return response()->json(['data' => $organizations]);
    }

    public function getWorkAgreements(Request $request)
    {
        $work_agreements_query = WorkAgreementDocument::query();
//        if (isset($request->name))
//            $work_agreements_query->where('name', 'ILIKE', "%{$request->name}%");
        $work_agreements = $work_agreements_query
            ->where('is_visible_to_client', true)
            ->orderByDesc('created_at')
            ->get();
        return response()->json(['data' => $work_agreements]);
    }

    public function getProviderContracts(Request $request)
    {
        $provider_contracts_query = ProviderContractDocument::query();
//        if (isset($request->name))
//            $provider_contracts_query->where('name', 'ILIKE', "%{$request->name}%");
        $provider_contracts = $provider_contracts_query
            ->orderByDesc('created_at')
            ->where('is_visible_to_client', true)
            ->get();
        return response()->json(['data' => $provider_contracts]);
    }

    public function getObjects(Request $request)
    {
        $objects_query = CustomerObject::query();
//        if (isset($request->name))
//            $objects_query->where('name', 'ILIKE', "%{$request->name}%");

        $objects = $objects_query
            ->whereHas('subObjects', $filter = function ($query) {
                $query->where('is_visible_to_client', true);
            })
            ->where('is_visible_to_client', true)
            ->with(['subObjects' => $filter])
            ->orderByDesc('created_at')
            ->get();
        return response()->json(['data' => $objects]);
    }

    public function getContrAgents(Request $request)
    {
        $contr_agents_query = ContrAgent::query();
//        if (isset($request->name))
//            $contr_agents_query->where('name', 'ILIKE', "%{$request->name}%");

        $contr_agents = $contr_agents_query
            ->orderByDesc('created_at')
            ->where('is_visible_to_client', true)
            ->get();
        return response()->json(['data' => $contr_agents]);
    }

    public function getNomenclature(Request $request, $nomenclature_id = null)
    {
        try {
            $nomenclature_query = Nomenclature::query()->with('units')
                ->where('is_visible_to_client', true)
                ->orderByDesc('created_at');

            if ($nomenclature_id) {
                $nomenclature = $nomenclature_query->findOrFail($nomenclature_id);
                return response()->json(['data' => $nomenclature]);
            }
            if (isset($request->name))
                $nomenclature_query->where('name', 'ILIKE', "%{$request->name}%");
            if (isset($request->mnemocode))
                $nomenclature_query->where('mnemocode', 'ILIKE', "%{$request->mnemocode}%");
            if (data_get($request, 'pagination', null) === 'off') {
                $nomenclature = $nomenclature_query->get();
                return response()->json(['data' => $nomenclature]);
            } else {
                $nomenclature = $nomenclature_query->paginate($request->per_page);
                return response()->json(['data' => $nomenclature]);
            }
        } catch
        (ModelNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            if ($e->getCode() >= 400 && $e->getCode() < 500)
                return response()->json(['message' => $e->getMessage()], $e->getCode());
            else {
                Log::error($e->getMessage(), $e->getTrace());
                return response()->json(['message' => 'System error'], 500);
            }
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function glossary(Request $request)
    {
        $data = [];

        $data['work_types'] = [
            'Строительство', 'Разработка', 'Интеграция',
        ];
        $data['vat_rates'] = config('vat_rates');
        $data['countries'] = config('countries');

        $data['actions'] = Order::getActions();
        $data['orders'] = [
            'customer_statuses' => Order::getCustomerStatuses(),
            'provider_statuses' => Order::getProviderStatuses(),
            'positions' => [
                'statuses' => OrderPosition::getStatuses()
            ]

        ];

        $data['consignment_registers'] = [
            'customer_statuses' => ConsignmentRegister::getCustomerStatuses(),
            'contractor_statuses' => ConsignmentRegister::getContractorStatuses(),
            'contr_agent_statuses' => ConsignmentRegister::getContrAgentStatuses(),
        ];

        $data['payment_registers'] = [
            'customer_statuses' => PaymentRegister::getCustomerStatuses(),
            'provider_statuses' => PaymentRegister::getProviderStatuses(),
            'positions' => [
                'payment_types' => PaymentRegisterPosition::getPaymentTypes(),
            ]
        ];

        $data['price_negotiations'] = [
            'types' => PriceNegotiation::HUMAN_READABLE_TYPES(),
        ];

        $data['provider_orders'] = [
            'stages' => ProviderOrder::HUMAN_READABLE_STAGES(),
        ];
        $data['requirement_corrections'] = [
            'provider_statuses' => RequirementCorrection::getProviderStatuses(),
            'positions' => [
                'statuses' => [RequirementCorrectionPosition::STATUS_AGREED(), RequirementCorrectionPosition::STATUS_REJECTED()]
            ]
        ];

        $data['request_addition_nomenclature'] = [
            'organization_statuses' => RequestAdditionNomenclature::getOrganizationStatuses(),
        ];
        $data['request_addition_objects'] = [
            'organization_statuses' => RequestAdditionObject::getOrganizationStatuses(),
        ];
        $data['notifications'] = [
            'targets' => Notification::targets(),
            'contractor_notification_statuses' => ContractorNotification:: getContractorStatuses(),
            'organization_notification_statuses' => OrganizationNotification::getOrganizationStatuses(),
        ];

        return response()->json(['data' => $data]);
    }
}
