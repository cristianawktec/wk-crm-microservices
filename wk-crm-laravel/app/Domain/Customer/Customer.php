<?php

namespace App\Domain\Customer;

/**
 * Customer Domain Entity - DDD Pattern
 * Versão simplificada sem Value Objects complexos (para MVP)
 * TODO: Evoluir para usar Value Objects quando necessário
 */
class Customer
{
    private ?string $id;
    private string $name;
    private string $email;
    private ?string $phone;
    private ?string $cpf;
    private ?string $address;
    private ?string $city;
    private ?string $state;
    private ?string $postalCode;
    private string $status;

    private function __construct(
        ?string $id,
        string $name,
        string $email,
        ?string $phone,
        ?string $cpf,
        ?string $address,
        ?string $city,
        ?string $state,
        ?string $postalCode,
        string $status
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->cpf = $cpf;
        $this->address = $address;
        $this->city = $city;
        $this->state = $state;
        $this->postalCode = $postalCode;
        $this->status = $status;
    }

    public static function create(
        ?string $id,
        string $name,
        string $email,
        ?string $phone = null,
        ?string $cpf = null,
        ?string $address = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postalCode = null
    ): self {
        return new self(
            $id,
            $name,
            $email,
            $phone,
            $cpf,
            $address,
            $city,
            $state,
            $postalCode,
            'active'
        );
    }

    public function updateInfo(
        ?string $name = null,
        ?string $phone = null,
        ?string $cpf = null,
        ?string $address = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postalCode = null
    ): void {
        if ($name !== null) $this->name = $name;
        if ($phone !== null) $this->phone = $phone;
        if ($cpf !== null) $this->cpf = $cpf;
        if ($address !== null) $this->address = $address;
        if ($city !== null) $this->city = $city;
        if ($state !== null) $this->state = $state;
        if ($postalCode !== null) $this->postalCode = $postalCode;
    }

    public function activate(): void
    {
        $this->status = 'active';
    }

    public function deactivate(): void
    {
        $this->status = 'inactive';
    }

    // Getters
    public function getId(): ?string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getCpf(): ?string { return $this->cpf; }
    public function getAddress(): ?string { return $this->address; }
    public function getCity(): ?string { return $this->city; }
    public function getState(): ?string { return $this->state; }
    public function getPostalCode(): ?string { return $this->postalCode; }
    public function getStatus(): string { return $this->status; }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'cpf' => $this->cpf,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'status' => $this->status,
        ];
    }
}