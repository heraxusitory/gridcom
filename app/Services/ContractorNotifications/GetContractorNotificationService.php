<?php


namespace App\Services\ContractorNotifications;


use App\Models\Notifications\ContractorNotification;
use App\Services\IService;

class GetContractorNotificationService implements IService
{
    public function __construct(private $payload, private ContractorNotification $contractor_notification)
    {
    }

    public function run()
    {
        return $this->contractor_notification;
    }
}
