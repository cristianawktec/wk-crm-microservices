<?php

namespace App\Infrastructure\Customer\Repositories;

use App\Domain\Customer\Customer;
use App\Domain\Customer\Repositories\CustomerRepositoryInterface;
use App\Domain\Customer\ValueObjects\CustomerId;
use App\Domain\Customer\ValueObjects\CustomerEmail;
use App\Domain\Customer\ValueObjects\CustomerPhone;
use App\Domain\Shared\ValueObjects\Name;
use App\Domain\Shared\ValueObjects\CreatedAt;
use App\Domain\Shared\ValueObjects\UpdatedAt;
use Illuminate\Support\Facades\DB;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function save(Customer $customer): void
    {
        $data = [
            'id' => $customer->id()->value(),
            'name' => $customer->name()->value(),
            'email' => $customer->email()->value(),
            'phone' => $customer->phone()?->value(),
            'status' => $customer->status(),
            'company' => $customer->company(),
            'address' => $customer->address(),
            'city' => $customer->city(),
            'state' => $customer->state(),
            'zip_code' => $customer->zipCode(),
            'country' => $customer->country(),
            'created_at' => $customer->createdAt()->value(),
            'updated_at' => $customer->updatedAt()->value(),
        ];

        DB::table('customers')->updateOrInsert(
            ['id' => $customer->id()->value()],
            $data
        );
    }

    public function findById(CustomerId $id): ?Customer
    {
        $data = DB::table('customers')->where('id', $id->value())->first();
        
        if (!$data) {
            return null;
        }

        return $this->mapToCustomer($data);
    }

    public function findByEmail(CustomerEmail $email): ?Customer
    {
        $data = DB::table('customers')->where('email', $email->value())->first();
        
        if (!$data) {
            return null;
        }

        return $this->mapToCustomer($data);
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        $results = DB::table('customers')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return $results->map(fn($data) => $this->mapToCustomer($data))->toArray();
    }

    public function findActiveCustomers(int $limit = 100, int $offset = 0): array
    {
        $results = DB::table('customers')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        return $results->map(fn($data) => $this->mapToCustomer($data))->toArray();
    }

    public function delete(CustomerId $id): void
    {
        DB::table('customers')->where('id', $id->value())->delete();
    }

    public function exists(CustomerId $id): bool
    {
        return DB::table('customers')->where('id', $id->value())->exists();
    }

    public function existsByEmail(CustomerEmail $email): bool
    {
        return DB::table('customers')->where('email', $email->value())->exists();
    }

    public function count(): int
    {
        return DB::table('customers')->count();
    }

    public function countActive(): int
    {
        return DB::table('customers')->where('status', 'active')->count();
    }

    private function mapToCustomer($data): Customer
    {
        return new Customer(
            new CustomerId($data->id),
            new Name($data->name),
            new CustomerEmail($data->email),
            $data->phone ? new CustomerPhone($data->phone) : null,
            $data->status,
            $data->company,
            $data->address,
            $data->city,
            $data->state,
            $data->zip_code,
            $data->country,
            CreatedAt::fromString($data->created_at),
            UpdatedAt::fromString($data->updated_at)
        );
    }
}