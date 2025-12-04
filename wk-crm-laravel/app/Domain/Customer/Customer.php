<?php

namespace App\Domain\Customer;

use App\Domain\Customer\ValueObjects\CustomerId;
use App\Domain\Customer\ValueObjects\CustomerEmail;
use App\Domain\Customer\ValueObjects\CustomerPhone;
use App\Domain\Shared\ValueObjects\Name;
use App\Domain\Shared\ValueObjects\CreatedAt;
use App\Domain\Shared\ValueObjects\UpdatedAt;

class Customer
{
    private CustomerId $id;
    private Name $name;
    private CustomerEmail $email;
    private ?CustomerPhone $phone;
    private string $status;
    private ?string $company;
    private ?string $address;
    private ?string $city;
    private ?string $state;
    private ?string $zipCode;
    private ?string $country;
    private CreatedAt $createdAt;
    private UpdatedAt $updatedAt;

    public function __construct(
        CustomerId $id,
        Name $name,
        CustomerEmail $email,
        ?CustomerPhone $phone = null,
        string $status = 'active',
        ?string $company = null,
        ?string $address = null,
        ?string $city = null,
        ?string $state = null,
        ?string $zipCode = null,
        ?string $country = null,
        ?CreatedAt $createdAt = null,
        ?UpdatedAt $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->status = $status;
        $this->company = $company;
        $this->address = $address;
        $this->city = $city;
        $this->state = $state;
        $this->zipCode = $zipCode;
        $this->country = $country;
        $this->createdAt = $createdAt ?? CreatedAt::now();
        $this->updatedAt = $updatedAt ?? UpdatedAt::now();
    }

    public static function create(
        Name $name,
        CustomerEmail $email,
        ?CustomerPhone $phone = null,
        string $status = 'active',
        ?string $company = null,
        ?string $address = null,
        ?string $city = null,
        ?string $state = null,
        ?string $zipCode = null,
        ?string $country = null
    ): self {
        return new self(
            CustomerId::generate(),
            $name,
            $email,
            $phone,
            $status,
            $company,
            $address,
            $city,
            $state,
            $zipCode,
            $country
        );
    }

    public function updateInfo(
        Name $name,
        CustomerEmail $email,
        ?CustomerPhone $phone = null,
        ?string $company = null,
        ?string $address = null,
        ?string $city = null,
        ?string $state = null,
        ?string $zipCode = null,
        ?string $country = null
    ): void {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->company = $company;
        $this->address = $address;
        $this->city = $city;
        $this->state = $state;
        $this->zipCode = $zipCode;
        $this->country = $country;
        $this->updatedAt = UpdatedAt::now();
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->updatedAt = UpdatedAt::now();
    }

    public function deactivate(): void
    {
        $this->status = 'inactive';
        $this->updatedAt = UpdatedAt::now();
    }

    // Getters
    public function id(): CustomerId { return $this->id; }
    public function name(): Name { return $this->name; }
    public function email(): CustomerEmail { return $this->email; }
    public function phone(): ?CustomerPhone { return $this->phone; }
    public function status(): string { return $this->status; }
    public function company(): ?string { return $this->company; }
    public function address(): ?string { return $this->address; }
    public function city(): ?string { return $this->city; }
    public function state(): ?string { return $this->state; }
    public function zipCode(): ?string { return $this->zipCode; }
    public function country(): ?string { return $this->country; }
    public function createdAt(): CreatedAt { return $this->createdAt; }
    public function updatedAt(): UpdatedAt { return $this->updatedAt; }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'name' => $this->name->value(),
            'email' => $this->email->value(),
            'phone' => $this->phone?->value(),
            'status' => $this->status,
            'company' => $this->company,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zipCode,
            'country' => $this->country,
            'created_at' => $this->createdAt->value(),
            'updated_at' => $this->updatedAt->value(),
        ];
    }
}