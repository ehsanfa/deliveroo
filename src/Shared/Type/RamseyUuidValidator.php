<?php

declare(strict_types=1);

namespace App\Shared\Type;

class RamseyUuidValidator implements UuidValidator
{
    public function isValid(string $uuid): bool
    {
        return \Ramsey\Uuid\Uuid::isValid($uuid);
    }
}