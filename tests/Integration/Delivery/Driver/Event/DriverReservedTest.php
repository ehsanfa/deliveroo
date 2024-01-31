<?php

declare(strict_types=1);

namespace Test\Integration\Delivery\Driver\Event;

use App\Delivery\Driver;
use App\Delivery\Driver\Command\CreateDriverCommand;
use App\Delivery\Driver\Event\DriverReserved;
use App\Delivery\Trip;
use App\Delivery\Trip\Command\CreateTripCommand;
use App\Shared\Type\CommandBus;
use App\Shared\Type\EventDispatcher;
use App\Shared\Type\Location;
use App\Shared\Type\UuidGenerator;
use Test\Integration\Shared\TestWithCleanup;

class DriverReservedTest extends TestWithCleanup
{
    private EventDispatcher $eventDispatcher;
    private CommandBus $driverCommandBus;
    private CommandBus $tripCommandBus;
    private UuidGenerator $uuidGenerator;
    private Trip\PersistingTripRepository $tripRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventDispatcher = $this->getContainer()->get('delivery.driver.event.dispatcher');
        $this->driverCommandBus = $this->getContainer()->get('delivery.driver.command.bus');
        $this->tripCommandBus = $this->getContainer()->get('delivery.trip.command.bus');
        $this->uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
        $this->tripRepository = $this->getContainer()->get(Trip\PersistingTripRepository::class);
    }

    public function testEvent(): void
    {
        $driverId = new Driver\Id($this->uuidGenerator->generate());
        $createDriverCommand = new CreateDriverCommand($driverId);
        $this->driverCommandBus->handle($createDriverCommand);

        $tripId = new Trip\Id($this->uuidGenerator->generate());
        $createTripCommand = new CreateTripCommand(
            id: $tripId,
            source: new Location(latitude: 34.5422, longitude: 53.4235),
            destination: new Location(latitude: 34.5422, longitude: 53.4235),
        );
        $this->tripCommandBus->handle($createTripCommand);

        $driverReserved = new DriverReserved(
            driverId: $driverId,
            tripId: $tripId,
        );

        $this->eventDispatcher->dispatch($driverReserved);

        $trip = $this->tripRepository->find($tripId);
        self::assertEquals(
            expected: Trip\Status::InProgress,
            actual: $trip->getStatus(),
        );
        self::assertEquals(
            expected: $driverId->toString(),
            actual: $trip->getDriverId()?->toString(),
        );
    }
}