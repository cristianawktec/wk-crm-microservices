<?php

namespace App\Application\Customer\UseCases;

use App\Domain\Customer\Customer;

class CreateCustomerResponse
{
    private Customer $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->customer->id()->value(),
            'name' => $this->customer->name()->value(),
            'email' => $this->customer->email()->value(),
            'phone' => $this->customer->phone()?->displayFormat(),
            'status' => $this->customer->status(),
            'company' => $this->customer->company(),
            'address' => $this->customer->address(),
            'city' => $this->customer->city(),
            'state' => $this->customer->state(),
            'zip_code' => $this->customer->zipCode(),
            'country' => $this->customer->country(),
            'created_at' => $this->customer->createdAt()->toISOString(),
            'updated_at' => $this->customer->updatedAt()->toISOString(),
        ];
    }

    public function customer(): Customer
    {
        return $this->customer;
    }
}