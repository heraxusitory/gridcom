<?php

namespace App\Observers;

use App\Models\Orders\Order;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    public function created(Order $order)
    {
        if ($order->provider_status === Order::PROVIDER_STATUS_DRAFT || $order->customer_status === Order::CUSTOMER_STATUS_DRAFT)
            return;

        $header = 'Новый заказ';
        $notification_data = [
            'body' => "Заказ на поставку № {$order?->number} от {$order?->order_date}",
            'header' => $header,
            'notificationable_type' => Order::class,
            'notificationable_id' => $order->id,
            'config_data' => json_encode([
                'entity' => 'order',
                'ids' => [$order->id]
            ]),
            'created_at' => now(),
        ];

        if (Auth::guard('webapi')->check()) {
            $order->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $order->provider?->contr_agent?->uuid,
                ]));
        }

        if (Auth::guard('api')->check()) {
            $order->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $order->provider?->contr_agent?->uuid,
                ]));
            $order->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $order->contractor?->contr_agent?->uuid,
                ]));
        }
    }

    public function updated(Order $order)
    {
        if ($order->provider_status === Order::PROVIDER_STATUS_DRAFT || $order->customer_status === Order::CUSTOMER_STATUS_DRAFT)
            return;

        if ($order->wasChanged(['customer_status', 'provider_status'])) {
            $header = 'Новый статус';
            $notification_data = [
                'body' => "Заказ на поставку № {$order?->number} от {$order?->order_date}",
                'header' => $header,
                'notificationable_type' => Order::class,
                'notificationable_id' => $order->id,
                'config_data' => json_encode([
                    'entity' => 'order',
                    'ids' => [$order->id]
                ]),
                'created_at' => now()
            ];

            if (Auth::guard('webapi')->check()) {
                $order->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $order->provider?->contr_agent?->uuid,
                    ]));
            }

            if (Auth::guard('api')->check()) {
                $order->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $order->provider?->contr_agent?->uuid,
                    ]));
                $order->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $order->contractor?->contr_agent?->uuid,
                    ]));
            }
        }
    }
}
