<?php

declare(strict_types=1);

namespace Integration\Delivery\Trip\Repository;

use App\Delivery\Shared\EventStoreRepository;
use App\Delivery\Trip\Command\CreateTripCommand;
use App\Delivery\Trip\PersistingTripRepository;
use App\Delivery\Trip;
use App\Shared\Type\CommandBus;
use App\Shared\Type\Location;
use App\Shared\Type\UuidGenerator;
use Test\Integration\Shared\TestWithCleanup;

class DbalTripEventRepositoryTest extends TestWithCleanup
{
    private EventStoreRepository $tripEventStoreRepository;
    private PersistingTripRepository $tripRepository;
    private CommandBus $tripCommandBus;
    private UuidGenerator $uuidGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tripEventStoreRepository = $this->getContainer()->get('delivery.trip.repository.event_store');
        $this->tripCommandBus = $this->getContainer()->get('delivery.trip.command.bus');
        $this->uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
    }

    private function createTrip(): void
    {
        $this->tripCommandBus->handle(new CreateTripCommand(
            new Trip\Id($this->uuidGenerator->generate()),
            new Location(34.423, 43.324),
            new Location(34.423, 43.324),
        ));
    }

    public function testCreate(): void
    {
        $this->createTrip();
        $events = $this->tripEventStoreRepository
            ->getEventsByIdentifier(Trip\Event\TripCreated::class);

        self::assertEquals(
            expected: 1,
            actual: $events->count(),
        );
    }
}