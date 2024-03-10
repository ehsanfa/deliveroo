<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Event;

use App\Delivery\Driver;
use App\Delivery\Trip;
use App\Shared\Type\ClassNameAsIdentifier;
use App\Shared\Type\DomainEvent;
use App\Shared\Type\DomainEventWithPayload;
use App\Shared\Type\Id;

readonly class TripMarkedAsInProgress implements DomainEvent, DomainEventWithPayload
{
    use ClassNameAsIdentifier;

    public function __construct(
        private Trip\Id $tripId,
        private Driver\Id $driverId,
    ) {
    }

    #[\Override]
    public function getAggregateRootId(): Id
    {
        return $this->tripId;
    }

    public function tripId(): Trip\Id
    {
        return $this->tripId;
    }

    public function driverId(): Driver\Id
    {
        return $this->driverId;
    }

    public function getPayload(): array
    {
        return [
            'driver_id' => $this->driverId()->toString(),
        ];
    }
}