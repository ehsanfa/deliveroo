<?php

declare(strict_types=1);

namespace Test\Unit\Geo\DriverLocation;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Status;
use App\Shared\Type\Location;
use App\Geo\DriverLocation\DriverLocation;
use App\Shared\Distance\Distance;
use App\Shared\Distance\DistanceCalculator;
use App\Shared\Distance\Unit;
use PHPUnit\Framework\TestCase;
use Test\Unit\Delivery\Driver\MockUuid;

class DriverLocationTest extends TestCase
{
    public function testCreate(): void
    {
        $driverLocation = DriverLocation::create(
            driverId: new Id(MockUuid::fromString('driver')),
            location: new Location(latitude: 44.342134, longitude: 34.34225),
            receivedAt: new \DateTimeImmutable(),
        );

        self::assertTrue($driverLocation->getIsFresh());
    }
}