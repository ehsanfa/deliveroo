<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Event;

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

    private function createTripCreatedEvent(string $tripId): TripCreated
    {
        $tripId = new Trip\Id(Uuid::fromString($tripId, $this->uuidValidator));
        return new TripCreated($tripId);
    }

    private function createTripMarkedAsInProgressEvent(string $tripId, array $payload): TripMarkedAsInProgress
    {
        $tripId = new Trip\Id(Uuid::fromString($tripId, $this->uuidValidator));
        $driverId = new Driver\Id(Uuid::fromString($payload['driver_id'], $this->uuidValidator));
        return new TripMarkedAsInProgress($tripId, $driverId);
    }

    private function createTripDeliveredEvent(string $tripId): TripDelivered
    {
        $tripId = new Trip\Id(Uuid::fromString($tripId, $this->uuidValidator));
        return new TripDelivered($tripId);
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
            TripCreated::class => $this->createTripCreatedEvent($data['trip_id']),
            TripMarkedAsInProgress::class => $this->createTripMarkedAsInProgressEvent($data['trip_id'], $payload),
            TripDelivered::class => $this->createTripDeliveredEvent($data['trip_id']),
            default => throw new UndefinedDomainEventException(),
        };
    }
}