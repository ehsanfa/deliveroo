<?php

declare(strict_types=1);

namespace App\Delivery\Driver;

use App\Delivery\Driver\Event\DriverAssigned;
use App\Delivery\Driver\Event\DriverCreated;
use App\Delivery\Driver\Event\DriverReserved;
use App\Delivery\Driver\Exception\DriverNotFreeException;
use App\Delivery\Driver\Exception\DriverNotReserved;
use App\Delivery\Shared\Exception\HydrationException;
use App\Shared\Distance\Distance;
use App\Shared\Distance\DistanceCalculator;
use App\Delivery\Trip\ReadOnlyTripRepository;
use App\Delivery\Trip;
use App\Shared\Type\AggregateRoot;
use App\Shared\Type\Changeable;
use App\Shared\Type\Changeset;
use App\Shared\Type\ComparisonResult;
use App\Shared\Type\DomainEvents;
use App\Shared\Type\InvalidUuidException;
use App\Shared\Type\Location;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidValidator;

class Driver implements AggregateRoot
{
    use DomainEvents, Changeable;

    private bool $isDirty = false;
    private bool $isFresh = false;
    private float $score = 1;
    private ?Location $location = null;
    private ?\DateTimeImmutable $lastLocationUpdateAt = null;

    private function __construct(
        private readonly Id $id,
        private Status $status,
    ) {
    }

    public static function create(
        Id $id,
        Status $status,
    ): Driver {
        $driver = new self(
            id: $id,
            status: $status,
        );
        $driver->isFresh = true;
        $driver->addDomainEvent(
            new DriverCreated(
                driverId: $driver->getId(),
            )
        );
        return $driver;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function isFree(): bool
    {
        return $this->status === Status::Free;
    }

    public function isReserved(): bool
    {
        return $this->status === Status::Reserved;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): void
    {
        $oldValue = $this->location;
        $this->location = $location;
        $this->appendChangeset(
            new Changeset(
                field: 'location',
                old: $oldValue !== null ? json_encode($oldValue->toArray()) : null,
                new: json_encode($location->toArray()),
            )
        );
    }

    public function getLastLocationUpdateAt(): ?\DateTimeImmutable
    {
        return $this->lastLocationUpdateAt?->setTimezone(new \DateTimeZone('UTC'));
    }

    public function setLastLocationUpdateAt(\DateTimeImmutable $lastLocationUpdateAt): void
    {
        $oldValue = $this->getLastLocationUpdateAt();
        $this->lastLocationUpdateAt = $lastLocationUpdateAt->setTimezone(new \DateTimeZone('UTC'));
        $this->appendChangeset(
            new Changeset(
                field: 'location_updated_at',
                old: $oldValue?->format('Y-m-d H:i:s'),
                new: $this->getLastLocationUpdateAt()->format('Y-m-d H:i:s'),
            )
        );
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
     * @throws DriverNotFreeException
     */
    public function reserveFor(Trip\Id $tripId): void
    {
        if (!$this->isFree()) {
            throw new DriverNotFreeException();
        }

        $this->changeStatusTo(Status::Reserved);
        $this->addDomainEvent(
            new DriverReserved(
                driverId: $this->getId(),
                tripId: $tripId,
            ),
        );
    }

    /**
     * @throws DriverNotReserved
     */
    public function markAsBusy(Trip\Id $tripId): void
    {
        if (!$this->isReserved()) {
            throw new DriverNotReserved();
        }

        $this->changeStatusTo(Status::Busy);
        $this->addDomainEvent(
            new DriverAssigned(
                driverId: $this->getId(),
                tripId: $tripId,
            ),
        );

    }

    public function markAsFree(): void
    {
        if ($this->isFree()) {
            return;
        }

        $this->changeStatusTo(Status::Free);
    }

    public function isRookie(ReadOnlyTripRepository $tripRepository): bool
    {
        $hasDoneMoreThanFiveTrips = $tripRepository->driverHasDoneMoreTripsThan(
            driver: $this,
            trips: 5
        );
        return !$hasDoneMoreThanFiveTrips;
    }

    public function isFresh(): bool
    {
        return $this->isFresh;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    private function setScore(float $score): void
    {
        $this->score = round($score, 3);
    }

    public function multiplyScoreBy(float $weight): Driver
    {
        $this->setScore($this->score * $weight);
        return $this;
    }

    public function isAround(
        Location $target,
        Distance $maxDistance,
        DistanceCalculator $distanceCalculator,
    ): bool {
        if (null === $this->getLocation()) {
            return false;
        }

        $driverDistanceToTarget = $distanceCalculator->calculate(
            from: $this->getLocation(),
            to: $target,
        );

        return $driverDistanceToTarget->compareTo($maxDistance) !== ComparisonResult::IsBiggerThan;
    }

    /**
     * @throws InvalidUuidException
     * @throws HydrationException
     * @throws \Exception
     */
    public static function fromArray(
        array $data,
        UuidValidator $uuidValidator,
    ): Driver {
        self::validateHydration($data);

        $driver = new self(
            id: new Id(Uuid::fromString($data['id'], $uuidValidator)),
            status: Status::from($data['status'])
        );

        if (isset($data['location_latitude'])
            && isset($data['location_longitude'])
        ) {
            $driver->setLocation(new Location(
                latitude: $data['location_latitude'],
                longitude: $data['location_longitude'],
            ));
        }

        if (isset($data['location_updated_at'])) {
            $driver->setLastLocationUpdateAt(
                new \DateTimeImmutable($data['location_updated_at']),
            );
        }

        return $driver;
    }

    /**
     * @throws HydrationException
     */
    private static function validateHydration(array $data): void
    {
        $fields = [
            'id',
            'status',
        ];

        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                throw new HydrationException($field);
            }
        }
    }

    public static function fromData(
        Id $id,
        Status $status,
        ?Location $location,
        ?\DateTimeImmutable $lastLocationUpdateAt,
    ): Driver {
        $driver = new self(
            id: $id,
            status: $status,
        );

        if (null !== $location) {
            $driver->setLocation($location);
        }

        if (null !== $lastLocationUpdateAt) {
            $driver->setLastLocationUpdateAt($lastLocationUpdateAt);
        }

        return $driver;
    }
}