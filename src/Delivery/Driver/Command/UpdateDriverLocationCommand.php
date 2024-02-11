<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver;
use App\Shared\Type\Command;
use App\Shared\Type\Location;

final readonly class UpdateDriverLocationCommand implements Command
{
    public function __construct(
        private Driver\Id $driverId,
        private Location $location,
    ) {
    }

    public function getDriverId(): Driver\Id
    {
        return $this->driverId;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }
}