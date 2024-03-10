<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Command;

use App\Delivery\Driver\DriverRepository;
use App\Delivery\Trip\Exception\NoReservedDriverFound;
use App\Delivery\Trip\Exception\TripIsNotOpenException;
use App\Delivery\Trip\PersistingTripRepository;
use App\Shared\Type\HandlerNotFoundException;

final readonly class RecruitDriverHandler
{
    public function __construct(
        private DriverRepository $driverRepository,
        private PersistingTripRepository $tripRepository,
    ) {
    }

    /**
     * @throws NoReservedDriverFound
     * @throws HandlerNotFoundException
     */
    public function __invoke(RecruitDriverCommand $command): void
    {
        $tripId = $command->tripId();

        $reservedDrivers = $this->driverRepository->getReservedDriversForTrip($tripId);

        if ($reservedDrivers->isEmpty()) {
            return;
//            throw new NoReservedDriverFound();
        }

        $trip = $this->tripRepository->find($tripId);

        try {
            $trip->markAsAssigned($reservedDrivers->first()->getId());
        } catch (TripIsNotOpenException) {
        }
        $this->tripRepository->update($trip);
    }
}