<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFoundException;

readonly class MarkDriverBusyHandler
{
    public function __construct(
        private DriverRepository $driverRepository,
    ) {
    }

    /**
     * @throws DriverNotFoundException
     */
    public function __invoke(MarkDriverBusyCommand $command): void
    {
        $driver = $this->driverRepository->find($command->getDriverId());
        if (null === $driver) {
            throw new DriverNotFoundException();
        }
        $driver->markAsBusy($command->getTripId());
        $this->driverRepository->update($driver);
    }
}