<?php

declare(strict_types=1);

namespace App\Shared\Type;

readonly class Changeset
{
    public function __construct(
        private string $field,
        private int|string|bool|null $old,
        private int|string|bool|null $new,
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOld(): int|string|bool|null
    {
        return $this->old;
    }

    public function getNew(): int|string|bool|null
    {
        return $this->new;
    }
}