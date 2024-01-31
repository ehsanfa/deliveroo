<?php

declare(strict_types=1);

namespace Integration\Geo\DriverLocation\Repository;

use App\Geo\DriverLocation\DriverId;
use App\Geo\DriverLocation\DriverLocation;
use App\Geo\DriverLocation\PersistingDriverLocationRepository;
use App\Shared\Type\Location;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Test\Unit\Delivery\Driver\MockUuid;

class DbalDriverLocationRepositoryTest extends KernelTestCase
{
    public function testCreate(): void
    {
        $driverLocation = DriverLocation::create(
            driverId: new DriverId((new MockUuid())->generate()),
            location: new Location(latitude: 34.35435, longitude: 45.3424),
            receivedAt: new \DateTimeImmutable(),
        );
        $driverLocationRepository = $this->getContainer()->get(PersistingDriverLocationRepository::class);

    }
}