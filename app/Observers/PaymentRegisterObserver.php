<?php

namespace App\Observers;

use App\Models\PaymentRegisters\PaymentRegister;
use Illuminate\Support\Facades\Auth;

class PaymentRegisterObserver
{
    public function created(PaymentRegister $payment_register)
    {
        $header = 'Новый реестр платежей';
        $notification_data = [
            'body' => "Реестр платежей № {$payment_register?->number} от {$payment_register?->date}",
            'header' => $header,
            'notificationable_type' => PaymentRegister::class,
            'notificationable_id' => $payment_register->id,
            'config_data' => json_encode([
                'entity' => 'payment-register',
                'ids' => [$payment_register->id]
            ])
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();
            $user_exists = $user->isProvider() ? $payment_register->contractor?->uuid : ($user->isContractor() ? $payment_register->provider?->uuid : null);
            if (!$user_exists)
                return;
            $payment_register->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $user->isProvider() ? $payment_register->contractor?->uuid : ($user->isContractor() ? $payment_register->provider?->uuid : null),
                ]));
        }

        if (Auth::guard('api')->check()) {
            if ($payment_register->provider?->uuid) {
                $payment_register->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $payment_register->provider?->uuid,
                    ]));
            }
            if ($payment_register->contractor?->uuid) {
                $payment_register->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $payment_register->contractor?->uuid,
                    ]));
            }
        }
    }

    public function updated(PaymentRegister $payment_register)
    {
        if ($payment_register->provider_status === PaymentRegister::PROVIDER_STATUS_DRAFT)
            return;

        if ($payment_register->wasChanged(['provider_status'])) {
            $header = 'Новый статус';
            $notification_data = [
                'body' => "Реестр платежей № {$payment_register?->number} от {$payment_register?->date}",
                'header' => $header,
                'notificationable_type' => PaymentRegister::class,
                'notificationable_id' => $payment_register->id,
                'config_data' => json_encode([
                    'entity' => 'payment-register',
                    'ids' => [$payment_register->id]
                ]),
                'created_at' => now()
            ];

            if (Auth::guard('webapi')->check()) {
                $user = Auth::guard('webapi')->user();
                $user_exists = $user->isProvider() ? $payment_register->contractor?->uuid : ($user->isContractor() ? $payment_register->provider?->uuid : null);
                if (!$user_exists)
                    return;

                $payment_register->notifications()->insertOrIgnore(
                    array_merge($notification_data, [
                        'contr_agent_id' => $user->isProvider() ? $payment_register->contractor?->uuid : ($user->isContractor() ? $payment_register->provider?->uuid : null),
                    ]));
            }

            if (Auth::guard('api')->check()) {
                if ($payment_register->provider?->uuid) {
                    $payment_register->notifications()->insertOrIgnore(
                        array_merge($notification_data, [
                            'contr_agent_id' => $payment_register->provider?->uuid,
                        ]));
                }
                if ($payment_register->contractor?->uuid) {
                    $payment_register->notifications()->insertOrIgnore(
                        array_merge($notification_data, [
                            'contr_agent_id' => $payment_register->contractor?->uuid,
                        ]));
                }
            }
        }
    }
}
