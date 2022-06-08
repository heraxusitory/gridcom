<?php


namespace App\Services\Orders;


use App\Models\Orders\Order;
use App\Models\User;
use App\Services\Filters\OrderFilter;
use App\Services\IService;
use App\Services\Sortings\OrderSorting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GetOrdersService implements IService
{
    private $payload;
    private $user;

    public function __construct($payload, private OrderFilter $filter, private OrderSorting $sorting)
    {
        $this->payload = $payload;
        $this->user = Auth::user();
    }

    public function run()
    {
        $orders = Order::query()->filter($this->filter)
            ->with(['customer.contract', 'provider.contract', 'contractor']);
        if ($this->user->isProvider()) {
            $orders->whereRelation('provider', 'contr_agent_id', $this->user->contr_agent_id())
                ->whereRelation('customer', 'customer_status', '<>', Order::CUSTOMER_STATUS_DRAFT)
                ->whereRelation('provider', 'provider_status', '<>', Order::PROVIDER_STATUS_DRAFT);
        } elseif ($this->user->isContractor()) {
            $orders->whereRelation('contractor', 'contr_agent_id', $this->user->contr_agent_id());
        }
        $orders->withSum('positions', 'amount_without_vat');
        return $orders->sorting($this->sorting)/*orderByDesc('created_at')*/ ->get();
    }
}
