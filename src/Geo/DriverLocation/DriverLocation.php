<?php

declare(strict_types=1);

namespace App\Geo\DriverLocation;

use App\Shared\Type\Location;

class DriverLocation
{
    private bool $isFresh = false;

    private function __construct(
        private readonly DriverId $driverId,
        private readonly Location $location,
        private readonly \DateTimeImmutable $receivedAt,
    ) {
    }

    public static function create(
        DriverId $driverId,
        Location $location,
        \DateTimeImmutable $receivedAt,
    ): DriverLocation {
         $driverLocation = new self(
            driverId: $driverId,
            location: $location,
            receivedAt: $receivedAt,
        );
         $driverLocation->isFresh = true;

         return $driverLocation;
    }

    public function getDriverId(): DriverId
    {
        return $this->driverId;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getIsFresh(): bool
    {
        return $this->isFresh;
    }

    public function getReceivedAt(): \DateTimeImmutable
    {
        return $this->receivedAt;
    }
}