<?php

namespace App\Shared\Type;

trait Changeable
{
    private bool $isDirty = false;
    /** @var Changeset[] */
    private array $changesets = [];

    public function isDirty(): bool
    {
        return $this->isDirty;
    }

    public function appendChangeset(Changeset $changeset): void
    {
        $this->isDirty = true;
        $this->changesets[] = $changeset;
    }

    /**
     * @return Changeset[]
     */
    public function getChangesets(): array
    {
        return $this->changesets;
    }
}