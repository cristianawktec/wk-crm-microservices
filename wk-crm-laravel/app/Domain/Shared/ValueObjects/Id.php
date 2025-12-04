<?php

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

abstract class Id
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    protected function validate(string $value): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException('ID não pode estar vazio');
        }

        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException('ID deve ser um UUID válido');
        }
    }

    protected static function generateUuid(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Id $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}