<?php


namespace App\Serializers;


use League\Fractal\Pagination\CursorInterface;
use League\Fractal\Pagination\PaginatorInterface;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\SerializerAbstract;

class CustomerSerializer extends SerializerAbstract
{

    public function collection(?string $resourceKey, array $data): array
    {
        return $data;
    }

    public function item(?string $resourceKey, array $data): array
    {
        return $data ?? [];
    }

    public function null(): ?array
    {
        return [];
    }

    public function includedData(ResourceInterface $resource, array $data): array
    {
        // TODO: Implement includedData() method.
    }

    public function meta(array $meta): array
    {
        if (empty($meta)) {
            return [];
        }

        return ['meta' => $meta];
    }

    public function paginator(PaginatorInterface $paginator): array
    {
        // TODO: Implement paginator() method.
    }

    public function cursor(CursorInterface $cursor): array
    {
        // TODO: Implement cursor() method.
    }
}
