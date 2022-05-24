<?php

namespace App\Observers;

use App\Models\ConsignmentRegisters\ConsignmentRegister;
use Illuminate\Support\Facades\Auth;

class ConsignmentRegisterObserver
{
    public function created(ConsignmentRegister $consignment_register)
    {
        $header = 'Новый РТН';
        $notification_data = [
            'body' => "РТН № {$consignment_register?->number} от {$consignment_register?->date}",
            'header' => $header,
            'notificationable_type' => ConsignmentRegister::class,
            'notificationable_id' => $consignment_register->id,
            'config_data' => json_encode([
                'entity' => 'consignment-register',
                'ids' => [$consignment_register->id]
            ])
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();
            $consignment_register->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $user->isProvider() ? $consignment_register->contractor?->uuid : ($user->isContractor() ? $consignment_register->provider?->uuid : null),
                ]));
        }

        if (Auth::guard('api')->check()) {
            $consignment_register->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $consignment_register->provider?->uuid,
                ]));
            $consignment_register->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $consignment_register->contractor?->uuid,
                ]));
        }
    }
}
