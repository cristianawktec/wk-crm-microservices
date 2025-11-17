<?php

namespace App\Domain\Opportunity;

use InvalidArgumentException;

class Opportunity
{
    private string $id;
    private string $title;
    private ?string $description;
    private float $amount;
    private ?\DateTime $expectedCloseDate;
    private string $status;
    private ?string $leadId;
    private ?string $clienteId;
    private ?\DateTime $createdAt;
    private ?\DateTime $updatedAt;

    // Status válidos
    public const STATUS_OPEN = 'open';
    public const STATUS_NEGOTIATION = 'negotiation';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';
    public const STATUS_CANCELLED = 'cancelled';

    public const VALID_STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_NEGOTIATION,
        self::STATUS_WON,
        self::STATUS_LOST,
        self::STATUS_CANCELLED,
    ];

    private function __construct(
        string $id,
        string $title,
        ?string $description,
        float $amount,
        ?\DateTime $expectedCloseDate,
        string $status,
        ?string $leadId = null,
        ?string $clienteId = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->amount = $amount;
        $this->expectedCloseDate = $expectedCloseDate;
        $this->status = $status;
        $this->leadId = $leadId;
        $this->clienteId = $clienteId;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;

        $this->validate();
    }

    public static function create(
        string $id,
        string $title,
        ?string $description,
        float $amount,
        ?\DateTime $expectedCloseDate,
        string $status,
        ?string $leadId = null,
        ?string $clienteId = null,
        ?\DateTime $createdAt = null,
        ?\DateTime $updatedAt = null
    ): self {
        return new self(
            $id,
            $title,
            $description,
            $amount,
            $expectedCloseDate,
            $status,
            $leadId,
            $clienteId,
            $createdAt,
            $updatedAt
        );
    }

    private function validate(): void
    {
        // Validar título
        if (empty(trim($this->title))) {
            throw new InvalidArgumentException('O título da oportunidade é obrigatório');
        }

        // Validar valor (não pode ser negativo)
        if ($this->amount < 0) {
            throw new InvalidArgumentException('O valor da oportunidade não pode ser negativo');
        }

        // Validar status
        if (!in_array($this->status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                'Status inválido. Valores permitidos: ' . implode(', ', self::VALID_STATUSES)
            );
        }

        // Ao menos um relacionamento (lead ou cliente) deve existir
        if (empty($this->leadId) && empty($this->clienteId)) {
            throw new InvalidArgumentException('A oportunidade deve estar associada a um Lead ou Cliente');
        }
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getExpectedCloseDate(): ?\DateTime
    {
        return $this->expectedCloseDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLeadId(): ?string
    {
        return $this->leadId;
    }

    public function getClienteId(): ?string
    {
        return $this->clienteId;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    // Métodos de negócio - Transições de status
    public function moveToNegotiation(): void
    {
        if ($this->status !== self::STATUS_OPEN) {
            throw new InvalidArgumentException('Apenas oportunidades abertas podem ir para negociação');
        }
        $this->status = self::STATUS_NEGOTIATION;
    }

    public function markAsWon(): void
    {
        if (!in_array($this->status, [self::STATUS_OPEN, self::STATUS_NEGOTIATION], true)) {
            throw new InvalidArgumentException('Status atual não permite marcar como ganha');
        }
        $this->status = self::STATUS_WON;
    }

    public function markAsLost(string $reason = null): void
    {
        if ($this->status === self::STATUS_WON) {
            throw new InvalidArgumentException('Oportunidades ganhas não podem ser marcadas como perdidas');
        }
        $this->status = self::STATUS_LOST;
    }

    public function cancel(): void
    {
        if (in_array($this->status, [self::STATUS_WON, self::STATUS_LOST], true)) {
            throw new InvalidArgumentException('Oportunidades finalizadas não podem ser canceladas');
        }
        $this->status = self::STATUS_CANCELLED;
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isNegotiating(): bool
    {
        return $this->status === self::STATUS_NEGOTIATION;
    }

    public function isWon(): bool
    {
        return $this->status === self::STATUS_WON;
    }

    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_WON, self::STATUS_LOST, self::STATUS_CANCELLED], true);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'expected_close_date' => $this->expectedCloseDate?->format('Y-m-d'),
            'status' => $this->status,
            'lead_id' => $this->leadId,
            'cliente_id' => $this->clienteId,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
