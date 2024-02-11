<?php

declare(strict_types=1);

namespace App\Delivery\Driver;

use App\Delivery\Driver\Exception\DriverNotFoundException;
use App\Delivery\Driver\DriverList;
use App\Shared\Distance\Distance;
use App\Shared\Type\Location;

interface DriverRepository
{
    public function find(Id $driverId): ?Driver;

    public function create(Driver $driver): void;

    public function update(Driver $driver): void;

    public function delete(Driver $driver): void;

    /**
     * @throws DriverNotFoundException
     */
    public function getStatusById(Id $driverId): Status;

    public function getFreeDriversAround(
        Location $location,
        Distance $distance,
        \DateTimeImmutable $lastActivityUntil,
    ): DriverList;
}