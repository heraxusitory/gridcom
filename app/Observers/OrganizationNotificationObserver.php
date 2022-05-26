<?php

namespace App\Observers;

use App\Models\Notifications\OrganizationNotification;
use Illuminate\Support\Facades\Auth;

class OrganizationNotificationObserver
{

    public function created(OrganizationNotification $organization_notification)
    {
        $header = 'Новое статус';
        $notification_data = [
            'body' => "Уведомление поставщика № {$organization_notification->id} от {$organization_notification?->date}",
            'header' => $header,
            'notificationable_type' => OrganizationNotification::class,
            'notificationable_id' => $organization_notification->id,
            'config_data' => json_encode([
                'entity' => 'organization_notification',
                'ids' => [$organization_notification->id]
            ]),
            'created_at' => now()
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();
            if ($user->isProvider() ? $organization_notification->provider?->uuid : null) {
                $organization_notification->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $user->isProvider() ? $organization_notification->provider?->uuid : null,
                    ]));
            }
        }

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            if ($user->isProvider() ? $organization_notification->provider?->uuid : null) {
                $organization_notification->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $user->isProvider() ? $organization_notification->provider?->uuid : null,
                    ]));
            }
        }
    }

    public function updated(OrganizationNotification $organization_notification)
    {
        if (!$organization_notification->wasChanged(['status']))
            return;
        $header = 'Новое статус';
        $notification_data = [
            'body' => "Уведомление поставщика № {$organization_notification->id} от {$organization_notification?->date}",
            'header' => $header,
            'notificationable_type' => OrganizationNotification::class,
            'notificationable_id' => $organization_notification->id,
            'config_data' => json_encode([
                'entity' => 'organization_notification',
                'ids' => [$organization_notification->id]
            ]),
            'created_at' => now()
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();
            if ($user->isProvider() ? $organization_notification->provider?->uuid : null) {
                $organization_notification->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $user->isProvider() ? $organization_notification->provider?->uuid : null,
                    ]));
            }
        }

        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            if ($user->isProvider() ? $organization_notification->provider?->uuid : null) {
                $organization_notification->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $user->isProvider() ? $organization_notification->provider?->uuid : null,
                    ]));
            }
        }
    }

}
