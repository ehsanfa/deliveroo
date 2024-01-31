<?php

declare(strict_types=1);

namespace App\Shared\Type;

interface AggregateRoot
{
    public function isDirty(): bool;

    public function isFresh(): bool;

    public function appendChangeset(Changeset $changeset): void;

    /**
     * @return Changeset[]
     */
    public function getChangesets(): array;
}