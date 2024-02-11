<?php

declare(strict_types=1);

namespace App\Shared\Type;

trait DomainEvents
{
    private ?DomainEventList $domainEvents = null;

    public function flushDomainEvents(): void
    {
        $this->domainEvents = new DomainEventList([]);
    }

    public function getDomainEvents(): DomainEventList
    {
        return $this->domainEvents ?: new DomainEventList([]);
    }

    public function addDomainEvent(DomainEvent $domainEvent): void
    {
        $this->domainEvents = $this->getDomainEvents()->append($domainEvent);
    }
}