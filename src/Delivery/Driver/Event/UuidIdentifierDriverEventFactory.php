<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Event;

use App\Delivery\Driver;
use App\Delivery\Driver\DriverEventFactory;
use App\Delivery\Trip;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidValidator;

final readonly class UuidIdentifierDriverEventFactory implements DriverEventFactory
{
    public function __construct(
        private UuidValidator $uuidValidator,
    ) {
    }

    public function createDriverAssignedEvent(mixed $driverId, mixed $tripId): DriverAssigned
    {
        $driverId = new Driver\Id(Uuid::fromString($driverId, $this->uuidValidator));
        $tripId = new Trip\Id(Uuid::fromString($tripId, $this->uuidValidator));

        return new DriverAssigned($driverId, $tripId);
    }

    public function createDriverCreatedEvent(mixed $driverId): DriverCreated
    {
        $driverId = new Driver\Id(Uuid::fromString($driverId, $this->uuidValidator));

        return new DriverCreated($driverId);
    }

    public function createDriverReservedEvent(mixed $driverId, mixed $tripId): DriverReserved
    {
        $driverId = new Driver\Id(Uuid::fromString($driverId, $this->uuidValidator));
        $tripId = new Trip\Id(Uuid::fromString($tripId, $this->uuidValidator));

        return new DriverReserved($driverId, $tripId);
    }
}