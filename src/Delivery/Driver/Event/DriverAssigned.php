<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Event;

use App\Delivery\Driver;
use App\Delivery\Trip;
use App\Shared\Type\ClassNameAsIdentifier;
use App\Shared\Type\DomainEvent;
use App\Shared\Type\DomainEventWithPayload;
use App\Shared\Type\Id;

readonly class DriverAssigned implements DomainEvent, DomainEventWithPayload
{
    use ClassNameAsIdentifier;

    public function __construct(
        private Driver\Id $driverId,
        private Trip\Id $tripId,
    ) {
    }

    public function getDriverId(): Driver\Id
    {
        return $this->driverId;
    }

    public function getTripId(): Trip\Id
    {
        return $this->tripId;
    }

    public function getAggregateRootId(): Id
    {
        return $this->driverId;
    }

    public function getPayload(): array
    {
        return [
            'trip_id' => $this->getTripId()->toString(),
        ];
    }
}