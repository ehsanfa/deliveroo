<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Event;

use App\Delivery\Driver;
use App\Delivery\Trip;
use App\Delivery\Trip\TripEventFactory;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidValidator;

final readonly class UuidIdentifierTripEventFactory implements TripEventFactory
{
    public function __construct(
        private UuidValidator $uuidValidator,
    ) {
    }

    #[\Override] public function createTripCreatedEvent(mixed $tripId): TripCreated
    {
        $tripId = new Trip\Id(Uuid::fromString($tripId, $this->uuidValidator));
        return new TripCreated($tripId);
    }

    #[\Override] public function createTripDeliveredEvent(mixed $tripId): TripDelivered
    {
        $tripId = new Trip\Id(Uuid::fromString($tripId, $this->uuidValidator));
        return new TripDelivered($tripId);
    }

    #[\Override] public function createTripMarkedAsInProgressEvent(
        mixed $tripId,
        mixed $driverId
    ): TripMarkedAsInProgress {
        $tripId = new Trip\Id(Uuid::fromString($tripId, $this->uuidValidator));
        $driverId = new Driver\Id(Uuid::fromString($driverId, $this->uuidValidator));
        return new TripMarkedAsInProgress($tripId, $driverId);
    }
}