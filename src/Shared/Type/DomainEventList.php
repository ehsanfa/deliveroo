<?php

declare(strict_types=1);

namespace App\Shared\Type;

use Traversable;

/**
 * @implements \IteratorAggregate<DomainEvent>
 */
readonly class DomainEventList implements \IteratorAggregate
{
    /**
     * @param DomainEvent[] $domainEvents
     */
    public function __construct(
        private array $domainEvents,
    ) {
    }

    public function append(DomainEvent $domainEvent): DomainEventList
    {
        $domainEvents = $this->domainEvents;
        $domainEvents[] = $domainEvent;
        return new DomainEventList($domainEvents);
    }

    public function count(): int
    {
        return count($this->domainEvents);
    }

    public function getIterator(): Traversable
    {
       return new \ArrayIterator($this->domainEvents);
    }
}