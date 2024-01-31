<?php

declare(strict_types=1);

namespace App\Delivery\Trip;

use App\Delivery\Driver;
use App\Delivery\Shared\Exception\HydrationException;
use App\Delivery\Trip\Event\TripDelivered;
use App\Delivery\Trip\Event\TripMarkedAsInProgress;
use App\Delivery\Trip\Event\TripCreated;
use App\Delivery\Trip\Exception\TripIsNotOpenException;
use App\Shared\Distance\Distance;
use App\Shared\Distance\DistanceCalculator;
use App\Shared\Type\AggregateRoot;
use App\Shared\Type\Arrayable;
use App\Shared\Type\Changeable;
use App\Shared\Type\Changeset;
use App\Shared\Type\DomainEvents;
use App\Shared\Type\InvalidUuidException;
use App\Shared\Type\Location;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidValidator;

class Trip implements AggregateRoot, Arrayable
{
    use DomainEvents, Changeable;

    private bool $isDirty = false;
    private bool $isFresh = false;
    private ?Driver\Id $driverId = null;

    private function __construct(
        private readonly Id $id,
        private Status $status,
        private readonly Location $source,
        private readonly Location $destination,
    ) {
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public static function create(
        Id $id,
        Status $status,
        Location $source,
        Location $destination,
    ): Trip {
        $trip = new self(
            id: $id,
            status: $status,
            source: $source,
            destination: $destination,
        );
        $trip->isFresh = true;
        $trip->addDomainEvent(
            new TripCreated($trip->getId()),
        );
        return $trip;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getSource(): Location
    {
        return $this->source;
    }

    public function getDestination(): Location
    {
        return $this->destination;
    }

    public function getDriverId(): ?Driver\Id
    {
        return $this->driverId;
    }

    public function isDelivered(): bool
    {
        return $this->status === Status::Finished;
    }

    public function isOpen(): bool
    {
        return $this->status === Status::Open;
    }

    public function markAsDelivered(): void
    {
        if ($this->isDelivered()) {
            return;
        }

        $this->addDomainEvent(
            new TripDelivered($this->getId())
        );

        $this->changeStatusTo(Status::Finished);
    }

    public function isFresh(): bool
    {
        return $this->isFresh;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId()->toString(),
            'status' => $this->getStatus()->name,
            'source' => $this->getSource()->toArray(),
            'destination' => $this->getDestination()->toArray(),
        ];
    }

    public function distance(DistanceCalculator $calculator): Distance
    {
        return $calculator->calculate(
            from: $this->source,
            to: $this->destination,
        );
    }

    private function setDriverId(Driver\Id $driverId): void
    {
        $oldDriverId = $this->driverId;
        $this->driverId = $driverId;
        $this->isDirty = true;
        $this->addDomainEvent(
            new TripMarkedAsInProgress(
                tripId: $this->getId(),
                driverId: $driverId,
            ),
        );
        $this->appendChangeset(
            new Changeset(
                field: 'driver_id',
                old: $oldDriverId?->toString(),
                new: $driverId->toString(),
            )
        );
    }

    /**
     * @throws TripIsNotOpenException
     */
    public function markAsAssigned(Driver\Id $driverId): void
    {
        if (!$this->isOpen()) {
            throw new TripIsNotOpenException();
        }

        $this->setDriverId($driverId);
        $this->changeStatusTo(Status::InProgress);
    }

    private function changeStatusTo(Status $status): void
    {
        $oldStatus = $this->status;
        $this->status = $status;
        $this->appendChangeset(
            new Changeset(
                field: 'status',
                old: $oldStatus->value,
                new: $this->getStatus()->value,
            )
        );
    }

    /**
     * @throws HydrationException
     * @throws InvalidUuidException
     */
    public static function fromArray(
        array $data,
        UuidValidator $uuidValidator
    ): Trip {
        self::validateHydration($data);
        $uuid = Uuid::fromString(
            string: $data["id"],
            validator: $uuidValidator
        );
        $id = new Id($uuid);
        $status = Status::from($data["status"]);
        $driverId = $data['driver_id'] !== null ? new Driver\Id(Uuid::fromString(
            string: $data['driver_id'],
            validator: $uuidValidator,
        )) : null;
        $source = new Location(
            latitude: $data["source_latitude"],
            longitude: $data["source_longitude"],
        );
        $destination = new Location(
            latitude: $data["destination_latitude"],
            longitude: $data["destination_longitude"],
        );

        $trip = new self(
            $id,
            $status,
            $source,
            $destination,
        );
        if (null !== $driverId) {
            $trip->setDriverId($driverId);
        }
        return $trip;
    }

    /**
     * @throws HydrationException
     */
    private static function validateHydration(array $data): void
    {
        $fields = [
            "id",
            "status",
            "source_latitude",
            "source_longitude",
            "destination_latitude",
            "destination_longitude",
        ];
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                throw new HydrationException($field);
            }
        }
    }
}