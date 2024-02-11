<?php

declare(strict_types=1);

namespace App\Delivery\Shared;

use App\Shared\Type\DomainEvent;

final readonly class DomainEventEntity
{
    public function __construct(
        private DomainEventId $id,
        private DomainEvent $domainEvent,
    ) {
    }

    public function getId(): DomainEventId
    {
        return $this->id;
    }

    public function getDomainEvent(): DomainEvent
    {
        return $this->domainEvent;
    }
}