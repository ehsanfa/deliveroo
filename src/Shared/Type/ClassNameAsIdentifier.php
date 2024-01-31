<?php

declare(strict_types=1);

namespace App\Shared\Type;

trait ClassNameAsIdentifier
{
    public static function getIdentifier(): string
    {
        return self::class;
    }
}