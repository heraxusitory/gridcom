<?php


namespace App\Services\Filters;


use App\Models\PriceNegotiations\PriceNegotiation;

class PriceNegotiationFilter extends QueryFilter
{
    public function number(string $number)
    {
        return $this->builder->where('number', 'ILIKE', "%{$number}%");
    }

    public function date(string $time_start = null, string $time_end = null)
    {
        if (is_numeric(strtotime($time_start))) {
            $this->builder->whereDate('date', '>=', $time_start);
        }
        if (is_numeric(strtotime($time_end))) {
            $this->builder->whereDate('date', '<=', $time_end);
        }
        return $this->builder;
    }

    public function type(string $type)
    {
        return $this->builder->where('type', $type);
    }

    public function organization_status(string $organization_status)
    {
        return $this->builder->where('organization_status', $organization_status);
    }

    public function object_name(string $object_name)
    {
        return $this->builder
            ->where('type', PriceNegotiation::TYPE_CONTRACT_WORK())
            ->join('orders', 'price_negotiations.order_id', '=', 'orders.id')
            ->join('order_customers', 'orders.customer_id', '=', 'order_customers.id')
            ->join('customer_objects', 'order_customers.object_id', '=', 'customer_objects.id')
            ->where('customer_objects.name', 'ILIKE', "%{$object_name}%");
    }

    public function order_number(string $order_number)
    {
        $price_negotiation_ids = collect();

        $ids = PriceNegotiation::query()
            ->where('type', PriceNegotiation::TYPE_CONTRACT_WORK())
            ->join('orders', 'price_negotiations.order_id', '=', 'orders.id')
            ->where('orders.number', 'ILIKE', "%{$order_number}%")
            ->pluck('price_negotiations.id');

        $price_negotiation_ids = $price_negotiation_ids->merge($ids);

        $ids = PriceNegotiation::query()
            ->where('type', PriceNegotiation::TYPE_CONTRACT_HOME_METHOD())
            ->join('provider_orders', 'price_negotiations.order_id', '=', 'provider_orders.id')
            ->where('provider_orders.number', 'ILIKE', "%{$order_number}%")
            ->pluck('price_negotiations.id');

        $price_negotiation_ids = $price_negotiation_ids->merge($ids);

        return $this->builder->whereIn('id', $price_negotiation_ids);
    }

    public function provider_name(string $provider_name)
    {
        $price_negotiation_ids = collect();

        $ids = PriceNegotiation::query()
            ->where('type', PriceNegotiation::TYPE_CONTRACT_WORK())
            ->join('orders', 'price_negotiations.order_id', '=', 'orders.id')
            ->join('order_providers', 'orders.provider_id', '=', 'order_providers.id')
            ->join('contr_agents', 'order_providers.contr_agent_id', '=', 'contr_agents.id')
            ->where('contr_agents.name', 'ILIKE', "%{$provider_name}%")
            ->pluck('price_negotiations.id');

        $price_negotiation_ids = $price_negotiation_ids->merge($ids);

        $ids = PriceNegotiation::query()
            ->where('type', PriceNegotiation::TYPE_CONTRACT_HOME_METHOD())
            ->join('provider_orders', 'price_negotiations.order_id', '=', 'provider_orders.id')
            ->join('contr_agents', 'provider_orders.provider_contr_agent_id', '=', 'contr_agents.id')
            ->where('contr_agents.name', 'ILIKE', "%{$provider_name}%")
            ->pluck('price_negotiations.id');

        $price_negotiation_ids = $price_negotiation_ids->merge($ids);

        return $this->builder->whereIn('id', $price_negotiation_ids);
    }
}
