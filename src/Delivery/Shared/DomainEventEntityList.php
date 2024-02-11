<?php

declare(strict_types=1);

namespace App\Delivery\Shared;

use Traversable;

/**
 * @implements \IteratorAggregate<DomainEventEntity>
 */
final readonly class DomainEventEntityList implements \IteratorAggregate
{
    /**
     * @param DomainEventEntity[] $domainEventEntities
     */
    public function __construct(
        private array $domainEventEntities,
    ) {
    }

    public function append(DomainEventEntity $domainEventEntity): DomainEventEntityList
    {
        $domainEventEntities = $this->domainEventEntities;
        $domainEventEntities[] = $domainEventEntity;
        return new DomainEventEntityList($domainEventEntities);
    }

    public function count(): int
    {
        return count($this->domainEventEntities);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->domainEventEntities);
    }
}