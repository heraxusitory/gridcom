<?php

namespace App\Observers;

use App\Models\Consignments\Consignment;
use Illuminate\Support\Facades\Auth;

class ConsignmentObserver
{
    /**
     * Handle the Consignment "created" event.
     *
     * @param Consignment $consignment
     * @return void
     */
    public function created(Consignment $consignment)
    {
        $header = 'Новая товарная накладная';
        $notification_data = [
            'body' => "Товарная накладная № {$consignment?->number} от {$consignment?->date}",
            'header' => $header,
            'notificationable_type' => Consignment::class,
            'notificationable_id' => $consignment->id,
            'config_data' => json_encode([
                'entity' => 'consignment',
                'ids' => [$consignment->id]
            ])
        ];

        if ($user = Auth::guard('webapi')->check()) {
            $consignment->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $user->isProvider() ? $consignment->contractor?->uuid : ($user->isContractor() ? $consignment->provider?->uuid : null),
                ]));
        }

        if (Auth::guard('api')->check()) {
            $consignment->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $consignment->provider?->uuid,
                ]));
            $consignment->notifications()->insertOrIgnore(
                array_merge($notification_data, [
                    'contr_agent_id' => $consignment->contractor?->uuid,
                ]));
        }
    }

    /**
     * Handle the Consignment "updated" event.
     *
     * @return void
     */
    public function updated(Consignment $consignment)
    {
        //
    }

    /**
     * Handle the Consignment "deleted" event.
     *
     * @param Consignment $consignment
     * @return void
     */
    public function deleted(Consignment $consignment)
    {
        //
    }

    /**
     * Handle the Consignment "restored" event.
     *
     * @param Consignment $consignment
     * @return void
     */
    public function restored(Consignment $consignment)
    {
        //
    }

    /**
     * Handle the Consignment "force deleted" event.
     *
     * @param Consignment $consignment
     * @return void
     */
    public function forceDeleted(Consignment $consignment)
    {
        //
    }
}
