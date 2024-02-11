<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFoundException;

final readonly class UpdateDriverLocationHandler
{
    public function __construct(
        private DriverRepository $driverRepository,
    ) {
    }

    /**
     * @throws DriverNotFoundException
     */
    public function __invoke(UpdateDriverLocationCommand $command): void
    {
        $driver = $this->driverRepository->find($command->getDriverId());
        if (null === $driver) {
            throw new DriverNotFoundException();
        }
        $location = $command->getLocation();

        $driver->updateLocation($location, new \DateTimeImmutable());
        $this->driverRepository->update($driver);
    }
}