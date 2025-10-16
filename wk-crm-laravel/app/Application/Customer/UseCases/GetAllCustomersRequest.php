<?php

namespace App\Application\Customer\UseCases;

class GetAllCustomersRequest
{
    public int $limit;
    public int $offset;
    public bool $activeOnly;

    public function __construct(array $data = [])
    {
        $this->limit = min($data['limit'] ?? 50, 100); // Máximo 100
        $this->offset = max($data['offset'] ?? 0, 0);
        $this->activeOnly = $data['active_only'] ?? false;
    }
}