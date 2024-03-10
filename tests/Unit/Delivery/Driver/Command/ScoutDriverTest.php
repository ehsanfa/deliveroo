<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Driver\Command;

use App\Delivery\Driver\Command\ScoutDriverCommand;
use App\Delivery\Driver\Command\ScoutDriverHandler;
use App\Delivery\Driver\Driver;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Event\DriverReserved;
use App\Delivery\Driver\Exception\NoDriverAvailableException;
use App\Delivery\Driver\Id as DriverId;
use App\Delivery\Driver\Scorer;
use App\Delivery\Driver\Status as DriverStatus;
use App\Delivery\Driver\DriverList;
use App\Delivery\Shared\Configuration\ConfigurationManager;
use App\Delivery\Trip\Id as TripId;
use App\Delivery\Trip\Status as TripStatus;
use App\Delivery\Trip\Trip;
use App\Shared\Distance\Distance;
use App\Shared\Distance\Unit;
use App\Shared\Type\Location;
use App\Shared\Type\QueryBus;
use PHPUnit\Framework\TestCase;
use Test\Unit\Delivery\Driver\MockUuid;

class ScoutDriverTest extends TestCase
{
    public function testAddsDomainEventWhenSuccessfullyReserved(): void
    {
        $driver2 = Driver::create(
            id: new DriverId(MockUuid::fromString('driver-2')),
            status: DriverStatus::Free,
        );

        $trip = Trip::create(
            id: new TripId(MockUuid::fromString('trip-1')),
            status: TripStatus::Open,
            source: new Location(
                latitude: 34.2123,
                longitude: 52.24531,
            ),
            destination: new Location(
                latitude: 34.2023,
                longitude: 52.23431,
            ),
        );

        $driverRepository = $this->createMock(DriverRepository::class);
        $driverRepository->expects(self::once())
            ->method('update');


        $scorer = $this->createMock(Scorer::class);
        $scorer->method('score')
            ->willReturn(new DriverList([
                $driver2,
            ]));

        $scoutDriverCommand = new ScoutDriverCommand(
            tripId: $trip->getId(),
            scorer: $scorer,
        );

        $configurationManager = $this->createStub(ConfigurationManager::class);
        $configurationManager
            ->method('scoutDriverMaxDistanceBikersAround')
            ->willReturn(new Distance(5, Unit::Kilometer));
        $driverQueryBus = $this->createStub(QueryBus::class);
        $driverQueryBus->method('handle')
            ->willReturn(new DriverList([
                $driver2,
            ]));
        $tripQueryBus = $this->createStub(QueryBus::class);
        $tripQueryBus->method('handle')
            ->willReturn($trip);

        (new ScoutDriverHandler(
            driverRepository: $driverRepository,
            configurationManager: $configurationManager,
            driverQueryBus: $driverQueryBus,
            tripQueryBus: $tripQueryBus,
        ))->__invoke($scoutDriverCommand);

        $detectedEvent = null;
        foreach ($driver2->getDomainEvents() as $domainEvent) {
            if ($domainEvent instanceof DriverReserved) {
                $detectedEvent = $domainEvent;
            }
        }
        self::assertNotNull($detectedEvent);
        self::assertEquals(
            expected: 'driver-2',
            actual: $detectedEvent->getDriverId()->toString(),
        );
    }

    public function testThrowsExceptionWhenThereAreNoDriversAround(): void
    {
        self::expectException(NoDriverAvailableException::class);

        $driverRepository = $this->createMock(DriverRepository::class);
        $driverRepository->expects(self::never())
            ->method('update');

        $scorer = $this->createMock(Scorer::class);

        $trip = Trip::create(
            id: new TripId(MockUuid::fromString('trip-1')),
            status: TripStatus::Open,
            source: new Location(
                latitude: 34.2123,
                longitude: 52.24531,
            ),
            destination: new Location(
                latitude: 34.2023,
                longitude: 52.23431,
            ),
        );

        $scoutDriverCommand = new ScoutDriverCommand(
            tripId: $trip->getId(),
            scorer: $scorer,
        );

        $configurationManager = $this->createStub(ConfigurationManager::class);
        $configurationManager
            ->method('scoutDriverMaxDistanceBikersAround')
            ->willReturn(new Distance(5, Unit::Kilometer));
        $driverQueryBus = $this->createStub(QueryBus::class);
        $driverQueryBus->method('handle')
            ->willReturn(new DriverList([]));
        $tripQueryBus = $this->createStub(QueryBus::class);
        $tripQueryBus->method('handle')
            ->willReturn($trip);

        (new ScoutDriverHandler(
            driverRepository: $driverRepository,
            configurationManager: $configurationManager,
            driverQueryBus: $driverQueryBus,
            tripQueryBus: $tripQueryBus,
        ))->__invoke($scoutDriverCommand);
    }
}