<?php

namespace App\Providers;

use App\Events\NewStack;
use App\Listeners\StackListener;
use App\Models\ConsignmentRegisters\ConsignmentRegister;
use App\Models\Consignments\Consignment;
use App\Models\Notifications\ContractorNotification;
use App\Models\Orders\Order;
use App\Observers\ConsignmentObserver;
use App\Observers\ConsignmentRegisterObserver;
use App\Observers\ContractorNotificationObserver;
use App\Observers\OrderObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        NewStack::class => [
            StackListener::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Order::observe(OrderObserver::class);
        Consignment::observe(ConsignmentObserver::class);
        ConsignmentRegister::observe(ConsignmentRegisterObserver::class);
        ContractorNotification::observe(ContractorNotificationObserver::class);
    }
}
