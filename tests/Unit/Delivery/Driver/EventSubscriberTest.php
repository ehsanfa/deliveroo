<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Driver;

use App\Delivery\Driver;
use App\Delivery\Driver\Event\DriverAssigned;
use App\Delivery\Driver\Event\DriverReserved;
use App\Delivery\Shared\DomainEventEntity;
use App\Delivery\Shared\DomainEventEntityList;
use App\Delivery\Shared\DomainEventId;
use App\Delivery\Shared\EventStoreRepository;
use App\Delivery\Trip;
use App\Delivery\Shared\EventHandler\EventDispatcherImplementation;
use App\Delivery\Trip\Event\Handler\MarkTripAsAssignedWhenDriverReserved;
use App\Delivery\Trip\PersistingTripRepository;
use App\Shared\Type\CommandBus;
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
            ->willReturn(new DomainEventEntityList([
                new DomainEventEntity(
                    id: new DomainEventId(MockUuid::fromString('event-id')),
                    domainEvent: $domainEvent,
                )
            ]));

        $tripRepository = $this->createStub(PersistingTripRepository::class);
        $tripRepository->method('find')
            ->willReturn($trip);

        $tripCommandBus = $this->createStub(CommandBus::class);
        $updateTripWhenDriverAssignedHandler = new MarkTripAsAssignedWhenDriverReserved(
            $tripCommandBus,
        );

        $eventDispatcher = new EventDispatcherImplementation(
            [
                DriverReserved::class => [
                    $updateTripWhenDriverAssignedHandler,
                ]
            ]
        );
        $eventDispatcher->dispatch($domainEvent);
    }
}