<?php

declare(strict_types=1);

namespace App\Delivery\Trip;

use App\Delivery\Driver\Driver;

interface ReadOnlyTripRepository
{
    public function getOpenTrips(): TripList;

    public function driverHasDoneMoreTripsThan(Driver $driver, int $trips): bool;
}