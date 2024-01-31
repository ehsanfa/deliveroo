<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Event;

use App\Delivery\Trip;
use App\Shared\Type\AggregateRoot;
use App\Shared\Type\ClassNameAsIdentifier;
use App\Shared\Type\DomainEvent;
use App\Shared\Type\Id;

readonly class TripCreated implements DomainEvent
{
    use ClassNameAsIdentifier;

    public function __construct(
        private Trip\Id $tripId,
    ) {
    }

    public function getTripId(): Trip\Id
    {
        return $this->tripId;
    }

    #[\Override]
    public function getAggregateRootId(): Id
    {
        return $this->tripId;
    }
}