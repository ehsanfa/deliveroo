<?php

declare(strict_types=1);

namespace Test\Integration\Delivery\Trip\Event;

use App\Delivery\Driver;
use App\Delivery\Trip;
use App\Shared\Type\CommandBus;
use App\Shared\Type\EventDispatcher;
use App\Shared\Type\Location;
use App\Shared\Type\QueryBus;
use App\Shared\Type\UuidGenerator;
use Test\Integration\Shared\TestWithCleanup;

class TripMarkedAsInProgressTest extends TestWithCleanup
{
    private EventDispatcher $tripEventDispatcher;
    private CommandBus $driverCommandBus;
    private CommandBus $tripCommandBus;
    private UuidGenerator $uuidGenerator;
    private Driver\DriverRepository $driverRepository;
    private QueryBus $driverQueryBus;
    private EventDispatcher $driverEventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->driverEventDispatcher = $this->getContainer()->get('delivery.driver.event.dispatcher');
        $this->tripEventDispatcher = $this->getContainer()->get('delivery.trip.event.dispatcher');
        $this->driverCommandBus = $this->getContainer()->get('delivery.driver.command.bus');
        $this->tripCommandBus = $this->getContainer()->get('delivery.trip.command.bus');
        $this->uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
        $this->driverRepository = $this->getContainer()->get(Driver\DriverRepository::class);
        $this->driverQueryBus = $this->getContainer()->get('delivery.driver.query.bus');
    }

    public function testEvent(): void
    {
        $driverId = new Driver\Id($this->uuidGenerator->generate());
        $driverId2 = new Driver\Id($this->uuidGenerator->generate());
        $driverId3 = new Driver\Id($this->uuidGenerator->generate());
        $tripId = new Trip\Id($this->uuidGenerator->generate());
        $source = new Location(latitude: 34.5324, longitude: 53.3425);

        $this->driverCommandBus->handle(new Driver\Command\CreateDriverCommand(
            id: $driverId,
        ));
        $this->driverCommandBus->handle(new Driver\Command\CreateDriverCommand(
            id: $driverId2,
        ));
        $this->driverCommandBus->handle(new Driver\Command\CreateDriverCommand(
            id: $driverId3,
        ));

        $this->tripCommandBus->handle(new Trip\Command\CreateTripCommand(
            id: $tripId,
            source: $source,
            destination: new Location(latitude: 34.5324, longitude: 53.3425),
        ));

        /** @var Driver\Driver $driver */
        $driver = $this->driverQueryBus->handle(new Driver\Query\GetDriverQuery($driverId));
        $driver2 = $this->driverQueryBus->handle(new Driver\Query\GetDriverQuery($driverId2));
        $driver3 = $this->driverQueryBus->handle(new Driver\Query\GetDriverQuery($driverId3));
        $driver->markAsFree();
        $driver2->markAsFree();
        $driver3->markAsFree();
        $driver->reserveFor($tripId);
        $driver2->reserveFor($tripId);
        $driver3->reserveFor($tripId);

        $this->driverRepository->update($driver);
        $this->driverRepository->update($driver2);
        $this->driverRepository->update($driver3);

        $this->driverEventDispatcher->dispatch(new Driver\Event\DriverReserved(
            $driverId, $tripId
        ));
        $this->driverEventDispatcher->dispatch(new Driver\Event\DriverReserved(
            $driverId2, $tripId
        ));
        $this->driverEventDispatcher->dispatch(new Driver\Event\DriverReserved(
            $driverId3, $tripId
        ));

        $event = new Trip\Event\TripMarkedAsInProgress($tripId, $driverId);
        $this->tripEventDispatcher->dispatch($event);

        self::assertEquals(
            expected: $this->driverRepository->find($driverId)?->getStatus(),
            actual: Driver\Status::Busy,
        );
        self::assertEquals(
            expected: $this->driverRepository->find($driverId2)?->getStatus(),
            actual: Driver\Status::Free,
        );
        self::assertEquals(
            expected: $this->driverRepository->find($driverId3)?->getStatus(),
            actual: Driver\Status::Free,
        );
    }
}