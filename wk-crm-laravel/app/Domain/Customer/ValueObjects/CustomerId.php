<?php

namespace App\Domain\Customer\ValueObjects;

use App\Domain\Shared\ValueObjects\Id;

class CustomerId extends Id
{
    public static function generate(): self
    {
        return new self(self::generateUuid());
    }
}