<?php


namespace App\Http\Controllers\WebAPI\v1;


use App\Http\Controllers\Controller;
use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\Consignments\Consignment;
use App\Models\Notification;
use App\Models\Notifications\OrganizationNotification;
use App\Models\Orders\Order;
use App\Models\PaymentRegisters\PaymentRegister;
use App\Models\PriceNegotiations\PriceNegotiation;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\RequestAdditions\RequestAdditionNomenclature;
use App\Models\RequestAdditions\RequestAdditionObject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->user = auth('webapi')->user();
    }

    public function index(Request $request)
    {
        $notifications = [];
        if ($this->user->hasCompany())
            $notifications = Notification::query()
                ->where('contr_agent_id', $this->user->contr_agent()->uuid)
                ->orderByDesc('created_at')
                ->get();
        return response()->json(['data' => $notifications]);
    }

    public function getCountNotificationsForEntities()
    {
        $data = [
            'orders' => 0,
            'provider_orders' => 0,
            'consignments' => 0,
            'consignment_registers' => 0,
            'payment_registers' => 0,
            'price_negotiations' => 0,
            'request_additions' => 0,
            'organization_notifications' => 0
        ];

        if ($this->user->hasCompany()) {
            $notifications_count = Notification::query()
                ->where('contr_agent_id', $this->user->contr_agent()->uuid)
                ->get()->groupBy('notificationable_type')
                ->map(function ($notification, $type) {
                    return $notification->count();
                })->toArray();

            if (array_key_exists(Order::class, $notifications_count))
                $data['orders'] = $notifications_count[Order::class];

            if (array_key_exists(ProviderOrder::class, $notifications_count))
                $data['provider_orders'] = $notifications_count[ProviderOrder::class];

            if (array_key_exists(Consignment::class, $notifications_count))
                $data['consignments'] = $notifications_count[Consignment::class];

            if (array_key_exists(ConsignmentRegister::class, $notifications_count))
                $data['consignment_registers'] = $notifications_count[ConsignmentRegister::class];

            if (array_key_exists(PaymentRegister::class, $notifications_count))
                $data['payment_registers'] = $notifications_count[PaymentRegister::class];

            if (array_key_exists(PriceNegotiation::class, $notifications_count))
                $data['price_negotiations'] = $notifications_count[PriceNegotiation::class];

            if (array_key_exists(RequestAdditionObject::class, $notifications_count))
                $data['request_additions'] = $notifications_count[RequestAdditionObject::class];

            if (array_key_exists(RequestAdditionNomenclature::class, $notifications_count))
                $data['request_additions'] = $data['request_additions'] + $notifications_count[RequestAdditionNomenclature::class];

            if (array_key_exists(OrganizationNotification::class, $notifications_count))
                $data['organization_notifications'] = $notifications_count[OrganizationNotification::class];
        }
        return response($data);
    }

    public function destroy(Request $request, $notification_id)
    {
        $notification = Notification::where('contr_agent_id', $this->user->contr_agent()->uuid)->find($notification_id);
        if ($notification) {
            $parent = $notification->notificationable;
            $parent->notifications()->delete();
        }
        return response('', 204);
    }

    public function destroyByEntity(Request $request)
    {
        $request->validate([
            'entity' => ['required', Rule::in(Notification::ENTITIES())],
            'id' => 'required,integer',
        ]);

        $model_class = Notification::ENTITY_TO_MODEL()[$request->entity];
        $object = $model_class::where('id', $request->id)->first();
        if (!is_null($object)) {
            $object->notifications()->where('contr_agent_id', $this->user->contr_agent()->uuid)->delete();
        }
        return response('', 204);
    }
}
