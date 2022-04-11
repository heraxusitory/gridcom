<?php


namespace App\Services\ContractorNotifications;


use App\Models\Notifications\ContractorNotification;
use App\Services\IService;
use Illuminate\Support\Facades\Auth;

class IndexContractorNotificationService implements IService
{
    private ?\Illuminate\Contracts\Auth\Authenticatable $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function run()
    {
        $contractor_notifications = ContractorNotification::query();
        if ($this->user->isProvider()) {
            $contractor_notifications->where('provider_contr_agent_id', $this->user->contr_agent_id());
        }
        if ($this->user->isContractor()) {
            $contractor_notifications->where('contractor_contr_agent_id', $this->user->contr_agent_id());
        }
        return $contractor_notifications->paginate();
    }
}
