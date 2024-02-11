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
    private EventDispatcher $eventDispatcher;
    private CommandBus $driverCommandBus;
    private CommandBus $tripCommandBus;
    private UuidGenerator $uuidGenerator;
    private Driver\DriverRepository $driverRepository;
    private QueryBus $driverQueryBus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventDispatcher = $this->getContainer()->get('delivery.trip.event.dispatcher');
        $this->driverCommandBus = $this->getContainer()->get('delivery.driver.command.bus');
        $this->tripCommandBus = $this->getContainer()->get('delivery.trip.command.bus');
        $this->uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
        $this->driverRepository = $this->getContainer()->get(Driver\DriverRepository::class);
        $this->driverQueryBus = $this->getContainer()->get('delivery.driver.query.bus');
    }

    public function testEvent(): void
    {
        $driverId = new Driver\Id($this->uuidGenerator->generate());
        $tripId = new Trip\Id($this->uuidGenerator->generate());
        $source = new Location(latitude: 34.5324, longitude: 53.3425);

        $this->driverCommandBus->handle(new Driver\Command\CreateDriverCommand(
            id: $driverId,
        ));

        $this->tripCommandBus->handle(new Trip\Command\CreateTripCommand(
            id: $tripId,
            source: $source,
            destination: new Location(latitude: 34.5324, longitude: 53.3425),
        ));

        /** @var Driver\Driver $driver */
        $driver = $this->driverQueryBus->handle(new Driver\Query\GetDriverQuery($driverId));
        $driver->markAsFree();
        $driver->reserveFor($tripId);

        $this->driverRepository->update($driver);

        $event = new Trip\Event\TripMarkedAsInProgress($tripId, $driverId);
        $this->eventDispatcher->dispatch($event);

        self::assertEquals(
            expected: $this->driverRepository->find($driverId)?->getStatus(),
            actual: Driver\Status::Busy,
        );
    }
}