<?php

declare(strict_types=1);

namespace Test\Unit\Geo\DriverLocation\Command;

use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFoundException;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Status;
use App\Shared\Type\Location;
use App\Geo\DriverLocation\Command\AddDriverLocationCommand;
use App\Geo\DriverLocation\Command\AddDriverLocationHandler;
use App\Geo\DriverLocation\PersistingDriverLocationRepository;
use PHPUnit\Framework\TestCase;
use Test\Unit\Delivery\Driver\MockUuid;

class AddDriverLocationCommandTest extends TestCase
{
    public function testSuccessfulAddLocation(): void
    {
        $driverLocationRepository = $this->createMock(PersistingDriverLocationRepository::class);
        $driverLocationRepository->expects(self::once())
            ->method('saveLocation');

        $command = new AddDriverLocationCommand(
            driverId: new Id((new MockUuid())->generate()),
            location: new Location(latitude: 35.23123, longitude: 53.123),
            receivedAt: new \DateTimeImmutable(),
        );

        (new AddDriverLocationHandler(
            driverLocationRepository: $driverLocationRepository,
        ))->__invoke($command);
    }
}