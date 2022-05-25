<?php

namespace App\Observers;

use App\Models\RequestAdditions\RequestAdditionNomenclature;
use Illuminate\Support\Facades\Auth;

class RANomenclatureObserver
{
    public function created(RequestAdditionNomenclature $ra_nomenclature)
    {
        $header = 'Новый статус';
        $notification_data = [
            'body' => "Запрос НСИ № {$ra_nomenclature?->number} от {$ra_nomenclature?->date}",
            'header' => $header,
            'notificationable_type' => RequestAdditionNomenclature::class,
            'notificationable_id' => $ra_nomenclature->id,
            'config_data' => json_encode([
                'entity' => 'request-addition-nomenclature',
                'ids' => [$ra_nomenclature->id]
            ]),
            'created_at' => now()
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();

            $ra_nomenclature->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $ra_nomenclature->contr_agent?->uuid,
                ]));
        }

        if (Auth::guard('api')->check()) {
            $ra_nomenclature->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $ra_nomenclature->contr_agent?->uuid,
                ]));
        }
    }

    public function updated(RequestAdditionNomenclature $ra_nomenclature)
    {
        $header = 'Новый статус';
        $notification_data = [
            'body' => "Запрос НСИ № {$ra_nomenclature?->number} от {$ra_nomenclature?->date}",
            'header' => $header,
            'notificationable_type' => RequestAdditionNomenclature::class,
            'notificationable_id' => $ra_nomenclature->id,
            'config_data' => json_encode([
                'entity' => 'request-addition-nomenclature',
                'ids' => [$ra_nomenclature->id]
            ]),
            'created_at' => now()
        ];


        if (Auth::guard('webapi')->check()) {
            $user = Auth::guard('webapi')->user();

            $ra_nomenclature->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $ra_nomenclature->contr_agent?->uuid,
                ]));
        }

        if (Auth::guard('api')->check()) {
            $ra_nomenclature->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $ra_nomenclature->contr_agent?->uuid,
                ]));
        }
    }
}
