<?php

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

class Name
{
    private string $value;

    public function __construct(string $name)
    {
        $this->validate($name);
        $this->value = trim($name);
    }

    private function validate(string $name): void
    {
        $trimmedName = trim($name);
        
        if (empty($trimmedName)) {
            throw new InvalidArgumentException('Nome não pode estar vazio');
        }

        if (strlen($trimmedName) < 2) {
            throw new InvalidArgumentException('Nome deve ter pelo menos 2 caracteres');
        }

        if (strlen($trimmedName) > 255) {
            throw new InvalidArgumentException('Nome não pode ter mais de 255 caracteres');
        }

        if (!preg_match('/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u', $trimmedName)) {
            throw new InvalidArgumentException('Nome contém caracteres inválidos');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function firstName(): string
    {
        $parts = explode(' ', $this->value);
        return $parts[0];
    }

    public function lastName(): string
    {
        $parts = explode(' ', $this->value);
        if (count($parts) > 1) {
            return end($parts);
        }
        return '';
    }

    public function fullName(): string
    {
        return $this->value;
    }

    public function initials(): string
    {
        $words = explode(' ', $this->value);
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty(trim($word))) {
                $initials .= strtoupper(substr(trim($word), 0, 1));
            }
        }
        
        return $initials;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}