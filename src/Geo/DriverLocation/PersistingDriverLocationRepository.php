<?php

declare(strict_types=1);

namespace App\Geo\DriverLocation;

interface PersistingDriverLocationRepository
{
    public function saveLocation(DriverLocation $driverLocation): void;
}