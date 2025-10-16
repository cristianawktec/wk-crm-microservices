<?php

namespace App\Domain\Customer\ValueObjects;

use InvalidArgumentException;

class CustomerPhone
{
    private string $value;

    public function __construct(string $phone)
    {
        $this->validate($phone);
        $this->value = $this->format($phone);
    }

    private function validate(string $phone): void
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        if (empty($cleanPhone)) {
            throw new InvalidArgumentException('Telefone não pode estar vazio');
        }

        if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15) {
            throw new InvalidArgumentException('Telefone deve ter entre 10 e 15 dígitos');
        }
    }

    private function format(string $phone): string
    {
        // Remove tudo que não é número
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Formata telefone brasileiro (11 dígitos com DDD)
        if (strlen($cleanPhone) === 11 && substr($cleanPhone, 0, 2) !== '55') {
            return '+55' . $cleanPhone;
        }
        
        // Se já tem código do país, mantém
        if (strlen($cleanPhone) === 13 && substr($cleanPhone, 0, 2) === '55') {
            return '+' . $cleanPhone;
        }
        
        // Para outros padrões, adiciona + se não tiver
        return '+' . $cleanPhone;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function displayFormat(): string
    {
        // Remove o + e formata para exibição
        $numbers = substr($this->value, 1);
        
        // Formato brasileiro: +55 (11) 99999-9999
        if (substr($numbers, 0, 2) === '55' && strlen($numbers) === 13) {
            $ddd = substr($numbers, 2, 2);
            $part1 = substr($numbers, 4, 5);
            $part2 = substr($numbers, 9, 4);
            return "+55 ({$ddd}) {$part1}-{$part2}";
        }
        
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}