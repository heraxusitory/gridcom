<?php

namespace App\Observers;

use App\Models\Notifications\ContractorNotification;
use Illuminate\Support\Facades\Auth;

class ContractorNotificationObserver
{
    public function created(ContractorNotification $contractor_notification)
    {
        $header = 'Новое уведомление';
        $notification_data = [
            'body' => "Уведомление о поставке № {$contractor_notification->id} от {$contractor_notification?->date}",
            'header' => $header,
            'notificationable_type' => ContractorNotification::class,
            'notificationable_id' => $contractor_notification->id,
            'config_data' => json_encode([
                'entity' => 'contractor_notification',
                'ids' => [$contractor_notification->id]
            ]),
            'created_at' => now()
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();
            $contr_agent_id_exists = $user->isProvider() ? $contractor_notification->contractor?->uuid : null;
            if (!$contr_agent_id_exists)
                return;

            $contractor_notification->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $user->isProvider() ? $contractor_notification->contractor?->uuid : null,
                ]));
        }

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            $contr_agent_id_exists = $user->isProvider() ? $contractor_notification->contractor?->uuid : null;
            if (!$contr_agent_id_exists)
                return;

            $contractor_notification->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $user->isProvider() ? $contractor_notification->contractor?->uuid : null,
                ]));
        }
    }
}
