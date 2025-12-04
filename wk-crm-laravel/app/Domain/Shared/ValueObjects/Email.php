<?php

namespace App\Domain\Shared\ValueObjects;

use InvalidArgumentException;

class Email
{
    private string $value;

    public function __construct(string $email)
    {
        $this->validate($email);
        $this->value = strtolower(trim($email));
    }

    private function validate(string $email): void
    {
        $trimmedEmail = trim($email);
        
        if (empty($trimmedEmail)) {
            throw new InvalidArgumentException('Email não pode estar vazio');
        }

        if (!filter_var($trimmedEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email inválido');
        }

        if (strlen($trimmedEmail) > 320) {
            throw new InvalidArgumentException('Email muito longo');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domain(): string
    {
        $parts = explode('@', $this->value);
        return $parts[1] ?? '';
    }

    public function localPart(): string
    {
        $parts = explode('@', $this->value);
        return $parts[0] ?? '';
    }

    public function isBusinessEmail(): bool
    {
        $commonPersonalDomains = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
            'uol.com.br', 'terra.com.br', 'ig.com.br', 'bol.com.br'
        ];
        
        return !in_array($this->domain(), $commonPersonalDomains);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}