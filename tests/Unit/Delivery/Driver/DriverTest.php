<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Driver;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\Event\DriverReserved;
use App\Delivery\Driver\Exception\DriverNotFreeException;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Status;
use App\Delivery\Driver\Status as DriverStatus;
use App\Delivery\Trip\Id as TripId;
use App\Delivery\Trip\ReadOnlyTripRepository;
use App\Delivery\Trip\Status as TripStatus;
use App\Delivery\Trip\Trip;
use App\Delivery\Driver\Id as DriverId;
use App\Geo\DriverLocation\DriverLocation;
use App\Shared\Distance\Distance;
use App\Shared\Distance\DistanceCalculator;
use App\Shared\Distance\Unit;
use App\Shared\Type\Location;
use PHPUnit\Framework\TestCase;

class DriverTest extends TestCase
{
    /**
     * @throws DriverNotFreeException
     */
    public function testSuccessfulReserve(): void
    {
        $driver = Driver::create(
            id: new DriverId(
                id: (new MockUuid())->generate(),
            ),
            status: DriverStatus::Free,
        );

        $trip = Trip::create(
            id: new TripId (
                id: (new MockUuid())->generate(),
            ),
            status: TripStatus::Open,
            source: new Location(
                latitude: 25.2422,
                longitude: 52.2533,
            ),
            destination: new Location(
                latitude: 25.2412,
                longitude: 52.2523,
            ),
        );

        $driver->reserveFor($trip->getId());

        $detectedEvent = null;
        foreach ($driver->getDomainEvents() as $domainEvent) {
            if ($domainEvent instanceof DriverReserved) {
                $detectedEvent = $domainEvent;
            }
        }

        self::assertNotNull($detectedEvent);
    }

    public function testReservingThrowsExceptionWhenDriverIsNotFree(): void
    {
        self::expectException(DriverNotFreeException::class);

        $driver = Driver::create(
            id: new DriverId(
                id: (new MockUuid())->generate(),
            ),
            status: DriverStatus::Busy,
        );

        $trip = Trip::create(
            id: new TripId (
                id: (new MockUuid())->generate(),
            ),
            status: TripStatus::Open,
            source: new Location(
                latitude: 25.2422,
                longitude: 52.2533,
            ),
            destination: new Location(
                latitude: 25.2412,
                longitude: 52.2523,
            ),
        );

        $driver->reserveFor($trip->getId());
    }

    public function testDriverIsRookie(): void
    {
        $driver = Driver::create(
            id: new DriverId(
                id: (new MockUuid())->generate(),
            ),
            status: DriverStatus::Busy,
        );

        $tripRepository = $this->createMock(ReadOnlyTripRepository::class);
        $tripRepository->expects(self::once())
            ->method('driverHasDoneMoreTripsThan')
            ->willReturn(true);

        self::assertFalse($driver->isRookie($tripRepository));
    }

    public function testDriverIsAround(): void
    {
        $driver = Driver::create(
            id: new Id(MockUuid::fromString('driver')),
            status: Status::Free
        );

        $driver->updateLocation(
            location: new Location(latitude: 35.234234, longitude: 54.2313),
            locationUpdateAt: new \DateTimeImmutable(),
        );

        $distanceCalculatorStub = $this->createStub(DistanceCalculator::class);
        $distanceCalculatorStub->method('calculate')
            ->willReturn(new Distance(500, Unit::Meter));

        self::assertTrue($driver->isAround(
            target: new Location(latitude: 35.22423, longitude: 54.2312),
            maxDistance: new Distance(2, Unit::Kilometer),
            distanceCalculator: $distanceCalculatorStub
        ));
        self::assertTrue($driver->isAround(
            target: new Location(latitude: 35.22423, longitude: 54.2312),
            maxDistance: new Distance(500, Unit::Meter),
            distanceCalculator: $distanceCalculatorStub
        ));
        self::assertFalse($driver->isAround(
            target: new Location(latitude: 35.22423, longitude: 54.2312),
            maxDistance: new Distance(250, Unit::Meter),
            distanceCalculator: $distanceCalculatorStub
        ));

    }
}