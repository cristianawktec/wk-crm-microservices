<?php

namespace App\Application\Customer\UseCases;

class CreateCustomerRequest
{
    public string $name;
    public string $email;
    public ?string $phone;
    public ?string $status;
    public ?string $company;
    public ?string $address;
    public ?string $city;
    public ?string $state;
    public ?string $zipCode;
    public ?string $country;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->phone = $data['phone'] ?? null;
        $this->status = $data['status'] ?? 'active';
        $this->company = $data['company'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->state = $data['state'] ?? null;
        $this->zipCode = $data['zip_code'] ?? null;
        $this->country = $data['country'] ?? 'Brasil';
    }
}