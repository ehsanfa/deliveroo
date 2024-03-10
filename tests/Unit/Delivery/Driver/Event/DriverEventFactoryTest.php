<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Driver\Event;

use App\Delivery\Driver\Event\DriverAssigned;
use App\Delivery\Driver\Event\DriverCreated;
use App\Delivery\Driver\Event\DriverReserved;
use App\Delivery\Driver\Event\EventFactory;
use App\Shared\Type\UuidValidator;
use PHPUnit\Framework\TestCase;

class DriverEventFactoryTest extends TestCase
{
    public function testCreateDriverAssignedEvent(): void
    {
        $tripId = "c6b91e0d-44ff-4790-9a0f-9ec2753fab3c";
        $driverId = "1fe442fe-d39b-4442-a5b0-0c3c98b7102c";

        $uuidValidator = $this->createMock(UuidValidator::class);
        $uuidValidator->method('isValid')
            ->willReturn(true);

        $factory = new EventFactory($uuidValidator);
        $event = $factory->getDomainEvent([
            'event' => DriverAssigned::class,
            'driver_id' => $driverId,
            'payload' => json_encode([
                'trip_id' => $tripId,
            ]),
        ]);

        self::assertInstanceOf(
            expected: DriverAssigned::class,
            actual: $event,
        );
        self::assertEquals(
            expected: $driverId,
            actual: $event->getDriverId()->toString(),
        );
        self::assertEquals(
            expected: $tripId,
            actual: $event->getTripId()->toString()
        );
    }

    public function testCreateDriverCreatedEvent(): void
    {
        $driverId = "1fe442fe-d39b-4442-a5b0-0c3c98b7102c";

        $uuidValidator = $this->createMock(UuidValidator::class);
        $uuidValidator->method('isValid')
            ->willReturn(true);

        $factory = new EventFactory($uuidValidator);
        $driverCreatedEvent = $factory->getDomainEvent([
            'event' => DriverCreated::class,
            'driver_id' => $driverId,
        ]);

        self::assertInstanceOf(
            expected: DriverCreated::class,
            actual: $driverCreatedEvent,
        );
        self::assertEquals(
            expected: $driverId,
            actual: $driverCreatedEvent->getDriverId()->toString(),
        );
    }

    public function testCreateDriverReservedEvent(): void
    {
        $tripId = "c6b91e0d-44ff-4790-9a0f-9ec2753fab3c";
        $driverId = "1fe442fe-d39b-4442-a5b0-0c3c98b7102c";

        $uuidValidator = $this->createMock(UuidValidator::class);
        $uuidValidator->method('isValid')
            ->willReturn(true);

        $factory = new EventFactory($uuidValidator);
        $driverCreatedEvent = $factory->getDomainEvent([
            'event' => DriverReserved::class,
            'driver_id' => $driverId,
            'payload' => json_encode([
                'trip_id' => $tripId,
            ]),
        ]);

        self::assertInstanceOf(
            expected: DriverReserved::class,
            actual: $driverCreatedEvent,
        );
        self::assertEquals(
            expected: $driverId,
            actual: $driverCreatedEvent->getDriverId()->toString(),
        );
        self::assertEquals(
            expected: $tripId,
            actual: $driverCreatedEvent->getTripId()->toString(),
        );
    }
}