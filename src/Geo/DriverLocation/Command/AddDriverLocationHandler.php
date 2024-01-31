<?php

declare(strict_types=1);

namespace App\Geo\DriverLocation\Command;

use App\Geo\DriverLocation\DriverLocation;
use App\Geo\DriverLocation\PersistingDriverLocationRepository;

readonly class AddDriverLocationHandler
{
    public function __construct(
        private PersistingDriverLocationRepository $driverLocationRepository,
    ) {
    }

    public function __invoke(AddDriverLocationCommand $command): void
    {
        $driverLocation = DriverLocation::create(
            driverId: $command->getDriverId(),
            location: $command->getLocation(),
            receivedAt: $command->getReceivedAt(),
        );
        $this->driverLocationRepository->saveLocation($driverLocation);
    }
}