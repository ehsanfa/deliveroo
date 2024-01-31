<?php

declare(strict_types=1);

namespace App\Shared\Type;

interface EventStoreRepository
{
    public function getEventsByIdentifier(string $identifier): DomainEventList;

    public function delete(DomainEventList $domainEventList): void;
}