<?php


namespace App\Services\Sortings;


use Illuminate\Support\Facades\DB;

class PriceNegotiationSorting extends QuerySorting
{

    protected function default_order()
    {
        return $this->builder->orderByDesc('created_at');
    }

    public function number()
    {
        return $this->builder->orderBy('number', $this->order);
    }

    public function type()
    {
        return $this->builder->orderBy('type', $this->order);
    }

    public function date()
    {
        return $this->builder->orderBy('date', $this->order);
    }

    public function organization_status()
    {
        return $this->builder->orderBy('date', $this->order);
    }

    public function responsible_full_name()
    {
        return $this->builder->orderBy('responsible_full_name', $this->order);
    }

    public function responsible_phone()
    {
        return $this->builder->orderBy('responsible_phone', $this->order);
    }

    public function comment()
    {
        return $this->builder->orderBy('comment', $this->order);
    }

    public function order_number()
    {
        return $this->builder
            ->select('price_negotiations.*', DB::raw("
                (CASE price_negotiations.type
                    WHEN 'contract_work'
                    THEN (select number from orders where orders.id = price_negotiations.order_id)
                    WHEN 'contract_home_method'
                    THEN (select number from provider_orders where provider_orders.id = price_negotiations.order_id)
                    ELSE null
                END) as order_number"
            ))
            ->orderBy('order_number', $this->order);
    }

    public function order_date()
    {
        return $this->builder
            ->select('price_negotiations.*', DB::raw("
                (CASE price_negotiations.type
                    WHEN 'contract_work'
                    THEN (select order_date from orders where orders.id = price_negotiations.order_id)
                    WHEN 'contract_home_method'
                    THEN (select order_date from provider_orders where provider_orders.id = price_negotiations.order_id)
                    ELSE null
                END) as order_date"
            ))
            ->orderBy('order_date', $this->order);
    }


    public function order_provider_contract_number()
    {
        return $this->builder
            ->select('price_negotiations.*', DB::raw("
                (CASE price_negotiations.type
                    WHEN 'contract_work'
                    THEN (select provider_contracts.number from orders
                        LEFT JOIN order_providers
                            ON orders.provider_id = order_providers.id
                        LEFT JOIN provider_contracts
                            ON order_providers.provider_contract_id = provider_contracts.id
                        WHERE orders.id = price_negotiations.order_id
                        )

                    WHEN 'contract_home_method'
                    THEN (select contract_number from provider_orders
                        WHERE provider_orders.id = price_negotiations.order_id
                        )
                    ELSE null
                END) as order_provider_contract_number"
            ))
            ->orderBy('order_provider_contract_number', $this->order);
    }

    public function order_provider_contract_date()
    {
        return $this->builder
            ->select('price_negotiations.*', DB::raw("
                (CASE price_negotiations.type
                    WHEN 'contract_work'
                    THEN (select provider_contracts.date from orders
                        LEFT JOIN order_providers
                            ON orders.provider_id = order_providers.id
                        LEFT JOIN provider_contracts
                            ON order_providers.provider_contract_id = provider_contracts.id
                        WHERE orders.id = price_negotiations.order_id
                        )

                    WHEN 'contract_home_method'
                    THEN (select contract_date from provider_orders
                        WHERE provider_orders.id = price_negotiations.order_id
                        )
                    ELSE null
                END) as order_provider_contract_date"
            ))
            ->orderBy('order_provider_contract_date', $this->order);
    }

    public function provider()
    {
        return $this->builder
            ->select('price_negotiations.*', DB::raw("
                (CASE price_negotiations.type
                    WHEN 'contract_work'
                    THEN (select contr_agents.name from orders
                        LEFT JOIN order_providers
                            ON orders.provider_id = order_providers.id
                        LEFT JOIN contr_agents
                            ON order_providers.contr_agent_id = contr_agents.id
                        WHERE orders.id = price_negotiations.order_id
                        )

                    WHEN 'contract_home_method'
                    THEN (select contr_agents.name from provider_orders
                        LEFT JOIN contr_agents
                            ON provider_orders.provider_contr_agent_id = contr_agents.id
                        WHERE provider_orders.id = price_negotiations.order_id
                        )
                    ELSE null
                END) as provider_contr_agent_name"
            ))
            ->orderBy('provider_contr_agent_name', $this->order);
    }

    public function contractor()
    {
        return $this->builder
            ->select('price_negotiations.*', DB::raw("
                (CASE price_negotiations.type
                    WHEN 'contract_work'
                    THEN (select contr_agents.name from orders
                        LEFT JOIN order_contractors
                            ON orders.provider_id = order_contractors.id
                        LEFT JOIN contr_agents
                            ON order_contractors.contr_agent_id = contr_agents.id
                        WHERE orders.id = price_negotiations.order_id
                        )
                    ELSE null
                END) as contractor_contr_agent_name"
            ))
            ->orderBy('contractor_contr_agent_name', $this->order);
    }

    public function organization()
    {
        return $this->builder
            ->select('price_negotiations.*', DB::raw("
                (CASE price_negotiations.type
                    WHEN 'contract_work'
                    THEN (select organizations.name FROM orders
                        LEFT JOIN order_customers
                            ON orders.customer_id = order_customers.id
                        LEFT JOIN organizations
                            ON order_customers.organization_id = organizations.id
                        WHERE orders.id = price_negotiations.order_id
                        )

                    WHEN 'contract_home_method'
                    THEN (select organizations.name FROM provider_orders
                        LEFT JOIN organizations
                            ON provider_orders.organization_id = organizations.id
                        WHERE provider_orders.id = price_negotiations.order_id
                        )
                    ELSE null
                END) as organization_name"
            ))
            ->orderBy('organization_name', $this->order);
    }

    public function object()
    {
        return $this->builder
            ->select('price_negotiations.*', DB::raw("
                (CASE price_negotiations.type
                    WHEN 'contract_work'
                    THEN (select customer_objects.name FROM orders
                        LEFT JOIN order_customers
                            ON orders.customer_id = order_customers.id
                        LEFT JOIN customer_objects
                            ON order_customers.object_id = customer_objects.id
                        WHERE orders.id = price_negotiations.order_id
                        )
                    ELSE null
                END) as object_name"
            ))
            ->orderBy('object_name', $this->order);
    }

    public function sub_object()
    {
        return $this->builder
            ->select('price_negotiations.*', DB::raw("
                (CASE price_negotiations.type
                    WHEN 'contract_work'
                    THEN (select customer_sub_objects.name FROM orders
                        LEFT JOIN order_customers
                            ON orders.customer_id = order_customers.id
                        LEFT JOIN customer_sub_objects
                            ON order_customers.object_id = customer_sub_objects.id
                        WHERE orders.id = price_negotiations.order_id
                        )
                    ELSE null
                END) as sub_object_name"
            ))
            ->orderBy('sub_object_name', $this->order);
    }
}
