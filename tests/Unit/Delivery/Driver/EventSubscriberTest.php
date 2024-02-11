<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Driver;

use App\Delivery\Driver;
use App\Delivery\Driver\Event\DriverAssigned;
use App\Delivery\Driver\Event\DriverCreated;
use App\Delivery\Shared\EventStoreRepository;
use App\Delivery\Trip;
use App\Delivery\Shared\EventHandler\EventDispatcherImplementation;
use App\Delivery\Trip\PersistingTripRepository;
use App\Shared\Type\DomainEventList;
use App\Shared\Type\Location;
use PHPUnit\Framework\TestCase;

class EventSubscriberTest extends TestCase
{
    public function testFetchingDomainEvents(): void
    {
        self::expectNotToPerformAssertions();

        $trip = Trip\Trip::create(
            id: new Trip\Id(MockUuid::fromString('trip')),
            status: Trip\Status::Open,
            source: new Location(35.342, 43.342),
            destination: new Location(35.342, 43.342),
        );
        $domainEvent = new DriverAssigned(
            driverId: new Driver\Id(MockUuid::fromString('driver')),
            tripId: new Trip\Id(MockUuid::fromString('trip'))
        );

        $eventStoreRepository = $this->createStub(EventStoreRepository::class);
        $eventStoreRepository->method('getEventsByIdentifier')
            ->willReturn(new DomainEventList([
                $domainEvent,
            ]));

        $tripRepository = $this->createStub(PersistingTripRepository::class);
        $tripRepository->method('find')
            ->willReturn($trip);

        $updateTripWhenDriverAssignedHandler = new UpdateTripWhenDriverReserved(
            tripRepository: $tripRepository,
        );

        $eventDispatcher = new EventDispatcherImplementation(
            [
                DriverCreated::class => [
                    $updateTripWhenDriverAssignedHandler,
                ]
            ]
        );
        $eventDispatcher->dispatch($domainEvent);
    }
}