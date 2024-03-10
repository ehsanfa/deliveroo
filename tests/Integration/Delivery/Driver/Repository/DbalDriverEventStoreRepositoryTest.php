<?php

declare(strict_types=1);

namespace Test\Integration\Delivery\Driver\Repository;

use App\Delivery\Driver;
use App\Delivery\Driver\Command\CreateDriverCommand;
use App\Delivery\Shared\EventStoreRepository;
use App\Delivery\Trip;
use App\Shared\Type\CommandBus;
use App\Shared\Type\Location;
use App\Shared\Type\QueryBus;
use App\Shared\Type\UuidGenerator;
use Test\Integration\Shared\TestWithCleanup;

class DbalDriverEventStoreRepositoryTest extends TestWithCleanup
{
    private EventStoreRepository $driverEventStoreRepository;
    private UuidGenerator $uuidGenerator;
    private CommandBus $tripCommandBus;
    private CommandBus $driverCommandBus;
    private Driver\Scorer $scorer;
    private QueryBus $driverQueryBus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->driverEventStoreRepository = $this->getContainer()->get('delivery.driver.repository.event_store');
        $this->uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
        $this->driverCommandBus = $this->getContainer()->get('delivery.driver.command.bus');
        $this->tripCommandBus = $this->getContainer()->get('delivery.trip.command.bus');
        $this->scorer = $this->getContainer()->get('delivery.scout.scorer.rookie');
        $this->driverQueryBus = $this->getContainer()->get('delivery.driver.query.bus');
    }

    private function createDriver(): void
    {
        $createDriver = new CreateDriverCommand(
            id: new Driver\Id($this->uuidGenerator->generate())
        );
        $this->driverCommandBus->handle($createDriver);
    }

    public function testGetEventsByIdentifier(): void
    {
        $this->createDriver();
        $events = $this->driverEventStoreRepository
            ->getEventsByIdentifier(Driver\Event\DriverCreated::class);

        self::assertEquals(
            expected: 1,
            actual: $events->count(),
        );
    }

    public function testDelete(): void
    {
        $this->createDriver();
        $this->createDriver();

        $eventIds = [];
        $events = $this->driverEventStoreRepository
            ->getEventsByIdentifier(Driver\Event\DriverCreated::class);
        foreach ($events as $event) {
            $eventIds[] = $event->getId();
        }

        $this->driverEventStoreRepository
            ->delete($eventIds);

        $events = $this->driverEventStoreRepository
            ->getEventsByIdentifier(Driver\Event\DriverCreated::class);

        self::assertEquals(
            expected: 0,
            actual: $events->count(),
        );
    }

    public function testGetEventById(): void
    {
        $this->createDriver();
        $events = $this->driverEventStoreRepository
            ->getEventsByIdentifier(Driver\Event\DriverCreated::class);
        $eventId = null;
        foreach ($events as $event) {
            $eventId = $event->getId();
        }

        $event = $this->driverEventStoreRepository
            ->getEventById($eventId);

        self::assertInstanceOf(
            expected: Driver\Event\DriverCreated::class,
            actual: $event
        );
    }

    public function testGetOldestEventIds(): void
    {
        $driverId = new Driver\Id($this->uuidGenerator->generate());
        $createDriver = new CreateDriverCommand(
            id: $driverId,
        );
        $this->driverCommandBus->handle($createDriver);

        $tripId = new Trip\Id($this->uuidGenerator->generate());
        $tripSource = new Location(latitude: 45.324, longitude: 54.234);
        $createTrip = new Trip\Command\CreateTripCommand(
            id: $tripId,
            source: $tripSource,
            destination: new Location(latitude: 45.324, longitude: 54.234),
        );
        $this->tripCommandBus->handle($createTrip);

        $updateDriverLocationCommand = new Driver\Command\UpdateDriverLocationCommand(
            driverId: $driverId,
            location: $tripSource,
        );
        $this->driverCommandBus->handle($updateDriverLocationCommand);

        $markDriverFreeCommand = new Driver\Command\MarkDriverFreeCommand($driverId);
        $this->driverCommandBus->handle($markDriverFreeCommand);

        $driver = $this->driverQueryBus->handle(new Driver\Query\GetDriverQuery(
            driverId: $driverId,
        ));

        $scoutDriverCommand = new Driver\Command\ScoutDriverCommand(
            tripId: $tripId,
            scorer: $this->scorer,
        );
        $this->driverCommandBus->handle($scoutDriverCommand);

        self::assertEquals(
            expected: 1,
            actual: $this->driverEventStoreRepository->getEventsByIdentifier(Driver\Event\DriverCreated::class)->count(),
        );
        self::assertEquals(
            expected: 1,
            actual: $this->driverEventStoreRepository->getEventsByIdentifier(Driver\Event\DriverReserved::class)->count(),
        );
    }
}