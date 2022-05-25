<?php

namespace App\Observers;

use App\Models\ProviderOrders\ProviderOrder;
use Illuminate\Support\Facades\Auth;

class ProviderOrderObserver
{
    public function created(ProviderOrder $provider_order)
    {
        $header = 'Новый заказ поставщику';
        $notification_data = [
            'body' => "Заказ поставщику № {$provider_order?->number} от {$provider_order?->order_date}",
            'header' => $header,
            'notificationable_type' => ProviderOrder::class,
            'notificationable_id' => $provider_order->id,
            'config_data' => json_encode([
                'entity' => 'provider-order',
                'ids' => [$provider_order->id]
            ])
        ];


//        if (Auth::guard('webapi')->check()) {
//            $user = Auth::guard('webapi')->user();
//            $provider_order->notifications()->insertOrIgnore(
//                array_merge($notification_data, [
//                    'contr_agent_id' => $user->isProvider() ? $provider_order->contractor?->uuid : ($user->isContractor() ? $provider_order->provider?->uuid : null),
//                ]));
//        }

        if (Auth::guard('api')->check()) {
            $provider_order->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $provider_order->provider?->uuid,
                ]));
        }
    }

    public function updated(ProviderOrder $provider_order)
    {
//        if ($provider_order->wasChanged(['provider_status'])) {
        $header = 'Изменения';
        $notification_data = [
            'body' => "Заказ поставщику № {$provider_order?->number} от {$provider_order?->order_date}",
            'header' => $header,
            'notificationable_type' => ProviderOrder::class,
            'notificationable_id' => $provider_order->id,
            'config_data' => json_encode([
                'entity' => 'provider-order',
                'ids' => [$provider_order->id]
            ])
        ];

//            if (Auth::guard('webapi')->check()) {
//                $user = Auth::guard('webapi')->user();
//                $payment_register->notifications()->insertOrIgnore(
//                    array_merge($notification_data, [
//                        'contr_agent_id' => $user->isProvider() ? $payment_register->contractor?->uuid : ($user->isContractor() ? $payment_register->provider?->uuid : null),
//                    ]));
//            }

        if (Auth::guard('api')->check()) {
            $provider_order->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $provider_order->provider?->uuid,
                ]));
        }
//        }
    }
}
