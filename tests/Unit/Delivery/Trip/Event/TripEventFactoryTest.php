<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Trip\Event;

use App\Delivery\Trip\Event\EventFactory;
use App\Delivery\Trip\Event\TripCreated;
use App\Delivery\Trip\Event\TripDelivered;
use App\Delivery\Trip\Event\TripMarkedAsInProgress;
use App\Shared\Type\UuidValidator;
use PHPUnit\Framework\TestCase;

class TripEventFactoryTest extends TestCase
{
    public function testCreatesTripCreatedEvent(): void
    {
        $tripId = "c6b91e0d-44ff-4790-9a0f-9ec2753fab3c";

        $uuidValidator = $this->createMock(UuidValidator::class);
        $uuidValidator->method('isValid')
            ->willReturn(true);

        $factory = new EventFactory($uuidValidator);
        $tripCreatedEvent = $factory->getDomainEvent([
            'event' => TripCreated::class,
            'trip_id' => $tripId,
        ]);

        self::assertInstanceOf(
            expected: TripCreated::class,
            actual: $tripCreatedEvent,
        );
    }

    public function testCreatesTripDeliveredEvent(): void
    {
        $tripId = "c6b91e0d-44ff-4790-9a0f-9ec2753fab3c";

        $uuidValidator = $this->createMock(UuidValidator::class);
        $uuidValidator->method('isValid')
            ->willReturn(true);

        $factory = new EventFactory($uuidValidator);
        $tripCreatedEvent = $factory->getDomainEvent([
            'event' => TripDelivered::class,
            'trip_id' => $tripId,
        ]);

        self::assertInstanceOf(
            expected: TripDelivered::class,
            actual: $tripCreatedEvent,
        );
    }

    public function testCreatesTripMarkedAsInProgressEvent(): void
    {
        $tripId = "c6b91e0d-44ff-4790-9a0f-9ec2753fab3c";
        $driverId = "1fe442fe-d39b-4442-a5b0-0c3c98b7102c";

        $uuidValidator = $this->createMock(UuidValidator::class);
        $uuidValidator->method('isValid')
            ->willReturn(true);

        $factory = new EventFactory($uuidValidator);
        $tripCreatedEvent = $factory->getDomainEvent([
            'event' => TripMarkedAsInProgress::class,
            'trip_id' => $tripId,
            'payload' => json_encode([
                'driver_id' => $driverId,
            ]),
        ]);

        self::assertInstanceOf(
            expected: TripMarkedAsInProgress::class,
            actual: $tripCreatedEvent,
        );
    }
}