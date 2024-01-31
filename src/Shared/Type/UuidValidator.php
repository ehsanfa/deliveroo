<?php

declare(strict_types=1);

namespace App\Shared\Type;

interface UuidValidator
{
    public function isValid(string $uuid): bool;
}