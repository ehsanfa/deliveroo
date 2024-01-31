<?php

declare(strict_types=1);

namespace App\Geo\DriverLocation\Command;

use App\Geo\DriverLocation\DriverId;
use App\Shared\Type\Command;
use App\Shared\Type\Location;

readonly class AddDriverLocationCommand implements Command
{
    public function __construct(
        private DriverId $driverId,
        private Location $location,
        private \DateTimeImmutable $receivedAt,
    ) {
    }

    public function getDriverId(): DriverId
    {
        return $this->driverId;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getReceivedAt(): \DateTimeImmutable
    {
        return $this->receivedAt;
    }
}