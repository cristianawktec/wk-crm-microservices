<?php

namespace App\Domain\Shared\ValueObjects;

use Carbon\Carbon;
use DateTimeInterface;

class UpdatedAt
{
    private Carbon $value;

    public function __construct(DateTimeInterface $date)
    {
        $this->value = Carbon::instance($date);
    }

    public static function now(): self
    {
        return new self(Carbon::now());
    }

    public static function fromString(string $date): self
    {
        return new self(Carbon::parse($date));
    }

    public function value(): Carbon
    {
        return $this->value;
    }

    public function format(string $format = 'Y-m-d H:i:s'): string
    {
        return $this->value->format($format);
    }

    public function toISOString(): string
    {
        return $this->value->toISOString();
    }

    public function __toString(): string
    {
        return $this->value->toDateTimeString();
    }
}