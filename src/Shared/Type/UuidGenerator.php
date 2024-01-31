<?php

declare(strict_types=1);

namespace App\Shared\Type;

interface UuidGenerator
{
    public function generate(): Uuid;
}