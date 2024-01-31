<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Repository;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFoundException;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Status;
use App\Delivery\DriverRate\DriverList;
use App\Shared\Distance\Distance;
use App\Shared\Distance\DistanceCalculator;
use App\Shared\Type\Location;

final class InMemoryDriverRepository implements DriverRepository
{
    /**
     * @var array<string, Driver>
     */
    private array $drivers = [];

    public function __construct(
        private readonly DistanceCalculator $distanceCalculator,
    ) {
    }

    public function create(Driver $driver): void
    {
        if (!$driver->isFresh()) {
            return;
        }

        $this->drivers[$driver->getId()->toString()] = $driver;
    }

    public function update(Driver $driver): void
    {
        if (!$driver->isDirty()) {
            return;
        }

        $this->drivers[$driver->getId()->toString()] = $driver;
    }

    public function delete(Driver $driver): void
    {
        unset($this->drivers[$driver->getId()->toString()]);
    }

    public function getStatusById(Id $driverId): Status
    {
        $foundDriver = $this->drivers[$driverId->toString()] ?? null;
        if (null === $foundDriver) {
            throw new DriverNotFoundException();
        }

        return $foundDriver->getStatus();
    }

    public function getFreeDriversAround(
        Location $location,
        Distance $distance,
        \DateTimeImmutable $lastActivityUntil
    ): DriverList {
        $pickedDrivers = [];
        foreach ($this->drivers as $driver) {
            if ($driver->getStatus() === Status::Free
                && $driver->isAround(
                    target: $location,
                    maxDistance: $distance,
                    distanceCalculator: $this->distanceCalculator
                )
            ) {
                $pickedDrivers[] = $driver;
            }
        }

        return new DriverList($pickedDrivers);
    }

    public function find(Id $driverId): ?Driver
    {
        return $this->drivers[$driverId->toString()];
    }
}