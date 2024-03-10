<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Port\Persistence\Repository;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFoundException;
use App\Delivery\Driver\Exception\EmptyAssociatedTrip;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Status;
use App\Delivery\Driver\DriverList;
use App\Delivery\Trip;
use App\Shared\Distance\Distance;
use App\Shared\Type\Changeset;
use App\Shared\Type\DomainEventWithPayload;
use App\Shared\Type\InvalidUuidException;
use App\Shared\Type\Location;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidGenerator;
use App\Shared\Type\UuidValidator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class DbalDriverRepository implements DriverRepository
{
    private const array PLACEHOLDER_LOCATION = [
        'latitude' => -65.21188000000,
        'longitude' => 76.14142300000,
    ];

    public function __construct(
        private string $tableName,
        private string $eventStoreTableName,
        private string $driverReservationTableName,
        private Connection $connection,
        private UuidValidator $uuidValidator,
        private UuidGenerator $uuidGenerator,
    ) {
    }

    private function persistDomainEvents(Driver $driver): void
    {
        foreach ($driver->getDomainEvents() as $domainEvent) {
            $payload = null;
            if ($domainEvent instanceof DomainEventWithPayload) {
                $payload = $domainEvent->getPayload();
            }
            $this->connection->insert($this->eventStoreTableName, [
                'id' => $this->uuidGenerator->generate()->toString(),
                'driver_id' => $domainEvent->getAggregateRootId()->toString(),
                'event' => $domainEvent::getIdentifier(),
                'payload' => $payload !== null ? json_encode($payload) : null,
            ]);
        }
        $driver->flushDomainEvents();
    }

    public function find(Id $driverId): ?Driver
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->from($this->tableName, 'd')
            ->select(
                'd.id',
                'd.status',
                'ST_Latitude(d.location) as location_latitude',
                'ST_Longitude(d.location) as location_longitude',
                'd.location_updated_at'
            )
            ->where('d.id = :id');
        $qb->setParameter('id', $driverId->toString());
        $res = $qb->fetchAssociative();

        if (false === $res) {
            return null;
        }

        return $this->getDriverFromArray($res);
    }

    private function isLocationNull(float $lat, float $lng): bool
    {
        if ($lat === self::PLACEHOLDER_LOCATION['latitude']
            && $lng === self::PLACEHOLDER_LOCATION['longitude']
        ) {
            return true;
        }
        return false;
    }

    /**
     * @throws InvalidUuidException
     * @throws \Exception
     */
    private function getDriverFromArray(array $data): Driver
    {
        $driverId = new Id(Uuid::fromString($data['id'], $this->uuidValidator));
        $status = Status::from((int)$data['status']);

        $location = null;
        $lat = (float)$data['location_latitude'];
        $lng = (float)$data['location_longitude'];

        if (!$this->isLocationNull($lat, $lng)) {
            $location = new Location($lat, $lng);
        }

        $locationUpdatedAt = null;
        if (null !== $data['location_updated_at']) {
            $locationUpdatedAt = new \DateTimeImmutable($data['location_updated_at']);
        }

        return Driver::fromData(
            $driverId,
            $status,
            $location,
            $locationUpdatedAt,
        );
    }

    /**
     * @throws Exception
     */
    public function create(Driver $driver): void
    {
        if (!$driver->isFresh()) {
            return;
        }

        $driverLocation = $driver->getLocation();
        if (null === $driverLocation) {
            $driverLocation = new Location(
                latitude: self::PLACEHOLDER_LOCATION['latitude'],
                longitude: self::PLACEHOLDER_LOCATION['longitude'],
            );
        }

        $this->connection->beginTransaction();
        try {
            $this->persistDomainEvents($driver);

            $q = $this->connection->prepare("
                INSERT INTO {$this->tableName}
                (`id`, `status`, `location`, `location_updated_at`)
                VALUES (
                    :id,
                    :status,
                    ST_SRID(POINT(:location_lng, :location_lat), 4326),
                    :location_updated_at
                )
            ");
            $q->bindValue("id", $driver->getId()->toString());
            $q->bindValue("status", $driver->getStatus()->value);
            $q->bindValue("location_lat", $driverLocation->getLatitude());
            $q->bindValue("location_lng", $driverLocation->getLongitude());
            $q->bindValue("location_updated_at", $driver->getLastLocationUpdateAt()?->format('Y-m-d H:i:s'));
            $q->executeQuery();

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * @throws Exception
     * @throws \Throwable
     */
    public function update(Driver $driver): void
    {
        if (!$driver->isDirty()) {
            return;
        }

        $changes = [];
        foreach ($driver->getChangesets() as $changeset) {
            $changes[$changeset->getField()] = $changeset->getNew();
        }

        $this->connection->beginTransaction();
        try {
            if (isset($changes['location'])) {
                $location = json_decode($changes['location'], associative: true);
                unset($changes['location']);
                $this->updateDriverLocation($driver->getId(), $location);
            }

            $this->handleDriverReservation($driver);

            if ($changes) {
                $this->connection->update(
                    table: $this->tableName,
                    data: $changes,
                    criteria: [
                        'id' => $driver->getId()->toString(),
                    ]
                );
            }

            $this->persistDomainEvents($driver);

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param Driver $driver
     * @throws EmptyAssociatedTrip
     * @throws Exception
     */
    private function handleDriverReservation(Driver $driver): void
    {
        $addDriverReservation = false;
        $deleteDriverReservation = false;

        foreach ($driver->getChangesets() as $change) {
            if ($change->getField() === 'status') {
                if (Status::tryFrom($change->getNew()) === Status::Reserved) {
                    $addDriverReservation = true;
                } elseif (Status::tryFrom($change->getOld()) === Status::Reserved) {
                    $deleteDriverReservation = true;
                }
            }
        }

        if ($addDriverReservation) {
            $this->addDriverReservation($driver);
        }

        if ($deleteDriverReservation) {
            $this->deleteDriverReservation($driver);
        }
    }

    /**
     * @throws EmptyAssociatedTrip
     * @throws Exception
     */
    private function addDriverReservation(Driver $driver): void
    {
        if (null === $driver->associatedTrip()) {
            throw new EmptyAssociatedTrip();
        }
        $this->connection->insert(
            table: $this->driverReservationTableName,
            data: [
                'driver_id' => $driver->getId()->toString(),
                'trip_id' => $driver->associatedTrip()->toString(),
            ]
        );
    }

    private function deleteDriverReservation(Driver $driver): void
    {
        if (null === $driver->associatedTrip()) {
            $this->deleteAllDriverReservations($driver);
            return;
        }

        $this->deleteDriverReservationForTrip($driver);
    }

    private function deleteAllDriverReservations(Driver $driver): void
    {
        $this->connection->delete(
            table: $this->driverReservationTableName,
            criteria: [
                'driver_id' => $driver->getId()->toString(),
            ],
        );
    }

    private function deleteDriverReservationForTrip(Driver $driver): void
    {
        $this->connection->delete(
            table: $this->driverReservationTableName,
            criteria: [
                'driver_id' => $driver->getId()->toString(),
                'trip_id' => $driver->associatedTrip()->toString(),
            ],
        );
    }

    /**
     * @param <string, float>[] $location
     * @throws Exception
     */
    private function updateDriverLocation(Id $driverId, array $location): void
    {
        $q = $this->connection->prepare("
                UPDATE {$this->tableName}
                SET location = ST_SRID(POINT(:location_lng, :location_lat), 4326),
                location_updated_at = :location_updated_at
                WHERE id = :driver_id
            ");
        $q->bindValue("location_lat", $location['latitude']);
        $q->bindValue("location_lng", $location['longitude']);
        $q->bindValue("location_updated_at", date('Y-m-d H:i:s'));
        $q->bindValue('driver_id', $driverId->toString());
        $q->executeQuery();
    }

    /**
     * @throws Exception
     */
    public function delete(Driver $driver): void
    {
        $this->connection->delete(
            table: $this->tableName,
            criteria: [
                'id' => $driver->getId()->toString(),
            ]
        );
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getStatusById(Id $driverId): Status
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('d.status')
            ->from($this->tableName, 'd')
            ->where('d.id = :id')
            ->setParameters([
                'id' => $driverId->toString(),
            ]);

        $driverStatus = $qb->fetchOne();
        if (false === $driverStatus) {
            throw new DriverNotFoundException();
        }

        return Status::from($driverStatus);
    }

    /**
     * @throws InvalidUuidException
     * @throws Exception
     * @throws \Exception
     */
    public function getFreeDriversAround(
        Location $location,
        Distance $distance,
        \DateTimeImmutable $lastActivityUntil
    ): DriverList {
        $qb = $this->connection->createQueryBuilder();
        $qb->from($this->tableName, 'd')
            ->select(
                'd.id',
                'd.status',
                'ST_Latitude(d.location) as location_latitude',
                'ST_Longitude(d.location) as location_longitude',
                'd.location_updated_at',
                'ST_Latitude(d.location) as location_latitude',
                'ST_Longitude(d.location) as location_longitude',
                'st_distance_sphere(d.location, ST_SRID(POINT(:location_lng, :location_lat), 4326))'
            )
            ->where('d.status = :status')
            ->andWhere('st_distance_sphere(d.location, ST_SRID(POINT(:location_lng, :location_lat), 4326)) <= :distanceInMeters')
            ->andWhere('d.location_updated_at >= :locationUpdatedAt')
            ->setParameters([
                'status' => Status::Free->value,
                'location_lat' => $location->getLatitude(),
                'location_lng' => $location->getLongitude(),
                'distanceInMeters' => $distance->meters(),
                'locationUpdatedAt' => $lastActivityUntil->format('Y-m-d H:i:s'),
            ]);

        $fetchedDrivers = $qb->fetchAllAssociative();
        $drivers = [];
        foreach ($fetchedDrivers as $fetchedDriver) {
            $drivers[] = Driver::fromData(
                new Id(Uuid::fromString($fetchedDriver['id'], $this->uuidValidator)),
                status: Status::from($fetchedDriver['status']),
                location: new Location(
                    latitude: $fetchedDriver['location_latitude'],
                    longitude: $fetchedDriver['location_longitude'],
                ),
                lastLocationUpdateAt: new \DateTimeImmutable($fetchedDriver['location_updated_at']),
            );
        }

        return new DriverList($drivers);
    }

    /**
     * @throws Exception
     * @throws InvalidUuidException
     */
    public function getReservedDriversForTrip(Trip\Id $tripId): DriverList
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->from($this->driverReservationTableName)
            ->select('driver_id')
            ->where('trip_id = :trip_id')
            ->setParameter('trip_id', $tripId->toString());

        $driversList = [];
        foreach ($qb->fetchAllAssociative() as $reservedDriver) {
            $driverId = new Id(Uuid::fromString($reservedDriver['driver_id'], $this->uuidValidator));
            $driversList[] = $this->find($driverId);
        }

        return new DriverList($driversList);
    }

    public function nextIdentity(): Id
    {
        return new Id($this->uuidGenerator->generate());
    }
}