<?php

namespace App\Providers;

use App\Extensions\CustomGuard;
use App\Models\Orders\LKK\Order;
use App\Models\ProviderOrders\ProviderOrder;
use App\Models\References\ContrAgent;
use App\Models\References\CustomerObject;
use App\Models\References\Organization;
use App\Models\References\ProviderContractDocument;
use App\Models\References\WorkAgreementDocument;
use App\Policies\OrderPolicy;
use App\Policies\References\ContrAgentPolicy;
use App\Policies\References\ObjectPolicy;
use App\Policies\References\OrganizationPolicy;
use App\Policies\References\ProviderContractPolicy;
use App\Policies\References\WorkAgreementPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Organization::class => OrganizationPolicy::class,
        CustomerObject::class => ObjectPolicy::class,
        ContrAgent::class => ContrAgentPolicy::class,
        ProviderContractDocument::class => ProviderContractPolicy::class,
        WorkAgreementDocument::class => WorkAgreementPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::extend('keycloak', function ($app, $name, array $config) {
            return new CustomGuard(/*new CustomUserProvider()*/ Auth::createUserProvider($config['provider']), $app->request);
        });

        $this->registerPolicies();
    }
}
