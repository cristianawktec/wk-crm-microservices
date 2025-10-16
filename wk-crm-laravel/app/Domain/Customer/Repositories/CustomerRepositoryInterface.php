<?php

namespace App\Domain\Customer\Repositories;

use App\Domain\Customer\Customer;
use App\Domain\Customer\ValueObjects\CustomerId;
use App\Domain\Customer\ValueObjects\CustomerEmail;

interface CustomerRepositoryInterface
{
    public function save(Customer $customer): void;
    
    public function findById(CustomerId $id): ?Customer;
    
    public function findByEmail(CustomerEmail $email): ?Customer;
    
    public function findAll(int $limit = 100, int $offset = 0): array;
    
    public function findActiveCustomers(int $limit = 100, int $offset = 0): array;
    
    public function delete(CustomerId $id): void;
    
    public function exists(CustomerId $id): bool;
    
    public function existsByEmail(CustomerEmail $email): bool;
    
    public function count(): int;
    
    public function countActive(): int;
}