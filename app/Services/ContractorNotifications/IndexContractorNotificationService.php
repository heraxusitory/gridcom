<?php


namespace App\Services\ContractorNotifications;


use App\Models\Notifications\ContractorNotification;
use App\Services\IService;

class IndexContractorNotificationService implements IService
{
    public function __construct()
    {
    }

    public function run()
    {
        return ContractorNotification::query()->paginate();
    }
}
