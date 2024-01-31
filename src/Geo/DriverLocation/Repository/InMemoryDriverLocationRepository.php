<?php

declare(strict_types=1);

namespace App\Geo\DriverLocation\Repository;

use App\Delivery\Driver\Status;
use App\Shared\Type\Location;
use App\Geo\DriverLocation\DriverLocation;
use App\Geo\DriverLocation\DriverLocationList;
use App\Geo\DriverLocation\PersistingDriverLocationRepository;
use App\Geo\DriverLocation\ReadOnlyDriverLocationRepository;
use App\Shared\Distance\DistanceCalculator;

class InMemoryDriverLocationRepository implements PersistingDriverLocationRepository, ReadOnlyDriverLocationRepository
{
    /**
     * @var array<string, DriverLocation>
     */
    private array $driverLocations = [];

    public function __construct(
        private readonly DistanceCalculator $distanceCalculator,
    ) {
    }

    public function saveLocation(DriverLocation $driverLocation): void
    {
        $this->driverLocations[$driverLocation->getDriverId()->toString()] = $driverLocation;
    }

    public function getFreeDriversAround(Location $location, Distance $distance): DriverLocationList
    {
        $pickedDrivers = [];
        foreach ($this->driverLocations as $driverLocation) {
            if ($driverLocation->getDriverStatus() === Status::Free
                && $driverLocation->isAround(
                    target: $location,
                    maxDistance: $distance,
                    distanceCalculator: $this->distanceCalculator
                )
            ) {
                $pickedDrivers[] = $driverLocation->getId();
            }
        }

        return new DriverLocationList($pickedDrivers);
    }
}