<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Repository;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFoundException;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Status;
use App\Delivery\DriverRate\DriverList;
use App\Shared\Distance\Distance;
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
        'latitude' => -65.211880,
        'longitude' => 76.141423,
    ];

    public function __construct(
        private string $tableName,
        private string $eventStoreTableName,
        private Connection $connection,
        private UuidValidator $uuidValidator,
        private UuidGenerator $uuidGenerator,
    ) {
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
            ->where('d.id', ':id');
        $qb->setParameter('id', $driverId->toString());
        $res = $qb->fetchAssociative();

        if (false === $res) {
            return null;
        }

        return Driver::fromArray($res, $this->uuidValidator);
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

        $q = $this->connection->prepare("
            INSERT INTO {$this->tableName}
            (`id`, `status`, `location`, `location_updated_at`)
            VALUES (
                :id,
                :status,
                ST_SRID(POINT(:location_lat, :location_lng), 4326),
                :location_updated_at
            )
        ");
        $q->bindValue("id", $driver->getId()->toString());
        $q->bindValue("status", $driver->getStatus()->value);
        $q->bindValue("location_lat", $driverLocation->getLatitude());
        $q->bindValue("location_lng", $driverLocation->getLongitude());
        $q->bindValue("location_updated_at", $driver->getLastLocationUpdateAt()?->format('Y-m-d H:i:s'));
        $q->executeQuery();
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
            if ($changeset->getField() === 'status') {
                $changes['status'] = $changeset->getNew();
            }
        }

        $this->connection->beginTransaction();
        try {
            $this->connection->update(
                table: $this->tableName,
                data: $changes,
                criteria: [
                    'id' => $driver->getId()->toString(),
                ]
            );

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

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $this->connection->update(
            table: $this->tableName,
            data: $changes,
            criteria: [
                'id' => $driver->getId()->toString(),
            ]
        );
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
                'd.location_updated_at'
            )
            ->where('d.status = :status')
            ->andWhere('st_distance_sphere(d.location, ST_SRID(POINT(:location_lat, :location_lng), 4326)) <= :distanceInMeters')
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
}