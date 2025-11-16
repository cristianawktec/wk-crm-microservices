<?php

namespace App\Domain\Lead;

use InvalidArgumentException;

/**
 * Lead Entity - Domain Layer
 * Representa um lead (prospect) no sistema com regras de negócio
 */
class Lead
{
    private function __construct(
        private ?string $id,
        private string $name,
        private ?string $email,
        private ?string $phone,
        private ?string $company,
        private ?string $source,
        private string $status,
        private ?string $interest,
        private ?string $city,
        private ?string $state,
        private ?string $notes,
        private ?\DateTimeInterface $createdAt = null,
        private ?\DateTimeInterface $updatedAt = null
    ) {
        $this->validate();
    }

    public static function create(
        ?string $id,
        string $name,
        ?string $email,
        ?string $phone = null,
        ?string $company = null,
        ?string $source = null,
        string $status = 'new',
        ?string $interest = null,
        ?string $city = null,
        ?string $state = null,
        ?string $notes = null,
        ?\DateTimeInterface $createdAt = null,
        ?\DateTimeInterface $updatedAt = null
    ): self {
        return new self(
            $id,
            $name,
            $email,
            $phone,
            $company,
            $source,
            $status,
            $interest,
            $city,
            $state,
            $notes,
            $createdAt,
            $updatedAt
        );
    }

    private function validate(): void
    {
        if (empty($this->name)) {
            throw new InvalidArgumentException('Lead name is required');
        }

        if (strlen($this->name) > 255) {
            throw new InvalidArgumentException('Lead name cannot exceed 255 characters');
        }

        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        if ($this->state && strlen($this->state) > 2) {
            throw new InvalidArgumentException('State must be 2 characters');
        }

        $validStatuses = ['new', 'contacted', 'qualified', 'converted', 'lost'];
        if (!in_array($this->status, $validStatuses)) {
            throw new InvalidArgumentException('Invalid lead status');
        }
    }

    // Status transitions
    public function markAsContacted(): void
    {
        $this->status = 'contacted';
    }

    public function markAsQualified(): void
    {
        if ($this->status === 'new') {
            throw new InvalidArgumentException('Lead must be contacted before being qualified');
        }
        $this->status = 'qualified';
    }

    public function markAsConverted(): void
    {
        $this->status = 'converted';
    }

    public function markAsLost(): void
    {
        $this->status = 'lost';
    }

    public function addNotes(string $notes): void
    {
        $this->notes = $this->notes ? $this->notes . "\n\n" . $notes : $notes;
    }

    // Getters
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getInterest(): ?string
    {
        return $this->interest;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'source' => $this->source,
            'status' => $this->status,
            'interest' => $this->interest,
            'city' => $this->city,
            'state' => $this->state,
            'notes' => $this->notes,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
