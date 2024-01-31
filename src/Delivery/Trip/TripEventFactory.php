<?php

declare(strict_types=1);

namespace App\Delivery\Trip;

use App\Delivery\Trip\Event\TripCreated;
use App\Delivery\Trip\Event\TripDelivered;
use App\Delivery\Trip\Event\TripMarkedAsInProgress;

interface TripEventFactory
{
    public function createTripCreatedEvent(mixed $tripId): TripCreated;

    public function createTripDeliveredEvent(mixed $tripId): TripDelivered;

    public function createTripMarkedAsInProgressEvent(
        mixed $tripId,
        mixed $driverId,
    ): TripMarkedAsInProgress;
}