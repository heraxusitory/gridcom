<?php

namespace App\Observers;

use App\Models\RequestAdditions\RequestAdditionObject;
use Illuminate\Support\Facades\Auth;

class RAObjectObserver
{
    public function created(RequestAdditionObject $ra_object)
    {
        $header = 'Новый статус';
        $notification_data = [
            'body' => "Запрос НСИ № {$ra_object?->number} от {$ra_object?->date}",
            'header' => $header,
            'notificationable_type' => RequestAdditionObject::class,
            'notificationable_id' => $ra_object->id,
            'config_data' => json_encode([
                'entity' => 'request-addition-object',
                'ids' => [$ra_object->id]
            ])
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();

            $ra_object->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $ra_object->contr_agent?->uuid,
                ]));
        }

        if (Auth::guard('api')->check()) {
            $ra_object->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $ra_object->contr_agent?->uuid,
                ]));
        }
    }

    public function updated(RequestAdditionObject $ra_object)
    {
        $header = 'Новый статус';
        $notification_data = [
            'body' => "Запрос НСИ № {$ra_object?->number} от {$ra_object?->date}",
            'header' => $header,
            'notificationable_type' => RequestAdditionObject::class,
            'notificationable_id' => $ra_object->id,
            'config_data' => json_encode([
                'entity' => 'request-addition-object',
                'ids' => [$ra_object->id]
            ])
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();

            $ra_object->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $ra_object->contr_agent?->uuid,
                ]));
        }

        if (Auth::guard('api')->check()) {
            $ra_object->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $ra_object->contr_agent?->uuid,
                ]));
        }
    }
}
