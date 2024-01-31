<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Event\Handler;

use App\Delivery\Driver\Event\DriverAssigned;
use App\Delivery\Driver\Event\DriverReserved;
use App\Delivery\Trip\Exception\TripIsNotOpenException;
use App\Delivery\Trip\PersistingTripRepository;

final readonly class MarkTripAsAssignedWhenDriverReserved
{
    public function __construct(
        private PersistingTripRepository $tripRepository,
    ) {
    }

    /**
     * @throws TripIsNotOpenException
     */
    public function handle(DriverReserved $domainEvent): void
    {
        $trip = $this->tripRepository->find(
            id: $domainEvent->getTripId(),
        );

        if (null === $trip) {
            return;
        }

        try {
            $trip->markAsAssigned($domainEvent->getDriverId());
        } catch (TripIsNotOpenException) {
            return;
        }

        if (!$trip->isDirty()) {
            return;
        }
        $this->tripRepository->update($trip);
    }
}