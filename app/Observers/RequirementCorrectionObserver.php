<?php

namespace App\Observers;

use App\Models\ProviderOrders\Corrections\RequirementCorrection;
use App\Models\ProviderOrders\ProviderOrder;
use Illuminate\Support\Facades\Auth;

class RequirementCorrectionObserver
{
    public function created(RequirementCorrection $requirement_correction)
    {
        $provider_order = $requirement_correction->provider_order;
        if (!is_null($provider_order))
            return;

        $header = 'Изменение потребности';
        $notification_data = [
            'body' => "Заказ поставщику № {$provider_order?->number} от {$provider_order?->order_date}",
            'header' => $header,
            'notificationable_type' => ProviderOrder::class,
            'notificationable_id' => $provider_order->id,
            'config_data' => json_encode([
                'entity' => 'provider-order/requirement-corrections',
                'ids' => [$provider_order->id]
            ]),
            'created_at' => now()
        ];

        if (Auth::guard('api')->check()) {
            if ($provider_order->notifications()->where(['header' => $header])->doesntExist())
                $provider_order->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $provider_order->provider?->uuid,
                    ]));
        }
    }

    public function updated(RequirementCorrection $requirement_correction)
    {
        $provider_order = $requirement_correction->provider_order;
        if (!is_null($provider_order))
            return;

        $header = 'Изменение потребности';
        $notification_data = [
            'body' => "Заказ поставщику № {$provider_order?->number} от {$provider_order?->order_date}",
            'header' => $header,
            'notificationable_type' => ProviderOrder::class,
            'notificationable_id' => $provider_order->id,
            'config_data' => json_encode([
                'entity' => 'provider-order/requirement-corrections',
                'ids' => [$provider_order->id]
            ]),
            'created_at' => now()
        ];

        if (Auth::guard('api')->check()) {
            if ($provider_order->notifications()->where(['header' => $header])->doesntExist())
                $provider_order->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $provider_order->provider?->uuid,
                    ]));
        }
    }
}
