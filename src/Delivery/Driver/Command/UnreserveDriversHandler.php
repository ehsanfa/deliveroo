<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotReserved;

final readonly class UnreserveDriversHandler
{
    public function __construct(
        private DriverRepository $driverRepository,
    ) {
    }

    public function __invoke(UnreserveDriversCommand $command): void
    {
        foreach ($this->driverRepository->getReservedDriversForTrip($command->tripId()) as $driver) {
            try {
                $driver->unreserveFor($command->tripId());
                $this->driverRepository->update($driver);
            } catch (DriverNotReserved) {
                continue;
            }
        }
    }
}