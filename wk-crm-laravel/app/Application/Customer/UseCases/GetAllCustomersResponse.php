<?php

namespace App\Application\Customer\UseCases;

class GetAllCustomersResponse
{
    private array $customers;
    private int $total;
    private int $limit;
    private int $offset;

    public function __construct(array $customers, int $total, int $limit, int $offset)
    {
        $this->customers = $customers;
        $this->total = $total;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function toArray(): array
    {
        return [
            'data' => array_map(fn($customer) => $customer->toArray(), $this->customers),
            'pagination' => [
                'total' => $this->total,
                'limit' => $this->limit,
                'offset' => $this->offset,
                'current_page' => floor($this->offset / $this->limit) + 1,
                'total_pages' => ceil($this->total / $this->limit),
                'has_next' => ($this->offset + $this->limit) < $this->total,
                'has_previous' => $this->offset > 0,
            ]
        ];
    }
}