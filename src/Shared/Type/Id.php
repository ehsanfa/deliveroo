<?php

declare(strict_types=1);

namespace App\Shared\Type;

abstract readonly class Id
{
    public function __construct(
        private Uuid $id,
    ) {
    }

    public function toString(): string
    {
        return $this->id->toString();
    }
}