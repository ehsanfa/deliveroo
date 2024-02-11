<?php

declare(strict_types=1);

namespace App\Delivery\Shared;

use App\Delivery\Shared\Exception\DomainEventNotFoundException;
use App\Shared\Type\DomainEvent;

interface EventStoreRepository
{
    public function getEventsByIdentifier(string $identifier): DomainEventEntityList;

    /**
     * @throws DomainEventNotFoundException
     */
    public function getEventById(DomainEventId $id): DomainEvent;

    /**
     * @implements \Traversable<DomainEventId>
     */
    public function getOldestEventIds(int $limit): \Traversable;

    /**
     * @param DomainEventId[] $ids
     */
    public function delete(array $ids): void;
}