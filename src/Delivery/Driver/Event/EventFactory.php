<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Event;

use App\Delivery\Driver;
use App\Delivery\Shared\DomainEventFactory;
use App\Delivery\Shared\Exception\UndefinedDomainEventException;
use App\Delivery\Trip;
use App\Shared\Type\DomainEvent;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidValidator;
use JetBrains\PhpStorm\ArrayShape;

final readonly class EventFactory implements DomainEventFactory
{
    public function __construct(
        private UuidValidator $uuidValidator,
    ) {
    }

    private function createDriverAssignedEvent(mixed $driverId, array $payload): DriverAssigned
    {
        $driverId = new Driver\Id(Uuid::fromString($driverId, $this->uuidValidator));
        $tripId = new Trip\Id(Uuid::fromString($payload['trip_id'], $this->uuidValidator));

        return new DriverAssigned($driverId, $tripId);
    }

    private function createDriverCreatedEvent(mixed $driverId): DriverCreated
    {
        $driverId = new Driver\Id(Uuid::fromString($driverId, $this->uuidValidator));

        return new DriverCreated($driverId);
    }

    private function createDriverReservedEvent(mixed $driverId, array $payload): DriverReserved
    {
        $driverId = new Driver\Id(Uuid::fromString($driverId, $this->uuidValidator));
        $tripId = new Trip\Id(Uuid::fromString($payload['trip_id'], $this->uuidValidator));

        return new DriverReserved($driverId, $tripId);
    }

    /**
     * @inheritDoc
     */
    #[ArrayShape([
        'payload' => 'string',
        'data' => 'mixed'
    ])]
    public function getDomainEvent(array $data): DomainEvent
    {
        $payload = isset($data['payload']) ? json_decode($data['payload'], true) : null;
        return match ($data['event']) {
            DriverReserved::class => $this->createDriverReservedEvent($data['driver_id'], $payload),
            DriverAssigned::class => $this->createDriverAssignedEvent($data['driver_id'], $payload),
            DriverCreated::class => $this->createDriverCreatedEvent($data['driver_id']),
            default => throw new UndefinedDomainEventException(),
        };
    }
}