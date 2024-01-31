<?php

declare(strict_types=1);

namespace Integration\Geo\DriverLocation\Command;

use App\Geo\DriverLocation\Command\AddDriverLocationCommand;
use App\Geo\DriverLocation\DriverId;
use App\Geo\Shared\Bus\CommandBus;
use App\Shared\Type\Location;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Test\Unit\Delivery\Driver\MockUuid;

class AddDriverLocationCommandTest extends KernelTestCase
{
    public function testAddDriverLocation(): void
    {
        $driverId = new DriverId(
            id: (new MockUuid())->generate(),
        );
        /** @var CommandBus $bus */
        $bus = $this->getContainer()->get('geo.driver_location.command.bus');
        $addDriverLocationCommand = new AddDriverLocationCommand(
            driverId: $driverId,
            location: new Location(latitude: 34.4543, longitude: 53.4353),
            receivedAt: new \DateTimeImmutable(),
        );
        $bus->handle($addDriverLocationCommand);
    }
}