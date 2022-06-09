<?php

namespace App\Observers;

use App\Models\PriceNegotiations\PriceNegotiation;
use Illuminate\Support\Facades\Auth;

class PriceNegotiationObserver
{
    public function created(PriceNegotiation $price_negotiation)
    {
        $header = 'Новый статус';
        $notification_data = [
            'body' => "Согласование цен № {$price_negotiation?->number} от {$price_negotiation?->date}",
            'header' => $header,
            'notificationable_type' => PriceNegotiation::class,
            'notificationable_id' => $price_negotiation->id,
            'config_data' => json_encode([
                'entity' => 'price-negotiation',
                'ids' => [$price_negotiation->id]
            ]),
            'created_at' => now()
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();
            if ($price_negotiation->type === PriceNegotiation::TYPE_CONTRACT_WORK()) {
                $user_exists = $user->isProvider() ? $price_negotiation->contractor?->uuid : ($user->isContractor() ? $price_negotiation->provider?->uuid : null);
                if (!$user_exists)
                    return;
                $price_negotiation->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $user->isProvider() ? $price_negotiation->contractor?->uuid : ($user->isContractor() ? $price_negotiation->provider?->uuid : null),
                    ]));
            }
            if ($price_negotiation->type === PriceNegotiation::TYPE_CONTRACT_HOME_METHOD()) {
                if ($price_negotiation->provider?->uuid) {
                    $price_negotiation->notifications()->insertOrIgnore(
                        array_merge($notification_data, [
                            'contr_agent_id' => $price_negotiation->provider?->uuid,
                        ]));
                }
            }
        }

        if (Auth::guard('api')->check()) {
            if ($price_negotiation->provider?->uuid) {
                $price_negotiation->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $price_negotiation->provider?->uuid,
                    ]));
            }
            if ($price_negotiation->contractor?->uuid) {
                $price_negotiation->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $price_negotiation->contractor?->uuid,
                    ]));
            }
        }
    }

    public function updated(PriceNegotiation $price_negotiation)
    {
        $header = 'Новый статус';
        $notification_data = [
            'body' => "Согласование цен № {$price_negotiation?->number} от {$price_negotiation?->date}",
            'header' => $header,
            'notificationable_type' => PriceNegotiation::class,
            'notificationable_id' => $price_negotiation->id,
            'config_data' => json_encode([
                'entity' => 'price-negotiation',
                'ids' => [$price_negotiation->id]
            ]),
            'created_at' => now()
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();
            if ($price_negotiation->type === PriceNegotiation::TYPE_CONTRACT_WORK()) {
                $user_exists = $user->isProvider() ? $price_negotiation->contractor?->uuid : ($user->isContractor() ? $price_negotiation->provider?->uuid : null);
                if (!$user_exists)
                    return;

                $price_negotiation->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $user->isProvider() ? $price_negotiation->contractor?->uuid : ($user->isContractor() ? $price_negotiation->provider?->uuid : null),
                    ]));
            }
            if ($price_negotiation->type === PriceNegotiation::TYPE_CONTRACT_HOME_METHOD()) {
                if ($price_negotiation->provider?->uuid) {
                    $price_negotiation->notifications()->insertOrIgnore(
                        array_merge($notification_data, [
                            'contr_agent_id' => $price_negotiation->provider?->uuid,
                        ]));
                }
            }
        }

        if (Auth::guard('api')->check()) {
            if ($price_negotiation->contr_agent?->uuid) {
                $price_negotiation->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $price_negotiation->contr_agent?->uuid,
                    ]));
            }
//            if ($price_negotiation->contractor?->uuid) {
//                $price_negotiation->notifications()->insertOrIgnore(
//                    array_merge($notification_data, [
//                        'contr_agent_id' => $price_negotiation->contractor?->uuid,
//                    ]));
//            }
        }
    }
}
